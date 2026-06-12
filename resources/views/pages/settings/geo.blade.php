@extends('layouts.app')
@section('title', __('settings.geo.title'))
@section('page-title', __('settings.geo.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings') }}</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings_geo') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-geolocation fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('settings.geo.card_title') }}</h3>
                <div class="text-muted fs-7">
                    {{ __('settings.geo.subtitle') }}
                    <br>{!! __('settings.geo.subtitle_drag', ['handle' => '<span class="fw-bold">⠿</span>']) !!}
                </div>
            </div>
        </div>
        <div class="card-toolbar">
            <button id="btn-add-country" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('settings.geo.btn_add') }}
            </button>
        </div>
    </div>

    <div class="card-header border-0 align-items-center">
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative">
                <i class="ki-outline ki-magnifier fs-4 position-absolute ms-3"></i>
                <input type="text" id="country-search"
                       class="form-control form-control-sm form-control-solid w-250px ps-10"
                       placeholder="{{ __('settings.search_code_name') }}" />
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="countries-loading" class="text-center py-10">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>{{ __('common.loading') }}
        </div>
        <table id="countries-table" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 d-none">
            <thead>
                <tr class="fw-bold text-muted fs-7 text-uppercase">
                    <th class="w-30px"></th>
                    <th class="min-w-200px">{{ __('settings.geo.col_country') }}</th>
                    <th class="w-80px text-center">{{ __('settings.geo.col_code') }}</th>
                    <th class="w-100px text-center">{{ __('settings.geo.col_partner') }}</th>
                    <th class="w-100px text-center">{{ __('settings.geo.col_requests') }}</th>
                    <th class="w-140px text-center">{{ __('settings.geo.col_destinations') }}</th>
                    <th class="w-100px text-end">{{ __('settings.geo.col_actions') }}</th>
                </tr>
            </thead>
            <tbody id="countries-tbody"></tbody>
        </table>
        <div id="countries-empty" class="text-center text-muted py-10 d-none">{{ __('common.nothing_found') }}</div>
    </div>
</div>

{{-- Country modal (add / edit) --}}
<div class="modal fade" id="modal-country" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold" id="country-modal-title">{{ __('settings.geo.c_add_title') }}</h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <form id="country-form">
                <div class="modal-body py-6 px-7">
                    <input type="hidden" id="country-edit-code">
                    <div class="row g-4">
                        <div class="col-4">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.geo.code_label') }}</label>
                            <input type="text" id="country-code" maxlength="2"
                                   class="form-control form-control-solid text-uppercase" placeholder="TR">
                        </div>
                        <div class="col-8">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.geo.name_label') }}</label>
                            <input type="text" id="country-name" class="form-control form-control-solid" placeholder="{{ __('settings.geo.name_ph') }}">
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold fs-7 mb-2">{{ __('settings.geo.tz_label') }}</label>
                            <select id="country-timezone" class="form-select form-select-solid">
                                <option value="">{{ __('settings.geo.tz_none') }}</option>
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz['id'] }}">{{ $tz['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('settings.geo.tz_hint') }}</div>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-6 mt-2">
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="country-is-active">
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.geo.flag_partner') }}</span>
                            </label>
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="country-for-requests">
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.geo.flag_requests') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="country-save" class="btn btn-primary btn-sm" data-kt-indicator="off">
                        <span class="indicator-label">{{ __('common.save') }}</span>
                        <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Destinations modal --}}
<div class="modal fade" id="modal-destinations" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold">{{ __('settings.geo.dest_title') }} <span id="dest-country-name"></span></h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="dest-add-form" class="d-flex gap-2 mb-5">
                    <input type="text" id="dest-name" class="form-control form-control-solid form-control-sm" placeholder="{{ __('settings.geo.dest_ph') }}">
                    <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                        <i class="ki-outline ki-plus fs-5"></i>
                    </button>
                </form>
                <div class="text-muted fs-8 mb-3">{!! __('settings.geo.dest_hint', ['handle' => '<span class="fw-bold">⠿</span>', 'icon' => '<i class="ki-outline ki-pencil fs-7"></i>']) !!}</div>
                <div id="dest-loading" class="text-center py-6 d-none">
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </div>
                <div id="dest-list" class="d-flex flex-column gap-2"></div>
                <div id="dest-empty" class="text-center text-muted fs-7 py-6 d-none">{{ __('settings.geo.dest_empty') }}</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Локальные хелперы (в этом проекте они не глобальные — каждая страница объявляет свои).
