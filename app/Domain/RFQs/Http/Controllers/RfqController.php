<?php

namespace App\Domain\RFQs\Http\Controllers;

use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\Services\ServiceCatalog;
use App\Domain\RFQs\Http\Requests\AddRfqSupplierRequest;
use App\Domain\RFQs\Http\Requests\SendRfqRequest;
use App\Domain\RFQs\Http\Requests\StoreRfqRequest;
use App\Domain\RFQs\Http\Resources\RfqResource;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\RFQs\Services\RfqService;
use App\Domain\Requests\Models\TravelRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RfqController extends Controller
{
    public function __construct(
        private readonly RfqService $rfqService,
    ) {}

    public function indexAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Rfq::with(['request', 'suppliers.media', 'offers', 'sharedAttachments', 'country', 'leg.services', 'leg.destinations']);

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereHas('request', fn ($q) => $q->whereIn('agency_id', $agencyIds));
        } elseif ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            $query->whereHas('suppliers', fn ($q) => $q->whereIn('suppliers.id', $supplierIds));
        }

        // Filters that are independent of the status chips. Applied to the base so
        // the per-status counts reflect the current search/service/request context.
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('request_id')) {
            $query->where('request_id', $request->integer('request_id'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'ILIKE', "%{$s}%")
                  ->orWhereHas('request', fn ($rq) => $rq->where('title', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('suppliers', fn ($sq) => $sq->where('suppliers.name', 'ILIKE', "%{$s}%"));
            });
        }

        // Per-status counts + "due soon" count, computed before status/due narrowing
        // so the chips always show the full picture for the current search/service.
        //
        // Поставщик видит список, СГРУППИРОВАННЫЙ по заявке (одна карточка = заявка),
        // поэтому считаем уникальные заявки: число на чипе == число карточек под фильтром.
        // Агентство/оператор видят плоский список RFQ — там обычный count(*).
        $isSupplier = $user->isSupplier();
        $countExpr  = $isSupplier ? \DB::raw('count(distinct request_id) as c') : \DB::raw('count(*) as c');

        $counts = (clone $query)
            ->select('status', $countExpr)
            ->groupBy('status')
            ->pluck('c', 'status');

        // «Открытые» = sent|awaiting. Для поставщика — уникальные заявки (нельзя
        // складывать distinct-счётчики статусов: заявка с обоими статусами задвоится).
        $openQuery = (clone $query)->whereIn('status', [RfqStatus::Sent->value, RfqStatus::Awaiting->value]);
        $open      = $isSupplier ? (clone $openQuery)->distinct()->count('request_id') : (clone $openQuery)->count();

        $dueSoonQuery = (clone $query)
            ->whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<=', now()->addDays(3))
            ->whereNotIn('status', ['closed', 'cancelled']);
        $dueSoon = $isSupplier ? (clone $dueSoonQuery)->distinct()->count('request_id') : (clone $dueSoonQuery)->count();

        $totalAll = $isSupplier ? (clone $query)->distinct()->count('request_id') : $counts->sum();

        // "Due soon" triage: open RFQs whose deadline is within 3 days or overdue.
        if ($request->input('due') === 'soon') {
            $query->whereNotNull('deadline_at')
                  ->whereDate('deadline_at', '<=', now()->addDays(3))
                  ->whereNotIn('status', ['closed', 'cancelled']);
        }

        if ($request->filled('status')) {
            // Suppliers see sent + awaiting as one "open" state, so allow status=open.
            if ($request->input('status') === 'open') {
                $query->whereIn('status', [RfqStatus::Sent->value, RfqStatus::Awaiting->value]);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        $perPage = $request->integer('per_page', 15);

        // Поставщик: единица пагинации — заявка (список сгруппирован по заявке).
        // Берём страницу уникальных request_id, отсортированных по агрегату их RFQ,
        // затем тянем ВСЕ их RFQ — чтобы заявка не разрывалась между страницами.
        if ($isSupplier) {
            [$aggExpr, $aggDir] = match ($request->input('sort')) {
                'created_asc'  => ['min(created_at)',  'asc'],
                'deadline_asc' => ['min(deadline_at)', 'asc'],
                default        => ['max(created_at)',  'desc'],
            };

            $orderedReqIds = (clone $query)
                ->select('request_id')
                ->groupBy('request_id')
                ->orderByRaw("{$aggExpr} {$aggDir} nulls last")
                ->pluck('request_id');

            $total    = $orderedReqIds->count();
            $lastPage = (int) max(1, ceil($total / $perPage));
            $page     = min(max(1, $request->integer('page', 1)), $lastPage);

            $pageReqIds = $orderedReqIds->forPage($page, $perPage)->values();
            $pos        = $pageReqIds->flip();   // request_id => позиция на странице

            $items = (clone $query)
                ->whereIn('request_id', $pageReqIds)
                ->get()
                // Порядок заявок = порядок страницы; внутри заявки — по id RFQ.
                ->sortBy(fn ($r) => sprintf('%06d-%012d', $pos[$r->request_id] ?? 999999, $r->id))
                ->values();

            return response()->json([
                'success' => true,
                'data'    => RfqResource::collection($items),
                'meta'    => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'per_page'     => $perPage,
                    'total'        => $total,
                    'counts'       => $counts,
                    'open'         => $open,
                    'total_all'    => $totalAll,
                    'due_soon'     => $dueSoon,
                ],
            ]);
        }

        match ($request->input('sort')) {
            'created_asc'  => $query->orderBy('created_at'),
            'deadline_asc' => $query->orderByRaw('deadline_at ASC NULLS LAST'),
            default        => $query->latest(),
        };

        $rfqs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => RfqResource::collection($rfqs->items()),
            'meta'    => [
                'current_page' => $rfqs->currentPage(),
                'last_page'    => $rfqs->lastPage(),
                'per_page'     => $rfqs->perPage(),
                'total'        => $rfqs->total(),
                'counts'       => $counts,
                'open'         => $open,
                'total_all'    => $totalAll,
                'due_soon'     => $dueSoon,
            ],
        ]);
    }

    public function index(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            abort(403, 'Access denied.');
        }

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            abort_unless($agencyIds->contains($travelRequest->agency_id), 403, 'Access denied.');
        }

        $query = $travelRequest->rfqs()->with(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        $rfqs = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => RfqResource::collection($rfqs->items()),
            'meta'    => [
                'current_page' => $rfqs->currentPage(),
                'last_page'    => $rfqs->lastPage(),
                'per_page'     => $rfqs->perPage(),
                'total'        => $rfqs->total(),
            ],
        ]);
    }

    public function store(StoreRfqRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $user = $request->user();

        if (! $user->isOperator()) {
            abort(403, 'Only operators can create RFQs.');
        }

        $rfq = $this->rfqService->createDraft($request->validated(), $travelRequest, $user);
        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ], 201);
    }

    public function show(Request $request, Rfq $rfq): JsonResponse
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

        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ]);
    }

    public function send(SendRfqRequest $request, Rfq $rfq): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can send RFQs.');
        }

        $supplierIds = $request->validated('supplier_ids') ?? [];
        $rfq = $this->rfqService->send($rfq, $supplierIds, $request->user());
        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ]);
    }

    public function close(Request $request, Rfq $rfq): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can close RFQs.');
        }

        $rfq = $this->rfqService->close($rfq, $request->user());
        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ]);
    }

    public function cancel(Request $request, Rfq $rfq): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can cancel RFQs.');
        }

        $rfq = $this->rfqService->cancel($rfq, $request->user());
        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ]);
    }

    /**
     * Предпросмотр рассылки: по сегментам и услугам — сколько подходящих
     * поставщиков и не разослана ли пара. Питает модалку рассылки у оператора.
     */
    public function preview(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can preview RFQ matches.');
        }

        return response()->json([
            'success' => true,
            'data'    => $this->rfqService->previewMatches($travelRequest),
        ]);
    }

    public function broadcast(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can broadcast RFQs.');
        }

        $data = $request->validate([
            'deadline_at'               => ['required', 'date', 'after_or_equal:today'],
            'notes'                     => ['nullable', 'string', 'max:2000'],
            'attachment_ids'            => ['nullable', 'array'],
            'attachment_ids.*'          => ['integer', 'exists:attachments,id'],
            'operator_attachment_ids'   => ['nullable', 'array'],
            'operator_attachment_ids.*' => ['integer', 'exists:attachments,id'],
            // Опц. выборка из UI: какие пары (сегмент × услуга) и каких поставщиков слать.
            'selection'                 => ['nullable', 'array'],
            'selection.*.leg_id'        => ['required_with:selection', 'integer'],
            'selection.*.service_type'  => ['required_with:selection', 'string', Rule::in(app(ServiceCatalog::class)->activeCodes())],
            'selection.*.supplier_ids'  => ['nullable', 'array'],
            'selection.*.supplier_ids.*'=> ['integer', 'exists:suppliers,id'],
        ]);

        // Дедлайн введён в поясе оператора → переводим в UTC (как при сохранении заявки).
        $data['deadline_at'] = \Illuminate\Support\Carbon::parse(
            $data['deadline_at'], $request->user()->effectiveTimezone()
        )->utc();

        $rfqs = $this->rfqService->broadcastToSuppliers($travelRequest, $data, $request->user());

        collect($rfqs)->each(fn ($rfq) => $rfq->loadCount('suppliers as supplier_count'));

        return response()->json([
            'success' => true,
            'data'    => RfqResource::collection($rfqs),
            'meta'    => ['count' => count($rfqs)],
        ], 201);
    }

    public function addSupplier(AddRfqSupplierRequest $request, Rfq $rfq): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can add suppliers to RFQs.');
        }

        $this->rfqService->addSupplier($rfq, $request->validated('supplier_ids'), $request->validated('service_types'), $request->validated('notes'));
        $rfq->load(['request', 'suppliers.media', 'offers', 'country', 'leg.services', 'leg.destinations']);

        return response()->json([
            'success' => true,
            'data'    => new RfqResource($rfq),
        ]);
    }
}
