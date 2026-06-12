@extends('layouts.agency')

@php($isEdit = (bool) ($editData ?? null))

@section('title', $isEdit ? 'Редактировать заявку' : 'Новая заявка на тур')
@section('page-title', $isEdit ? 'Редактировать заявку' : 'Новая заявка на тур')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('agency.requests.index') }}" class="text-muted text-hover-primary">Мои заявки</a>
    </li>
    <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
    <li class="breadcrumb-item text-muted">{{ $isEdit ? 'Редактирование' : 'Новая заявка' }}</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-body py-8 px-6 px-lg-10">

        <div id="form-alert" class="alert alert-danger d-none mb-6">
            <span id="form-alert-text"></span>
        </div>

        <div class="stepper stepper-pills stepper-column d-flex flex-column flex-lg-row" id="kt_request_stepper">

            {{-- ── Навигация шагов ─────────────────────────────────────────────── --}}
            <div class="d-flex justify-content-center justify-content-lg-start flex-column-fluid flex-lg-row-auto w-100 w-lg-300px me-lg-12 mb-10 mb-lg-0">
                <div class="stepper-nav flex-column">

                    <div class="stepper-item current" data-kt-stepper-element="nav">
                        <div class="stepper-wrapper d-flex align-items-center">
                            <div class="stepper-icon w-40px h-40px">
                                <i class="ki-outline ki-check stepper-check fs-2"></i>
                                <span class="stepper-number">1</span>
                            </div>
                            <div class="stepper-label">
                                <h3 class="stepper-title fs-6">Основное</h3>
                                <div class="stepper-desc fw-semibold fs-8">Название, гости, срок</div>
                            </div>
                        </div>
                        <div class="stepper-line h-40px"></div>
                    </div>

                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <div class="stepper-wrapper d-flex align-items-center">
                            <div class="stepper-icon w-40px h-40px">
                                <i class="ki-outline ki-check stepper-check fs-2"></i>
                                <span class="stepper-number">2</span>
                            </div>
                            <div class="stepper-label">
                                <h3 class="stepper-title fs-6">Маршрут</h3>
                                <div class="stepper-desc fw-semibold fs-8">Страны, даты, услуги</div>
                            </div>
                        </div>
                        <div class="stepper-line h-40px"></div>
                    </div>

                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <div class="stepper-wrapper d-flex align-items-center">
                            <div class="stepper-icon w-40px h-40px">
                                <i class="ki-outline ki-check stepper-check fs-2"></i>
                                <span class="stepper-number">3</span>
                            </div>
                            <div class="stepper-label">
                                <h3 class="stepper-title fs-6">Файлы</h3>
                                <div class="stepper-desc fw-semibold fs-8">Вложения — необязательно</div>
                            </div>
                        </div>
                        <div class="stepper-line h-40px"></div>
                    </div>

                    <div class="stepper-item" data-kt-stepper-element="nav">
                        <div class="stepper-wrapper d-flex align-items-center">
                            <div class="stepper-icon w-40px h-40px">
                                <i class="ki-outline ki-check stepper-check fs-2"></i>
                                <span class="stepper-number">4</span>
                            </div>
                            <div class="stepper-label">
                                <h3 class="stepper-title fs-6">Проверка</h3>
                                <div class="stepper-desc fw-semibold fs-8">Подтверждение</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Форма ───────────────────────────────────────────────────────── --}}
            <div class="flex-row-fluid">
            <form id="request-form" novalidate class="w-100">

                {{-- Шаг 1: Основное --}}
                <div class="current" data-kt-stepper-element="content">
                    <div class="w-100 mw-700px">
                        <h2 class="fw-bold text-gray-900 mb-1">Основная информация</h2>
                        <div class="text-muted fs-7 mb-8">Коротко опишите заявку и сроки.</div>

                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2"><span class="required">Название заявки</span><i class="ki-outline ki-information-5 fs-5 text-muted ms-1 align-middle" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Краткое понятное название, по которому вы найдёте заявку в списке"></i></label>
                            <input type="text" name="title" id="title"
                                   class="form-control form-control-solid"
                                   placeholder="Например: Сафари-тур для 10 гостей — Кения, октябрь 2026"
                                   required />
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2"><span class="required">Количество гостей</span><i class="ki-outline ki-information-5 fs-5 text-muted ms-1 align-middle" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Общее число туристов в группе"></i></label>
                                <input type="number" name="pax_count" id="pax_count"
                                       class="form-control form-control-solid"
                                       min="1" placeholder="10" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2"><span class="required">Срок ответа</span><i class="ki-outline ki-information-5 fs-5 text-muted ms-1 align-middle" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="До какого момента ждёте предложения. Время в вашем поясе: {{ $userTimezone }}"></i></label>
                                <input type="text" name="deadline_at" id="deadline_at"
                                       class="form-control form-control-solid"
                                       placeholder="{{ __('common.datetime_ph') }}" readonly />
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="fw-semibold fs-6 mb-2">Примечания и особые пожелания<i class="ki-outline ki-information-5 fs-5 text-muted ms-1 align-middle" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Любые особые требования и пожелания для оператора. Необязательно."></i></label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="form-control form-control-solid"
                                      placeholder="Любые особые требования, пожелания или примечания для оператора…"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Шаг 2: Маршрут --}}
                <div data-kt-stepper-element="content">
                    <div class="w-100">
                        <h2 class="fw-bold text-gray-900 mb-1">Маршрут по странам</h2>
                        <div class="text-muted fs-7 mb-8">
                            Добавьте страны по порядку. Для каждой — даты, направления и нужные услуги с требованиями.
                        </div>

                        <div id="legs-container" class="d-flex flex-column gap-4"></div>

                        <button type="button" id="add-leg-btn" class="btn btn-light-primary btn-sm mt-4">
                            <i class="ki-outline ki-plus fs-5 me-1"></i>Добавить страну
                        </button>
                    </div>
                </div>

                {{-- Шаг 3: Файлы --}}
                <div data-kt-stepper-element="content">
                    <div class="w-100 mw-700px">
                        <h2 class="fw-bold text-gray-900 mb-1">Вложения</h2>
                        <div class="text-muted fs-7 mb-8">Программа, паспорта, пожелания — всё, что поможет оператору. Необязательно.</div>

                        {{-- Уже прикреплённые файлы (только в режиме редактирования) --}}
                        <div id="attach-existing-wrap" class="mb-5 d-none">
                            <div class="text-muted fs-8 text-uppercase fw-bold mb-2">Прикреплённые файлы</div>
                            <div id="attach-existing-list" class="d-flex flex-column gap-2"></div>
                        </div>

                        <div id="attach-dropzone"
                             class="border border-dashed border-gray-300 rounded-2 p-8 text-center"
                             style="cursor:pointer;transition:border-color .15s">
                            <i class="ki-outline ki-paper-clip fs-2x text-gray-400 mb-2 d-block"></i>
                            <div class="text-muted fs-7">
                                Перетащите файлы или <span class="text-primary fw-semibold">выберите</span>
                            </div>
                            <div class="text-muted fs-8 mt-1">PDF, Word, Excel, JPG, PNG · до 20 МБ</div>
                            <input type="file" id="attach-file-input" multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="d-none">
                        </div>

                        <div id="attach-file-list" class="mt-3 d-flex flex-column gap-2"></div>
                    </div>
                </div>

                {{-- Шаг 4: Проверка --}}
                <div data-kt-stepper-element="content">
                    <div class="w-100 mw-700px">
                        <h2 class="fw-bold text-gray-900 mb-1">Проверьте заявку</h2>
                        <div class="text-muted fs-7 mb-8">Убедитесь, что всё верно, и создайте заявку.</div>
                        <div id="review-container"></div>
                    </div>
                </div>

                {{-- ── Кнопки навигации ──────────────────────────────────────────── --}}
                <div class="d-flex flex-stack pt-10">
                    <div class="me-2">
                        <button type="button" class="btn btn-light" data-kt-stepper-action="previous">
                            <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Назад
                        </button>
                    </div>
                    <div>
                        <a href="{{ route('agency.requests.index') }}" class="btn btn-light me-2">Отмена</a>
                        <button type="button" id="btn-save-draft" class="btn btn-light me-2" data-kt-stepper-action="submit" data-submit-mode="draft" data-kt-indicator="off">
                            <span class="indicator-label"><i class="ki-outline ki-file fs-4 me-1"></i>Сохранить черновик</span>
                            <span class="indicator-progress">
                                <span class="submit-progress-text">Сохранение…</span>
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                        <button type="button" id="btn-save-submit" class="btn btn-success" data-kt-stepper-action="submit" data-submit-mode="submit" data-kt-indicator="off">
                            <span class="indicator-label"><i class="ki-outline ki-send fs-4 me-1"></i>{{ $isEdit ? 'Сохранить и подать' : 'Создать и подать' }}</span>
                            <span class="indicator-progress">
                                <span class="submit-progress-text">Отправка…</span>
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-kt-stepper-action="next">
                            Далее<i class="ki-outline ki-arrow-right fs-4 ms-1"></i>
                        </button>
                    </div>
                </div>

            </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Date pickers ──────────────────────────────────────────────────────────────
