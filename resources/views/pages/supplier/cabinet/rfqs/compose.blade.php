@extends('layouts.supplier')

@section('title', __('suppliers.cabinet.rfqs.compose.title'))
@section('page-title', __('suppliers.cabinet.rfqs.compose.title'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('supplier.rfqs.index') }}" class="text-muted text-hover-primary">{{ __('suppliers.cabinet.rfqs.compose.breadcrumb') }}</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted">{{ __('suppliers.cabinet.rfqs.compose.title') }}</li>
@endsection

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
    .filepond--root       { font-family: inherit; }
    .filepond--panel-root { background: #f9f9f9; border: 2px dashed #e4e6ef; border-radius: 8px; }
    .filepond--drop-label { color: #a1a5b7; }
    .filepond--item-panel { background: #1e1e2d; }

    #modal-price { -moz-appearance: textfield; }
    #modal-price::-webkit-outer-spin-button,
    #modal-price::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #modal-currency-label { border: none; background: var(--bs-gray-100, #f5f8fa); padding-left: 6px; padding-right: 0; }

    .svc-item {
        display: flex;
        align-items: center;
        padding: 14px 16px;
        border: 1.5px solid #eff2f5;
        border-radius: 10px;
        gap: 14px;
        transition: border-color .15s, background .15s, box-shadow .15s;
        background: #fff;
    }
    .svc-item.is-pending { cursor: pointer; }
    .svc-item.is-pending:hover { border-color: #009ef7; box-shadow: 0 2px 8px rgba(0,158,247,.08); }
    .svc-item.is-submitted { border-color: #50cd89; background: #f6fff9; }
    .svc-item.is-closed  { opacity: .65; }
</style>
@endpush

@section('content')

<div id="page-loader" class="text-center py-20">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div id="page-content" class="d-none">

    {{-- Request header --}}
    <div class="card card-flush mb-6">
        <div class="card-body py-5">
            <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">
                <h2 class="fw-bold text-gray-900 mb-0 fs-4" id="req-title">—</h2>
                <a href="{{ route('supplier.rfqs.index') }}" class="btn btn-sm btn-light flex-shrink-0">
                    <i class="ki-outline ki-arrow-left fs-6 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.back') }}
                </a>
            </div>
            <div id="req-cities" class="text-gray-700 fs-6 mt-2 d-none"></div>
            <div id="req-deadline" class="d-none mt-3">
                <span class="text-muted fs-8 me-1">
                    <i class="ki-outline ki-calendar fs-7 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.sub_deadline') }}
                </span>
                <span class="fw-semibold text-gray-700 fs-7" id="req-deadline-val"></span>
            </div>
        </div>
    </div>

    {{-- Offer parameters --}}
    <div class="card card-flush mb-6">
        <div class="card-header pt-6 pb-4">
            <div class="card-title flex-column gap-1">
                <h3 class="fw-bold text-gray-900 mb-0">{{ __('suppliers.cabinet.rfqs.compose.svc_section') }}</h3>
                <span class="text-muted fs-7">{{ __('suppliers.cabinet.rfqs.compose.svc_hint') }}</span>
            </div>
        </div>
        <div class="card-body pb-7">

            {{-- Service list — ответ на каждую услугу через модалку (свой срок/примечания/файлы) --}}
            <div id="svc-list" class="d-flex flex-column gap-3"></div>

        </div>
    </div>

</div>

{{-- Modal: fill price for one service --}}
<div class="modal fade" id="svcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span id="modal-type-icon"
                              class="w-32px h-32px rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"></span>
                        <h4 class="modal-title fw-bold mb-0" id="modal-svc-title">{{ __('suppliers.cabinet.rfqs.compose.modal_service') }}</h4>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-5">
                <div class="row g-6">

                    {{-- Левая колонка: что предлагаете + цена --}}
                    <div class="col-md-6">
                        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">{{ __('suppliers.cabinet.rfqs.compose.what_offer') }}</div>

                        {{-- Переключатель источника: каталог / вручную. Виден при наличии ресурсов. --}}
                        <div id="modal-source-toggle" class="btn-group w-100 mb-4 d-none" role="group">
                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary flex-fill active"
                                    data-source="catalog" onclick="setModalSource('catalog')">
                                <i class="ki-outline ki-category fs-6 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.from_catalog') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary flex-fill"
                                    data-source="manual" onclick="setModalSource('manual')">
                                <i class="ki-outline ki-pencil fs-6 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.manual') }}
                            </button>
                        </div>

                        <div id="modal-catalog-wrap" class="mb-4 d-none">
                            <label class="form-label fw-semibold">
                                {{ __('suppliers.cabinet.rfqs.compose.catalog_res') }}
                                <span class="text-muted fw-normal fs-8">{{ __('suppliers.cabinet.rfqs.compose.optional') }}</span>
                            </label>
                            <select id="modal-catalog-sel" class="form-select form-select-solid"
                                    onchange="onModalCatalogChange()">
                                <option value="">{{ __('suppliers.cabinet.rfqs.compose.choose_res') }}</option>
                            </select>
                            <div id="modal-catalog-hint" class="text-muted fs-8 mt-2 d-none">
                                <i class="ki-outline ki-information-3 fs-8 me-1"></i><span></span>
                            </div>
                        </div>

                        <div class="mb-4" id="modal-resource-name-wrap">
                            <label class="form-label fw-semibold">
                                {{ __('suppliers.cabinet.rfqs.compose.res_name') }}
                                <span class="text-muted fw-normal fs-8">{{ __('suppliers.cabinet.rfqs.compose.optional') }}</span>
                            </label>
                            <input type="text" id="modal-resource-name"
                                   class="form-control form-control-solid"
                                   placeholder="{{ __('suppliers.cabinet.rfqs.compose.res_name_ph') }}" />
                        </div>

                        <div>
                            <label class="form-label fw-semibold required">{{ __('suppliers.cabinet.rfqs.compose.price') }}</label>
                            <div class="input-group">
                                <input type="number" id="modal-price"
                                       class="form-control form-control-solid form-control-lg"
                                       placeholder="0.00" min="0.01" step="0.01" />
                                <span class="input-group-text fw-semibold fs-6 pe-3" id="modal-currency-label"></span>
                            </div>
                            <div id="modal-price-error" class="text-danger fs-8 mt-2 d-none">{{ __('suppliers.cabinet.rfqs.compose.price_err') }}</div>
                            <div id="modal-price-hint" class="d-flex align-items-start gap-2 mt-3 p-3 bg-light-warning rounded">
                                <i class="ki-outline ki-information-5 fs-5 text-warning flex-shrink-0 mt-1"></i>
                                <span class="text-gray-700 fs-7" id="modal-price-hint-text"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Правая колонка: примечания + файлы (срок действия задаёт сервер) --}}
                    <div class="col-md-6">
                        <div class="text-gray-500 fw-bold fs-8 text-uppercase mb-3">{{ __('suppliers.cabinet.rfqs.compose.terms') }}</div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                {{ __('suppliers.cabinet.rfqs.compose.notes') }} <span class="text-muted fw-normal fs-8">{{ __('suppliers.cabinet.rfqs.compose.optional') }}</span>
                            </label>
                            <textarea id="modal-notes" class="form-control form-control-solid" rows="3"
                                      placeholder="{{ __('suppliers.cabinet.rfqs.compose.notes_ph') }}"></textarea>
                        </div>

                        <div>
                            <label class="form-label fw-semibold">
                                <i class="ki-outline ki-paper-clip fs-6 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.files') }}
                                <span class="text-muted fw-normal fs-8">{{ __('suppliers.cabinet.rfqs.compose.optional') }}</span>
                            </label>
                            <input type="file" id="fp-modal-input" multiple />
                        </div>
                    </div>

                </div>

                <div id="modal-submit-error" class="alert alert-danger d-none mt-5 mb-0 py-3"></div>

            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('suppliers.cabinet.rfqs.compose.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-modal-ok">
                    <span class="indicator-label"><i class="ki-outline ki-send fs-5 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.send') }}</span>
                    <span class="indicator-progress">{{ __('suppliers.cabinet.rfqs.compose.sending') }}<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Быстрый просмотр услуги: детали запроса оператора (read-only) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="svc-detail-drawer" style="width:460px;max-width:92vw">
    <div class="offcanvas-header border-bottom">
        <h4 class="offcanvas-title fw-bold" id="svc-detail-title">{{ __('suppliers.cabinet.rfqs.compose.details') }}</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="svc-detail-body"></div>
</div>

{{-- Подтверждение отзыва предложения (текст зависит от статуса) --}}
<div class="modal fade" id="modal-withdraw" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-475px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('suppliers.cabinet.rfqs.compose.wd_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-5">
                <div class="fs-6 text-gray-700" id="withdraw-msg"></div>
                <div id="withdraw-error" class="alert alert-danger d-none mt-3 mb-0 py-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('suppliers.cabinet.rfqs.compose.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="btn-withdraw-confirm">
                    <span class="indicator-label"><i class="ki-outline ki-cross-circle fs-6 me-1"></i>{{ __('suppliers.cabinet.rfqs.compose.wd_confirm') }}</span>
                    <span class="indicator-progress">{{ __('suppliers.cabinet.rfqs.compose.wd_progress') }}<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
FilePond.registerPlugin(FilePondPluginFileValidateSize);

const requestId        = {{ $requestId }};
const supplierId       = {{ $supplier?->id ?? 'null' }};
const supplierCurrency = '{{ strtoupper($supplier?->currency_code ?? 'AZN') }}';
const USER_TZ          = @json($userTimezone);

// ── i18n ────────────────────────────────────────────────────────────────────────
const L   = @json(__('suppliers.cabinet.rfqs'));
const C   = L.compose;
const LOC = window.APP_LOCALE || 'ru';

// Множественное число: ru — три формы, en — две (1/много), az — единственная.
function plForm(n, forms) {
    if (LOC === 'ru') {
        const m10 = n % 10, m100 = n % 100;
        if (m10 === 1 && m100 !== 11) return forms.one;
        if (m10 >= 2 && m10 <= 4 && (m100 < 10 || m100 >= 20)) return forms.few;
        return forms.many;
    }
    if (LOC === 'en') return n === 1 ? forms.one : forms.many;
    return forms.one;
}

let allRfqs          = [];
let rfqOffersMap     = {};
let catalogByType    = {};
let activeCurrencies = [];
let composePond      = null;

// draftMap key = `${rfqId}:${type}`, value = { rfqId, type, price, catalogId, catalogHint, resourceName, source }
let draftMap = {};

let modalCtx    = null;      // { rfqId, type }
let modalSource = 'manual';  // 'catalog' | 'manual' — текущий режим заполнения в модалке

// Перенос примечаний между услугами (одинаковые условия — не вводить заново).
let lastNotes = '';

// Лейблы из каталога; цвет/иконка нейтральные (фолбэк в местах использования).
const SERVICE_LABELS = window.SERVICE_LABELS;
const SVC_ICON = {};
const SVC_COLOR = {};
const PRICE_UNIT_SHORT = C.unit;

function esc(str) {
    return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function fmtDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
// Дедлайн — момент (datetime). Показываем в часовом поясе аккаунта (как у
// оператора и везде в системе), а не в поясе браузера, + GMT-метка.
function fmtDeadline(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    const s = d.toLocaleString('ru-RU', {
        timeZone: USER_TZ, day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit',
    });
    let off = '';
    try {
        off = new Intl.DateTimeFormat('ru-RU', { timeZone: USER_TZ, timeZoneName: 'shortOffset' })
            .formatToParts(d).find(p => p.type === 'timeZoneName')?.value || '';
    } catch (e) { /* пояс не распознан */ }
    return off ? `${s} (${off})` : s;
}
function fmtMoney(amount, currency) {
    if (amount == null || amount === '') return '—';
    return Number(amount).toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
        + ' ' + (currency ?? '');
}

// ── FilePond ──────────────────────────────────────────────────────────────────

// Stores { serverId: attachmentId } for uploaded temp files
let uploadedTempIds = [];

function initComposePond() {
    const input = document.getElementById('fp-modal-input');
    if (!input || composePond) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    composePond = FilePond.create(input, {
        allowMultiple:           true,
        maxFiles:                10,
        maxFileSize:             '20MB',
        allowFileTypeValidation: false,
        credits:                 false,
        labelIdle: C.fp_idle
                 + `<br><span style="font-size:11px;color:#a1a5b7">${C.fp_hint}</span>`,
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                const formData = new FormData();
                formData.append('file', file, file.name);

                const controller = new AbortController();

                fetch('/api/attachments/temp', {
                    method:      'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  csrfToken,
                    },
                    body:   formData,
                    signal: controller.signal,
                }).then(async res => {
                    const data = await res.json();
                    if (res.ok && data?.data?.id) {
                        uploadedTempIds.push(data.data.id);
                        load(String(data.data.id));
                    } else {
                        error(data?.message ?? data?.errors?.file?.[0] ?? C.upload_err);
                    }
                }).catch(err => {
                    if (err.name !== 'AbortError') error(C.network_err);
                });

                return { abort: () => controller.abort() };
            },
            revert: (uniqueFileId, load, error) => {
                const id = parseInt(uniqueFileId);
                uploadedTempIds = uploadedTempIds.filter(x => x !== id);
                fetch('/api/attachments/' + id, {
                    method:      'DELETE',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                }).then(load).catch(error);
            },
        },
    });
}

function destroyComposePond() {
    if (composePond) { try { composePond.destroy(); } catch {} composePond = null; }
    uploadedTempIds = [];
}

function getUploadedTempIds() {
    return [...uploadedTempIds];
}

// ── Load ──────────────────────────────────────────────────────────────────────

async function loadCurrencies() {
    try {
        const data = await api.get('/settings/currencies/active');
        activeCurrencies = data.data ?? [];
    } catch {
        activeCurrencies = [{ code: supplierCurrency, name: supplierCurrency }];
    }
}

async function loadCatalog() {
    if (!supplierId) return;
    try {
        const data = await api.get(`/suppliers/${supplierId}/services`);
        const services = Array.isArray(data) ? data : (data.data ?? []);
        catalogByType = {};
        services.forEach(s => {
            if (!catalogByType[s.type]) catalogByType[s.type] = [];
            catalogByType[s.type].push(s);
        });
    } catch {}
}

async function loadRfqs() {
    const data = await api.get(`/rfqs?request_id=${requestId}&per_page=50`);
    allRfqs = (data.data ?? []).filter(r => (r.request?.id ?? r.request_id) == requestId);
}

async function loadAllOffers() {
    await Promise.all(allRfqs.map(async rfq => {
        try {
            const data = await api.get(`/rfqs/${rfq.id}/offers`);
            rfqOffersMap[rfq.id] = data.data ?? [];
        } catch {
            rfqOffersMap[rfq.id] = [];
        }
    }));
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function getAssignedTypes(rfq) {
    const myPivot = (rfq.suppliers ?? []).find(s => s.id === supplierId);
    const pivot   = myPivot?.pivot_service_types ?? [];
    return pivot.length ? pivot : [rfq.service_type];
}

function getActiveOffer(rfqId) {
    return (rfqOffersMap[rfqId] ?? []).find(
        o => !['withdrawn', 'rejected', 'expired'].includes(o.status)
    );
}

function isPendingRfq(rfq) {
    return !getActiveOffer(rfq.id) && ['sent', 'awaiting'].includes(rfq.status);
}

function draftKey(rfqId, type) { return `${rfqId}:${type}`; }

// Build flat list: [{ rfq, type, state, offer?, lastOffer? }]
function buildSvcItems() {
    const items = [];
    for (const rfq of allRfqs) {
        const types       = getAssignedTypes(rfq);
        const activeOffer = getActiveOffer(rfq.id);
        const isPending   = !activeOffer && ['sent', 'awaiting'].includes(rfq.status);
        for (const type of types) {
            if (activeOffer) {
                items.push({ rfq, type, state: 'submitted', offer: activeOffer });
            } else if (isPending) {
                items.push({ rfq, type, state: 'pending' });
            } else {
                items.push({ rfq, type, state: 'closed', lastOffer: (rfqOffersMap[rfq.id] ?? [])[0] });
            }
        }
    }
    return items;
}

// ── Render ────────────────────────────────────────────────────────────────────

function renderPage() {
    document.getElementById('page-loader').classList.add('d-none');
    document.getElementById('page-content').classList.remove('d-none');

    renderRequestHeader();
    renderSvcList();
}

function pluralDays(n) {
    return plForm(n, C.day);
}

function renderRequestHeader() {
    // Сегмент (страна поставщика всегда его же → не показываем; нужны даты и города).
    const seg      = allRfqs.find(r => r.segment)?.segment ?? {};
    const segDates = (seg.date_from || seg.date_to) ? `${fmtDate(seg.date_from)} — ${fmtDate(seg.date_to)}` : '';
    const cities   = (seg.destinations ?? []).filter(Boolean);

    document.getElementById('req-title').innerHTML = segDates
        ? `<i class="ki-outline ki-calendar fs-3 me-2 text-gray-500"></i>${esc(segDates)}`
        : C.title;

    const citiesEl = document.getElementById('req-cities');
    if (cities.length) {
        citiesEl.innerHTML = `<i class="ki-outline ki-geolocation fs-6 me-1 text-gray-500"></i>${cities.map(esc).join(', ')}`;
        citiesEl.classList.remove('d-none');
    } else {
        citiesEl.classList.add('d-none');
    }

    const deadlines = allRfqs.filter(r => r.deadline_at).map(r => r.deadline_at).sort();
    const deadline  = deadlines[0] ?? null;
    const dw        = document.getElementById('req-deadline');
    if (deadline) {
        const daysLeft = Math.ceil((new Date(deadline) - Date.now()) / 86400000);
        let timerHtml = '';
        if (daysLeft > 0) {
            const cls = daysLeft <= 1 ? 'danger' : daysLeft <= 3 ? 'warning' : 'success';
            const txt = C.days_left.replace(':count', daysLeft).replace(':unit', pluralDays(daysLeft));
            timerHtml = `<span class="badge badge-light-${cls} fs-8 ms-2">${txt}</span>`;
        } else if (daysLeft === 0) {
            timerHtml = `<span class="badge badge-light-danger fs-8 ms-2">${C.last_day}</span>`;
        } else {
            timerHtml = `<span class="badge badge-light-danger fs-8 ms-2">${C.expired}</span>`;
        }
        document.getElementById('req-deadline-val').innerHTML = fmtDeadline(deadline) + timerHtml;
        dw.classList.remove('d-none');
    } else {
        dw.classList.add('d-none');
    }
}

function renderSvcList() {
    document.getElementById('svc-list').innerHTML =
        buildSvcItems().map(renderSvcItem).join('');
}

function renderSvcItem(item) {
    const { rfq, type, state } = item;
    const color = SVC_COLOR[type] ?? 'secondary';
    const icon  = SVC_ICON[type]  ?? 'ki-abstract-26';
    const label = SERVICE_LABELS[type] ?? type;
    const key   = draftKey(rfq.id, type);

    const reqSummary = (rfq.service_type === type) ? (rfq.segment?.requirements_summary || '') : '';
    const reqHtml = reqSummary
        ? `<div class="text-muted fs-7 mt-1"><i class="ki-outline ki-information-3 fs-7 me-1"></i>${esc(reqSummary)}</div>`
        : '';

    const circleColor = state === 'closed' ? 'secondary' : (state === 'submitted' ? 'success' : color);
    const circleIcon  = state === 'submitted' ? 'ki-check-circle' : icon;
    const circleText  = state === 'submitted' ? 'success' : (state === 'closed' ? 'muted' : color);
    const iconCircle  = `
        <span class="w-40px h-40px rounded-circle bg-light-${circleColor}
              d-flex align-items-center justify-content-center flex-shrink-0">
            <i class="ki-outline ${circleIcon} fs-5 text-${circleText}"></i>
        </span>`;

    const detailsBtn = `<button type="button" class="btn btn-sm btn-light"
        onclick="event.stopPropagation();showSvcDetail(${rfq.id},'${type}')">
        <i class="ki-outline ki-eye fs-6 me-1"></i>${C.details}</button>`;

    if (state === 'submitted') {
        const { offer } = item;
        // Отзыв доступен для всех активных статусов; жёсткое правило «КП отправлено/принято»
        // проверяет сервер (и вернёт ошибку в модалку). selected → строгое предупреждение.
        const canWithdraw = ['received', 'reviewed', 'selected'].includes(offer.status);
        const { resourceLine } = parseOfferNotes(offer.notes);
        const fileCount = (offer.attachments ?? []).length;
        return `
        <div class="svc-item is-submitted" data-key="${esc(key)}">
            ${iconCircle}
            <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-gray-900 fs-6">${esc(label)}</div>
                ${resourceLine ? `<div class="text-muted fs-8 mt-1"><i class="ki-outline ki-abstract-26 fs-8 me-1"></i>${esc(resourceLine.replace(/^[^:]+:\s*/, ''))}</div>` : ''}
                <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
                    <span class="badge ${offer.status_badge_class} fs-8">${esc(offer.status_label)}</span>
                    <span class="text-muted fs-8">${C.submitted_at.replace(':date', fmtDate(offer.created_at))}</span>
                    ${fileCount ? `<span class="text-muted fs-8"><i class="ki-outline ki-paper-clip fs-8 me-1"></i>${fileCount}</span>` : ''}
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                <div class="fw-bolder text-gray-900 fs-4">${fmtMoney(offer.unit_price, offer.currency)}</div>
                <div class="d-flex gap-2">
                    ${detailsBtn}
                    ${canWithdraw
                        ? `<button class="btn btn-sm btn-light-danger" onclick="withdrawOffer(${offer.id}, ${rfq.id}, '${offer.status}')">
                               <i class="ki-outline ki-cross-circle fs-7 me-1"></i>${C.wd_confirm}
                           </button>`
                        : ''}
                </div>
            </div>
        </div>`;
    }

    if (state === 'closed') {
        const { lastOffer } = item;
        return `
        <div class="svc-item is-closed" data-key="${esc(key)}">
            ${iconCircle}
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-gray-600 fs-6">${esc(label)}</div>
                <div class="mt-1"><span class="badge ${rfq.status_badge_class} fs-8">${esc(rfq.status_label)}</span></div>
            </div>
            <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                ${lastOffer
                    ? `<div class="fw-semibold text-gray-600 fs-6">${fmtMoney(lastOffer.unit_price, lastOffer.currency)}</div>
                       <span class="badge ${lastOffer.status_badge_class} fs-8">${esc(lastOffer.status_label)}</span>`
                    : `<i class="ki-outline ki-lock-2 fs-2 text-gray-400"></i>`}
                ${detailsBtn}
            </div>
        </div>`;
    }

    // pending — ответ целиком в модалке (цена + срок + примечания + файлы).
    const deadline = rfq.deadline_at;
    let deadlineHtml = '';
    if (deadline) {
        const days = Math.ceil((new Date(deadline) - Date.now()) / 86400000);
        const cls  = days <= 1 ? 'danger' : days <= 3 ? 'warning' : 'gray-600';
        deadlineHtml = `<div class="text-${cls} fs-8 mt-1"><i class="ki-outline ki-timer fs-8 me-1"></i>${C.reply_by.replace(':date', fmtDeadline(deadline))}</div>`;
    }
    return `
    <div class="svc-item is-pending" data-key="${esc(key)}"
         onclick="openSvcModal('${rfq.id}', '${type}')">
        ${iconCircle}
        <div class="flex-grow-1 min-w-0">
            <div class="fw-bold text-gray-900 fs-6">${esc(label)}</div>
            ${reqHtml}
            ${deadlineHtml}
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            ${detailsBtn}
            <button class="btn btn-sm btn-primary" tabindex="-1">
                <i class="ki-outline ki-send fs-6 me-1"></i>${C.respond}
            </button>
        </div>
    </div>`;
}

// Resource lines are prepended to notes as "ServiceLabel: ResourceName\n\nUserNotes"
// This helper splits them back apart.
function parseOfferNotes(notes) {
    if (!notes?.trim()) return { resourceLine: null, userNotes: null };
    const parts = notes.split('\n\n');
    const first  = parts[0].trim();
    const serviceLabels = Object.values(SERVICE_LABELS);
    const isResource = serviceLabels.some(l => first.startsWith(l + ':'));
    if (isResource) {
        const rest = parts.slice(1).join('\n\n').trim();
        return { resourceLine: first, userNotes: rest || null };
    }
    return { resourceLine: null, userNotes: notes.trim() };
}

// ── Быстрый просмотр услуги (drawer) ────────────────────────────────────────────

const svcDetailDrawer = new bootstrap.Offcanvas(document.getElementById('svc-detail-drawer'));

function svcFileChip(a) {
    const ext = (a.filename ?? '').split('.').pop().toLowerCase();
    let c = 'text-primary';
    if (ext === 'pdf')                            c = 'text-danger';
    else if (['xls','xlsx'].includes(ext))        c = 'text-success';
    else if (['jpg','jpeg','png'].includes(ext))  c = 'text-warning';
    const name = (a.filename ?? '').length > 28 ? a.filename.slice(0, 25) + '…' : (a.filename ?? 'file');
    return `<a href="/api/attachments/${a.id}/download" target="_blank" rel="noopener"
        class="d-inline-flex align-items-center gap-2 border border-dashed rounded px-3 py-2 bg-white text-gray-700 text-hover-primary text-decoration-none">
        <i class="ki-outline ki-file fs-2 ${c}"></i>
        <div class="lh-sm"><div class="fw-semibold fs-7">${esc(name)}</div><div class="text-muted fs-8">${esc(a.human_size ?? '')}</div></div>
    </a>`;
}

function svcDetailRow(label, valueHtml) {
    return `<div class="d-flex flex-column">
        <span class="text-muted fs-8 text-uppercase fw-bold">${esc(label)}</span>
        <span class="text-gray-800 fw-semibold fs-6 mt-1">${valueHtml}</span></div>`;
}

function svcFilesBlock(title, atts) {
    if (!atts.length) return '';
    return `<div class="d-flex flex-column gap-2">
        <span class="text-muted fs-8 text-uppercase fw-bold">${esc(title)}</span>
        <div class="d-flex flex-wrap gap-3">${atts.map(svcFileChip).join('')}</div></div>`;
}

function showSvcDetail(rfqId, type) {
    rfqId = parseInt(rfqId);
    const rfq = allRfqs.find(r => r.id === rfqId);
    if (!rfq) return;

    const label    = SERVICE_LABELS[type] ?? type;
    const seg      = rfq.segment ?? {};
    const dates    = (seg.date_from || seg.date_to) ? `${fmtDate(seg.date_from)} — ${fmtDate(seg.date_to)}` : '—';
    const cities   = (seg.destinations ?? []).filter(Boolean);
    const pax      = rfq.request?.pax_count;
    const reqs     = seg.requirements_summary;
    const note     = rfq.description?.trim();
    const opAtts   = rfq.shared_attachments ?? [];
    const deadline = rfq.deadline_at;

    // Ваше предложение по этой услуге (если уже ответили) — со своими файлами/примечаниями.
    const myOffer  = getActiveOffer(rfqId);
    const myNotes  = myOffer ? parseOfferNotes(myOffer.notes).userNotes : null;
    const myAtts   = myOffer?.attachments ?? [];

    const myOfferBlock = myOffer ? `
        <div class="separator"></div>
        <div class="text-muted fs-8 text-uppercase fw-bold">${C.your_offer}</div>
        ${svcDetailRow(C.your_status, `<span class="badge ${myOffer.status_badge_class} fs-8">${esc(myOffer.status_label)}</span>`)}
        ${svcDetailRow(C.your_amount, fmtMoney(myOffer.unit_price, myOffer.currency))}
        ${myNotes ? svcDetailRow(C.your_notes, esc(myNotes)) : ''}
        ${svcFilesBlock(C.your_files, myAtts)}` : '';

    document.getElementById('svc-detail-title').textContent = label;
    document.getElementById('svc-detail-body').innerHTML = `
        <div class="d-flex flex-column gap-5">
            ${reqs ? svcDetailRow(L.detail.requirements, esc(reqs)) : ''}
            ${svcDetailRow(L.detail.dates, esc(dates))}
            ${cities.length ? svcDetailRow(L.detail.route, cities.map(esc).join(', ')) : ''}
            ${pax ? svcDetailRow(L.detail.pax, `${pax}`) : ''}
            ${deadline ? svcDetailRow(L.detail.deadline, fmtDeadline(deadline)) : ''}
            ${note ? svcDetailRow(L.detail.description, esc(note)) : ''}
            ${svcFilesBlock(C.op_files, opAtts)}
            ${myOfferBlock}
        </div>`;
    svcDetailDrawer.show();
}

// ── Modal ─────────────────────────────────────────────────────────────────────

function openSvcModal(rfqId, type) {
    rfqId = parseInt(rfqId);
    modalCtx = { rfqId, type };

    const label   = SERVICE_LABELS[type] ?? type;
    const color   = SVC_COLOR[type] ?? 'secondary';
    const icon    = SVC_ICON[type]  ?? 'ki-abstract-26';
    const rfq     = allRfqs.find(r => r.id === rfqId);
    const catalog = catalogByType[type] ?? [];
    const draft   = draftMap[draftKey(rfqId, type)];

    // Требование сегмента для этой услуги — показываем в заголовке: «Транспорт (Минибус)».
    const reqSummary = (rfq?.service_type === type) ? (rfq?.segment?.requirements_summary || '') : '';
    document.getElementById('modal-svc-title').textContent = reqSummary ? `${label} (${reqSummary})` : label;
    document.getElementById('modal-price-error').classList.add('d-none');

    const pax      = rfq?.request?.pax_count;
    const hintText = pax
        ? C.price_hint_pax.replace(':count', pax).replace(':unit', plForm(pax, L.tourists))
        : C.price_hint;
    document.getElementById('modal-price-hint-text').textContent = hintText;
    document.getElementById('modal-currency-label').textContent = supplierCurrency;

    const iconEl = document.getElementById('modal-type-icon');
    iconEl.className = `w-32px h-32px rounded-circle bg-light-${color} d-flex align-items-center justify-content-center flex-shrink-0`;
    iconEl.innerHTML = `<i class="ki-outline ${icon} fs-7 text-${color}"></i>`;

    // Catalog
    const hasCatalog  = catalog.length > 0;
    const catalogSel  = document.getElementById('modal-catalog-sel');
    const $cat        = $(catalogSel);
    if ($cat.hasClass('select2-hidden-accessible')) $cat.select2('destroy');
    if (hasCatalog) {
        catalogSel.innerHTML = `<option value=""></option>`
            + catalog.map(s => {
                const hint = s.base_price
                    ? `${C.base_rate} ${Number(s.base_price).toLocaleString('ru-RU')} ${s.currency ?? ''} ${PRICE_UNIT_SHORT[s.price_unit] ?? ''}`.trim()
                    : '';
                return `<option value="${s.id}"
                    data-price="${s.base_price ?? ''}"
                    data-hint="${esc(hint)}">${esc(s.name)}</option>`;
            }).join('');
        catalogSel.value = draft?.catalogId ?? '';
        $cat.select2({
            placeholder:    C.choose_res,
            allowClear:     true,
            width:          '100%',
            dropdownParent: $('#svcModal'),
            language:       { noResults: () => C.sel_empty, searching: () => C.sel_search },
        });
    }

    document.getElementById('modal-price').value = draft?.price ?? '';
    document.getElementById('modal-price').classList.remove('is-invalid');
    document.getElementById('modal-resource-name').value = draft?.resourceName ?? '';

    // Тумблер источника: виден только при наличии ресурсов.
    document.getElementById('modal-source-toggle').classList.toggle('d-none', !hasCatalog);

    // Стартовый режим: без ресурсов — всегда вручную; иначе режим из черновика,
    // а для нового — «из каталога» (поощряем переиспользование ресурсов).
    let initialSource;
    if (!hasCatalog)            initialSource = 'manual';
    else if (draft?.source)     initialSource = draft.source;
    else if (draft)             initialSource = draft.catalogId ? 'catalog' : 'manual';
    else                        initialSource = 'catalog';
    setModalSource(initialSource);

    // Срок / примечания / файлы — для оффера именно по этой услуге.
    setupModalOfferFields(rfq);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('svcModal')).show();
    setTimeout(() => document.getElementById('modal-price').focus(), 350);
}

// Готовит поля оффера в модалке: примечания + файлы.
// Срок действия поставщик не задаёт — его подставляет сервер.
function setupModalOfferFields(rfq) {
    document.getElementById('modal-submit-error').classList.add('d-none');

    // Примечания — перенос с прошлой услуги (редактируемо).
    document.getElementById('modal-notes').value = lastNotes || '';

    // Файлы — всегда заново (свежий FilePond, пустой набор temp-id).
    destroyComposePond();
    initComposePond();
}

// Переключение режима заполнения. Видимость полей задаётся здесь, чтобы не
// зависеть от выбора в селекте: каталог → селект ресурсов; вручную → текстовое поле.
function setModalSource(source) {
    modalSource = source;

    document.querySelectorAll('#modal-source-toggle [data-source]').forEach(b => {
        b.classList.toggle('active', b.dataset.source === source);
    });

    const catalogWrap = document.getElementById('modal-catalog-wrap');
    const nameWrap    = document.getElementById('modal-resource-name-wrap');

    if (source === 'catalog') {
        catalogWrap.classList.remove('d-none');
        nameWrap.classList.add('d-none');
        onModalCatalogChange(false); // обновить подсказку/имя, цену не трогаем
    } else {
        catalogWrap.classList.add('d-none');
        document.getElementById('modal-catalog-hint').classList.add('d-none');
        nameWrap.classList.remove('d-none');
    }
}

// Вызывается при выборе ресурса (autofillPrice=true) и программно из setModalSource
// (autofillPrice=false — чтобы не затирать цену из черновика/введённую вручную).
function onModalCatalogChange(autofillPrice = true) {
    const sel    = document.getElementById('modal-catalog-sel');
    const opt    = sel.options[sel.selectedIndex];
    const hint   = opt?.dataset?.hint ?? '';
    const price  = opt?.dataset?.price ?? '';
    const hintEl = document.getElementById('modal-catalog-hint');

    hintEl.classList.toggle('d-none', !hint);
    if (hint) hintEl.querySelector('span').textContent = hint;

    if (sel.value) {
        if (autofillPrice && price && parseFloat(price) > 0) {
            document.getElementById('modal-price').value = parseFloat(price).toFixed(2);
        }
        // В режиме каталога имя берём из выбранного ресурса (поле скрыто, но сохраняется).
        document.getElementById('modal-resource-name').value = opt.text;
    }
}

document.getElementById('btn-modal-ok').addEventListener('click', async function () {
    if (!modalCtx) return;
    const okBtn      = this;
    const errorEl    = document.getElementById('modal-submit-error');
    const priceInput = document.getElementById('modal-price');
    const price      = parseFloat(priceInput.value);

    errorEl.classList.add('d-none');

    if (!price || price <= 0) {
        priceInput.classList.add('is-invalid');
        document.getElementById('modal-price-error').classList.remove('d-none');
        priceInput.focus();
        return;
    }
    priceInput.classList.remove('is-invalid');
    document.getElementById('modal-price-error').classList.add('d-none');

    const { rfqId, type } = modalCtx;
    const rfq          = allRfqs.find(r => r.id === rfqId);
    const catalogSel   = document.getElementById('modal-catalog-sel');
    const catalogId    = modalSource === 'catalog' ? (catalogSel.value || null) : null;
    const resourceName = document.getElementById('modal-resource-name').value.trim() || null;
    const notes        = document.getElementById('modal-notes').value.trim() || null;
    const tempIds      = getUploadedTempIds();
    const assigned     = getAssignedTypes(rfq);

    btnLoading(okBtn, true);
    try {
        // Один оффер на этот RFQ (= услугу). Срок действия подставляет сервер.
        const result = await api.post(`/rfqs/${rfqId}/offers`, {
            unit_price:       price,
            currency:         supplierCurrency,
            is_partial:       assigned.length > 1,
            covered_services: [type],
            notes:            notes,
            items:            [{ type, unit_price: price, supplier_service_id: catalogId ? parseInt(catalogId) : null, name: resourceName }],
        });

        const newId = result?.data?.id;
        if (newId && tempIds.length) {
            await api.post('/attachments/claim', {
                attachment_ids:  tempIds,
                attachable_type: 'offers',
                attachable_id:   newId,
            });
        }

        // Обновляем офферы услуги; чистим temp-набор; переносим примечания на следующую.
        const res = await api.get(`/rfqs/${rfqId}/offers`);
        rfqOffersMap[rfqId] = res.data ?? [];
        uploadedTempIds     = [];
        lastNotes           = notes ?? '';

        bootstrap.Modal.getInstance(document.getElementById('svcModal'))?.hide();
        showToast(C.sent_ok, 'success');
        renderSvcList();
        renderRequestHeader();
    } catch (err) {
        errorEl.textContent = err?.message ?? C.send_err;
        errorEl.classList.remove('d-none');
    } finally {
        btnLoading(okBtn, false);
    }
});

// Чистим FilePond при закрытии модалки (temp-файлы не должны перетекать на след. услугу).
document.getElementById('svcModal').addEventListener('hidden.bs.modal', () => destroyComposePond());


// ── Withdraw ──────────────────────────────────────────────────────────────────

const withdrawModal = new bootstrap.Modal(document.getElementById('modal-withdraw'));
let pendingWithdraw = null;   // { offerId, rfqId }

// Предупреждение зависит от статуса: selected (в КП) — строже.
function withdrawOffer(offerId, rfqId, status) {
    pendingWithdraw = { offerId, rfqId };
    document.getElementById('withdraw-msg').textContent = status === 'selected'
        ? C.wd_selected
        : C.wd_msg;
    document.getElementById('withdraw-error').classList.add('d-none');
    withdrawModal.show();
}

document.getElementById('btn-withdraw-confirm').addEventListener('click', async function () {
    if (!pendingWithdraw) return;
    const { offerId, rfqId } = pendingWithdraw;
    const errEl = document.getElementById('withdraw-error');
    errEl.classList.add('d-none');
    btnLoading(this, true);
    try {
        await api.patch(`/offers/${offerId}/withdraw`);
        const res = await api.get(`/rfqs/${rfqId}/offers`);
        rfqOffersMap[rfqId] = res.data ?? [];
        pendingWithdraw = null;
        withdrawModal.hide();
        showToast(C.wd_done);
        renderSvcList();
        renderRequestHeader();
    } catch (err) {
        // Серверное правило (КП отправлено/принято) — показываем причину в модалке.
        errEl.textContent = err?.message ?? C.wd_err;
        errEl.classList.remove('d-none');
    } finally {
        btnLoading(this, false);
    }
});

// ── Init ──────────────────────────────────────────────────────────────────────

(async function init() {
    try {
        await Promise.all([loadCurrencies(), loadCatalog()]);
        await loadRfqs();

        if (!allRfqs.length) {
            document.getElementById('page-loader').innerHTML = `
                <div class="text-center py-20">
                    <i class="ki-outline ki-document fs-4x text-gray-300 mb-4 d-block"></i>
                    <div class="text-gray-600 fw-semibold fs-5">${C.empty_title}</div>
                    <div class="text-muted fs-7 mt-2">${C.empty_hint}</div>
                    <a href="{{ route('supplier.rfqs.index') }}" class="btn btn-light btn-sm mt-4">
                        <i class="ki-outline ki-arrow-left fs-5 me-1"></i>${C.back}
                    </a>
                </div>`;
            return;
        }

        await loadAllOffers();
        renderPage();
    } catch (err) {
        document.getElementById('page-loader').innerHTML = `
            <div class="text-center py-20">
                <div class="text-danger fw-semibold fs-5">${esc(err?.message ?? C.load_err)}</div>
                <a href="{{ route('supplier.rfqs.index') }}" class="btn btn-light btn-sm mt-4">
                    <i class="ki-outline ki-arrow-left fs-5 me-1"></i>${C.back}
                </a>
            </div>`;
    }
})();
</script>
@endpush
