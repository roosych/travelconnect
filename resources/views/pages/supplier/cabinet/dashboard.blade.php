@extends('layouts.supplier')

@section('title', __('nav.dashboard'))
@section('page-title', __('dashboard.supplier.page_title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('dashboard.supplier.home') }}</li>
@endsection

@php
    $sym   = 'AZN'; // выручка считается в AZN
    $money = fn ($v) => number_format((float) $v, 0, '.', ' ') . ' ' . $sym;

    $deltaBadge = function ($d) {
        if ($d === null) {
            return '<span class="badge badge-light-secondary fs-8">' . __('common.new') . '</span>';
        }
        $cls   = $d > 0 ? 'success' : ($d < 0 ? 'danger' : 'secondary');
        $arrow = $d > 0 ? '↑' : ($d < 0 ? '↓' : '');
        return '<span class="badge badge-light-' . $cls . ' fs-8">' . $arrow . ' ' . number_format(abs($d), 1) . '%</span>';
    };

    $periods = ['today' => __('dashboard.supplier.period_today'), 'week' => __('dashboard.supplier.period_week'), 'month' => __('dashboard.supplier.period_month')];

    $rfqTotal = max(1, $funnel['rfqs']);
    $pctSubmitted = round($funnel['submitted'] / $rfqTotal * 100);
    $pctWon       = round($funnel['won'] / $rfqTotal * 100);
    $winRate      = $funnel['submitted'] > 0 ? round($funnel['won'] / $funnel['submitted'] * 100) : 0;
@endphp

@section('content')

{{-- ── Period toggle ───────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
    <span class="text-muted fw-semibold fs-7">{{ __('dashboard.supplier.summary') }} · {{ $periodLabel }}</span>
    <div class="btn-group" role="group">
        @foreach ($periods as $key => $label)
            <a href="{{ route('supplier.dashboard', ['period' => $key]) }}"
               class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- ── KPI cards ───────────────────────────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.supplier.new_requests') }}</span>
                    {!! $deltaBadge($kpi['d_rfqs']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $kpi['rfqs'] }}</span>
                <span class="text-muted fs-7 ms-1">{{ __('dashboard.supplier.new_requests_unit') }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.supplier.offers_sent') }}</span>
                    {!! $deltaBadge($kpi['d_submitted']) !!}
                </div>
                <span class="fs-2hx fw-bold text-info">{{ $kpi['submitted'] }}</span>
                <span class="text-muted fs-7 ms-1">{{ __('common.pcs') }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.supplier.won') }}</span>
                    {!! $deltaBadge($kpi['d_won']) !!}
                </div>
                <span class="fs-2hx fw-bold text-success">{{ $kpi['won'] }}</span>
                <span class="text-muted fs-7 ms-1">{{ __('common.pcs') }}</span>
                <span class="text-muted fs-8 d-block mt-1">{{ __('dashboard.supplier.won_hint') }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7" title="{{ __('dashboard.supplier.confirmed_revenue_hint') }}">{{ __('dashboard.supplier.confirmed_revenue') }}</span>
                    {!! $deltaBadge($kpi['d_confirmed']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $money($kpi['confirmed_revenue']) }}</span>
                <div class="separator separator-dashed my-3"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted fs-8" title="{{ __('dashboard.supplier.completed_hint') }}">{{ __('dashboard.supplier.completed') }}</span>
                    <span class="fw-bold fs-7 text-success">{{ $money($kpi['completed_revenue']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Action queue ────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.supplier.attention') }}</span>
    </div>
    <div class="card-body pt-2">
        <div class="row g-4">
            @foreach ($queue as $item)
                <div class="col-6 col-xl-3">
                    <a href="{{ $item['url'] }}"
                       class="d-block border border-gray-300 border-dashed rounded p-4 h-100 text-hover-primary
                              {{ $item['count'] > 0 ? 'bg-light-' . $item['urgency'] : '' }}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ki-outline {{ $item['icon'] }} fs-3 text-{{ $item['count'] > 0 ? $item['urgency'] : 'gray-400' }}"></i>
                            <span class="fs-2hx fw-bold {{ $item['count'] > 0 ? 'text-' . $item['urgency'] : 'text-gray-400' }}">{{ $item['count'] }}</span>
                        </div>
                        <span class="fw-semibold text-gray-800 d-block">{{ $item['label'] }}</span>
                        <span class="text-muted fs-8 d-block">{{ $item['hint'] }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Chart + funnel ──────────────────────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-8">
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.supplier.dynamics') }}</span>
                <span class="text-muted fs-7 ms-2 align-self-center">{{ __('dashboard.supplier.dynamics_sub') }}</span>
            </div>
            <div class="card-body">
                <div id="dash-chart" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.supplier.funnel') }}</span>
                <span class="text-muted fs-7 ms-2 align-self-center">{{ __('dashboard.supplier.funnel_sub') }}</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center gap-6">
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-document fs-5 text-primary me-1"></i>{{ __('dashboard.supplier.funnel_received') }}</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['rfqs'] }}</span>
                    </div>
                    <div class="progress h-8px bg-light-primary"><div class="progress-bar bg-primary" style="width:100%"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-book-open fs-5 text-info me-1"></i>{{ __('dashboard.supplier.funnel_sent') }}</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['submitted'] }} <span class="text-muted fs-8">({{ $pctSubmitted }}%)</span></span>
                    </div>
                    <div class="progress h-8px bg-light-info"><div class="progress-bar bg-info" style="width:{{ $pctSubmitted }}%"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-medal-star fs-5 text-success me-1"></i>{{ __('dashboard.supplier.funnel_won') }}</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['won'] }} <span class="text-muted fs-8">({{ $pctWon }}%)</span></span>
                    </div>
                    <div class="progress h-8px bg-light-success"><div class="progress-bar bg-success" style="width:{{ $pctWon }}%"></div></div>
                </div>
                <div class="text-center border-top pt-4">
                    <span class="text-muted fs-7">{{ __('dashboard.supplier.win_rate') }}</span>
                    <span class="fw-bold fs-4 text-success ms-1">{{ $winRate }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent RFQs ─────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.supplier.recent') }}</span>
        <div class="card-toolbar">
            <a href="{{ route('supplier.rfqs.index') }}" class="btn btn-sm btn-light-primary">
                {{ __('dashboard.supplier.all_requests') }} <i class="ki-outline ki-arrow-right fs-5 ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body pt-2">
        <div id="recent-table"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* Shared helpers (serviceMeta, serviceBadge, deadlineCell, formatDate, escHtml)
   come from partials/js-helpers.blade.php. */

const RECENT = @json($recent);

/* ── Trend chart ───────────────────────────────────────────────────────── */
(function renderChart() {
    const data = @json($chart);
    const el = document.getElementById('dash-chart');
    if (!el || typeof ApexCharts === 'undefined') return;

    const nf = new Intl.NumberFormat('ru-RU');
    new ApexCharts(el, {
        series: [
            { name: @json(__('dashboard.supplier.confirmed_revenue')), type: 'area',   data: data.revenue },
            { name: @json(__('dashboard.supplier.funnel_sent')), type: 'column', data: data.submitted },
        ],
        chart:      { height: 300, toolbar: { show: false }, fontFamily: 'inherit', stacked: false },
        colors:     ['#17C653', '#1B84FF'],
        dataLabels: { enabled: false },
        stroke:     { curve: 'smooth', width: [2, 0] },
        fill:       { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
        plotOptions:{ bar: { columnWidth: '35%', borderRadius: 4 } },
        xaxis:      { categories: data.categories },
        yaxis: [
            { seriesName: @json(__('dashboard.supplier.confirmed_revenue')), labels: { formatter: (v) => nf.format(Math.round(v)) } },
            { seriesName: @json(__('dashboard.supplier.funnel_sent')), opposite: true, labels: { formatter: (v) => Math.round(v) }, min: 0 },
        ],
        tooltip:    { y: { formatter: (v, { seriesIndex }) => seriesIndex === 0 ? nf.format(Math.round(v)) + ' AZN' : Math.round(v) + ' ' + @json(__('common.pcs')) } },
        legend:     { show: true, position: 'top', horizontalAlign: 'left' },
        grid:       { borderColor: '#eff2f5', strokeDashArray: 4 },
    }).render();
})();

/* ── Recent RFQs table ─────────────────────────────────────────────────── */
(function renderRecent() {
    const wrap = document.getElementById('recent-table');

    if (!RECENT.length) {
        wrap.innerHTML = `
            <div class="text-center py-10">
                <i class="ki-outline ki-document fs-3x text-gray-300 mb-3 d-block"></i>
                <span class="text-muted fs-7">{{ __('dashboard.supplier.empty') }}</span>
            </div>`;
        return;
    }

    const rows = RECENT.map(r => {
        const sm = serviceMeta(r.service_type);
        const respondedHtml = r.responded
            ? '<span class="badge badge-light-success fs-8"><i class="ki-outline ki-check fs-7 me-1"></i>' + @json(__('dashboard.supplier.answered')) + '</span>'
            : '<span class="badge badge-light-warning fs-8">' + @json(__('dashboard.supplier.need_answer')) + '</span>';
        return `
            <tr>
                <td>
                    <a href="/supplier/rfqs/request/${r.request_id}" class="fw-bold text-gray-800 text-hover-primary d-block">${escHtml(r.title)}</a>
                </td>
                <td>
                    <i class="${sm.icon} fs-6 text-gray-500 me-1"></i>
                    <span class="fs-7 text-gray-700">${sm.label}</span>
                </td>
                <td>${deadlineCell(r.deadline_at, ['closed', 'cancelled'].includes(r.status))}</td>
                <td>${respondedHtml}</td>
                <td><span class="badge ${r.status_badge} fs-8">${escHtml(r.status_label)}</span></td>
                <td class="text-end">
                    <a href="/supplier/rfqs/request/${r.request_id}" class="btn btn-icon btn-sm btn-light-primary" title="${@json(__('common.open'))}">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`;
    }).join('');

    wrap.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-3">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-200px">${@json(__('dashboard.supplier.col_request'))}</th>
                    <th class="min-w-120px">${@json(__('dashboard.supplier.col_service'))}</th>
                    <th class="min-w-110px">${@json(__('dashboard.supplier.col_deadline'))}</th>
                    <th class="min-w-110px">${@json(__('dashboard.supplier.col_answer'))}</th>
                    <th class="min-w-90px">${@json(__('dashboard.supplier.col_status'))}</th>
                    <th class="w-70px text-end"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>`;
})();
</script>
@endpush
