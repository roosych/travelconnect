@extends('layouts.agency')

@section('title', 'Бронирование #' . $id)
@section('page-title', 'Детали бронирования')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('agency.bookings.index') }}" class="text-muted text-hover-primary">Бронирования</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted">Бронирование #{{ $id }}</li>
@endsection

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
                        <h2 class="fw-bold text-gray-900 mb-0 fs-3">Бронирование #{{ $id }}</h2>
                        <span id="status-badge"></span>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <span class="text-muted fs-7" id="header-confirmed"></span>
                        <span id="header-request-link"></span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted fs-7 mb-1">Итоговая сумма</div>
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

    {{-- Info + Proposal --}}
    <div class="row g-6">

        <div class="col-lg-5">
            <div class="card card-flush h-100">
                <div class="card-header pt-5 pb-0">
                    <h4 class="card-title fw-bold text-gray-800 fs-6">Детали поездки</h4>
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
                        Коммерческое предложение
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
                        <div class="fw-bold text-gray-800 mb-1">Примечание</div>
                        <div class="text-gray-600 fs-6" id="notes-text"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const bookingId = {{ $id }};


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

function escHtml(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
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
            '<div class="alert alert-danger text-center">Не удалось загрузить данные.</div>';
    }
})();

function render(b) {
    document.getElementById('page-loader').classList.add('d-none');
    document.getElementById('page-content').classList.remove('d-none');

    // Header
    const sCls   = b.status_badge_class ?? 'badge-light-secondary';
    const sLabel = b.status_label      ?? b.status ?? '—';
    document.getElementById('status-badge').innerHTML = `<span class="badge ${sCls} fs-7">${sLabel}</span>`;
    document.getElementById('header-price').textContent = formatCurrency(b.final_price, b.currency);

    const confirmedStr = b.confirmed_at
        ? 'Подтверждено ' + formatDate(b.confirmed_at) : '';
    document.getElementById('header-confirmed').textContent = confirmedStr;

    const req = b.proposal?.request;
    if (req?.id) {
        document.getElementById('header-request-link').innerHTML =
            `<a href="/agency/requests/${req.id}" class="d-flex align-items-center gap-1 text-primary fs-7 fw-semibold">
                <i class="ki-outline ki-document fs-7"></i>${escHtml(req.title ?? 'Заявка #' + req.id)}
                <i class="ki-outline ki-arrow-up-right fs-8"></i>
            </a>`;
    }

    // Stepper
    renderTimeline(b);

    // Trip details
    renderTripDetails(b);

    // Proposal
    if (b.proposal?.id) {
        loadProposal(b.proposal.id);
    } else {
        document.getElementById('proposal-body').innerHTML =
            '<span class="text-muted fs-7">Нет данных о предложении.</span>';
    }

    // Notes
    if (b.notes) {
        document.getElementById('notes-text').textContent = b.notes;
        document.getElementById('notes-wrap').classList.remove('d-none');
    }
}

function renderTimeline(b) {
    const el = document.getElementById('status-timeline');

    if (b.status === 'cancelled') {
        el.innerHTML = `
        <div class="d-flex align-items-center gap-3 p-4 bg-light-danger rounded">
            <i class="ki-outline ki-cross-circle fs-2 text-danger"></i>
            <div>
                <div class="fw-bold text-danger">Бронирование отменено</div>
                ${b.notes ? `<div class="text-muted fs-7 mt-1">${escHtml(b.notes)}</div>` : ''}
            </div>
        </div>`;
        return;
    }

    const ORDER = [
        { key: 'confirmed',        label: 'Подтверждено' },
        { key: 'awaiting_payment', label: 'Счёт выставлен' },
        { key: 'paid',             label: 'Оплачено' },
        { key: 'in_progress',      label: 'В пути' },
        { key: 'completed',        label: 'Завершено' },
    ];
    const isCompleted = b.status === 'completed';
    const idx = ORDER.findIndex(s => s.key === b.status);

    // Текущий статус — выполненный шаг (зелёный), следующий — активный (синий).
    window.renderStepper(el, ORDER.map((s, i) => ({
        label:  s.label,
        done:   isCompleted || (idx > -1 && i <= idx),
        active: !isCompleted && i === idx + 1,
    })));
}

