@extends('layouts.app')

@section('title', __('proposals.show.title'))
@section('page-title', __('proposals.show.title'))

@push('styles')
<style>
input[type=number].form-control-solid::-webkit-outer-spin-button,
input[type=number].form-control-solid::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number].form-control-solid { -moz-appearance: textfield; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.proposals.index') }}" class="text-muted text-hover-primary">{{ __('proposals.title') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('proposals.show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@section('toolbar-actions')
    <div id="proposal-actions" class="d-flex gap-2 align-items-center"></div>
@endsection

@section('content')

{{-- Offer quick-view drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offer-drawer" style="width:480px">
    <div class="offcanvas-header border-bottom py-5 px-7">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1" id="drawer-offer-supplier">{{ __('proposals.show.offer_ph') }}</h5>
            <span id="drawer-offer-service"></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-7" id="drawer-offer-body">
        <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
    </div>
</div>

{{-- ===================== HEADER CARD ===================== --}}
<div id="proposal-header-card" class="card card-flush mb-6">
    <div class="card-body py-7">
        <div class="text-center py-6"><span class="spinner-border text-success"></span></div>
    </div>
</div>

<div class="row g-6">

    {{-- ===================== INCLUDED OFFERS (left) ===================== --}}
    <div id="included-col" class="col-lg-8">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0">
                        {{ __('proposals.show.included_title') }}
                        <span class="badge badge-light-success ms-2" id="included-count">0</span>
                    </h3>
                </div>
            </div>
            <div class="card-body pt-0 px-0">
                <div id="included-offers-container">
                    <div class="text-center py-8"><span class="spinner-border text-success"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== RIGHT PANEL (col-4) ===================== --}}
    <div class="col-lg-4">
        <div class="card card-flush mb-6" id="right-panel-card">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <h3 class="card-label fw-bold fs-5 mb-0" id="right-panel-title">...</h3>
                </div>
            </div>
            <div class="card-body pt-0" id="right-panel-body">
                <div class="text-center py-8"><span class="spinner-border text-warning"></span></div>
            </div>
        </div>

        @include('components.attachments', [
            'entityType' => 'proposals',
            'entityId'   => 'proposalId',
            'canUpload'  => true,
            'colClass'   => 'col-6',
        ])
    </div>

</div>

@endsection

@push('scripts')
<script>
const proposalId = @json($id);
const t  = @json(__('proposals'));
const tc = @json(__('common'));
const ts = t.show;
let proposal     = null;
let includedOffers  = [];
let availableOffers = [];
let markupSettings  = {};

// Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
    [k, { label: v, cls: 'badge-light-secondary', color: 'secondary', icon: 'ki-abstract-26' }]));

(async function init() {
    await loadProposal();
    const isDraft = proposal?.status === 'draft';
    loadAttachments('proposals', proposalId, isDraft);
    if (isDraft) {
        initFilepond('proposals', () => proposalId, isDraft);
    } else {
        document.getElementById('filepond-proposals')?.closest('.mb-6')?.remove();
    }
})();

// =============================================================================
// LOAD
// =============================================================================

async function loadProposal() {
    try {
        const data = await api.get(`/proposals/${proposalId}`);
        proposal = data.data ?? data;

        includedOffers = proposal.offers ?? [];

        renderHeaderCard(proposal);
        renderToolbarActions(proposal);
        renderIncludedOffers(includedOffers);
        renderRightPanel(proposal);

        if (proposal.status === 'draft' && proposal.request?.status !== 'booked') {
            await loadAvailableOffers();
        } else if (proposal.status === 'draft' && proposal.request?.status === 'booked') {
            renderRightPanelBooked();
        }
    } catch (err) {
        document.getElementById('proposal-header-card').querySelector('.card-body').innerHTML =
            `<div class="alert alert-danger">${ts.load_error}</div>`;
    }
}

async function loadAvailableOffers() {
    if (!proposal) return;

    const requestId = proposal.request?.id;
    if (!requestId) {
        renderRightPanelEmpty(ts.avail.no_request_link);
        return;
    }

    try {
        const [settingsData, rfqData] = await Promise.all([
            api.get('/settings/service-types/markups'),
            api.get(`/requests/${requestId}/rfqs`),
        ]);
        markupSettings = settingsData.data ?? {};

        const rfqs = rfqData.data ?? rfqData ?? [];
        const groups = await Promise.all(
            rfqs.map(rfq =>
                api.get(`/rfqs/${rfq.id}/offers`).then(d => {
                    const offers = d.data ?? d ?? [];
                    offers.forEach(o => { o._rfq = rfq; });
                    return offers;
                }).catch(() => [])
            )
        );

        const includedIds = new Set(includedOffers.map(o => o.id));
        availableOffers = groups.flat().filter(o =>
            !includedIds.has(o.id) && !['rejected', 'withdrawn'].includes(o.status)
        );

        renderAvailableOffers(availableOffers);
    } catch (err) {
        renderRightPanelEmpty(ts.avail.load_error);
    }
}

// =============================================================================
// RENDER: HEADER CARD
// =============================================================================

