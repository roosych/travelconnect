@extends('layouts.app')

@section('title', __('rfqs.title'))
@section('page-title', __('rfqs.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('rfqs.title') }}</li>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Quick-filter chips (status counts + due-soon) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="summary-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + service + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="rfq-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('rfqs.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <div class="d-flex align-items-center flex-nowrap gap-3">
                <select id="rfq-service-filter" class="form-select form-select-solid w-150px flex-shrink-0">
                    <option value="">{{ __('rfqs.index.all_services') }}</option>
                    <option value="accommodation">{{ __('common.services.accommodation') }}</option>
                    <option value="transport">{{ __('common.services.transport') }}</option>
                    <option value="guide">{{ __('common.services.guide') }}</option>
                    <option value="activity">{{ __('common.services.activity') }}</option>
                    <option value="other">{{ __('common.services.other') }}</option>
                </select>
                <select id="rfq-sort" class="form-select form-select-solid w-175px flex-shrink-0">
                    <option value="">{{ __('rfqs.index.sort.newest') }}</option>
                    <option value="created_asc">{{ __('rfqs.index.sort.oldest') }}</option>
                    <option value="deadline_asc">{{ __('rfqs.index.sort.deadline') }}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="rfqs-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Shared helpers (serviceMeta, statusBadge, formatDate, escHtml, deadlineCell)
// come from partials/js-helpers.blade.php.
const t  = @json(__('rfqs'));

let currentPage    = 1;
let currentSearch  = '';
let currentService = '';
let currentStatus  = '';
let currentDue     = '';
let currentSort    = '';

const TERMINAL = ['closed', 'cancelled'];

async function loadRfqs(page = 1) {
    currentPage = page;
    const container = document.getElementById('rfqs-table-container');
    container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

    const params = new URLSearchParams({ page, per_page: 20 });
    if (currentSearch)  params.set('search', currentSearch);
    if (currentService) params.set('service_type', currentService);
    if (currentStatus)  params.set('status', currentStatus);
    if (currentDue)     params.set('due', currentDue);
    if (currentSort)    params.set('sort', currentSort);

    try {
        const data = await api.get(`/rfqs?${params}`);
        renderChips(data.meta);
        renderTable(data.data ?? [], data.meta);
    } catch {
        container.innerHTML = `<div class="alert alert-danger">${t.index.load_error}</div>`;
    }
}

loadRfqs();

/* ── Filters ─────────────────────────────────────────────────────────── */
let _searchTimer;
document.getElementById('rfq-search').addEventListener('input', function () {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadRfqs(1); }, 300);
});
document.getElementById('rfq-service-filter').addEventListener('change', function () {
    currentService = this.value; loadRfqs(1);
});
document.getElementById('rfq-sort').addEventListener('change', function () {
    currentSort = this.value; loadRfqs(1);
});

function setFilter(status, due) {
    currentStatus = status || '';
    currentDue    = due || '';
    loadRfqs(1);
}

/* ── Chips ───────────────────────────────────────────────────────────── */
function renderChips(meta) {
    const counts = meta?.counts ?? {};
    const c = t.index.chips;
    const defs = [
        { status: '',          due: '',     label: c.all,       cls: 'secondary', n: meta?.total_all ?? 0, core: true },
        { status: 'sent',      due: '',     label: c.sent,      cls: 'primary',   n: counts.sent ?? 0,     core: true },
        { status: 'awaiting',  due: '',     label: c.awaiting,  cls: 'info',      n: counts.awaiting ?? 0, core: true },
        { status: '',          due: 'soon', label: c.hot,       cls: 'danger',    n: meta?.due_soon ?? 0,   core: true },
        { status: 'closed',    due: '',     label: c.closed,    cls: 'warning',   n: counts.closed ?? 0 },
        { status: 'cancelled', due: '',     label: c.cancelled, cls: 'dark',      n: counts.cancelled ?? 0 },
        { status: 'draft',     due: '',     label: c.draft,     cls: 'secondary', n: counts.draft ?? 0 },
    ];

    const chips = defs
        .filter(c => c.core || c.n > 0)
        .map(c => {
            const active = (c.status === currentStatus && c.due === currentDue);
            const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${c.status}','${c.due}')">${c.label}: ${c.n}</span>`;
        }).join('');

    document.getElementById('summary-chips').innerHTML = chips;
}

