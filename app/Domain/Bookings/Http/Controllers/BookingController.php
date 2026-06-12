<?php

namespace App\Domain\Bookings\Http\Controllers;

use App\Domain\Bookings\Http\Requests\CancelBookingRequest;
use App\Domain\Bookings\Http\Requests\CompleteBookingRequest;
use App\Domain\Bookings\Http\Resources\BookingResource;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Services\BookingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            abort(403, 'Access denied.');
        }

        $query = Booking::with(['agency', 'operator', 'proposal.request']);

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');
            $query->whereIn('agency_id', $agencyIds);
        }

        // Search is independent of the status chips, so apply it before counting.
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                if (ctype_digit((string) $s)) {
                    $q->orWhere('id', (int) $s);
                }
                $q->orWhereHas('proposal', fn ($pq) => $pq->where('title', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('proposal.request', fn ($rq) => $rq->where('title', 'ILIKE', "%{$s}%")
                                                                  ->orWhere('destination', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('agency', fn ($aq) => $aq->where('name', 'ILIKE', "%{$s}%"));
            });
        }

        // Per-status counts for the filter chips, before status narrowing.
        $counts = (clone $query)
            ->select('status', \DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Price sort uses the AZN basis: proposal snapshot (original_total_price)
        // falling back to the booking's final_price for a consistent ordering.
        $aznExpr = '(select coalesce(p.original_total_price, bookings.final_price) from proposals p where p.id = bookings.proposal_id)';
        match ($request->input('sort')) {
            'created_asc' => $query->orderBy('created_at'),
            'travel_asc'  => $query->orderByRaw('travel_date_from ASC NULLS LAST'),
            'travel_desc' => $query->orderByRaw('travel_date_from DESC NULLS LAST'),
            'price_asc'   => $query->orderByRaw("{$aznExpr} ASC NULLS LAST"),
            'price_desc'  => $query->orderByRaw("{$aznExpr} DESC NULLS LAST"),
            default       => $query->latest(),
        };

        $bookings = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => BookingResource::collection($bookings->items()),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'counts' => $counts,
                'total_all' => $counts->sum(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        $this->authorize('view', $booking);

        $booking->load(['agency', 'operator', 'proposal.request', 'items']);

        return response()->json([
            'success' => true,
            'data' => new BookingResource($booking),
        ]);
    }

    public function requestPayment(Request $request, Booking $booking): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403);
        }

        $booking = $this->bookingService->requestPayment($booking, $request->user());
        $booking->load(['agency', 'operator', 'proposal']);

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }

    public function markPaid(Request $request, Booking $booking): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403);
        }

        $booking = $this->bookingService->markPaid($booking, $request->user());
        $booking->load(['agency', 'operator', 'proposal']);

        return response()->json(['success' => true, 'data' => new BookingResource($booking)]);
    }

    public function complete(CompleteBookingRequest $request, Booking $booking): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can complete bookings.');
        }

        $booking = $this->bookingService->complete($booking, $request->user(), $request->validated('notes'));
        $booking->load(['agency', 'operator', 'proposal']);

        return response()->json([
            'success' => true,
            'data' => new BookingResource($booking),
        ]);
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403, 'Only operators can cancel bookings.');
        }

        $booking = $this->bookingService->cancel($booking, $request->user(), $request->validated('notes'));
        $booking->load(['agency', 'operator', 'proposal']);

        return response()->json([
            'success' => true,
            'data' => new BookingResource($booking),
        ]);
    }
}