const byId    = id => document.getElementById(id);
const escHtml = str => String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
function btnLoading(btn, on) {
    if (!btn) return;
    btn.setAttribute('data-kt-indicator', on ? 'on' : 'off');
    btn.disabled = on;
}

const t  = @json(__('settings.geo'));
const tc = @json(__('common'));
let countriesCache = [];
let destCountryCode = null;

const countryModal = new bootstrap.Modal(byId('modal-country'));
const destModal     = new bootstrap.Modal(byId('modal-destinations'));

// select2 для часового пояса. dropdownParent = модалка — иначе focus-trap
// Bootstrap-модалки не даёт печатать в поле поиска.
$('#country-timezone').select2({
    dropdownParent: $('#modal-country'),
    width: '100%',
    placeholder: @json(__('settings.geo.tz_none')),
    allowClear: true,
});

// ── Countries list ──────────────────────────────────────────────────────────
async function loadCountries() {
    const q = byId('country-search').value.trim();
    byId('countries-loading').classList.remove('d-none');
    byId('countries-table').classList.add('d-none');
    byId('countries-empty').classList.add('d-none');
    try {
        const res = await api.get('/settings/countries' + (q ? `?search=${encodeURIComponent(q)}` : ''));
        countriesCache = res.data || [];
        renderCountries();
    } catch (err) {
        showToast(err?.message ?? t.load_error, 'error');
    } finally {
        byId('countries-loading').classList.add('d-none');
    }
}

function renderCountries() {
    const tbody = byId('countries-tbody');
    if (!countriesCache.length) {
        byId('countries-empty').classList.remove('d-none');
        byId('countries-table').classList.add('d-none');
        return;
    }
    byId('countries-table').classList.remove('d-none');
    const canDrag = !byId('country-search').value.trim();
    tbody.innerHTML = countriesCache.map(c => `
        <tr draggable="${canDrag ? 'true' : 'false'}" data-code="${escHtml(c.code)}">
            <td class="text-center">
                <span class="drag-handle text-gray-400 fs-3 ${canDrag ? 'cursor-move' : 'opacity-25'}"
                      title="${canDrag ? t.drag_tip : t.drag_disabled_tip}">⠿</span>
            </td>
            <td class="fw-semibold text-gray-800">${escHtml(c.name)}</td>
            <td class="text-center"><span class="badge badge-light-primary">${escHtml(c.code)}</span></td>
            <td class="text-center">
                <label class="form-check form-switch form-check-custom justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" ${c.is_active ? 'checked' : ''}
                           onchange="toggleFlag('${c.code}','is_active',this)">
                </label>
            </td>
            <td class="text-center">
                <label class="form-check form-switch form-check-custom justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" ${c.available_for_requests ? 'checked' : ''}
                           onchange="toggleFlag('${c.code}','available_for_requests',this)">
                </label>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-light-primary py-1 px-3" onclick='openDestinations(${JSON.stringify(c.code)}, ${JSON.stringify(c.name)})'>
                    <i class="ki-outline ki-geolocation fs-6 me-1"></i>${c.destinations_count}
                </button>
            </td>
            <td class="text-end">
                <button class="btn btn-icon btn-sm btn-light me-1" onclick='openCountryEdit(${JSON.stringify(c)})' title="${tc.edit}">
                    <i class="ki-outline ki-pencil fs-6"></i>
                </button>
                <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteCountry('${c.code}')" title="${tc.delete}">
                    <i class="ki-outline ki-trash fs-6"></i>
                </button>
            </td>
        </tr>`).join('');
}

async function toggleFlag(code, flag, el) {
    try {
        await api.patch(`/settings/countries/${code}`, { [flag]: el.checked });
    } catch (err) {
        el.checked = !el.checked; // откатить визуально
        showToast(err?.message ?? t.save_error, 'error');
    }
}

// ── Country add / edit ──────────────────────────────────────────────────────
byId('btn-add-country').addEventListener('click', () => {
    byId('country-modal-title').textContent = t.c_add_title;
    byId('country-form').reset();
    byId('country-edit-code').value = '';
    byId('country-code').disabled = false;
    $('#country-timezone').val('').trigger('change');
    countryModal.show();
});