function renderHeaderCard(p) {
    const netTotal    = includedOffers.reduce((s, o) => s + parseFloat(o.unit_price ?? 0), 0);
    const grossTotal  = includedOffers.reduce((s, o) => s + parseFloat(o.price_with_markup ?? o.unit_price ?? 0), 0);
    const markupTotal = grossTotal - netTotal;
    const count       = includedOffers.length;

    const requestLink = p.request?.id
        ? `<a href="/admin/requests/${p.request.id}" class="text-primary fw-semibold text-hover-primary">
               <i class="ki-outline ki-arrow-left fs-6 me-1"></i>${escHtml(p.request?.title ?? ts.request_ref.replace(':id', p.request.id))}
           </a>`
        : '<span class="text-muted">—</span>';

    // Services coverage
    const needed  = p.request?.services_needed ?? [];
    const covered = new Set(
        includedOffers.flatMap(o =>
            Array.isArray(o.items) && o.items.length
                ? o.items.map(i => i.type)
                : [o.rfq_service_type]
        ).filter(Boolean)
    );
    const servicesBadges = needed.length
        ? needed.map(s => {
              const m    = SERVICE_META[s] ?? { label: s, cls: 'badge-light-secondary' };
              const done = covered.has(s);
              return done
                  ? `<span class="badge badge-light-success py-2 px-3"><i class="ki-outline ki-check fs-8 me-1"></i>${escHtml(m.label)}</span>`
                  : `<span class="badge badge-light-secondary py-2 px-3 opacity-75"><i class="ki-outline ki-minus-circle fs-8 me-1"></i>${escHtml(m.label)}</span>`;
          }).join('')
        : '';

    const validUntil = p.valid_until
        ? `<span class="${p.is_expired ? 'text-danger fw-bold' : 'text-gray-700'}">${p.is_expired ? '<i class="ki-outline ki-warning-2 fs-6 me-1"></i>' : ''}${formatDate(p.valid_until)}</span>`
        : '<span class="text-muted">—</span>';

    document.getElementById('proposal-header-card').innerHTML = `
        <div class="card-body py-7">
            <div class="row g-6 align-items-start">

                {{-- Left: title + meta --}}
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h2 class="fw-bold text-gray-900 mb-0 fs-3">${escHtml(p.title ?? ts.proposal_ref.replace(':id', p.id))}</h2>
                        ${statusBadge(p.status)}
                    </div>

                    <div class="mb-3">${requestLink}</div>

                    ${p.description
                        ? `<p class="text-gray-600 fs-6 mb-4">${escHtml(p.description)}</p>`
                        : ''}

                    <div class="d-flex flex-wrap gap-5 fs-7 text-gray-600 mb-4">
                        <span class="d-flex align-items-center gap-1">
                            <i class="ki-outline ki-profile-circle fs-5 text-muted"></i>
                            <span>${ts.header.operator} <span class="fw-semibold text-gray-800">${escHtml(p.operator?.name ?? '—')}</span></span>
                        </span>
                        <span class="d-flex align-items-center gap-1">
                            <i class="ki-outline ki-calendar fs-5 text-muted"></i>
                            <span>${ts.header.created} <span class="fw-semibold text-gray-800">${formatDate(p.created_at)}</span></span>
                        </span>
                        <span class="d-flex align-items-center gap-1">
                            <i class="ki-outline ki-timer fs-5 text-muted"></i>
                            <span>${ts.header.valid_until} ${validUntil}</span>
                        </span>
                    </div>

                    ${servicesBadges ? `<div class="d-flex flex-wrap gap-2">${servicesBadges}</div>` : ''}
                </div>

                {{-- Right: financial stats --}}
                <div class="col-lg-5">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="bg-light-secondary rounded-2 p-4 text-center">
                                <div class="fs-3 fw-bold text-gray-900">${count}</div>
                                <div class="text-muted fs-7 mt-1">${ts.stats.count}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light-primary rounded-2 p-4 text-center">
                                <div class="fs-3 fw-bold text-primary">${formatCurrency(netTotal)}</div>
                                <div class="text-muted fs-7 mt-1">${ts.stats.net}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light-success rounded-2 p-4 text-center">
                                <div class="fs-3 fw-bold text-success">+${formatCurrency(markupTotal)}</div>
                                <div class="text-muted fs-7 mt-1">${ts.stats.markup}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-2 p-4 text-center">
                                <div class="fs-3 fw-bold text-gray-900">${formatCurrency(grossTotal)}</div>
                                <div class="text-muted fs-7 mt-1">${ts.stats.total}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>`;
}

// =============================================================================
// RENDER: TOOLBAR
// =============================================================================

function renderToolbarActions(p) {
    const el = document.getElementById('proposal-actions');
    const requestBooked = p.request?.status === 'booked';

    if (p.status === 'draft') {
        el.innerHTML = requestBooked
            ? `<span class="badge badge-light-warning fs-7 py-2 px-4">
                   <i class="ki-outline ki-lock-2 fs-6 me-1"></i>${ts.toolbar.booked_badge}
               </span>`
            : `<button class="btn btn-sm btn-success" onclick="sendProposal()">
                   <i class="ki-outline ki-send fs-2"></i> ${ts.toolbar.send}
               </button>
               <button class="btn btn-sm btn-light-danger ms-2" onclick="cancelProposal()">
                   <i class="ki-outline ki-cross-circle fs-2"></i> ${ts.toolbar.cancel}
               </button>`;
    } else if (p.status === 'sent') {
        el.innerHTML = `
            <button class="btn btn-sm btn-light-danger" onclick="cancelProposal()">
                <i class="ki-outline ki-cross-circle fs-2"></i> ${ts.toolbar.cancel_sent}
            </button>`;
    } else {
        el.innerHTML = '';
    }
}

