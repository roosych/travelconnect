@extends('layouts.app')

@section('title', __('offers.show.title'))
@section('page-title', __('offers.show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.offers.index') }}" class="text-muted text-hover-primary">{{ __('offers.breadcrumb') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('offers.show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@section('toolbar-actions')
    <div id="offer-actions" class="d-flex gap-2"></div>
@endsection

@section('content')

{{-- Универсальный модал подтверждения --}}
<div class="modal fade" id="modal-confirm-action" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('offers.confirm.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-5 fs-6 text-gray-700" id="confirm-action-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-action-ok">{{ __('offers.confirm.ok') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="row g-6">

    {{-- Main offer card --}}
    <div class="col-lg-8">
        <div class="card card-flush mb-6" id="offer-header-card">
            <div class="card-body py-8">
                <div class="text-center py-6"><span class="spinner-border text-primary"></span></div>
            </div>
        </div>

        {{-- Attachments --}}
        @include('components.attachments', [
            'entityType' => 'offers',
            'entityId'   => 'offerId',
            'canUpload'  => false,
        ])
    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">

        {{-- Supplier card --}}
        <div class="card card-flush mb-6" id="offer-supplier-card">
            <div class="card-header py-4">
                <div class="card-title">
                    <h4 class="fw-bold fs-6 mb-0">{{ __('offers.show.supplier_card') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="text-center py-6"><span class="spinner-border text-primary spinner-border-sm"></span></div>
            </div>
        </div>

        {{-- Context card --}}
        <div class="card card-flush mb-6" id="offer-context-card">
            <div class="card-header py-4">
                <div class="card-title">
                    <h4 class="fw-bold fs-6 mb-0">{{ __('offers.show.request_card') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="text-center py-6"><span class="spinner-border text-primary spinner-border-sm"></span></div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    const offerId = {{ $id }};
    const t  = @json(__('offers'));
    const tc = @json(__('common'));
    const USER_TZ = @json($userTimezone);
    // Момент в поясе смотрящего + GMT-метка.
    const fmtDT = (d) => window.formatDateTimeTz(d, USER_TZ);
    let currentOffer = null;
    let confirmActionCallback = null;

    (async function load() {
        try {
            const data = await api.get(`/offers/${offerId}`);
            currentOffer = data.data ?? data;
            renderOffer(currentOffer);
            loadAttachments('offers', offerId, false);
        } catch (err) {
            document.getElementById('offer-header-card').querySelector('.card-body').innerHTML =
                `<div class="alert alert-danger">${t.show.load_error}</div>`;
        }
    })();

    document.getElementById('btn-confirm-action-ok').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('modal-confirm-action')).hide();
        if (confirmActionCallback) confirmActionCallback();
        confirmActionCallback = null;
    });

    function showConfirm(message, callback, btnLabel = t.confirm.ok) {
        document.getElementById('confirm-action-body').textContent = message;
        document.getElementById('btn-confirm-action-ok').textContent = btnLabel;
        confirmActionCallback = callback;
        new bootstrap.Modal(document.getElementById('modal-confirm-action')).show();
    }

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary', icon: 'ki-abstract-26', cls: 'badge-light-secondary' }]));


    function renderOffer(offer) {
        renderToolbar(offer);
        renderHeaderCard(offer);
        renderSupplierCard(offer);
        renderContextCard(offer);
    }

    function renderToolbar(offer) {
        const el = document.getElementById('offer-actions');
        const buttons = [];

        if (offer.status === 'received' || offer.status === 'reviewed') {
            const reqId = offer.rfq?.request?.id;
            if (reqId) {
                // Сборка предложения живёт на странице заявки (вкладка «Предложения
                // от поставщиков») — ведём туда с предвыбором этого оффера.
                buttons.push(`
                    <a href="/admin/requests/${reqId}?offer=${offer.id}" class="btn btn-sm btn-primary">
                        <i class="ki-outline ki-plus-square fs-2"></i> ${t.show.add_to_proposal}
                    </a>`);
            }
            buttons.push(`
                <button class="btn btn-sm btn-light-danger" onclick="rejectOffer()">
                    <i class="ki-outline ki-cross-circle fs-2"></i> ${t.show.reject}
                </button>`);
        }

        el.innerHTML = buttons.join('');
    }

    function renderHeaderCard(offer) {
        const items     = Array.isArray(offer.items) ? offer.items : [];
        const covered   = Array.isArray(offer.covered_services) ? offer.covered_services : [];
        const uncovered = Array.isArray(offer.uncovered_services) ? offer.uncovered_services : [];
        const currency  = offer.currency ?? 'AZN';

        const priceByType = {};
        items.forEach(item => { priceByType[item.type] = item; });

        const coveredRows = covered.map(s => {
            const m    = SERVICE_META[s] ?? { label: s, icon: 'ki-abstract-26', color: 'secondary' };
            const item = priceByType[s];
            // Fallback to offer.unit_price when no item rows exist (single-service offer)
            const price = item != null ? item.unit_price
                        : (covered.length === 1 ? (offer.unit_price ?? null) : null);
            return `
            <div class="d-flex align-items-center gap-3 py-3" >
                <span class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light-${m.color} flex-shrink-0">
                    <i class="ki-outline ${m.icon} fs-5 text-${m.color}"></i>
                </span>
                <span class="flex-grow-1 fw-semibold text-gray-800">${m.label}</span>
                ${item?.name && item.name !== s
                    ? `<span class="text-muted fs-7 me-4">${escHtml(item.name)}</span>`
                    : ''}
                <span class="fw-bold text-gray-900 text-nowrap">
                    ${price != null ? formatCurrency(price, currency) : '—'}
                </span>
            </div>`;
        }).join('');

        const uncoveredRows = uncovered.map(s => {
            const m = SERVICE_META[s] ?? { label: s, icon: 'ki-abstract-26', color: 'secondary' };
            return `
            <div class="d-flex align-items-center gap-3 py-3 opacity-50" >
                <span class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light flex-shrink-0">
                    <i class="ki-outline ${m.icon} fs-5 text-gray-400"></i>
                </span>
                <span class="flex-grow-1 fw-semibold text-gray-500">${m.label}</span>
                <span class="badge badge-light-danger fs-8">${t.labels.not_covered}</span>
            </div>`;
        }).join('');

        const totalPrice = items.length
            ? items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0)
            : parseFloat(offer.unit_price ?? 0);

        // Catalog resource block (shown when supplier picked a service from catalog)
        const catalogItem = items.find(i => i.supplier_service_id && (i.catalog_photos?.length || i.catalog_name || i.catalog_description));
        const catalogBlock = catalogItem ? `
            <div class="separator my-4"></div>
            <div>
                <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">
                    <i class="ki-outline ki-archive fs-6 me-1 text-muted"></i>${t.show.catalog_resource}
                </div>
                ${catalogItem.catalog_photos?.length ? `
                <div class="d-flex gap-2 mb-3" style="overflow-x:auto;">
                    ${catalogItem.catalog_photos.map(url =>
                        `<a href="${url}" class="glightbox flex-shrink-0" data-gallery="catalog-resource">
                            <img src="${url}" alt="" class="rounded" style="height:80px;width:110px;object-fit:cover;cursor:pointer;">
                        </a>`
                    ).join('')}
                </div>` : ''}
                ${catalogItem.catalog_name ? `<div class="fw-semibold text-gray-800 fs-6 mb-1">${escHtml(catalogItem.catalog_name)}</div>` : ''}
                ${catalogItem.catalog_description ? `<div class="text-gray-600 fs-7 lh-base">${escHtml(catalogItem.catalog_description)}</div>` : ''}
            </div>` : '';

        const totalRow = covered.length > 1 ? `
            <div class="d-flex align-items-center justify-content-end gap-3 pt-3">
                <span class="text-muted fs-7">${t.labels.total}</span>
                <span class="fw-bold fs-4 text-gray-900">${formatCurrency(totalPrice, currency)}</span>
            </div>` : '';

        const expiredBadge = offer.is_expired ? `<span class="badge badge-light-danger ms-2">${t.labels.expired}</span>` : '';
        const partialBadge = offer.is_partial  ? `<span class="badge badge-light-warning ms-2">${t.labels.partial}</span>` : '';

        document.getElementById('offer-header-card')._lightbox?.destroy();
        document.getElementById('offer-header-card').innerHTML = `
            <div class="card-body py-7">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-6">
                    <div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h2 class="fw-bold text-gray-900 mb-0 fs-3">${t.show.offer_title.replace(':id', offer.id)}</h2>
                            ${statusBadge(offer)}${partialBadge}${expiredBadge}
                        </div>
                        <div class="text-muted fs-6">
                            ${t.show.submitted_by}
                            <a href="/admin/suppliers/${offer.supplier?.id ?? ''}" class="fw-semibold text-gray-800 text-hover-primary ms-1">
                                ${escHtml(offer.supplier?.name ?? '—')}
                            </a>
                        </div>
                        <div class="text-muted fs-8 mt-1">${t.labels.received_at.replace(':date', fmtDT(offer.created_at))}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted fs-8 mb-1">${t.labels.valid_until}</div>
                        <div class="fw-bold fs-6 ${offer.is_expired ? 'text-danger' : 'text-gray-800'}">
                            ${offer.valid_until ? fmtDT(offer.valid_until) : '—'}
                        </div>
                    </div>
                </div>

                ${covered.length || uncovered.length ? `
                <div class="mb-5">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">${t.labels.services_prices}</div>
                    ${coveredRows}${uncoveredRows}
                    ${covered.length > 1 ? totalRow : ''}
                    ${catalogBlock}
                </div>` : `
                <div class="mb-5 text-muted fs-6">${t.labels.prices_none}</div>`}

                ${offer.notes ? `
                <div class="separator mb-5"></div>
                <div class="mb-0">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-2">${t.labels.notes}</div>
                    <div class="text-gray-700 fs-6">${escHtml(offer.notes)}</div>
                </div>` : ''}
            </div>`;

        if (catalogItem?.catalog_photos?.length) {
            const lb = GLightbox({ selector: '#offer-header-card .glightbox', loop: true });
            document.getElementById('offer-header-card')._lightbox = lb;
        }
    }

    function renderSupplierCard(offer) {
        const s = offer.supplier;
        const cardBody = document.getElementById('offer-supplier-card').querySelector('.card-body');

        if (!s) {
            cardBody.innerHTML = `<span class="text-muted fs-7">${t.show.supplier_unavailable}</span>`;
            return;
        }

        const initials = (s.name ?? '?').trim().split(/\s+/).filter(Boolean)
            .slice(0, 2).map(w => w[0].toUpperCase()).join('');

        cardBody.innerHTML = `
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="symbol symbol-45px symbol-circle flex-shrink-0">
                    ${s.avatar_url
                        ? `<img src="${escHtml(s.avatar_url)}" class="rounded-circle" />`
                        : `<div class="symbol-label bg-light-primary text-primary fw-bold fs-5">${initials}</div>`
                    }
                </div>
                <div class="min-w-0">
                    <a href="/admin/suppliers/${s.id}" class="fw-bold text-gray-800 text-hover-primary d-block text-truncate">${escHtml(s.name)}</a>
                    ${s.company_name ? `<div class="text-muted fs-7 text-truncate">${escHtml(s.company_name)}</div>` : ''}
                </div>
            </div>
            <div class="separator mb-4"></div>
            <div class="d-flex flex-column gap-3">
                ${s.email ? `
                <div class="d-flex align-items-center gap-2">
                    <i class="ki-outline ki-sms fs-5 text-primary flex-shrink-0"></i>
                    <a href="mailto:${escHtml(s.email)}" class="text-gray-700 text-hover-primary fs-7 text-break">${escHtml(s.email)}</a>
                </div>` : ''}
                ${s.phone ? `
                <div class="d-flex align-items-center gap-2">
                    <i class="ki-outline ki-phone fs-5 text-success flex-shrink-0"></i>
                    <span class="text-gray-700 fs-7">${escHtml(s.phone)}</span>
                </div>` : ''}
            </div>
            <div class="mt-4">
                <a href="/admin/suppliers/${s.id}" class="btn btn-light-primary btn-sm w-100">
                    <i class="ki-outline ki-arrow-right fs-5 me-1"></i>${t.show.supplier_profile}
                </a>
            </div>`;
    }

    function renderContextCard(offer) {
        const rfq      = offer.rfq;
        const req      = rfq?.request;
        const cardBody = document.getElementById('offer-context-card').querySelector('.card-body');

        if (!req) {
            cardBody.innerHTML = `<span class="text-muted fs-7">${t.show.context_unavailable}</span>`;
            return;
        }

        const svcMeta   = SERVICE_META[rfq?.service_type] ?? { label: rfq?.service_type ?? '—', icon: 'ki-abstract-26', color: 'secondary' };
        const agencyName = req.agency?.company_name ?? req.agency?.name;

        const dateFrom = req.travel_date_from ? formatDate(req.travel_date_from) : null;
        const dateTo   = req.travel_date_to   ? formatDate(req.travel_date_to)   : null;
        const dateStr  = dateFrom && dateTo ? `${dateFrom} — ${dateTo}` : (dateFrom ?? dateTo ?? null);

        cardBody.innerHTML = `
            <div class="d-flex flex-column gap-3">

                ${agencyName ? `
                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center justify-content-center w-32px h-32px rounded-circle bg-light-primary flex-shrink-0">
                        <i class="ki-outline ki-building fs-6 text-primary"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="text-muted fs-8">${t.show.agency}</div>
                        ${req.agency?.id
                            ? `<a href="/admin/agencies/${req.agency.id}" class="fw-semibold text-gray-800 text-hover-primary fs-7 d-block text-truncate">${escHtml(agencyName)}</a>`
                            : `<div class="fw-semibold text-gray-800 fs-7">${escHtml(agencyName)}</div>`}
                    </div>
                </div>` : ''}

                <div class="separator"></div>

                <div class="d-flex align-items-start gap-3">
                    <span class="d-flex align-items-center justify-content-center w-32px h-32px rounded-circle bg-light-info flex-shrink-0 mt-1">
                        <i class="ki-outline ki-document fs-6 text-info"></i>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="text-muted fs-8">${t.show.request}</div>
                        <a href="/admin/requests/${req.id}" class="fw-semibold text-gray-800 text-hover-primary fs-7 d-block lh-sm mb-2">
                            ${escHtml(req.title ?? t.show.request_ref.replace(':id', req.id))}
                        </a>
                        <div class="d-flex flex-column gap-1">
                            ${rfq?.country_code ? `
                            <div class="d-flex align-items-center gap-2 fs-8">
                                ${rfq.country_flag ? `<img src="${rfq.country_flag}" style="width:18px;height:13px;object-fit:cover;border-radius:2px" onerror="this.remove()">` : `<i class="ki-outline ki-geolocation fs-7 flex-shrink-0 text-muted"></i>`}
                                <span class="fw-semibold text-gray-700">${escHtml(rfq.country_name ?? rfq.country_code)}</span>
                            </div>` : (req.destination ? `
                            <div class="d-flex align-items-center gap-2 text-muted fs-8">
                                <i class="ki-outline ki-geolocation fs-7 flex-shrink-0"></i>
                                <span>${escHtml(req.destination)}</span>
                            </div>` : '')}
                            ${dateStr ? `
                            <div class="d-flex align-items-center gap-2 text-muted fs-8">
                                <i class="ki-outline ki-calendar fs-7 flex-shrink-0"></i>
                                <span>${dateStr}</span>
                            </div>` : ''}
                            ${req.pax_count ? `
                            <div class="d-flex align-items-center gap-2 text-muted fs-8">
                                <i class="ki-outline ki-people fs-7 flex-shrink-0"></i>
                                <span>${t.show.pax.replace(':n', req.pax_count)}</span>
                            </div>` : ''}
                        </div>
                        <div class="mt-2">
                            <span class="badge ${req.status_badge_class} fs-9">${escHtml(req.status_label ?? req.status ?? '')}</span>
                        </div>
                    </div>
                </div>

                <div class="separator"></div>

                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center justify-content-center w-32px h-32px rounded-circle bg-light-${svcMeta.color} flex-shrink-0">
                        <i class="ki-outline ${svcMeta.icon} fs-6 text-${svcMeta.color}"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="text-muted fs-8">${t.show.service_type}</div>
                        <div class="fw-semibold text-gray-800 fs-7">${escHtml(svcMeta.label)}</div>
                    </div>
                </div>

            </div>`;
    }

    // ---- Actions ----

    function rejectOffer() {
        showConfirm(t.show.confirm_reject, async () => {
            try {
                const d = await api.patch(`/offers/${offerId}/reject`);
                const updated = d.data ?? d;
                if (updated?.id) {
                    showToast(t.toast.rejected);
                    currentOffer = { ...currentOffer, status: 'rejected' };
                    renderOffer(currentOffer);
                } else {
                    showToast(d.message ?? t.toast.error, 'error');
                }
            } catch (e) {
                showToast(e.message ?? t.toast.error, 'error');
            }
        }, t.show.reject);
    }

    // ---- Helpers ----
    function statusBadge(offer) {
        if (offer.status_label) {
            return `<span class="badge ${offer.status_badge_class}">${escHtml(offer.status_label)}</span>`;
        }
        return `<span class="badge badge-light-secondary">${escHtml(offer.status ?? '—')}</span>`;
    }

    function formatDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDateTime(d) {
        if (!d) return '—';
        return new Date(d).toLocaleString('ru-RU', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    }

    function formatCurrency(v, currency) {
        if (v == null || v === '' || isNaN(v)) return '—';
        return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v)) + ' ' + (currency || 'AZN');
    }

    function escHtml(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>
@endpush
