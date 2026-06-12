@extends('layouts.supplier')

@section('title', 'Предложение')
@section('page-title', 'Предложение')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('supplier.offers.index') }}" class="text-muted text-hover-primary">Мои предложения</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted" id="breadcrumb-title">Оффер #{{ $id }}</li>
@endsection

@section('toolbar-actions')
    <button id="btn-withdraw" class="btn btn-light-danger btn-sm d-none" onclick="withdrawOffer()">
        <i class="ki-outline ki-cross-circle fs-4 me-1"></i>Отозвать предложение
    </button>
@endsection

@section('content')

<div id="page-loader" class="text-center py-20">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div id="page-content" class="d-none">

    {{-- Offer info --}}
    <div class="card card-flush mb-6">
        <div class="card-body py-7">

            <div class="d-flex align-items-start justify-content-between gap-4 flex-wrap mb-3">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-2">Оффер #{{ $id }}</h2>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="offer-status-badge"></span>
                        <span class="text-muted fs-7" id="offer-created"></span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fs-1 fw-bolder text-gray-900 lh-1" id="offer-price">—</div>
                    <div id="offer-valid" class="text-muted fs-8 mt-1"></div>
                </div>
            </div>

            <div class="separator my-5"></div>

            <div class="row g-4 mb-0">
                <div class="col-6 col-xl-3">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-primary d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-document fs-4 text-primary"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">Запрос</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-rfq">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-primary d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-category fs-4 text-primary"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">Услуга</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-covered">—</div>
                        </div>
                    </div>
                </div>
                <div id="info-resource-wrap" class="col-6 col-xl-3 d-none">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-40px h-40px rounded-2 bg-light-success d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="ki-outline ki-archive fs-4 text-success"></i>
                        </span>
                        <div>
                            <div class="text-muted fs-8">Ресурс / описание</div>
                            <div class="fw-semibold text-gray-800 fs-7 mt-1" id="info-resource">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="info-notes-wrap" class="d-none">
                <div class="separator my-4"></div>
                <div class="d-flex align-items-start gap-3 bg-light rounded-2 p-4">
                    <i class="ki-outline ki-message-text-2 fs-3 text-gray-500 mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="text-muted fs-8 mb-1">Примечания</div>
                        <div class="text-gray-700 fs-7 lh-lg" id="info-notes"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Вложения (read-only: на деталке оффера всегда только просмотр/скачивание) --}}
    <div class="mb-6">
        @include('components.attachments', ['entityType' => 'offers', 'canUpload' => false])
    </div>

</div>

@endsection

@push('scripts')
<script>
const offerId = {{ $id }};


