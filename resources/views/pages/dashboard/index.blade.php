@extends('layouts.app')

@section('title', __('nav.dashboard'))
@section('page-title', __('dashboard.page_title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.dashboard') }}</li>
@endsection

@php
    $fmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $deltaBadge = function ($d) {
        if ($d === null) {
            return '<span class="badge badge-light-secondary fs-8">' . __('common.new') . '</span>';
        }
        $cls   = $d > 0 ? 'success' : ($d < 0 ? 'danger' : 'secondary');
        $arrow = $d > 0 ? '↑' : ($d < 0 ? '↓' : '');
        return '<span class="badge badge-light-' . $cls . ' fs-8">' . $arrow . ' ' . number_format(abs($d), 1) . '%</span>';
    };
    $periods = [
        'today' => __('dashboard.period.today'),
        'week'  => __('dashboard.period.week'),
        'month' => __('dashboard.period.month'),
    ];
@endphp

@section('content')

{{-- ── Period toggle ───────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
    <span class="text-muted fw-semibold fs-7">{{ __('dashboard.summary') }} · {{ $periodLabel }}</span>
    <div class="btn-group" role="group">
        @foreach ($periods as $key => $label)
            <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
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
                    <span class="text-gray-500 fw-semibold fs-7" title="{{ __('dashboard.kpi.revenue_hint') }}">{{ __('dashboard.kpi.revenue') }}</span>
                    {!! $deltaBadge($kpi['d_revenue']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($kpi['revenue']) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.kpi.margin') }}</span>
                    {!! $deltaBadge($kpi['d_margin']) !!}
                </div>
                <span class="fs-2hx fw-bold text-success">{{ $fmt($kpi['margin']) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
                <div class="text-muted fs-8 mt-1">{{ __('dashboard.kpi.markup', ['pct' => number_format($kpi['markup'], 1)]) }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.kpi.bookings') }}</span>
                    {!! $deltaBadge($kpi['d_bookings']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $kpi['bookings'] }}</span>
                <span class="text-muted fs-7 ms-1">{{ __('common.pcs') }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('dashboard.kpi.avg_check') }}</span>
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $fmt($kpi['avg_check']) }}</span>
                <span class="text-muted fs-7 ms-1">AZN</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Action queue ────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.queue_title') }}</span>
    </div>
    <div class="card-body pt-2">
        <div class="row g-4">
            @foreach ($queue as $item)
                <div class="col-6 col-xl">
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

{{-- ── Revenue / margin trend ──────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">{{ __('dashboard.chart_title') }}</span>
        <span class="text-muted fs-7 ms-2 align-self-center">{{ __('dashboard.chart_subtitle') }}</span>
    </div>
    <div class="card-body">
        <div id="dash-chart" style="min-height:300px"></div>
    </div>
</div>

{{-- ── Recent requests table ───────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title d-flex align-items-baseline gap-3">
            <h3 class="card-label fw-bold fs-4 mb-0">{{ __('dashboard.recent_title') }}</h3>
            <span class="text-muted fw-semibold fs-7" id="table-subtitle"></span>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-primary">
                {{ __('dashboard.all_requests') }}
            </a>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="recent-requests-container">
            <div class="text-center py-8">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
    <div class="card-footer border-top-0 py-4 text-end" id="table-footer" style="display:none">
        <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-light-primary">
            {{ __('dashboard.show_all') }} <i class="ki-outline ki-arrow-right fs-5"></i>
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Shared helpers (countryName, serviceBadge, statusBadge, formatDate, escHtml)
// come from partials/js-helpers.blade.php.

// Локализованные строки страницы (ru — источник, az/en — фолбэк на ru).
const t  = @json(__('dashboard'));
const tc = @json(__('common'));

// ── Revenue / margin chart ───────────────────────────────────────────────
(function renderChart() {
    const data = @json($chart);
    const el = document.getElementById('dash-chart');
    if (!el || typeof ApexCharts === 'undefined') return;

    const nf = new Intl.NumberFormat('ru-RU');
    new ApexCharts(el, {
        series: [
            { name: t.js.series_revenue, data: data.revenue },
            { name: t.js.series_margin, data: data.margin },
        ],
        chart:      { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        colors:     ['#1B84FF', '#17C653'],
        dataLabels: { enabled: false },
        stroke:     { curve: 'smooth', width: 2 },
        fill:       { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
        xaxis:      { categories: data.categories },
        yaxis:      { labels: { formatter: (v) => nf.format(Math.round(v)) } },
        tooltip:    { y: { formatter: (v) => nf.format(Math.round(v)) + ' AZN' } },
        legend:     { show: true, position: 'top', horizontalAlign: 'left' },
        grid:       { borderColor: '#eff2f5', strokeDashArray: 4 },
    }).render();
})();

// ── Recent requests table ─────────────────────────────────────────────────
(async function loadRecentRequests() {
    try {
        const reqData   = await api.get('/requests?per_page=10');
        const requests  = reqData.data ?? [];
        const totalReqs = reqData.meta?.total ?? requests.length;

        if (totalReqs > requests.length) {
            document.getElementById('table-subtitle').textContent =
                t.js.recent_of.replace(':shown', requests.length).replace(':total', totalReqs);
            document.getElementById('table-footer').style.display = '';
        }

        renderRecentRequests(requests);
    } catch (err) {
        console.error('Dashboard load error:', err);
        document.getElementById('recent-requests-container').innerHTML =
            `<div class="alert alert-danger">${t.js.load_error}</div>`;
    }
})();

function renderRecentRequests(requests) {
    const container = document.getElementById('recent-requests-container');

    if (!requests || requests.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-document fs-3x text-gray-300 mb-4 d-block"></i>
                <div class="text-muted fs-6 mb-4">${t.js.empty}</div>
            </div>`;
        return;
    }

    const rows = requests.map(r => {
        const services = Array.isArray(r.services_needed) ? r.services_needed : [];
        const serviceBadges = services.length
            ? services.map(s => serviceBadge(s)).join('')
            : '<span class="text-muted fs-8">—</span>';

        const dateRange = (r.travel_date_from || r.travel_date_to)
            ? `${formatDate(r.travel_date_from)} <i class="ki-outline ki-arrow-right fs-8 mx-1"></i> ${formatDate(r.travel_date_to)}`
            : '—';

        const statsHtml = `
            <span class="d-inline-flex align-items-center gap-1 me-3" title="${t.js.tip_rfqs}">
                <i class="ki-outline ki-send fs-5 text-primary"></i>
                <span class="fw-bold fs-6 text-gray-800">${r.rfqs_count ?? 0}</span>
            </span>
            <span class="d-inline-flex align-items-center gap-1" title="${t.js.tip_proposals}">
                <i class="ki-outline ki-document fs-5 text-warning"></i>
                <span class="fw-bold fs-6 text-gray-800">${r.proposals_count ?? 0}</span>
            </span>`;

        return `
            <tr>
                <td class="w-100px pe-2">
                    <a href="/admin/requests/${r.id}" class="text-gray-800 text-hover-primary fw-bold">${r.id}</a>
                </td>
                <td>
                    <a href="/admin/requests/${r.id}" class="fw-bold text-gray-800 text-hover-primary d-block">${escHtml(r.title ?? '—')}</a>
                    <div class="text-muted fs-7 mb-1">${escHtml(r.destination ?? '')}</div>
                    <div>${serviceBadges}</div>
                </td>
                <td>
                    ${r.agency?.id
                        ? `<a href="/admin/agencies/${r.agency.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(r.agency.company_name ?? r.agency.name)}</a>
                           <div class="text-muted fs-7">${escHtml(countryName(r.agency.country))}</div>`
                        : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-center">
                    ${r.pax_count != null
                        ? `<span class="fw-bold fs-5 text-gray-800">${r.pax_count}</span>`
                        : '<span class="text-muted">—</span>'}
                </td>
                <td><span class="fs-7">${dateRange}</span></td>
                <td>${statsHtml}</td>
                <td>${statusBadge(r)}</td>
                <td class="text-end">
                    <a href="/admin/requests/${r.id}" class="btn btn-icon btn-sm btn-light-primary" title="${tc.open}">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`;
    }).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-3">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-100px pe-2">#</th>
                    <th class="min-w-220px">${t.cols.title}</th>
                    <th class="min-w-120px">${t.cols.agency}</th>
                    <th class="min-w-60px text-center">${t.cols.pax}</th>
                    <th class="min-w-160px">${t.cols.dates}</th>
                    <th class="min-w-110px">${t.cols.stats}</th>
                    <th class="min-w-90px">${t.cols.status}</th>
                    <th class="w-90px text-end"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>`;
}
</script>
@endpush
