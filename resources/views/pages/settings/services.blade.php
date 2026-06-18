@extends('layouts.app')
@section('title', __('settings.services.title'))
@section('page-title', __('settings.services.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings') }}</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings_services') }}</li>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-handcart fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('settings.services.card_title') }}</h3>
                <div class="text-muted fs-7">{{ __('settings.services.subtitle') }}</div>
            </div>
        </div>
        <div class="card-toolbar">
            <button id="btn-add-type" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('settings.services.btn_add_type') }}
            </button>
        </div>
    </div>

    <div class="card-header border-0 align-items-center">
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative">
                <i class="ki-outline ki-magnifier fs-4 position-absolute ms-3"></i>
                <input type="text" id="type-search"
                       class="form-control form-control-sm form-control-solid w-250px ps-10"
                       placeholder="{{ __('settings.search_code_name') }}" />
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="types-loading" class="text-center py-10">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>{{ __('common.loading') }}
        </div>
        <table id="types-table" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 d-none">
            <thead>
                <tr class="fw-bold text-muted fs-7 text-uppercase">
                    <th class="w-30px"></th>
                    <th class="min-w-200px">{{ __('settings.services.col_type') }}</th>
                    <th class="w-90px text-center">{{ __('settings.services.col_markup') }}</th>
                    <th class="w-90px text-center">{{ __('settings.services.col_active') }}</th>
                    <th class="w-110px text-center">{{ __('settings.services.col_requests') }}</th>
                    <th class="w-130px text-center">{{ __('settings.services.col_attributes') }}</th>
                    <th class="w-100px text-end">{{ __('settings.services.col_actions') }}</th>
                </tr>
            </thead>
            <tbody id="types-tbody"></tbody>
        </table>
        <div id="types-empty" class="text-center text-muted py-10 d-none">{{ __('common.nothing_found') }}</div>
    </div>
</div>

{{-- Type modal (add / edit) --}}
<div class="modal fade" id="modal-type" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold" id="type-modal-title"></h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <form id="type-form">
                <div class="modal-body py-6 px-7">
                    <input type="hidden" id="type-edit-id">
                    <div class="row g-4">
                        <div class="col-6">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.services.code_label') }}</label>
                            <input type="text" id="type-code" class="form-control form-control-solid" placeholder="insurance">
                            <div class="form-text">{{ __('settings.services.code_hint') }}</div>
                        </div>
                        <div class="col-6">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.services.name_label') }}</label>
                            <input type="text" id="type-name" class="form-control form-control-solid" placeholder="Insurance">
                            <div class="form-text">{{ __('settings.services.name_hint') }}</div>
                        </div>
                        <div class="col-12">
                            <label class="fw-semibold fs-7 mb-2">{{ __('settings.services.markup_label') }}</label>
                            <div class="input-group input-group-solid">
                                <input type="number" id="type-markup" min="0" max="100" step="0.01" class="form-control form-control-solid" placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-6 mt-2">
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="type-is-active" checked>
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.services.flag_active') }}</span>
                            </label>
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="type-for-requests" checked>
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.services.flag_requests') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="type-save" class="btn btn-primary btn-sm" data-kt-indicator="off">
                        <span class="indicator-label">{{ __('common.save') }}</span>
                        <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Attributes list modal --}}
<div class="modal fade" id="modal-attributes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold">{{ __('settings.services.attrs_title') }} <span id="attrs-type-name" class="text-primary"></span></h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="text-muted fs-8">{{ __('settings.services.attrs_hint') }}</div>
                    <button id="btn-add-attr" class="btn btn-sm btn-light-primary flex-shrink-0">
                        <i class="ki-outline ki-plus fs-5 me-1"></i>{{ __('settings.services.btn_add_attr') }}
                    </button>
                </div>
                <div id="attrs-loading" class="text-center py-6 d-none">
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </div>
                <div id="attrs-list" class="d-flex flex-column gap-2"></div>
                <div id="attrs-empty" class="text-center text-muted fs-7 py-6 d-none">{{ __('settings.services.attrs_empty') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Attribute edit modal (add / edit) --}}
