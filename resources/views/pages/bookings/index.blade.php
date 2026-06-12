@extends('layouts.app')

@section('title', __('bookings.title'))
@section('page-title', __('bookings.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('bookings.title') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    {{-- Quick-filter chips (status counts) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="bookings-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="booking-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('bookings.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="booking-sort" class="form-select form-select-solid w-190px flex-shrink-0">
                <option value="">{{ __('bookings.index.sort.newest') }}</option>
                <option value="created_asc">{{ __('bookings.index.sort.oldest') }}</option>
                <option value="travel_asc">{{ __('bookings.index.sort.travel_asc') }}</option>
                <option value="travel_desc">{{ __('bookings.index.sort.travel_desc') }}</option>
                <option value="price_desc">{{ __('bookings.index.sort.price_desc') }}</option>
                <option value="price_asc">{{ __('bookings.index.sort.price_asc') }}</option>
            </select>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="bookings-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

{{-- Quick-view drawer (with margin breakdown from the cost snapshot) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="booking-drawer" style="width:480px;max-width:95vw">
    <div class="offcanvas-header border-bottom px-7 py-5">
        <div class="min-w-0">
            <h5 class="offcanvas-title fw-bold mb-1" id="bk-drawer-title">{{ __('bookings.drawer.title_ph') }}</h5>
            <div id="bk-drawer-meta" class="text-muted fs-7"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-7 py-6" id="bk-drawer-body"></div>
    <div class="offcanvas-footer border-top px-7 py-4 d-flex gap-2" id="bk-drawer-footer"></div>
</div>

@endsection

@push('scripts')
<script>
    // Shared helpers (statusBadge, formatDate, formatCurrency, escHtml) come from
    // partials/js-helpers.blade.php.
    const t  = @json(__('bookings'));
    const tc = @json(__('common'));
    let allBookings   = [];
    let currentPage   = 1;
    let currentSearch = '';
    let currentStatus = '';
    let currentSort   = '';

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
            allBookings = data.data ?? [];
            renderChips(data.meta);
            renderTable(allBookings, data.meta);
        } catch (err) {
            container.innerHTML = `<div class="alert alert-danger">${t.index.load_error}</div>`;
        }
    }

    loadBookings();

    let _searchTimer;
    document.getElementById('booking-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadBookings(1); }, 300);
    });
    document.getElementById('booking-sort').addEventListener('change', function () {
        currentSort = this.value; loadBookings(1);
    });

    function setFilter(status) {
        currentStatus = status || '';
        loadBookings(1);
    }

    function renderChips(meta) {
        const counts = meta?.counts ?? {};
        const so = t.status.operator;
        const defs = [
            { status: '',                label: t.index.all,        cls: 'secondary', n: meta?.total_all ?? 0,        core: true },
            { status: 'confirmed',       label: so.confirmed,       cls: 'success',   n: counts.confirmed ?? 0,       core: true },
            { status: 'awaiting_payment',label: so.awaiting_payment,cls: 'warning',   n: counts.awaiting_payment ?? 0,core: true },
            { status: 'paid',            label: so.paid,            cls: 'success',   n: counts.paid ?? 0,            core: true },
            { status: 'in_progress',     label: so.in_progress,     cls: 'primary',   n: counts.in_progress ?? 0,     core: true },
            { status: 'completed',       label: so.completed,       cls: 'dark',      n: counts.completed ?? 0 },
            { status: 'rescheduled',     label: so.rescheduled,     cls: 'info',      n: counts.rescheduled ?? 0 },
            { status: 'cancelled',       label: so.cancelled,       cls: 'danger',    n: counts.cancelled ?? 0 },
        ];
        const chips = defs
            .filter(c => c.core || c.n > 0)
            .map(c => {
                const active = c.status === currentStatus;
                const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
                return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                              onclick="setFilter('${c.status}')">${c.label}: ${c.n}</span>`;
            }).join('');
        document.getElementById('bookings-chips').innerHTML = chips;
    }

    function renderTable(bookings, meta) {
        const container = document.getElementById('bookings-table-container');

        if (!bookings.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-calendar-check fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.index.empty}</span>
                </div>`;
            return;
        }

        const rows = bookings.map(b => {
            const req    = b.proposal?.request;
            const agency = b.agency;

            const titleCell = `
                <a href="/admin/bookings/${b.id}" class="fw-semibold text-gray-800 text-hover-primary">
                    ${escHtml(b.proposal?.title ?? req?.title ?? '—')}
                </a>
                ${req?.destination ? `<div class="text-muted fs-7">${escHtml(req.destination)}</div>` : ''}`;

            const agencyCell = agency?.id
                ? `<a href="/admin/agencies/${agency.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(agency.name ?? agency.company_name ?? '—')}</a>`
                : '<span class="text-muted">—</span>';

            const hasAgencyCur = b.agency_currency && b.agency_currency !== 'AZN';
            const priceCell = `
                <span class="fw-bold text-gray-900">${formatCurrency(b.final_price_azn ?? b.final_price)}</span>
                ${hasAgencyCur ? `<div class="text-muted fs-8 fw-normal">${formatCurrency(b.agency_final_price, b.agency_currency)}</div>` : ''}`;

            const travelCell = b.travel_date_from
                ? `<span class="text-gray-700 fs-7">${formatDate(b.travel_date_from)}${b.travel_date_to ? ' – ' + formatDate(b.travel_date_to) : ''}</span>`
                : '<span class="text-muted">—</span>';

            return `
            <tr>
                <td><a href="/admin/bookings/${b.id}" class="fw-bold text-gray-800 text-hover-primary">#${b.id}</a></td>
                <td>${titleCell}</td>
                <td>${agencyCell}</td>
                <td>${priceCell}</td>
                <td>${travelCell}</td>
                <td>${statusBadge(b)}</td>
                <td class="text-muted fs-7">${formatDate(b.confirmed_at ?? b.created_at)}</td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="btn btn-sm btn-icon btn-light" title="${t.index.quick_view}"
                                onclick="openBookingDrawer(${b.id})">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </button>
                        <a href="/admin/bookings/${b.id}" class="btn btn-sm btn-icon btn-light" title="${t.index.open}">
                            <i class="ki-outline ki-arrow-right fs-4"></i>
                        </a>
                    </div>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-50px">#</th>
                        <th class="min-w-200px">${t.index.cols.tour}</th>
                        <th class="min-w-130px">${t.index.cols.agency}</th>
                        <th class="min-w-110px">${t.index.cols.amount}</th>
                        <th class="min-w-130px">${t.index.cols.travel_dates}</th>
                        <th class="min-w-110px">${t.index.cols.status}</th>
                        <th class="min-w-100px">${t.index.cols.confirmed}</th>
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
                <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
                </li>
                ${items}
                <li class="page-item ${cur === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadBookings(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
                </li>
            </ul>
        </div>`;
    }

    // ── Quick-view drawer ─────────────────────────────────────────────────────

    const SERVICE_LABELS = window.SERVICE_LABELS;

    async function openBookingDrawer(id) {
        const body   = document.getElementById('bk-drawer-body');
        const title  = document.getElementById('bk-drawer-title');
        const meta    = document.getElementById('bk-drawer-meta');
        const footer = document.getElementById('bk-drawer-footer');

        title.textContent = t.drawer.ref.replace(':id', id);
        meta.innerHTML    = '';
        footer.innerHTML  = '';
        body.innerHTML    = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('booking-drawer')).show();

        try {
            const data = await api.get(`/bookings/${id}`);
            renderBookingDrawer(data.data ?? data);
        } catch (err) {
            body.innerHTML = `<div class="alert alert-danger fs-7">${t.drawer.load_error}</div>`;
        }
    }

    function renderBookingDrawer(b) {
        const req    = b.proposal?.request;
        const agency = b.agency;

        document.getElementById('bk-drawer-title').innerHTML = `${t.drawer.ref.replace(':id', b.id)} ${statusBadge(b)}`;
        const metaParts = [
            b.proposal?.title ? escHtml(b.proposal.title) : (req?.title ? escHtml(req.title) : null),
            agency?.name ? escHtml(agency.name) : null,
        ].filter(Boolean);
        document.getElementById('bk-drawer-meta').innerHTML = metaParts.join(' · ');

        const hasAgencyCur = b.agency_currency && b.agency_currency !== 'AZN';

        // — Price + key facts —
        const priceHtml = `
        <div class="d-flex align-items-center justify-content-between bg-light rounded p-4 mb-5">
            <div>
                <div class="fw-bold fs-3 text-gray-900">${formatCurrency(b.final_price_azn ?? b.final_price)}</div>
                <div class="text-muted fs-8">${t.drawer.total}${hasAgencyCur ? ' ' + t.drawer.for_agency.replace(':amount', formatCurrency(b.agency_final_price, b.agency_currency)) : ''}</div>
            </div>
            <i class="ki-outline ki-handcart fs-2x text-gray-300"></i>
        </div>`;

        const facts = [];
        if (b.travel_date_from) {
            facts.push([t.drawer.travel_dates, `${formatDate(b.travel_date_from)}${b.travel_date_to ? ' → ' + formatDate(b.travel_date_to) : ''}`]);
        }
        if (b.pax_count) facts.push([t.drawer.pax, t.drawer.pax_unit.replace(':n', b.pax_count)]);
        facts.push([t.drawer.confirmed, formatDate(b.confirmed_at ?? b.created_at)]);
        if (req?.destination) facts.push([t.drawer.destination, escHtml(req.destination)]);

        const factsHtml = `
        <div class="row g-3 mb-5">
            ${facts.map(([k, v]) => `
            <div class="col-6">
                <div class="text-muted fs-8 text-uppercase fw-bold mb-1">${k}</div>
                <div class="fw-semibold text-gray-800 fs-7">${v}</div>
            </div>`).join('')}
        </div>`;

        // — Margin breakdown (cost snapshot) —
        let marginHtml = '';
        if (b.cost_total_azn != null && b.cost_total_azn > 0) {
            const pct = b.margin_pct != null ? ` <span class="fs-8 fw-normal">(${b.margin_pct}%)</span>` : '';
            marginHtml = `
            <div class="separator separator-dashed my-5"></div>
            <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${t.drawer.margin_title}</div>
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="bg-light-primary rounded p-3 text-center">
                        <div class="text-muted fs-8 mb-1">${t.drawer.net}</div>
                        <div class="fw-bolder fs-6 text-primary">${formatCurrency(b.cost_total_azn)}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light-success rounded p-3 text-center">
                        <div class="text-muted fs-8 mb-1">${t.drawer.margin}</div>
                        <div class="fw-bolder fs-6 text-success">+${formatCurrency(b.margin_azn)}${pct}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light-dark rounded p-3 text-center">
                        <div class="text-muted fs-8 mb-1">${t.drawer.sell}</div>
                        <div class="fw-bolder fs-6 text-gray-900">${formatCurrency(b.sell_total_azn)}</div>
                    </div>
                </div>
            </div>`;

            const items = Array.isArray(b.items) ? b.items : [];
            if (items.length) {
                marginHtml += items.map(it => {
                    const label = SERVICE_LABELS[it.service_type] ?? it.service_type ?? '';
                    const m = parseFloat(it.margin_azn ?? 0);
                    return `
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom gap-2">
                        <div class="min-w-0">
                            <span class="text-gray-800 fs-7 fw-semibold">${escHtml(it.name ?? label)}</span>
                            ${it.supplier_name ? `<div class="text-muted fs-8">${escHtml(it.supplier_name)}${label ? ' · ' + label : ''}</div>` : (label ? `<div class="text-muted fs-8">${label}</div>` : '')}
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold text-gray-900 fs-7">${formatCurrency(it.sell_amount_azn)}</div>
                            <div class="text-muted fs-8">${formatCurrency(it.net_amount_azn)} ${t.drawer.net_short}${m > 0 ? ` · <span class="text-success">+${formatCurrency(m)}</span>` : ''}</div>
                        </div>
                    </div>`;
                }).join('');
            }
        }

        // — Notes —
        const notesHtml = b.notes
            ? `<div class="separator separator-dashed my-5"></div>
               <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.drawer.notes}</div>
               <div class="text-gray-700 fs-7 bg-light rounded p-3">${escHtml(b.notes)}</div>`
            : '';

        document.getElementById('bk-drawer-body').innerHTML = priceHtml + factsHtml + marginHtml + notesHtml;

        // — Footer —
        let footerBtns = `<a href="/admin/bookings/${b.id}" class="btn btn-light-primary btn-sm flex-fill">
                            <i class="ki-outline ki-arrow-right fs-5 me-1"></i>${t.drawer.open_full}
                          </a>`;
        if (b.proposal?.id) {
            footerBtns += `<a href="/admin/proposals/${b.proposal.id}" class="btn btn-light btn-sm flex-fill">
                            <i class="ki-outline ki-document fs-5 me-1"></i>${t.drawer.proposal_ref.replace(':id', b.proposal.id)}
                           </a>`;
        }
        document.getElementById('bk-drawer-footer').innerHTML = footerBtns;
    }
</script>
@endpush