/* ── Table ───────────────────────────────────────────────────────────── */
function renderTable(rfqs, meta) {
    const container = document.getElementById('rfqs-table-container');

    if (!rfqs.length) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-send fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">${t.index.empty}</span>
            </div>`;
        return;
    }

    const rows = rfqs.map(r => {
        const svc        = serviceMeta(r.service_type);
        const suppliers  = r.suppliers ?? [];
        const offerCount = r.offer_count ?? 0;
        const isActive   = !TERMINAL.includes(r.status);

        const responseCell = suppliers.length
            ? (() => {
                const pct   = Math.round((offerCount / suppliers.length) * 100);
                const color = offerCount >= suppliers.length ? 'success'
                            : offerCount > 0                 ? 'warning' : 'muted';
                return `
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-${color} fs-6">${offerCount}</span>
                    <span class="text-muted fs-7">${t.index.of} ${suppliers.length}</span>
                </div>
                <div class="progress h-4px w-75px mt-1 bg-light">
                    <div class="progress-bar bg-${offerCount > 0 ? color : 'secondary'}" style="width:${pct}%"></div>
                </div>`;
            })()
            : `<span class="text-muted fs-7">—</span>`;

        const supplierNames = suppliers.length
            ? `<div class="text-gray-600 fs-8 mt-1">
                ${suppliers.slice(0, 2).map(s => escHtml(s.name)).join(', ')}
                ${suppliers.length > 2 ? `<span class="text-muted">+${suppliers.length - 2}</span>` : ''}
               </div>`
            : '';

        const actionBtns = `
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="/admin/rfqs/${r.id}"
                   class="btn btn-sm btn-icon btn-light btn-active-light-primary" title="${t.index.actions.open}">
                    <i class="ki-outline ki-eye fs-5"></i>
                </a>
                ${r.status === 'sent' || r.status === 'awaiting' ? `
                <button class="btn btn-sm btn-icon btn-light-warning btn-active-warning"
                        title="${t.index.actions.close}" onclick="closeRfq('${r.id}')">
                    <i class="ki-outline ki-lock fs-5"></i>
                </button>` : ''}
                ${isActive && r.status !== 'awaiting' ? `
                <button class="btn btn-sm btn-icon btn-light-danger btn-active-danger"
                        title="${t.index.actions.cancel}" onclick="cancelRfq('${r.id}')">
                    <i class="ki-outline ki-cross fs-5"></i>
                </button>` : ''}
            </div>`;

        return `
        <tr>
            <td class="w-100px pe-2">
                <a href="/admin/rfqs/${r.id}" class="fw-bold text-gray-800 text-hover-primary">${r.id}</a>
            </td>
            <td>
                <a href="/admin/requests/${r.request?.id}"
                   class="fw-semibold text-gray-800 text-hover-primary d-flex align-items-center gap-1 mb-2">
                   ${escHtml(r.request?.title ?? t.index.request_ref.replace(':id', r.request?.id ?? '—'))}
                   <i class="ki-outline ki-arrow-up-right fs-8 text-gray-400"></i>
                </a>
                <span class="badge ${svc.cls} fs-8">${svc.label}</span>
                ${supplierNames}
            </td>
            <td>${responseCell}</td>
            <td>${statusBadge(r)}</td>
            <td class="text-muted fs-7">${formatDate(r.created_at)}</td>
            <td>${deadlineCell(r.deadline_at, TERMINAL.includes(r.status))}</td>
            <td>${actionBtns}</td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-4">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-100px pe-2">${t.index.cols.id}</th>
                    <th class="min-w-220px">${t.index.cols.request_service}</th>
                    <th class="min-w-130px">${t.index.cols.responses}</th>
                    <th class="min-w-110px">${t.index.cols.status}</th>
                    <th class="min-w-110px">${t.index.cols.created}</th>
                    <th class="min-w-120px">${t.index.cols.deadline}</th>
                    <th class="text-end min-w-100px">${t.index.cols.actions}</th>
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

    const items = pages.map(p => {
        if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
        return `<li class="page-item ${p === cur ? 'active' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault();loadRfqs(${p})">${p}</a>
        </li>`;
    }).join('');

    return `
    <div class="d-flex justify-content-between align-items-center pt-4 px-1">
        <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadRfqs(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
            </li>
            ${items}
            <li class="page-item ${cur === last ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadRfqs(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
            </li>
        </ul>
    </div>`;
}

/* ── Actions ─────────────────────────────────────────────────────────── */
async function closeRfq(id) {
    const d = await api.patch(`/rfqs/${id}/close`);
    if (d.success) { showToast(t.toast.closed); loadRfqs(currentPage); }
    else           { showToast(d.message ?? t.toast.error, 'error'); }
}

async function cancelRfq(id) {
    const d = await api.patch(`/rfqs/${id}/cancel`);
    if (d.success) { showToast(t.toast.cancelled); loadRfqs(currentPage); }
    else           { showToast(d.message ?? t.toast.error, 'error'); }
}
</script>
@endpush