function makeDatePicker(selector, extraOptions = {}) {
    return flatpickr(selector, {
        dateFormat:    'Y-m-d',
        altInput:      true,
        altFormat:     'd.m.Y',
        minDate:       'today',
        allowInput:    false,
        disableMobile: true,
        onReady(_, __, fp) {
            if (fp.altInput) fp.altInput.readOnly = true;
        },
        ...extraOptions,
    });
}

// ── Подсказки-тултипы ─────────────────────────────────────────────────────────
function infoIcon(text) {
    return `<i class="ki-outline ki-information-5 fs-5 text-muted ms-1 align-middle" style="cursor:help"
              data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${escHtml(text)}"></i>`;
}
function initTooltips(root = document) {
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        if (!el._ttInit) { new bootstrap.Tooltip(el); el._ttInit = true; }
    });
}

// Дедлайн — дата + время; значение трактуется в поясе пользователя (конвертация в UTC на бэке).
makeDatePicker('#deadline_at', {
    enableTime: true,
    time_24hr:  true,
    dateFormat: 'Y-m-d H:i',
    altFormat:  'd.m.Y H:i',
});

// ── Маршрут: редактор сегментов ─────────────────────────────────────────────
const countriesData = @json($countries);
const countryByCode = Object.fromEntries(countriesData.map(c => [c.code, c]));
const USER_TZ = @json($userTimezone);
const EDIT = @json($editData ?? null);

