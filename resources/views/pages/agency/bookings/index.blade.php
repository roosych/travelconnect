@extends('layouts.agency')

@section('title', 'Мои бронирования')
@section('page-title', 'Мои бронирования')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Бронирования</li>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Quick-filter chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="bookings-chips">
            <span class="text-muted fs-7 fw-semibold">Загрузка…</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="bookings-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Поиск по заявке / №…" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="bookings-sort" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">Сначала новые</option>
                <option value="created_asc">Сначала старые</option>
                <option value="travel_asc">Дата тура ↑</option>
                <option value="travel_desc">Дата тура ↓</option>
                <option value="price_desc">Сумма ↓</option>
                <option value="price_asc">Сумма ↑</option>
            </select>
        </div>
    </div>

    {{-- Table container --}}
    <div class="card-body pt-0">
        <div id="bookings-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
/* Shared helpers (statusBadge, escHtml, formatDate, formatCurrency)
   come from partials/js-helpers.blade.php (loaded by the agency layout). */

let currentPage   = 1;
let currentSearch = '';
let currentStatus = '';
let currentSort   = '';

/* ================================================================
   DATA LOADING
================================================================ */

async function loadBookings(page = 1) {
    currentPage = page;
    const container = document.getElementById('bookings-table-container');
    container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

    const params = new URLSearchParams({ page, per_page: 20 });
    if (currentSearch) params.set('search', currentSearch);
    if (currentStatus) params.set('status', currentStatus);
    if (currentSort)   params.set('sort', currentSort);

    try {
        const data = await api.get(`/bookings?${params}`);
        renderChips(data.meta);
        renderTable(data.data ?? [], data.meta);
    } catch {
        container.innerHTML = `<div class="alert alert-danger mx-4">Не удалось загрузить бронирования. Обновите страницу.</div>`;
    }
}

/* ================================================================
   QUICK-FILTER CHIPS
================================================================ */

function renderChips(meta) {
    const counts = meta?.counts ?? {};
    const defs = [
        { status: '',                label: 'Все',           cls: 'secondary', n: meta?.total_all ?? 0,        core: true },
        { status: 'confirmed',       label: 'Подтверждены',  cls: 'success',   n: counts.confirmed ?? 0,       core: true },
        { status: 'awaiting_payment',label: 'Ждут оплаты',   cls: 'warning',   n: counts.awaiting_payment ?? 0,core: true },
        { status: 'in_progress',     label: 'В пути',        cls: 'primary',   n: counts.in_progress ?? 0,     core: true },
        { status: 'paid',            label: 'Оплачены',      cls: 'success',   n: counts.paid ?? 0 },
        { status: 'rescheduled',     label: 'Даты изменены', cls: 'info',      n: counts.rescheduled ?? 0 },
        { status: 'completed',       label: 'Завершены',     cls: 'dark',      n: counts.completed ?? 0 },
        { status: 'cancelled',       label: 'Отменены',      cls: 'danger',    n: counts.cancelled ?? 0 },
    ];

    const chips = defs
        .filter(c => c.core || c.n > 0)
        .map(c => {
            const active = (c.status === currentStatus);
            const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${c.status}')">${c.label}: ${c.n}</span>`;
        }).join('');

    document.getElementById('bookings-chips').innerHTML = chips;
}

function setFilter(status) {
    currentStatus = status || '';
    loadBookings(1);
}

/* ================================================================
   SEARCH & SORT
================================================================ */

let _searchTimer;
document.getElementById('bookings-search').addEventListener('input', function () {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => {
        currentSearch = this.value.trim();
        loadBookings(1);
    }, 300);
});

document.getElementById('bookings-sort').addEventListener('change', function () {
    currentSort = this.value;
    loadBookings(1);
});

/* ================================================================
   TABLE RENDERING
================================================================ */

function renderTable(bookings, meta) {
    const container = document.getElementById('bookings-table-container');

    if (!bookings || bookings.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-calendar-check fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">Бронирований не найдено.</span>
            </div>`;
        return;
    }

    const rows = bookings.map(b => renderRow(b)).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-4">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-50px">#</th>
                    <th class="min-w-220px">Заявка / КП</th>
                    <th class="min-w-150px">Даты / Туристы</th>
                    <th class="min-w-110px">Сумма</th>
                    <th class="min-w-120px">Статус</th>
                    <th class="min-w-100px">Подтверждено</th>
                    <th class="text-end min-w-80px"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>
        ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;
}

function renderRow(b) {
    const requestTitle = b.proposal?.request?.title ?? b.proposal?.title ?? '—';
    // Числовой формат ДД.ММ.ГГГГ — 12.07.2026 — 22.07.2026
    const fmtNum = d => d ? new Date(d).toLocaleDateString('ru-RU') : '';
    const dateParts = [b.travel_date_from, b.travel_date_to].filter(Boolean).map(fmtNum);
    const dates = dateParts.length ? dateParts.join(' — ') : '—';

    return `
        <tr data-id="${b.id}">
            <td>
                <a href="/agency/bookings/${b.id}" class="fw-bold text-gray-800 text-hover-primary">#${b.id}</a>
            </td>
            <td>
                <a href="/agency/bookings/${b.id}" class="fw-semibold text-gray-800 text-hover-primary d-block">${escHtml(requestTitle)}</a>
                ${b.proposal?.title ? `<div class="text-muted fs-7 mt-1">${escHtml(b.proposal.title)}</div>` : ''}
            </td>
            <td>
                <div class="fw-semibold text-gray-800 fs-7">${dates}</div>
                ${b.pax_count ? `<div class="text-muted fs-8 mt-1"><i class="ki-outline ki-people fs-8 me-1"></i>${b.pax_count} чел.</div>` : ''}
            </td>
            <td class="fw-bold text-gray-900 fs-6">${formatCurrency(b.final_price, b.currency)}</td>
            <td>${statusBadge(b)}</td>
            <td class="text-muted fs-7">${formatDate(b.confirmed_at ?? b.created_at)}</td>
            <td class="text-end">
                <a href="/agency/bookings/${b.id}" class="btn btn-sm btn-light btn-active-light-primary">
                    Подробнее <i class="ki-outline ki-arrow-right fs-6 ms-1"></i>
                </a>
            </td>
        </tr>`;
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
            <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${p})">${p}</a>
        </li>`;
    }).join('');

    return `
    <div class="d-flex justify-content-between align-items-center pt-4 px-1">
        <div class="text-muted fs-7">${from}–${to} из ${total}</div>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${cur - 1})">
                    <i class="ki-outline ki-arrow-left fs-7"></i>
                </a>
            </li>
            ${items}
            <li class="page-item ${cur === last ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${cur + 1})">
                    <i class="ki-outline ki-arrow-right fs-7"></i>
                </a>
            </li>
        </ul>
    </div>`;
}

/* ================================================================
   BOOT
================================================================ */
loadBookings();
</script>
@endpush