function openCountryEdit(c) {
    byId('country-modal-title').textContent = t.c_edit_title;
    byId('country-edit-code').value = c.code;
    byId('country-code').value = c.code;
    byId('country-code').disabled = true;        // код менять нельзя (это ключ)
    byId('country-name').value = c.name;
    byId('country-is-active').checked = c.is_active;
    byId('country-for-requests').checked = c.available_for_requests;
    $('#country-timezone').val(c.timezone || '').trigger('change');
    countryModal.show();
}

byId('country-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn      = byId('country-save');
    const editCode = byId('country-edit-code').value;
    const payload  = {
        name:                   byId('country-name').value.trim(),
        timezone:               byId('country-timezone').value || null,
        is_active:              byId('country-is-active').checked,
        available_for_requests: byId('country-for-requests').checked,
    };

    if (!payload.name) { showToast(t.name_required, 'error'); return; }

    btnLoading(btn, true);
    try {
        if (editCode) {
            await api.patch(`/settings/countries/${editCode}`, payload);
            showToast(t.country_updated);
        } else {
            const code = byId('country-code').value.trim().toUpperCase();
            if (code.length !== 2) { showToast(t.code_2, 'error'); btnLoading(btn, false); return; }
            // Новая страна — в конец списка (порядок дальше меняется драгом).
            await api.post('/settings/countries', { code, ...payload, sort_order: countriesCache.length });
            showToast(t.country_added.replace(':code', code));
        }
        countryModal.hide();
        await loadCountries();
    } catch (err) {
        showToast(err?.message ?? t.save_error2, 'error');
    } finally {
        btnLoading(btn, false);
    }
});

async function deleteCountry(code) {
    if (!confirm(t.delete_country_confirm.replace(':code', code))) return;
    try {
        await api.delete(`/settings/countries/${code}`);
        showToast(t.country_deleted);
        await loadCountries();
    } catch (err) {
        showToast(err?.message ?? t.delete_error, 'error');
    }
}

// ── Destinations ──────────────────────────────────────────────────────────────
async function openDestinations(code, name) {
    destCountryCode = code;
    byId('dest-country-name').textContent = name;
    byId('dest-add-form').reset();
    destModal.show();
    await loadDestinations();
}

async function loadDestinations() {
    byId('dest-loading').classList.remove('d-none');
    byId('dest-list').innerHTML = '';
    byId('dest-empty').classList.add('d-none');
    try {
        const res = await api.get(`/settings/countries/${destCountryCode}/destinations`);
        const list = res.data || [];
        if (!list.length) { byId('dest-empty').classList.remove('d-none'); return; }
        byId('dest-list').innerHTML = list.map(d => `
            <div class="dest-row d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2"
                 draggable="true" data-id="${d.id}">
                <span class="drag-handle cursor-move text-gray-400 fs-4">⠿</span>
                <label class="form-check form-switch form-check-custom mb-0">
                    <input class="form-check-input" type="checkbox" ${d.is_active ? 'checked' : ''}
                           onchange="toggleDestActive(${d.id}, this)">
                </label>
                <span class="dest-name flex-grow-1 fw-semibold text-gray-800 fs-7">${escHtml(d.name)}</span>
                <button class="btn btn-icon btn-sm btn-light me-1" onclick="editDest(${d.id})" title="${t.rename_tip}">
                    <i class="ki-outline ki-pencil fs-6"></i>
                </button>
                <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteDest(${d.id})" title="${tc.delete}">
                    <i class="ki-outline ki-trash fs-6"></i>
                </button>
            </div>`).join('');
    } catch (err) {
        showToast(err?.message ?? t.dest_load_error, 'error');
    } finally {
        byId('dest-loading').classList.add('d-none');
    }
}

byId('dest-add-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const name = byId('dest-name').value.trim();
    if (!name) { showToast(t.dest_name_required, 'error'); return; }
    try {
        // Новое направление — в конец (порядок дальше меняется драгом).
        const sort = byId('dest-list').querySelectorAll('.dest-row').length;
        await api.post(`/settings/countries/${destCountryCode}/destinations`, { name, sort_order: sort });
        this.reset();
        await loadDestinations();
        await loadCountries(); // обновить счётчик в таблице
    } catch (err) {
        showToast(err?.message ?? t.dest_add_error, 'error');
    }
});