// =============================================================================
// RENDER: INCLUDED OFFERS TABLE
// =============================================================================

function renderIncludedOffers(offers) {
    const container = document.getElementById('included-offers-container');

    if (!offers.length) {
        container.innerHTML = `
            <div class="text-center py-12 px-6">
                <i class="ki-outline ki-tag fs-3x text-gray-300 mb-4 d-block"></i>
                <span class="text-muted fs-6">${ts.included_empty}</span>
            </div>`;
        return;
    }

    let totalNet   = 0;
    let totalGross = 0;
    let rowCount   = 0;

    const rows = offers.map(o => {
        const supplierId   = o.supplier?.id ?? '';
        const supplierName = o.supplier?.company_name ?? o.supplier?.name ?? '—';
        const offerMarkup  = parseFloat(o.markup_pct ?? 0);

        const validUntil = o.valid_until
            ? `<span class="${o.is_expired ? 'text-danger fw-bold' : 'text-gray-700'} fs-7">${o.is_expired ? '⚠ ' : ''}${formatDate(o.valid_until)}</span>`
            : '<span class="text-muted fs-7">—</span>';

        const hasItems = Array.isArray(o.items) && o.items.length > 0;

        if (hasItems) {
            return o.items.map((item, idx) => {
                const serviceType = item.type ?? o.rfq_service_type ?? 'other';
                const serviceMeta = SERVICE_META[serviceType] ?? SERVICE_META.other;
                const net   = parseFloat(item.line_total ?? item.unit_price ?? 0);
                const gross = net * (1 + offerMarkup / 100);
                const markup = gross - net;

                totalNet   += net;
                totalGross += gross;
                rowCount++;

                const isFirst = idx === 0;

                const removeBtn = proposal?.status === 'draft' && proposal?.request?.status !== 'booked'
                    ? (isFirst
                        ? `<button class="btn btn-sm btn-icon btn-light-danger ms-1" onclick="removeOffer('${o.id}')"
                               title="${ts.table.remove_whole}">
                               <i class="ki-outline ki-trash fs-5"></i>
                           </button>`
                        : `<span style="width:32px;display:inline-block"></span>`)
                    : (isFirst ? statusBadge(o.status) : '');

                return `
                <tr class="align-middle${isFirst ? ' border-top' : ''}">
                    <td class="ps-4">
                        <span class="badge ${serviceMeta.cls} fs-8 py-1 px-2 d-inline-block mb-1">${serviceMeta.label}</span>
                        <div class="fw-semibold fs-6">
                            <a href="/admin/suppliers/${supplierId}" class="text-gray-800 text-hover-primary">${escHtml(supplierName)}</a>
                        </div>
                        {{-- <div class="text-muted fs-8">${escHtml((item.name && item.name !== item.type) ? item.name : (SERVICE_META[item.type]?.label ?? item.name ?? '—'))}</div> --}}
                    </td>
                    <td class="text-end text-gray-700 fw-semibold">${formatCurrency(net)}</td>
                    <td class="text-end text-success fw-semibold">+${formatCurrency(markup)}</td>
                    <td class="text-end fw-bold text-gray-900">${formatCurrency(gross)}</td>
                    <td>${isFirst ? validUntil : ''}</td>
                    <td class="pe-4 text-end">
                        ${isFirst ? `<button class="btn btn-sm btn-icon btn-light" onclick="openOfferDrawer('${o.id}')" title="${ts.quick_view}">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </button>` : ''}
                        ${removeBtn}
                    </td>
                </tr>`;
            }).join('');
        }

        // Offer without items — single row
        const net    = parseFloat(o.unit_price ?? 0);
        const gross  = parseFloat(o.price_with_markup ?? net * (1 + offerMarkup / 100));
        const markup = gross - net;
        const serviceType = o.rfq_service_type ?? 'other';
        const serviceMeta = SERVICE_META[serviceType] ?? SERVICE_META.other;

        totalNet   += net;
        totalGross += gross;
        rowCount++;

        const removeBtn = proposal?.status === 'draft' && proposal?.request?.status !== 'booked'
            ? `<button class="btn btn-sm btn-icon btn-light-danger ms-1" onclick="removeOffer('${o.id}')" title="${ts.table.remove}">
                   <i class="ki-outline ki-trash fs-5"></i>
               </button>`
            : statusBadge(o.status);

        return `
        <tr class="align-middle border-top">
            <td class="ps-4">
                <span class="badge ${serviceMeta.cls} fs-8 py-1 px-2 d-inline-block mb-1">${serviceMeta.label}</span>
                <div class="fw-semibold fs-6">
                    <a href="/admin/suppliers/${supplierId}" class="text-gray-800 text-hover-primary">${escHtml(supplierName)}</a>
                </div>
                {{-- <div class="text-muted fs-8">${escHtml(o.rfq_title ?? '')}</div> --}}
            </td>
            <td class="text-end text-gray-700 fw-semibold">${formatCurrency(net)}</td>
            <td class="text-end text-success fw-semibold">+${formatCurrency(markup)}</td>
            <td class="text-end fw-bold text-gray-900">${formatCurrency(gross)}</td>
            <td>${validUntil}</td>
            <td class="pe-4 text-end">
                <button class="btn btn-sm btn-icon btn-light" onclick="openOfferDrawer('${o.id}')" title="${ts.quick_view}">
                    <i class="ki-outline ki-eye fs-4"></i>
                </button>
                ${removeBtn}
            </td>
        </tr>`;
    }).join('');

    const totalMarkup = totalGross - totalNet;

    container.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-3 mb-0">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-8 text-uppercase gs-0">
                        <th class="ps-4 min-w-200px">${ts.table.service_supplier}</th>
                        <th class="text-end min-w-90px">${ts.table.net}</th>
                        <th class="text-end min-w-90px">${ts.table.markup}</th>
                        <th class="text-end min-w-100px">${ts.table.total}</th>
                        <th class="min-w-90px">${ts.table.deadline}</th>
                        <th class="pe-4 text-end min-w-100px">${ts.table.action}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
                <tfoot class="border-top border-gray-200">
                    <tr class="fw-bold text-gray-800">
                        <td class="pt-4"></td>
                        <td class="text-end pt-4 fs-6 fw-bold text-gray-900">${ts.table.footer_total}</td>
                        <td class="text-end pt-4 text-success">+${formatCurrency(totalMarkup)}</td>
                        <td class="text-end pt-4 text-gray-900 fw-bold fs-5">${formatCurrency(totalGross)}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;

    document.getElementById('included-count').textContent = rowCount;
}

