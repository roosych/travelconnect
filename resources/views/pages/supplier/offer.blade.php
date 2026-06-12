@extends('layouts.public')

@section('title', 'Подать предложение — ' . config('app.name'))

@section('content')

{{-- Loading state --}}
<div id="state-loading" class="text-center py-20">
    <span class="spinner-border text-primary" style="width:3rem;height:3rem;"></span>
    <p class="text-muted mt-4">Загрузка деталей запроса...</p>
</div>

{{-- Error state --}}
<div id="state-error" class="d-none">
    <div class="card card-flush shadow-sm mx-auto" style="max-width:520px;">
        <div class="card-body text-center py-16 px-8">
            <i class="ki-outline ki-cross-circle fs-3x text-danger mb-4 d-block"></i>
            <h2 class="fw-bold text-gray-900 mb-3" id="error-title">Ссылка недействительна</h2>
            <p class="text-muted fs-6" id="error-message">Эта ссылка недействительна или истекла.</p>
        </div>
    </div>
</div>

{{-- Already submitted state --}}
<div id="state-submitted" class="d-none">
    <div class="card card-flush shadow-sm mx-auto" style="max-width:560px;">
        <div class="card-body py-10 px-8">
            <div class="text-center mb-7">
                <i class="ki-outline ki-check-circle fs-3x text-success mb-3 d-block"></i>
                <h2 class="fw-bold text-gray-900 mb-1">Предложение подано</h2>
                <p class="text-muted fs-6 mb-0">Ваше предложение получено и находится на рассмотрении.</p>
            </div>
            <div id="submitted-offer-info"></div>
        </div>
    </div>
</div>

{{-- Success state (after submission) --}}
<div id="state-success" class="d-none">
    <div class="card card-flush shadow-sm mx-auto" style="max-width:520px;">
        <div class="card-body text-center py-16 px-8">
            <i class="ki-outline ki-check-circle fs-3x text-success mb-4 d-block"></i>
            <h2 class="fw-bold text-gray-900 mb-3">Предложение подано!</h2>
            <p class="text-muted fs-6">
                Ваше предложение получено. Оператор рассмотрит его и свяжется с вами.
            </p>
        </div>
    </div>
</div>

