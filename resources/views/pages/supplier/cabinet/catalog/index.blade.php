@extends('layouts.supplier')

@section('title', __('suppliers.cabinet.catalog.title'))
@section('page-title', __('suppliers.cabinet.catalog.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('suppliers.cabinet.catalog.title') }}</li>
@endsection

@section('toolbar-actions')
    @if($supplier)
    <button class="btn btn-primary btn-sm" id="btn-add-resource">
        <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('suppliers.cabinet.catalog.add') }}
    </button>
    @endif
@endsection

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
    #resource-price { -moz-appearance: textfield; }
    #resource-price::-webkit-outer-spin-button,
    #resource-price::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    #resource-currency-label { border: none; background: var(--bs-gray-100, #f5f8fa); padding-left: 6px; padding-right: 0; }
</style>
@endpush

@section('content')

{{-- Info notice: what the catalog is and why it’s convenient --}}
<div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-5 mb-6">
    <i class="ki-outline ki-information-5 fs-2tx text-primary me-4 flex-shrink-0"></i>
    <div>
        <h4 class="fw-bold text-gray-900 mb-1">{{ __('suppliers.cabinet.catalog.info_title') }}</h4>
        <div class="fs-7 text-gray-700">{{ __('suppliers.cabinet.catalog.info_body') }}</div>
    </div>
</div>

