@extends('layouts.app')
@section('title', __('settings.currencies.title'))
@section('page-title', __('settings.currencies.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings') }}</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">{{ __('nav.operator.settings_currencies') }}</li>
@endsection

@section('content')

<div class="row g-6">

    {{-- Currency list --}}
    <div class="col-lg-8">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <div class="card-title">
                    <i class="ki-outline ki-dollar fs-2x text-primary me-3"></i>
                    <div>
                        <h3 class="card-label fw-bold fs-4 mb-0">{{ __('settings.currencies.card_title') }}</h3>
                        <div class="text-muted fs-7" id="rates-updated-line">
                            {{ __('settings.currencies.subtitle') }}
                        </div>
                    </div>
                </div>
                <div class="card-toolbar gap-3">
                    <button id="btn-sync" class="btn btn-sm btn-light-primary">
                        <span class="spinner-border spinner-border-sm align-middle me-1 d-none"></span>
                        <i class="ki-outline ki-arrows-circle fs-4 me-1"></i>
                        {{ __('settings.currencies.btn_sync') }}
                    </button>
                    <button id="btn-add-open" class="btn btn-sm btn-primary">
                        <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('settings.currencies.btn_add') }}
                    </button>
                </div>
            </div>

            {{-- Filter chips + search --}}
            <div class="card-header border-0 align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="currency-chips"></div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-outline ki-magnifier fs-4 position-absolute ms-3"></i>
                        <input type="text" id="currency-search"
                               class="form-control form-control-sm form-control-solid w-200px ps-10"
                               placeholder="{{ __('settings.search_code_name') }}" />
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div id="currencies-loading" class="text-center py-10">
                    <span class="spinner-border spinner-border-sm text-primary me-2"></span>{{ __('common.loading') }}
                </div>
                <div id="currencies-table" class="d-none">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted fs-7 text-uppercase">
                                <th>{{ __('settings.currencies.col_code') }}</th>
                                <th>{{ __('settings.currencies.col_name') }}</th>
                                <th>{{ __('settings.currencies.col_rate') }}</th>
                                <th>{{ __('settings.currencies.col_updated') }}</th>
                                <th class="text-end">{{ __('settings.currencies.col_status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="currencies-tbody"></tbody>
                    </table>
                    <div id="currencies-empty" class="text-center py-10 d-none">
                        <i class="ki-outline ki-dollar fs-3x text-gray-300 mb-3 d-block"></i>
                        <span class="text-muted fs-6">{{ __('common.nothing_found') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info card --}}
    <div class="col-lg-4">
        <div class="card card-flush">
            <div class="card-header py-5">
                <div class="card-title">
                    <h4 class="fw-bold fs-5 mb-0">{{ __('settings.currencies.info_title') }}</h4>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-light-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px">
                            <span class="fw-bold text-primary fs-7">1</span>
                        </div>
                        <div class="text-muted fs-7">{{ __('settings.currencies.info_1') }}</div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-light-info d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px">
                            <span class="fw-bold text-info fs-7">2</span>
                        </div>
                        <div class="text-muted fs-7">{{ __('settings.currencies.info_2') }}</div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-light-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px">
                            <span class="fw-bold text-success fs-7">3</span>
                        </div>
                        <div class="text-muted fs-7">{{ __('settings.currencies.info_3') }}</div>
                    </div>
                </div>

                <div class="separator my-5"></div>

                <div class="bg-light rounded p-4">
                    <div class="fw-semibold text-gray-700 mb-2 fs-7">{{ __('settings.currencies.source_title') }}</div>
                    <div class="text-muted fs-7">
                        {{ __('settings.currencies.source_body') }}<br>
                        <span class="text-primary">cbar.az</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Add currency modal --}}
<div class="modal fade" id="modal-add-currency" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold fs-4">{{ __('settings.currencies.modal_title') }}</h2>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </button>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="add-form" novalidate>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">{{ __('settings.currencies.iso_label') }}</label>
                        <input type="text" id="new-code" class="form-control form-control-solid"
                               placeholder="USD" maxlength="3" autocomplete="off" style="text-transform:uppercase" />
                        <div class="text-muted fs-7 mt-1">{{ __('settings.currencies.iso_hint') }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">{{ __('settings.currencies.name_label') }} <span class="text-muted fw-normal">{{ __('settings.currencies.name_optional') }}</span></label>
                        <input type="text" id="new-name" class="form-control form-control-solid"
                               placeholder="{{ __('settings.currencies.name_ph') }}" maxlength="60" />
                        <div class="text-muted fs-8 mt-1">{{ __('settings.currencies.name_hint') }}</div>
                    </div>
                    <div class="alert alert-light-primary border border-primary border-dashed d-flex align-items-center mt-5 mb-0 py-3 px-4">
                        <i class="ki-outline ki-information-5 fs-3 text-primary me-3"></i>
                        <span class="fs-8 text-gray-700">{{ __('settings.currencies.add_note') }}</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="submit" form="add-form" id="btn-add-save" class="btn btn-primary btn-sm">
                    <span class="spinner-border spinner-border-sm align-middle me-1 d-none"></span>
                    <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('common.add') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const t  = @json(__('settings.currencies'));
const tc = @json(__('common'));
let currencies     = [];
let currentStatus  = '';   // '' | active | inactive
let currentSearch  = '';
const byId = id => document.getElementById(id);

// Show/hide spinner inside button without changing its text
function btnLoading(btn, on) {
    btn.disabled = on;
    const spinner = btn.querySelector('.spinner-border');
    const icon    = btn.querySelector('i.ki-outline');
    if (spinner) spinner.classList.toggle('d-none', !on);
    if (icon)    icon.classList.toggle('d-none', on);
}

async function loadCurrencies() {
    byId('currencies-loading').classList.remove('d-none');
    byId('currencies-table').classList.add('d-none');

    try {
        const data = await api.get('/settings/currencies');
        currencies = data.data ?? [];
        renderRatesUpdated();
        renderChips();
        applyFilters();
    } catch {
        showToast(t.load_error, 'error');
    } finally {
        byId('currencies-loading').classList.add('d-none');
        byId('currencies-table').classList.remove('d-none');
    }
}

function renderRatesUpdated() {
    const dates = currencies
        .filter(c => c.code !== 'AZN' && c.rates_updated_at)
        .map(c => new Date(c.rates_updated_at).getTime());
    const line = byId('rates-updated-line');
    if (!dates.length) {
        line.textContent = t.subtitle;
        return;
    }
    const last = new Date(Math.max(...dates));
    const fmt  = last.toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    line.innerHTML = `<i class="ki-outline ki-time fs-7 me-1"></i>${t.rates_updated} <span class="fw-semibold text-gray-700">${fmt}</span>`;
}

function renderChips() {
    const active   = currencies.filter(c => c.is_active).length;
    const inactive = currencies.length - active;
    const defs = [
        { key: '',         label: t.chip_all,      cls: 'secondary', n: currencies.length },
        { key: 'active',   label: t.chip_active,   cls: 'success',   n: active },
        { key: 'inactive', label: t.chip_inactive, cls: 'warning',   n: inactive },
    ];
    byId('currency-chips').innerHTML = defs.map(d => {
        const on  = d.key === currentStatus;
        const cls = on ? `badge-${d.cls}` : `badge-light-${d.cls}`;
        return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer" data-status="${d.key}">${d.label}: ${d.n}</span>`;
    }).join('');
    byId('currency-chips').querySelectorAll('[data-status]').forEach(el => {
        el.addEventListener('click', () => { currentStatus = el.dataset.status; renderChips(); applyFilters(); });
    });
}

function applyFilters() {
    const s = currentSearch.toLowerCase();
    const filtered = currencies.filter(c => {
        const matchStatus = !currentStatus
            || (currentStatus === 'active' && c.is_active)
            || (currentStatus === 'inactive' && !c.is_active);
        const matchSearch = !s
            || (c.code ?? '').toLowerCase().includes(s)
            || (c.name ?? '').toLowerCase().includes(s);
        return matchStatus && matchSearch;
    });
    renderTable(filtered);
}

byId('currency-search').addEventListener('input', function () {
    currentSearch = this.value.trim();
    applyFilters();
});

function renderTable(list) {
    const tbody = byId('currencies-tbody');
    const empty = byId('currencies-empty');
    tbody.innerHTML = '';

    empty.classList.toggle('d-none', list.length > 0);

    list.forEach(c => {
        const rateUpdated = c.rates_updated_at
            ? new Date(c.rates_updated_at).toLocaleDateString('ru-RU', {day:'2-digit', month:'2-digit', year:'numeric'})
            : '—';

        const isDefault = c.is_default;
        const isActive  = c.is_active;

        const switchCell = isDefault
            ? `<div class="d-inline-flex align-items-center gap-2">
                   <div class="form-check form-switch form-check-custom form-check-solid form-check-sm d-inline-flex">
                       <input class="form-check-input" type="checkbox" checked disabled
                              style="width:36px;height:20px;" title="${t.default_tip}" />
                   </div>
                   <span class="badge badge-light-success fs-8">${t.default_badge}</span>
               </div>`
            : `<div class="form-check form-switch form-check-custom form-check-solid form-check-sm d-inline-flex">
                   <input class="form-check-input currency-toggle" type="checkbox" ${isActive ? 'checked' : ''}
                          data-code="${c.code}" style="cursor:pointer;width:36px;height:20px;" />
               </div>`;

        const codeCell = isDefault
            ? `<span class="fw-bold fs-6 text-primary">${c.code}</span>
               <i class="ki-outline ki-star fs-7 text-primary ms-1" title="${t.base_tip}"></i>`
            : `<span class="fw-bold fs-6">${c.code}</span>`;

        tbody.insertAdjacentHTML('beforeend', `
            <tr class="${isDefault ? 'bg-light-primary bg-opacity-50' : ''}">
                <td>${codeCell}</td>
                <td><span class="text-gray-700">${currencyName(c.code, c.name)}</span></td>
                <td>
                    ${c.code === 'AZN'
                        ? `<span class="text-muted fs-7">${t.rate_base}</span>`
                        : `<span class="fw-semibold">${parseFloat(c.rate).toFixed(4)}</span> AZN`
                    }
                </td>
                <td><span class="text-muted fs-7">${rateUpdated}</span></td>
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        ${switchCell}
                    </div>
                </td>
            </tr>
        `);
    });

    tbody.querySelectorAll('.currency-toggle').forEach(el => {
        el.addEventListener('change', () => toggleActive(el.dataset.code, el));
    });
}

async function toggleActive(code, el) {
    el.disabled = true;
    try {
        await api.patch(`/settings/currencies/${code}/toggle-active`);
        await loadCurrencies();   // re-renders the table on success
        showToast(t.status_updated);
    } catch {
        showToast(t.status_error, 'error');
        el.checked = !el.checked; // revert the toggle
        el.disabled = false;
    }
}

// Sync rates
byId('btn-sync').addEventListener('click', async function () {
    btnLoading(this, true);
    try {
        const data = await api.post('/settings/currencies/sync-rates');
        if (!data?.success) {
            throw new Error(data?.message ?? t.sync_error);
        }
        showToast(t.sync_done.replace(':count', data.updated).replace(':date', data.date));
        await loadCurrencies();
    } catch (err) {
        showToast(err?.message ?? t.sync_error, 'error');
    } finally {
        btnLoading(this, false);
    }
});

// Add currency modal
const addModalEl = byId('modal-add-currency');
const addModal   = new bootstrap.Modal(addModalEl);

byId('btn-add-open').addEventListener('click', () => {
    byId('add-form').reset();
    addModal.show();
});
addModalEl.addEventListener('shown.bs.modal', () => byId('new-code').focus());

// Auto-uppercase the ISO code as the user types
byId('new-code').addEventListener('input', function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
});

byId('add-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn  = byId('btn-add-save');
    const code = byId('new-code').value.trim().toUpperCase();
    let   name = byId('new-name').value.trim();

    if (!code || code.length !== 3) {
        showToast(t.add_validation, 'error');
        return;
    }

    // Название необязательно: если пусто — берём каноничное (англ.) по коду как фолбэк в БД.
    if (!name) {
        try { name = new Intl.DisplayNames(['en'], { type: 'currency' }).of(code); } catch (e) {}
        if (!name || name.toUpperCase() === code) name = code;
    }

    btnLoading(btn, true);
    try {
        await api.post('/settings/currencies', { code, name });
        showToast(t.added.replace(':code', code));
        addModal.hide();
        this.reset();
        await loadCurrencies();
    } catch (err) {
        showToast(err?.message ?? t.add_error, 'error');
    } finally {
        btnLoading(btn, false);
    }
});

loadCurrencies();
</script>
@endpush
