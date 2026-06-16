<?php

namespace App\Domain\Offers\Http\Controllers;

use App\Domain\Offers\Http\Requests\StoreOfferRequest;
use App\Domain\Offers\Http\Resources\OfferResource;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Services\OfferService;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        private readonly OfferService $offerService,
    ) {}

    public function indexAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Offer::with(['supplier', 'rfq.country', 'rfq.request']);

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereHas('rfq.request', fn ($q) => $q->whereIn('agency_id', $agencyIds));
        } elseif ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            $query->whereIn('supplier_id', $supplierIds);
            // Нужно для производного «выиграно»/«можно отозвать» в OfferResource.
            $query->with('proposals');
        }

        // Filters independent of the status chips, applied before counting.
        if ($request->filled('service_type')) {
            $query->whereHas('rfq', fn ($rq) => $rq->where('service_type', $request->input('service_type')));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->whereHas('supplier', fn ($sq) => $sq->where('name', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('rfq', fn ($rq) => $rq->where('title', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('rfq.request', fn ($rq) => $rq->where('title', 'ILIKE', "%{$s}%")
                                                            ->orWhere('destination', 'ILIKE', "%{$s}%"));
            });
        }

        // Per-status counts + "expiring soon" count, before status/expiring narrowing.
        $counts = (clone $query)
            ->select('status', \DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $expiring = (clone $query)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', now()->addDays(3))
            ->whereIn('status', ['received', 'reviewed'])
            ->count();

        // "Expiring soon" triage: still-open offers whose validity ends within 3 days.
        if ($request->input('expiring') === 'soon') {
            $query->whereNotNull('valid_until')
                  ->whereDate('valid_until', '<=', now()->addDays(3))
                  ->whereIn('status', ['received', 'reviewed']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        match ($request->input('sort')) {
            'created_asc'     => $query->orderBy('created_at'),
            'price_asc'       => $query->orderByRaw('unit_price_azn ASC NULLS LAST'),
            'price_desc'      => $query->orderByRaw('unit_price_azn DESC NULLS LAST'),
            'valid_until_asc' => $query->orderByRaw('valid_until ASC NULLS LAST'),
            default           => $query->latest(),
        };

        $offers = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => OfferResource::collection($offers->items()),
            'meta'    => [
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'per_page'     => $offers->perPage(),
                'total'        => $offers->total(),
                'counts'       => $counts,
                'total_all'    => $counts->sum(),
                'expiring'     => $expiring,
            ],
        ]);
    }

    public function indexForRequest(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            abort(403, 'Access denied.');
        }

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($travelRequest->agency_id), 403, 'Access denied.');
        }

        $offers = Offer::with(['supplier', 'rfq', 'items.supplierService.media', 'attachments'])
            ->whereHas('rfq', fn ($q) => $q->where('request_id', $travelRequest->id))
            ->latest()
            ->get()
            ->map(fn (Offer $offer) => $this->offerService->checkExpiry($offer));

        return response()->json([
            'success' => true,
            'data'    => OfferResource::collection($offers),
        ]);
    }

    public function index(Request $request, Rfq $rfq): JsonResponse
    {
        $user = $request->user();

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($rfq->request->agency_id), 403, 'Access denied.');
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            abort_unless($rfq->suppliers()->whereIn('suppliers.id', $supplierIds)->exists(), 403, 'Access denied.');
        }

        $query = $rfq->offers()->with(['supplier', 'items', 'attachments']);

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            $query->whereIn('supplier_id', $supplierIds);
            // Производный статус «выиграно»/доступность отзыва в OfferResource.
            $query->with('proposals');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $offers = $query->latest()->paginate(15);

        // Run expiry check on each offer
        $items = collect($offers->items())->map(
            fn (Offer $offer) => $this->offerService->checkExpiry($offer)
        );

        return response()->json([
            'success' => true,
            'data'    => OfferResource::collection($items),
            'meta'    => [
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'per_page'     => $offers->perPage(),
                'total'        => $offers->total(),
            ],
        ]);
    }

    public function store(StoreOfferRequest $request, Rfq $rfq): JsonResponse
    {
        $user = $request->user();

        if ($user->isAgency()) {
            abort(403, 'Access denied.');
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            abort_unless(
                $rfq->suppliers()->whereIn('suppliers.id', $supplierIds)->exists(),
                403,
                'Этот запрос не назначен вашей компании.'
            );
            $supplierId = $supplierIds->first();

            $hasActive = Offer::where('rfq_id', $rfq->id)
                ->where('supplier_id', $supplierId)
                ->whereNotIn('status', ['withdrawn', 'rejected', 'expired'])
                ->exists();

            if ($hasActive) {
                return response()->json(['message' => 'У вас уже есть активное предложение по этому запросу.'], 422);
            }
        } else {
            $supplierId = $request->validated('supplier_id');
            abort_if(empty($supplierId), 422, 'supplier_id is required.');
        }

        $data = $request->validated();
        // «Действительно до» введено в поясе автора (поставщик/оператор) → переводим в UTC.
        if (! empty($data['valid_until'])) {
            $data['valid_until'] = \Illuminate\Support\Carbon::parse(
                $data['valid_until'], $user->effectiveTimezone()
            )->utc();
        } else {
            // Поставщик больше не задаёт срок действия — берём дедлайн запроса,
            // который оператор обязательно вводит при рассылке (NOT NULL, required).
            // Валидность предложения для агентства задаётся отдельно на уровне КП.
            $data['valid_until'] = $rfq->deadline_at;
        }

        $offer = $this->offerService->recordOffer(
            $data,
            $rfq,
            $supplierId,
            $user,
            operatorEntered: $user->isOperator(),
        );

        $offer->load('supplier');

        return response()->json([
            'success' => true,
            'data'    => new OfferResource($offer),
        ], 201);
    }

    public function show(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($offer->rfq->request->agency_id), 403, 'Access denied.');
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            abort_unless($supplierIds->contains($offer->supplier_id), 403, 'Access denied.');
        }

        $offer = $this->offerService->checkExpiry($offer);
        $offer->load(['supplier', 'items.supplierService.media', 'rfq.country', 'rfq.request.agency']);
        // Для поставщика — производный статус «выиграно»/доступность отзыва.
        if ($user->isSupplier()) {
            $offer->load('proposals');
        }

        return response()->json([
            'success' => true,
            'data'    => new OfferResource($offer),
        ]);
    }

    public function reject(Request $request, Offer $offer): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can reject offers.');
        }

        $offer = $this->offerService->reject($offer);
        $offer->load('supplier');

        return response()->json([
            'success' => true,
            'data'    => new OfferResource($offer),
        ]);
    }

    public function withdraw(Request $request, Offer $offer): JsonResponse
    {
        $user = $request->user();

        if ($user->isAgency()) {
            abort(403, 'Access denied.');
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            abort_unless($supplierIds->contains($offer->supplier_id), 403, 'Access denied.');
        }

        $offer = $this->offerService->markWithdrawn($offer);
        $offer->load('supplier');

        return response()->json([
            'success' => true,
            'data'    => new OfferResource($offer),
        ]);
    }
}