// =============================================================================
// RENDER: RIGHT PANEL
// =============================================================================

function renderRightPanel(p) {
    document.getElementById('right-panel-title').textContent =
        p.status === 'draft' ? ts.panel.available_title : ts.panel.details_title;

    if (p.status !== 'draft') {
        renderProposalDetailsPanel(p);
    }
}

function renderProposalDetailsPanel(p) {
    const el = document.getElementById('right-panel-body');

    const statusMap = {
        draft:    { cls: 'badge-light-secondary', label: ts.status.draft },
        sent:     { cls: 'badge-light-primary',   label: ts.status.sent },
        accepted: { cls: 'badge-light-success',   label: ts.status.accepted },
        rejected: { cls: 'badge-light-danger',    label: ts.status.rejected },
        expired:  { cls: 'badge-light-dark',      label: ts.status.expired },
        cancelled:{ cls: 'badge-light-dark',      label: ts.status.cancelled },
    };
    const sm = statusMap[p.status] ?? { cls: 'badge-light-secondary', label: p.status };

    el.innerHTML = `
        <div class="d-flex flex-column gap-5">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-4">
                <span class="text-gray-500 fs-7 fw-semibold">${ts.panel.status_label}</span>
                <span class="badge ${sm.cls} fs-7">${sm.label}</span>
            </div>

            ${p.description ? `
            <div>
                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">${ts.panel.description}</div>
                <p class="text-gray-700 fs-6 mb-0">${escHtml(p.description)}</p>
            </div>` : ''}

            <div>
                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-3">${ts.panel.financial}</div>
                <div class="d-flex flex-column gap-2">
                    ${buildFinancialRow(ts.fin.count, ts.fin.unit.replace(':n', includedOffers.length))}
                    ${buildFinancialRow(ts.fin.net,    formatCurrency(includedOffers.reduce((s,o)=>s+parseFloat(o.unit_price??0),0)))}
                    ${buildFinancialRow(ts.fin.markup, '+' + formatCurrency(includedOffers.reduce((s,o)=>s+(parseFloat(o.price_with_markup??o.unit_price??0)-parseFloat(o.unit_price??0)),0)))}
                    ${buildFinancialRow(ts.fin.total,  formatCurrency(includedOffers.reduce((s,o)=>s+parseFloat(o.price_with_markup??o.unit_price??0),0)), 'text-gray-900 fw-bold')}
                </div>
            </div>

            ${(p.agency_amount != null && p.agency_currency && p.agency_currency !== 'AZN') ? `
            <div class="bg-light-primary rounded-2 p-4">
                <div class="text-primary fs-8 fw-bold text-uppercase mb-2">${ts.panel.agency_amount}</div>
                <div class="text-gray-800 fw-bold fs-6 mb-2">${formatCurrency(p.agency_amount, p.agency_currency)}</div>
                <div class="fs-4 fw-bold text-primary">${formatCurrency(p.amount_azn, 'AZN')}</div>
                ${p.exchange_rate_snapshot
                    ? `<div class="text-muted fs-8">${ts.panel.rate.replace(':cur', escHtml(p.agency_currency)).replace(':rate', parseFloat(p.exchange_rate_snapshot).toFixed(4))} <span class="badge badge-light-success py-1 px-2 ms-1 fs-9">${ts.panel.rate_fixed}</span></div>`
                    : `<div class="text-muted fs-8">${ts.panel.total_in.replace(':cur', escHtml(p.agency_currency))}</div>`
                }
            </div>` : ''}

            <div>
                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-3">${ts.panel.info}</div>
                <div class="d-flex flex-column gap-2">
                    ${buildMetaRow(ts.meta.operator,    p.operator?.name ?? '—')}
                    ${buildMetaRow(ts.meta.currency,    p.currency ?? 'USD')}
                    ${buildMetaRow(ts.meta.valid_until, p.valid_until ? (p.is_expired ? '<span class="text-danger">'+formatDate(p.valid_until)+' '+ts.meta.expired_suffix+'</span>' : formatDate(p.valid_until)) : '—')}
                    ${buildMetaRow(ts.meta.created,     formatDate(p.created_at))}
                </div>
            </div>

            ${p.request?.id ? `
            <a href="/admin/requests/${p.request.id}" class="btn btn-light-primary btn-sm">
                <i class="ki-outline ki-arrow-left fs-5 me-1"></i>${ts.panel.back_to_request}
            </a>` : ''}

        </div>`;
}

