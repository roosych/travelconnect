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
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
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
    <div id="payments-panel" class="mb-6"></div>

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

{{-- Record payment modal --}}
<div class="modal fade" id="modal-payment" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="payment-form" onsubmit="submitPayment(event); return false;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">{{ __('payments.panel.modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label required fw-semibold">{{ __('payments.panel.f_amount') }}</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-solid" id="pay-amount" required>
                            <select class="form-select form-select-solid" id="pay-currency" style="max-width:110px">
                                @foreach(\App\Domain\Settings\Models\Currency::where('is_active', true)->orderBy('code')->pluck('code') as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text" id="pay-amount-hint"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label required fw-semibold">{{ __('payments.panel.f_paid_at') }}</label>
                        <input type="text" class="form-control form-control-solid" id="pay-paid-at" autocomplete="off" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ __('payments.panel.f_reference') }}</label>
                        <input type="text" maxlength="255" class="form-control form-control-solid" id="pay-reference">
                    </div>
                    <div class="mb-2">
                        <label class="form-label required fw-semibold">{{ __('payments.panel.f_proof') }}</label>
                        <input type="file" id="pay-proof" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </div>
                    <div id="pay-error" class="text-danger fs-8 mt-2 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="pay-submit">
                        <span class="indicator-label">{{ __('payments.panel.submit') }}</span>
                        <span class="indicator-progress">{{ __('payments.panel.submit') }}... <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
const bookingId = {{ $id }};

// Локализация (bookings.agency_show.*). :id/:n/:date — через .replace().
const L  = @json(__('bookings.agency_show'));
const PS = @json(__('bookings.show.prop_status'));
const PM = @json(__('payments'));
const CURRENCIES = @json(\App\Domain\Settings\Models\Currency::where('is_active', true)->orderBy('code')->pluck('code'));
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

    // Расчёты (леджер): агентство видит своё входящее, может заявлять платежи
    loadPayments(b);

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

// ── Расчёты (агентство заявляет входящие платежи, оператор подтверждает) ───────
const PAY_STATUS_CLS = { pending: 'badge-light-secondary', partial: 'badge-light-warning', settled: 'badge-light-success' };
const FP_IDLE = @json(__('attachments.fp_idle'));
let _payDate = null;   // flatpickr
let _payPond = null;   // FilePond
let _payTargets = {};
let _payModal = null;

async function loadPayments(b) {
    const el = document.getElementById('payments-panel');
    if (!el) return;
    if (b.status === 'cancelled') { el.innerHTML = ''; return; }
    try {
        const res = await api.get(`/payments/ledger?payable_type=booking&payable_id=${b.id}`);
        renderPayments(Array.isArray(res.data) ? res.data : []);
    } catch {
        el.innerHTML = `<div class="card card-flush"><div class="card-body py-4 text-danger fs-7">${PM.panel.load_error}</div></div>`;
    }
}

function renderPayments(rows) {
    const el = document.getElementById('payments-panel');
    _payTargets = {};
    if (!rows.length) { el.innerHTML = ''; return; }

    const body = rows.map(row => {
        const key = `${row.direction}:${row.counterparty.type}:${row.counterparty.id}`;
        _payTargets[key] = row;
        const stCls = PAY_STATUS_CLS[row.status] ?? 'badge-light-secondary';

        const refDue = (row.ref_currency && row.ref_currency !== 'AZN')
            ? `<span class="text-muted fs-8 ms-1">≈ ${formatCurrency(row.ref_due, row.ref_currency)}</span>` : '';

        const payments = (row.payments ?? []).map(p => {
            const proof = (p.proof ?? []).map(f =>
                `<a href="#" onclick="downloadProof(${f.id}, '${String(f.filename).replace(/'/g, "\\'")}'); return false;" class="text-primary fs-8 ms-2"><i class="ki-outline ki-paper-clip fs-7 me-1"></i>${PM.panel.proof}</a>`).join('');
            const azn = p.currency !== 'AZN' ? ` <span class="text-muted fs-8">(≈ ${formatCurrency(p.amount_base, 'AZN')})</span>` : '';
            const badge = p.confirmed
                ? `<span class="badge badge-light-success fs-9 ms-1">${PM.panel.confirmed}</span>`
                : `<span class="badge badge-light-warning fs-9 ms-1">${PM.panel.awaiting}</span>`;
            // Своё ещё не подтверждённое можно удалить (для исправления).
            const delBtn = p.confirmed ? '' :
                `<button class="btn btn-icon btn-sm btn-light-danger" title="${PM.panel.delete}" onclick="deletePayment(${p.id})"><i class="ki-outline ki-trash fs-6"></i></button>`;
            return `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2">
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 fs-7">${formatCurrency(p.amount, p.currency)}${azn}${badge}</div>
                    <div class="text-muted fs-8">${formatDate(p.paid_at)}${p.reference ? ' · ' + escHtml(p.reference) : ''}${proof}</div>
                </div>
                <div class="flex-shrink-0">${delBtn}</div>
            </div>`;
        }).join('') || `<div class="text-muted fs-8 mb-2">${PM.panel.no_payments}</div>`;

        return `
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="badge ${stCls} fs-7">${PM.status[row.status]}</span>
            ${row.remaining > 0 ? `<button class="btn btn-sm btn-light-primary" onclick="openPaymentModal('${key}')"><i class="ki-outline ki-plus fs-5"></i>${PM.panel.record}</button>` : ''}
        </div>
        <div class="d-flex flex-wrap gap-6 mb-3">
            <div><div class="text-muted fs-8 text-uppercase">${PM.panel.due}</div><div class="fw-bold fs-6">${formatCurrency(row.due, 'AZN')}${refDue}</div></div>
            <div><div class="text-muted fs-8 text-uppercase">${PM.panel.paid}</div><div class="fw-bold fs-6 text-success">${formatCurrency(row.paid, 'AZN')}</div></div>
            <div><div class="text-muted fs-8 text-uppercase">${PM.panel.remaining}</div><div class="fw-bold fs-6 ${row.remaining > 0 ? 'text-warning' : ''}">${formatCurrency(row.remaining, 'AZN')}</div></div>
            ${row.pending > 0 ? `<div><div class="text-muted fs-8 text-uppercase">${PM.panel.pending}</div><div class="fw-bold fs-6 text-muted">${formatCurrency(row.pending, 'AZN')}</div></div>` : ''}
        </div>
        ${payments}`;
    }).join('');

    el.innerHTML = `
    <div class="card card-flush">
        <div class="card-header py-4"><div class="card-title"><h4 class="fw-bold fs-6 mb-0">${PM.panel.title}</h4></div></div>
        <div class="card-body pt-2">${body}</div>
    </div>`;
}

function openPaymentModal(key) {
    const row = _payTargets[key];
    if (!row) return;
    const sel = document.getElementById('pay-currency');
    sel.value = CURRENCIES.includes(row.ref_currency) ? row.ref_currency : 'AZN';
    document.getElementById('pay-amount').value = '';
    document.getElementById('pay-amount-hint').textContent = PM.panel.f_amount_hint.replace(':amount', formatCurrency(row.remaining, 'AZN'));
    document.getElementById('pay-reference').value = '';
    document.getElementById('pay-error').classList.add('d-none');
    document.getElementById('payment-form').dataset.key = key;

    if (!_payDate) {
        _payDate = flatpickr('#pay-paid-at', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd.m.Y', maxDate: 'today', allowInput: false, disableMobile: true });
    }
    _payDate.setDate(new Date(), true);

    if (!_payPond) {
        _payPond = FilePond.create(document.getElementById('pay-proof'), { allowMultiple: false, labelIdle: FP_IDLE });
    }
    _payPond.removeFiles();

    if (!_payModal) _payModal = new bootstrap.Modal(document.getElementById('modal-payment'));
    _payModal.show();
}

async function submitPayment(e) {
    e.preventDefault();
    const form = document.getElementById('payment-form');
    const row = _payTargets[form.dataset.key];
    if (!row) return;
    const errEl = document.getElementById('pay-error'); errEl.classList.add('d-none');
    const btn = document.getElementById('pay-submit');
    const file = _payPond?.getFile()?.file;
    if (!file) { errEl.textContent = PM.panel.f_proof; errEl.classList.remove('d-none'); return; }

    const fd = new FormData();
    fd.append('payable_type', 'booking');
    fd.append('payable_id', bookingId);
    fd.append('direction', row.direction);
    fd.append('counterparty_type', row.counterparty.type);
    fd.append('counterparty_id', row.counterparty.id);
    fd.append('amount', document.getElementById('pay-amount').value);
    fd.append('currency', document.getElementById('pay-currency').value);
    fd.append('paid_at', document.getElementById('pay-paid-at').value);
    fd.append('reference', document.getElementById('pay-reference').value);
    fd.append('proof', file);

    window.btnLoading?.(btn, true);
    try {
        const res = await fetch('/api/payments', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd,
        });
        if (!res.ok) {
            let msg = PM.panel.err;
            try { msg = (await res.json())?.message || msg; } catch (e) {}
            errEl.textContent = msg; errEl.classList.remove('d-none');
            return;
        }
        _payModal?.hide();
        showToast(PM.panel.saved);
        await reloadBooking();
    } catch {
        errEl.textContent = PM.panel.err; errEl.classList.remove('d-none');
    } finally {
        window.btnLoading?.(btn, false);
    }
}

async function deletePayment(id) {
    if (!confirm(PM.panel.del_confirm)) return;
    try {
        await api.delete(`/payments/${id}`);
        showToast(PM.panel.deleted);
        await reloadBooking();
    } catch (e) { showToast(e?.message ?? PM.panel.err, 'error'); }
}

async function downloadProof(id, filename) {
    try {
        const res = await fetch(`/api/attachments/${id}/download`, {
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        if (!res.ok) return;
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = filename;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } catch {}
}

async function reloadBooking() {
    const d = await api.get(`/bookings/${bookingId}`);
    render(d.data ?? d);
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