<div class="modal fade" id="modal-attr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold" id="attr-modal-title"></h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <form id="attr-form">
                <div class="modal-body py-6 px-7">
                    <input type="hidden" id="attr-edit-id">
                    <div class="row g-4">
                        <div class="col-6">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.services.code_label') }}</label>
                            <input type="text" id="attr-code" class="form-control form-control-solid" placeholder="coverage">
                            <div class="form-text">{{ __('settings.services.attr_code_hint') }}</div>
                        </div>
                        <div class="col-6">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.services.name_label') }}</label>
                            <input type="text" id="attr-name" class="form-control form-control-solid" placeholder="Coverage">
                        </div>
                        <div class="col-12">
                            <label class="required fw-semibold fs-7 mb-2">{{ __('settings.services.input_type_label') }}</label>
                            <select id="attr-input-type" class="form-select form-select-solid">
                                <option value="select">{{ __('settings.services.it_select') }}</option>
                                <option value="multiselect">{{ __('settings.services.it_multiselect') }}</option>
                                <option value="boolean">{{ __('settings.services.it_boolean') }}</option>
                                <option value="number">{{ __('settings.services.it_number') }}</option>
                                <option value="text">{{ __('settings.services.it_text') }}</option>
                                <option value="textarea">{{ __('settings.services.it_textarea') }}</option>
                            </select>
                        </div>
                        <div class="col-12" id="attr-options-wrap">
                            <label class="fw-semibold fs-7 mb-2">{{ __('settings.services.options_label') }}</label>
                            <div class="text-muted fs-8 mb-2">{{ __('settings.services.options_hint') }}</div>
                            <div id="attr-options-list" class="d-flex flex-column gap-2 mb-2"></div>
                            <button type="button" id="btn-add-option" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-plus fs-6 me-1"></i>{{ __('settings.services.btn_add_option') }}
                            </button>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-6 mt-2">
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="attr-is-required">
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.services.flag_required') }}</span>
                            </label>
                            <label class="form-check form-switch form-check-custom">
                                <input class="form-check-input" type="checkbox" id="attr-is-active" checked>
                                <span class="form-check-label fw-semibold ms-2">{{ __('settings.services.flag_active') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="attr-save" class="btn btn-primary btn-sm" data-kt-indicator="off">
                        <span class="indicator-label">{{ __('common.save') }}</span>
                        <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-1"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cannot-delete info modal (тип используется — нельзя удалить) --}}
<div class="modal fade" id="modal-delete-blocked" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="fw-bold d-flex align-items-center gap-2">
                    <i class="ki-outline ki-information-5 fs-2 text-warning"></i>{{ __('settings.services.cannot_delete_title') }}
                </h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <div class="modal-body py-6 px-7">
                <p class="text-gray-700 fs-6 mb-0" id="delete-blocked-msg"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Локальные хелперы (в этом проекте не глобальные — каждая страница объявляет свои).
const byId    = id => document.getElementById(id);
const escHtml = str => String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
function btnLoading(btn, on) {
    if (!btn) return;
    btn.setAttribute('data-kt-indicator', on ? 'on' : 'off');
    btn.disabled = on;
}

// Транслитерация для авто-генерации кода из названия (RU + AZ/TR-спецсимволы).
const TRANSLIT = {
    а:'a',б:'b',в:'v',г:'g',д:'d',е:'e',ё:'e',ж:'zh',з:'z',и:'i',й:'y',к:'k',л:'l',м:'m',
    н:'n',о:'o',п:'p',р:'r',с:'s',т:'t',у:'u',ф:'f',х:'h',ц:'ts',ч:'ch',ш:'sh',щ:'sch',
    ъ:'',ы:'y',ь:'',э:'e',ю:'yu',я:'ya',
    ə:'e',ğ:'g',ş:'s',ç:'c',ı:'i',ö:'o',ü:'u',
};
// Название → код по маске ^[a-z][a-z0-9_]*$ (нижний регистр, _ вместо разделителей).
function slugify(str) {
    let out = '';
    for (const ch of String(str || '').toLowerCase()) out += (ch in TRANSLIT) ? TRANSLIT[ch] : ch;
    return out
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/_+/g, '_')
        .replace(/^[_0-9]+/, '')   // код должен начинаться с буквы
        .replace(/_+$/, '');
}

// Авто-подсказка кода из названия: пока поле кода активно и не правилось вручную.
function wireCodeSuggest(nameId, codeId) {
    const name = byId(nameId), code = byId(codeId);
    let touched = false;
    name.addEventListener('input', () => {
        if (code.disabled || touched) return;
        code.value = slugify(name.value);
    });
    code.addEventListener('input', () => { touched = true; });
    return { reset: () => { touched = false; } };
}

const t  = @json(__('settings.services'));
const tc = @json(__('common'));
let typesCache = [];
let attrsTypeId = null;     // тип, чьи атрибуты открыты
let attrsTypeName = '';

const typeModal  = new bootstrap.Modal(byId('modal-type'));
const attrsModal = new bootstrap.Modal(byId('modal-attributes'));
const attrModal  = new bootstrap.Modal(byId('modal-attr'));
const deleteBlockedModal = new bootstrap.Modal(byId('modal-delete-blocked'));

const typeCodeSuggest = wireCodeSuggest('type-name', 'type-code');
const attrCodeSuggest = wireCodeSuggest('attr-name', 'attr-code');

// ── Types list ────────────────────────────────────────────────────────────────
async function loadTypes() {
    const q = byId('type-search').value.trim();
    byId('types-loading').classList.remove('d-none');
    byId('types-table').classList.add('d-none');
    byId('types-empty').classList.add('d-none');
    try {
        const res = await api.get('/settings/service-types' + (q ? `?search=${encodeURIComponent(q)}` : ''));
        typesCache = res.data || [];
        renderTypes();
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
    } finally {
        byId('types-loading').classList.add('d-none');
    }
}

function renderTypes() {
    const tbody = byId('types-tbody');
    if (!typesCache.length) {
        byId('types-empty').classList.remove('d-none');
        byId('types-table').classList.add('d-none');
        return;
    }
    byId('types-table').classList.remove('d-none');
    const canDrag = !byId('type-search').value.trim();
    tbody.innerHTML = typesCache.map(c => `
        <tr draggable="${canDrag ? 'true' : 'false'}" data-id="${c.id}">
            <td class="text-center">
                <span class="drag-handle text-gray-400 fs-3 ${canDrag ? 'cursor-move' : 'opacity-25'}">⠿</span>
            </td>
            <td>
                <span class="fw-semibold text-gray-800">${escHtml(c.name)}</span>
                <span class="badge badge-light-primary ms-2">${escHtml(c.code)}</span>
            </td>
            <td class="text-center fw-semibold text-gray-700">${Number(c.default_markup_pct)}%</td>
            <td class="text-center">
                <label class="form-check form-switch form-check-custom justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" ${c.is_active ? 'checked' : ''}
                           onchange="toggleTypeFlag(${c.id},'is_active',this)">
                </label>
            </td>
            <td class="text-center">
                <label class="form-check form-switch form-check-custom justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" ${c.available_for_requests ? 'checked' : ''}
                           onchange="toggleTypeFlag(${c.id},'available_for_requests',this)">
                </label>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-light-primary py-1 px-3" onclick='openAttributes(${c.id}, ${JSON.stringify(c.name)})'>
                    <i class="ki-outline ki-element-11 fs-6 me-1"></i>${c.service_attributes_count}
                </button>
            </td>
            <td class="text-end">
                <button class="btn btn-icon btn-sm btn-light me-1" onclick='openTypeEdit(${JSON.stringify(c)})' title="${tc.edit}">
                    <i class="ki-outline ki-pencil fs-6"></i>
                </button>
                <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteType(${c.id})" title="${tc.delete}">
                    <i class="ki-outline ki-trash fs-6"></i>
                </button>
            </td>
        </tr>`).join('');
}

async function toggleTypeFlag(id, flag, el) {
    try {
        await api.patch(`/settings/service-types/${id}`, { [flag]: el.checked });
    } catch (err) {
        el.checked = !el.checked;
        showToast(err?.message ?? tc.unexpected_error, 'error');
    }
}

// ── Type add / edit ─────────────────────────────────────────────────────────
byId('btn-add-type').addEventListener('click', () => {
    byId('type-modal-title').textContent = t.type_add_title;
    byId('type-form').reset();
    byId('type-edit-id').value = '';
    byId('type-code').disabled = false;
    byId('type-is-active').checked = true;
    byId('type-for-requests').checked = true;
    typeCodeSuggest.reset();
    typeModal.show();
});

function openTypeEdit(c) {
    byId('type-modal-title').textContent = t.type_edit_title;
    byId('type-edit-id').value = c.id;
    byId('type-code').value = c.code;
    byId('type-code').disabled = true;             // код неизменяем
    byId('type-name').value = c.name;
    byId('type-markup').value = Number(c.default_markup_pct);
    byId('type-is-active').checked = c.is_active;
    byId('type-for-requests').checked = c.available_for_requests;
    typeModal.show();
}

byId('type-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn    = byId('type-save');
    const editId = byId('type-edit-id').value;
    const payload = {
        name:                   byId('type-name').value.trim(),
        default_markup_pct:     parseFloat(byId('type-markup').value) || 0,
        is_active:              byId('type-is-active').checked,
        available_for_requests: byId('type-for-requests').checked,
    };
    if (!payload.name) { showToast(t.name_required, 'error'); return; }

    btnLoading(btn, true);
    try {
        if (editId) {
            await api.patch(`/settings/service-types/${editId}`, payload);
        } else {
            const code = byId('type-code').value.trim().toLowerCase();
            if (!/^[a-z][a-z0-9_]*$/.test(code)) { showToast(t.code_invalid, 'error'); btnLoading(btn, false); return; }
            await api.post('/settings/service-types', { code, ...payload, sort_order: typesCache.length });
        }
        showToast(tc.saved ?? t.saved);
        typeModal.hide();
        await loadTypes();
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
    } finally {
        btnLoading(btn, false);
    }
});