function buildFinancialRow(label, value, valueCls = 'text-gray-800 fw-semibold') {
    return `<div class="d-flex justify-content-between align-items-center">
                <span class="text-gray-500 fs-7">${label}</span>
                <span class="fs-7 ${valueCls}">${value}</span>
            </div>`;
}

function buildMetaRow(label, value) {
    return `<div class="d-flex justify-content-between align-items-center">
                <span class="text-gray-500 fs-7">${label}</span>
                <span class="text-gray-700 fs-7">${value}</span>
            </div>`;
}

function renderRightPanelEmpty(msg) {
    document.getElementById('right-panel-body').innerHTML =
        `<span class="text-muted fs-7">${escHtml(msg)}</span>`;
}

function renderRightPanelBooked() {
    document.getElementById('right-panel-title').textContent = ts.panel.available_title;
    document.getElementById('right-panel-body').innerHTML = `
        <div class="text-center py-10 px-4">
            <i class="ki-outline ki-lock-2 fs-3x text-warning mb-4 d-block"></i>
            <div class="fw-bold text-gray-800 fs-6 mb-2">${ts.booked.title}</div>
            <div class="text-muted fs-7">${ts.booked.text}</div>
        </div>`;
}

// =============================================================================
// RENDER: AVAILABLE OFFERS (building state) — grouped by services_needed
// =============================================================================

