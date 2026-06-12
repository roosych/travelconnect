@extends('layouts.agency')

@section('title', 'Главная')
@section('page-title', 'Мой кабинет')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Главная</li>
@endsection

@section('toolbar-actions')
    <!-- <a href="{{ route('agency.requests.create') }}" class="btn btn-success btn-sm">
        <i class="ki-outline ki-plus fs-4 me-1"></i>Новая заявка
    </a> -->
@endsection

@php
    $sym = $currency; // ISO-код валюты вместо символа
    $money = fn ($v) => number_format((float) $v, 0, '.', ' ') . ' ' . $sym;

    $deltaBadge = function ($d) {
        if ($d === null) {
            return '<span class="badge badge-light-secondary fs-8">нов.</span>';
        }
        $cls   = $d > 0 ? 'success' : ($d < 0 ? 'danger' : 'secondary');
        $arrow = $d > 0 ? '↑' : ($d < 0 ? '↓' : '');
        return '<span class="badge badge-light-' . $cls . ' fs-8">' . $arrow . ' ' . number_format(abs($d), 1) . '%</span>';
    };

    $periods = ['today' => 'Сегодня', 'week' => '7 дней', 'month' => 'Этот месяц'];

    $reqTotal = max(1, $funnel['requests']);
    $pctProposal = round($funnel['with_proposal'] / $reqTotal * 100);
    $pctBooked   = round($funnel['booked'] / $reqTotal * 100);
@endphp

@section('content')

