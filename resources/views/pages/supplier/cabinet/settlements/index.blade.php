@extends('layouts.supplier')

@section('title', __('payments.my.title'))
@section('page-title', __('payments.my.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('payments.my.title') }}</li>
@endsection

@section('content')

<div class="d-flex flex-column gap-3 mb-5">
    <div class="text-muted fs-6">{{ __('payments.my.subtitle') }}</div>
</div>

<div id="settlements-wrap">
    <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
</div>

@endsection

@push('scripts')
<script>
const PM = @json(__('payments'));
const PAY_STATUS_CLS = { pending: 'badge-light-secondary', partial: 'badge-light-warning', settled: 'badge-light-success' };

function escHtml(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
function formatCurrency(v, currency = 'AZN') {
    if (v == null || v === '' || isNaN(v)) return '—';
    return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v)) + ' ' + (currency || 'AZN');
}

(async function () {
    const wrap = document.getElementById('settlements-wrap');
    try {
        const res = await api.get('/payments/my-settlements');
        render(Array.isArray(res.data) ? res.data : []);
    } catch {
        wrap.innerHTML = `<div class="alert alert-danger">${PM.my.load_error}</div>`;
    }
})();

function render(rows) {
    const wrap = document.getElementById('settlements-wrap');
    if (!rows.length) {
        wrap.innerHTML = `<div class="card card-flush"><div class="card-body text-center text-muted py-14 fs-6">${PM.my.empty}</div></div>`;
        return;
    }

    wrap.innerHTML = `<div class="d-flex flex-column gap-4">${rows.map(row => {
        const b = row.booking ?? {};
        const stCls = PAY_STATUS_CLS[row.status] ?? 'badge-light-secondary';
        const dates = (b.travel_date_from || b.travel_date_to)
            ? `${formatDate(b.travel_date_from)} — ${formatDate(b.travel_date_to)}` : '';

        const refDue = (row.ref_currency && row.ref_currency !== 'AZN')
            ? `<span class="text-muted fs-8 ms-1">≈ ${formatCurrency(row.ref_due, row.ref_currency)}</span>` : '';

        const payments = (row.payments ?? []).map(p => {
            const proof = (p.proof ?? []).map(f =>
                `<a href="#" onclick="downloadProof(${f.id}, '${String(f.filename).replace(/'/g, "\\'")}'); return false;" class="text-primary fs-8 ms-2"><i class="ki-outline ki-paper-clip fs-7 me-1"></i>${PM.panel.proof}</a>`).join('');
            const azn = p.currency !== 'AZN' ? ` <span class="text-muted fs-8">(≈ ${formatCurrency(p.amount_base, 'AZN')})</span>` : '';
            return `
            <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2 mb-2">
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-gray-800 fs-7">${formatCurrency(p.amount, p.currency)}${azn}</div>
                    <div class="text-muted fs-8">${formatDate(p.paid_at)}${p.reference ? ' · ' + escHtml(p.reference) : ''}${proof}</div>
                </div>
            </div>`;
        }).join('') || `<div class="text-muted fs-8">${PM.panel.no_payments}</div>`;

        return `
        <div class="card card-flush">
            <div class="card-body py-5">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <span class="fw-bold text-gray-900 fs-5">${PM.my.booking.replace(':id', b.id)}</span>
                        ${dates ? `<span class="text-muted fs-7 ms-2"><i class="ki-outline ki-calendar fs-7 me-1"></i>${escHtml(dates)}</span>` : ''}
                        ${b.destination ? `<span class="text-muted fs-7 ms-2"><i class="ki-outline ki-geolocation fs-7 me-1"></i>${escHtml(b.destination)}</span>` : ''}
                    </div>
                    <span class="badge ${stCls} fs-7">${PM.status[row.status]}</span>
                </div>
                <div class="d-flex flex-wrap gap-6 mb-3">
                    <div><div class="text-muted fs-8 text-uppercase">${PM.panel.due}</div><div class="fw-bold fs-6">${formatCurrency(row.due, 'AZN')}${refDue}</div></div>
                    <div><div class="text-muted fs-8 text-uppercase">${PM.panel.paid}</div><div class="fw-bold fs-6 text-success">${formatCurrency(row.paid, 'AZN')}</div></div>
                    <div><div class="text-muted fs-8 text-uppercase">${PM.panel.remaining}</div><div class="fw-bold fs-6 ${row.remaining > 0 ? 'text-warning' : ''}">${formatCurrency(row.remaining, 'AZN')}</div></div>
                </div>
                ${payments}
            </div>
        </div>`;
    }).join('')}</div>`;
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
</script>
@endpush