// Inline-переименование направления.
function editDest(id) {
    const row = byId('dest-list').querySelector(`[data-id="${id}"]`);
    if (!row) return;
    const span = row.querySelector('.dest-name');
    const current = span.textContent;
    row.draggable = false; // не мешать выделению текста
    const input = document.createElement('input');
    input.type = 'text';
    input.value = current;
    input.className = 'dest-name form-control form-control-solid form-control-sm flex-grow-1';
    span.replaceWith(input);
    input.focus();
    input.select();

    let done = false;
    const finish = async (save) => {
        if (done) return;
        done = true;
        const name = input.value.trim();
        if (save && name && name !== current) {
            try {
                await api.patch(`/settings/destinations/${id}`, { name });
                showToast(t.dest_renamed);
            } catch (err) {
                showToast(err?.message ?? t.save_error, 'error');
            }
        }
        await loadDestinations();
    };
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); finish(true); }
        else if (e.key === 'Escape') { finish(false); }
    });
    input.addEventListener('blur', () => finish(true));
}

async function toggleDestActive(id, el) {
    try {
        await api.patch(`/settings/destinations/${id}`, { is_active: el.checked });
    } catch (err) {
        el.checked = !el.checked;
        showToast(err?.message ?? t.save_error, 'error');
    }
}

async function deleteDest(id) {
    if (!confirm(t.dest_delete_confirm)) return;
    try {
        await api.delete(`/settings/destinations/${id}`);
        await loadDestinations();
        await loadCountries();
    } catch (err) {
        showToast(err?.message ?? t.delete_error, 'error');
    }
}

// ── Drag-and-drop сортировка (нативная, без зависимостей) ────────────────────
function getDragAfterElement(container, sel, y) {
    const els = [...container.querySelectorAll(`${sel}:not(.is-dragging)`)];
    return els.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) return { offset, element: child };
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element || null;
}

// Делегированная инициализация — вешается один раз на контейнер, работает и для
// элементов, добавленных позже (перерисовка innerHTML). canDrag() — опц. гард.
function initSortable(container, itemSel, onReorder, canDrag) {
    if (container.dataset.sortable) return;
    container.dataset.sortable = '1';
    let dragEl = null;

    container.addEventListener('dragstart', (e) => {
        const item = e.target.closest(itemSel);
        if (!item || !container.contains(item) || (canDrag && !canDrag())) { e.preventDefault(); return; }
        dragEl = item;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', ''); // нужно для Firefox
        setTimeout(() => item.classList.add('is-dragging', 'opacity-25'), 0);
    });
    container.addEventListener('dragover', (e) => {
        if (!dragEl) return;
        e.preventDefault();
        const after = getDragAfterElement(container, itemSel, e.clientY);
        if (after == null) container.appendChild(dragEl);
        else container.insertBefore(dragEl, after);
    });
    container.addEventListener('drop', (e) => e.preventDefault());
    container.addEventListener('dragend', () => {
        if (!dragEl) return;
        dragEl.classList.remove('is-dragging', 'opacity-25');
        dragEl = null;
        onReorder();
    });
}

async function persistCountryOrder() {
    const codes = [...byId('countries-tbody').querySelectorAll('tr')].map(tr => tr.dataset.code);
    // Синхронизируем кэш с новым порядком DOM.
    countriesCache.sort((a, b) => codes.indexOf(a.code) - codes.indexOf(b.code));
    try {
        await api.post('/settings/countries/reorder', { codes });
    } catch (err) {
        showToast(err?.message ?? t.reorder_error, 'error');
        await loadCountries();
    }
}

async function persistDestOrder() {
    const ids = [...byId('dest-list').querySelectorAll('.dest-row')].map(el => parseInt(el.dataset.id, 10));
    try {
        await api.post('/settings/destinations/reorder', { ids });
    } catch (err) {
        showToast(err?.message ?? t.reorder_error, 'error');
        await loadDestinations();
    }
}

initSortable(byId('countries-tbody'), 'tr', persistCountryOrder, () => !byId('country-search').value.trim());
initSortable(byId('dest-list'), '.dest-row', persistDestOrder);

// ── Boot ────────────────────────────────────────────────────────────────────
let searchTimer;
byId('country-search').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadCountries, 250);
});

loadCountries();
</script>
@endpush