{{-- ── Period toggle ───────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
    <span class="text-muted fw-semibold fs-7">Сводка · {{ $periodLabel }}</span>
    <div class="btn-group" role="group">
        @foreach ($periods as $key => $label)
            <a href="{{ route('agency.dashboard', ['period' => $key]) }}"
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
                    <span class="text-gray-500 fw-semibold fs-7">Новые заявки</span>
                    {!! $deltaBadge($kpi['d_requests']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $kpi['requests'] }}</span>
                <span class="text-muted fs-7 ms-1">{{ $periodLabel }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">Получено КП</span>
                    {!! $deltaBadge($kpi['d_proposals']) !!}
                </div>
                <span class="fs-2hx fw-bold text-info">{{ $kpi['proposals'] }}</span>
                <span class="text-muted fs-7 ms-1">шт</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">Бронирований</span>
                    {!! $deltaBadge($kpi['d_bookings']) !!}
                </div>
                <span class="fs-2hx fw-bold text-success">{{ $kpi['bookings'] }}</span>
                <span class="text-muted fs-7 ms-1">шт</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush h-100">
            <div class="card-body py-7 px-7">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-gray-500 fw-semibold fs-7">Расходы на туры</span>
                    {!! $deltaBadge($kpi['d_spend']) !!}
                </div>
                <span class="fs-2hx fw-bold text-gray-900">{{ $money($kpi['spend']) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Action queue ────────────────────────────────────────────────────── --}}
<div class="card card-flush mb-8">
    <div class="card-header pt-6">
        <span class="fw-bold fs-4 text-gray-800">Требует вашего внимания</span>
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
                <span class="fw-bold fs-4 text-gray-800">Динамика бронирований</span>
                <span class="text-muted fs-7 ms-2 align-self-center">последние 6 месяцев</span>
            </div>
            <div class="card-body">
                <div id="dash-chart" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <span class="fw-bold fs-4 text-gray-800">Воронка</span>
                <span class="text-muted fs-7 ms-2 align-self-center">за всё время</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center gap-6">
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-document fs-5 text-primary me-1"></i>Заявки</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['requests'] }}</span>
                    </div>
                    <div class="progress h-8px bg-light-primary"><div class="progress-bar bg-primary" style="width:100%"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-questionnaire-tablet fs-5 text-info me-1"></i>Получили КП</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['with_proposal'] }} <span class="text-muted fs-8">({{ $pctProposal }}%)</span></span>
                    </div>
                    <div class="progress h-8px bg-light-info"><div class="progress-bar bg-info" style="width:{{ $pctProposal }}%"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold text-gray-700"><i class="ki-outline ki-calendar-tick fs-5 text-success me-1"></i>Забронировали</span>
                        <span class="fw-bold text-gray-900">{{ $funnel['booked'] }} <span class="text-muted fs-8">({{ $pctBooked }}%)</span></span>
                    </div>
                    <div class="progress h-8px bg-light-success"><div class="progress-bar bg-success" style="width:{{ $pctBooked }}%"></div></div>
                </div>
                <div class="text-center border-top pt-4">
                    <span class="text-muted fs-7">Конверсия заявка → бронь:</span>
                    <span class="fw-bold fs-4 text-success ms-1">{{ $pctBooked }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Upcoming trips + recent requests ────────────────────────────────── --}}
<div class="row g-5 g-xl-8 mb-8">

    {{-- Upcoming trips --}}
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <span class="fw-bold fs-4 text-gray-800">Ближайшие поездки</span>
            </div>
            <div class="card-body pt-2">
                @forelse ($upcoming as $t)
                    <a href="{{ route('agency.bookings.show', $t['id']) }}"
                       class="d-flex align-items-center gap-3 p-3 rounded mb-2 bg-hover-light text-hover-primary">
                        <div class="d-flex flex-column align-items-center justify-content-center w-45px h-45px rounded bg-light-success flex-shrink-0">
                            <span class="fw-bold fs-5 text-success lh-1">{{ \Illuminate\Support\Carbon::parse($t['date_from'])->format('d') }}</span>
                            <span class="fs-9 text-success text-uppercase">{{ ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'][\Illuminate\Support\Carbon::parse($t['date_from'])->month - 1] }}</span>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-gray-800 text-truncate">{{ $t['title'] }}</div>
                            <div class="text-muted fs-8">
                                @if ($t['days_until'] <= 0) сегодня
                                @elseif ($t['days_until'] === 1) завтра
                                @else через {{ $t['days_until'] }} дн.
                                @endif
                                @if ($t['pax']) · {{ $t['pax'] }} чел. @endif
                            </div>
                        </div>
                        <span class="badge {{ $t['status_badge'] }} fs-8 flex-shrink-0">{{ $t['status_label'] }}</span>
                    </a>
                @empty
                    <div class="text-center py-10">
                        <i class="ki-outline ki-airplane fs-3x text-gray-300 mb-3 d-block"></i>
                        <span class="text-muted fs-7">Запланированных поездок нет</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent requests --}}
    <div class="col-xl-8">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <span class="fw-bold fs-4 text-gray-800">Последние заявки</span>
                <div class="card-toolbar">
                    <a href="{{ route('agency.requests.index') }}" class="btn btn-sm btn-light-primary">
                        Все заявки <i class="ki-outline ki-arrow-right fs-5 ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body pt-2">
                <div id="recent-table"></div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     QUICK VIEW MODAL
================================================================ --}}
<div class="modal fade" id="modal-quick-view" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">Детали заявки</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="mb-6">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <h4 class="fw-bold mb-0" id="qv-title">—</h4>
                        <span id="qv-status-badge"></span>
                    </div>
                    <div class="text-muted fs-6">
                        <i class="ki-outline ki-geolocation fs-6 me-1"></i>
                        <span id="qv-destination">—</span>
                    </div>
                </div>
                <div class="row g-4 mb-6">
                    <div class="col-md-4">
                        <div class="bg-light rounded p-4 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="ki-outline ki-people fs-2x text-primary mb-2 d-block"></i>
                            <div class="fw-bold fs-4" id="qv-pax">—</div>
                            <div class="text-muted fs-7">Туристов</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light rounded p-4 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="ki-outline ki-calendar-2 fs-2x text-info mb-2 d-block"></i>
                            <div class="fw-bold fs-7 lh-sm" id="qv-dates">—</div>
                            <div class="text-muted fs-7">Даты тура</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light rounded p-4 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="ki-outline ki-document fs-2x text-warning mb-2 d-block"></i>
                            <div class="fw-bold fs-4" id="qv-proposals">—</div>
                            <div class="text-muted fs-7">КП получено</div>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <div class="fw-bold text-gray-700 mb-2">Нужные услуги</div>
                    <div id="qv-services">—</div>
                </div>
                <div class="mb-4" id="qv-notes-section">
                    <div class="fw-bold text-gray-700 mb-1">Примечания</div>
                    <div class="text-gray-600 fs-6" id="qv-notes">—</div>
                </div>
                <div class="text-muted fs-7 border-top pt-4">
                    <span class="me-4">Создана: <span id="qv-created-at">—</span></span>
                    <span>Изменена: <span id="qv-updated-at">—</span></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Закрыть</button>
                <a id="qv-view-link" href="#" class="btn btn-primary">
                    <i class="ki-outline ki-arrow-right fs-4 me-1"></i> Полный просмотр
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* Shared helpers (serviceBadge, statusBadge, formatDate, formatDateTime,
   deadlineCell, escHtml) come from partials/js-helpers.blade.php. */

const RECENT = @json($recent);