async function deleteType(id) {
    if (!confirm(t.delete_type_confirm)) return;
    try {
        await api.delete(`/settings/service-types/${id}`);
        showToast(t.type_deleted);
        await loadTypes();
    } catch (err) {
        // Сообщение «тип используется» показываем модалкой, чтобы успеть прочитать.
        byId('delete-blocked-msg').textContent = err?.message ?? tc.unexpected_error;
        deleteBlockedModal.show();
    }
}

// ── Attributes ──────────────────────────────────────────────────────────────
async function openAttributes(typeId, name) {
    attrsTypeId = typeId;
    attrsTypeName = name;
    byId('attrs-type-name').textContent = name;
    attrsModal.show();
    await loadAttributes();
}

async function loadAttributes() {
    byId('attrs-loading').classList.remove('d-none');
    byId('attrs-list').innerHTML = '';
    byId('attrs-empty').classList.add('d-none');
    try {
        const res = await api.get(`/settings/service-types/${attrsTypeId}/attributes`);
        const list = res.data || [];
        if (!list.length) { byId('attrs-empty').classList.remove('d-none'); return; }
        byId('attrs-list').innerHTML = list.map(a => {
            const opts = (a.options || []).length ? ` · ${(a.options || []).length} ${t.options_count}` : '';
            return `
            <div class="attr-row d-flex align-items-center gap-3 px-3 py-2 border border-dashed border-gray-300 rounded-2"
                 draggable="true" data-id="${a.id}">
                <span class="drag-handle cursor-move text-gray-400 fs-4">⠿</span>
                <label class="form-check form-switch form-check-custom mb-0" title="${t.flag_active}">
                    <input class="form-check-input" type="checkbox" ${a.is_active ? 'checked' : ''}
                           onchange="toggleAttrFlag(${a.id},'is_active',this)">
                </label>
                <div class="flex-grow-1 min-w-0">
                    <span class="fw-semibold text-gray-800 fs-7">${escHtml(a.name)}</span>
                    <span class="badge badge-light-primary ms-1">${escHtml(a.code)}</span>
                    <span class="badge badge-light ms-1">${escHtml(t['it_' + a.input_type] || a.input_type)}</span>
                    ${a.is_required ? `<span class="badge badge-light-warning ms-1">${escHtml(t.required_badge)}</span>` : ''}
                    <span class="text-muted fs-8">${opts}</span>
                </div>
                <button class="btn btn-icon btn-sm btn-light me-1" onclick='openAttrEdit(${JSON.stringify(a)})' title="${tc.edit}">
                    <i class="ki-outline ki-pencil fs-6"></i>
                </button>
                <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteAttr(${a.id})" title="${tc.delete}">
                    <i class="ki-outline ki-trash fs-6"></i>
                </button>
            </div>`;
        }).join('');
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
    } finally {
        byId('attrs-loading').classList.add('d-none');
    }
}

