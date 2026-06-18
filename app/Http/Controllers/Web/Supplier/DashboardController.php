<?php

namespace App\Http\Controllers\Web\Supplier;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $supplierIds = $request->user()->suppliers()->pluck('suppliers.id');

        $period = in_array($request->query('period'), ['today', 'week', 'month'], true)
            ? $request->query('period')
            : 'month';

        [$from, $to, $prevFrom, $prevTo, $periodLabel] = $this->periodRange($period);

        // null delta = no comparable base in the previous period.
        $delta = fn ($c, $p) => $p > 0 ? round(($c - $p) / $p * 100, 1) : ($c > 0 ? null : 0.0);

        $cur  = $this->kpiTotals($supplierIds, $from, $to);
        $prev = $this->kpiTotals($supplierIds, $prevFrom, $prevTo);

        $kpi = [
            'rfqs'              => (int) $cur->rfqs,
            'submitted'         => (int) $cur->submitted,
            'won'               => (int) $cur->won,
            'confirmed_revenue' => (float) $cur->confirmedRevenue,
            'completed_revenue' => (float) $cur->completedRevenue,
            'd_rfqs'            => $delta((int) $cur->rfqs, (int) $prev->rfqs),
            'd_submitted'       => $delta((int) $cur->submitted, (int) $prev->submitted),
            'd_won'             => $delta((int) $cur->won, (int) $prev->won),
            'd_confirmed'       => $delta((float) $cur->confirmedRevenue, (float) $prev->confirmedRevenue),
        ];

        return view('pages.supplier.cabinet.dashboard', [
            'period'      => $period,
            'periodLabel' => $periodLabel,
            'kpi'         => $kpi,
            'queue'       => $this->actionQueue($supplierIds),
            'funnel'      => $this->funnel($supplierIds),
            'chart'       => $this->monthlyChart($supplierIds),
            'recent'      => $this->recentRfqs($supplierIds),
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
                __('dashboard.supplier.period_label_today'),
            ],
            'week' => [
                $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(),
                $now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay(),
                __('dashboard.supplier.period_label_week'),
            ],
            default => [
                $now->copy()->startOfMonth(), $now->copy()->endOfMonth(),
                $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(),
                __('dashboard.supplier.period_label_month'),
            ],
        };
    }

    /**
     * Period KPI counters scoped to the supplier: RFQs received, offers submitted,
     * offers won (selected — funnel only) plus real money earned via bookings.
     *
     * Money is NOT derived from "selected" offers: an offer being picked into a
     * proposal does not mean the agency accepted it, nor that the tour happened.
     * Revenue is read from booking_items (frozen net cost paid to the supplier):
     *   - confirmed: agency accepted → a booking exists and is not cancelled
     *     (anchored on bookings.confirmed_at)
     *   - completed: the tour actually took place (status = completed,
     *     anchored on bookings.travel_date_to — when it ended)
     */
    private function kpiTotals($supplierIds, Carbon $from, Carbon $to): object
    {
        $rfqs = DB::table('rfq_supplier')
            ->whereIn('supplier_id', $supplierIds)
            ->whereBetween('sent_at', [$from, $to])
            ->count();

        $submitted = Offer::whereIn('supplier_id', $supplierIds)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // Funnel metric only — "won" = picked into a proposal, not guaranteed money.
        $won = Offer::whereIn('supplier_id', $supplierIds)
            ->where('status', OfferStatus::Selected->value)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $confirmedRevenue = (float) DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('booking_items.supplier_id', $supplierIds)
            ->where('bookings.status', '!=', BookingStatus::Cancelled->value)
            ->whereBetween('bookings.confirmed_at', [$from, $to])
            ->sum('booking_items.net_amount_azn');

        $completedRevenue = (float) DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('booking_items.supplier_id', $supplierIds)
            ->where('bookings.status', BookingStatus::Completed->value)
            ->whereBetween('bookings.travel_date_to', [$from->copy()->toDateString(), $to->copy()->toDateString()])
            ->sum('booking_items.net_amount_azn');

        return (object) compact('rfqs', 'submitted', 'won', 'confirmedRevenue', 'completedRevenue');
    }

    /**
     * "Needs your attention" — current pipeline items for the supplier.
     *
     * @return array<int, array<string, mixed>>
     */
    private function actionQueue($supplierIds): array
    {
        $soon = Carbon::today()->addDays(3);

        $openAssigned = fn () => Rfq::whereIn('status', [RfqStatus::Sent->value, RfqStatus::Awaiting->value])
            ->whereHas('suppliers', fn ($q) => $q->whereIn('suppliers.id', $supplierIds))
            ->whereDoesntHave('offers', fn ($q) => $q->whereIn('supplier_id', $supplierIds));

        $needResponse = $openAssigned()->count();

        $dueSoon = $openAssigned()
            ->whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<=', $soon)
            ->count();

        $underReview = Offer::whereIn('supplier_id', $supplierIds)
            ->whereIn('status', [OfferStatus::Received->value, OfferStatus::Reviewed->value])
            ->count();

        $won = Offer::whereIn('supplier_id', $supplierIds)
            ->where('status', OfferStatus::Selected->value)
            ->count();

        return [
            [
                'label' => __('dashboard.supplier.q_new_label'), 'hint' => __('dashboard.supplier.q_new_hint'),
                'count' => $needResponse, 'urgency' => 'warning', 'icon' => 'ki-questionnaire-tablet',
                'url' => route('supplier.rfqs.index'),
            ],
            [
                'label' => __('dashboard.supplier.q_deadline_label'), 'hint' => __('dashboard.supplier.q_deadline_hint'),
                'count' => $dueSoon, 'urgency' => 'danger', 'icon' => 'ki-time',
                'url' => route('supplier.rfqs.index'),
            ],
            [
                'label' => __('dashboard.supplier.q_review_label'), 'hint' => __('dashboard.supplier.q_review_hint'),
                'count' => $underReview, 'urgency' => 'info', 'icon' => 'ki-book-open',
                'url' => route('supplier.offers.index'),
            ],
            [
                'label' => __('dashboard.supplier.q_won_label'), 'hint' => __('dashboard.supplier.q_won_hint'),
                'count' => $won, 'urgency' => 'success', 'icon' => 'ki-medal-star',
                'url' => route('supplier.offers.index'),
            ],
        ];
    }

    /**
     * Conversion funnel: RFQs received → offers submitted → won (all-time).
     *
     * @return array<string, int>
     */
    private function funnel($supplierIds): array
    {
        $rfqs = (int) DB::table('rfq_supplier')
            ->whereIn('supplier_id', $supplierIds)
            ->distinct()
            ->count('rfq_id');

        $submitted = Offer::whereIn('supplier_id', $supplierIds)->count();

        $won = Offer::whereIn('supplier_id', $supplierIds)
            ->where('status', OfferStatus::Selected->value)
            ->count();

        return compact('rfqs', 'submitted', 'won');
    }

    /**
     * Confirmed revenue (AZN, from bookings) + offers submitted per month, last 6 months.
     *
     * @return array{categories: array<int, string>, revenue: array<int, float>, submitted: array<int, int>}
     */
    private function monthlyChart($supplierIds): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $submittedRows = Offer::whereIn('supplier_id', $supplierIds)
            ->where('created_at', '>=', $start)
            ->selectRaw("to_char(created_at, 'YYYY-MM') as ym, COUNT(*) as cnt")
            ->groupBy(DB::raw("to_char(created_at, 'YYYY-MM')"))
            ->pluck('cnt', 'ym');

        $revenueRows = DB::table('booking_items')
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('booking_items.supplier_id', $supplierIds)
            ->where('bookings.status', '!=', BookingStatus::Cancelled->value)
            ->where('bookings.confirmed_at', '>=', $start)
            ->selectRaw("to_char(bookings.confirmed_at, 'YYYY-MM') as ym, COALESCE(SUM(booking_items.net_amount_azn), 0) as rev")
            ->groupBy(DB::raw("to_char(bookings.confirmed_at, 'YYYY-MM')"))
            ->pluck('rev', 'ym');

        $categories = $revenue = $submitted = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $categories[] = $m->locale(app()->getLocale())->translatedFormat('M') . ' ' . $m->format('y');
            $revenue[]    = round((float) ($revenueRows[$key] ?? 0), 2);
            $submitted[]  = (int) ($submittedRows[$key] ?? 0);
        }

        return ['categories' => $categories, 'revenue' => $revenue, 'submitted' => $submitted];
    }

    /**
     * Latest RFQs assigned to the supplier, with a "responded" flag.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentRfqs($supplierIds): array
    {
        return Rfq::with('request')
            ->whereHas('suppliers', fn ($q) => $q->whereIn('suppliers.id', $supplierIds))
            ->withCount(['offers as my_offers_count' => fn ($q) => $q->whereIn('supplier_id', $supplierIds)])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Rfq $r) => [
                'id'           => $r->public_code,
                'request_id'   => $r->request?->public_code,
                'title'        => $r->title ?? $r->request?->title ?? __('dashboard.supplier.request_fallback', ['id' => $r->public_code]),
                'service_type' => $r->service_type,
                'deadline_at'  => $r->deadline_at?->toIso8601String(),
                'responded'    => $r->my_offers_count > 0,
                'status'       => $r->status->value,
                'status_label' => $r->status->supplierLabel(),
                'status_badge' => $r->status->supplierBadgeClass(),
            ])
            ->all();
    }
}