/* ── Bookings trend chart ──────────────────────────────────────────────── */
(function renderChart() {
    const data = @json($chart);
    const el = document.getElementById('dash-chart');
    if (!el || typeof ApexCharts === 'undefined') return;

    const nf = new Intl.NumberFormat('ru-RU');
    new ApexCharts(el, {
        series: [
            { name: 'Расходы', type: 'area',   data: data.spend },
            { name: 'Брони',   type: 'column', data: data.bookings },
        ],
        chart:      { height: 300, toolbar: { show: false }, fontFamily: 'inherit', stacked: false },
        colors:     ['#17C653', '#1B84FF'],
        dataLabels: { enabled: false },
        stroke:     { curve: 'smooth', width: [2, 0] },
        fill:       { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
        plotOptions:{ bar: { columnWidth: '35%', borderRadius: 4 } },
        xaxis:      { categories: data.categories },
        yaxis: [
            { seriesName: 'Расходы', labels: { formatter: (v) => nf.format(Math.round(v)) } },
            { seriesName: 'Брони', opposite: true, labels: { formatter: (v) => Math.round(v) }, min: 0 },
        ],
        tooltip:    { y: { formatter: (v, { seriesIndex }) => seriesIndex === 0 ? nf.format(Math.round(v)) + ' {{ $sym }}' : Math.round(v) + ' шт' } },
        legend:     { show: true, position: 'top', horizontalAlign: 'left' },
        grid:       { borderColor: '#eff2f5', strokeDashArray: 4 },
    }).render();
})();

/* ── Recent requests table ─────────────────────────────────────────────── */
(function renderRecent() {
    const wrap = document.getElementById('recent-table');

    if (!RECENT.length) {
        wrap.innerHTML = `
            <div class="text-center py-10">
                <i class="ki-outline ki-document fs-3x text-gray-300 mb-3 d-block"></i>
                <span class="text-muted fs-7 d-block mb-3">Заявок пока нет.</span>
                <a href="{{ route('agency.requests.create') }}" class="btn btn-sm btn-light-success">Подать первую заявку</a>
            </div>`;
        return;
    }

    const rows = RECENT.map(r => {
        const dateRange = (r.travel_date_from || r.travel_date_to)
            ? `${formatDate(r.travel_date_from)} <i class="ki-outline ki-arrow-right fs-8 mx-1"></i> ${formatDate(r.travel_date_to)}`
            : '—';
        const terminal = ['booked', 'completed', 'cancelled'].includes(r.status);
        return `
            <tr>
                <td>
                    <a href="/agency/requests/${r.id}" class="fw-bold text-gray-800 text-hover-primary d-block">${escHtml(r.title ?? '—')}</a>
                    <div class="text-muted fs-7">${escHtml(r.destination ?? '')}</div>
                </td>
                <td><span class="fs-7">${dateRange}</span></td>
                <td>${deadlineCell(r.deadline_at, terminal)}</td>
                <td class="text-center">
                    ${r.proposals_count > 0
                        ? `<span class="badge badge-light-info">${r.proposals_count}</span>`
                        : '<span class="text-muted fs-8">—</span>'}
                </td>
                <td>${statusBadge(r)}</td>
                <td class="text-end">
                    <button type="button" onclick="quickView(${r.id})" class="btn btn-icon btn-sm btn-light-primary me-1" title="Быстрый просмотр">
                        <i class="ki-outline ki-eye fs-4"></i>
                    </button>
                    <a href="/agency/requests/${r.id}" class="btn btn-icon btn-sm btn-light-primary" title="Открыть">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`;
    }).join('');

    wrap.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-3">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-200px">Заявка</th>
                    <th class="min-w-150px">Период поездки</th>
                    <th class="min-w-110px">Срок ответа</th>
                    <th class="text-center">КП</th>
                    <th class="min-w-90px">Статус</th>
                    <th class="w-90px text-end"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>`;
})();

/* ── Quick view ────────────────────────────────────────────────────────── */
function quickView(id) {
    const r = RECENT.find(x => x.id === id);
    if (!r) return;

    document.getElementById('qv-title').textContent       = r.title ?? '—';
    document.getElementById('qv-status-badge').innerHTML  = statusBadge(r);
    document.getElementById('qv-destination').textContent = r.destination ?? '—';
    document.getElementById('qv-pax').textContent         = r.pax_count ?? '—';
    document.getElementById('qv-proposals').textContent   = r.proposals_count ?? 0;
    document.getElementById('qv-created-at').textContent  = formatDateTime(r.created_at);
    document.getElementById('qv-updated-at').textContent  = formatDateTime(r.updated_at);
    document.getElementById('qv-view-link').href          = '/agency/requests/' + r.id;

    document.getElementById('qv-dates').textContent = (r.travel_date_from || r.travel_date_to)
        ? formatDate(r.travel_date_from) + ' → ' + formatDate(r.travel_date_to)
        : '—';

    const services = Array.isArray(r.services_needed) ? r.services_needed : [];
    document.getElementById('qv-services').innerHTML = services.length
        ? services.map(s => serviceBadge(s, true)).join('')
        : '<span class="text-muted fs-7">Не указаны</span>';

    const notesSection = document.getElementById('qv-notes-section');
    if (r.notes) {
        document.getElementById('qv-notes').textContent = r.notes;
        notesSection.classList.remove('d-none');
    } else {
        notesSection.classList.add('d-none');
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-quick-view')).show();
}
</script>
@endpush
