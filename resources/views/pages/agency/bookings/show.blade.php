@extends('layouts.agency')

@section('title', __('bookings.agency_show.breadcrumb', ['id' => $id]))
@section('page-title', __('bookings.agency_show.title'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('agency.bookings.index') }}" class="text-muted text-hover-primary">{{ __('nav.agency.bookings') }}</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted">{{ __('bookings.agency_show.breadcrumb', ['id' => $id]) }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
@endpush

@section('content')

<div id="page-loader" class="text-center py-15">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div id="page-content" class="d-none">

    {{-- Header --}}
    <div class="card card-flush mb-6">
        <div class="card-body py-7">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-4">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h2 class="fw-bold text-gray-900 mb-0 fs-3">{{ __('bookings.agency_show.breadcrumb', ['id' => $id]) }}</h2>
                        <span id="status-badge"></span>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <span class="text-muted fs-7" id="header-confirmed"></span>
                        <span id="header-request-link"></span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted fs-7 mb-1">{{ __('bookings.agency_show.total') }}</div>
                    <div class="fs-2hx fw-bold text-gray-900" id="header-price">—</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="card card-flush mb-6">
        <div class="card-body py-6">
            <div id="status-timeline"></div>
        </div>
    </div>

    {{-- Payment / next step --}}
    <div id="payment-block"></div>

    {{-- Payment proof (agency uploads receipt; operator confirms) --}}
    <div id="payment-proof" class="mb-6"></div>

    {{-- Info + Proposal --}}
    <div class="row g-6">

        <div class="col-lg-5">
            <div class="card card-flush h-100">
                <div class="card-header pt-5 pb-0">
                    <h4 class="card-title fw-bold text-gray-800 fs-6">{{ __('bookings.agency_show.trip_details') }}</h4>
                </div>
                <div class="card-body pt-4">
                    <div class="d-flex flex-column gap-4" id="trip-details"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-flush h-100">
                <div class="card-header pt-5 pb-0">
                    <h4 class="card-title fw-bold text-gray-800 fs-6">
                        {{ __('bookings.agency_show.proposal_title') }}
                        <span id="proposal-badge" class="ms-2"></span>
                    </h4>
                </div>
                <div class="card-body pt-4" id="proposal-body">
                    <div class="text-center py-6"><span class="spinner-border spinner-border-sm text-primary"></span></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Notes (shown only when present) --}}
    <div id="notes-wrap" class="d-none mt-6">
        <div class="card card-flush border-left border-warning">
            <div class="card-body py-5">
                <div class="d-flex align-items-start gap-3">
                    <i class="ki-outline ki-information-5 fs-3 text-warning mt-1"></i>
                    <div>
                        <div class="fw-bold text-gray-800 mb-1">{{ __('bookings.agency_show.note') }}</div>
                        <div class="text-gray-600 fs-6" id="notes-text"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
const bookingId = {{ $id }};

// Локализация (bookings.agency_show.*). :id/:n/:date — через .replace().
const L  = @json(__('bookings.agency_show'));
const PS = @json(__('bookings.show.prop_status'));
// Локализованные названия типов услуг (value => label).
const SERVICE_LABELS = @json(collect(app(\App\Domain\Services\ServiceCatalog::class)->activeTypes())->pluck('label', 'value'));


const SERVICE_ICONS = {
    hotel: 'ki-home-2', accommodation: 'ki-home-2',
    flight: 'ki-airplane', aviation: 'ki-airplane',
    transfer: 'ki-car',
    tour: 'ki-map', excursion: 'ki-map',
    insurance: 'ki-shield-tick',
};
const SERVICE_COLORS = {
    hotel: 'bg-light-primary text-primary', accommodation: 'bg-light-primary text-primary',
    flight: 'bg-light-info text-info',      aviation: 'bg-light-info text-info',
    transfer: 'bg-light-warning text-warning',
    tour: 'bg-light-success text-success',  excursion: 'bg-light-success text-success',
    insurance: 'bg-light-danger text-danger',
};

