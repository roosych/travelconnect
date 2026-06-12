@extends('layouts.supplier')

@section('title', __('suppliers.cabinet.rfqs.title'))
@section('page-title', __('suppliers.cabinet.rfqs.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('suppliers.cabinet.rfqs.title') }}</li>
@endsection

@section('content')

<div class="card card-flush">

    {{-- Quick-filter chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="rfqs-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('suppliers.cabinet.rfqs.loading') }}</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="search-input"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('suppliers.cabinet.rfqs.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="sort-select" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">{{ __('suppliers.cabinet.rfqs.sort.newest') }}</option>
                <option value="created_asc">{{ __('suppliers.cabinet.rfqs.sort.oldest') }}</option>
                <option value="deadline_asc">{{ __('suppliers.cabinet.rfqs.sort.deadline') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="rfqs-loading" class="text-center py-10">
            <span class="spinner-border text-primary"></span>
        </div>

        <div id="rfqs-empty" class="text-center py-20 d-none">
            <i class="ki-outline ki-document fs-4x text-gray-300 mb-4 d-block"></i>
            <div class="text-gray-600 fw-semibold fs-5">{{ __('suppliers.cabinet.rfqs.empty_title') }}</div>
            <div class="text-muted fs-7 mt-2">{{ __('suppliers.cabinet.rfqs.empty_hint') }}</div>
        </div>

        <div id="rfqs-groups" class="d-flex flex-column gap-4"></div>

        <div id="pagination-wrap" class="d-flex justify-content-end mt-6"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const supplierId   = {{ $supplier?->id ?? 'null' }};
let currentPage    = 1;
let currentStatus  = '';
let currentDue     = '';
let currentSort    = '';
let currentSearch  = '';

const SERVICE_LABELS = window.SERVICE_LABELS;

// ── i18n bag ───────────────────────────────────────────────────────────────────
const L = @json(__('suppliers.cabinet.rfqs'));
const LOC = window.APP_LOCALE || 'ru';

// Множественное число: ru — три формы, en — две (1/много), az — единственная.
function plForm(n, forms) {
    if (LOC === 'ru') {
        const m10 = n % 10, m100 = n % 100;
        if (m10 === 1 && m100 !== 11) return forms.one;
        if (m10 >= 2 && m10 <= 4 && (m100 < 10 || m100 >= 20)) return forms.few;
        return forms.many;
    }
    if (LOC === 'en') return n === 1 ? forms.one : forms.many;
    return forms.one;
}

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
// Дата/время — единый числовой формат ДД.ММ.ГГГГ на всех локалях (см. js-helpers).
function fmtDate(iso)     { return window.formatDate(iso); }
// Дедлайн — момент (datetime) в поясе смотрящего + GMT-метка: «12.07.2026 13:45 (GMT+4)».
function fmtDeadline(iso) { return window.formatDateTimeTz(iso); }

// ── RFQ list ──────────────────────────────────────────────────────────────────

async function loadRfqs(page = 1) {
    currentPage = page;
    document.getElementById('rfqs-loading').classList.remove('d-none');
    document.getElementById('rfqs-groups').innerHTML = '';
    document.getElementById('rfqs-empty').classList.add('d-none');
    document.getElementById('pagination-wrap').innerHTML = '';

    const params = new URLSearchParams({ page, per_page: 50 });
    if (currentStatus) params.set('status', currentStatus);
    if (currentDue)    params.set('due', currentDue);
    if (currentSort)   params.set('sort', currentSort);
    if (currentSearch) params.set('search', currentSearch);

    try {
        const data  = await api.get(`/rfqs?${params}`);
        const items = data?.data ?? [];
        const meta  = data?.meta ?? {};

        document.getElementById('rfqs-loading').classList.add('d-none');
        renderChips(meta);

        if (!items.length) {
            document.getElementById('rfqs-empty').classList.remove('d-none');
            return;
        }

        renderGroups(items);
        renderPagination(meta);
    } catch (err) {
        document.getElementById('rfqs-loading').classList.add('d-none');
        showToast(err?.message ?? L.load_error, 'error');
    }
}

// ── Quick-filter chips ────────────────────────────────────────────────────────

function renderChips(meta) {
    const counts = meta?.counts ?? {};
    // meta.open — уникальные заявки в статусах sent|awaiting (см. API: нельзя
    // складывать distinct-счётчики статусов). Фолбэк — сумма для старого ответа.
    const open   = meta?.open ?? ((counts.sent ?? 0) + (counts.awaiting ?? 0));
    const defs = [
        { status: '',          due: '',     label: L.chips.all,       cls: 'secondary', n: meta?.total_all ?? 0,    core: true },
        { status: 'open',      due: '',     label: L.chips.open,      cls: 'primary',   n: open,                    core: true },
        { status: '',          due: 'soon', label: L.chips.hot,       cls: 'danger',    n: meta?.due_soon ?? 0,     core: true },
        { status: 'closed',    due: '',     label: L.chips.closed,    cls: 'dark',      n: counts.closed ?? 0 },
        { status: 'cancelled', due: '',     label: L.chips.cancelled, cls: 'danger',    n: counts.cancelled ?? 0 },
    ];

    const chips = defs
        .filter(c => c.core || c.n > 0)
        .map(c => {
            const active = (c.status === currentStatus && c.due === currentDue);
            const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${c.status}','${c.due}')">${c.label}: ${c.n}</span>`;
        }).join('');

    document.getElementById('rfqs-chips').innerHTML = chips;
}

function setFilter(status, due) {
    currentStatus = status || '';
    currentDue    = due || '';
    loadRfqs(1);
}

// ── Grouping & rendering ──────────────────────────────────────────────────────
// Заявка — единица работы: одна карточка = заявка, услуги внутри чипами со статусом.
// Детали (цена/отзыв по каждой услуге) живут на странице деталей заявки.

function renderGroups(items) {
    const groups = new Map();
    items.forEach(r => {
        const reqId = r.request?.id ?? 0;
        if (!groups.has(reqId)) groups.set(reqId, { request: r.request, rfqs: [] });
        groups.get(reqId).rfqs.push(r);
    });

    const container = document.getElementById('rfqs-groups');
    container.innerHTML = '';
    groups.forEach(({ request, rfqs }) => {
        container.insertAdjacentHTML('beforeend', renderGroup(request, rfqs));
    });
}

// Услуга = название (текстом) + бейдж статуса. Тип в самом бейдже не пишем;
// текст/цвет бейджа — из статусов: оффер (Подано/На рассмотрении/Выбрано…),
// иначе статус RFQ (Открыт/Завершён/Отменён).
function svcChip(r) {
    const myPivot = (r.suppliers ?? []).find(s => s.id === supplierId);
    const types   = (myPivot?.pivot_service_types?.length ? myPivot.pivot_service_types : [r.service_type]).filter(Boolean);
    const name    = types.map(t => esc(SERVICE_LABELS[t] ?? t)).join(' + ');
    const myOffer = r.my_active_offer;
    const label   = myOffer ? myOffer.status_label       : r.status_label;
    const cls     = myOffer ? myOffer.status_badge_class : r.status_badge_class;
    return `<span class="d-inline-flex align-items-center gap-2 border border-gray-300 rounded ps-3 pe-2 py-1">
        <span class="fw-semibold text-gray-800 fs-7">${name}</span>
        <span class="badge ${cls} fs-8">${esc(label)}</span>
    </span>`;
}

function renderGroup(request, rfqs) {
    const requestId    = request?.id;
    const serviceCount = rfqs.length;
    const pax          = request?.pax_count;
    // Поставщик одностранный → все его RFQ по заявке в одном сегменте; страну не показываем.
    const seg          = rfqs.find(r => r.segment)?.segment ?? {};
    const segDates     = (seg.date_from || seg.date_to) ? `${fmtDate(seg.date_from)} — ${fmtDate(seg.date_to)}` : '';
    const cities       = (seg.destinations ?? []).filter(Boolean);
    const hasComposable = rfqs.some(r => ['sent', 'awaiting'].includes(r.status) && !r.my_active_offer);
    const deadline     = rfqs.filter(r => r.deadline_at).map(r => r.deadline_at).sort()[0] ?? null;

    let deadlineCls = 'badge-light-info';
    if (deadline) {
        const days = Math.ceil((new Date(deadline) - Date.now()) / 86400000);
        if (days <= 1)      deadlineCls = 'badge-light-danger';
        else if (days <= 3) deadlineCls = 'badge-light-warning';
    }

    const meta = [
        cities.length ? cities.map(esc).join(', ') : '',
        pax ? `${pax} ${plForm(pax, L.tourists)}` : '',
        `${serviceCount} ${plForm(serviceCount, L.services)}`,
    ].filter(Boolean).join(' · ');

    const chips    = rfqs.map(svcChip).join('');
    const btnLabel = hasComposable ? L.card.respond : L.card.open;
    const btnCls   = hasComposable ? 'btn-primary'  : 'btn-light';

    return `
    <a href="/supplier/rfqs/request/${requestId}"
       class="card card-flush border border-gray-200 text-decoration-none shadow-hover">
        <div class="card-body d-flex align-items-center justify-content-between gap-4 flex-wrap py-5 px-6">
            <div class="d-flex flex-column gap-2 flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-gray-900 fs-5">
                        ${segDates ? `<i class="ki-outline ki-calendar fs-5 me-1 text-gray-500"></i>${esc(segDates)}` : esc(L.request)}
                    </span>
                    ${deadline ? `<span class="badge ${deadlineCls} fs-8">
                        <i class="ki-outline ki-timer fs-8 me-1"></i>${fmtDeadline(deadline)}</span>` : ''}
                </div>
                ${meta ? `<div class="text-muted fs-7">${meta}</div>` : ''}
                <div class="d-flex flex-wrap gap-2 mt-1">${chips}</div>
            </div>
            <span class="btn btn-sm ${btnCls} flex-shrink-0">
                ${esc(btnLabel)}<i class="ki-outline ki-arrow-right fs-6 ms-1"></i>
            </span>
        </div>
    </a>`;
}

// ── Pagination ────────────────────────────────────────────────────────────────

function renderPagination(meta) {
    const wrap = document.getElementById('pagination-wrap');
    const { current_page: cur, last_page: last, per_page, total } = meta;
    if (!last || last <= 1) { wrap.innerHTML = ''; return; }

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
               <a class="page-link" href="#" onclick="event.preventDefault();loadRfqs(${p})">${p}</a>
           </li>`).join('');

    wrap.innerHTML = `
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="text-muted fs-7">${from}–${to} ${L.of} ${total}</div>
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

// ── Filters ───────────────────────────────────────────────────────────────────

document.getElementById('sort-select').addEventListener('change', function () {
    currentSort = this.value;
    loadRfqs(1);
});

let searchTimer;
document.getElementById('search-input').addEventListener('input', function () {
    clearTimeout(searchTimer);
    currentSearch = this.value.trim();
    searchTimer   = setTimeout(() => loadRfqs(1), 350);
});

loadRfqs();
</script>
@endpush