// Типы услуг с атрибутами приходят из динамического каталога (ServiceCatalog),
// лейблы уже локализованы на бэке. META.serviceTypes: [{value,label,attributes}].
const META = @json($serviceMeta);
const optionsHtml = (list, sel = '') =>
    list.map(o => `<option value="${o.value}"${String(o.value) === String(sel) ? ' selected' : ''}>${escHtml(o.label)}</option>`).join('');

// Атрибуты (подкатегории) типа услуги из каталога.
function attrsOf(type) {
    const t = META.serviceTypes.find(s => s.value === type);
    return t ? (t.attributes || []) : [];
}
// Локализованный лейбл опции атрибута.
function optLabel(attr, value) {
    const o = (attr.options || []).find(x => String(x.value) === String(value));
    return o ? o.label : value;
}

// Формат опции страны в select2 — флаг + название.
function formatCountry(item) {
    if (!item.id) return item.text;
    const flag = item.element ? item.element.getAttribute('data-flag') : null;
    if (!flag) return item.text;
    const span = document.createElement('span');
    const img  = document.createElement('img');
    img.src = flag;
    img.className = 'rounded h-15px me-2';
    img.onerror = function () { this.remove(); };
    span.appendChild(img);
    span.appendChild(document.createTextNode(item.text));
    return $(span);
}

let legSeq = 0;
const legPickers = {}; // uid -> { dates } (range-пикер дат пребывания)
const legsContainer = document.getElementById('legs-container');

document.getElementById('add-leg-btn').addEventListener('click', () => addLeg());

function addLeg() {
    const uid  = ++legSeq;
    const card = document.createElement('div');
    card.className = 'border border-gray-300 rounded-2 p-5';
    card.dataset.leg = uid;
    card.innerHTML = legCardHtml();
    legsContainer.appendChild(card);

    $(card).find('.leg-country')
        .select2({
            placeholder: 'Выберите страну…',
            width: '100%',
            closeOnSelect: true,
            templateResult: formatCountry,
            templateSelection: formatCountry,
        })
        .on('change', function () { renderLegDestinations(card, this.value); });

    const datesPicker = makeDatePicker(card.querySelector('.leg-dates'), {
        mode:   'range',
        locale: { rangeSeparator: ' — ' },
    });
    legPickers[uid] = { dates: datesPicker };

    initTooltips(card);
    renumberLegs();
    return card;
}

function legCardHtml() {
    // Название страны — в текущей локали (countryName/Intl), фолбэк на имя из БД.
    const countryOptions = countriesData.map(c => `<option value="${c.code}" data-flag="${c.flag}">${escHtml(countryName(c.code) || c.name)}</option>`).join('');
    const svcChips = META.serviceTypes.map(s =>
        `<button type="button" class="btn btn-sm btn-light svc-chip" data-type="${s.value}">${escHtml(s.label)}</button>`).join('');

    return `
      <div class="d-flex align-items-center justify-content-between mb-4">
          <span class="fw-bold text-gray-800 fs-5"><span class="leg-no"></span>. Страна маршрута</span>
          <div class="d-flex gap-2">
              <button type="button" class="btn btn-icon btn-sm btn-light leg-up"     title="Выше"><i class="ki-outline ki-up fs-5"></i></button>
              <button type="button" class="btn btn-icon btn-sm btn-light leg-down"   title="Ниже"><i class="ki-outline ki-down fs-5"></i></button>
              <button type="button" class="btn btn-icon btn-sm btn-light-danger leg-remove" title="Удалить"><i class="ki-outline ki-trash fs-5"></i></button>
          </div>
      </div>
      <div class="row mb-4">
          <div class="col-md-6 fv-row mb-3 mb-md-0">
              <label class="fw-semibold fs-7 text-muted mb-1"><span class="required">Страна</span>${infoIcon('Страна сегмента маршрута. Каждую страну можно добавить только один раз.')}</label>
              <select class="form-select form-select-solid leg-country"><option></option>${countryOptions}</select>
          </div>
          <div class="col-md-6 fv-row">
              <label class="fw-semibold fs-7 text-muted mb-1"><span class="required">Даты пребывания</span>${infoIcon('Период в этой стране. Граничный день с соседней страной может совпадать — выезд и заезд в один день.')}</label>
              <input type="text" class="form-control form-control-solid leg-dates" placeholder="{{ __('common.date_range_ph') }}" readonly>
          </div>
      </div>
      <div class="fv-row mb-4">
          <label class="fw-semibold fs-7 text-muted mb-1"><span class="required">Направления</span>${infoIcon('Кликайте по направлениям в порядке маршрута — номер покажет последовательность.')}</label>
          <div class="leg-destinations d-flex flex-wrap gap-2"><span class="text-muted fs-7">Сначала выберите страну.</span></div>
      </div>
      <div class="fv-row">
          <label class="fw-semibold fs-7 text-muted mb-1"><span class="required">Услуги</span>${infoIcon('Выбранные услуги относятся ко всем выбранным направлениям этой страны.')}</label>
          <div class="d-flex flex-wrap gap-2 mb-3 leg-services">${svcChips}</div>
          <div class="leg-requirements d-flex flex-column gap-3"></div>
      </div>`;
}