function renderAvailableOffers(offers) {
    if (proposal?.status !== 'draft') return;

    const el = document.getElementById('right-panel-body');
    const servicesNeeded = proposal.request?.services_needed ?? [];

    // Map: service type → included offer — keyed by RFQ service type (one slot per type)
    const coveredByType = {};
    includedOffers.forEach(o => {
        const type = o.rfq_service_type ?? 'other';
        if (!coveredByType[type]) coveredByType[type] = o;
    });

    // If no services_needed — fall back to flat list
    if (!servicesNeeded.length) {
        if (!offers.length) {
            el.innerHTML = `
                <div class="text-center py-10">
                    <i class="ki-outline ki-tag fs-3x text-gray-300 mb-3 d-block"></i>
                    <span class="text-muted fs-6">${ts.avail.all_included}</span>
                </div>`;
            return;
        }
        const itemsHtml = offers.map(o => buildAvailOfferCard(o, coveredByType)).join('');
        el.innerHTML = `<div style="max-height:560px;overflow-y:auto;overflow-x:hidden">${itemsHtml}</div>`;
        return;
    }

    const allCovered = servicesNeeded.every(t => coveredByType[t]);

    const sectionsHtml = servicesNeeded.map(serviceType => {
        const meta    = SERVICE_META[serviceType] ?? SERVICE_META.other;
        const covered = coveredByType[serviceType];

        // ── Already covered ──────────────────────────────────────────────────
        if (covered) {
            const supplierName = covered.supplier?.company_name ?? covered.supplier?.name ?? '—';
            const supplierId   = covered.supplier?.id ?? '';
            const gross        = parseFloat(covered.price_with_markup ?? covered.unit_price ?? 0);
            return `
            <div class="mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge ${meta.cls} py-1 px-3">${escHtml(meta.label)}</span>
                    <span class="text-success fs-8 fw-bold">
                        <i class="ki-outline ki-check-circle fs-7 me-1"></i>${ts.avail.included_badge}
                    </span>
                </div>
                <div class="bg-light-success rounded-2 px-4 py-3 d-flex align-items-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <a href="/admin/suppliers/${supplierId}" class="fw-semibold text-gray-800 text-hover-primary fs-7 d-block text-truncate">
                            ${escHtml(supplierName)}
                        </a>
                        <div class="text-success fw-bold fs-7 mt-1">${formatCurrency(gross)}</div>
                    </div>
                    <button class="btn btn-sm btn-icon btn-light-danger flex-shrink-0"
                            onclick="removeOffer('${covered.id}')" title="${ts.table.remove}">
                        <i class="ki-outline ki-trash fs-5"></i>
                    </button>
                </div>
            </div>`;
        }

        // ── Not covered — show available offers ───────────────────────────────
        const typeOffers = offers.filter(o => (o._rfq?.service_type ?? 'other') === serviceType);

        if (!typeOffers.length) {
            return `
            <div class="mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge ${meta.cls} py-1 px-3">${escHtml(meta.label)}</span>
                    <span class="text-muted fs-8">${ts.avail.no_offers_badge}</span>
                </div>
                <div class="text-muted fs-7 ps-1 py-1 pb-3 border-bottom">
                    ${ts.avail.no_offers_text}
                </div>
            </div>`;
        }

        const offersHtml = typeOffers.map(o => {
            const supplierName  = o.supplier?.company_name ?? o.supplier?.name ?? '—';
            const supplierId    = o.supplier?.id ?? '';
            const cost          = parseFloat(o.unit_price ?? 0);
            const defaultMarkup = markupSettings[serviceType] ?? 0;
            return `
            <div class="d-flex align-items-center gap-3 py-2 border-bottom" id="avail-offer-${o.id}">
                <div class="flex-grow-1 min-w-0">
                    <a href="/admin/suppliers/${supplierId}"
                       class="fw-semibold text-gray-800 text-hover-primary fs-7 d-block text-truncate"
                       target="_blank">${escHtml(supplierName)}</a>
                    <div class="d-flex align-items-center gap-3 mt-1">
                        <span class="text-gray-700 fw-bold fs-7">${formatCurrency(cost)}</span>
                        ${o.valid_until
                            ? `<span class="fs-8 ${o.is_expired ? 'text-danger' : 'text-muted'}">${o.is_expired ? ts.avail.expired : ts.avail.until.replace(':date', formatDate(o.valid_until))}</span>`
                            : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <div class="input-group input-group-sm" style="width:68px;border:none">
                        <input type="number" class="form-control form-control-sm form-control-solid"
                               id="markup-${o.id}" value="${defaultMarkup}"
                               min="0" max="100" step="0.01" title="${ts.avail.markup_title}"
                               style="-moz-appearance:textfield" />
                        <span class="input-group-text px-2 fs-8" style="border:none;background:var(--bs-gray-100)">%</span>
                    </div>
                    <button class="btn btn-sm btn-icon btn-light"
                            onclick="openOfferDrawer('${o.id}')"
                            title="${ts.quick_view}">
                        <i class="ki-outline ki-eye fs-4"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light-success"
                            onclick="addOfferWithMarkup('${o.id}')"
                            title="${ts.avail.add_title}">
                        <i class="ki-outline ki-plus fs-4"></i>
                    </button>
                </div>
            </div>`;
        }).join('');

        return `
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge ${meta.cls} py-1 px-3">${escHtml(meta.label)}</span>
                <span class="text-muted fs-8">${ts.avail.offers_count.replace(':n', typeOffers.length)}</span>
            </div>
            <div>${offersHtml}</div>
        </div>`;
    }).join('');

    const completionBanner = allCovered
        ? `<div class="alert alert-success d-flex align-items-center gap-3 py-3 mb-4 border-0">
               <i class="ki-outline ki-check-circle fs-2 text-success flex-shrink-0"></i>
               <div>
                   <div class="fw-bold fs-7 text-success">${ts.avail.all_covered_title}</div>
                   <div class="text-muted fs-8">${ts.avail.all_covered_text}</div>
               </div>
           </div>`
        : '';

    el.innerHTML = `${completionBanner}<div style="max-height:560px;overflow-y:auto;overflow-x:hidden;padding-right:2px">${sectionsHtml}</div>`;
}

function buildAvailOfferCard(o, coveredByType) {
    const serviceType   = o._rfq?.service_type ?? 'other';
    const serviceMeta   = SERVICE_META[serviceType] ?? SERVICE_META.other;
    const defaultMarkup = markupSettings[serviceType] ?? 0;
    const cost          = parseFloat(o.unit_price ?? 0);
    const supplierName  = o.supplier?.company_name ?? o.supplier?.name ?? '—';
    const supplierId    = o.supplier?.id ?? '';
    return `
    <div class="d-flex align-items-center gap-3 py-3 border-bottom" id="avail-offer-${o.id}">
        <div class="flex-grow-1 min-w-0">
            <div class="mb-1"><span class="badge ${serviceMeta.cls} fs-8 py-1 px-2">${escHtml(serviceMeta.label)}</span></div>
            <a href="/admin/suppliers/${supplierId}"
               class="fw-semibold text-gray-800 text-hover-primary fs-7 d-block text-truncate">${escHtml(supplierName)}</a>
            <div class="text-gray-700 fw-bold fs-7 mt-1">${formatCurrency(cost)}</div>
        </div>
        <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <div class="input-group input-group-sm" style="width:68px">
                <input type="number" class="form-control form-control-sm form-control-solid"
                       id="markup-${o.id}" value="${defaultMarkup}"
                       min="0" max="100" step="0.01" title="${ts.avail.markup_title}" />
                <span class="input-group-text px-2 fs-8">%</span>
            </div>
            <button class="btn btn-sm btn-icon btn-light"
                    onclick="openOfferDrawer('${o.id}')" title="${ts.quick_view}">
                <i class="ki-outline ki-eye fs-4"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-light-success"
                    onclick="addOfferWithMarkup('${o.id}')" title="${ts.avail.add_title}">
                <i class="ki-outline ki-plus fs-4"></i>
            </button>
        </div>
    </div>`;
}