async function toggleAttrFlag(id, flag, el) {
    try {
        await api.patch(`/settings/service-attributes/${id}`, { [flag]: el.checked });
    } catch (err) {
        el.checked = !el.checked;
        showToast(err?.message ?? tc.unexpected_error, 'error');
    }
}

// ── Attribute add / edit ────────────────────────────────────────────────────
byId('btn-add-attr').addEventListener('click', () => {
    byId('attr-modal-title').textContent = t.attr_add_title;
    byId('attr-form').reset();
    byId('attr-edit-id').value = '';
    byId('attr-code').disabled = false;
    byId('attr-input-type').value = 'select';
    byId('attr-is-active').checked = true;
    byId('attr-options-list').innerHTML = '';
    addOptionRow();
    syncOptionsVisibility();
    attrCodeSuggest.reset();
    attrModal.show();
});

function openAttrEdit(a) {
    byId('attr-modal-title').textContent = t.attr_edit_title;
    byId('attr-edit-id').value = a.id;
    byId('attr-code').value = a.code;
    byId('attr-code').disabled = true;             // код неизменяем
    byId('attr-name').value = a.name;
    byId('attr-input-type').value = a.input_type;
    byId('attr-is-required').checked = a.is_required;
    byId('attr-is-active').checked = a.is_active;
    byId('attr-options-list').innerHTML = '';
    (a.options || []).forEach(o => addOptionRow(o.value, o.name));
    if (!(a.options || []).length) addOptionRow();
    syncOptionsVisibility();
    attrModal.show();
}