function renderLegDestinations(card, code) {
    card._destOrder = []; // смена страны сбрасывает выбор/порядок направлений
    const wrap = card.querySelector('.leg-destinations');
    const c = countryByCode[code];
    if (!c) { wrap.innerHTML = '<span class="text-muted fs-7">Сначала выберите страну.</span>'; return; }
    wrap.innerHTML = (c.destinations || []).length
        ? c.destinations.map(d => `<button type="button" class="btn btn-sm btn-light dest-chip" data-id="${d.id}"><span class="dest-order fw-bold me-1"></span>${escHtml(d.name)}</button>`).join('')
        : '<span class="text-muted fs-7">Направления для этой страны не заданы — запрос будет по стране в целом.</span>';
}

// Проставляет номера последовательности на выбранных чипах направлений.
function renderDestOrder(card) {
    const order = card._destOrder || [];
    card.querySelectorAll('.dest-chip').forEach(chip => {
        const pos = order.indexOf(parseInt(chip.dataset.id, 10));
        chip.querySelector('.dest-order').textContent = pos >= 0 ? (pos + 1) + '.' : '';
    });
}

function renumberLegs() {
    [...legsContainer.querySelectorAll('[data-leg]')].forEach((card, i) => {
        card.querySelector('.leg-no').textContent = i + 1;
    });
}

// Делегированные клики внутри маршрута: чипы направлений/услуг, удаление, порядок.
legsContainer.addEventListener('click', function (e) {
    const dest = e.target.closest('.dest-chip');
    if (dest) {
        const card = dest.closest('[data-leg]');
        card._destOrder = card._destOrder || [];
        const id = parseInt(dest.dataset.id, 10);
        if (dest.classList.contains('btn-primary')) {
            dest.classList.replace('btn-primary', 'btn-light');
            card._destOrder = card._destOrder.filter(x => x !== id);
        } else {
            dest.classList.replace('btn-light', 'btn-primary');
            card._destOrder.push(id);
        }
        renderDestOrder(card);
        return;
    }

    const svc = e.target.closest('.svc-chip');
    if (svc) {
        const on = svc.classList.toggle('btn-primary'); svc.classList.toggle('btn-light');
        toggleRequirements(svc.closest('[data-leg]'), svc.dataset.type, on);
        return;
    }

    const rm = e.target.closest('.leg-remove');
    if (rm) { rm.closest('[data-leg]').remove(); renumberLegs(); return; }

    const up = e.target.closest('.leg-up');
    if (up) { const card = up.closest('[data-leg]'); const prev = card.previousElementSibling; if (prev) legsContainer.insertBefore(card, prev); renumberLegs(); return; }

    const down = e.target.closest('.leg-down');
    if (down) { const card = down.closest('[data-leg]'); const next = card.nextElementSibling; if (next) legsContainer.insertBefore(next, card); renumberLegs(); return; }
});

function toggleRequirements(card, type, on) {
    const wrap = card.querySelector('.leg-requirements');
    let block = wrap.querySelector(`[data-req="${type}"]`);
    if (!on) { if (block) block.remove(); return; }
    if (block) return;
    block = document.createElement('div');
    block.dataset.req = type;
    block.className = 'border border-dashed border-gray-300 rounded-2 p-3';
    block.innerHTML = requirementsHtml(type);
    wrap.appendChild(block);
}

// Generic-рендерер требований: строит поля из атрибутов каталога по input_type.
function requirementsHtml(type) {
    const svc   = META.serviceTypes.find(s => s.value === type);
    const head  = `<div class="fw-semibold text-gray-700 fs-7 mb-2">${escHtml(svc ? svc.label : '')} — требования</div>`;
    const attrs = attrsOf(type);

    if (!attrs.length) {
        return head + `<div class="text-muted fs-8">Без дополнительных требований.</div>`;
    }

    return head + `<div class="row g-3">${attrs.map(fieldHtml).join('')}</div>`;
}