// =============================================================================
// ACTIONS
// =============================================================================

async function addOfferWithMarkup(offerId) {
    const input = document.getElementById('markup-' + offerId);
    await addOffer(offerId, input ? parseFloat(input.value) : 0);
}

async function addOffer(offerId, markupPct) {
    // Enforce one-per-service-type constraint
    const offer = availableOffers.find(o => o.id === offerId);
    if (offer) {
        const offerType = offer._rfq?.service_type ?? 'other';
        const coveredTypes = new Set(includedOffers.map(o => o.rfq_service_type ?? 'other'));

        if (coveredTypes.has(offerType)) {
            const meta = SERVICE_META[offerType] ?? SERVICE_META.other;
            showToast(ts.toast.type_covered.replace(':label', meta.label), 'error');
            return;
        }
    }

    const d = await api.post(`/proposals/${proposalId}/offers`, {
        offer_id: offerId,
        markup_pct: markupPct,
        operator_notes: '',
    });
    if (d?.data || d?.id) {
        showToast(ts.toast.added);
        await loadProposal();
    } else {
        showToast(d?.message ?? ts.toast.add_error, 'error');
    }
}

async function removeOffer(offerId) {
    if (!confirm(ts.confirm.remove)) return;
    const d = await api.delete(`/proposals/${proposalId}/offers/${offerId}`);
    if (d?.data || d?.id || (d?.message && !d?.errors)) {
        showToast(ts.toast.removed);
        await loadProposal();
    } else {
        showToast(d?.message ?? ts.toast.remove_error, 'error');
    }
}

async function sendProposal() {
    if (!confirm(ts.confirm.send)) return;
    const d = await api.patch(`/proposals/${proposalId}/send`);
    if (d?.data || d?.id) {
        showToast(ts.toast.sent);
        await loadProposal();
    } else {
        showToast(d?.message ?? ts.toast.send_error, 'error');
    }
}

async function acceptProposal() {
    if (!confirm(ts.confirm.accept)) return;
    const d = await api.patch(`/proposals/${proposalId}/accept`);
    if (d?.data || d?.id) {
        showToast(ts.toast.accepted);
        await loadProposal();
    } else {
        showToast(d?.message ?? t.toast.error, 'error');
    }
}

async function rejectProposal() {
    if (!confirm(ts.confirm.reject)) return;
    const d = await api.patch(`/proposals/${proposalId}/reject`);
    if (d?.data || d?.id) {
        showToast(ts.toast.rejected);
        await loadProposal();
    } else {
        showToast(d?.message ?? t.toast.error, 'error');
    }
}

async function cancelProposal() {
    if (!confirm(ts.confirm.cancel)) return;
    const d = await api.patch(`/proposals/${proposalId}/cancel`);
    if (d?.data || d?.id) {
        showToast(ts.toast.cancelled);
        await loadProposal();
    } else {
        showToast(d?.message ?? t.toast.error, 'error');
    }
}

// =============================================================================
// HELPERS
// =============================================================================

function statusBadge(s) {
    const m = {
        draft:    'badge-light-secondary', sent:     'badge-light-primary',
        accepted: 'badge-light-success',   rejected: 'badge-light-danger',
        expired:  'badge-light-dark',      cancelled:'badge-light-dark',
        pending:  'badge-light-warning',   reviewed: 'badge-light-info',
        withdrawn:'badge-light-dark',
    };
    const labels = ts.status;
    const label = labels[s] ?? (s ? s.replace(/\b\w/g, l => l.toUpperCase()) : '—');
    return `<span class="badge ${m[s] ?? 'badge-light-secondary'}">${label}</span>`;
}

// =============================================================================
// OFFER DRAWER
// =============================================================================

