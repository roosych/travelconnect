<?php

namespace App\Http\Controllers\Web;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingItem;
use App\Domain\Payments\Models\Payment;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Services\ServiceCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportWebController extends Controller
{
    private const GROUPS = ['supplier', 'service_type', 'agency', 'month'];

    /**
     * Margin report (operator-only). All money is in AZN — the stable working
     * currency the cost snapshot was frozen in (see booking_items / rollups).
     */
    public function margin(Request $request)
    {
        $from     = $request->query('from') ?: null;
        $to       = $request->query('to') ?: null;
        $agencyId = $request->query('agency_id') ?: null;
        $groupBy  = in_array($request->query('group_by'), self::GROUPS, true)
            ? $request->query('group_by')
            : 'supplier';

        // Only bookings that actually carry a cost snapshot, excluding cancelled.
        $applyFilters = function ($q) use ($from, $to, $agencyId) {
            $q->whereNotNull('bookings.cost_total_azn')
              ->where('bookings.status', '!=', BookingStatus::Cancelled->value);

            if ($from) {
                $q->whereDate('bookings.confirmed_at', '>=', $from);
            }
            if ($to) {
                $q->whereDate('bookings.confirmed_at', '<=', $to);
            }
            if ($agencyId) {
                $q->where('bookings.agency_id', $agencyId);
            }
        };

        // KPI totals from booking rollups.
        $totalsQuery = Booking::query();
        $applyFilters($totalsQuery);
        $totals = $totalsQuery->selectRaw(
            'COUNT(*) as cnt,
             COALESCE(SUM(cost_total_azn), 0) as cost,
             COALESCE(SUM(sell_total_azn), 0) as sell,
             COALESCE(SUM(margin_azn), 0) as margin'
        )->first();

        // Фактическая касса по подтверждённым платежам тех же броней (всё в AZN):
        // получено от агентств (incoming) и выплачено поставщикам (outgoing).
        $idsQuery = Booking::query();
        $applyFilters($idsQuery);
        $bookingIds = $idsQuery->pluck('id');

        $cash = Payment::query()
            ->whereNotNull('confirmed_at')
            ->where('payable_type', (new Booking)->getMorphClass())
            ->whereIn('payable_id', $bookingIds)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN direction = 'incoming' THEN amount_base ELSE 0 END), 0) as received,
                 COALESCE(SUM(CASE WHEN direction = 'outgoing' THEN amount_base ELSE 0 END), 0) as paid_out"
            )->first();

        $totals->received = (float) $cash->received;
        $totals->paid_out = (float) $cash->paid_out;

        // Breakdown by the chosen dimension.
        $rows = $this->breakdown($groupBy, $applyFilters);

        return view('pages.reports.margin', [
            'groupBy'   => $groupBy,
            'from'      => $from,
            'to'        => $to,
            'agencyId'  => $agencyId ? (int) $agencyId : null,
            'agencies'  => Agency::orderBy('name')->get(['id', 'name']),
            'totals'    => $totals,
            'rows'      => $rows,
        ]);
    }

    /**
     * Request conversion funnel — where deals are lost between stages.
     * Counts requests by created_at; draft (not yet submitted) is excluded.
     */
    public function funnel(Request $request)
    {
        $from     = $request->query('from') ?: null;
        $to       = $request->query('to') ?: null;
        $agencyId = $request->query('agency_id') ?: null;

        // Fresh builder over the in-period, non-draft requests for each metric.
        $base = function () use ($from, $to, $agencyId) {
            $q = TravelRequest::query()->where('status', '!=', RequestStatus::Draft->value);
            if ($from) {
                $q->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $q->whereDate('created_at', '<=', $to);
            }
            if ($agencyId) {
                $q->where('agency_id', $agencyId);
            }

            return $q;
        };

        $sentProposal = ['sent', 'accepted', 'rejected', 'expired'];

        $stages = [
            ['label' => __('reports.funnel.stages.created'),         'count' => $base()->count()],
            ['label' => __('reports.funnel.stages.rfq_sent'),        'count' => $base()->whereHas('rfqs')->count()],
            ['label' => __('reports.funnel.stages.offers_received'), 'count' => $base()->whereHas('rfqs.offers')->count()],
            ['label' => __('reports.funnel.stages.proposal_sent'),   'count' => $base()->whereHas('proposals', fn ($q) => $q->whereIn('status', $sentProposal))->count()],
            ['label' => __('reports.funnel.stages.booked'),          'count' => $base()->whereIn('status', ['booked', 'completed'])->count()],
            ['label' => __('reports.funnel.stages.completed'),       'count' => $base()->where('status', 'completed')->count()],
        ];

        // Where deals leak out of the pipeline.
        $leaks = [
            ['label' => __('reports.funnel.leaks.no_rfq.label'),    'count' => $base()->whereIn('status', ['submitted', 'processing'])->whereDoesntHave('rfqs')->count(), 'hint' => __('reports.funnel.leaks.no_rfq.hint')],
            ['label' => __('reports.funnel.leaks.no_offers.label'), 'count' => $base()->whereHas('rfqs')->whereDoesntHave('rfqs.offers')->count(),                       'hint' => __('reports.funnel.leaks.no_offers.hint')],
            ['label' => __('reports.funnel.leaks.proposal_unaccepted.label'), 'count' => $base()->whereNotIn('status', ['booked', 'completed'])->whereHas('proposals', fn ($q) => $q->whereIn('status', ['rejected', 'expired']))->count(), 'hint' => __('reports.funnel.leaks.proposal_unaccepted.hint')],
            ['label' => __('reports.funnel.leaks.cancelled.label'), 'count' => $base()->where('status', 'cancelled')->count(),                                            'hint' => __('reports.funnel.leaks.cancelled.hint')],
        ];

        return view('pages.reports.funnel', [
            'from'     => $from,
            'to'       => $to,
            'agencyId' => $agencyId ? (int) $agencyId : null,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'stages'   => $stages,
            'leaks'    => $leaks,
        ]);
    }

    /**
     * Supplier effectiveness — response rate, speed, win rate and incidents.
     * Period filters on rfq_supplier.sent_at (when the RFQ went out).
     */
    public function suppliers(Request $request)
    {
        $from     = $request->query('from') ?: null;
        $to       = $request->query('to') ?: null;
        $agencyId = $request->query('agency_id') ?: null;

        // First offer per (rfq, supplier) — used for response time and "answered".
        $firstOffer = DB::table('offers')
            ->select('rfq_id', 'supplier_id', DB::raw('MIN(created_at) as first_at'))
            ->groupBy('rfq_id', 'supplier_id');

        $query = DB::table('rfq_supplier as rs')
            ->join('suppliers as s', 's.id', '=', 'rs.supplier_id')
            ->leftJoinSub($firstOffer, 'fo', function ($join) {
                $join->on('fo.rfq_id', '=', 'rs.rfq_id')
                     ->on('fo.supplier_id', '=', 'rs.supplier_id');
            })
            ->whereNotNull('rs.sent_at');

        if ($from) {
            $query->whereDate('rs.sent_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('rs.sent_at', '<=', $to);
        }
        if ($agencyId) {
            $query->join('rfqs as r', 'r.id', '=', 'rs.rfq_id')
                  ->join('travel_requests as tr', 'tr.id', '=', 'r.request_id')
                  ->where('tr.agency_id', $agencyId);
        }

        $rows = $query
            ->groupBy('s.id', 's.name')
            ->selectRaw(
                's.id, s.name,
                 COUNT(*) as rfqs_sent,
                 COUNT(fo.first_at) as rfqs_answered,
                 AVG(EXTRACT(EPOCH FROM (fo.first_at - rs.sent_at)) / 3600.0) as avg_hours'
            )
            ->orderByDesc('rfqs_sent')
            ->get();

        // Wins: distinct offers that made it into an accepted proposal, filtered to
        // the same period by when the proposal was accepted.
        $winsQuery = DB::table('proposal_offer as po')
            ->join('proposals as p', 'p.id', '=', 'po.proposal_id')
            ->join('offers as o', 'o.id', '=', 'po.offer_id')
            ->where('p.status', 'accepted');
        if ($from) {
            $winsQuery->whereDate('p.accepted_at', '>=', $from);
        }
        if ($to) {
            $winsQuery->whereDate('p.accepted_at', '<=', $to);
        }
        if ($agencyId) {
            $winsQuery->join('travel_requests as tr2', 'tr2.id', '=', 'p.request_id')
                      ->where('tr2.agency_id', $agencyId);
        }
        $wins = $winsQuery->groupBy('o.supplier_id')
            ->selectRaw('o.supplier_id, COUNT(DISTINCT o.id) as wins')
            ->pluck('wins', 'o.supplier_id');

        // Incidents in the same period.
        $incQuery = DB::table('supplier_incidents');
        if ($from) {
            $incQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $incQuery->whereDate('created_at', '<=', $to);
        }
        $incidents = $incQuery->groupBy('supplier_id')
            ->selectRaw('supplier_id, COUNT(*) as cnt')
            ->pluck('cnt', 'supplier_id');

        $rows->each(function ($r) use ($wins, $incidents) {
            $r->wins          = (int) ($wins[$r->id] ?? 0);
            $r->incidents     = (int) ($incidents[$r->id] ?? 0);
            $r->response_rate = $r->rfqs_sent > 0 ? ($r->rfqs_answered / $r->rfqs_sent) * 100 : 0;
            $r->win_rate      = $r->rfqs_answered > 0 ? ($r->wins / $r->rfqs_answered) * 100 : 0;
        });

        return view('pages.reports.suppliers', [
            'from'     => $from,
            'to'       => $to,
            'agencyId' => $agencyId ? (int) $agencyId : null,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'rows'     => $rows,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object> rows with: label, bookings_cnt, cost, sell, margin
     */
    private function breakdown(string $groupBy, callable $applyFilters)
    {
        // supplier / service_type → aggregate the frozen line items.
        if (in_array($groupBy, ['supplier', 'service_type'], true)) {
            $col = $groupBy === 'supplier' ? 'booking_items.supplier_name' : 'booking_items.service_type';

            $query = BookingItem::query()
                ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id');
            $applyFilters($query);

            $rows = $query->selectRaw(
                "$col as grp,
                 COUNT(DISTINCT bookings.id) as bookings_cnt,
                 SUM(booking_items.net_amount_azn) as cost,
                 SUM(booking_items.sell_amount_azn) as sell,
                 SUM(booking_items.sell_amount_azn - booking_items.net_amount_azn) as margin"
            )
                ->groupBy(DB::raw($col))
                ->orderByDesc('margin')
                ->get();

            if ($groupBy === 'service_type') {
                $catalog = app(ServiceCatalog::class);
                $rows->each(function ($r) use ($catalog) {
                    $r->label = $r->grp ? $catalog->typeLabel($r->grp) : '—';
                });
            } else {
                $rows->each(fn ($r) => $r->label = $r->grp ?? '—');
            }

            return $rows;
        }

        // agency → aggregate booking rollups joined to the agency name.
        if ($groupBy === 'agency') {
            $query = Booking::query()
                ->join('agencies', 'agencies.id', '=', 'bookings.agency_id');
            $applyFilters($query);

            $rows = $query->selectRaw(
                'agencies.name as grp,
                 COUNT(*) as bookings_cnt,
                 SUM(bookings.cost_total_azn) as cost,
                 SUM(bookings.sell_total_azn) as sell,
                 SUM(bookings.margin_azn) as margin'
            )
                ->groupBy('agencies.name')
                ->orderByDesc('margin')
                ->get();

            $rows->each(fn ($r) => $r->label = $r->grp ?? '—');

            return $rows;
        }

        // month → group by YYYY-MM of confirmation date (Postgres to_char).
        $query = Booking::query();
        $applyFilters($query);

        $rows = $query->selectRaw(
            "to_char(bookings.confirmed_at, 'YYYY-MM') as grp,
             COUNT(*) as bookings_cnt,
             SUM(bookings.cost_total_azn) as cost,
             SUM(bookings.sell_total_azn) as sell,
             SUM(bookings.margin_azn) as margin"
        )
            ->groupBy(DB::raw("to_char(bookings.confirmed_at, 'YYYY-MM')"))
            ->orderBy('grp', 'desc')
            ->get();

        $rows->each(fn ($r) => $r->label = $r->grp ?? '—');

        return $rows;
    }
}
