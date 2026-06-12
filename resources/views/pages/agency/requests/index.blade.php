@extends('layouts.agency')

@section('title', 'Мои заявки')
@section('page-title', 'Мои заявки')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Мои заявки</li>
@endsection

@section('toolbar-actions')
    <a href="{{ route('agency.requests.create') }}" class="btn btn-success btn-sm">
        <i class="ki-outline ki-plus fs-4 me-1"></i>Новая заявка
    </a>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Quick-filter chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="requests-chips">
            <span class="text-muted fs-7 fw-semibold">Загрузка…</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text"
                       id="requests-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Поиск заявок…" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="requests-sort" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">Сначала новые</option>
                <option value="created_asc">Сначала старые</option>
                <option value="deadline_asc">Ближайший дедлайн</option>
                <option value="pax_desc">Больше гостей</option>
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

@endsection

@push('scripts')
<script>
/* Shared helpers (statusBadge, serviceBadge, deadlineCell, formatDate, escHtml)
   come from partials/js-helpers.blade.php (loaded by the agency layout). */

// Часовой пояс смотрящего — срок ответа показываем в нём (UTC хранится в БД).
const USER_TZ = @json($userTimezone);

let currentPage   = 1;
let currentSearch = '';
let currentStatus = '';
let currentDue    = '';
let currentSort   = '';

/* ================================================================
   DATA LOADING
================================================================ */

async function loadRequests(page = 1) {
    currentPage = page;
    const container = document.getElementById('requests-table-container');
    container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

    const params = new URLSearchParams({ page, per_page: 15 });
    if (currentSearch) params.set('search', currentSearch);
    if (currentStatus) params.set('status', currentStatus);
    if (currentDue)    params.set('due', currentDue);
    if (currentSort)   params.set('sort', currentSort);

    try {
        const data = await api.get(`/requests?${params}`);
        renderChips(data.meta);
        renderTable(data.data ?? [], data.meta);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger mx-4">Не удалось загрузить заявки. Обновите страницу.</div>`;
    }
}

/* ================================================================
   QUICK-FILTER CHIPS
================================================================ */

function renderChips(meta) {
    const counts = meta?.counts ?? {};
    const defs = [
        { status: '',          due: '',     label: 'Все',             cls: 'secondary', n: meta?.total_all ?? 0,    core: true },
        { status: 'submitted', due: '',     label: 'Поданы',          cls: 'primary',   n: counts.submitted ?? 0,   core: true },
        { status: 'processing',due: '',     label: 'На рассмотрении', cls: 'info',      n: counts.processing ?? 0,  core: true },
        { status: '',          due: 'soon', label: '🔥 Горящие',      cls: 'danger',    n: meta?.due_soon ?? 0,     core: true },
        { status: 'booked',    due: '',     label: 'Забронированы',   cls: 'success',   n: counts.booked ?? 0 },
        { status: 'completed', due: '',     label: 'Завершены',       cls: 'dark',      n: counts.completed ?? 0 },
        { status: 'cancelled', due: '',     label: 'Отменены',        cls: 'danger',    n: counts.cancelled ?? 0 },
        { status: 'draft',     due: '',     label: 'Черновики',       cls: 'secondary', n: counts.draft ?? 0 },
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
   SEARCH & SORT
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
                <span class="text-muted fs-6 d-block mb-3">Заявок не найдено.</span>
                <a href="{{ route('agency.requests.create') }}" class="btn btn-sm btn-light-success">
                    <i class="ki-outline ki-plus fs-5 me-1"></i>Подать первую заявку
                </a>
            </div>`;
        return;
    }

    const rows = requests.map(r => renderRow(r)).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-4">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-250px">Заявка и маршрут</th>
                    <th class="min-w-140px">Услуги</th>
                    <th class="min-w-160px">Период поездки</th>
                    <th class="min-w-60px text-center">Гостей</th>
                    <th class="min-w-110px">Срок ответа</th>
                    <th class="min-w-110px text-center">Предложений</th>
                    <th class="min-w-90px">Статус</th>
                    <th class="w-90px text-end"></th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>
        ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;
}

/* Маршрут странами с флагами и направлениями (сегментная модель).
   Фолбэк на legacy-строку destination, если сегменты ещё не загружены. */
function renderRoute(r) {
    const legs = Array.isArray(r.legs) ? r.legs : [];
    if (!legs.length) {
        return r.destination
            ? `<div class="text-muted fs-7 mt-1">${escHtml(r.destination)}</div>`
            : '';
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
    return types.length
        ? types.map(s => serviceBadge(s, false)).join('')
        : '';
}

function renderRow(r) {
    // Числовой формат ДД.ММ.ГГГГ — 12.07.2026 — 22.07.2026
    const fmtNum = d => d ? new Date(d).toLocaleDateString('ru-RU') : '';
    const dateParts = [r.travel_date_from, r.travel_date_to].filter(Boolean).map(fmtNum);
    const dateRange = dateParts.length ? dateParts.join(' — ') : '—';

    // Кол-во стран маршрута — подсказка о мультистрановой заявке.
    const countriesCount = Array.isArray(r.legs) ? r.legs.length : 0;
    const countryWord = (n) => {
        const m10 = n % 10, m100 = n % 100;
        if (m10 === 1 && m100 !== 11) return 'страна';
        if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return 'страны';
        return 'стран';
    };
    const multiBadge = countriesCount > 1
        ? `<span class="badge badge-light-info fs-8 ms-2">${countriesCount} ${countryWord(countriesCount)}</span>`
        : '';

    // Mute the deadline for terminal statuses where a response is no longer expected.
    const terminal = ['booked', 'completed', 'cancelled'].includes(r.status);

    // КП, видимые агентству (sent/accepted/rejected) — совпадает с детальной страницей.
    const proposals = r.received_proposals_count ?? 0;
    const proposalsHtml = proposals > 0
        ? `<span class="badge badge-light-success fs-7">${proposals}</span>`
        : '<span class="text-muted fs-8">—</span>';

    const services = renderServices(r);

    return `
        <tr data-id="${r.id}">
            <td>
                <div class="d-flex align-items-center">
                    <a href="/agency/requests/${r.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(r.title ?? '—')}</a>
                    ${multiBadge}
                </div>
                ${renderRoute(r)}
            </td>
            <td>
                ${services
                    ? `<div class="d-flex flex-column align-items-start gap-1">${services}</div>`
                    : '<span class="text-muted fs-8">—</span>'}
            </td>
            <td><span class="fs-7">${dateRange}</span></td>
            <td class="text-center">
                ${r.pax_count != null
                    ? `<span class="fw-bold fs-5 text-gray-800">${r.pax_count}</span>`
                    : '<span class="text-muted">—</span>'}
            </td>
            <td>${deadlineCell(r.deadline_at, terminal, USER_TZ)}</td>
            <td class="text-center">${proposalsHtml}</td>
            <td>${statusBadge(r)}</td>
            <td class="text-end">
                <a href="/agency/requests/${r.id}" class="btn btn-icon btn-sm btn-light-primary" title="Открыть">
                    <i class="ki-outline ki-arrow-right fs-4"></i>
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
            <a class="page-link" href="#" onclick="event.preventDefault();loadRequests(${p})">${p}</a>
        </li>`;
    }).join('');

    return `
    <div class="d-flex justify-content-between align-items-center pt-4 px-1">
        <div class="text-muted fs-7">${from}–${to} из ${total}</div>
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

/* ================================================================
   BOOT
================================================================ */
loadRequests();
</script>
@endpush
