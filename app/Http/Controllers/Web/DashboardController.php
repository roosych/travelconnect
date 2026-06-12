<?php

namespace App\Http\Controllers\Web;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\RFQs\Models\Rfq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const RU_MONTHS = [1 => 'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];

    public function index(Request $request)
    {
        $period = in_array($request->query('period'), ['today', 'week', 'month'], true)
            ? $request->query('period')
            : 'month';

        [$from, $to, $prevFrom, $prevTo, $periodLabel] = $this->periodRange($period);

        $cur  = $this->kpiTotals($from, $to);
        $prev = $this->kpiTotals($prevFrom, $prevTo);

        // null delta = no comparable base in the previous period.
        $delta = fn ($c, $p) => $p > 0 ? round(($c - $p) / $p * 100, 1) : ($c > 0 ? null : 0.0);

        $kpi = [
            'revenue'    => (float) $cur->sell,
            'margin'     => (float) $cur->margin,
            'bookings'   => (int) $cur->cnt,
            'avg_check'  => $cur->cnt > 0 ? (float) $cur->sell / $cur->cnt : 0,
            'markup'     => $cur->cost > 0 ? (float) $cur->margin / $cur->cost * 100 : 0,
            'd_revenue'  => $delta((float) $cur->sell, (float) $prev->sell),
            'd_margin'   => $delta((float) $cur->margin, (float) $prev->margin),
            'd_bookings' => $delta((int) $cur->cnt, (int) $prev->cnt),
        ];

        return view('pages.dashboard.index', [
            'period'      => $period,
            'periodLabel' => $periodLabel,
            'kpi'         => $kpi,
            'queue'       => $this->actionQueue(),
            'chart'       => $this->monthlyChart(),
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
                __('dashboard.period_label.today'),
            ],
            'week' => [
                $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(),
                $now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay(),
                __('dashboard.period_label.week'),
            ],
            default => [
                $now->copy()->startOfMonth(), $now->copy()->endOfMonth(),
                $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth(),
                __('dashboard.period_label.month'),
            ],
        };
    }

    /**
     * Financial totals in AZN over [$from, $to] by booking confirmation date.
     * Only bookings carrying a cost snapshot, excluding cancelled.
     */
    private function kpiTotals(Carbon $from, Carbon $to): object
    {
        return Booking::query()
            ->whereNotNull('cost_total_azn')
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->whereBetween('confirmed_at', [$from, $to])
            ->selectRaw(
                'COUNT(*) as cnt,
                 COALESCE(SUM(cost_total_azn), 0) as cost,
                 COALESCE(SUM(sell_total_azn), 0) as sell,
                 COALESCE(SUM(margin_azn), 0) as margin'
            )
            ->first();
    }

    /**
     * "Needs action now" items — current pipeline state, not period-bound.
     *
     * @return array<int, array<string, mixed>>
     */
    private function actionQueue(): array
    {
        $today = Carbon::today();
        $soon  = Carbon::today()->addDays(3);

        $newRequests = TravelRequest::where('status', RequestStatus::Submitted->value)->count();

        $rfqOverdue = Rfq::whereIn('status', [RfqStatus::Sent->value, RfqStatus::Awaiting->value])
            ->whereNotNull('deadline_at')
            ->whereDate('deadline_at', '<', $today)
            ->count();

        $offersReview = Offer::where('status', OfferStatus::Received->value)->count();

        $proposalsSent = Proposal::where('status', ProposalStatus::Sent->value)->count();
        $proposalsExpiring = Proposal::where('status', ProposalStatus::Sent->value)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', $soon)
            ->count();

        $awaitingPayment = Booking::where('status', BookingStatus::AwaitingPayment->value)->count();

        return [
            [
                'label' => __('dashboard.queue.new_requests'), 'hint' => __('dashboard.queue.new_requests_hint'),
                'count' => $newRequests, 'urgency' => 'info', 'icon' => 'ki-document',
                'url' => route('admin.requests.index'),
            ],
            [
                'label' => __('dashboard.queue.rfq_overdue'), 'hint' => __('dashboard.queue.rfq_overdue_hint'),
                'count' => $rfqOverdue, 'urgency' => 'danger', 'icon' => 'ki-send',
                'url' => route('admin.rfqs.index'),
            ],
            [
                'label' => __('dashboard.queue.offers_review'), 'hint' => __('dashboard.queue.offers_review_hint'),
                'count' => $offersReview, 'urgency' => 'warning', 'icon' => 'ki-tag',
                'url' => route('admin.offers.index'),
            ],
            [
                'label' => __('dashboard.queue.proposals_sent'),
                'hint' => $proposalsExpiring > 0
                    ? __('dashboard.queue.proposals_expiring', ['count' => $proposalsExpiring])
                    : __('dashboard.queue.proposals_sent_hint'),
                'count' => $proposalsSent, 'urgency' => $proposalsExpiring > 0 ? 'warning' : 'primary', 'icon' => 'ki-questionnaire-tablet',
                'url' => route('admin.proposals.index'),
            ],
            [
                'label' => __('dashboard.queue.awaiting_payment'), 'hint' => __('dashboard.queue.awaiting_payment_hint'),
                'count' => $awaitingPayment, 'urgency' => 'primary', 'icon' => 'ki-wallet',
                'url' => route('admin.bookings.index'),
            ],
        ];
    }

    /**
     * Revenue + margin (AZN) per month for the last 6 months, gap-filled.
     *
     * @return array{categories: array<int, string>, revenue: array<int, float>, margin: array<int, float>}
     */
    private function monthlyChart(): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $rows = Booking::query()
            ->whereNotNull('cost_total_azn')
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->where('confirmed_at', '>=', $start)
            ->selectRaw(
                "to_char(confirmed_at, 'YYYY-MM') as ym,
                 COALESCE(SUM(sell_total_azn), 0) as sell,
                 COALESCE(SUM(margin_azn), 0) as margin"
            )
            ->groupBy(DB::raw("to_char(confirmed_at, 'YYYY-MM')"))
            ->get()
            ->keyBy('ym');

        $categories = $revenue = $margin = [];
        for ($i = 5; $i >= 0; $i--) {
            $m   = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $categories[] = self::RU_MONTHS[(int) $m->format('n')] . ' ' . $m->format('y');
            $revenue[]    = round((float) ($rows[$key]->sell ?? 0), 2);
            $margin[]     = round((float) ($rows[$key]->margin ?? 0), 2);
        }

        return ['categories' => $categories, 'revenue' => $revenue, 'margin' => $margin];
    }
}