function renderTripDetails(b) {
    const rows = [];

    const dates = (b.travel_date_from || b.travel_date_to)
        ? `${formatDate(b.travel_date_from)} — ${formatDate(b.travel_date_to)}`
        : '—';

    rows.push(detailRow('ki-calendar', 'Даты поездки', dates));

    if (b.pax_count) {
        rows.push(detailRow('ki-people', 'Туристы', `${b.pax_count} чел.`));
    }

    if (b.operator?.name) {
        rows.push(detailRow('ki-user', 'Оператор', escHtml(b.operator.name)));
    }

    rows.push(detailRow('ki-check-circle', 'Подтверждено', formatDate(b.confirmed_at ?? b.created_at)));

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
            '<div class="alert alert-danger">Не удалось загрузить предложение.</div>';
    }
}

function renderProposal(p) {
    const [sCls, sLabel] = {
        sent:     ['badge-light-primary', 'Отправлено'],
        accepted: ['badge-light-success', 'Принято'],
        rejected: ['badge-light-danger',  'Отклонено'],
    }[p.status] ?? ['badge-light-secondary', p.status];

    document.getElementById('proposal-badge').innerHTML =
        `<span class="badge ${sCls} fs-8">${sLabel}</span>`;

    const offers = p.offers ?? [];

    const offerRows = offers.map(o => {
        const type     = o.rfq_service_type ?? 'other';
        const icon     = SERVICE_ICONS[type] ?? 'ki-tag';
        const iconCls  = SERVICE_COLORS[type] ?? 'bg-light text-gray-600';
        const title    = o.rfq_title || `Услуга #${o.id}`;
        const supplier = o.supplier?.name ?? '';

        return `
        <div class="d-flex align-items-center gap-3 p-3 rounded bg-light mb-2">
            <span class="w-36px h-36px rounded-circle ${iconCls} d-flex align-items-center justify-content-center flex-shrink-0">
                <i class="ki-outline ${escHtml(icon)} fs-6"></i>
            </span>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-gray-800 fs-7">${escHtml(title)}</div>
                ${supplier ? `<div class="text-muted fs-8">${escHtml(supplier)}</div>` : ''}
            </div>
        </div>`;
    }).join('');

    const noOffers = !offers.length
        ? `<div class="text-muted fs-7 py-4 text-center">Нет услуг в предложении.</div>` : '';

    document.getElementById('proposal-body').innerHTML = `
        ${p.description ? `<div class="text-gray-600 fs-7 mb-4 fst-italic">${escHtml(p.description)}</div>` : ''}
        ${p.valid_until ? `<div class="text-muted fs-8 mb-3">
            <i class="ki-outline ki-calendar fs-8 me-1"></i>
            Действует до ${formatDate(p.valid_until)}
            ${p.is_expired ? '<span class="badge badge-light-danger ms-2 fs-9">Срок истёк</span>' : ''}
        </div>` : ''}
        <div class="mb-2 text-gray-400 fs-8 fw-bold text-uppercase">
            Состав
            ${offers.length ? `<span class="badge badge-light-secondary ms-1">${offers.length}</span>` : ''}
        </div>
        ${offerRows}${noOffers}
        <div class="d-flex align-items-center justify-content-between bg-light rounded p-4 mt-3">
            <span class="text-gray-600 fw-semibold fs-7">Итого</span>
            <span class="fw-bolder text-gray-900 fs-4">${formatCurrency(p.total_price, p.currency)}</span>
        </div>`;
}
</script>
@endpush