const SERVICE_LABELS = window.SERVICE_LABELS;

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fmtDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
// «Действительно до» — момент: дата+время в локальном поясе + GMT-метка.
function fmtDeadline(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    const s = d.toLocaleString('ru-RU', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
    let off = '';
    try { off = new Intl.DateTimeFormat('ru-RU', { timeZoneName:'shortOffset' }).formatToParts(d).find(p => p.type === 'timeZoneName')?.value || ''; } catch (e) {}
    return off ? `${s} (${off})` : s;
}

function fmtMoney(amount, currency) {
    if (amount == null) return '—';
    return Number(amount).toLocaleString('ru-RU') + ' ' + (currency ?? '');
}

// covered/uncovered приходят массивом значений типов услуг — показываем лейблы.
function serviceList(val) {
    if (val == null) return '—';
    const arr = Array.isArray(val) ? val : [val];
    const out = arr.filter(Boolean).map(v => SERVICE_LABELS[v] ?? v).join(', ');
    return out || '—';
}

async function withdrawOffer() {
    if (!confirm('Отозвать это предложение?')) return;
    const btn = document.getElementById('btn-withdraw');
    btn.disabled = true;
    try {
        await api.patch(`/offers/${offerId}/withdraw`);
        showToast('Предложение отозвано');
        window.location.reload();
    } catch (err) {
        showToast(err?.message ?? 'Не удалось отозвать предложение', 'error');
        btn.disabled = false;
    }
}

(async function init() {
    let data;
    try {
        data = await api.get(`/offers/${offerId}`);
    } catch (err) {
        document.getElementById('page-loader').innerHTML = `
            <div class="text-center py-20">
                <i class="ki-outline ki-lock-2 fs-4x text-primary mb-4 d-block"></i>
                <div class="fw-semibold fs-5 text-gray-700">Нет доступа к этому предложению</div>
                <div class="text-muted fs-7 mt-2 mb-6">Предложение не найдено или принадлежит другому поставщику</div>
                <a href="{{ route('supplier.offers.index') }}" class="btn btn-light btn-sm">
                    <i class="ki-outline ki-arrow-left fs-5 me-1"></i>К предложениям
                </a>
            </div>`;
        return;
    }

    const o = data?.data;
    if (!o) return;

    document.getElementById('page-loader').classList.add('d-none');
    document.getElementById('page-content').classList.remove('d-none');

    document.getElementById('breadcrumb-title').textContent  = `Оффер #${offerId}`;
    document.getElementById('offer-status-badge').innerHTML  = `<span class="badge ${o.status_badge_class} fs-7">${esc(o.status_label)}</span>`;
    document.getElementById('offer-created').textContent     = o.created_at ? 'Создан ' + fmtDate(o.created_at) : '';
    document.getElementById('offer-price').textContent       = fmtMoney(o.unit_price, o.currency);

    // Срок действия актуален только для активных офферов (получено/рассматривается).
    // У выбранного — срок неважен, показываем позитивную пометку вместо «(истёк)».
    const validEl = document.getElementById('offer-valid');
    if (o.status === 'selected') {
        validEl.innerHTML = '<span class="text-success fw-semibold"><i class="ki-outline ki-check-circle fs-7 me-1"></i>Выбрано агентством</span>';
    } else if (o.status === 'received' || o.status === 'reviewed') {
        validEl.textContent = o.valid_until ? `Действует до ${fmtDeadline(o.valid_until)}${o.is_expired ? ' (истёк)' : ''}` : '';
    } else if (o.status === 'expired') {
        validEl.textContent = o.valid_until ? `Срок истёк ${fmtDeadline(o.valid_until)}` : 'Срок истёк';
    } else {
        validEl.textContent = '';   // отклонено / отозвано — срок неактуален
    }

    document.getElementById('info-rfq').textContent          = o.rfq?.title ?? `Запрос #${o.rfq_id}`;
    // Оффер на один запрос = одна услуга, поэтому «Тип услуги» и «Покрываемые
    // услуги» совпадали — оставили одну ячейку «Услуга».
    document.getElementById('info-covered').textContent      = serviceList(o.covered_services) !== '—'
        ? serviceList(o.covered_services)
        : (SERVICE_LABELS[o.rfq_service_type] ?? o.rfq_service_type ?? '—');

    if (o.notes) {
        document.getElementById('info-notes-wrap').classList.remove('d-none');
        document.getElementById('info-notes').textContent = o.notes;
    }

    // Отозвать можно активный оффер (получено/рассматривается) — как на индексе/RFQ.
    const canWithdraw = o.status === 'received' || o.status === 'reviewed';
    document.getElementById('btn-withdraw').classList.toggle('d-none', !canWithdraw);

    // Вложения — только просмотр/скачивание (управление живёт на странице запроса).
    loadAttachments('offers', offerId, false);

    // Оффер привязан к одному запросу (одна услуга), поэтому отдельный блок позиций
    // не нужен — название/описание ресурса показываем строкой в карточке выше.
    const resourceName = (o.items ?? []).map(i => i.name).find(n => n && n.trim());
    if (resourceName) {
        document.getElementById('info-resource-wrap').classList.remove('d-none');
        document.getElementById('info-resource').textContent = resourceName;
    }
})();
</script>
@endpush