function addOptionRow(value = '', name = '') {
    const row = document.createElement('div');
    row.className = 'opt-row d-flex gap-2';
    row.innerHTML = `
        <input type="text" class="form-control form-control-sm form-control-solid opt-value" placeholder="${escHtml(t.opt_value_ph)}" value="${escHtml(value)}">
        <input type="text" class="form-control form-control-sm form-control-solid opt-name" placeholder="${escHtml(t.opt_name_ph)}" value="${escHtml(name)}">
        <button type="button" class="btn btn-icon btn-sm btn-light-danger flex-shrink-0" onclick="this.closest('.opt-row').remove()">
            <i class="ki-outline ki-cross fs-5"></i>
        </button>`;
    byId('attr-options-list').appendChild(row);
}

byId('btn-add-option').addEventListener('click', () => addOptionRow());
byId('attr-input-type').addEventListener('change', syncOptionsVisibility);

// Опции нужны только для select/multiselect.
function syncOptionsVisibility() {
    const it = byId('attr-input-type').value;
    byId('attr-options-wrap').style.display = (it === 'select' || it === 'multiselect') ? '' : 'none';
}

function collectOptions() {
    return [...byId('attr-options-list').querySelectorAll('.opt-row')]
        .map(r => ({ value: r.querySelector('.opt-value').value.trim(), name: r.querySelector('.opt-name').value.trim() }))
        .filter(o => o.value !== '' && o.name !== '');
}