{{-- Main form state --}}
<div id="state-form" class="d-none">

    <div class="mx-auto d-flex flex-column gap-6" style="max-width:640px;">

        {{-- RFQ details --}}
        <div class="card card-flush shadow-sm" id="rfq-info-card">
        </div>

        {{-- Offer form --}}
        <div class="card card-flush shadow-sm">
                <div class="card-header border-0 pt-6 pb-0">
                    <div class="card-title flex-column">
                        <h2 class="fw-bold text-gray-900 mb-1">Ваше предложение</h2>
                        <p class="text-muted fs-7 mb-0">Ответ от <strong id="supplier-name-label">—</strong></p>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <form id="form-offer" novalidate>

                        {{-- Service selector --}}
                        <div class="mb-7">
                            <label class="form-label fw-semibold d-block mb-1">Услуги, которые вы берётесь выполнить</label>
                            <div class="text-muted fs-8 mb-3">Отметьте одну или несколько — основная услуга предвыбрана</div>
                            <div id="service-toggles" class="d-flex flex-column gap-2"></div>
                            <div id="coverage-summary" class="mt-3 fs-7 fw-semibold d-none"></div>
                        </div>

                        <div class="separator mb-7"></div>

                        {{-- Valid until --}}
                        <div class="mb-6">
                            <label class="form-label required fw-semibold">Предложение действительно до</label>
                            <input type="date" id="f-valid-until" name="valid_until"
                                   class="form-control form-control-solid" required />
                            <div class="form-text">После этой даты предложение не гарантируется</div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-8">
                            <label class="form-label fw-semibold">Примечания / Условия <span class="text-muted fw-normal">(необязательно)</span></label>
                            <textarea id="f-notes" name="notes"
                                      class="form-control" rows="3"
                                      placeholder="Политика отмены, детали питания, мин./макс. кол-во гостей, сезонные тарифы..."></textarea>
                        </div>

                        {{-- Consent checkbox --}}
                        <div class="mb-6">
                            <label class="d-flex align-items-start gap-3 cursor-pointer">
                                <input type="checkbox" id="f-consent" class="form-check-input w-20px h-20px flex-shrink-0 mt-1" required />
                                <span class="text-gray-600 fs-7">Я подтверждаю, что указанные цены актуальны и берусь выполнить выбранные услуги на заявленных условиях</span>
                            </label>
                        </div>

                        <div id="form-error" class="alert alert-danger d-none mb-5"></div>

                        <button type="submit" id="btn-submit" class="btn btn-primary w-100 py-4 fs-5">
                            <span class="indicator-label">
                                <i class="ki-outline ki-send fs-4 me-2"></i>Подать предложение
                            </span>
                            <span class="indicator-progress d-none">
                                Отправка... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>

                    </form>
                </div>
            </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
    const token = @json($token);
    const apiBase = '/api/supplier/rfq/' + token;

    // ── Init ─────────────────────────────────────────────────────────────────

    (async function init() {
        try {
            const res = await fetch(apiBase, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            hide('state-loading');

            if (!res.ok) {
                showError(data);
                return;
            }

            if (data.already_submitted) {
                showAlreadySubmitted(data.existing_offer);
                return;
            }

            renderForm(data);
            show('state-form');

        } catch (e) {
            hide('state-loading');
            showError({ message: 'Ошибка сети. Проверьте подключение и попробуйте ещё раз.' });
        }
    })();

    // ── Constants ─────────────────────────────────────────────────────────────

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary', icon: 'ki-abstract-26', cls: 'badge-light-secondary' }]));

    let _allServices = [];   // full list from request
    let _primaryService = null; // rfq.service_type

    // ── Render helpers ────────────────────────────────────────────────────────

    function renderForm(data) {
        const { rfq, request, supplier } = data;

        document.getElementById('supplier-name-label').textContent = supplier.name;

        // Services this supplier is assigned for
        _allServices = rfq.assigned_service_types?.length
            ? rfq.assigned_service_types
            : [rfq.service_type];
        _primaryService = _allServices[0];

        // RFQ info card
        const serviceBadges = _allServices.map(t => {
            const m = SERVICE_META[t] ?? { label: t, icon: 'ki-abstract-26', color: 'secondary' };
            return `<span class="badge badge-light-${m.color} fs-8 me-1">${m.label}</span>`;
        }).join('');

        const paxRow = request?.pax_count
            ? `<div class="d-flex align-items-center gap-2 text-muted fs-7 mt-2">
                   <i class="ki-outline ki-people fs-5"></i>
                   <span><strong class="text-gray-800">${request.pax_count}</strong> чел.</span>
               </div>`
            : '';
        const datesRow = (request?.travel_date_from || request?.travel_date_to)
            ? `<div class="d-flex align-items-center gap-2 text-muted fs-7 mt-2">
                   <i class="ki-outline ki-calendar fs-5"></i>
                   <span><strong class="text-gray-800">${fmtDate(request.travel_date_from)}</strong> → <strong class="text-gray-800">${fmtDate(request.travel_date_to)}</strong></span>
               </div>`
            : '';
        document.getElementById('rfq-info-card').innerHTML = `
            <div class="card-body py-6 px-7">
                <div class="mb-1">${serviceBadges}</div>
                <h3 class="fw-bold text-gray-900 fs-4 mt-3 mb-4">${esc(rfq.title)}</h3>
                ${rfq.description ? `<p class="text-muted fs-6 mb-4">${esc(rfq.description)}</p>` : ''}
                ${datesRow}
                ${paxRow}
            </div>`;

        // Render service toggles
        renderServiceToggles();

        // Pre-fill valid_until to deadline - 1 day
        if (rfq.deadline_at) {
            const d = new Date(rfq.deadline_at);
            d.setDate(d.getDate() - 1);
            document.getElementById('f-valid-until').value = d.toISOString().slice(0, 10);
        }
    }

    function renderServiceToggles() {
        const container = document.getElementById('service-toggles');
        container.innerHTML = _allServices.map(s => {
            const m = SERVICE_META[s] ?? { label: s, icon: 'ki-abstract-26', color: 'secondary' };
            return `
            <div class="service-card border rounded overflow-hidden" data-service="${s}" style="transition:border-color .15s,background .15s">
                <label class="d-flex align-items-center gap-3 px-4 py-3 cursor-pointer mb-0 w-100">
                    <input type="checkbox" class="svc-checkbox form-check-input w-20px h-20px flex-shrink-0 mt-0"
                           data-service="${s}" />
                    <span class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light-${m.color} flex-shrink-0">
                        <i class="ki-outline ${m.icon} fs-4 text-${m.color}"></i>
                    </span>
                    <span class="fw-semibold text-gray-800">${m.label}</span>
                </label>
                <div class="svc-detail px-4 pb-4 d-none">
                    <div class="border-top pt-3 d-flex gap-3 align-items-start">
                        <div class="flex-grow-1">
                            <input type="text" class="form-control form-control-sm svc-name mb-2"
                                   placeholder="Краткое описание (необязательно)"
                                   data-service="${s}" />
                        </div>
                        <div style="width:150px;flex-shrink:0">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control svc-price"
                                       placeholder="0.00" min="0.01" step="0.01"
                                       data-service="${s}" />
                                <span class="input-group-text">AZN</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        container.querySelectorAll('.svc-checkbox').forEach(cb => {
            cb.addEventListener('change', () => { applyCardStyle(cb); updateCoverageSummary(); });
        });

        updateCoverageSummary();
    }

    function applyCardStyle(cb) {
        const card   = cb.closest('.service-card');
        const detail = card.querySelector('.svc-detail');
        const price  = card.querySelector('.svc-price');
        if (cb.checked) {
            card.style.borderColor     = '#009ef7';
            card.style.backgroundColor = '#f1faff';
            detail.classList.remove('d-none');
            if (price) price.required = true;
        } else {
            card.style.borderColor     = '';
            card.style.backgroundColor = '';
            detail.classList.add('d-none');
            card.querySelector('.svc-name').value = '';
            if (price) { price.required = false; price.value = ''; }
        }
    }

    function updateCoverageSummary() {
        const total   = _allServices.length;
        const checked = document.querySelectorAll('#service-toggles .svc-checkbox:checked').length;
        const el      = document.getElementById('coverage-summary');
        if (total <= 1) { el.classList.add('d-none'); return; }
        el.classList.remove('d-none');
        if (checked === total) {
            el.innerHTML = `<i class="ki-outline ki-check-circle fs-5 text-success me-1"></i><span class="text-success">Полное покрытие — все ${total} услуги</span>`;
        } else if (checked === 0) {
            el.innerHTML = `<i class="ki-outline ki-warning-2 fs-5 text-danger me-1"></i><span class="text-danger">Выберите хотя бы одну услугу</span>`;
        } else {
            el.innerHTML = `<i class="ki-outline ki-information-3 fs-5 text-primary me-1"></i><span class="text-primary">Частичное покрытие — ${checked} из ${total} услуг</span>`;
        }
    }

    function showError(data) {
        show('state-error');
        const titles = {
            expired:    'Ссылка истекла',
            not_found:  'Ссылка не найдена',
            rfq_closed: 'Запрос закрыт',
        };
        document.getElementById('error-title').textContent   = titles[data.error] ?? 'Ошибка';
        document.getElementById('error-message').textContent = data.message ?? 'Произошла ошибка.';
    }

    const STATUS_LABELS = {
        received:  'Подано, ожидает ответа',
        reviewed:  'Рассматривается',
        selected:  'Выбрано ✓',
        rejected:  'Не выбрано',
        withdrawn: 'Отозвано вами',
    };

    function showAlreadySubmitted(offer) {
        show('state-submitted');
        if (!offer) return;

        const items    = Array.isArray(offer.items) ? offer.items : [];
        const currency = offer.currency ?? 'AZN';
        const covered  = Array.isArray(offer.covered_services) ? offer.covered_services : [];

        // Per-service rows
        const priceByType = {};
        items.forEach(i => { priceByType[i.type] = i; });

        const serviceRows = covered.map(s => {
            const m    = SERVICE_META[s] ?? { label: s, icon: 'ki-abstract-26', color: 'secondary' };
            const item = priceByType[s];
            return `
            <div class="d-flex align-items-center gap-3 py-3" style="border-bottom:1px solid #f1f1f4">
                <span class="d-flex align-items-center justify-content-center w-32px h-32px rounded-circle bg-light-${m.color} flex-shrink-0">
                    <i class="ki-outline ${m.icon} fs-6 text-${m.color}"></i>
                </span>
                <span class="flex-grow-1 fw-semibold text-gray-800 fs-6">${m.label}</span>
                ${item?.name && item.name !== s
                    ? `<span class="text-muted fs-7 me-3 text-truncate" style="max-width:160px">${esc(item.name)}</span>`
                    : ''}
                <span class="fw-bold text-gray-900 text-nowrap">${item ? fmtCurrency(item.unit_price, currency) : '—'}</span>
            </div>`;
        }).join('');

        const total = items.length
            ? items.reduce((s, i) => s + parseFloat(i.unit_price ?? 0), 0)
            : parseFloat(offer.unit_price ?? 0);

        const totalRow = covered.length > 1
            ? `<div class="d-flex justify-content-end gap-3 pt-3 mb-2">
                   <span class="text-muted fs-7">Итого:</span>
                   <span class="fw-bold fs-5 text-gray-900">${fmtCurrency(total, currency)}</span>
               </div>`
            : `<div class="text-end pt-3 mb-2">
                   <span class="fw-bold fs-4 text-gray-900">${fmtCurrency(total, currency)}</span>
               </div>`;

        document.getElementById('submitted-offer-info').innerHTML = `
            <div class="bg-light rounded p-4 mb-4">
                ${serviceRows}
                ${totalRow}
            </div>
            <div class="d-flex justify-content-between align-items-center px-1">
                <span class="text-muted fs-7">Статус предложения</span>
                <span class="badge badge-light-success">${STATUS_LABELS[offer.status] ?? offer.status}</span>
            </div>
            ${offer.valid_until ? `
            <div class="d-flex justify-content-between align-items-center px-1 mt-2">
                <span class="text-muted fs-7">Действительно до</span>
                <span class="fw-semibold text-gray-700 fs-7">${fmtDate(offer.valid_until)}</span>
            </div>` : ''}
            ${offer.notes ? `
            <div class="mt-4 px-1">
                <div class="text-muted fs-8 mb-1">Ваши примечания</div>
                <div class="text-gray-600 fs-7">${esc(offer.notes)}</div>
            </div>` : ''}`;
    }

    // ── Form interactions ─────────────────────────────────────────────────────

    document.getElementById('form-offer').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn     = document.getElementById('btn-submit');
        const errorEl = document.getElementById('form-error');

        const services = [];
        document.querySelectorAll('#service-toggles .svc-checkbox:checked').forEach(cb => {
            const card  = cb.closest('.service-card');
            const price = parseFloat(card.querySelector('.svc-price')?.value);
            services.push({
                type:  cb.dataset.service,
                name:  card.querySelector('.svc-name')?.value?.trim() || '',
                price: isNaN(price) ? null : price,
            });
        });

        if (services.length === 0) {
            errorEl.textContent = 'Выберите хотя бы одну услугу, которую вы берётесь выполнить.';
            errorEl.classList.remove('d-none');
            return;
        }

        const missingPrice = services.find(s => !s.price || s.price <= 0);
        if (missingPrice) {
            const m = SERVICE_META[missingPrice.type] ?? { label: missingPrice.type };
            errorEl.textContent = `Укажите цену для услуги «${m.label}».`;
            errorEl.classList.remove('d-none');
            return;
        }

        if (!document.getElementById('f-consent').checked) {
            errorEl.textContent = 'Подтвердите согласие перед отправкой.';
            errorEl.classList.remove('d-none');
            return;
        }

        if (!this.checkValidity()) { this.reportValidity(); return; }

        const fd = new FormData(this);
        const payload = {
            currency:    'AZN',
            valid_until: fd.get('valid_until'),
            services,
            notes:       fd.get('notes') || null,
        };

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        try {
            const res  = await fetch(apiBase + '/offer', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify(payload),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                hide('state-form');
                show('state-success');
            } else {
                const msg = data.message
                    ?? (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                    ?? 'Не удалось отправить предложение. Попробуйте ещё раз.';
                errorEl.textContent = msg;
                errorEl.classList.remove('d-none');
                btn.disabled = false;
                btn.querySelector('.indicator-label').classList.remove('d-none');
                btn.querySelector('.indicator-progress').classList.add('d-none');
            }
        } catch {
            errorEl.textContent = 'Ошибка сети. Проверьте подключение.';
            errorEl.classList.remove('d-none');
            btn.disabled = false;
            btn.querySelector('.indicator-label').classList.remove('d-none');
            btn.querySelector('.indicator-progress').classList.add('d-none');
        }
    });

    // ── Utilities ─────────────────────────────────────────────────────────────

    function show(id) { document.getElementById(id).classList.remove('d-none'); }
    function hide(id) { document.getElementById(id).classList.add('d-none'); }

    function fmtDate(d) {
        if (!d) return '—';
        const dt = new Date(d);
        const dd = String(dt.getDate()).padStart(2, '0');
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        const yyyy = dt.getFullYear();
        return `${dd}.${mm}.${yyyy}`;
    }

    function fmtCurrency(val, currency = 'AZN') {
        if (val == null || isNaN(val)) return '—';
        return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(val)) + ' ' + (currency || 'AZN');
    }

    function esc(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>
@endpush
