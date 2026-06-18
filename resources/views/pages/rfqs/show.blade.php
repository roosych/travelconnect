@extends('layouts.app')

@section('title', __('rfqs.show.title'))
@section('page-title', __('rfqs.show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.rfqs.index') }}" class="text-muted text-hover-primary">{{ __('rfqs.title') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('rfqs.show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@section('toolbar-actions')
    <div id="rfq-actions" class="d-flex gap-2"></div>
@endsection

@section('content')

{{-- RFQ info card --}}
<div id="rfq-info-card" class="card card-flush mb-6">
    <div class="card-body py-6">
        <div class="text-center py-6">
            <span class="spinner-border text-primary"></span>
        </div>
    </div>
</div>

<div class="row g-6">
    {{-- Suppliers panel --}}
    <div class="col-lg-5">
        <div class="card card-flush h-100">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">{{ __('rfqs.show.suppliers_title') }}</h3>
                </div>
                <div class="card-toolbar">
                    <button class="btn btn-sm btn-light-primary" id="btn-add-supplier">
                        <i class="ki-outline ki-plus fs-3"></i> {{ __('rfqs.show.add_supplier') }}
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="suppliers-container">
                    <div class="text-center py-8"><span class="spinner-border text-primary"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Offers panel --}}
    <div class="col-lg-7">
        <div class="card card-flush h-100">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">
                        {{ __('rfqs.show.offers_title') }} <span class="badge badge-light-warning ms-2" id="offers-count">0</span>
                    </h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="rfq-offers-container">
                    <div class="text-center py-8"><span class="spinner-border text-warning"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Attachments panel --}}
<div class="mt-6">
    @include('components.attachments', [
        'entityType' => 'rfqs',
        'entityId'   => 'rfqId',
        'canUpload'  => true,
    ])
</div>