const USER_TZ = @json($userTimezone);
function escHtml(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
// Дата + время в часовом поясе смотрящего.
function fmtDateTimeTz(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('ru-RU', {
        timeZone: USER_TZ, day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}
function tzOffsetLabel(iso) {
    try {
        return new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
            .formatToParts(iso ? new Date(iso) : new Date())
            .find(p => p.type === 'timeZoneName')?.value || USER_TZ;
    } catch (e) { return USER_TZ; }
}
// Дата + время + метка пояса. Для всех дат, кроме дат тура.
function fmtDtTz(iso) {
    if (!iso) return '—';
    return `${fmtDateTimeTz(iso)} (${tzOffsetLabel(iso)})`;
}
// Вторичная строка с суммой в AZN (рабочая валюта) — если у агентства иная валюта.
function aznSub(azn, currency, cls = 'fs-8') {
    if (azn == null || isNaN(azn) || currency === 'AZN') return '';
    return `<div class="text-muted ${cls}">≈ ${formatCurrency(azn, 'AZN')}</div>`;
}
function formatCurrency(v, currency = 'AZN') {
    if (v == null || v === '' || isNaN(v)) return '—';
    const num = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v));
    return num + ' ' + (currency || 'AZN');
}

(async function () {
    try {
        const data = await api.get(`/bookings/${bookingId}`);
        const b = data?.data ?? data;
        render(b);
    } catch {
        document.getElementById('page-loader').innerHTML =
            `<div class="alert alert-danger text-center">${L.load_error}</div>`;
    }
})();

function render(b) {
    document.getElementById('page-loader').classList.add('d-none');
    document.getElementById('page-content').classList.remove('d-none');

    // Header
    const sCls   = b.status_badge_class ?? 'badge-light-secondary';
    const sLabel = b.status_label      ?? b.status ?? '—';
    document.getElementById('status-badge').innerHTML = `<span class="badge ${sCls} fs-7">${sLabel}</span>`;
    document.getElementById('header-price').innerHTML =
        `${formatCurrency(b.final_price, b.currency)}${aznSub(b.final_price_azn, b.currency, 'fs-6')}`;

    const confirmedStr = b.confirmed_at
        ? L.confirmed_prefix.replace(':date', `${fmtDateTimeTz(b.confirmed_at)} (${tzOffsetLabel(b.confirmed_at)})`) : '';
    document.getElementById('header-confirmed').textContent = confirmedStr;

    const req = b.proposal?.request;
    if (req?.id) {
        document.getElementById('header-request-link').innerHTML =
            `<a href="/agency/requests/${req.id}" class="d-flex align-items-center gap-1 text-primary fs-7 fw-semibold">
                <i class="ki-outline ki-document fs-7"></i>${escHtml(req.title ?? L.request_fallback.replace(':id', req.id))}
                <i class="ki-outline ki-arrow-up-right fs-8"></i>
            </a>`;
    }

    // Stepper
    renderTimeline(b);

    // Payment / next step
    renderPayment(b);

    // Payment proof (receipt upload)
    loadPaymentProof(b);

    // Trip details
    renderTripDetails(b);

    // Proposal
    if (b.proposal?.id) {
        loadProposal(b.proposal.id);
    } else {
        document.getElementById('proposal-body').innerHTML =
            `<span class="text-muted fs-7">${L.no_proposal}</span>`;
    }

    // Notes
    if (b.notes) {
        document.getElementById('notes-text').textContent = b.notes;
        document.getElementById('notes-wrap').classList.remove('d-none');
    }
}

// Блок «что сейчас» — поясняет текущий статус и сумму к оплате (если ожидается).
function renderPayment(b) {
    const el = document.getElementById('payment-block');
    if (!el) return;
    if (b.status === 'cancelled') { el.innerHTML = ''; return; }

    const P = L.payment;
    const map = {
        confirmed:        { cls: 'primary', icon: 'ki-information-5', msg: P.confirmed   },
        awaiting_payment: { cls: 'warning', icon: 'ki-dollar',        msg: P.awaiting    },
        paid:             { cls: 'success', icon: 'ki-check-circle',  msg: P.paid        },
        in_progress:      { cls: 'info',    icon: 'ki-airplane',      msg: P.in_progress },
        completed:        { cls: 'success', icon: 'ki-medal-star',    msg: P.completed   },
    };
    const m = map[b.status];
    if (!m) { el.innerHTML = ''; return; }

    el.innerHTML = `
    <div class="card card-flush mb-6 border border-${m.cls} border-dashed bg-light-${m.cls}">
        <div class="card-body py-5 d-flex align-items-center gap-4">
            <span class="w-45px h-45px rounded-circle bg-${m.cls} d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="ki-outline ${m.icon} fs-2 text-white"></i>
            </span>
            <div class="flex-grow-1">
                <div class="text-muted fs-8 fw-bold text-uppercase">${P.title}</div>
                <div class="fw-semibold text-gray-800 fs-6">${escHtml(m.msg)}</div>
            </div>
        </div>
    </div>`;
}

// ── Подтверждение оплаты (агентство грузит чек, оператор подтверждает) ──────────
let _proofBooking = null;
async function loadPaymentProof(b) {
    _proofBooking = b;
    const el = document.getElementById('payment-proof');
    if (!el) return;
    if (b.status === 'cancelled') { el.innerHTML = ''; return; }
    try {
        const res = await api.get(`/bookings/${b.id}/attachments`);
        renderPaymentProof(b, Array.isArray(res.data) ? res.data : []);
    } catch {
        renderPaymentProof(b, []);
    }
}

function renderPaymentProof(b, files) {
    const el = document.getElementById('payment-proof');
    if (!el) return;
    const P = L.proof;
    const canUpload = ['confirmed', 'awaiting_payment'].includes(b.status);
    if (!files.length && !canUpload) { el.innerHTML = ''; return; }

    const list = files.length ? files.map(f => `
        <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2" id="proof-${f.id}">
            <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
            <div class="flex-grow-1 min-w-0">
                <a href="#" onclick="downloadBookingFile(${f.id}, '${String(f.filename).replace(/'/g, "\\'")}'); return false;"
                   class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block">${escHtml(f.filename)}</a>
                <div class="text-muted fs-8">${[f.human_size, P.uploaded.replace(':date', fmtDtTz(f.created_at))].filter(Boolean).join(' · ')}</div>
            </div>
            ${canUpload ? `<button class="btn btn-icon btn-sm btn-light-danger flex-shrink-0" title="${P.delete}" onclick="deletePaymentProof(${f.id})"><i class="ki-outline ki-trash fs-5"></i></button>` : ''}
        </div>`).join('') : `<div class="text-muted fs-7 mb-2">${P.empty}</div>`;

    const uploader = canUpload ? `
        <label class="btn btn-light-primary btn-sm mb-0">
            <i class="ki-outline ki-cloud-add fs-5 me-1"></i>${P.upload}
            <input type="file" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" onchange="uploadPaymentProof(this)">
        </label>` : '';

    el.innerHTML = `
    <div class="card card-flush">
        <div class="card-body py-5">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                <div class="fw-bold text-gray-800 fs-6">${P.title}</div>
                ${uploader}
            </div>
            <div class="text-muted fs-8 mb-3">${P.hint}</div>
            ${list}
        </div>
    </div>`;
}

async function uploadPaymentProof(input) {
    const file = input.files?.[0];
    if (!file) return;
    // Лоадер на кнопке загрузки на время запроса.
    const label = input.closest('label');
    if (label) { label.classList.add('disabled'); label.style.pointerEvents = 'none'; }
    const fd = new FormData();
    fd.append('file', file);
    try {
        const res = await fetch(`/api/bookings/${_proofBooking.id}/attachments`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd,
        });
        if (!res.ok) {
            let msg = L.proof.error;
            try { msg = (await res.json())?.message || msg; } catch (e) {}
            showToast(msg, 'error');
            return;
        }
        showToast(L.proof.success, 'success');
        loadPaymentProof(_proofBooking);
    } catch {
        showToast(L.proof.error, 'error');
    } finally {
        input.value = '';
        if (label) { label.classList.remove('disabled'); label.style.pointerEvents = ''; }
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

async function deletePaymentProof(id) {
    try {
        await api.delete(`/attachments/${id}`);
        showToast(L.proof.deleted, 'success');
        loadPaymentProof(_proofBooking);
    } catch (e) { showToast(e?.message ?? L.proof.error, 'error'); }
}

function renderTimeline(b) {
    const el = document.getElementById('status-timeline');

    if (b.status === 'cancelled') {
        el.innerHTML = `
        <div class="d-flex align-items-center gap-3 p-4 bg-light-danger rounded">
            <i class="ki-outline ki-cross-circle fs-2 text-danger"></i>
            <div>
                <div class="fw-bold text-danger">${L.cancelled_banner}</div>
                ${b.notes ? `<div class="text-muted fs-7 mt-1">${escHtml(b.notes)}</div>` : ''}
            </div>
        </div>`;
        return;
    }

    const ORDER = [
        { key: 'confirmed',        label: L.stepper.confirmed },
        { key: 'awaiting_payment', label: L.stepper.invoiced },
        { key: 'paid',             label: L.stepper.paid },
        { key: 'in_progress',      label: L.stepper.in_progress },
        { key: 'completed',        label: L.stepper.completed },
    ];
    const isCompleted = b.status === 'completed';
    const idx = ORDER.findIndex(s => s.key === b.status);

    // Текущий статус — выполненный шаг (зелёный), следующий — активный (синий).
    window.renderStepper(el, ORDER.map((s, i) => ({
        label:  s.label,
        done:   isCompleted || (idx > -1 && i <= idx),
        active: !isCompleted && i === idx + 1,
    })));

    // Между оплатой и началом тура — поясняем, что статус сменится автоматически.
    const startsFuture = ['confirmed', 'paid'].includes(b.status)
        && b.travel_date_from && new Date(b.travel_date_from) > new Date();
    if (startsFuture) {
        const hint = document.createElement('div');
        hint.className = 'd-flex align-items-center gap-2 text-muted fs-7 mt-4';
        hint.innerHTML = `<i class="ki-outline ki-information-5 fs-5 text-primary"></i>${L.auto_start_hint.replace(':date', formatDate(b.travel_date_from))}`;
        el.appendChild(hint);
    }
}

function renderTripDetails(b) {
    const rows = [];

    const dates = (b.travel_date_from || b.travel_date_to)
        ? `${formatDate(b.travel_date_from)} — ${formatDate(b.travel_date_to)}`
        : '—';

    rows.push(detailRow('ki-calendar', L.trip.dates, dates));

    const req = b.proposal?.request;
    if (req?.destination) {
        rows.push(detailRow('ki-geolocation', L.trip.destination, escHtml(req.destination)));
    }

    const services = Array.isArray(req?.services_needed) ? req.services_needed : [];
    if (services.length) {
        const badges = services
            .map(s => `<span class="badge badge-light-primary fs-8 me-1 mb-1">${escHtml(SERVICE_LABELS[s] ?? s)}</span>`)
            .join('');
        rows.push(detailRow('ki-abstract-26', L.trip.services, badges));
    }

    if (b.pax_count) {
        rows.push(detailRow('ki-people', L.trip.travellers, L.pax_unit.replace(':n', b.pax_count)));
    }

    if (b.operator?.name) {
        rows.push(detailRow('ki-user', L.trip.operator, escHtml(b.operator.name)));
    }

    rows.push(detailRow('ki-check-circle', L.trip.confirmed, fmtDtTz(b.confirmed_at ?? b.created_at)));

    document.getElementById('trip-details').innerHTML = rows.join('');
}

function detailRow(icon, label, value) {
    return `
    <div class="d-flex align-items-center gap-3">
        <span class="w-36px h-36px rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0">
            <i class="ki-outline ${icon} fs-6 text-gray-500"></i>
        </span>
        <div>
            <div class="text-muted fs-8 fw-bold text-uppercase">${label}</div>
            <div class="fw-semibold text-gray-800 fs-6">${value}</div>
        </div>
    </div>`;
}

async function loadProposal(proposalId) {
    try {
        const data = await api.get(`/proposals/${proposalId}`);
        const p = data?.data ?? data;
        renderProposal(p);
    } catch {
        document.getElementById('proposal-body').innerHTML =
            `<div class="alert alert-danger">${L.proposal_load_error}</div>`;
    }
}

function renderProposal(p) {
    const [sCls, sLabel] = {
        sent:     ['badge-light-primary', PS.sent],
        accepted: ['badge-light-success', PS.accepted],
        rejected: ['badge-light-danger',  PS.rejected],
    }[p.status] ?? ['badge-light-secondary', p.status];

    document.getElementById('proposal-badge').innerHTML =
        `<span class="badge ${sCls} fs-8">${sLabel}</span>`;

    const offers = p.offers ?? [];

    // Цена по каждой услуге в валюте агентства: раскладываем итог КП пропорционально
    // AZN-цене офферов (price_with_markup). Сумма строк всегда равна общему итогу.
    const offersAzn = offers.map(o => parseFloat(o.price_with_markup ?? 0));
    const sumAzn    = offersAzn.reduce((a, b) => a + b, 0);
    const factor    = sumAzn > 0 ? (parseFloat(p.total_price ?? 0) / sumAzn) : 0;

    const offerRows = offers.map((o, i) => {
        const type    = o.rfq_service_type ?? 'other';
        const label   = SERVICE_LABELS[type] ?? (o.rfq_title || L.service_fallback.replace(':id', o.id));
        const price   = factor > 0 ? offersAzn[i] * factor : null;

        // Материалы поставщика, расшаренные оператором (анонимные роуты КП).
        const photoUrls = [
            ...((o.agency_catalog_photos) ?? []),
            ...((o.photo_attachment_ids ?? []).map(id => `/api/proposals/${p.id}/photos/${id}`)),
        ];
        const gallery = photoUrls.length ? `
            <div class="d-flex gap-2 mt-2" style="overflow-x:auto;">
                ${photoUrls.map(url =>
                    `<a href="${url}" class="bk-glightbox flex-shrink-0" data-gallery="bk-offer-${o.id}" data-glightbox="type: image">
                        <img src="${url}" alt="" class="rounded" style="height:54px;width:78px;object-fit:cover;cursor:pointer;">
                    </a>`).join('')}
            </div>` : '';
        const files = Array.isArray(o.file_attachments) ? o.file_attachments : [];
        const filesHtml = files.length ? `
            <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="text-muted fs-8 w-100">${L.files}</span>
                ${files.map(f =>
                    `<a href="/api/proposals/${p.id}/files/${f.id}" target="_blank" rel="noopener"
                        class="badge badge-light-primary d-inline-flex align-items-center gap-1 text-uppercase">
                        <i class="ki-outline ki-document fs-7"></i>${escHtml(f.ext)}</a>`).join('')}
            </div>` : '';

        return `
        <div class="p-3 rounded bg-light mb-2">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 fs-7">${escHtml(label)}</div>
                    ${o.notes ? `<div class="text-muted fs-8 fst-italic">${escHtml(o.notes)}</div>` : ''}
                </div>
                ${price != null ? `<div class="text-end flex-shrink-0">
                    <span class="fw-bold text-gray-900 fs-7">${formatCurrency(price, p.currency)}</span>
                    ${aznSub(offersAzn[i], p.currency)}
                </div>` : ''}
            </div>
            ${gallery}${filesHtml}
        </div>`;
    }).join('');

    const noOffers = !offers.length
        ? `<div class="text-muted fs-7 py-4 text-center">${L.no_offers}</div>` : '';

    document.getElementById('proposal-body').innerHTML = `
        ${p.description ? `<div class="text-gray-600 fs-7 mb-4 fst-italic">${escHtml(p.description)}</div>` : ''}
        ${p.valid_until ? `<div class="text-muted fs-8 mb-3">
            <i class="ki-outline ki-calendar fs-8 me-1"></i>
            ${L.valid_until.replace(':date', fmtDtTz(p.valid_until))}
            ${p.is_expired ? `<span class="badge badge-light-danger ms-2 fs-9">${L.expired}</span>` : ''}
        </div>` : ''}
        <div class="mb-2 text-gray-400 fs-8 fw-bold text-uppercase">
            ${L.composition}
            ${offers.length ? `<span class="badge badge-light-secondary ms-1">${offers.length}</span>` : ''}
        </div>
        ${offerRows}${noOffers}
        <div class="d-flex align-items-center justify-content-between bg-light rounded p-4 mt-3">
            <span class="text-gray-600 fw-semibold fs-7">${L.total_line}</span>
            <div class="text-end">
                <span class="fw-bolder text-gray-900 fs-4">${formatCurrency(p.total_price, p.currency)}</span>
                ${aznSub(p.original_total_price, p.currency)}
            </div>
        </div>`;

    setTimeout(() => {
        window._bkLb?.destroy();
        window._bkLb = GLightbox({ selector: '#proposal-body .bk-glightbox', loop: true });
    }, 50);
}
</script>
@endpush
