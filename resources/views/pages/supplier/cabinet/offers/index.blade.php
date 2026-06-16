@extends('layouts.supplier')

@section('title', __('suppliers.cabinet.offers.title'))
@section('page-title', __('suppliers.cabinet.offers.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('suppliers.cabinet.offers.title') }}</li>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Quick-filter chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="offers-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('suppliers.cabinet.offers.loading') }}</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="offers-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('suppliers.cabinet.offers.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="offers-sort" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">{{ __('suppliers.cabinet.offers.sort.newest') }}</option>
                <option value="created_asc">{{ __('suppliers.cabinet.offers.sort.oldest') }}</option>
                <option value="price_desc">{{ __('suppliers.cabinet.offers.sort.price_desc') }}</option>
                <option value="price_asc">{{ __('suppliers.cabinet.offers.sort.price_asc') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="offers-table-container">
            <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* Shared helpers (escHtml, serviceMeta) come from partials/js-helpers.blade.php. */

const L = @json(__('suppliers.cabinet.offers'));

let currentPage   = 1;
let currentStatus = '';
let currentSort   = '';
let currentSearch = '';

function fmtMoney(amount, currency) {
    if (amount == null) return '—';
    return Number(amount).toLocaleString('ru-RU') + ' ' + (currency ?? '');
}

/* ── Data loading ──────────────────────────────────────────────────────── */

async function loadOffers(page = 1) {
    currentPage = page;
    const container = document.getElementById('offers-table-container');
    container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

    const params = new URLSearchParams({ page, per_page: 15 });
    if (currentStatus) params.set('status', currentStatus);
    if (currentSort)   params.set('sort', currentSort);
    if (currentSearch) params.set('search', currentSearch);

    try {
        const data = await api.get(`/offers?${params}`);
        renderChips(data.meta);
        renderTable(data.data ?? [], data.meta);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger mx-4">${L.load_error}</div>`;
    }
}

/* ── Quick-filter chips ────────────────────────────────────────────────── */

function renderChips(meta) {
    const counts = meta?.counts ?? {};
    const ch = L.chips;
    const defs = [
        { status: '',          label: ch.all,       cls: 'secondary', n: meta?.total_all ?? 0,    core: true },
        { status: 'received',  label: ch.received,  cls: 'warning',   n: counts.received ?? 0,    core: true },
        { status: 'reviewed',  label: ch.reviewed,  cls: 'info',      n: counts.reviewed ?? 0,    core: true },
        { status: 'selected',  label: ch.selected,  cls: 'primary',   n: counts.selected ?? 0,    core: true },
        { status: 'rejected',  label: ch.rejected,  cls: 'secondary', n: counts.rejected ?? 0 },
        { status: 'expired',   label: ch.expired,   cls: 'dark',      n: counts.expired ?? 0 },
        { status: 'withdrawn', label: ch.withdrawn, cls: 'secondary', n: counts.withdrawn ?? 0 },
    ];

    const chips = defs
        .filter(c => c.core || c.n > 0)
        .map(c => {
            const active = (c.status === currentStatus);
            const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${c.status}')">${c.label}: ${c.n}</span>`;
        }).join('');

    document.getElementById('offers-chips').innerHTML = chips;
}

function setFilter(status) {
    currentStatus = status || '';
    loadOffers(1);
}

/* ── Table ─────────────────────────────────────────────────────────────── */

function renderTable(offers, meta) {
    const container = document.getElementById('offers-table-container');

    if (!offers || offers.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-book-open fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">${L.empty}</span>
            </div>`;
        return;
    }

    const rows = offers.map(o => {
        const sm = serviceMeta(o.rfq_service_type);
        const reqId = o.rfq?.request?.id;
        const reqCell = reqId
            ? `<a href="/supplier/rfqs/request/${reqId}" class="fw-bold text-gray-800 text-hover-primary">${L.request_no.replace(':id', reqId)}</a>
               <div class="text-muted fs-8">${escHtml(o.rfq_title ?? '')}</div>`
            : `<span class="text-muted">${escHtml(o.rfq_title ?? '—')}</span>`;
        return `
            <tr>
                <td><a href="/supplier/offers/${o.id}" class="fw-bold text-gray-800 text-hover-primary">#${o.id}</a></td>
                <td>${reqCell}</td>
                <td>
                    <i class="${sm.icon} fs-6 text-gray-500 me-1"></i>
                    <span class="fs-7 text-gray-700">${sm.label}</span>
                </td>
                <td class="fw-bold text-gray-800">${fmtMoney(o.unit_price, o.currency)}</td>
                <td><span class="badge ${o.status_badge_class}">${escHtml(o.status_label)}</span></td>
                <td class="text-end">
                    <a href="/supplier/offers/${o.id}" class="btn btn-icon btn-sm btn-light-primary" title="${L.open}">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`;
    }).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-3">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-90px">${L.cols.offer}</th>
                    <th class="min-w-180px">${L.cols.request}</th>
                    <th class="min-w-120px">${L.cols.service}</th>
                    <th class="min-w-100px">${L.cols.price}</th>
                    <th class="min-w-110px">${L.cols.status}</th>
                    <th class="w-70px text-end"></th>
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
        if (p === 1 || p === last || (p >= cur - 2 && p <= cur + 2)) pages.push(p);
        else if (pages[pages.length - 1] !== '…') pages.push('…');
    }
    const items = pages.map(p => p === '…'
        ? `<li class="page-item disabled"><span class="page-link">…</span></li>`
        : `<li class="page-item ${p === cur ? 'active' : ''}">
               <a class="page-link" href="#" onclick="event.preventDefault();loadOffers(${p})">${p}</a>
           </li>`).join('');

    return `
    <div class="d-flex justify-content-between align-items-center pt-4 px-1">
        <div class="text-muted fs-7">${from}–${to} ${L.of} ${total}</div>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadOffers(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
            </li>
            ${items}
            <li class="page-item ${cur === last ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadOffers(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
            </li>
        </ul>
    </div>`;
}

/* ── Search & sort ─────────────────────────────────────────────────────── */

let _searchTimer;
document.getElementById('offers-search').addEventListener('input', function () {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadOffers(1); }, 300);
});

document.getElementById('offers-sort').addEventListener('change', function () {
    currentSort = this.value;
    loadOffers(1);
});

loadOffers();
</script>
@endpush