// Один контрол по типу атрибута.
function fieldHtml(a) {
    const ph = (a.config && a.config.placeholder) ? a.config.placeholder : '';

    switch (a.input_type) {
        case 'select':
            return `
              <div class="col-sm-6">
                <label class="fs-8 text-muted">${escHtml(a.label)}</label>
                <select class="form-select form-select-sm form-select-solid req-field" data-key="${a.code}" data-input="select">
                  <option value="">Выберите…</option>${optionsHtml(a.options)}
                </select>
              </div>`;

        case 'multiselect':
            return `
              <div class="col-sm-6">
                <label class="fs-8 text-muted d-block mb-1">${escHtml(a.label)}</label>
                <div class="d-flex flex-wrap gap-3 req-multi" data-key="${a.code}">
                  ${a.options.map(o => `<label class="form-check form-check-sm form-check-inline mb-0"><input class="form-check-input req-multi-opt" type="checkbox" value="${o.value}"><span class="form-check-label">${escHtml(o.label)}</span></label>`).join('')}
                </div>
              </div>`;

        case 'boolean':
            return `
              <div class="col-12">
                <label class="form-check form-check-sm mb-0"><input class="form-check-input req-field" type="checkbox" data-key="${a.code}" data-input="boolean" value="1"><span class="form-check-label fs-8 text-muted">${escHtml(a.label)}</span></label>
              </div>`;

        case 'number':
            return `
              <div class="col-sm-6">
                <label class="fs-8 text-muted">${escHtml(a.label)}</label>
                <input type="number" class="form-control form-control-sm form-control-solid req-field" data-key="${a.code}" data-input="number" placeholder="${escHtml(ph)}">
              </div>`;

        case 'textarea':
            return `
              <div class="col-12">
                <label class="fs-8 text-muted">${escHtml(a.label)}</label>
                <textarea rows="2" class="form-control form-control-sm form-control-solid req-field" data-key="${a.code}" data-input="textarea" placeholder="${escHtml(ph)}"></textarea>
              </div>`;

        default: // text
            return `
              <div class="col-sm-6">
                <label class="fs-8 text-muted">${escHtml(a.label)}</label>
                <input type="text" class="form-control form-control-sm form-control-solid req-field" data-key="${a.code}" data-input="text" placeholder="${escHtml(ph)}">
              </div>`;
    }
}

// Сборка сегментов из DOM (порядок карточек = sort_order).
// {from, to} выбранного диапазона дат сегмента в формате Y-m-d. Один выбранный день = заезд+выезд в этот день.
function legDates(card) {
    const fp = card.querySelector('.leg-dates')?._flatpickr;
    const s  = fp ? fp.selectedDates : [];
    const f  = d => d ? flatpickr.formatDate(d, 'Y-m-d') : null;
    return { from: f(s[0]) || null, to: f(s[1]) || f(s[0]) || null };
}

function collectLegs() {
    return [...legsContainer.querySelectorAll('[data-leg]')].map((card, idx) => {
        const d = legDates(card);
        return {
            country_code:    card.querySelector('.leg-country').value,
            date_from:       d.from,
            date_to:         d.to,
            sort_order:      idx,
            destination_ids: (card._destOrder || []).slice(), // порядок = последовательность выбора
            services:        [...card.querySelectorAll('.svc-chip.btn-primary')].map(chip => ({
                service_type: chip.dataset.type,
                requirements: collectRequirements(card.querySelector(`.leg-requirements [data-req="${chip.dataset.type}"]`), chip.dataset.type),
            })),
        };
    }).filter(l => l.country_code);
}

// Сбор требований из DOM в {code: value}. Значения опций — коды (не текст).
function collectRequirements(block, type) {
    const req = {};
    if (!block) return req;

    block.querySelectorAll('.req-field').forEach(el => {
        const key = el.dataset.key;
        if (el.dataset.input === 'boolean') { if (el.checked) req[key] = true; }
        else if (el.value !== '')           { req[key] = el.dataset.input === 'number' ? parseInt(el.value, 10) : el.value; }
    });

    block.querySelectorAll('.req-multi').forEach(group => {
        const vals = [...group.querySelectorAll('.req-multi-opt:checked')].map(c => c.value);
        if (vals.length) req[group.dataset.key] = vals;
    });

    return req;
}

// Предзаполнение при редактировании, иначе — один пустой сегмент.
function fillRequirements(card, type, req) {
    const block = card.querySelector(`.leg-requirements [data-req="${type}"]`);
    if (!block) return;
    block.querySelectorAll('.req-field').forEach(el => {
        const key = el.dataset.key;
        if (el.dataset.input === 'boolean') { el.checked = !!req[key]; }
        else if (req[key] != null && req[key] !== '') { el.value = req[key]; }
    });
    block.querySelectorAll('.req-multi').forEach(group => {
        const vals = Array.isArray(req[group.dataset.key]) ? req[group.dataset.key] : [];
        vals.forEach(v => { const cb = group.querySelector(`.req-multi-opt[value="${v}"]`); if (cb) cb.checked = true; });
    });
}

function addLegFromData(leg) {
    const card = addLeg();
    $(card).find('.leg-country').val(leg.country_code).trigger('change'); // рендерит направления синхронно
    const dateRange = [leg.date_from, leg.date_to].filter(Boolean);
    if (dateRange.length) card.querySelector('.leg-dates')._flatpickr.setDate(dateRange, true);

    card._destOrder = [];
    (leg.destination_ids || []).forEach(id => {
        const chip = card.querySelector(`.dest-chip[data-id="${id}"]`);
        if (chip) { chip.classList.replace('btn-light', 'btn-primary'); card._destOrder.push(id); }
    });
    renderDestOrder(card);

    (leg.services || []).forEach(s => {
        const chip = card.querySelector(`.svc-chip[data-type="${s.service_type}"]`);
        if (chip) {
            chip.classList.replace('btn-light', 'btn-primary');
            toggleRequirements(card, s.service_type, true);
            fillRequirements(card, s.service_type, s.requirements || {});
        }
    });
}

// ── Existing attachments (edit mode) ───────────────────────────────────────────
let existingAttachments = (EDIT && Array.isArray(EDIT.attachments)) ? [...EDIT.attachments] : [];

