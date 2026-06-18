@extends('layouts.app')

@section('title', __('requests.tour_requests'))
@section('page-title', __('requests.tour_requests'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('requests.tour_requests') }}</li>
@endsection

@section('content')

{{-- Main card --}}
<div class="card card-flush">

    {{-- Quick-filter chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="requests-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Card header: search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text"
                       id="requests-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('requests.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="requests-sort" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">{{ __('requests.index.sort.newest') }}</option>
                <option value="created_asc">{{ __('requests.index.sort.oldest') }}</option>
                <option value="deadline_asc">{{ __('requests.index.sort.deadline') }}</option>
                <option value="pax_desc">{{ __('requests.index.sort.pax') }}</option>
            </select>
        </div>
    </div>

    {{-- Table container --}}
    <div class="card-body pt-0">
        <div id="requests-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
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
                <h2 class="fw-bold">{{ __('requests.qv.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">

                {{-- Title + status + agency (под заголовком) --}}
                <div class="mb-6">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <h4 class="fw-bold mb-0" id="qv-title">—</h4>
                        <span id="qv-status-badge"></span>
                    </div>
                    <div class="fs-6" id="qv-agency">—</div>
                </div>

                {{-- Период · Гостей · Срок ответа — плитки как на странице заявки --}}
                <div class="row g-4 mb-6">
                    <div class="col-sm-6 col-xl-4">
                        <div class="d-flex align-items-start gap-3">
                            <span class="w-40px h-40px rounded-2 bg-light-info d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="ki-outline ki-calendar fs-4 text-info"></i>
                            </span>
                            <div>
                                <div class="text-muted fs-8">{{ __('requests.qv.period') }}</div>
                                <div class="fw-semibold text-gray-800 fs-7 mt-1" id="qv-dates">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="d-flex align-items-start gap-3">
                            <span class="w-40px h-40px rounded-2 bg-light-warning d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="ki-outline ki-people fs-4 text-warning"></i>
                            </span>
                            <div>
                                <div class="text-muted fs-8">{{ __('requests.qv.guests') }}</div>
                                <div class="fw-semibold text-gray-800 fs-7 mt-1" id="qv-pax">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <div class="d-flex align-items-start gap-3">
                            <span class="w-40px h-40px rounded-2 bg-light-danger d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="ki-outline ki-time fs-4 text-danger"></i>
                            </span>
                            <div>
                                <div class="text-muted fs-8">{{ __('requests.qv.deadline') }}</div>
                                <div class="fw-semibold text-gray-800 fs-7 mt-1" id="qv-deadline">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Маршрут по странам (сегменты) --}}
                <div class="mb-6">
                    <div class="fw-bold text-gray-700 mb-3">{{ __('requests.qv.route') }}</div>
                    <div id="qv-route">—</div>
                </div>

                {{-- Notes --}}
                <div class="mb-6" id="qv-notes-section">
                    <div class="fw-bold text-gray-700 mb-1">{{ __('requests.qv.notes') }}</div>
                    <div class="text-gray-600 fs-6" id="qv-notes">—</div>
                </div>

                {{-- Attachments --}}
                <div class="mb-6 d-none" id="qv-attachments-section">
                    <div class="fw-bold text-gray-700 mb-2">
                        <i class="ki-outline ki-paper-clip fs-6 me-1"></i>{{ __('requests.qv.attachments') }}
                    </div>
                    <div id="qv-attachments" class="d-flex flex-wrap gap-3"></div>
                </div>

                {{-- Stats: suppliers / offers --}}
                <div class="row g-4 mb-4">
                    <div class="col-6">
                        <div class="border border-dashed rounded p-4 text-center">
                            <div class="fw-bold fs-2 text-primary" id="qv-suppliers-count">0</div>
                            <div class="text-muted fs-7">{{ __('requests.qv.suppliers_notified') }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border border-dashed rounded p-4 text-center">
                            <div class="fw-bold fs-2 text-success" id="qv-offers-count">0</div>
                            <div class="text-muted fs-7">{{ __('requests.qv.offers_received') }}</div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('requests.qv.close') }}</button>
                <a id="qv-view-link" href="#" class="btn btn-primary">
                    <i class="ki-outline ki-arrow-right fs-4 me-1"></i> {{ __('requests.qv.full_view') }}
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ================================================================
   CONSTANTS & HELPERS
================================================================ */

// Shared helpers (serviceBadge, statusBadge, formatDate, formatDateTime,
// escHtml, countryName, deadlineCell) come from partials/js-helpers.blade.php.

// Часовой пояс смотрящего оператора — срок ответа показываем в нём (UTC в БД).
const USER_TZ = @json($userTimezone);

// Локализация: словарь страницы + общий, локаль-зависимая плюрализация.
const t  = @json(__('requests'));
const tc = @json(__('common'));
const _PR = new Intl.PluralRules(@json(app()->getLocale()));
function plural(n, forms) { return forms[_PR.select(n)] ?? forms.other ?? forms.one ?? ''; }

let currentPage     = 1;
let currentSearch   = '';
let currentStatus   = '';
let currentDue      = '';
let currentSort     = '';
let currentPageData = [];
const _rfqCache     = {};

/* ================================================================
   DATA LOADING
================================================================ */

async function loadRequests(page = 1) {
    currentPage = page;
    const container = document.getElementById('requests-table-container');
    container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

    const params = new URLSearchParams({ page, per_page: 20 });
    if (currentSearch) params.set('search', currentSearch);
    if (currentStatus) params.set('status', currentStatus);
    if (currentDue)    params.set('due', currentDue);
    if (currentSort)   params.set('sort', currentSort);

    // clear RFQ sub-row cache on each full reload
    Object.keys(_rfqCache).forEach(k => delete _rfqCache[k]);

    try {
        const data = await api.get(`/requests?${params}`);
        currentPageData = data.data ?? [];
        renderChips(data.meta);
        renderTable(currentPageData, data.meta);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger mx-4">${t.index.load_error}</div>`;
    }
}

/* ================================================================
   QUICK-FILTER CHIPS (status counts + due-soon)
================================================================ */

function renderChips(meta) {
    const counts = meta?.counts ?? {};
    const c = t.index.chips;
    const defs = [
        { status: '',          due: '',     label: c.all,        cls: 'secondary', n: meta?.total_all ?? 0,    core: true },
        { status: 'submitted', due: '',     label: c.new,        cls: 'primary',   n: counts.submitted ?? 0,   core: true },
        { status: 'processing',due: '',     label: c.processing, cls: 'info',      n: counts.processing ?? 0,  core: true },
        { status: '',          due: 'soon', label: c.hot,        cls: 'danger',    n: meta?.due_soon ?? 0,     core: true },
        { status: 'booked',    due: '',     label: c.booked,     cls: 'success',   n: counts.booked ?? 0 },
        { status: 'completed', due: '',     label: c.completed,  cls: 'dark',      n: counts.completed ?? 0 },
        { status: 'cancelled', due: '',     label: c.cancelled,  cls: 'danger',    n: counts.cancelled ?? 0 },
        { status: 'draft',     due: '',     label: c.draft,      cls: 'secondary', n: counts.draft ?? 0 },
    ];

    const chips = defs
        .filter(c => c.core || c.n > 0)
        .map(c => {
            const active = (c.status === currentStatus && c.due === currentDue);
            const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${c.status}','${c.due}')">${c.label}: ${c.n}</span>`;
        }).join('');

    document.getElementById('requests-chips').innerHTML = chips;
}

function setFilter(status, due) {
    currentStatus = status || '';
    currentDue    = due || '';
    loadRequests(1);
}

/* ================================================================
   SEARCH & FILTER
================================================================ */

let _searchTimer;
document.getElementById('requests-search').addEventListener('input', function () {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => {
        currentSearch = this.value.trim();
        loadRequests(1);
    }, 300);
});

document.getElementById('requests-sort').addEventListener('change', function () {
    currentSort = this.value;
    loadRequests(1);
});

/* ================================================================
   TABLE RENDERING
================================================================ */

function renderTable(requests, meta) {
    const container = document.getElementById('requests-table-container');

    if (!requests || requests.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-document fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">${t.index.empty}</span>
            </div>`;
        return;
    }

    const rows = requests.map(r => renderRow(r)).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-3">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-100px pe-2">#</th>
                    <th class="min-w-220px">${t.index.cols.request_route}</th>
                    <th class="min-w-130px">${t.index.cols.services}</th>
                    <th class="min-w-120px">${t.index.cols.agency}</th>
                    <th class="min-w-60px text-center">${t.index.cols.pax}</th>
                    <th class="min-w-160px">${t.index.cols.tour_dates}</th>
                    <th class="min-w-175px">${t.index.cols.deadline}</th>
                    <th class="min-w-90px">${t.index.cols.status}</th>
                    <th class="w-90px text-end"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>
        ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;
}

function renderPagination(meta) {
    const { current_page: cur, last_page: last, per_page, total } = meta;
    const from = (cur - 1) * per_page + 1;
    const to   = Math.min(cur * per_page, total);

    const pages = [];
    for (let p = 1; p <= last; p++) {
        if (p === 1 || p === last || (p >= cur - 2 && p <= cur + 2)) {
            pages.push(p);
        } else if (pages[pages.length - 1] !== '…') {
            pages.push('…');
        }
    }

    const items = pages.map(p => {
        if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        return `<li class="page-item ${p === cur ? 'active' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault();loadRequests(${p})">${p}</a>
        </li>`;
    }).join('');

    return `
    <div class="d-flex justify-content-between align-items-center pt-4 px-1">
        <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadRequests(${cur - 1})">
                    <i class="ki-outline ki-arrow-left fs-7"></i>
                </a>
            </li>
            ${items}
            <li class="page-item ${cur === last ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadRequests(${cur + 1})">
                    <i class="ki-outline ki-arrow-right fs-7"></i>
                </a>
            </li>
        </ul>
    </div>`;
}

/* Маршрут странами с флагами и направлениями (сегментная модель).
   Фолбэк на legacy-строку destination, если сегменты ещё не загружены. */
function renderRoute(r) {
    const legs = Array.isArray(r.legs) ? r.legs : [];
    if (!legs.length) {
        return r.destination ? `<div class="text-muted fs-7 mt-1">${escHtml(r.destination)}</div>` : '';
    }
    const lines = legs.map(leg => {
        const cities = Array.isArray(leg.destinations) && leg.destinations.length
            ? `<div class="text-muted fs-8" style="margin-left:26px">${leg.destinations.map(escHtml).join(' · ')}</div>`
            : '';
        return `
            <div>
                <div class="d-flex align-items-center gap-2">
                    <img src="${leg.country_flag}" alt=""
                         style="width:18px;height:13px;object-fit:cover;border-radius:2px;flex-shrink:0"
                         onerror="this.style.display='none'">
                    <span class="text-gray-800 fw-semibold fs-7">${escHtml(leg.country_name)}</span>
                </div>
                ${cities}
            </div>`;
    }).join('');
    return `<div class="d-flex flex-column gap-1 mt-1">${lines}</div>`;
}

/* Объединение типов услуг по всем сегментам (с сохранением порядка появления). */
function renderServices(r) {
    const legs = Array.isArray(r.legs) ? r.legs : [];
    let types = [];
    if (legs.length) {
        const seen = new Set();
        legs.forEach(l => (l.services || []).forEach(s => {
            if (!seen.has(s.type)) { seen.add(s.type); types.push(s.type); }
        }));
    } else {
        types = Array.isArray(r.services_needed) ? r.services_needed : [];
    }
    return types.length ? types.map(s => serviceBadge(s, false)).join('') : '';
}

function countryWord(n) {
    return plural(n, t.plural.countries);
}

/* Подробный маршрут для quick-view: сегменты с датами, направлениями и услугами+требованиями. */
function quickViewRoute(r) {
    const legs = Array.isArray(r.legs) ? r.legs : [];
    if (!legs.length) {
        const s = Array.isArray(r.services_needed) ? r.services_needed : [];
        return s.length
            ? `<div class="text-muted fs-7 mb-2">${escHtml(r.destination ?? '')}</div>` + s.map(x => serviceBadge(x, true)).join('')
            : `<span class="text-muted fs-7">${t.qv.no_segments}</span>`;
    }
    return legs.map((leg, i) => {
        const dates = (leg.date_from || leg.date_to)
            ? `${formatDate(leg.date_from)} — ${formatDate(leg.date_to)}`
            : t.show.info.dates_none;
        const dests = (leg.destinations || []).length
            ? leg.destinations.map((d, idx) => `<span class="badge badge-light-primary fs-8 me-1 mb-1">${idx + 1}. ${escHtml(d)}</span>`).join('')
            : `<span class="text-muted fs-8">${t.show.info.whole_country}</span>`;
        const svcs = (leg.services || []).length
            ? leg.services.map(s => {
                const sum = s.summary ? ` <span class="text-muted fs-8">(${escHtml(s.summary)})</span>` : '';
                return `<span class="d-inline-flex align-items-center gap-1 me-3 mb-1"><span class="badge badge-light-info fs-8">${escHtml(s.label)}</span>${sum}</span>`;
              }).join('')
            : '<span class="text-muted fs-8">—</span>';
        const flag = leg.country_flag
            ? `<img src="${leg.country_flag}" style="width:20px;height:14px;object-fit:cover;border-radius:2px" onerror="this.remove()">`
            : '';
        return `
            <div class="d-flex gap-3 ${i < legs.length - 1 ? 'pb-3 mb-3 border-bottom border-gray-200' : ''}">
                <span class="badge badge-circle badge-primary flex-shrink-0" style="width:24px;height:24px">${i + 1}</span>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        ${flag}
                        <span class="fw-bold text-gray-900 fs-6">${escHtml(leg.country_name)}</span>
                        <span class="text-muted fs-8"><i class="ki-outline ki-calendar fs-8 me-1"></i>${escHtml(dates)}</span>
                    </div>
                    <div class="mb-1">${dests}</div>
                    <div>${svcs}</div>
                </div>
            </div>`;
    }).join('');
}

function renderRow(r) {
    /* ---- route + services (segment model) ---- */
    const services       = renderServices(r);
    const countriesCount = Array.isArray(r.legs) ? r.legs.length : 0;
    const multiBadge     = countriesCount > 1
        ? `<span class="badge badge-light-info fs-8 ms-2">${countriesCount} ${countryWord(countriesCount)}</span>`
        : '';

    /* ---- date range ---- */
    const dateRange = (r.travel_date_from || r.travel_date_to)
        ? `${formatDate(r.travel_date_from)} <i class="ki-outline ki-arrow-right fs-8 mx-1"></i> ${formatDate(r.travel_date_to)}`
        : '—';

    /* статистику (поставщики/предложения) показываем в квик-модалке и на странице заявки. */
    const hasRfqs = (r.rfqs_count ?? 0) > 0;

    /* ---- main row ---- */
    const mainRow = `
        <tr data-id="${r.id}">
            <td class="w-100px pe-2">
                <a href="/admin/requests/${r.id}" class="text-gray-800 text-hover-primary fw-bold">${r.id}</a>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <a href="/admin/requests/${r.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(r.title ?? '—')}</a>
                    ${multiBadge}
                </div>
                ${renderRoute(r)}
            </td>
            <td>
                ${services
                    ? `<div class="d-flex flex-column align-items-start gap-1">${services}</div>`
                    : '<span class="text-muted fs-8">—</span>'}
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
            <td>
                <span class="fs-7">${dateRange}</span>
            </td>
            <td>${deadlineCell(r.deadline_at, ['booked','completed','cancelled'].includes(r.status), USER_TZ)}</td>
            <td>${statusBadge(r)}</td>
            <td class="text-end">
                ${hasRfqs ? `
                <button type="button"
                        data-toggle-rfqs="${r.id}"
                        onclick="toggleRfqs('${r.id}')"
                        class="btn btn-icon btn-sm btn-light me-1"
                        title="${t.index.rfq_sub.title}">
                    <i class="ki-outline ki-arrow-down fs-5"></i>
                </button>` : ''}
                <button type="button"
                        onclick="quickView('${r.id}')"
                        class="btn btn-icon btn-sm btn-light-primary"
                        title="${t.show.offers.quick_view}">
                    <i class="ki-outline ki-eye fs-4"></i>
                </button>
            </td>
        </tr>`;

    /* ---- RFQ sub-row (collapsed by default) ---- */
    const subRow = hasRfqs ? `
        <tr id="rfq-sub-${r.id}" class="d-none">
            <td colspan="9" class="pt-0 pb-4 px-5">
                <div class="bg-light rounded px-5 py-4">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">${t.index.rfq_sub.title}</div>
                    <div class="rfq-sub-content d-flex flex-wrap gap-2">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                </div>
            </td>
        </tr>` : '';

    return mainRow + subRow;
}

/* ================================================================
   RFQ SUB-ROW: TOGGLE + LAZY LOAD
================================================================ */

async function toggleRfqs(id) {
    const subRow = document.getElementById(`rfq-sub-${id}`);
    const btn    = document.querySelector(`[data-toggle-rfqs="${id}"]`);
    const icon   = btn.querySelector('i');

    if (subRow.classList.contains('d-none')) {
        subRow.classList.remove('d-none');
        icon.className = 'ki-outline ki-arrow-up fs-5';

        if (!_rfqCache[id]) {
            try {
                const res = await api.get(`/requests/${id}/rfqs?per_page=50`);
                _rfqCache[id] = res.data ?? [];
            } catch {
                subRow.querySelector('.rfq-sub-content').innerHTML =
                    `<span class="text-danger fs-7">${t.index.rfq_sub.load_error}</span>`;
                return;
            }
        }

        renderRfqSubRow(id, subRow);
    } else {
        subRow.classList.add('d-none');
        icon.className = 'ki-outline ki-arrow-down fs-5';
    }
}

function renderRfqSubRow(id, subRow) {
    const rfqs    = _rfqCache[id] ?? [];
    const content = subRow.querySelector('.rfq-sub-content');

    if (rfqs.length === 0) {
        content.innerHTML = `<span class="text-muted fs-7">${t.index.rfq_sub.empty}</span>`;
        return;
    }

    const rows = rfqs.map(rfq => {
        const sm = serviceMeta(rfq.service_type);
        const supCount   = rfq.suppliers?.length ?? 0;
        const offerCount = rfq.offer_count ?? rfq.offers?.length ?? 0;
        const deadline   = rfq.deadline_at ? formatDate(rfq.deadline_at) : '—';

        return `
            <tr>
                <td class="py-2 pe-6 text-nowrap">
                    <i class="${sm.icon} fs-6 text-gray-500 me-1"></i>
                    <span class="fw-semibold fs-7 text-gray-700">${sm.label}</span>
                </td>
                <td class="py-2 pe-6">
                    <span class="badge ${rfq.status_badge_class} fs-8">${rfq.status_label}</span>
                </td>
                <td class="py-2 pe-6 text-nowrap">
                    <i class="ki-outline ki-people fs-8 text-muted me-1"></i>
                    <span class="text-muted fs-7">${t.index.rfq_sub.sup_count.replace(':n', supCount)}</span>
                </td>
                <td class="py-2 pe-6 text-nowrap">
                    <i class="ki-outline ki-document fs-8 text-muted me-1"></i>
                    <span class="text-muted fs-7">${offerCount > 0 ? t.index.rfq_sub.offer_count.replace(':n', offerCount) : '—'}</span>
                </td>
                <td class="py-2 text-nowrap">
                    <i class="ki-outline ki-calendar fs-8 text-muted me-1"></i>
                    <span class="text-muted fs-7">${deadline}</span>
                </td>
            </tr>`;
    }).join('');

    content.innerHTML = `
        <table class="table table-sm table-borderless mb-0">
            <thead>
                <tr class="text-gray-400 fw-bold fs-8 text-uppercase">
                    <th class="pb-1 pe-6">${t.index.rfq_sub.service}</th>
                    <th class="pb-1 pe-6">${t.index.rfq_sub.status}</th>
                    <th class="pb-1 pe-6">${t.index.rfq_sub.suppliers}</th>
                    <th class="pb-1 pe-6">${t.index.rfq_sub.offers}</th>
                    <th class="pb-1">${t.index.rfq_sub.deadline}</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
}

/* ================================================================
   QUICK VIEW MODAL
================================================================ */

// Срок ответа: момент UTC → дата+время в поясе смотрящего + метка смещения, числовой формат.
function qvDeadline(iso) {
    if (!iso) return '—';
    const dt = new Date(iso).toLocaleString('ru-RU', {
        timeZone: USER_TZ, day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
    let off = '';
    try {
        off = new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
            .formatToParts(new Date(iso)).find(p => p.type === 'timeZoneName')?.value || '';
    } catch (e) { /* пояс не распознан */ }
    return dt + (off ? ` (${off})` : '');
}

async function quickView(id) {
    const r = currentPageData.find(x => x.id === id);
    if (!r) return;

    document.getElementById('qv-title').textContent       = r.title ?? '—';
    document.getElementById('qv-status-badge').innerHTML  = statusBadge(r);
    const qvAg = r.agency;
    document.getElementById('qv-agency').innerHTML = qvAg?.id
        ? `<a href="/admin/agencies/${qvAg.id}" target="_blank" rel="noopener"
              class="d-inline-flex align-items-center gap-2 text-gray-800 text-hover-primary fw-semibold text-decoration-none">
               ${qvAg.country ? `<img src="/flags/${String(qvAg.country).toLowerCase()}.svg" alt="" style="width:20px;height:14px;object-fit:cover;border-radius:2px;flex-shrink:0" onerror="this.remove()">` : ''}
               <span>${escHtml(qvAg.company_name ?? qvAg.name ?? '—')}</span>
           </a>`
        : '<span class="text-muted">—</span>';
    document.getElementById('qv-pax').textContent         = r.pax_count != null ? t.qv.pax_unit.replace(':n', r.pax_count) : '—';
    document.getElementById('qv-deadline').textContent    = qvDeadline(r.deadline_at);
    document.getElementById('qv-suppliers-count').textContent = r.suppliers_notified_count ?? 0;
    document.getElementById('qv-offers-count').textContent    = r.offers_count ?? 0;
    document.getElementById('qv-view-link').href = '/admin/requests/' + r.id;

    /* dates (период) */
    document.getElementById('qv-dates').textContent = (r.travel_date_from || r.travel_date_to)
        ? formatDate(r.travel_date_from) + ' — ' + formatDate(r.travel_date_to)
        : '—';

    /* route (segments) */
    document.getElementById('qv-route').innerHTML = quickViewRoute(r);

    /* notes */
    const notesSection = document.getElementById('qv-notes-section');
    if (r.notes) {
        document.getElementById('qv-notes').textContent = r.notes;
        notesSection.classList.remove('d-none');
    } else {
        notesSection.classList.add('d-none');
    }

    /* attachments — reset while loading */
    const attSection = document.getElementById('qv-attachments-section');
    const attEl      = document.getElementById('qv-attachments');
    attSection.classList.add('d-none');
    attEl.innerHTML  = '<span class="spinner-border spinner-border-sm text-primary"></span>';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-quick-view')).show();

    /* load attachments async */
    try {
        const res  = await api.get(`/requests/${id}/attachments`);
        const atts = res.data ?? [];
        if (atts.length) {
            attEl.innerHTML = atts.map(a => {
                const ext = (a.filename ?? '').split('.').pop().toLowerCase();
                let iconColor = 'text-primary';
                if (ext === 'pdf')                            iconColor = 'text-danger';
                else if (['xls','xlsx'].includes(ext))        iconColor = 'text-success';
                else if (['jpg','jpeg','png'].includes(ext))  iconColor = 'text-warning';
                const name = (a.filename ?? '').length > 26
                    ? a.filename.substring(0, 23) + '…' : (a.filename ?? 'file');
                return `<a href="${escHtml(a.url)}" target="_blank" rel="noopener"
                    class="d-inline-flex align-items-center gap-2 border border-dashed rounded px-3 py-2 bg-white text-gray-700 text-hover-primary text-decoration-none">
                    <i class="ki-outline ki-file fs-2 ${iconColor}"></i>
                    <div class="lh-sm">
                        <div class="fw-semibold fs-7">${escHtml(name)}</div>
                        <div class="text-muted fs-8">${escHtml(a.human_size ?? '')}</div>
                    </div>
                </a>`;
            }).join('');
            attSection.classList.remove('d-none');
        }
    } catch { /* вложения вспомогательные */ }
}

/* ================================================================
   ACTIONS: SUBMIT / CANCEL
================================================================ */

async function submitRequest(id) {
    if (!confirm(t.show.confirm.submit_request)) return;
    try {
        const data = await api.patch(`/requests/${id}/submit`);
        if (data?.data?.id ?? data?.id) {
            showToast(t.show.toast.request_submitted);
            await loadRequests(currentPage);
        } else {
            showToast(data?.message ?? t.error_generic, 'error');
        }
    } catch (err) {
        showToast(tc.unexpected_error, 'error');
    }
}

async function cancelRequest(id) {
    if (!confirm(t.show.confirm.cancel_request)) return;
    try {
        const data = await api.patch(`/requests/${id}/cancel`);
        if (data?.data?.id ?? data?.id) {
            showToast(t.show.toast.request_cancelled);
            await loadRequests(currentPage);
        } else {
            showToast(data?.message ?? t.error_generic, 'error');
        }
    } catch (err) {
        showToast(tc.unexpected_error, 'error');
    }
}

/* ================================================================
   BOOT
================================================================ */
loadRequests();
</script>
@endpush
