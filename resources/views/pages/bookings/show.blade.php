@extends('layouts.app')

@section('title', __('bookings.show.title'))
@section('page-title', __('bookings.show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.bookings.index') }}" class="text-muted text-hover-primary">{{ __('bookings.title') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ __('bookings.show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@section('toolbar-actions')
    <div id="booking-actions" class="d-flex gap-2 flex-wrap"></div>
@endsection

@section('content')

<div class="row g-6">

    {{-- Main detail card --}}
    <div class="col-lg-8">
        <div class="card card-flush mb-6" id="booking-detail-card">
            <div class="card-body py-8">
                <div class="text-center py-6"><span class="spinner-border text-primary"></span></div>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">

        <div class="card card-flush mb-6" id="booking-proposal-card">
            <div class="card-header py-4">
                <div class="card-title">
                    <h4 class="fw-bold fs-6 mb-0">{{ __('bookings.show.proposal_card') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="text-center py-6"><span class="spinner-border text-success spinner-border-sm"></span></div>
            </div>
        </div>

        <div class="card card-flush mb-6" id="booking-request-card">
            <div class="card-header py-4">
                <div class="card-title">
                    <h4 class="fw-bold fs-6 mb-0">{{ __('bookings.show.request_card') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="text-center py-4"><span class="spinner-border text-info spinner-border-sm"></span></div>
            </div>
        </div>

        <div class="card card-flush" id="booking-proof-card">
            <div class="card-header py-4">
                <div class="card-title">
                    <h4 class="fw-bold fs-6 mb-0">{{ __('bookings.show.proof_card') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="text-center py-4"><span class="spinner-border text-warning spinner-border-sm"></span></div>
            </div>
        </div>

    </div>
</div>

{{-- Proposal drawer --}}
<div class="offcanvas offcanvas-end" id="proposalDrawer" tabindex="-1" style="width:520px;max-width:95vw">
    <div class="offcanvas-header border-bottom py-5 px-8">
        <h5 class="offcanvas-title fw-bold fs-5" id="proposalDrawerLabel">{{ __('bookings.show.drawer.title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-8 py-6" id="proposalDrawerBody">
        <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
    </div>
</div>


@endsection

@push('scripts')
<script>
const bookingId = {{ $id }};
const t  = @json(__('bookings'));
const tc = @json(__('common'));
const ts = t.show;
const USER_TZ = @json($userTimezone);
let currentBooking = null;

(async function load() {
    try {
        const data = await api.get(`/bookings/${bookingId}`);
        currentBooking = data.data ?? data;
        renderBooking(currentBooking);
    } catch (err) {
        document.getElementById('booking-detail-card').querySelector('.card-body').innerHTML =
            `<div class="alert alert-danger">${ts.load_error}</div>`;
    }
})();

function renderBooking(b) {
    renderToolbar(b);
    renderDetailCard(b);
    renderProposalCard(b);
    renderRequestCard(b);
    loadProofCard(b);
}

// Подтверждения оплаты, загруженные агентством (read-only для оператора).
async function loadProofCard(b) {
    const body = document.getElementById('booking-proof-card').querySelector('.card-body');
    try {
        const res = await api.get(`/bookings/${b.id}/attachments`);
        const files = Array.isArray(res.data) ? res.data : [];
        if (!files.length) {
            body.innerHTML = `<div class="text-muted fs-7 py-2">${ts.proof_empty}</div>`;
            return;
        }
        body.innerHTML = files.map(f => `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2">
                <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
                <div class="flex-grow-1 min-w-0">
                    <a href="#" onclick="downloadBookingFile(${f.id}, '${String(f.filename).replace(/'/g, "\\'")}'); return false;"
                       class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block">${escHtml(f.filename)}</a>
                    <div class="text-muted fs-8">${[f.human_size, ts.proof_uploaded.replace(':date', fmtDtTz(f.created_at))].filter(Boolean).join(' · ')}</div>
                </div>
            </div>`).join('');
    } catch {
        body.innerHTML = `<div class="text-muted fs-7 py-2">${ts.proof_empty}</div>`;
    }
}

async function downloadBookingFile(id, filename) {
    try {
        const res = await fetch(`/api/attachments/${id}/download`, {
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        if (!res.ok) return;
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } catch {}
}

function renderToolbar(b) {
    const el = document.getElementById('booking-actions');
    const buttons = [];
    const TERMINAL = ['completed', 'cancelled'];

    if (b.status === 'confirmed') {
        buttons.push(`<button class="btn btn-sm btn-warning" onclick="doRequestPayment()">
            <i class="ki-outline ki-bill fs-2"></i> ${ts.toolbar.request_payment}
        </button>`);
    }
    if (b.status === 'awaiting_payment') {
        buttons.push(`<button class="btn btn-sm btn-success" onclick="doMarkPaid()">
            <i class="ki-outline ki-check-circle fs-2"></i> ${ts.toolbar.mark_paid}
        </button>`);
    }
    if (b.status === 'in_progress') {
        buttons.push(`<button class="btn btn-sm btn-success" onclick="doComplete()">
            <i class="ki-outline ki-check-circle fs-2"></i> ${ts.toolbar.complete}
        </button>`);
    }
    if (!TERMINAL.includes(b.status)) {
        buttons.push(`<button class="btn btn-sm btn-light-danger" onclick="doCancel()">
            <i class="ki-outline ki-cross-circle fs-2"></i> ${ts.toolbar.cancel}
        </button>`);
    }

    el.innerHTML = buttons.join('');
}

function renderDetailCard(b) {
    const agency  = b.agency;

    const subtitleParts = [
        b.proposal?.title ? escHtml(b.proposal.title) : null,
        agency?.name ? escHtml(agency.name) : null,
    ].filter(Boolean);

    const stepperHtml = b.status === 'cancelled'
        ? `<div class="d-flex align-items-center gap-2 p-3 bg-light-danger rounded">
               <i class="ki-outline ki-cross-circle fs-3 text-danger"></i>
               <span class="fw-semibold text-danger">${ts.cancelled_banner}${b.notes ? ' — ' + escHtml(b.notes) : ''}</span>
           </div>`
        : `<div id="booking-stepper"></div>`;

    // Между оплатой и началом тура у оператора нет действий — поясняем, что
    // переход в «В процессе» произойдёт автоматически в день старта тура.
    const startsFuture = ['confirmed', 'paid'].includes(b.status)
        && b.travel_date_from && new Date(b.travel_date_from) > new Date();
    const autoHint = startsFuture
        ? `<div class="d-flex align-items-center gap-2 text-muted fs-7 mb-5">
               <i class="ki-outline ki-information-5 fs-5 text-primary"></i>
               ${ts.auto_start_hint.replace(':date', formatDate(b.travel_date_from))}
           </div>`
        : '';

    document.getElementById('booking-detail-card').innerHTML = `
        <div class="card-body py-7">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-4 mb-6">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h2 class="fw-bold text-gray-900 mb-0 fs-3">${t.drawer.ref.replace(':id', b.id)}</h2>
                        ${statusBadge(b)}
                    </div>
                    ${subtitleParts.length ? `<div class="text-muted fs-6">${subtitleParts.join('<span class="mx-2">·</span>')}</div>` : ''}
                </div>
                <div class="text-end">
                    <div class="text-muted fs-7 mb-1">${t.drawer.total}</div>
                    <div class="fs-2hx fw-bold text-gray-900">${formatCurrency(b.final_price_azn)}</div>
                    ${b.agency_currency && b.agency_currency !== 'AZN' ? `<div class="text-muted fs-7 mt-1">${ts.for_agency.replace(':amount', formatCurrency(b.agency_final_price, b.agency_currency))}</div>` : ''}
                </div>
            </div>

            <div class="bg-light rounded p-5 mb-5">${stepperHtml}</div>
            ${autoHint}

            <div class="row g-5">
                <div class="col-sm-6">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-1">${ts.agency}</div>
                    <div class="fw-semibold text-gray-800">
                        ${agency?.id
                            ? `<a href="/admin/agencies/${agency.id}" class="text-gray-800 text-hover-primary">${escHtml(agency.name)}</a>`
                            : escHtml(agency?.name ?? '—')}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-1">${t.drawer.confirmed}</div>
                    <div class="fw-semibold text-gray-800">${fmtDtTz(b.confirmed_at ?? b.created_at)}</div>
                </div>
                ${b.travel_date_from ? `
                <div class="col-sm-6">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-1">${t.drawer.travel_dates}</div>
                    <div class="fw-semibold text-gray-800">
                        ${formatDate(b.travel_date_from)} → ${formatDate(b.travel_date_to)}
                    </div>
                </div>` : ''}
                ${b.pax_count ? `
                <div class="col-sm-6">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-1">${t.drawer.pax}</div>
                    <div class="fw-semibold text-gray-800">${t.drawer.pax_unit.replace(':n', b.pax_count)}</div>
                </div>` : ''}
                ${b.notes ? `
                <div class="col-12">
                    <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-1">${t.drawer.notes}</div>
                    <div class="text-gray-700 fs-6 bg-light rounded p-3">${escHtml(b.notes)}</div>
                </div>` : ''}
            </div>
        </div>`;

    if (b.status !== 'cancelled') renderBookingStepper(b);
}

function renderBookingStepper(b) {
    const ORDER = [
        { key: 'confirmed',        label: ts.stepper.confirmed },
        { key: 'awaiting_payment', label: ts.stepper.invoiced },
        { key: 'paid',             label: ts.stepper.paid },
        { key: 'in_progress',      label: ts.stepper.in_progress },
        { key: 'completed',        label: ts.stepper.completed },
    ];
    const isCompleted = b.status === 'completed';
    const idx = ORDER.findIndex(s => s.key === b.status);

    // Labels are achieved milestones: the current status's step is done (green),
    // and the next pending milestone is active (blue).
    window.renderStepper('booking-stepper', ORDER.map((s, i) => ({
        label:  s.label,
        done:   isCompleted || (idx > -1 && i <= idx),
        active: !isCompleted && i === idx + 1,
    })));
}

function renderProposalCard(b) {
    const proposal = b.proposal;
    const cardBody = document.getElementById('booking-proposal-card').querySelector('.card-body');

    if (!proposal) {
        cardBody.innerHTML = `<span class="text-muted fs-7">${ts.proposal_unavailable}</span>`;
        return;
    }

    cardBody.innerHTML = `
        <div class="d-flex flex-column gap-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-gray-500 fs-7 fw-semibold">${ts.proposal_label}</span>
                <a href="/admin/proposals/${proposal.id}" class="fw-semibold text-primary fs-7">#${proposal.id}</a>
            </div>
            <div class="fw-semibold text-gray-800 fs-6">${escHtml(proposal.title ?? ts.proposal_ref.replace(':id', proposal.id))}</div>
            <div class="mt-2">
                <button class="btn btn-light-success btn-sm w-100" onclick="openProposalDrawer(${proposal.id})">
                    <i class="ki-outline ki-eye fs-5 me-1"></i>${ts.view_proposal}
                </button>
            </div>
        </div>`;
}

function renderRequestCard(b) {
    const request = b.proposal?.request;
    const cardBody = document.getElementById('booking-request-card').querySelector('.card-body');

    if (!request) {
        cardBody.innerHTML = `<span class="text-muted fs-7">${ts.request_unavailable}</span>`;
        return;
    }

    const rsCls   = request.status_badge_class ?? 'badge-light-secondary';
    const rsLabel = request.status_label      ?? request.status ?? '—';

    const SERVICE_LABELS = window.SERVICE_LABELS;
    const services = (request.services_needed ?? [])
        .map(s => `<span class="badge badge-light-primary fs-8 me-1 mb-1">${SERVICE_LABELS[s] ?? escHtml(s)}</span>`)
        .join('');

    const deadline = request.deadline_at ? (() => {
        const diff = Math.ceil((new Date(request.deadline_at) - Date.now()) / 86400000);
        const cls  = diff < 0 ? 'text-danger' : diff <= 3 ? 'text-warning' : 'text-muted';
        return `<div class="d-flex align-items-center gap-2 fs-7 ${cls}">
            <i class="ki-outline ki-time fs-6"></i>
            ${ts.deadline_label.replace(':date', fmtDtTz(request.deadline_at))}
            ${diff < 0 ? `<span class="badge badge-light-danger ms-1 fs-9">${tc.time.overdue}</span>` : diff <= 3 ? `<span class="text-muted fw-normal">(${tc.time.days.replace(':n', diff)})</span>` : ''}
        </div>`;
    })() : '';

    cardBody.innerHTML = `
        <div class="d-flex flex-column gap-4">

            <div class="d-flex align-items-start justify-content-between gap-2">
                <a href="/admin/requests/${request.id}" class="fw-bold text-gray-800 text-hover-primary fs-6 lh-sm">
                    ${escHtml(request.title ?? ts.request_ref.replace(':id', request.id))}
                </a>
                <span class="badge ${rsCls} flex-shrink-0">${rsLabel}</span>
            </div>

            ${request.destination ? `
            <div class="d-flex align-items-center gap-2 text-muted fs-7">
                <i class="ki-outline ki-geolocation fs-5 text-gray-400"></i>
                ${escHtml(request.destination)}
            </div>` : ''}

            <div class="d-flex align-items-center gap-2 text-muted fs-7">
                <i class="ki-outline ki-people fs-5 text-gray-400"></i>
                ${ts.travellers.replace(':n', b.pax_count ?? request.pax_count ?? '—')}
            </div>

            <div class="d-flex align-items-center gap-2 text-muted fs-7">
                <i class="ki-outline ki-calendar fs-5 text-gray-400"></i>
                ${formatDate(b.travel_date_from)} → ${formatDate(b.travel_date_to)}
            </div>

            ${deadline}

            ${services ? `<div class="d-flex flex-wrap gap-1">${services}</div>` : ''}

            ${request.notes ? `
            <div class="bg-light rounded p-3 fs-7 text-gray-600 fst-italic">
                "${escHtml(request.notes)}"
            </div>` : ''}

            <a href="/admin/requests/${request.id}" target="_blank" rel="noopener" class="btn btn-light-info btn-sm w-100 mt-1">
                <i class="ki-outline ki-arrow-up-right fs-5 me-1"></i>${ts.open_request}
            </a>

        </div>`;
}

// ---- Actions ----

async function doRequestPayment() {
    const d = await api.patch(`/bookings/${bookingId}/request-payment`);
    if (d.success) {
        showToast(ts.toast.payment_requested);
        currentBooking = { ...currentBooking, ...d.data };
        renderBooking(currentBooking);
    } else {
        showToast(d.message ?? ts.toast.error, 'error');
    }
}

async function doMarkPaid() {
    const d = await api.patch(`/bookings/${bookingId}/paid`);
    if (d.success) {
        showToast(ts.toast.paid);
        currentBooking = { ...currentBooking, ...d.data };
        renderBooking(currentBooking);
    } else {
        showToast(d.message ?? ts.toast.error, 'error');
    }
}

async function doComplete() {
    const notes = prompt(ts.prompt.complete);
    if (notes === null) return;
    if (!notes.trim()) { showToast(ts.err.notes_required, 'error'); return; }
    const d = await api.patch(`/bookings/${bookingId}/complete`, { notes });
    if (d.success) {
        showToast(ts.toast.completed);
        currentBooking = { ...currentBooking, ...d.data };
        renderBooking(currentBooking);
    } else {
        showToast(d.message ?? ts.toast.error, 'error');
    }
}

async function doCancel() {
    const notes = prompt(ts.prompt.cancel);
    if (notes === null) return;
    if (!notes.trim()) { showToast(ts.err.reason_required, 'error'); return; }
    const d = await api.patch(`/bookings/${bookingId}/cancel`, { notes });
    if (d.success) {
        showToast(ts.toast.cancelled);
        currentBooking = { ...currentBooking, ...d.data, notes };
        renderBooking(currentBooking);
    } else {
        showToast(d.message ?? ts.toast.error, 'error');
    }
}

// ---- Helpers ----

function statusBadge(b) {
    if (b.status_label) return `<span class="badge ${b.status_badge_class}">${b.status_label}</span>`;
    const [cls, label] = ['badge-light-secondary', b.status ?? '—'];
    return `<span class="badge ${cls}">${label}</span>`;
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Дата + время + метка пояса смотрящего. Для всех дат, кроме дат тура.
function fmtDtTz(iso) {
    if (!iso) return '—';
    const dt = new Date(iso).toLocaleString('ru-RU', {
        timeZone: USER_TZ, day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
    let tz = USER_TZ;
    try {
        tz = new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
            .formatToParts(new Date(iso)).find(p => p.type === 'timeZoneName')?.value || USER_TZ;
    } catch (e) {}
    return `${dt} (${tz})`;
}

function formatCurrency(v, currency = 'AZN') {
    if (v == null || v === '' || isNaN(v)) return '—';
    const num = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v));
    return num + ' ' + (currency || 'AZN');
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ---- Proposal Drawer ----

let _proposalDrawer = null;

async function openProposalDrawer(proposalId) {
    const body  = document.getElementById('proposalDrawerBody');
    const label = document.getElementById('proposalDrawerLabel');

    body.innerHTML = '<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>';
    label.textContent = ts.drawer.title;

    if (!_proposalDrawer) {
        _proposalDrawer = new bootstrap.Offcanvas(document.getElementById('proposalDrawer'));
    }
    _proposalDrawer.show();

    try {
        const data = await api.get(`/proposals/${proposalId}`);
        const p = data.data ?? data;
        label.textContent = p.title ?? t.drawer.proposal_ref.replace(':id', p.id);
        body.innerHTML = renderProposalDrawerContent(p);
    } catch (err) {
        body.innerHTML = `<div class="alert alert-danger">${ts.drawer.load_error}</div>`;
    }
}

function renderProposalDrawerContent(p) {
    const statusMap = {
        draft:    ['badge-light-secondary', ts.prop_status.draft],
        sent:     ['badge-light-primary',   ts.prop_status.sent],
        accepted: ['badge-light-success',   ts.prop_status.accepted],
        rejected: ['badge-light-danger',    ts.prop_status.rejected],
    };
    const [sCls, sLabel] = statusMap[p.status] ?? ['badge-light-secondary', p.status];

    const offerRows = (p.offers ?? []).map(o => {
        const title    = o.rfq_title || ts.drawer.service_ref.replace(':id', o.id);
        const supplier = o.supplier?.name ?? '';

        const baseRaw  = o.unit_price        != null ? parseFloat(o.unit_price)        : null;
        const finalRaw = o.price_with_markup  != null ? parseFloat(o.price_with_markup) : baseRaw;

        const finalPrice = finalRaw != null ? formatCurrency(finalRaw) : '—';
        const basePrice  = baseRaw  != null ? formatCurrency(baseRaw)  : null;

        // Наценка: считаем из разницы — работает как для markup_pct, так и для item_markups
        const markupRaw    = (baseRaw != null && finalRaw != null && finalRaw > baseRaw)
            ? finalRaw - baseRaw : null;
        const markupAmount = markupRaw != null ? formatCurrency(markupRaw) : null;
        const markupPct    = (markupRaw != null && baseRaw > 0)
            ? (markupRaw / baseRaw * 100).toFixed(1).replace(/\.0$/, '') : null;

        return `
        <div class="d-flex align-items-start gap-4 p-4 rounded bg-light mb-2">
            <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-gray-800 fs-6">${escHtml(title)}</div>
                ${supplier ? `<div class="d-flex align-items-center gap-1 text-muted fs-7 mt-1">
                    <i class="ki-outline ki-building fs-8"></i>${escHtml(supplier)}
                </div>` : ''}
                ${o.operator_notes ? `<div class="text-muted fs-8 mt-2 fst-italic">"${escHtml(o.operator_notes)}"</div>` : ''}
            </div>
            <div class="text-end flex-shrink-0">
                <div class="fw-bolder text-gray-900 fs-6">${finalPrice}</div>
                ${markupAmount ? `
                <div class="mt-1">
                    <span class="badge badge-light-success fs-9">+${markupPct}%</span>
                    <span class="text-success fs-8 ms-1">+${markupAmount}</span>
                </div>
                <div class="text-muted fs-9 mt-1">${ts.drawer.supplier_price.replace(':price', basePrice)}</div>
                ${o.supplier_currency && o.supplier_currency !== 'AZN' ? `<div class="text-muted fs-9">${ts.drawer.in_supplier_currency.replace(':amount', formatCurrency(o.supplier_unit_price, o.supplier_currency))}</div>` : ''}
                ` : ''}
            </div>
        </div>`;
    }).join('');

    const noOffers = !p.offers?.length
        ? `<div class="text-center text-muted fs-7 py-6"><i class="ki-outline ki-information fs-3 d-block mb-2"></i>${ts.drawer.no_offers}</div>`
        : '';

    return `
    ${p.request?.id ? `
    <a href="/admin/requests/${p.request.id}" target="_blank"
       class="d-flex align-items-center gap-3 bg-light-primary rounded p-4 mb-5 text-decoration-none">
        <span class="w-35px h-35px rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0">
            <i class="ki-outline ki-document fs-5 text-white"></i>
        </span>
        <div class="flex-grow-1 min-w-0">
            <div class="text-primary fs-8 fw-bold text-uppercase mb-1">${ts.drawer.request_label}</div>
            <div class="fw-semibold text-gray-800 fs-6 text-truncate">${escHtml(p.request.title ?? ts.request_ref.replace(':id', p.request.id))}</div>
        </div>
        <i class="ki-outline ki-arrow-up-right fs-5 text-primary flex-shrink-0"></i>
    </a>` : ''}

    <div class="d-flex align-items-center gap-3 flex-wrap mb-5">
        <span class="badge ${sCls} fs-7 py-2 px-3">${sLabel}</span>
        ${p.is_expired ? `<span class="badge badge-light-danger fs-7 py-2 px-3">${ts.drawer.expired}</span>` : ''}
        ${p.valid_until && !p.is_expired ? `<span class="text-muted fs-7"><i class="ki-outline ki-calendar fs-7 me-1"></i>${ts.drawer.valid_until.replace(':date', fmtDtTz(p.valid_until))}</span>` : ''}
    </div>

    <div class="d-flex align-items-center gap-6 mb-6 pb-5 border-bottom">
        ${p.operator?.name ? `
        <div>
            <div class="text-gray-400 fs-8 text-uppercase fw-bold mb-1">${ts.drawer.composed_by}</div>
            <div class="d-flex align-items-center gap-2 fw-semibold text-gray-800 fs-6">
                <i class="ki-outline ki-user fs-6 text-gray-400"></i>${escHtml(p.operator.name)}
            </div>
        </div>` : ''}
        <div>
            <div class="text-gray-400 fs-8 text-uppercase fw-bold mb-1">${ts.drawer.created}</div>
            <div class="fw-semibold text-gray-800 fs-6">${fmtDtTz(p.created_at)}</div>
        </div>
    </div>

    ${p.description ? `
    <div class="mb-6">
        <div class="text-gray-400 fs-8 text-uppercase fw-bold mb-3">${ts.drawer.description}</div>
        <div class="text-gray-700 fs-6 lh-lg">${escHtml(p.description)}</div>
    </div>` : ''}

    <div class="mb-4">
        <div class="text-gray-400 fs-8 text-uppercase fw-bold mb-3">
            ${ts.drawer.composition}
            ${p.offers?.length ? `<span class="badge badge-light-secondary ms-2">${p.offers.length}</span>` : ''}
        </div>
        ${offerRows}${noOffers}
    </div>

    <div class="d-flex align-items-center justify-content-between bg-light rounded p-5 mt-4">
        <div>
            <div class="text-gray-500 fs-7 fw-semibold mb-1">${t.drawer.total}</div>
            <div class="text-muted fs-8">${ts.drawer.positions.replace(':n', p.offers?.length ?? 0)}</div>
        </div>
        <div class="text-end">
            <div class="fs-2hx fw-bolder text-gray-900">${formatCurrency(p.amount_azn ?? p.total_price)}</div>
            ${p.agency_currency && p.agency_currency !== 'AZN' ? `<div class="text-muted fs-7 mt-1">${ts.for_agency.replace(':amount', formatCurrency(p.agency_amount, p.agency_currency))}</div>` : ''}
        </div>
    </div>`;
}
</script>
@endpush
