<?php

namespace App\Http\Controllers\Web\Agency;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $agencyIds = $request->user()->agencies()->pluck('agencies.id');
        $currency  = $request->user()->agencies()->value('currency_code') ?: 'AZN';

        $period = in_array($request->query('period'), ['today', 'week', 'month'], true)
            ? $request->query('period')
            : 'month';

        [$from, $to, $prevFrom, $prevTo, $periodLabel] = $this->periodRange($period);

        // null delta = no comparable base in the previous period.
        $delta = fn ($c, $p) => $p > 0 ? round(($c - $p) / $p * 100, 1) : ($c > 0 ? null : 0.0);

        $cur  = $this->kpiTotals($agencyIds, $from, $to);
        $prev = $this->kpiTotals($agencyIds, $prevFrom, $prevTo);

        $kpi = [
            'requests'    => (int) $cur->requests,
            'proposals'   => (int) $cur->proposals,
            'bookings'    => (int) $cur->bookings,
            'spend'       => (float) $cur->spend,
            'd_requests'  => $delta((int) $cur->requests, (int) $prev->requests),
            'd_proposals' => $delta((int) $cur->proposals, (int) $prev->proposals),
            'd_bookings'  => $delta((int) $cur->bookings, (int) $prev->bookings),
            'd_spend'     => $delta((float) $cur->spend, (float) $prev->spend),
        ];

        return view('pages.agency.dashboard.index', [
            'period'      => $period,
            'periodLabel' => $periodLabel,
            'currency'    => $currency,
            'kpi'         => $kpi,
            'queue'       => $this->actionQueue($agencyIds),
            'funnel'      => $this->funnel($agencyIds),
            'chart'       => $this->monthlyChart($agencyIds),
            'upcoming'    => $this->upcomingTrips($agencyIds),
            'recent'      => $this->recentRequests($agencyIds),
        ]);
    }

    /**
     * @return array{0:Carbon,1:Carbon,2:Carbon,3:Carbon,4:string}
     */
    private function periodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(), $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay(),
                __('dashboard.agency.period_label_today'),
            ],
            'week' => [
                $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(),
                $now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay(),
                __('dashboard.agency.period_label_week'),
            ],
            default => [
                $now->copy()->startOfMonth(), $now->copy()->endOfMonth(),
                $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(),
                __('dashboard.agency.period_label_month'),
            ],
        };
    }

    /**
     * Period KPI counters scoped to the agency: requests created, proposals
     * received (anything past draft), bookings confirmed and money spent.
     */
    private function kpiTotals($agencyIds, Carbon $from, Carbon $to): object
    {
        $requests = TravelRequest::whereIn('agency_id', $agencyIds)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $proposals = Proposal::whereHas('request', fn ($q) => $q->whereIn('agency_id', $agencyIds))
            ->where('status', '!=', ProposalStatus::Draft->value)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $bookingAgg = Booking::whereIn('agency_id', $agencyIds)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->whereBetween('confirmed_at', [$from, $to])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(final_price), 0) as spend')
            ->first();

        return (object) [
            'requests'  => $requests,
            'proposals' => $proposals,
            'bookings'  => (int) $bookingAgg->cnt,
            'spend'     => (float) $bookingAgg->spend,
        ];
    }

    /**
     * "Needs your attention" — current pipeline items the agency must act on.
     *
     * @return array<int, array<string, mixed>>
     */
    private function actionQueue($agencyIds): array
    {
        $today = Carbon::today();
        $soon  = Carbon::today()->addDays(3);

        $proposalsPending = Proposal::whereHas('request', fn ($q) => $q->whereIn('agency_id', $agencyIds))
            ->where('status', ProposalStatus::Sent->value)
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->count();

        $dueSoon = TravelRequest::whereIn('agency_id', $agencyIds)
            ->whereIn('status', [RequestStatus::Submitted->value, RequestStatus::Processing->value])
            ->whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<=', $soon)
            ->count();

        $awaitingPayment = Booking::whereIn('agency_id', $agencyIds)
            ->where('status', BookingStatus::AwaitingPayment->value)
            ->count();

        $upcoming = Booking::whereIn('agency_id', $agencyIds)
            ->whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::Completed->value])
            ->whereNotNull('travel_date_from')
            ->whereBetween('travel_date_from', [$today, $today->copy()->addDays(14)])
            ->count();

        return [
            [
                'label' => __('dashboard.agency.q_proposals_label'), 'hint' => __('dashboard.agency.q_proposals_hint'),
                'count' => $proposalsPending, 'urgency' => 'warning', 'icon' => 'ki-questionnaire-tablet',
                'url' => route('agency.requests.index'),
            ],
            [
                'label' => __('dashboard.agency.q_deadline_label'), 'hint' => __('dashboard.agency.q_deadline_hint'),
                'count' => $dueSoon, 'urgency' => 'danger', 'icon' => 'ki-time',
                'url' => route('agency.requests.index'),
            ],
            [
                'label' => __('dashboard.agency.q_payment_label'), 'hint' => __('dashboard.agency.q_payment_hint'),
                'count' => $awaitingPayment, 'urgency' => 'primary', 'icon' => 'ki-wallet',
                'url' => route('agency.bookings.index'),
            ],
            [
                'label' => __('dashboard.agency.q_upcoming_label'), 'hint' => __('dashboard.agency.q_upcoming_hint'),
                'count' => $upcoming, 'urgency' => 'info', 'icon' => 'ki-airplane',
                'url' => route('agency.bookings.index'),
            ],
        ];
    }

    /**
     * Conversion funnel: requests → got a proposal → booked (all-time).
     *
     * @return array<string, int>
     */
    private function funnel($agencyIds): array
    {
        $requests = TravelRequest::whereIn('agency_id', $agencyIds)->count();

        $withProposal = TravelRequest::whereIn('agency_id', $agencyIds)
            ->whereHas('proposals', fn ($q) => $q->where('status', '!=', ProposalStatus::Draft->value))
            ->count();

        $booked = Booking::whereIn('agency_id', $agencyIds)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->count();

        return [
            'requests'      => $requests,
            'with_proposal' => $withProposal,
            'booked'        => $booked,
        ];
    }

    /**
     * Bookings count + spend per month for the last 6 months, gap-filled.
     *
     * @return array{categories: array<int, string>, spend: array<int, float>, bookings: array<int, int>}
     */
    private function monthlyChart($agencyIds): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $rows = Booking::whereIn('agency_id', $agencyIds)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '>=', $start)
            ->selectRaw(
                "to_char(confirmed_at, 'YYYY-MM') as ym,
                 COUNT(*) as cnt,
                 COALESCE(SUM(final_price), 0) as spend"
            )
            ->groupBy(DB::raw("to_char(confirmed_at, 'YYYY-MM')"))
            ->get()
            ->keyBy('ym');

        $categories = $spend = $bookings = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $categories[] = $m->locale(app()->getLocale())->translatedFormat('M') . ' ' . $m->format('y');
            $spend[]      = round((float) ($rows[$key]->spend ?? 0), 2);
            $bookings[]   = (int) ($rows[$key]->cnt ?? 0);
        }

        return ['categories' => $categories, 'spend' => $spend, 'bookings' => $bookings];
    }

    /**
     * Next trips starting from today, soonest first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function upcomingTrips($agencyIds): array
    {
        return Booking::with('proposal.request')
            ->whereIn('agency_id', $agencyIds)
            ->whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::Completed->value])
            ->whereNotNull('travel_date_from')
            ->whereDate('travel_date_from', '>=', Carbon::today())
            ->orderBy('travel_date_from')
            ->limit(5)
            ->get()
            ->map(fn (Booking $b) => [
                'id'           => $b->public_code,
                'title'        => $b->proposal?->request?->title ?? $b->proposal?->title ?? __('dashboard.agency.booking_fallback', ['id' => $b->public_code]),
                'date_from'    => $b->travel_date_from?->toDateString(),
                'date_to'      => $b->travel_date_to?->toDateString(),
                'days_until'   => (int) Carbon::today()->diffInDays($b->travel_date_from, false),
                'price'        => (float) $b->final_price,
                'pax'          => $b->pax_count,
                'status'       => $b->status->value,
                'status_label' => $b->status->agencyLabel(),
                'status_badge' => $b->status->agencyBadgeClass(),
            ])
            ->all();
    }

    /**
     * Latest requests with their proposal counts, shaped for the quick-view modal.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentRequests($agencyIds): array
    {
        // Счётчик КП = только видимые агентству (sent/accepted/rejected), как на детальной странице.
        return TravelRequest::withCount(['proposals' => fn ($q) => $q->whereIn('status', [
                ProposalStatus::Sent->value,
                ProposalStatus::Accepted->value,
                ProposalStatus::Rejected->value,
            ])])
            ->whereIn('agency_id', $agencyIds)
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (TravelRequest $r) => [
                'id'                 => $r->public_code,
                'title'              => $r->title,
                'destination'        => $r->destination,
                'pax_count'          => $r->pax_count,
                'services_needed'    => $r->services_needed,
                'notes'              => $r->notes,
                'travel_date_from'   => $r->travel_date_from?->toDateString(),
                'travel_date_to'     => $r->travel_date_to?->toDateString(),
                'deadline_at'        => $r->deadline_at?->toIso8601String(),
                'created_at'         => $r->created_at?->toIso8601String(),
                'updated_at'         => $r->updated_at?->toIso8601String(),
                'proposals_count'    => $r->proposals_count,
                'status'             => $r->status->value,
                'status_label'       => $r->status->agencyLabel(),
                'status_badge_class' => $r->status->agencyBadgeClass(),
            ])
            ->all();
    }
}