byId('attr-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn    = byId('attr-save');
    const editId = byId('attr-edit-id').value;
    const it     = byId('attr-input-type').value;
    const payload = {
        name:        byId('attr-name').value.trim(),
        input_type:  it,
        is_required: byId('attr-is-required').checked,
        is_active:   byId('attr-is-active').checked,
        options:     collectOptions(),
    };
    if (!payload.name) { showToast(t.name_required, 'error'); return; }
    if ((it === 'select' || it === 'multiselect') && !payload.options.length) { showToast(t.options_required, 'error'); return; }

    btnLoading(btn, true);
    try {
        if (editId) {
            await api.patch(`/settings/service-attributes/${editId}`, payload);
        } else {
            const code = byId('attr-code').value.trim().toLowerCase();
            if (!/^[a-z][a-z0-9_]*$/.test(code)) { showToast(t.code_invalid, 'error'); btnLoading(btn, false); return; }
            const sort = byId('attrs-list').querySelectorAll('.attr-row').length;
            await api.post(`/settings/service-types/${attrsTypeId}/attributes`, { code, ...payload, sort_order: sort });
        }
        showToast(tc.saved ?? t.saved);
        attrModal.hide();
        await loadAttributes();
        await loadTypes(); // обновить счётчик атрибутов
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
    } finally {
        btnLoading(btn, false);
    }
});

async function deleteAttr(id) {
    if (!confirm(t.delete_attr_confirm)) return;
    try {
        await api.delete(`/settings/service-attributes/${id}`);
        await loadAttributes();
        await loadTypes();
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
    }
}

// ── Drag-and-drop сортировка (нативная) ──────────────────────────────────────
function getDragAfterElement(container, sel, y) {
    const els = [...container.querySelectorAll(`${sel}:not(.is-dragging)`)];
    return els.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) return { offset, element: child };
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element || null;
}

function initSortable(container, itemSel, onReorder, canDrag) {
    if (container.dataset.sortable) return;
    container.dataset.sortable = '1';
    let dragEl = null;

    container.addEventListener('dragstart', (e) => {
        const item = e.target.closest(itemSel);
        if (!item || !container.contains(item) || (canDrag && !canDrag())) { e.preventDefault(); return; }
        dragEl = item;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
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

async function persistTypeOrder() {
    const ids = [...byId('types-tbody').querySelectorAll('tr')].map(tr => parseInt(tr.dataset.id, 10));
    typesCache.sort((a, b) => ids.indexOf(a.id) - ids.indexOf(b.id));
    try {
        await api.post('/settings/service-types/reorder', { ids });
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
        await loadTypes();
    }
}

async function persistAttrOrder() {
    const ids = [...byId('attrs-list').querySelectorAll('.attr-row')].map(el => parseInt(el.dataset.id, 10));
    try {
        await api.post('/settings/service-attributes/reorder', { ids });
    } catch (err) {
        showToast(err?.message ?? tc.unexpected_error, 'error');
        await loadAttributes();
    }
}

initSortable(byId('types-tbody'), 'tr', persistTypeOrder, () => !byId('type-search').value.trim());
initSortable(byId('attrs-list'), '.attr-row', persistAttrOrder);

// ── Boot ────────────────────────────────────────────────────────────────────
let searchTimer;
byId('type-search').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadTypes, 250);
});

loadTypes();
</script>
@endpush