function openOfferDrawer(offerId) {
    const o = includedOffers.find(x => x.id === offerId)
           ?? availableOffers.find(x => x.id === offerId);
    if (!o) return;

    const supplierName = o.supplier?.company_name ?? o.supplier?.name ?? '—';
    const supplierId   = o.supplier?.id ?? '';
    const serviceType  = o.rfq_service_type ?? o._rfq?.service_type ?? 'other';
    const offerMarkup  = parseFloat(o.markup_pct ?? markupSettings[serviceType] ?? 0);
    const itemMarkups  = o.item_markups ?? {};
    const hasItems     = Array.isArray(o.items) && o.items.length > 0;

    // ── Header ───────────────────────────────────────────────────────────────
    document.getElementById('drawer-offer-supplier').innerHTML =
        `<a href="/admin/suppliers/${supplierId}" class="text-gray-900 text-hover-primary fw-bold">${escHtml(supplierName)}</a>`;
    document.getElementById('drawer-offer-service').innerHTML = '';

    // ── Items ─────────────────────────────────────────────────────────────────
    let drawerNet = 0, drawerGross = 0;

    const itemsHtml = hasItems ? o.items.map(i => {
        const iType  = i.type ?? o.rfq_service_type ?? 'other';
        const iMeta  = SERVICE_META[iType] ?? SERVICE_META.other;
        const pct    = parseFloat(itemMarkups[iType] ?? offerMarkup);
        const net    = parseFloat(i.line_total ?? i.unit_price ?? 0);
        const gross  = net * (1 + pct / 100);
        const markup = gross - net;
        drawerNet   += net;
        drawerGross += gross;
        return `
        <div class="border rounded-2 p-4 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="badge ${iMeta.cls} fs-8">${iMeta.label}</span>
                <span class="fw-bold text-gray-900">${formatCurrency(gross)}</span>
            </div>
            <div class="fw-semibold text-gray-800 fs-6 mb-1">${escHtml((i.name && i.name !== i.type) ? i.name : (SERVICE_META[i.type]?.label ?? i.name ?? '—'))}</div>
            ${i.description ? `<div class="text-muted fs-7 mb-2">${escHtml(i.description)}</div>` : ''}
            <div class="d-flex flex-wrap gap-4 fs-7 text-muted">
                <span class="text-gray-700">${ts.drawer.net_label} <b class="fs-6">${formatCurrency(net)}</b></span>
                <span class="text-success">+${pct.toFixed(1)}% = +${formatCurrency(markup)}</span>
            </div>
        </div>`;
    }).join('') : '';

    // If no items, use offer-level price
    if (!hasItems) {
        const net   = parseFloat(o.unit_price ?? 0);
        const gross = parseFloat(o.price_with_markup ?? net * (1 + offerMarkup / 100));
        drawerNet   = net;
        drawerGross = gross;
    }

    const drawerMarkup = drawerGross - drawerNet;

    // ── Notes ─────────────────────────────────────────────────────────────────
    const notesHtml = (o.notes || o.operator_notes) ? `
        <div class="separator separator-dashed my-4"></div>
        ${o.notes ? `<div class="mb-3">
            <div class="text-muted fs-8 fw-bold text-uppercase mb-1">${ts.drawer.supplier_notes}</div>
            <div class="text-gray-700 fs-7">${escHtml(o.notes)}</div>
        </div>` : ''}
        ${o.operator_notes ? `<div>
            <div class="text-muted fs-8 fw-bold text-uppercase mb-1">${ts.drawer.operator_notes}</div>
            <div class="text-gray-700 fs-7">${escHtml(o.operator_notes)}</div>
        </div>` : ''}` : '';

    document.getElementById('drawer-offer-body').innerHTML = `
        <div class="d-flex flex-wrap gap-5 fs-7 text-gray-600 mb-5">
            ${o.rfq_title ? `<span><i class="ki-outline ki-send fs-6 me-1 text-muted"></i>${escHtml(o.rfq_title)}</span>` : ''}
            ${o.valid_until ? `<span><i class="ki-outline ki-calendar fs-6 me-1 text-muted"></i>
                <span class="${o.is_expired ? 'text-danger fw-bold' : ''}">${o.is_expired ? '⚠ ' : ''}${formatDate(o.valid_until)}</span>
            </span>` : ''}
        </div>

        ${hasItems ? `
        <div class="text-muted fs-8 fw-bold text-uppercase mb-3">${ts.drawer.items.replace(':n', o.items.length)}</div>
        ${itemsHtml}` : `
        <div class="border rounded-2 p-4 mb-3">
            <div class="fw-semibold text-gray-800 mb-1">${escHtml(o.rfq_title ?? supplierName)}</div>
            <div class="fs-7 text-muted">${ts.drawer.net_label} <b class="text-gray-800">${formatCurrency(drawerNet)}</b>
                &nbsp;+${offerMarkup.toFixed(1)}% = <b class="text-gray-900">${formatCurrency(drawerGross)}</b>
            </div>
        </div>`}

        <div class="separator separator-dashed my-4"></div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-gray-500 fs-7">${ts.drawer.net_total}</span>
            <span class="fw-semibold text-gray-800">${formatCurrency(drawerNet)}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-gray-500 fs-7">${ts.drawer.markup}</span>
            <span class="fw-semibold text-success">+${formatCurrency(drawerMarkup)}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-5">
            <span class="text-gray-600 fs-6 fw-bold">${ts.drawer.total}</span>
            <span class="fw-bold text-gray-900 fs-5">${formatCurrency(drawerGross)}</span>
        </div>

        ${notesHtml}

        <a href="/admin/offers/${o.id}" class="btn btn-light-primary w-100 mt-4">
            <i class="ki-outline ki-arrow-right fs-4 me-1"></i> ${ts.drawer.open_full}
        </a>`;

    new bootstrap.Offcanvas(document.getElementById('offer-drawer')).show();
}

function formatDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    const dd = String(dt.getDate()).padStart(2, '0');
    const mm = String(dt.getMonth() + 1).padStart(2, '0');
    const yyyy = dt.getFullYear();
    return `${dd}.${mm}.${yyyy}`;
}

function formatCurrency(v, currency) {
    if (v == null || v === '' || isNaN(v)) return '—';
    return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v)) + ' ' + (currency || 'AZN');
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