<div class="card card-flush">

    {{-- Type chips with counts --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="type-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('suppliers.cabinet.catalog.loading') }}</span>
        </div>
    </div>

    {{-- Search + availability --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="catalog-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('suppliers.cabinet.catalog.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="catalog-avail" class="form-select form-select-solid w-200px flex-shrink-0">
                <option value="">{{ __('suppliers.cabinet.catalog.avail.all') }}</option>
                <option value="available">{{ __('suppliers.cabinet.catalog.avail.available') }}</option>
                <option value="unavailable">{{ __('suppliers.cabinet.catalog.avail.unavailable') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="resources-loading" class="text-center py-20">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div id="resources-empty" class="text-center py-20 d-none"></div>

        <div id="resources-grid" class="row g-5 d-none"></div>
    </div>
</div>

{{-- Resource Modal (create / edit) --}}
<div class="modal fade" id="modal-resource" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modal-resource-title">{{ __('suppliers.cabinet.catalog.modal.title_new') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-7 pt-5 pb-4">
                <input type="hidden" id="resource-id" value="" />

                <div class="row g-5 mb-5">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold required">{{ __('suppliers.cabinet.catalog.modal.type') }}</label>
                        <select id="resource-type" class="form-select form-select-solid" onchange="onResourceTypeChange(this.value)">
                            @foreach($supplier?->service_types ?? [] as $type)
                                <option value="{{ $type }}">{{ app(\App\Domain\Services\ServiceCatalog::class)->typeLabel($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold required">{{ __('suppliers.cabinet.catalog.modal.name') }}</label>
                        <input type="text" id="resource-name" class="form-control form-control-solid"
                               placeholder="{{ __('suppliers.cabinet.catalog.modal.name_ph') }}" />
                        <div class="text-muted fs-8 mt-1" id="resource-name-hint"></div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.description') }} <span class="text-muted fw-normal">{{ __('suppliers.cabinet.catalog.modal.optional') }}</span></label>
                    <textarea id="resource-description" class="form-control form-control-solid" rows="3"
                              placeholder="{{ __('suppliers.cabinet.catalog.modal.desc_ph') }}"></textarea>
                </div>

                <div class="row g-5 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.capacity') }} <span class="text-muted fw-normal">{{ __('suppliers.cabinet.catalog.modal.optional_s') }}</span></label>
                        <input type="number" id="resource-capacity" class="form-control form-control-solid"
                               placeholder="{{ __('suppliers.cabinet.catalog.modal.capacity_ph') }}" min="1" />
                        <div class="text-muted fs-8 mt-1" id="resource-capacity-hint">{{ __('suppliers.cabinet.catalog.modal.capacity_hint') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.contact_name') }} <span class="text-muted fw-normal">{{ __('suppliers.cabinet.catalog.modal.optional_s') }}</span></label>
                        <input type="text" id="resource-contact-name" class="form-control form-control-solid"
                               placeholder="{{ __('suppliers.cabinet.catalog.modal.contact_name_ph') }}" maxlength="150" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.contact_phone') }} <span class="text-muted fw-normal">{{ __('suppliers.cabinet.catalog.modal.optional_s') }}</span></label>
                        <input type="text" id="resource-contact-phone" class="form-control form-control-solid js-phone"
                               placeholder="+994 50 000 00 00" maxlength="50" />
                    </div>
                </div>

                <div class="separator my-5"></div>

                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="fw-semibold text-gray-700">{{ __('suppliers.cabinet.catalog.modal.base_rate') }}</span>
                    <span class="badge badge-light-warning fs-8">{{ __('suppliers.cabinet.catalog.modal.for_reference') }}</span>
                </div>
                <div class="text-muted fs-8 mb-4">{{ __('suppliers.cabinet.catalog.modal.base_rate_hint') }}</div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.rate') }}</label>
                        <div class="input-group">
                            <input type="number" id="resource-price" class="form-control form-control-solid"
                                   placeholder="0.00" min="0" step="0.01" />
                            <span class="input-group-text fw-semibold fs-6" id="resource-currency-label"></span>
                        </div>
                        <select id="resource-currency" class="d-none"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('suppliers.cabinet.catalog.modal.unit') }}</label>
                        <select id="resource-price-unit" class="form-select form-select-solid">
                            <option value="per_person">{{ __('suppliers.cabinet.catalog.modal.units.per_person') }}</option>
                            <option value="per_day">{{ __('suppliers.cabinet.catalog.modal.units.per_day') }}</option>
                            <option value="per_night">{{ __('suppliers.cabinet.catalog.modal.units.per_night') }}</option>
                            <option value="per_vehicle">{{ __('suppliers.cabinet.catalog.modal.units.per_vehicle') }}</option>
                            <option value="per_group">{{ __('suppliers.cabinet.catalog.modal.units.per_group') }}</option>
                            <option value="fixed">{{ __('suppliers.cabinet.catalog.modal.units.fixed') }}</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('suppliers.cabinet.catalog.modal.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-resource-save">
                    <span class="indicator-label">
                        <i class="ki-outline ki-check fs-4 me-1"></i>{{ __('suppliers.cabinet.catalog.modal.save') }}
                    </span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-2"></span>{{ __('suppliers.cabinet.catalog.modal.saving') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Photos Modal --}}
<div class="modal fade" id="modal-photos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-photos-title">{{ __('suppliers.cabinet.catalog.photos.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <input type="file" id="photos-filepond" multiple accept="image/jpeg,image/png,image/webp" />
                </div>
                <div id="photos-grid" class="row g-4"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('suppliers.cabinet.catalog.photos.close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Подтверждение удаления ресурса --}}
<div class="modal fade" id="modal-delete-resource" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('suppliers.cabinet.catalog.del_modal.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-3">
                    <i class="ki-outline ki-trash fs-2x text-danger flex-shrink-0"></i>
                    <p class="text-gray-700 fs-6 mb-0" id="modal-delete-resource-body"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('suppliers.cabinet.catalog.del_modal.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="modal-delete-resource-confirm">
                    <span class="indicator-label"><i class="ki-outline ki-trash fs-5 me-1"></i>{{ __('suppliers.cabinet.catalog.del_modal.confirm') }}</span>
                    <span class="indicator-progress">{{ __('suppliers.cabinet.catalog.del_modal.confirm') }}... <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
const supplierId       = {{ $supplier?->id ?? 'null' }};
const supplierCurrency = '{{ strtoupper($supplier?->currency_code ?? 'AZN') }}';
const L = @json(__('suppliers.cabinet.catalog'));
const PHOTO_LIMIT = @json((int) config('uploads.max_files_per_collection', 20));
let allResources    = [];
let activeTypeTab   = '';
let searchQuery     = '';
let availFilter     = '';
let activeCurrencies = [];
let photoPond       = null;
let activePhotoResourceId = null;

// Цвет/иконка — не переводятся; подписи берём из lang (L.types).
const TYPE_STYLE = {
    accommodation: { color: 'primary',   icon: 'ki-home-2' },
    transport:     { color: 'info',      icon: 'ki-car' },
    guide:         { color: 'success',   icon: 'ki-profile-user' },
    activity:      { color: 'warning',   icon: 'ki-rocket' },
    other:         { color: 'secondary', icon: 'ki-abstract-26' },
};
const TYPE_META = Object.fromEntries(
    Object.entries(TYPE_STYLE).map(([t, style]) => [t, { ...style, ...(L.types[t] ?? {}) }])
);

const PRICE_UNIT_LABELS = L.unit_short;

function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fmtMoney(amount, currency) {
    if (!amount) return null;
    return Number(amount).toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + (currency ?? '');
}

// ── Load & render ─────────────────────────────────────────────────────────────

async function loadResources() {
    if (!supplierId) {
        document.getElementById('resources-loading').classList.add('d-none');
        document.getElementById('type-chips').innerHTML = '';
        renderResources();
        return;
    }
    try {
        const data = await api.get(`/suppliers/${supplierId}/services?per_page=200`);
        allResources = data?.data ?? [];
        renderResources();
    } catch (err) {
        showToast(err?.message ?? L.toast.load_err, 'error');
    } finally {
        document.getElementById('resources-loading').classList.add('d-none');
    }
}

// Resources matching search + availability (but NOT the active type chip).
// This set drives the chip counts; the type chip narrows it further.
function baseFiltered() {
    let list = allResources;
    if (availFilter === 'available')   list = list.filter(r => r.is_available);
    if (availFilter === 'unavailable') list = list.filter(r => !r.is_available);
    const q = searchQuery.trim().toLowerCase();
    if (q) {
        list = list.filter(r =>
            (r.name ?? '').toLowerCase().includes(q) ||
            (r.description ?? '').toLowerCase().includes(q) ||
            (r.contact_name ?? '').toLowerCase().includes(q) ||
            (r.contact_phone ?? '').toLowerCase().includes(q)
        );
    }
    return list;
}

function renderChips() {
    const base = baseFiltered();
    const counts = {};
    base.forEach(r => { counts[r.type] = (counts[r.type] ?? 0) + 1; });

    // Show "Все" + every type that exists anywhere in the catalog, in TYPE_META order.
    const order   = Object.keys(TYPE_META);
    const present = order.filter(t => allResources.some(r => r.type === t));
    allResources.forEach(r => { if (!present.includes(r.type)) present.push(r.type); });

    const defs = [{ type: '', label: L.chip_all, color: 'dark', n: base.length }];
    present.forEach(t => {
        const meta = TYPE_META[t] ?? { label: t, color: 'secondary' };
        defs.push({ type: t, label: meta.label, color: meta.color, n: counts[t] ?? 0 });
    });

    document.getElementById('type-chips').innerHTML = defs.map(c => {
        const active = c.type === activeTypeTab;
        const cls = active ? `badge-${c.color}` : `badge-light-${c.color}`;
        return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                      onclick="setTypeTab('${c.type}')">${esc(c.label)}: ${c.n}</span>`;
    }).join('');
}

function renderResources() {
    renderChips();

    const base     = baseFiltered();
    const filtered = activeTypeTab ? base.filter(r => r.type === activeTypeTab) : base;

    const grid  = document.getElementById('resources-grid');
    const empty = document.getElementById('resources-empty');

    if (!filtered.length) {
        grid.classList.add('d-none');
        grid.innerHTML = '';
        empty.innerHTML = allResources.length
            ? `<i class="ki-outline ki-magnifier fs-4x text-gray-300 mb-4 d-block"></i>
               <div class="text-gray-600 fw-semibold fs-5">${L.empty.not_found_title}</div>
               <div class="text-muted fs-7 mt-2 mb-6">${L.empty.not_found_hint}</div>
               <button class="btn btn-light btn-sm" onclick="resetFilters()">
                   <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>${L.empty.reset}
               </button>`
            : `<i class="ki-outline ki-category fs-4x text-gray-300 mb-4 d-block"></i>
               <div class="text-gray-600 fw-semibold fs-5">${L.empty.none_title}</div>
               <div class="text-muted fs-7 mt-2 mb-6">${L.empty.none_hint}</div>
               <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                   <i class="ki-outline ki-plus fs-4 me-1"></i>${L.empty.none_add}
               </button>`;
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    grid.classList.remove('d-none');

    // Group by type for visual sections
    const grouped = new Map();
    filtered.forEach(r => {
        if (!grouped.has(r.type)) grouped.set(r.type, []);
        grouped.get(r.type).push(r);
    });

    let html = '';
    grouped.forEach((items, type) => {
        const meta = TYPE_META[type] ?? { heading: type, label: type, color: 'secondary', icon: 'ki-abstract-26' };
        html += `
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="w-32px h-32px rounded-circle bg-light-${meta.color} d-flex align-items-center justify-content-center">
                    <i class="ki-outline ${meta.icon} fs-5 text-${meta.color}"></i>
                </span>
                <h5 class="fw-bold text-gray-800 mb-0">${esc(meta.heading)}</h5>
                <span class="badge badge-light fs-8">${items.length}</span>
            </div>
        </div>`;
        items.forEach(r => { html += resourceCard(r, meta); });
        html += `<div class="col-12 mb-2"></div>`;
    });

    grid.innerHTML = html;
}

function resourceCard(r, meta) {
    const photos = r.photos ?? [];
    const thumb  = photos.length
        ? `<img src="${esc(photos[0].url)}" class="rounded-top-2 w-100" style="height:130px;object-fit:cover" alt="">`
        : `<div class="rounded-top-2 w-100 bg-light d-flex align-items-center justify-content-center" style="height:130px">
               <img src="/assets/media/svg/files/blank-image.svg" style="height:64px;opacity:0.35" alt="">
           </div>`;

    const rateHtml = (r.base_price && r.price_unit)
        ? `<div class="text-muted fs-8 mt-1">
               <i class="ki-outline ki-information-3 fs-8 me-1"></i>${L.card.rate} ${fmtMoney(r.base_price, r.currency)} ${PRICE_UNIT_LABELS[r.price_unit] ?? r.price_unit}
           </div>`
        : '';

    const contactHtml = (r.contact_name || r.contact_phone)
        ? `<div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
               <i class="ki-outline ki-user fs-7 text-muted flex-shrink-0"></i>
               <div class="min-w-0">
                   ${r.contact_name  ? `<div class="fw-semibold text-gray-700 fs-8 text-truncate">${esc(r.contact_name)}</div>` : ''}
                   ${r.contact_phone ? `<a href="tel:${esc(r.contact_phone)}" class="text-muted fs-8 text-hover-primary">${esc(r.contact_phone)}</a>` : ''}
               </div>
           </div>`
        : '';

    return `
    <div class="col-xl-3 col-md-4 col-sm-6" data-resource-id="${r.id}">
        <div class="card h-100 border ${r.is_available ? '' : 'opacity-60'}">
            ${thumb}
            <div class="card-body px-5 py-4 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <h6 class="fw-bold text-gray-900 mb-0 fs-6 lh-sm">${esc(r.name)}</h6>
                    <div class="form-check form-switch form-check-solid flex-shrink-0 ms-1" title="${r.is_available ? L.card.available : L.card.unavailable}">
                        <input class="form-check-input" type="checkbox" ${r.is_available ? 'checked' : ''}
                               onchange="toggleAvailable(${r.id}, this)" />
                    </div>
                </div>
                ${r.capacity ? `<div class="text-muted fs-8 mb-1"><i class="ki-outline ki-people fs-8 me-1"></i>${r.capacity} ${L.card.pax}</div>` : ''}
                ${r.description ? `<p class="text-muted fs-8 mb-2 flex-grow-1 lh-base">${esc(r.description).substring(0,90)}${r.description.length > 90 ? '…' : ''}</p>` : '<div class="flex-grow-1"></div>'}
                ${contactHtml}
                ${rateHtml}
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-sm btn-light flex-grow-1" title="${L.card.photos}"
                            onclick="openPhotos(${r.id}, '${esc(r.name)}')">
                        <i class="ki-outline ki-picture fs-6 pe-0"></i>
                        ${photos.length > 0 ? `<span class="ms-1 text-primary fw-semibold">${photos.length}</span>` : ''}
                    </button>
                    <button class="btn btn-sm btn-light-primary" title="${L.card.edit}"
                            onclick="editResource(${r.id})">
                        <i class="ki-outline ki-pencil fs-6 pe-0"></i>
                    </button>
                    <button class="btn btn-sm btn-light-danger" title="${L.card.delete}"
                            onclick="deleteResource(${r.id})">
                        <i class="ki-outline ki-trash fs-6 pe-0"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

// ── Tab filter ────────────────────────────────────────────────────────────────

function setTypeTab(type) {
    activeTypeTab = type;
    renderResources();
}

function resetFilters() {
    searchQuery   = '';
    availFilter   = '';
    activeTypeTab = '';
    document.getElementById('catalog-search').value = '';
    document.getElementById('catalog-avail').value  = '';
    renderResources();
}

// ── Modal helpers ─────────────────────────────────────────────────────────────

function onResourceTypeChange(type) {
    const meta = TYPE_META[type] ?? {};
    document.getElementById('resource-name-hint').textContent     = meta.namehint ?? '';
    document.getElementById('resource-capacity-hint').textContent = meta.caphint || L.modal.capacity_hint;
}

function openCreateModal(prefillType) {
    document.getElementById('resource-id').value            = '';
    document.getElementById('resource-name').value          = '';
    document.getElementById('resource-description').value   = '';
    document.getElementById('resource-capacity').value      = '';
    document.getElementById('resource-contact-name').value  = '';
    window.prefillPhoneDial('#resource-contact-phone');
    document.getElementById('resource-price').value         = '';
    document.getElementById('resource-price-unit').value    = 'per_day';
    document.getElementById('modal-resource-title').textContent = L.modal.title_new;

    const typeSelect = document.getElementById('resource-type');
    if (prefillType) typeSelect.value = prefillType;
    onResourceTypeChange(typeSelect.value);

    populateCurrencySelect(supplierCurrency);
    new bootstrap.Modal(document.getElementById('modal-resource')).show();
}

function editResource(id) {
    const r = allResources.find(r => r.id === id);
    if (!r) return;

    document.getElementById('resource-id').value            = r.id;
    document.getElementById('resource-type').value          = r.type;
    document.getElementById('resource-name').value          = r.name;
    document.getElementById('resource-description').value   = r.description ?? '';
    document.getElementById('resource-capacity').value      = r.capacity ?? '';
    document.getElementById('resource-contact-name').value  = r.contact_name  ?? '';
    if (r.contact_phone) window.setPhoneValue('#resource-contact-phone', r.contact_phone);
    else                 window.prefillPhoneDial('#resource-contact-phone');
    document.getElementById('resource-price').value         = r.base_price ?? '';
    document.getElementById('resource-price-unit').value    = r.price_unit ?? 'per_day';
    document.getElementById('modal-resource-title').textContent = L.modal.title_edit;

    onResourceTypeChange(r.type);
    populateCurrencySelect(r.currency ?? supplierCurrency);
    new bootstrap.Modal(document.getElementById('modal-resource')).show();
}

// ── Save ──────────────────────────────────────────────────────────────────────

document.getElementById('btn-resource-save').addEventListener('click', async function () {
    const id           = document.getElementById('resource-id').value;
    const type         = document.getElementById('resource-type').value;
    const name         = document.getElementById('resource-name').value.trim();
    const desc         = document.getElementById('resource-description').value.trim();
    const capacity     = document.getElementById('resource-capacity').value;
    const contactName  = document.getElementById('resource-contact-name').value.trim();
    const contactPhone = (window.getPhoneValue('#resource-contact-phone') || '').trim();
    const price        = document.getElementById('resource-price').value;
    const currency     = document.getElementById('resource-currency').value;
    const unit         = document.getElementById('resource-price-unit').value;

    if (!name) { showToast(L.toast.name_required, 'error'); return; }

    this.disabled = true;
    this.querySelector('.indicator-label').classList.add('d-none');
    this.querySelector('.indicator-progress').classList.remove('d-none');

    const payload = {
        type,
        name,
        description:   desc         || null,
        capacity:      capacity     ? parseInt(capacity) : null,
        contact_name:  contactName  || null,
        contact_phone: contactPhone || null,
        base_price:    price        ? parseFloat(price)  : null,
        currency:      price        ? currency           : null,
        price_unit:    price        ? unit               : null,
    };

    try {
        if (id) {
            await api.patch(`/suppliers/${supplierId}/services/${id}`, payload);
            showToast(L.toast.updated);
        } else {
            await api.post(`/suppliers/${supplierId}/services`, payload);
            showToast(L.toast.added);
        }
        bootstrap.Modal.getInstance(document.getElementById('modal-resource'))?.hide();
        await loadResources();
    } catch (err) {
        showToast(err?.message ?? L.toast.save_err, 'error');
    } finally {
        this.disabled = false;
        this.querySelector('.indicator-label').classList.remove('d-none');
        this.querySelector('.indicator-progress').classList.add('d-none');
    }
});

let _deleteResourceModal = null;
let _pendingDeleteResourceId = null;

function deleteResource(id) {
    const r = allResources.find(r => r.id === id);
    _pendingDeleteResourceId = id;
    document.getElementById('modal-delete-resource-body').textContent =
        L.del_modal.body.replace(':name', r?.name ?? '');
    if (!_deleteResourceModal) {
        _deleteResourceModal = new bootstrap.Modal(document.getElementById('modal-delete-resource'));
    }
    _deleteResourceModal.show();
}

document.getElementById('modal-delete-resource-confirm').addEventListener('click', async function () {
    const id = _pendingDeleteResourceId;
    if (!id) return;
    window.btnLoading?.(this, true);
    try {
        await api.delete(`/suppliers/${supplierId}/services/${id}`);
        _deleteResourceModal?.hide();
        _pendingDeleteResourceId = null;
        showToast(L.toast.deleted);
        await loadResources();
    } catch (err) {
        showToast(err?.message ?? L.toast.del_err, 'error');
    } finally {
        window.btnLoading?.(this, false);
    }
});

async function toggleAvailable(id, checkbox) {
    try {
        await api.patch(`/suppliers/${supplierId}/services/${id}/toggle-available`);
        const r = allResources.find(r => r.id === id);
        if (r) r.is_available = !r.is_available;
    } catch (err) {
        checkbox.checked = !checkbox.checked;
        showToast(err?.message ?? L.toast.generic, 'error');
    }
}

// ── Photos ────────────────────────────────────────────────────────────────────

function openPhotos(resourceId, resourceName) {
    activePhotoResourceId = resourceId;
    document.getElementById('modal-photos-title').textContent = resourceName;

    if (photoPond) { photoPond.destroy(); photoPond = null; }

    const r = allResources.find(r => r.id === resourceId);
    renderPhotoGrid(r?.photos ?? []);

    // Лимит фото на ресурс: остаток с учётом уже загруженных.
    const existing  = (r?.photos ?? []).length;
    const remaining = Math.max(0, PHOTO_LIMIT - existing);

    const input = document.getElementById('photos-filepond');
    photoPond = FilePond.create(input, {
        allowMultiple: true,
        maxFiles: remaining,
        disabled: remaining === 0,
        maxFileSize: '10MB',
        labelIdle: remaining === 0
            ? L.photos.limit_reached.replace(':n', PHOTO_LIMIT)
            : `${L.photos.fp_idle}<br><span style="font-size:11px;color:#a1a5b7">${L.photos.limit_hint.replace(':n', PHOTO_LIMIT)}</span>`,
        onprocessfile: (error, file) => {
            if (!error) {
                setTimeout(() => photoPond.removeFile(file), 800);
                reloadPhotos(resourceId);
            }
        },
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                const fd = new FormData();
                fd.append('photo', file);
                const xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.open('POST', `/api/suppliers/${supplierId}/services/${resourceId}/photos`);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                xhr.upload.onprogress = (e) => progress(e.lengthComputable, e.loaded, e.total);
                xhr.onload  = () => {
                    if (xhr.status >= 200 && xhr.status < 300) { load(xhr.responseText); return; }
                    let msg = L.toast.upload_err;
                    try { msg = JSON.parse(xhr.responseText)?.message || msg; } catch (e) {}
                    error(msg);
                };
                xhr.onerror = () => error(L.toast.network_err);
                xhr.send(fd);
                return { abort: () => { xhr.abort(); abort(); } };
            },
        },
    });

    new bootstrap.Modal(document.getElementById('modal-photos')).show();
}

async function reloadPhotos(resourceId) {
    try {
        const data = await api.get(`/suppliers/${supplierId}/services?per_page=200`);
        allResources = data?.data ?? [];
        const r = allResources.find(r => r.id === resourceId);
        renderPhotoGrid(r?.photos ?? []);
        renderResources();   // обновить миниатюру и счётчик фото в карточке грида
    } catch {}
}

function renderPhotoGrid(photos) {
    const grid = document.getElementById('photos-grid');
    if (!photos.length) {
        grid.innerHTML = `<div class="col-12 text-center text-muted py-4 fs-7">${L.photos.empty}</div>`;
        return;
    }
    grid.innerHTML = photos.map(p => `
        <div class="col-4 col-md-3" id="photo-${p.id}">
            <div class="position-relative">
                <img src="${esc(p.url)}" class="w-100 rounded-2" style="height:100px;object-fit:cover" alt="">
                <button type="button"
                        class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 m-1"
                        onclick="deletePhoto(${activePhotoResourceId}, ${p.id})"
                        style="width:24px;height:24px;padding:0">
                    <i class="ki-outline ki-cross fs-8"></i>
                </button>
            </div>
        </div>`).join('');
}

async function deletePhoto(resourceId, mediaId) {
    try {
        await api.delete(`/suppliers/${supplierId}/services/${resourceId}/photos/${mediaId}`);
        document.getElementById(`photo-${mediaId}`)?.remove();
        const r = allResources.find(r => r.id === resourceId);
        if (r) r.photos = (r.photos ?? []).filter(p => p.id !== mediaId);
        renderResources();   // обновить миниатюру и счётчик фото в карточке грида
    } catch (err) {
        showToast(err?.message ?? L.toast.photo_del_err, 'error');
    }
}

// ── Currencies ────────────────────────────────────────────────────────────────

async function loadCurrencies() {
    try {
        const data = await api.get('/settings/currencies/active');
        activeCurrencies = data.data ?? [];
    } catch {
        activeCurrencies = [{ code: supplierCurrency, name: supplierCurrency }];
    }
}

function populateCurrencySelect(selectedCode) {
    const sel = document.getElementById('resource-currency');
    sel.innerHTML = activeCurrencies.map(c =>
        `<option value="${c.code}" ${c.code === (selectedCode ?? supplierCurrency) ? 'selected' : ''}>${c.code} — ${currencyName(c.code, c.name)}</option>`
    ).join('');
    document.getElementById('resource-currency-label').textContent = sel.value;
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.getElementById('btn-add-resource')?.addEventListener('click', () => openCreateModal());

let searchDebounce = null;
document.getElementById('catalog-search')?.addEventListener('input', function () {
    clearTimeout(searchDebounce);
    const v = this.value;
    searchDebounce = setTimeout(() => { searchQuery = v; renderResources(); }, 150);
});
document.getElementById('catalog-avail')?.addEventListener('change', function () {
    availFilter = this.value;
    renderResources();
});

Promise.all([loadCurrencies(), loadResources()]);
</script>
@endpush