{{-- Offer Drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offer-drawer" style="width:560px">
    <div class="offcanvas-header border-bottom py-5 px-7">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1" id="drawer-offer-title">
                <span class="fw-bold fs-5 text-gray-900"></span>
            </div>
            <div class="text-muted fs-7" id="drawer-offer-subtitle"></div>
        </div>
        <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary ms-auto" data-bs-dismiss="offcanvas">
            <i class="ki-outline ki-cross fs-1"></i>
        </button>
    </div>
    <div class="offcanvas-body px-7 py-6" id="drawer-offer-body">
        <div class="text-center py-12"><span class="spinner-border text-primary"></span></div>
    </div>
    <div class="offcanvas-footer border-top px-7 py-4 d-flex justify-content-between align-items-center gap-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="offcanvas">{{ __('rfqs.show.drawer.close') }}</button>
        <div class="d-flex gap-2" id="drawer-offer-actions"></div>
    </div>
</div>

{{-- Add Supplier Modal --}}
<div class="modal fade" id="modal-add-supplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('rfqs.show.modal.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="row g-5">
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('rfqs.show.modal.service_type') }}</label>
                        <div id="add-supplier-service-type" class="form-control form-control-solid bg-light text-muted"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('rfqs.show.modal.supplier') }}</label>
                        <select id="add-supplier-select" class="form-select form-select-solid">
                            <option value="">{{ __('rfqs.show.modal.supplier_ph') }}</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('rfqs.show.modal.name') }}</label>
                        <input type="text" id="add-supplier-title" class="form-control form-control-solid"
                               placeholder="{{ __('rfqs.show.modal.name_ph') }}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('rfqs.show.modal.deadline') }}</label>
                        <input type="date" id="add-supplier-deadline" class="form-control form-control-solid" />
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('rfqs.show.modal.notes') }} <span class="text-muted fw-normal">{{ __('rfqs.show.modal.optional') }}</span></label>
                        <textarea id="add-supplier-notes" class="form-control form-control-solid" rows="2"
                                  placeholder="{{ __('rfqs.show.modal.notes_ph') }}"></textarea>
                    </div>
                </div>
                <div id="add-supplier-error" class="alert alert-danger mt-5 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-supplier" class="btn btn-primary" disabled>
                    <span class="indicator-label">{{ __('rfqs.show.modal.save') }}</span>
                    <span class="indicator-progress d-none">{{ __('rfqs.show.modal.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const rfqId = @json($id);
    const t  = @json(__('rfqs'));
    const tc = @json(__('common'));
    const ts = t.show;
    let currentRfq = null;
    let respondedSupplierIds = new Set();

    const SERVICE_TYPE_LABELS = window.SERVICE_LABELS;

    (async function init() {
        await Promise.all([loadRfqDetails(), loadOffers()]);
        if (currentRfq) renderSuppliers(currentRfq.suppliers ?? []);
        loadAttachments('rfqs', rfqId, true);
        initFilepond('rfqs', () => rfqId, true);
    })();

    async function loadRfqDetails() {
        try {
            const data = await api.get(`/rfqs/${rfqId}`);
            currentRfq = data.data ?? data;
            renderRfqInfo(currentRfq);
            renderSuppliers(currentRfq.suppliers ?? []);
        } catch(err) {
            document.getElementById('rfq-info-card').querySelector('.card-body').innerHTML =
                `<div class="alert alert-danger">${ts.load_error}</div>`;
        }
    }

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_INFO = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, icon: 'ki-abstract-26', color: 'secondary' }]));

    function renderRfqInfo(rfq) {
        const actionsEl = document.getElementById('rfq-actions');
        actionsEl.innerHTML = '';

        if (rfq.request?.id) {
            actionsEl.innerHTML += `
                <a href="/admin/requests/${rfq.request.id}" class="btn btn-sm btn-light">
                    <i class="ki-outline ki-arrow-left fs-4 me-1"></i>${ts.request_ref.replace(':id', rfq.request.id)}
                </a>`;
        }

        if (rfq.status === 'draft') {
            actionsEl.innerHTML += `
                <button class="btn btn-sm btn-primary" onclick="sendRfq()">
                    <i class="ki-outline ki-send fs-4 me-1"></i>${ts.actions.send}
                </button>`;
        } else if (['sent','awaiting'].includes(rfq.status)) {
            actionsEl.innerHTML += `
                <button class="btn btn-sm btn-warning" onclick="closeRfq()">
                    <i class="ki-outline ki-lock fs-4 me-1"></i>${ts.actions.close}
                </button>`;
        }

        const supplierCount = rfq.suppliers?.length ?? 0;
        const offersCount   = rfq.offer_count ?? 0;
        const svc           = SERVICE_INFO[rfq.service_type] ?? { label: rfq.service_type, icon: 'ki-category', color: 'secondary' };

        // Deadline coloring
        let deadlineHtml = '';
        if (rfq.deadline_at) {
            const diff = Math.ceil((new Date(rfq.deadline_at) - Date.now()) / 86400000);
            const TERMINAL = ['closed', 'cancelled'];
            const dl = ts.deadline_label.replace(':date', fmtDeadline(rfq.deadline_at));
            const daysLeft = tc.time.days.replace(':n', diff);
            if (TERMINAL.includes(rfq.status)) {
                deadlineHtml = `<span class="text-muted"><i class="ki-outline ki-calendar fs-5 me-1"></i>${dl}</span>`;
            } else if (diff < 0) {
                deadlineHtml = `<span class="text-danger fw-semibold"><i class="ki-outline ki-warning-2 fs-5 me-1"></i>${dl} <span class="badge badge-light-danger fs-9 ms-1">${tc.time.overdue}</span></span>`;
            } else if (diff === 0) {
                deadlineHtml = `<span class="text-warning fw-semibold"><i class="ki-outline ki-calendar fs-5 me-1"></i>${dl} <span class="badge badge-light-warning fs-9 ms-1">${tc.time.today}</span></span>`;
            } else if (diff <= 3) {
                deadlineHtml = `<span class="text-warning fw-semibold"><i class="ki-outline ki-calendar fs-5 me-1"></i>${dl} <span class="text-muted fw-normal fs-8">${daysLeft}</span></span>`;
            } else {
                deadlineHtml = `<span class="text-muted"><i class="ki-outline ki-calendar fs-5 me-1"></i>${dl} <span class="text-gray-500 fs-8">${daysLeft}</span></span>`;
            }
        }

        // Сегмент: страна с датами/направлениями/требованиями этого leg.
        let segmentHtml = '';
        if (rfq.segment || rfq.country_code) {
            const sg    = rfq.segment ?? {};
            const flag  = rfq.country_flag ? `<img src="${rfq.country_flag}" style="width:20px;height:14px;object-fit:cover;border-radius:2px" onerror="this.style.display='none'">` : '';
            const dates = (sg.date_from || sg.date_to) ? `<span class="text-muted fs-8"><i class="ki-outline ki-calendar fs-8 me-1"></i>${formatDate(sg.date_from)} — ${formatDate(sg.date_to)}</span>` : '';
            const dests = (sg.destinations ?? []).map(d => `<span class="badge badge-light-primary fs-9 me-1">${escHtml(d)}</span>`).join('');
            const reqs  = sg.requirements_summary ? `<div class="mt-1"><span class="text-muted fs-8">${ts.requirements} <span class="fw-semibold text-gray-700">${escHtml(sg.requirements_summary)}</span></span></div>` : '';
            segmentHtml = `
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    ${flag}
                    <span class="fw-bold text-gray-800 fs-6">${escHtml(rfq.country_name ?? rfq.country_code ?? '')}</span>
                    ${dates}
                    ${dests}
                </div>
                ${reqs}`;
        }

        document.getElementById('rfq-info-card').innerHTML = `
            <div class="card-body py-6">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h3 class="fw-bold text-gray-900 mb-0 fs-4">${escHtml(rfq.title ?? ts.rfq_ref.replace(':id', rfq.id))}</h3>
                        ${statusBadge(rfq.status)}
                    </div>
                    <div class="d-flex flex-wrap gap-5 text-muted fs-7">
                        <span class="badge badge-light-${svc.color} fs-8">${svc.label}</span>
                        ${deadlineHtml}
                        ${rfq.request?.id ? `
                            <a href="/admin/requests/${rfq.request.id}" class="d-flex align-items-center gap-1 text-gray-600 text-hover-primary">
                                <i class="ki-outline ki-document fs-5"></i>
                                ${rfq.request.title ? escHtml(rfq.request.title) : ts.request_ref.replace(':id', rfq.request.id)}
                                <i class="ki-outline ki-arrow-up-right fs-9 text-gray-400"></i>
                            </a>` : ''}
                        <span class="text-muted fs-8">${ts.created_label.replace(':date', formatDate(rfq.created_at))}</span>
                    </div>
                    ${segmentHtml}
                    ${rfq.description ? `<p class="text-gray-600 fs-7 mb-0">${escHtml(rfq.description)}</p>` : ''}
                    ${rfq.notes ? `<p class="text-muted fs-7 mb-0">${escHtml(rfq.notes)}</p>` : ''}
                </div>
            </div>`;
    }

    function renderSuppliers(suppliers) {
        const container = document.getElementById('suppliers-container');
        if (!suppliers.length) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="ki-outline ki-people fs-3x text-gray-300 mb-3 d-block"></i>
                    <span class="text-muted fs-7">${ts.suppliers_empty}</span>
                </div>`;
            return;
        }

        const baseUrl = window.location.origin + '/supplier/rfq/';

        const items = suppliers.map(s => {
            const hasResponded = respondedSupplierIds.has(s.id);
            const sentLine     = s.sent_at ? ts.sent_at.replace(':date', formatDate(s.sent_at)) : ts.not_sent;

            const portalBadge = s.uses_portal
                ? `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        style="flex-shrink:0;vertical-align:middle" title="${ts.uses_portal}">
                       <circle cx="12" cy="12" r="10" fill="#0095F6"/>
                       <path d="M7 12.5l3.5 3.5 6.5-7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                   </svg>`
                : '';

            const responseBadge = hasResponded
                ? `<span class="badge badge-light-success fs-9"><i class="ki-outline ki-check-circle fs-8 me-1"></i>${ts.responded}</span>`
                : (!s.sent_at && !s.uses_portal ? `<span class="badge badge-light-secondary fs-9">${ts.not_sent}</span>` : '');

            let linkBtns = '';
            if (!hasResponded && !s.uses_portal) {
                linkBtns = s.token
                    ? `<button class="btn btn-sm btn-icon btn-light-primary" title="${ts.send_link}">
                           <i class="ki-outline ki-send fs-4"></i>
                       </button>
                       <button class="btn btn-sm btn-icon btn-light" title="${ts.copy_link}"
                               onclick="copyLink('${baseUrl}${s.token}', this)">
                           <i class="ki-outline ki-copy fs-4"></i>
                       </button>`
                    : `<button class="btn btn-sm btn-icon btn-light-warning" title="${ts.create_link}"
                           onclick="generateSupplierToken(${s.id})">
                           <i class="ki-outline ki-key fs-4"></i>
                       </button>`;
            }
            const avatar = s.avatar_url
                ? `<img src="${escHtml(s.avatar_url)}" class="rounded-circle" style="width:35px;height:35px;object-fit:cover">`
                : `<span class="symbol-label bg-light-primary text-primary fw-semibold fs-6">
                       ${escHtml((s.name ?? s.email ?? 'S').charAt(0).toUpperCase())}
                   </span>`;
            return `
            <div class="d-flex align-items-center py-3 border-bottom gap-3">
                <div class="symbol symbol-35px symbol-circle flex-shrink-0">${avatar}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="/admin/suppliers/${s.id}" class="fw-semibold text-gray-800 text-hover-primary fs-6 text-truncate">${escHtml(s.name ?? s.email ?? '—')}</a>
                        ${portalBadge}
                        ${responseBadge}
                    </div>
                    <div class="text-muted fs-8">${escHtml(s.email ?? '')}</div>
                    <div class="text-muted fs-8">${sentLine}</div>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">${linkBtns}</div>
            </div>`;
        }).join('');

        container.innerHTML = `<div>${items}</div>`;
    }

    function copyLink(url, btn) {
        navigator.clipboard.writeText(url).then(() => {
            const icon = btn.querySelector('i');
            icon.className = 'ki-outline ki-check-circle fs-4 text-success';
            setTimeout(() => { icon.className = 'ki-outline ki-copy fs-4'; }, 2000);
        });
    }

    async function loadOffers() {
        try {
            const data = await api.get(`/rfqs/${rfqId}/offers`);
            const offers = data.data ?? data ?? [];
            respondedSupplierIds = new Set(offers.map(o => o.supplier?.id).filter(Boolean));
            const supplierTotal = currentRfq?.suppliers?.length ?? 0;
            const countBadge = document.getElementById('offers-count');
            countBadge.textContent = offers.length;
            countBadge.className = 'badge ms-2 ' + (
                offers.length === 0                       ? 'badge-light-secondary'
              : supplierTotal && offers.length >= supplierTotal ? 'badge-light-success'
              : 'badge-light-warning'
            );
            renderOffers(offers);
        } catch(err) {
            document.getElementById('rfq-offers-container').innerHTML =
                `<div class="alert alert-danger">${ts.offers_load_error}</div>`;
        }
    }

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, icon: 'ki-outline ki-abstract-26', color: 'secondary' }]));

    function renderOffers(offers) {
        const container = document.getElementById('rfq-offers-container');
        if (!offers.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-tag fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${ts.offers_empty}</span>
                </div>`;
            return;
        }

        const cards = offers.map(o => {
            const supplierName = escHtml(o.supplier?.name ?? o.supplier_name ?? '—');
            const items = o.items ?? [];
            const hasItems = items.length > 0;

            let priceBlock = '';
            if (hasItems) {
                const rows = items.map((item, idx) => {
                    const meta = SERVICE_META[item.type] ?? SERVICE_META.other;
                    const desc = item.name && item.name !== item.type
                        ? `<span class="text-muted fs-8 ms-1">— ${escHtml(item.name)}</span>` : '';
                    const isLast = idx === items.length - 1;
                    return `
                        <div class="d-flex align-items-center justify-content-between py-2${isLast ? '' : ' border-bottom'}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-gray-700 fs-7">${escHtml(meta.label)}</span>
                                ${desc}
                            </div>
                            <span class="fw-bold text-gray-800 fs-7">${formatCurrency(item.unit_price)}</span>
                        </div>`;
                }).join('');

                const totalRow = items.length > 1
                    ? `<div class="d-flex justify-content-between pt-2">
                           <span class="text-muted fs-8">${ts.total}</span>
                           <span class="fw-bold text-gray-900">${formatCurrency(o.unit_price)}</span>
                       </div>` : '';

                priceBlock = `<div class="mt-3">${rows}${totalRow}</div>`;
            } else {
                priceBlock = `<div class="mt-3 fw-bold text-gray-800">${formatCurrency(o.unit_price)}</div>`;
            }

            const actions = `<button class="btn btn-sm btn-light btn-active-light-primary" onclick="openOfferDrawer('${o.id}')">
                    <i class="ki-outline ki-eye fs-5 me-1"></i>${tc.open}
                </button>`;

            const offerAvatar = o.supplier?.avatar_url
                ? `<img src="${escHtml(o.supplier.avatar_url)}" class="rounded-circle" style="width:35px;height:35px;object-fit:cover">`
                : `<span class="symbol-label bg-light-success text-success fw-bold fs-7">
                       ${escHtml((o.supplier?.name ?? 'S').charAt(0).toUpperCase())}
                   </span>`;

            return `
                <div class="border rounded p-4 mb-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="symbol symbol-35px symbol-circle flex-shrink-0">${offerAvatar}</div>
                            <div class="min-w-0">
                                <button class="btn btn-link p-0 fw-bold text-gray-800 text-hover-primary fs-6 text-start"
                                        onclick="openOfferDrawer('${o.id}')">${supplierName}</button>
                                <div class="text-muted fs-8">${ts.offer_ref.replace(':id', o.id)} · ${ts.valid_until_short.replace(':date', formatDate(o.valid_until))}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            ${offerStatusBadge(o.status)}
                        </div>
                    </div>
                    ${priceBlock}
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        ${actions}
                    </div>
                </div>`;
        }).join('');

        container.innerHTML = cards;
    }

    function offerStatusBadge(s) {
        const m = {
            received:  ['badge-light-warning',  ts.offer_status.received],
            reviewed:  ['badge-light-info',     ts.offer_status.reviewed],
            selected:  ['badge-light-success',  ts.offer_status.selected],
            rejected:  ['badge-light-secondary',ts.offer_status.rejected],
            expired:   ['badge-light-secondary',ts.offer_status.expired],
            withdrawn: ['badge-light-dark',     ts.offer_status.withdrawn],
        };
        const [cls, label] = m[s] ?? ['badge-light-secondary', s ?? '—'];
        return `<span class="badge ${cls}">${label}</span>`;
    }

    async function sendRfq() {
        const supplierIds = (currentRfq?.suppliers ?? []).map(s => s.id);
        if (!supplierIds.length) { showToast(ts.toast.need_supplier, 'error'); return; }
        const d = await api.patch(`/rfqs/${rfqId}/send`, { supplier_ids: supplierIds });
        if (d.data ?? d.id) { showToast(ts.toast.sent); await loadRfqDetails(); }
        else showToast(d.message ?? t.toast.error, 'error');
    }

    async function generateSupplierToken(supplierId) {
        try {
            const d = await api.post(`/rfqs/${rfqId}/suppliers`, { supplier_ids: [supplierId] });
            await loadRfqDetails();
            // find fresh token for this supplier and copy it
            const supplier = (currentRfq?.suppliers ?? []).find(s => s.id === supplierId);
            if (supplier?.token) {
                const link = window.location.origin + '/supplier/rfq/' + supplier.token;
                await navigator.clipboard.writeText(link);
                showToast(ts.toast.link_copied);
            } else {
                showToast(ts.toast.link_created);
            }
        } catch (e) {
            showToast(e.message ?? ts.toast.link_error, 'error');
        }
    }

    async function closeRfq() {
        const d = await api.patch(`/rfqs/${rfqId}/close`);
        if (d.data ?? d.id) { showToast(ts.toast.closed); await loadRfqDetails(); }
        else showToast(d.message ?? t.toast.error, 'error');
    }

    // ---- Offer Drawer ----
    let drawerOfferId = null;

    async function openOfferDrawer(id) {
        drawerOfferId = id;
        const bodyEl   = document.getElementById('drawer-offer-body');
        const titleEl  = document.getElementById('drawer-offer-title');
        const subEl    = document.getElementById('drawer-offer-subtitle');
        const actionsEl = document.getElementById('drawer-offer-actions');

        titleEl.innerHTML = `<span class="fw-bold fs-5 text-gray-900">${ts.offer_ref.replace(':id', id)}</span>`;
        subEl.textContent = '';
        actionsEl.innerHTML = '';
        bodyEl.innerHTML = '<div class="text-center py-12"><span class="spinner-border text-primary"></span></div>';

        new bootstrap.Offcanvas(document.getElementById('offer-drawer')).show();

        try {
            const data  = await api.get(`/offers/${id}`);
            const offer = data.data ?? data;
            renderDrawerContent(offer);
        } catch {
            bodyEl.innerHTML = `<div class="alert alert-danger">${ts.drawer.load_error}</div>`;
        }
    }

    function renderDrawerContent(offer) {
        const s          = offer.supplier;
        const items      = Array.isArray(offer.items) ? offer.items : [];
        const covered    = Array.isArray(offer.covered_services)   ? offer.covered_services   : [];
        const uncovered  = Array.isArray(offer.uncovered_services)  ? offer.uncovered_services  : [];
        const currency   = offer.currency ?? 'AZN';

        // Header
        const rfqSvc = SERVICE_META[offer.rfq?.service_type];
        const svcBadge = rfqSvc
            ? `<span class="badge badge-light-${rfqSvc.color} fs-9">${rfqSvc.label}</span>`
            : '';
        document.getElementById('drawer-offer-title').innerHTML =
            `<span class="fw-bold fs-5 text-gray-900">${ts.offer_ref.replace(':id', offer.id)}</span>
             ${svcBadge}
             ${offerStatusBadge(offer.status)}
             ${offer.is_expired ? `<span class="badge badge-light-danger fs-9">${ts.drawer.expired}</span>` : ''}
             ${offer.is_partial ? `<span class="badge badge-light-warning fs-9">${ts.drawer.partial}</span>` : ''}`;
        document.getElementById('drawer-offer-subtitle').textContent = s?.name ?? '';

        // Build price rows from items (the actual line items), not from covered_services strings
        let pricesHtml;
        if (items.length > 0) {
            const itemRows = items.map(item => {
                const m = SERVICE_META[item.type] ?? { label: item.type, icon: 'ki-outline ki-abstract-26', color: 'secondary' };
                return `
                <div class="d-flex align-items-center gap-2 py-3 border-bottom">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-gray-800 fs-7">${m.label}</div>
                        ${item.name ? `<div class="text-muted fs-8 text-truncate">${escHtml(item.name)}</div>` : ''}
                    </div>
                    <span class="fw-bold text-gray-900 fs-7 text-nowrap">${formatCurrency(item.unit_price)}</span>
                </div>`;
            }).join('');

            const total = items.reduce((sum, i) => sum + parseFloat(i.unit_price ?? 0), 0);
            const totalRow = items.length > 1
                ? `<div class="d-flex justify-content-between align-items-center pt-3">
                       <span class="text-muted fs-7">${ts.total}:</span>
                       <span class="fw-bold fs-4 text-gray-900">${formatCurrency(total)}</span>
                   </div>`
                : '';
            pricesHtml = itemRows + totalRow;
        } else {
            pricesHtml = `<div class="fw-bold fs-4 text-gray-900 py-3">${formatCurrency(offer.unit_price)}</div>`;
        }

        // Uncovered rows (no prices, just labels)
        const uncoveredRows = uncovered.map(type => {
            const m = SERVICE_META[type] ?? { label: type };
            return `
            <div class="d-flex align-items-center gap-2 py-3 border-bottom opacity-50">
                <span class="flex-grow-1 fw-semibold text-gray-500 fs-7">${m.label}</span>
                <span class="badge badge-light-danger fs-9">${ts.drawer.uncovered}</span>
            </div>`;
        }).join('');

        // Supplier block
        const initials = s ? (s.name ?? '?').trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('') : '?';
        const supplierBlock = s ? `
            <div class="d-flex align-items-center gap-3 p-4 bg-light rounded mb-6">
                <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                    ${s.avatar_url
                        ? `<img src="${escHtml(s.avatar_url)}" class="rounded-circle">`
                        : `<span class="symbol-label bg-light-success text-success fw-bold fs-6">${initials}</span>`}
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-bold text-gray-800 fs-6">${escHtml(s.name ?? '—')}</div>
                    ${s.email ? `<div class="text-muted fs-8">${escHtml(s.email)}</div>` : ''}
                </div>
                ${s.id ? `<a href="/admin/suppliers/${s.id}" target="_blank"
                              class="btn btn-sm btn-icon btn-light" title="${ts.drawer.supplier_profile}">
                              <i class="ki-outline ki-arrow-up-right fs-5"></i>
                          </a>` : ''}
            </div>` : '';

        document.getElementById('drawer-offer-body').innerHTML = `
            ${supplierBlock}

            <div class="mb-2 text-gray-500 fw-bold fs-8 text-uppercase">${ts.drawer.services_prices}</div>
            ${pricesHtml}
            ${uncoveredRows}

            <div class="d-flex gap-8 mt-5 mb-1 text-muted fs-8">
                <div class="d-flex flex-column gap-1">
                    <span>${ts.drawer.valid_until}</span>
                    <span class="fw-semibold ${offer.is_expired ? 'text-danger' : 'text-gray-700'}">${formatDate(offer.valid_until)}</span>
                </div>
                <div class="d-flex flex-column gap-1">
                    <span>${ts.drawer.received}</span>
                    <span class="text-gray-700">${formatDate(offer.created_at)}</span>
                </div>
            </div>

            ${offer.notes ? `
            <div class="separator my-5"></div>
            <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-2">${ts.drawer.supplier_notes}</div>
            <div class="text-gray-700 fs-7">${escHtml(offer.notes)}</div>` : ''}`;

        // Footer actions
        const actionsEl = document.getElementById('drawer-offer-actions');
        const requestId = offer.rfq?.request?.id ?? currentRfq?.request?.id ?? null;
        const btns = [];

        if (['received', 'reviewed'].includes(offer.status)) {
            btns.push(`<button class="btn btn-sm btn-light-danger" onclick="drawerRejectOffer()">
                <i class="ki-outline ki-cross-circle fs-4 me-1"></i>${ts.drawer.reject}
            </button>`);
        }

        if (requestId) {
            btns.push(`<a href="/admin/requests/${requestId}" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-arrow-right fs-4 me-1"></i>${ts.drawer.goto_request}
            </a>`);
        }

        actionsEl.innerHTML = btns.join('');
    }

    async function drawerRejectOffer() {
        const d = await api.patch(`/offers/${drawerOfferId}/reject`);
        if (d.data ?? d.id) {
            showToast(ts.toast.offer_rejected);
            bootstrap.Offcanvas.getInstance(document.getElementById('offer-drawer'))?.hide();
            await loadOffers();
            if (currentRfq) renderSuppliers(currentRfq.suppliers ?? []);
        } else {
            showToast(d.message ?? t.toast.error, 'error');
        }
    }

    // Add supplier
    let _addSupplierList = [];

    document.getElementById('btn-add-supplier').addEventListener('click', async function() {
        const errorEl  = document.getElementById('add-supplier-error');
        const saveBtn  = document.getElementById('btn-save-supplier');
        errorEl.classList.add('d-none');
        saveBtn.disabled = true;

        // Pre-fill title and deadline from current RFQ
        const today = new Date().toISOString().split('T')[0];
        const deadlineEl = document.getElementById('add-supplier-deadline');
        deadlineEl.min   = today;
        document.getElementById('add-supplier-title').value = currentRfq?.title ?? '';
        deadlineEl.value = currentRfq?.deadline_at ?? '';
        document.getElementById('add-supplier-notes').value = '';

        // Show service type label (read-only)
        const svcLabel = SERVICE_TYPE_LABELS[currentRfq?.service_type] ?? currentRfq?.service_type ?? '—';
        document.getElementById('add-supplier-service-type').textContent = svcLabel;

        const $sel = $('#add-supplier-select');
        if ($sel.data('select2')) $sel.select2('destroy');
        $sel.off('change.add-supplier');
        $sel.empty().append(`<option value="">${ts.modal.loading_suppliers}</option>`);
        $sel.prop('disabled', true);
        $sel.select2({
            placeholder:    ts.modal.supplier_ph,
            allowClear:     true,
            dropdownParent: $('#modal-add-supplier'),
            language:       { noResults: () => ts.modal.no_results, searching: () => ts.modal.searching },
        });

        $sel.on('change.add-supplier', function () {
            saveBtn.disabled = !$(this).val();
        });

        new bootstrap.Modal(document.getElementById('modal-add-supplier')).show();

        const data      = await api.get('/suppliers');
        const existing  = new Set((currentRfq?.suppliers ?? []).map(s => s.id));
        const svcType   = currentRfq?.service_type;

        _addSupplierList = (data.data ?? data ?? []).filter(s =>
            s.is_active !== false &&
            !existing.has(s.id) &&
            (!svcType || (s.service_types ?? []).includes(svcType))
        );

        $sel.empty().append(`<option value="">${ts.modal.supplier_ph}</option>`);
        if (_addSupplierList.length) {
            _addSupplierList.forEach(s => $sel.append(new Option(s.name, s.id)));
        } else {
            $sel.append(`<option disabled>${ts.modal.no_suppliers}</option>`);
        }
        $sel.prop('disabled', false).trigger('change');
    });

    document.getElementById('btn-save-supplier').addEventListener('click', async function() {
        const btn        = this;
        const errorEl    = document.getElementById('add-supplier-error');
        const supplierId = $('#add-supplier-select').val();

        if (!supplierId) {
            errorEl.textContent = ts.modal.select_supplier;
            errorEl.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        const notes    = document.getElementById('add-supplier-notes').value.trim() || null;
        const svcType  = currentRfq?.service_type;

        try {
            await api.post(`/rfqs/${rfqId}/suppliers`, {
                supplier_ids:  [Number(supplierId)],
                service_types: svcType ? [svcType] : null,
                notes,
            });
            bootstrap.Modal.getInstance(document.getElementById('modal-add-supplier')).hide();
            const alreadyActive = ['sent', 'awaiting'].includes(currentRfq?.status);
            showToast(alreadyActive ? ts.toast.supplier_added_sent : ts.toast.supplier_added);
            await loadRfqDetails();
        } catch(err) {
            errorEl.textContent = ts.modal.add_error;
            errorEl.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.querySelector('.indicator-label').classList.remove('d-none');
            btn.querySelector('.indicator-progress').classList.add('d-none');
        }
    });

    function statusBadge(s) {
        const m = {
            draft:     'badge-light-secondary',
            sent:      'badge-light-primary',
            awaiting:  'badge-light-info',
            closed:    'badge-light-warning',
            cancelled: 'badge-light-dark',
        };
        const labels = t.status.operator;
        const c = m[s] ?? 'badge-light-secondary';
        const label = labels[s] ?? (s ? s.replace(/\b\w/g, l => l.toUpperCase()) : '—');
        return `<span class="badge ${c}">${label}</span>`;
    }
    function formatDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('ru-RU', {day:'2-digit', month:'2-digit', year:'numeric'}); }
    // Дедлайн — момент (datetime): дата+время в локальном поясе смотрящего.
    function fmtDeadline(d) { if (!d) return '—'; return new Date(d).toLocaleString('ru-RU', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}); }
    function formatCurrency(v, currency='AZN') { if (v==null||v===''||isNaN(v)) return '—'; return new Intl.NumberFormat('ru-RU', {minimumFractionDigits:0, maximumFractionDigits:2}).format(parseFloat(v)) + ' ' + (currency||'AZN'); }
    function escHtml(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
@endpush