if (EDIT) {
    document.getElementById('title').value     = EDIT.title ?? '';
    document.getElementById('pax_count').value = EDIT.pax_count ?? '';
    document.getElementById('notes').value     = EDIT.notes ?? '';
    if (EDIT.deadline_at) document.getElementById('deadline_at')._flatpickr.setDate(EDIT.deadline_at, true);
    (EDIT.legs || []).forEach(addLegFromData);
    if (!(EDIT.legs || []).length) addLeg();
    renderExistingAttachments();
} else {
    addLeg();
}

initTooltips(); // подсказки на статических полях Шага 1

function renderExistingAttachments() {
    const wrap = document.getElementById('attach-existing-wrap');
    const list = document.getElementById('attach-existing-list');
    if (!wrap || !list) return;

    if (!existingAttachments.length) { wrap.classList.add('d-none'); list.innerHTML = ''; return; }
    wrap.classList.remove('d-none');

    list.innerHTML = existingAttachments.map(a => `
        <div class="d-flex align-items-center gap-3 px-3 py-2 border border-gray-300 rounded-2" data-att-id="${a.id}">
            <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
            <div class="flex-grow-1 min-w-0">
                <a href="${a.url}" target="_blank" class="fw-semibold text-gray-800 text-hover-primary fs-7 text-truncate d-block">${escHtml(a.filename)}</a>
                <div class="text-muted fs-8">${escHtml(a.human_size || '')}</div>
            </div>
            <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0" onclick="removeExistingAttachment(${a.id}, this)">
                <i class="ki-outline ki-cross fs-5"></i>
            </button>
        </div>`).join('');
}

async function removeExistingAttachment(id, btn) {
    if (!confirm('Удалить этот файл?')) return;
    btn.disabled = true;
    try {
        await api.delete(`/attachments/${id}`);
        existingAttachments = existingAttachments.filter(a => a.id !== id);
        renderExistingAttachments();
    } catch (e) {
        btn.disabled = false;
        showToast?.('Не удалось удалить файл', 'error');
    }
}

// ── Staged files ──────────────────────────────────────────────────────────────
let stagedFiles = [];

(function initDropzone() {
    const dropzone  = document.getElementById('attach-dropzone');
    const fileInput = document.getElementById('attach-file-input');

    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => { addFiles([...fileInput.files]); fileInput.value = ''; });
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('border-primary'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-primary'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
        addFiles([...e.dataTransfer.files]);
    });
})();

function addFiles(files) { files.forEach(f => stagedFiles.push(f)); renderFileList(); }
function removeFile(idx) { stagedFiles.splice(idx, 1); renderFileList(); }

function renderFileList() {
    const list = document.getElementById('attach-file-list');
    if (!stagedFiles.length) { list.innerHTML = ''; return; }
    list.innerHTML = stagedFiles.map((f, i) => `
        <div class="d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2">
            <i class="ki-outline ki-paper-clip fs-5 text-muted flex-shrink-0"></i>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-gray-800 fs-7 text-truncate">${escHtml(f.name)}</div>
                <div class="text-muted fs-8">${fmtSize(f.size)}</div>
            </div>
            <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0" onclick="removeFile(${i})">
                <i class="ki-outline ki-cross fs-5"></i>
            </button>
        </div>`).join('');
}

