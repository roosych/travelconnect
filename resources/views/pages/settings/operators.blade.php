@extends('layouts.app')
@section('title', __('settings.operators.title'))
@section('page-title', __('settings.operators.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings') }}</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings_operators') }}</li>
@endsection

@section('content')

{{-- Модалка создания --}}
<div class="modal fade" id="modal-create-operator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h4 class="fw-bold">{{ __('settings.operators.create_title') }}</h4>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <form id="form-create-operator" novalidate>
                <div class="modal-body py-6 px-7">
                    <div class="alert alert-danger d-none mb-5" id="create-alert"></div>
                    <div class="mb-5">
                        <label class="form-label required fw-semibold">{{ __('settings.operators.name_label') }}</label>
                        <input type="text" name="name" id="create-name"
                               class="form-control form-control-solid" placeholder="{{ __('settings.operators.name_ph') }}" />
                        <div class="invalid-feedback" id="create-name-error"></div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required fw-semibold">{{ __('settings.operators.email_label') }}</label>
                        <input type="email" name="email" id="create-email"
                               class="form-control form-control-solid" placeholder="{{ __('settings.operators.email_ph') }}" />
                        <div class="invalid-feedback" id="create-email-error"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">{{ __('settings.operators.phone_label') }} <span class="text-muted">{{ __('settings.operators.phone_optional') }}</span></label>
                        <input type="text" name="phone" id="create-phone"
                               class="form-control form-control-solid js-phone" placeholder="{{ __('settings.operators.phone_ph') }}" />
                    </div>
                    <div class="text-muted fs-7 mt-3">
                        <i class="ki-outline ki-information-2 fs-6 me-1"></i>
                        {{ __('settings.operators.create_note') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="btn-create-submit">
                        <span class="indicator-label"><i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('settings.operators.create_btn') }}</span>
                        <span class="indicator-progress d-none">{{ __('settings.operators.creating') }} <span class="spinner-border spinner-border-sm ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Модалка показа пароля --}}
<div class="modal fade" id="modal-password" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered mw-450px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h4 class="fw-bold text-success">
                    <i class="ki-outline ki-shield-tick fs-2 text-success me-2"></i>{{ __('settings.operators.pw_title') }}
                </h4>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="text-gray-700 fs-6 mb-5">
                    {{ __('settings.operators.pw_body') }}
                    <strong>{{ __('settings.operators.pw_once') }}</strong>
                </div>
                <div class="mb-4">
                    <div class="text-muted fs-7 mb-1">{{ __('settings.operators.pw_email') }}</div>
                    <div class="fw-semibold fs-5 text-gray-800" id="pw-email"></div>
                </div>
                <div class="mb-5">
                    <div class="text-muted fs-7 mb-1">{{ __('settings.operators.pw_password') }}</div>
                    <div class="d-flex align-items-center gap-3">
                        <code class="fs-4 fw-bold text-primary bg-light-primary px-4 py-2 rounded flex-grow-1 text-center" id="pw-value"></code>
                        <button class="btn btn-icon btn-light btn-sm" id="btn-copy-password" title="{{ __('settings.operators.copy_tip') }}">
                            <i class="ki-outline ki-copy fs-4"></i>
                        </button>
                    </div>
                    <div class="text-success fs-8 mt-1 d-none" id="copy-ok">
                        <i class="ki-outline ki-check fs-7 me-1"></i>{{ __('settings.operators.copied') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('settings.operators.pw_ok') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Модалка редактирования --}}
<div class="modal fade" id="modal-edit-operator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h4 class="fw-bold">{{ __('settings.operators.edit_title') }}</h4>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <form id="form-edit-operator" novalidate>
                <input type="hidden" id="edit-id" />
                <div class="modal-body py-6 px-7">
                    <div class="alert alert-danger d-none mb-5" id="edit-alert"></div>
                    <div class="mb-5">
                        <label class="form-label required fw-semibold">{{ __('settings.operators.name_label') }}</label>
                        <input type="text" name="name" id="edit-name"
                               class="form-control form-control-solid" />
                        <div class="invalid-feedback" id="edit-name-error"></div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required fw-semibold">{{ __('settings.operators.email_label') }}</label>
                        <input type="email" name="email" id="edit-email"
                               class="form-control form-control-solid" />
                        <div class="invalid-feedback" id="edit-email-error"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">{{ __('settings.operators.phone_label') }} <span class="text-muted">{{ __('settings.operators.phone_optional') }}</span></label>
                        <input type="text" name="phone" id="edit-phone"
                               class="form-control form-control-solid js-phone" />
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="btn-edit-submit">
                        <span class="indicator-label"><i class="ki-outline ki-check fs-4 me-1"></i>{{ __('common.save') }}</span>
                        <span class="indicator-progress d-none">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Модалка сброса пароля --}}
