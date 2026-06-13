@extends('layouts.app')

@section('title', __('offers.title'))
@section('page-title', __('offers.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('offers.breadcrumb') }}</li>
@endsection

@section('content')

{{-- Подтверждение отклонения --}}
<div class="modal fade" id="modal-confirm-reject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('offers.confirm.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-5 fs-6 text-gray-700" id="confirm-reject-body">
                {{ __('offers.confirm.reject_q') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject-ok">{{ __('offers.confirm.reject') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Drawer быстрого просмотра оффера --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offer-drawer" style="width:520px;">
    <div class="offcanvas-header border-bottom px-7 py-5">
        <h5 class="offcanvas-title fw-bold fs-4" id="offer-drawer-title">{{ __('offers.offer') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-7 py-6" id="offer-drawer-body">
        <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
    </div>
    <div class="offcanvas-footer border-top px-7 py-4 d-flex gap-3 flex-wrap" id="offer-drawer-footer"></div>
</div>

<div class="card card-flush">
    {{-- Quick-filter chips (status counts + expiring) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="offers-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + service + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="offer-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('offers.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="offer-service-filter" class="form-select form-select-solid w-150px flex-shrink-0">
                <option value="">{{ __('offers.index.all_services') }}</option>
                <option value="accommodation">{{ __('common.services.accommodation') }}</option>
                <option value="transport">{{ __('common.services.transport') }}</option>
                <option value="guide">{{ __('common.services.guide') }}</option>
                <option value="activity">{{ __('common.services.activity') }}</option>
                <option value="other">{{ __('common.services.other') }}</option>
            </select>
            <select id="offer-sort" class="form-select form-select-solid w-175px flex-shrink-0">
                <option value="">{{ __('offers.index.sort.newest') }}</option>
                <option value="created_asc">{{ __('offers.index.sort.oldest') }}</option>
                <option value="price_asc">{{ __('offers.index.sort.price_asc') }}</option>
                <option value="price_desc">{{ __('offers.index.sort.price_desc') }}</option>
                <option value="valid_until_asc">{{ __('offers.index.sort.expiring') }}</option>
            </select>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="offers-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
    // Shared helpers (statusBadge, formatDate, formatCurrency, escHtml) come from
    // partials/js-helpers.blade.php. SERVICE_META stays local (used by the drawer).
    const USER_TZ = @json($userTimezone);
    const t  = @json(__('offers'));
    const tc = @json(__('common'));
    // Срок/действителен до — момент: дата+время в поясе смотрящего + GMT-метка.
    const fmtDT = (d) => window.formatDateTimeTz(d, USER_TZ);
    let pendingRejectId = null;
    let currentPage     = 1;
    let currentSearch   = '';
    let currentService  = '';
    let currentStatus   = '';
    let currentExpiring = '';
    let currentSort     = '';

    async function loadOffers(page = 1) {
        currentPage = page;
        const container = document.getElementById('offers-table-container');
        container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        const params = new URLSearchParams({ page, per_page: 20 });
        if (currentSearch)   params.set('search', currentSearch);
        if (currentService)  params.set('service_type', currentService);
        if (currentStatus)   params.set('status', currentStatus);
        if (currentExpiring) params.set('expiring', currentExpiring);
        if (currentSort)     params.set('sort', currentSort);

        try {
            const data = await api.get(`/offers?${params}`);
            renderChips(data.meta);
            renderTable(data.data ?? [], data.meta);
        } catch (err) {
            container.innerHTML = `<div class="alert alert-danger">${t.index.load_error}</div>`;
        }
    }

    loadOffers();

    let _searchTimer;
    document.getElementById('offer-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadOffers(1); }, 300);
    });
    document.getElementById('offer-service-filter').addEventListener('change', function () {
        currentService = this.value; loadOffers(1);
    });
    document.getElementById('offer-sort').addEventListener('change', function () {
        currentSort = this.value; loadOffers(1);
    });

    document.getElementById('btn-confirm-reject-ok').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('modal-confirm-reject')).hide();
        if (pendingRejectId !== null) doRejectOffer(pendingRejectId);
        pendingRejectId = null;
    });

    function setFilter(status, expiring) {
        currentStatus   = status || '';
        currentExpiring = expiring || '';
        loadOffers(1);
    }

    function renderChips(meta) {
        const counts = meta?.counts ?? {};
        const ch = t.index.chips;
        const defs = [
            { status: '',          exp: '',     label: ch.all,       cls: 'secondary', n: meta?.total_all ?? 0, core: true },
            { status: 'received',  exp: '',     label: ch.received,  cls: 'primary',   n: counts.received ?? 0, core: true },
            { status: '',          exp: 'soon', label: ch.expiring,  cls: 'danger',    n: meta?.expiring ?? 0,  core: true },
            { status: 'reviewed',  exp: '',     label: ch.reviewed,  cls: 'info',      n: counts.reviewed ?? 0 },
            { status: 'selected',  exp: '',     label: ch.selected,  cls: 'success',   n: counts.selected ?? 0 },
            { status: 'rejected',  exp: '',     label: ch.rejected,  cls: 'dark',      n: counts.rejected ?? 0 },
            { status: 'expired',   exp: '',     label: ch.expired,   cls: 'warning',   n: counts.expired ?? 0 },
            { status: 'withdrawn', exp: '',     label: ch.withdrawn, cls: 'secondary', n: counts.withdrawn ?? 0 },
        ];
        const chips = defs
            .filter(c => c.core || c.n > 0)
            .map(c => {
                const active = (c.status === currentStatus && c.exp === currentExpiring);
                const cls = active ? `badge-${c.cls}` : `badge-light-${c.cls}`;
                return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                              onclick="setFilter('${c.status}','${c.exp}')">${c.label}: ${c.n}</span>`;
            }).join('');
        document.getElementById('offers-chips').innerHTML = chips;
    }

    function renderTable(offers, meta) {
        const container = document.getElementById('offers-table-container');

        if (!offers.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-tag fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.index.empty}</span>
                </div>`;
            return;
        }

        const rows = offers.map(o => {
            const price = o.unit_price ?? o.total_price ?? o.price;

            // Заявка на тур
            const req = o.rfq?.request;
            const reqTitle = req
                ? escHtml(req.title ?? req.destination ?? t.drawer.request_ref.replace(':id', req.id))
                : '—';

            const reqLink = req
                ? `<a href="/admin/requests/${req.id}"
                       class="fw-semibold text-gray-800 text-hover-primary d-flex align-items-center gap-1 mb-2">
                       ${reqTitle}
                       <i class="ki-outline ki-arrow-up-right fs-8 text-gray-400"></i>
                   </a>`
                : `<span class="text-muted">—</span>`;

            // Тип услуги из запроса поставщику
            const svcType = o.rfq?.service_type;
            const svcMeta = svcType ? (SERVICE_META[svcType] ?? { label: svcType, color: 'secondary', icon: 'ki-abstract-26' }) : null;
            const svcBadge = svcMeta
                ? `<span class="badge badge-light-${svcMeta.color} fs-8">${svcMeta.label}</span>`
                : '';

            // Страна сегмента, по которому запрос (RFQ).
            const rfqCountry = o.rfq?.country_code
                ? `<div class="d-flex align-items-center gap-1 mt-1">
                       ${o.rfq.country_flag ? `<img src="${o.rfq.country_flag}" style="width:16px;height:12px;object-fit:cover;border-radius:2px" onerror="this.remove()">` : ''}
                       <span class="text-muted fs-8">${escHtml(o.rfq.country_name ?? o.rfq.country_code)}</span>
                   </div>`
                : '';

            // Кнопки действий по статусу
            const btns = [];
            if (o.status === 'received' || o.status === 'reviewed') {
                btns.push(`<button class="btn btn-icon btn-sm btn-light-danger" title="${t.index.reject}"
                               onclick="rejectOffer(${o.id}); return false;">
                               <i class="ki-outline ki-cross fs-4"></i>
                           </button>`);
            }
            btns.push(`<button class="btn btn-icon btn-sm btn-light-primary" title="${t.index.quick_view}"
                           onclick="openOfferDrawer(${o.id}); return false;">
                           <i class="ki-outline ki-eye fs-4"></i>
                       </button>`);
            btns.push(`<a href="/admin/offers/${o.id}" class="btn btn-icon btn-sm btn-light" title="${t.index.open_page}">
                           <i class="ki-outline ki-arrow-right fs-4"></i>
                       </a>`);

            return `
            <tr>
                <td>
                    ${reqLink}
                    ${svcBadge}
                    ${rfqCountry}
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <a href="/admin/suppliers/${o.supplier?.id ?? ''}" class="fw-semibold text-gray-800 text-hover-primary">
                            ${escHtml(o.supplier?.name ?? o.supplier_name ?? '—')}
                        </a>
                    </div>
                </td>
                <td class="fw-bold text-gray-900 fs-5">
                    ${price != null ? formatCurrency(price, o.currency) : '—'}
                </td>
                <td>${statusBadge(o)}</td>
                <td class="text-muted fs-7">
                    ${o.valid_until ? `<i class="ki-outline ki-calendar fs-7 me-1"></i>${fmtDT(o.valid_until)}` : '—'}
                </td>
                <td class="text-muted fs-7">${formatDate(o.created_at)}</td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">${btns.join('')}</div>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-220px">${t.index.cols.request}</th>
                        <th class="min-w-180px">${t.index.cols.supplier}</th>
                        <th class="min-w-120px">${t.index.cols.price}</th>
                        <th class="min-w-110px">${t.index.cols.status}</th>
                        <th class="min-w-120px">${t.index.cols.valid_until}</th>
                        <th class="min-w-100px">${t.index.cols.received}</th>
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
                <a class="page-link" href="#" onclick="event.preventDefault();loadOffers(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
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

    function rejectOffer(id) {
        pendingRejectId = id;
        new bootstrap.Modal(document.getElementById('modal-confirm-reject')).show();
    }

    async function doRejectOffer(id) {
        try {
            const d = await api.patch(`/offers/${id}/reject`);
            const updated = d.data ?? d;
            if (updated?.id) {
                showToast(t.toast.rejected);
                loadOffers(currentPage);
                if (drawerOfferId === id) renderDrawer({ ...drawerOffer, status: 'rejected' });
            } else {
                showToast(d.message ?? t.toast.error, 'error');
            }
        } catch (e) {
            showToast(e.message ?? t.toast.reject_error, 'error');
        }
    }

    // ---- Drawer ----
    let drawerOfferId = null;
    let drawerOffer   = null;

    async function openOfferDrawer(id) {
        drawerOfferId = id;
        drawerOffer   = null;

        const body   = document.getElementById('offer-drawer-body');
        const title  = document.getElementById('offer-drawer-title');
        const footer = document.getElementById('offer-drawer-footer');

        title.textContent  = t.drawer.default_title.replace(':id', id);
        body.innerHTML     = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;
        footer.innerHTML   = '';

        const canvas = new bootstrap.Offcanvas(document.getElementById('offer-drawer'));
        canvas.show();

        try {
            const data = await api.get(`/offers/${id}`);
            if (!data) throw new Error(tc.unexpected_error);
            drawerOffer = data.data ?? data;
            renderDrawer(drawerOffer);
        } catch (e) {
            console.error('[openOfferDrawer] error:', e);
            body.innerHTML = `<div class="alert alert-danger fs-7">
                ${t.drawer.error.replace(':msg', escHtml(e?.message ?? String(e)))}
            </div>`;
        }
    }

    function renderDrawer(offer) {
        const body   = document.getElementById('offer-drawer-body');
        const title  = document.getElementById('offer-drawer-title');
        const footer = document.getElementById('offer-drawer-footer');

        title.innerHTML = `${t.drawer.default_title.replace(':id', offer.id)} ${statusBadge(offer)}`;

        const items     = Array.isArray(offer.items) ? offer.items : [];
        const covered   = Array.isArray(offer.covered_services) ? offer.covered_services : [];
        const uncovered = Array.isArray(offer.uncovered_services) ? offer.uncovered_services : [];
        const currency  = offer.currency ?? 'AZN';

        // — Поставщик —
        const s = offer.supplier;
        const initials = s ? (s.name ?? '?').trim().split(/\s+/).filter(Boolean)
            .slice(0, 2).map(w => w[0].toUpperCase()).join('') : '?';

        const supplierHtml = s ? `
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                ${s.avatar_url
                    ? `<img src="${escHtml(s.avatar_url)}" class="rounded-circle" />`
                    : `<div class="symbol-label bg-light-primary text-primary fw-bold fs-6">${initials}</div>`
                }
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-1">
                    <a href="/admin/suppliers/${s.id}" class="fw-bold text-gray-800 text-hover-primary text-truncate">${escHtml(s.name)}</a>
                </div>
                ${s.email ? `<div class="text-muted fs-7"><i class="ki-outline ki-sms fs-7 me-1"></i>${escHtml(s.email)}</div>` : ''}
                ${s.phone ? `<div class="text-muted fs-7"><i class="ki-outline ki-phone fs-7 me-1"></i>${escHtml(s.phone)}</div>` : ''}
            </div>
        </div>` : `<p class="text-muted fs-7 mb-4">${t.drawer.no_supplier}</p>`;

        // — Услуги и цены —
        const priceByType = {};
        items.forEach(item => { priceByType[item.type] = item; });

        const coveredRows = covered.map(s => {
            const m     = SERVICE_META[s] ?? { label: s, color: 'secondary' };
            const item  = priceByType[s];
            const photos = item?.catalog_photos ?? [];
            const shown  = photos.slice(0, 3);
            const extra  = photos.length - shown.length;
            const groupId = `offer-${offer.id}-${s}`;

            const photosHtml = photos.length ? `
                <div class="d-flex align-items-center gap-1 mt-2">
                    ${shown.map((url, i) => `
                        <a href="${escHtml(url)}" data-fslightbox="${groupId}" class="d-block flex-shrink-0">
                            <img src="${escHtml(url)}" style="width:40px;height:40px;object-fit:cover;border-radius:6px">
                        </a>`).join('')}
                    ${photos.length > 3 ? `
                        <a href="${escHtml(photos[3])}" data-fslightbox="${groupId}"
                           class="d-flex align-items-center justify-content-center bg-light text-muted fw-semibold fs-8 flex-shrink-0"
                           style="width:40px;height:40px;border-radius:6px">
                            +${extra + 1}
                        </a>
                        ${photos.slice(4).map(url => `<a href="${escHtml(url)}" data-fslightbox="${groupId}" class="d-none"></a>`).join('')}` : ''}
                </div>` : '';

            return `
            <div class="py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="flex-grow-1 fw-semibold text-gray-800">${m.label}</span>
                    ${item?.name && item.name !== s
                        ? `<span class="text-muted fs-7 me-3 text-truncate" style="max-width:140px">${escHtml(item.name)}</span>`
                        : ''}
                    <span class="fw-bold text-gray-900 text-nowrap">
                        ${item ? formatCurrency(item.unit_price, currency) : '—'}
                    </span>
                </div>
                ${photosHtml}
            </div>`;
        }).join('');

        const uncoveredRows = uncovered.map(s => {
            const m = SERVICE_META[s] ?? { label: s, color: 'secondary' };
            return `
            <div class="d-flex align-items-center py-3 gap-3 opacity-50">
                <span class="flex-grow-1 fw-semibold text-gray-500">${m.label}</span>
                <span class="badge badge-light-danger fs-8">${t.labels.not_covered}</span>
            </div>`;
        }).join('');

        const totalPrice = items.length
            ? items.reduce((acc, i) => acc + parseFloat(i.unit_price ?? 0), 0)
            : parseFloat(offer.unit_price ?? 0);

        const totalRow = covered.length > 1 ? `
            <div class="d-flex align-items-center justify-content-end gap-3 pt-2">
                <span class="text-muted fs-7">${t.labels.total}</span>
                <span class="fw-bold fs-4 text-gray-900">${formatCurrency(totalPrice, currency)}</span>
            </div>` : '';

        // — RFQ / Заявка —
        const rfq = offer.rfq;
        const req = rfq?.request;
        const svcMeta = rfq ? (SERVICE_META[rfq.service_type] ?? { label: rfq.service_type ?? '—', icon: 'ki-abstract-26', color: 'secondary' }) : null;

        const contextHtml = rfq ? `
        <div class="separator my-5"></div>
        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-4">${t.drawer.context}</div>
        <div class="mb-${req ? '4' : '0'}">
            <div class="text-muted fs-8 mb-1">${t.drawer.rfq}</div>
            <a href="/admin/rfqs/${rfq.id}" class="fw-semibold text-gray-800 text-hover-primary fs-6 d-block">
                ${escHtml(rfq.title ?? t.drawer.rfq_ref.replace(':id', rfq.id))}
            </a>
            ${rfq.country_code ? `<div class="d-flex align-items-center gap-1 mt-1">
                ${rfq.country_flag ? `<img src="${rfq.country_flag}" style="width:18px;height:13px;object-fit:cover;border-radius:2px" onerror="this.remove()">` : ''}
                <span class="text-gray-700 fs-7 fw-semibold">${escHtml(rfq.country_name ?? rfq.country_code)}</span>
            </div>` : ''}
            ${rfq.deadline_at ? `<div class="text-muted fs-8 mt-1">${t.drawer.deadline.replace(':date', fmtDT(rfq.deadline_at))}</div>` : ''}
        </div>
        ${req ? `
        <div>
            <div class="text-muted fs-8 mb-1">${t.drawer.request}</div>
            <a href="/admin/requests/${req.id}" class="fw-semibold text-gray-800 text-hover-primary fs-6 d-block">
                ${escHtml(req.title ?? req.destination ?? t.drawer.request_ref.replace(':id', req.id))}
            </a>
            ${req.destination ? `<div class="text-muted fs-8 mt-1">${escHtml(req.destination)}</div>` : ''}
            ${req.agency?.name ? `<div class="text-muted fs-8 mt-1">${escHtml(req.agency.name)}</div>` : ''}
        </div>` : ''}` : '';

        const expiredBadge  = offer.is_expired ? `<span class="badge badge-light-danger ms-2">${t.labels.expired}</span>` : '';
        const partialBadge  = offer.is_partial  ? `<span class="badge badge-light-warning ms-2">${t.labels.partial}</span>` : '';

        body.innerHTML = `
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-6">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                ${partialBadge}${expiredBadge}
            </div>
            <div class="text-end">
                <div class="text-muted fs-8">${t.labels.valid_until}</div>
                <div class="fw-bold fs-6 ${offer.is_expired ? 'text-danger' : 'text-gray-800'}">
                    ${offer.valid_until ? fmtDT(offer.valid_until) : '—'}
                </div>
                <div class="text-muted fs-8 mt-1">${t.labels.received_at.replace(':date', fmtDT(offer.created_at))}</div>
            </div>
        </div>

        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">${t.labels.supplier}</div>
        ${supplierHtml}

        ${covered.length || uncovered.length ? `
        <div class="separator my-5"></div>
        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-2">${t.labels.services_prices}</div>
        ${coveredRows}${uncoveredRows}
        ${covered.length > 1 ? totalRow : ''}` : `
        <div class="separator my-5"></div>
        <div class="text-muted fs-7">${t.labels.prices_none}</div>`}

        ${offer.notes ? `
        <div class="separator my-5"></div>
        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-2">${t.labels.notes}</div>
        <div class="text-gray-700 fs-6">${escHtml(offer.notes)}</div>` : ''}

        ${contextHtml}`;

        // Кнопки в footer
        const footerBtns = [];
        if (offer.status === 'received' || offer.status === 'reviewed') {
            footerBtns.push(`<button class="btn btn-sm btn-light-danger" onclick="rejectOffer(${offer.id})">
                <i class="ki-outline ki-cross-circle fs-5 me-1"></i>${t.drawer.reject}</button>`);
        }
        footerBtns.push(`<a href="/admin/offers/${offer.id}" class="btn btn-sm btn-light ms-auto">
            <i class="ki-outline ki-arrow-right fs-5 me-1"></i>${t.drawer.open_page}</a>`);
        footer.innerHTML = footerBtns.join('');

        if (typeof refreshFsLightbox === 'function') refreshFsLightbox();
    }

    async function drawerWithdraw(id) {
        try {
            const d = await api.patch(`/offers/${id}/withdraw`);
            const updated = d.data ?? d;
            if (updated?.id) {
                showToast(t.toast.withdrawn);
                loadOffers(currentPage);
                if (drawerOfferId === id) renderDrawer({ ...drawerOffer, status: 'withdrawn' });
            } else {
                showToast(d.message ?? t.toast.error, 'error');
            }
        } catch (e) {
            showToast(e.message ?? t.toast.withdraw_error, 'error');
        }
    }

    // ---- Helpers ----
    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary', icon: 'ki-abstract-26', cls: 'badge-light-secondary' }]));

</script>
@endpush
