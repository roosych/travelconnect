<?php

namespace App\Domain\Requests\Http\Controllers;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Http\Requests\StoreRequestRequest;
use App\Domain\Requests\Http\Resources\TravelRequestResource;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Requests\Services\RequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct(
        private readonly RequestService $requestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            abort(403, 'Access denied.');
        }

        $query = TravelRequest::with(['agency', 'legs.country', 'legs.destinations', 'legs.services'])
            ->withCount(['rfqs', 'proposals', 'bookings',
                // КП, видимые агентству (детальная страница показывает те же статусы).
                'proposals as received_proposals_count' => fn ($q) => $q->whereIn('status', [
                    ProposalStatus::Sent->value,
                    ProposalStatus::Accepted->value,
                    ProposalStatus::Rejected->value,
                ]),
            ])
            ->addSelect([
                'suppliers_notified_count' => \DB::table('rfq_supplier')
                    ->join('rfqs', 'rfqs.id', '=', 'rfq_supplier.rfq_id')
                    ->whereColumn('rfqs.request_id', 'travel_requests.id')
                    ->selectRaw('count(*)'),
                'offers_count' => \DB::table('offers')
                    ->join('rfqs', 'rfqs.id', '=', 'offers.rfq_id')
                    ->whereColumn('rfqs.request_id', 'travel_requests.id')
                    ->selectRaw('count(*)'),
            ]);

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereIn('agency_id', $agencyIds);
        }

        if ($user->isOperator()) {
            // Операторы не видят черновики агентств — это приватная стадия
            // подготовки заявки. Исключаем до подсчёта чипов, чтобы счётчики
            // тоже не учитывали черновики.
            $query->where('status', '!=', RequestStatus::Draft->value);

            if ($request->filled('agency_id')) {
                $query->where('agency_id', $request->integer('agency_id'));
            }
        }

        // Search is independent of the status chips, so apply it before counting.
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'ILIKE', "%{$s}%")
                  ->orWhere('destination', 'ILIKE', "%{$s}%")
                  ->orWhereHas('agency', fn ($aq) => $aq->where('name', 'ILIKE', "%{$s}%"));
            });
        }

        // Per-status counts + "due soon" count, computed before status/due narrowing
        // so the chips always show the full picture for the current search/agency.
        $counts = (clone $query)
            ->select('status', \DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $dueSoon = (clone $query)
            ->whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<=', now()->addDays(3))
            ->whereNotIn('status', ['completed', 'cancelled', 'booked'])
            ->count();

        // "Due soon" triage: open requests whose deadline is within 3 days or overdue.
        if ($request->input('due') === 'soon') {
            $query->whereNotNull('deadline_at')
                  ->whereDate('deadline_at', '<=', now()->addDays(3))
                  ->whereNotIn('status', ['completed', 'cancelled', 'booked']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting (whitelisted). Default: newest first.
        // Tiebreak by id so identical created_at (e.g. seeded in one transaction)
        // still produce a deterministic newest-first order.
        match ($request->input('sort')) {
            'created_asc'  => $query->orderBy('created_at')->orderBy('id'),
            'deadline_asc' => $query->orderByRaw('deadline_at ASC NULLS LAST')->orderByDesc('id'),
            'pax_desc'     => $query->orderByDesc('pax_count')->orderByDesc('id'),
            default        => $query->latest()->orderByDesc('id'),
        };

        $perPage  = min((int) $request->input('per_page', 15), 100);
        $requests = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => TravelRequestResource::collection($requests->items()),
            'meta'    => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
                'counts'       => $counts,
                'total_all'    => $counts->sum(),
                'due_soon'     => $dueSoon,
            ],
        ]);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAgency()) {
            $agency = $user->agencies()->first();
            abort_unless($agency, 403, 'Пользователь не привязан ни к одному агентству.');
        } elseif ($user->isOperator()) {
            $agencyId = $request->validated()['agency_id'] ?? null;
            if (! $agencyId) {
                return response()->json(['success' => false, 'message' => 'agency_id обязателен для операторов.'], 422);
            }
            $agency = Agency::findOrFail($agencyId);
        } else {
            abort(403, 'Только агентства или операторы могут создавать заявки.');
        }

        $data = $request->validated();

        // Дедлайн введён как «местное» время автора → переводим в UTC для хранения.
        if (! empty($data['deadline_at'])) {
            $data['deadline_at'] = \Illuminate\Support\Carbon::parse($data['deadline_at'], $user->effectiveTimezone())->utc();
        }

        $travelRequest = $this->requestService->createDraft($data, $agency);
        $travelRequest->load('agency');

        return response()->json([
            'success' => true,
            'data'    => new TravelRequestResource($travelRequest),
        ], 201);
    }

    public function show(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('view', $travelRequest);

        $travelRequest->load(['agency', 'legs.country', 'legs.destinations', 'legs.services', 'bookings']);
        $travelRequest->loadCount([
            'rfqs',
            'bookings',
            'proposals as proposals_count' => fn ($q) => $q->whereIn('status', ['sent', 'accepted', 'rejected']),
        ]);

        return response()->json([
            'success' => true,
            'data'    => new TravelRequestResource($travelRequest),
        ]);
    }

    public function update(StoreRequestRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('update', $travelRequest);

        if ($request->user()->isAgency() && $travelRequest->status !== \App\Domain\Requests\Enums\RequestStatus::Draft) {
            abort(422, 'Редактирование заявки доступно только в статусе «Черновик».');
        }

        $data = $request->validated();

        // Дедлайн введён в поясе автора → переводим в UTC.
        if (! empty($data['deadline_at'])) {
            $data['deadline_at'] = \Illuminate\Support\Carbon::parse($data['deadline_at'], $request->user()->effectiveTimezone())->utc();
        }

        $this->requestService->updateDraft($travelRequest, $data);
        $travelRequest->load('agency');

        return response()->json([
            'success' => true,
            'data'    => new TravelRequestResource($travelRequest),
        ]);
    }

    public function submit(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('update', $travelRequest);

        $travelRequest = $this->requestService->submit($travelRequest, $request->user());
        $travelRequest->load('agency');

        return response()->json([
            'success' => true,
            'data'    => new TravelRequestResource($travelRequest),
        ]);
    }

    public function cancel(Request $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('update', $travelRequest);

        $travelRequest = $this->requestService->cancel($travelRequest, $request->user());
        $travelRequest->load('agency');

        return response()->json([
            'success' => true,
            'data'    => new TravelRequestResource($travelRequest),
        ]);
    }
}