function fmtSize(b) {
    if (b < 1024)    return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(0) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Stepper (KTStepper) ───────────────────────────────────────────────────────
const stepperRoot = document.querySelector('#kt_request_stepper');
const stepper     = new KTStepper(stepperRoot);
const navPrev     = stepperRoot.querySelector('[data-kt-stepper-action="previous"]');
const navNext     = stepperRoot.querySelector('[data-kt-stepper-action="next"]');
const navSubmit   = [...stepperRoot.querySelectorAll('[data-kt-stepper-action="submit"]')];
const TOTAL_STEPS = stepperRoot.querySelectorAll('[data-kt-stepper-element="nav"]').length;

function refreshStepperActions() {
    const i = stepper.getCurrentStepIndex();
    navPrev.style.display   = i > 1 ? '' : 'none';
    navNext.style.display   = i < TOTAL_STEPS ? '' : 'none';
    navSubmit.forEach(b => { b.style.display = i === TOTAL_STEPS ? '' : 'none'; });
}

function stepError(msg) {
    const alertEl  = document.getElementById('form-alert');
    document.getElementById('form-alert-text').textContent = msg;
    alertEl.classList.remove('d-none');
    return false;
}

// Обязательные атрибуты (is_required из каталога) должны быть заполнены.
// Булев флаг (boolean) обязательным быть не может — это просто да/нет.
function requirementError(card, type, n) {
    const block = card.querySelector(`.leg-requirements [data-req="${type}"]`);
    if (!block) return null;
    const label = svcLabel(type);

    for (const a of attrsOf(type)) {
        if (!a.is_required) continue;

        if (a.input_type === 'multiselect') {
            const grp = block.querySelector(`.req-multi[data-key="${a.code}"]`);
            if (!grp || !grp.querySelectorAll('.req-multi-opt:checked').length) {
                return `Сегмент ${n}, ${label}: выберите «${a.label}».`;
            }
        } else if (a.input_type !== 'boolean') {
            const el = block.querySelector(`.req-field[data-key="${a.code}"]`);
            if (!el || el.value.trim() === '') {
                return `Сегмент ${n}, ${label}: укажите «${a.label}».`;
            }
        }
    }
    return null;
}

function validateStep(i) {
    document.getElementById('form-alert').classList.add('d-none');

    if (i === 1) {
        if (!document.getElementById('title').value.trim()) {
            document.getElementById('title').focus();
            return stepError('Укажите название заявки.');
        }
        const pax = parseInt(document.getElementById('pax_count').value, 10);
        if (!pax || pax < 1) {
            document.getElementById('pax_count').focus();
            return stepError('Укажите количество гостей.');
        }
        if (!document.getElementById('deadline_at').value) {
            return stepError('Укажите срок ответа.');
        }
    }

    if (i === 2) {
        const cards = [...legsContainer.querySelectorAll('[data-leg]')];
        if (!cards.length) return stepError('Добавьте хотя бы одну страну в маршрут.');

        for (let idx = 0; idx < cards.length; idx++) {
            const card = cards[idx], n = idx + 1;
            const code = card.querySelector('.leg-country').value;
            if (!code) return stepError(`Сегмент ${n}: выберите страну.`);
            if (!legDates(card).from) return stepError(`Сегмент ${n}: укажите даты пребывания.`);
            if (!card.querySelectorAll('.svc-chip.btn-primary').length) return stepError(`Сегмент ${n}: выберите хотя бы одну услугу.`);

            const c = countryByCode[code];
            const hasDest = c && (c.destinations || []).length > 0;
            if (hasDest && !(card._destOrder || []).length) return stepError(`Сегмент ${n}: выберите хотя бы одно направление.`);

            // Требования по каждой выбранной услуге.
            for (const chip of card.querySelectorAll('.svc-chip.btn-primary')) {
                const err = requirementError(card, chip.dataset.type, n);
                if (err) return stepError(err);
            }
        }

        // Уникальность стран
        const codes = cards.map(c => c.querySelector('.leg-country').value).filter(Boolean);
        if (new Set(codes).size !== codes.length) return stepError('Каждая страна в маршруте должна быть только один раз.');

        // Даты по ПОРЯДКУ МАРШРУТА (как расставлены карточки), без пересортировки:
        // следующая страна начинается не раньше выезда из предыдущей (граничный день общий — ок).
        const iv = cards
            .map(c => legDates(c))
            .filter(x => x.from && x.to);
        for (let k = 1; k < iv.length; k++) {
            if (iv[k].from < iv[k - 1].to) return stepError('Даты должны идти по порядку маршрута: страна начинается не раньше выезда из предыдущей (общий граничный день допустим).');
        }
    }
    return true;
}

stepper.on('kt.stepper.next', (s) => {
    if (!validateStep(s.getCurrentStepIndex())) return;
    s.goNext();
    refreshStepperActions();
    if (s.getCurrentStepIndex() === TOTAL_STEPS) renderReview();
});
stepper.on('kt.stepper.previous', (s) => { s.goPrevious(); refreshStepperActions(); });
refreshStepperActions();

// ── Шаг «Проверка» ────────────────────────────────────────────────────────────
function svcLabel(v) { const s = META.serviceTypes.find(x => x.value === v); return s ? s.label : v; }

// Сводка из атрибутов каталога, напр. «Гид (Русский, Английский, Женский)».
function svcSummary(s) {
    const r = s.requirements || {};
    const p = [];

    for (const a of attrsOf(s.service_type)) {
        const v = r[a.code];
        if (v == null || v === '' || (Array.isArray(v) && !v.length)) continue;

        if (a.input_type === 'multiselect')   p.push((v || []).map(x => optLabel(a, x)).join(', '));
        else if (a.input_type === 'select')    p.push(optLabel(a, v));
        else if (a.input_type === 'boolean')  { if (v) p.push(a.label); }
        else                                   p.push(v);
    }

    return escHtml(svcLabel(s.service_type) + (p.length ? ` (${p.join(', ')})` : ''));
}
function destName(code, id) {
    const c = countryByCode[code];
    const d = c && (c.destinations || []).find(x => x.id === id);
    return d ? d.name : '';
}

function renderReview() {
    const title    = document.getElementById('title').value.trim();
    const pax      = document.getElementById('pax_count').value;
    const dlPicker = document.getElementById('deadline_at')._flatpickr;
    const deadline = dlPicker && dlPicker.altInput ? dlPicker.altInput.value : '';
    const notes    = document.getElementById('notes').value.trim();
    const legs     = collectLegs();

    const legsHtml = legs.map((l, i) => {
        const c     = countryByCode[l.country_code];
        const dates = (l.date_from || l.date_to) ? `${formatDate(l.date_from)} — ${formatDate(l.date_to)}` : 'даты не указаны';
        const dests = l.destination_ids.map(id => escHtml(destName(l.country_code, id))).filter(Boolean).join(', ') || 'по стране в целом';
        const svcs  = l.services.map(svcSummary).join(', ') || '—';
        const flag  = c && c.flag ? `<img src="${c.flag}" class="rounded h-15px me-1" onerror="this.remove()">` : '';
        return `
            <div class="border-start border-3 border-primary ps-4 py-2">
                <div class="fw-bold text-gray-900 mb-1">${i + 1}. ${flag}${escHtml(c ? c.name : l.country_code)}</div>
                <div class="text-muted fs-7"><i class="ki-outline ki-calendar fs-7 me-1"></i>${escHtml(dates)}</div>
                <div class="text-muted fs-7"><i class="ki-outline ki-geolocation fs-7 me-1"></i>${dests}</div>
                <div class="text-muted fs-7"><i class="ki-outline ki-handcart fs-7 me-1"></i>${svcs}</div>
            </div>`;
    }).join('');

    document.getElementById('review-container').innerHTML = `
        <div class="d-flex flex-column gap-5">
            <div>
                <div class="text-muted fs-8 text-uppercase mb-1">Название</div>
                <div class="fw-bold fs-5 text-gray-900">${escHtml(title) || '—'}</div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="text-muted fs-8 text-uppercase mb-1">Гостей</div>
                    <div class="fw-semibold">${pax || '—'}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted fs-8 text-uppercase mb-1">Срок ответа</div>
                    <div class="fw-semibold">${deadline ? escHtml(deadline) + ' <span class="text-muted fw-normal">(' + escHtml(USER_TZ) + ')</span>' : '—'}</div>
                </div>
            </div>
            <div>
                <div class="text-muted fs-8 text-uppercase mb-2">Маршрут</div>
                <div class="d-flex flex-column gap-3">${legsHtml || '<span class="text-muted">нет сегментов</span>'}</div>
            </div>
            ${notes ? `<div><div class="text-muted fs-8 text-uppercase mb-1">Примечания</div><div class="fw-semibold">${escHtml(notes)}</div></div>` : ''}
            <div>
                <div class="text-muted fs-8 text-uppercase mb-1">Файлы</div>
                <div class="fw-semibold">${stagedFiles.length ? stagedFiles.length + ' шт.' : 'нет'}</div>
            </div>
        </div>`;
}

// ── Form submit ───────────────────────────────────────────────────────────────
// Enter в поле не должен сабмитить форму (кнопки теперь type="button").
document.getElementById('request-form').addEventListener('submit', e => e.preventDefault());

// mode: 'draft' — сохранить как черновик; 'submit' — сохранить и сразу подать.
async function doSubmit(mode, btn) {
    const progressTxt = btn.querySelector('.submit-progress-text');
    const alertEl     = document.getElementById('form-alert');
    const alertTxt    = document.getElementById('form-alert-text');
    const otherBtn    = mode === 'submit'
        ? document.getElementById('btn-save-draft')
        : document.getElementById('btn-save-submit');

    btnLoading(btn, true);
    if (otherBtn) otherBtn.disabled = true;
    alertEl.classList.add('d-none');

    const showError = (msg) => {
        alertTxt.textContent = msg;
        alertEl.classList.remove('d-none');
        btnLoading(btn, false);
        if (otherBtn) otherBtn.disabled = false;
    };

    const payload = {
        title:       document.getElementById('title').value.trim(),
        legs:        collectLegs(),
        pax_count:   document.getElementById('pax_count').value ? parseInt(document.getElementById('pax_count').value) : null,
        deadline_at: document.getElementById('deadline_at').value || null,
        notes:       document.getElementById('notes').value.trim() || null,
    };

    try {
        const data = EDIT
            ? await api.patch(`/requests/${EDIT.id}`, payload)
            : await api.post('/requests', payload);

        if (!data?.success || !data?.data?.id) {
            showError(data?.message ?? (data?.errors ? Object.values(data.errors).flat().join(' ') : 'Что-то пошло не так.'));
            return;
        }

        const requestId = data.data.id;

        const failed = [];
        if (stagedFiles.length) {
            for (let i = 0; i < stagedFiles.length; i++) {
                progressTxt.textContent = `Загрузка файлов… ${i + 1}/${stagedFiles.length}`;
                const fd = new FormData();
                fd.append('file', stagedFiles[i]);
                try {
                    const resp = await fetch(`/api/requests/${requestId}/attachments`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: fd,
                    });
                    if (!resp.ok) failed.push(stagedFiles[i].name);
                } catch (e) {
                    failed.push(stagedFiles[i].name);
                }
            }
        }

        // Файлы не долетели — заявку не подаём, оставляем черновиком.
        if (failed.length) {
            showError('Заявка сохранена, но не загрузились файлы: ' + failed.join(', ') + '. Заявка не подана — откройте её и подайте вручную.');
            return;
        }

        // «Сохранить и подать» — переводим черновик в «Подана».
        if (mode === 'submit') {
            progressTxt.textContent = 'Подача заявки…';
            const sub = await api.patch(`/requests/${requestId}/submit`, {});
            if (!sub?.success) {
                showError(sub?.message ?? 'Заявка сохранена, но подать не удалось. Откройте её и нажмите «Подать».');
                return;
            }
        }

        window.location.href = '/agency/requests/' + requestId;
    } catch (err) {
        showError('Ошибка соединения. Попробуйте ещё раз.');
    }
}

document.getElementById('btn-save-draft').addEventListener('click', function () { doSubmit('draft', this); });
document.getElementById('btn-save-submit').addEventListener('click', function () { doSubmit('submit', this); });
</script>
@endpush