<div class="modal fade" id="modal-reset-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">{{ __('settings.operators.reset_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-5 fs-6 text-gray-700" id="reset-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-warning" id="btn-reset-confirm">{{ __('settings.operators.reset_btn') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Модалка удаления --}}
<div class="modal fade" id="modal-delete-operator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">{{ __('settings.operators.delete_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-5 fs-6 text-gray-700" id="delete-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="btn-delete-confirm">{{ __('common.delete') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Основная карточка --}}
<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="operator-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('nav.search_placeholder') }}" />
            </div>
            <span id="operators-count" class="text-muted fs-7 ms-4 d-none"></span>
        </div>
        <div class="card-toolbar">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create-operator">
                <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('settings.operators.add_btn') }}
            </button>
        </div>
    </div>
    <div class="card-body pt-0">
        <div id="operators-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const t  = @json(__('settings.operators'));
const tc = @json(__('common'));
let allOperators = [];
let pendingDeleteId = null;
let pendingResetId  = null;
const ME_ID = {{ auth()->id() }};

(async function load() {
    try {
        const data = await api.get('/operators');
        allOperators = data.data ?? [];
        renderTable(allOperators);
        updateCount(allOperators.length, allOperators.length);
    } catch {
        document.getElementById('operators-table-container').innerHTML =
            `<div class="alert alert-danger">${t.load_error}</div>`;
    }
})();

document.getElementById('operator-search').addEventListener('input', () => {
    const q = document.getElementById('operator-search').value.toLowerCase();
    const filtered = allOperators.filter(o =>
        o.name.toLowerCase().includes(q) || o.email.toLowerCase().includes(q)
    );
    renderTable(filtered);
    updateCount(filtered.length, allOperators.length);
});

function updateCount(shown, total) {
    const el = document.getElementById('operators-count');
    el.classList.remove('d-none');
    el.textContent = shown === total
        ? t.count_total.replace(':total', total)
        : t.count_of.replace(':shown', shown).replace(':total', total);
}

function renderTable(operators) {
    const container = document.getElementById('operators-table-container');

    if (!operators.length) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="ki-outline ki-people fs-3x text-gray-300 mb-4 d-block"></i>
                <div class="text-muted fs-6 mb-4">${t.empty}</div>
            </div>`;
        return;
    }

    const rows = operators.map(o => {
        const isSelf = o.id === ME_ID;
        const initials = o.name.trim().split(/\s+/).filter(Boolean)
            .slice(0, 2).map(w => w[0].toUpperCase()).join('');

        return `
        <tr>
            <td>
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-35px symbol-circle flex-shrink-0">
                        <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">${initials}</div>
                    </div>
                    <div>
                        <div class="fw-bold text-gray-800">
                            ${escHtml(o.name)}
                            ${isSelf ? `<span class="badge badge-light-primary fs-9 ms-2">${t.badge_self}</span>` : ''}
                        </div>
                        <div class="text-muted fs-7">${escHtml(o.email)}</div>
                    </div>
                </div>
            </td>
            <td class="text-muted fs-7">${o.phone ? escHtml(o.phone) : '—'}</td>
            <td class="text-muted fs-7">${formatDate(o.created_at)}</td>
            <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                    <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-warning"
                            title="${t.tip_reset}"
                            data-action="reset" data-id="${o.id}">
                        <i class="ki-outline ki-lock fs-4"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-primary"
                            title="${t.tip_edit}"
                            data-action="edit" data-id="${o.id}">
                        <i class="ki-outline ki-pencil fs-4"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-danger"
                            title="${isSelf ? t.tip_delete_self : t.tip_delete}"
                            data-action="delete" data-id="${o.id}"
                            ${isSelf ? 'disabled' : ''}>
                        <i class="ki-outline ki-trash fs-4"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <table class="table align-middle table-row-dashed fs-6 gy-4">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-250px">${t.col_operator}</th>
                    <th class="min-w-130px">${t.col_phone}</th>
                    <th class="min-w-120px">${t.col_added}</th>
                    <th class="text-end min-w-120px">${t.col_actions}</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">${rows}</tbody>
        </table>`;
}

// ── Event delegation для кнопок в таблице ──
document.getElementById('operators-table-container').addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-action]');
    if (!btn || btn.disabled) return;

    const action = btn.dataset.action;
    const id     = parseInt(btn.dataset.id, 10);
    const op     = allOperators.find(o => o.id === id);

    if (action === 'edit')   openEdit(id);
    if (action === 'reset')  confirmReset(id, op?.name ?? '');
    if (action === 'delete') confirmDelete(id, op?.name ?? '');
});

// ── Создание ──
document.getElementById('form-create-operator').addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors('create');

    const btn = document.getElementById('btn-create-submit');
    setLoading(btn, true);

    const res = await api.post('/operators', {
        name:  document.getElementById('create-name').value.trim(),
        email: document.getElementById('create-email').value.trim(),
        phone: document.getElementById('create-phone').value.trim() || null,
    });

    setLoading(btn, false);

    if (res?.success && res?.data?.id) {
        const op = res.data;

        bootstrap.Modal.getInstance(document.getElementById('modal-create-operator'))?.hide();
        document.getElementById('form-create-operator').reset();

        allOperators.unshift(op);
        renderTable(allOperators);
        updateCount(allOperators.length, allOperators.length);

        showPasswordModal(op.email, op.plain_password);
    } else {
        showFormErrors(res, 'create');
    }
});

function showPasswordModal(email, password) {
    document.getElementById('pw-email').textContent  = email;
    document.getElementById('pw-value').textContent  = password;
    document.getElementById('copy-ok').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modal-password')).show();
}

document.getElementById('btn-copy-password').addEventListener('click', () => {
    const val = document.getElementById('pw-value').textContent;
    navigator.clipboard.writeText(val).then(() => {
        document.getElementById('copy-ok').classList.remove('d-none');
    });
});

// ── Редактирование ──
function openEdit(id) {
    const op = allOperators.find(o => o.id === id);
    if (!op) return;

    document.getElementById('edit-id').value    = op.id;
    document.getElementById('edit-name').value  = op.name;
    document.getElementById('edit-email').value = op.email;
    window.setPhoneValue('#edit-phone', op.phone ?? '');
    clearErrors('edit');

    new bootstrap.Modal(document.getElementById('modal-edit-operator')).show();
}

document.getElementById('form-edit-operator').addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors('edit');

    const id  = parseInt(document.getElementById('edit-id').value);
    const btn = document.getElementById('btn-edit-submit');
    setLoading(btn, true);

    const res = await api.patch(`/operators/${id}`, {
        name:  document.getElementById('edit-name').value.trim(),
        email: document.getElementById('edit-email').value.trim(),
        phone: document.getElementById('edit-phone').value.trim() || null,
    });

    setLoading(btn, false);

    if (res?.success && res?.data) {
        bootstrap.Modal.getInstance(document.getElementById('modal-edit-operator'))?.hide();
        allOperators = allOperators.map(o => o.id === id ? { ...o, ...res.data } : o);
        renderTable(allOperators);
        showToast(t.updated);
    } else {
        showFormErrors(res, 'edit');
    }
});

// ── Сброс пароля ──
function confirmReset(id, name) {
    pendingResetId = id;
    document.getElementById('reset-body').innerHTML = t.reset_body.replace(':name', escHtml(name));
    new bootstrap.Modal(document.getElementById('modal-reset-confirm')).show();
}

document.getElementById('btn-reset-confirm').addEventListener('click', async () => {
    bootstrap.Modal.getInstance(document.getElementById('modal-reset-confirm'))?.hide();
    if (!pendingResetId) return;

    const res = await api.patch(`/operators/${pendingResetId}/reset-password`);
    const op  = allOperators.find(o => o.id === pendingResetId);
    pendingResetId = null;

    if (res?.success && res?.plain_password) {
        showPasswordModal(op?.email ?? '', res.plain_password);
    } else {
        showToast(res?.message ?? t.reset_error, 'error');
    }
});

// ── Удаление ──
function confirmDelete(id, name) {
    pendingDeleteId = id;
    document.getElementById('delete-body').innerHTML = t.delete_body.replace(':name', escHtml(name));
    new bootstrap.Modal(document.getElementById('modal-delete-operator')).show();
}

document.getElementById('btn-delete-confirm').addEventListener('click', async () => {
    bootstrap.Modal.getInstance(document.getElementById('modal-delete-operator'))?.hide();
    if (!pendingDeleteId) return;

    const id  = pendingDeleteId;
    pendingDeleteId = null;

    const res = await api.delete(`/operators/${id}`);

    if (res?.success) {
        allOperators = allOperators.filter(o => o.id !== id);
        renderTable(allOperators);
        updateCount(allOperators.length, allOperators.length);
        showToast(t.deleted);
    } else {
        showToast(res?.message ?? t.delete_error, 'error');
    }
});

// ── Вспомогательные функции ──
function setLoading(btn, loading) {
    btn.disabled = loading;
    btn.querySelector('.indicator-label').classList.toggle('d-none', loading);
    btn.querySelector('.indicator-progress').classList.toggle('d-none', !loading);
}

function clearErrors(prefix) {
    ['name', 'email'].forEach(field => {
        const el = document.getElementById(`${prefix}-${field}`);
        if (el) el.classList.remove('is-invalid');
        const errEl = document.getElementById(`${prefix}-${field}-error`);
        if (errEl) errEl.textContent = '';
    });
    const alertEl = document.getElementById(`${prefix}-alert`);
    if (alertEl) alertEl.classList.add('d-none');
}

function showFormErrors(res, prefix) {
    const alertEl = document.getElementById(`${prefix}-alert`);
    if (res?.errors) {
        ['name', 'email'].forEach(field => {
            if (res.errors[field]) {
                const el = document.getElementById(`${prefix}-${field}`);
                if (el) el.classList.add('is-invalid');
                const errEl = document.getElementById(`${prefix}-${field}-error`);
                if (errEl) errEl.textContent = res.errors[field][0];
            }
        });
        return;
    }
    if (alertEl) {
        alertEl.textContent = res?.message ?? tc.unexpected_error;
        alertEl.classList.remove('d-none');
    }
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
