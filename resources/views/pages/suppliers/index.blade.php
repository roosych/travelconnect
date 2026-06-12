@extends('layouts.app')

@section('title', __('suppliers.title'))
@section('page-title', __('suppliers.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('suppliers.breadcrumb_list') }}</li>
@endsection

@section('toolbar-actions')
    <button class="btn btn-primary btn-sm" id="btn-open-create-supplier">
        <i class="ki-outline ki-plus fs-2"></i> {{ __('suppliers.index.add') }}
    </button>
@endsection

@section('content')

<div class="card card-flush">
    {{-- Quick-filter chips (status counts) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="suppliers-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + service + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="supplier-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('suppliers.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="supplier-service-filter" class="form-select form-select-solid w-175px flex-shrink-0">
                <option value="">{{ __('suppliers.index.all_services') }}</option>
                <option value="accommodation">{{ __('common.services.accommodation') }}</option>
                <option value="transport">{{ __('common.services.transport') }}</option>
                <option value="guide">{{ __('common.services.guide') }}</option>
                <option value="activity">{{ __('common.services.activity') }}</option>
                <option value="other">{{ __('common.services.other') }}</option>
            </select>
            <select id="supplier-sort" class="form-select form-select-solid w-175px flex-shrink-0">
                <option value="">{{ __('suppliers.index.sort.name_asc') }}</option>
                <option value="name_desc">{{ __('suppliers.index.sort.name_desc') }}</option>
                <option value="offers_desc">{{ __('suppliers.index.sort.offers') }}</option>
                <option value="newest">{{ __('suppliers.index.sort.newest') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="suppliers-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

{{-- Create Supplier Modal --}}
<div class="modal fade" id="modal-create-supplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('suppliers.create_title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-create-supplier">
                    @include('pages.suppliers._form')
                    <div id="create-supplier-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-supplier" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label">{{ __('common.save') }}</span>
                    <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Supplier Modal --}}
<div class="modal fade" id="modal-edit-supplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('suppliers.edit_title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-edit-supplier">
                    <input type="hidden" id="edit-supplier-id" />
                    @include('pages.suppliers._form', ['prefix' => 'edit-'])
                    <div id="edit-supplier-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-update-supplier" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label">{{ __('suppliers.edit_submit') }}</span>
                    <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="modal-delete-supplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">{{ __('suppliers.delete.q') }}</h5>
            </div>
            <div class="modal-body text-muted py-3">
                {{ __('suppliers.delete.body') }}
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-confirm-delete" class="btn btn-danger">{{ __('suppliers.delete.btn') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const t  = @json(__('suppliers'));
    const tc = @json(__('common'));

    const SERVICE_TYPES = Object.keys(window.SERVICE_LABELS);

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary' }]));
    let allSuppliers   = [];   // current page (drawer / edit read from this)
    let currentPage    = 1;
    let currentSearch  = '';
    let currentService = '';
    let currentFilter  = '';   // '' | active | inactive | portal
    let currentSort    = '';

    async function loadSuppliers(page = 1) {
        currentPage = page;
        const container = document.getElementById('suppliers-table-container');
        container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        const params = new URLSearchParams({ page, per_page: 20 });
        if (currentSearch)  params.set('search', currentSearch);
        if (currentService) params.set('service_type', currentService);
        if (currentFilter)  params.set('filter', currentFilter);
        if (currentSort)    params.set('sort', currentSort);

        try {
            const data = await api.get(`/suppliers?${params}`);
            allSuppliers = data.data ?? [];
            renderChips(data.meta);
            renderTable(allSuppliers, data.meta);
        } catch(e) {
            container.innerHTML =
                `<div class="alert alert-danger">${t.index.load_error}</div>`;
        }
    }

    loadSuppliers();

    let _searchTimer;
    document.getElementById('supplier-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadSuppliers(1); }, 300);
    });
    document.getElementById('supplier-service-filter').addEventListener('change', function () {
        currentService = this.value; loadSuppliers(1);
    });
    document.getElementById('supplier-sort').addEventListener('change', function () {
        currentSort = this.value; loadSuppliers(1);
    });

    function setFilter(filter) {
        currentFilter = filter || '';
        loadSuppliers(1);
    }

    function renderChips(meta) {
        const c = meta?.counts ?? {};
        const ch = t.index.chips;
        const defs = [
            { key: '',         label: ch.all,      cls: 'secondary', n: c.all ?? 0 },
            { key: 'active',   label: ch.active,   cls: 'success',   n: c.active ?? 0 },
            { key: 'inactive', label: ch.inactive, cls: 'danger',    n: c.inactive ?? 0 },
            { key: 'portal',   label: ch.portal,   cls: 'primary',   n: c.portal ?? 0 },
        ];
        const chips = defs.map(d => {
            const active = d.key === currentFilter;
            const cls = active ? `badge-${d.cls}` : `badge-light-${d.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${d.key}')">${d.label}: ${d.n}</span>`;
        }).join('');
        document.getElementById('suppliers-chips').innerHTML = chips;
    }

    function renderTable(suppliers, meta) {
        const container = document.getElementById('suppliers-table-container');

        if (!suppliers.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-truck fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.index.empty}</span>
                </div>`;
            return;
        }

        const rows = suppliers.map(s => {
            const types = (s.service_types ?? []).map(t => {
                const meta = SERVICE_META[t] ?? { label: t, color: 'secondary' };
                return `<span class="badge badge-light-${meta.color} me-1">${escHtml(meta.label)}</span>`;
            }).join('') || '<span class="text-muted">—</span>';

            const activeSwitch = `<div class="form-check form-switch form-check-custom form-check-solid form-check-sm d-inline-flex">
                <input class="form-check-input" type="checkbox" ${s.is_active ? 'checked' : ''}
                       onchange="toggleActive(${s.id})" style="cursor:pointer;width:36px;height:20px;" />
            </div>`;

            return `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${avatarHtml(s)}
                        <div>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ url('/admin/suppliers') }}/${s.id}" class="text-gray-800 text-hover-primary fw-bold">${escHtml(s.name)}</a>
                                ${portalBadge(s)}
                                ${pausedBadge(s)}
                            </div>
                            <div class="text-muted fs-7">${escHtml(s.email ?? '')}</div>
                        </div>
                    </div>
                </td>
                <td>${countryCell(s.country)}</td>
                <td class="text-muted">${escHtml(s.phone ?? '—')}</td>
                <td>${types}</td>
                <td>${activeSwitch}</td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-icon btn-sm btn-light" title="${t.index.edit}"
                                onclick="openEditSupplier(${s.id})">
                            <i class="ki-outline ki-pencil fs-4"></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-sm btn-light-danger" title="${t.delete.btn}"
                                onclick="deleteSupplier(${s.id})">
                            <i class="ki-outline ki-trash fs-4"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">${t.index.cols.supplier}</th>
                        <th class="min-w-140px">${t.index.cols.country}</th>
                        <th class="min-w-100px">${t.index.cols.phone}</th>
                        <th class="min-w-180px">${t.index.cols.services}</th>
                        <th class="w-60px">${t.index.cols.status}</th>
                        <th class="min-w-100px text-end">${t.index.cols.actions}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>
            ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;

        container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    }

    function renderPagination(meta) {
        const { current_page: cur, last_page: last, per_page, total } = meta;
        const from = (cur - 1) * per_page + 1;
        const to   = Math.min(cur * per_page, total);

        const pages = [];
        for (let p = 1; p <= last; p++) {
            if (p === 1 || p === last || (p >= cur - 2 && p <= cur + 2)) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }

        const items = pages.map(p => {
            if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            return `<li class="page-item ${p === cur ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();loadSuppliers(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadSuppliers(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
                </li>
                ${items}
                <li class="page-item ${cur === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadSuppliers(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
                </li>
            </ul>
        </div>`;
    }

    // ---- Country cell (flag + name) ----
    const COUNTRY_NAMES = @json($countries->pluck('name', 'code'));

    function countryCell(code) {
        if (!code) return '<span class="text-muted">—</span>';
        const name = COUNTRY_NAMES[code] ?? code;
        return `<div class="d-flex align-items-center gap-2">
            <img src="/flags/${escHtml(String(code).toLowerCase())}.svg" alt=""
                 style="width:18px;height:13px;object-fit:cover;border-radius:2px;flex-shrink:0"
                 onerror="this.style.display='none'">
            <span class="text-gray-700">${escHtml(name)}</span>
        </div>`;
    }

    // ---- Currencies ----
    let ACTIVE_CURRENCIES = [];
    api.get('/settings/currencies/active')
        .then(res => {
            ACTIVE_CURRENCIES = res.data ?? [];
            document.querySelectorAll('.js-currency-select').forEach(sel => populateCurrencySelect(sel, sel.value));
        })
        .catch(() => {});

    function populateCurrencySelect(sel, selectedCode = '') {
        const current = selectedCode || sel.value;
        sel.innerHTML = `<option value="">${t.select_none}</option>` +
            ACTIVE_CURRENCIES.map(c =>
                `<option value="${escHtml(c.code)}" ${c.code === current ? 'selected' : ''}>${escHtml(c.code)} — ${escHtml(currencyName(c.code, c.name))}</option>`
            ).join('');
    }

    // Страна поставщика = страна направления (DB-справочник). Названия/сортировка/поиск —
    // общий хелпер (Intl, локаль) + select2 (поиск + автозакрытие). Если у поставщика
    // выставлена страна вне справочника — добавляем её код, чтобы не потерять значение.
    function populateSupplierCountry(sel, selected = '') {
        if (!sel) return;
        const codes = Object.keys(COUNTRY_NAMES);
        if (selected && !codes.includes(selected)) codes.push(selected);
        fillCountrySelect(sel, codes, selected, { emptyLabel: t.select_country });
        initCountrySelect(sel, { placeholder: t.select_country });
    }

    // ---- Create ----
    document.getElementById('btn-open-create-supplier').addEventListener('click', () => {
        const form = document.getElementById('form-create-supplier');
        form.reset();
        populateCurrencySelect(form.querySelector('[name="currency_code"]'), 'AZN');
        populateSupplierCountry(form.querySelector('[name="country"]'), '');
        document.getElementById('create-supplier-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-create-supplier')).show();
    });

    document.getElementById('btn-save-supplier').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-create-supplier');
        const errorEl = document.getElementById('create-supplier-error');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            country: fd.get('country') || null,
            currency_code: fd.get('currency_code') || null,
            description: fd.get('description') || null, website: fd.get('website') || null,
            service_types: [...form.querySelectorAll('input[name="service_types[]"]:checked')].map(el => el.value),
            is_active: fd.get('is_active') === '1',
        };

        btnLoading(btn, true);
        errorEl.classList.add('d-none');

        try {
            const res = await api.post('/suppliers', payload);
            if (res.data?.id ?? res.id) {
                bootstrap.Modal.getInstance(document.getElementById('modal-create-supplier')).hide();
                showToast(t.index.created);
                // Показать только что созданного сверху, а не в конце алфавитного списка.
                currentSort = 'newest';
                document.getElementById('supplier-sort').value = 'newest';
                await loadSuppliers(1);
            } else {
                showFormError(errorEl, res);
            }
        } catch (err) {
            showFormError(errorEl, err?.data ?? { message: t.index.create_error });
        } finally {
            btnLoading(btn, false);
        }
    });

    // ---- Edit ----
    function openEditSupplier(id) {
        const s = allSuppliers.find(x => x.id === id);
        if (!s) return;
        const form = document.getElementById('form-edit-supplier');
        document.getElementById('edit-supplier-id').value = s.id;
        form.querySelector('[name="name"]').value = s.name ?? '';
        form.querySelector('[name="email"]').value = s.email ?? '';
        window.setPhoneValue(form.querySelector('[name="phone"]'), s.phone ?? '');
        form.querySelector('[name="description"]').value = s.description ?? '';
        form.querySelector('[name="website"]').value = s.website ?? '';
        populateCurrencySelect(form.querySelector('[name="currency_code"]'), s.currency_code ?? '');

        // Страна (локализованный список + select2). Если страна поставщика вне
        // справочника направлений — хелпер добавит её код, чтобы не потерять значение.
        populateSupplierCountry(form.querySelector('[name="country"]'), s.country ?? '');

        const activeTypes = s.service_types ?? [];
        form.querySelectorAll('input[name="service_types[]"]').forEach(cb => {
            cb.checked = activeTypes.includes(cb.value);
        });

        const isActive = s.is_active ?? true;
        const activeRadio = form.querySelector(`input[name="is_active"][value="${isActive ? '1' : '0'}"]`);
        if (activeRadio) activeRadio.checked = true;

        document.getElementById('edit-supplier-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-edit-supplier')).show();
    }

    document.getElementById('btn-update-supplier').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-edit-supplier');
        const errorEl = document.getElementById('edit-supplier-error');
        const id = document.getElementById('edit-supplier-id').value;

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            country: fd.get('country') || null,
            currency_code: fd.get('currency_code') || null,
            description: fd.get('description') || null, website: fd.get('website') || null,
            service_types: [...form.querySelectorAll('input[name="service_types[]"]:checked')].map(el => el.value),
            is_active: fd.get('is_active') === '1',
        };

        btnLoading(btn, true);
        errorEl.classList.add('d-none');

        try {
            const res = await api.patch(`/suppliers/${id}`, payload);
            if (res.data?.id ?? res.id) {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-supplier')).hide();
                showToast(t.updated);
                await loadSuppliers(currentPage);
            } else {
                showFormError(errorEl, res);
            }
        } catch (err) {
            showFormError(errorEl, err?.data ?? { message: t.index.update_error });
        } finally {
            btnLoading(btn, false);
        }
    });

    // ---- Toggle Active ----
    async function toggleActive(id) {
        const res = await api.patch(`/suppliers/${id}/toggle-active`);
        if (res.is_active !== undefined) {
            showToast(res.is_active ? t.toggle.activated : t.toggle.deactivated);
            await loadSuppliers(currentPage);
        } else {
            showToast(res.message ?? t.toggle.error, 'error');
        }
    }

    // ---- Delete ----
    let pendingDeleteId = null;

    function deleteSupplier(id) {
        pendingDeleteId = id;
        new bootstrap.Modal(document.getElementById('modal-delete-supplier')).show();
    }

    document.getElementById('btn-confirm-delete').addEventListener('click', async function() {
        if (!pendingDeleteId) return;
        bootstrap.Modal.getInstance(document.getElementById('modal-delete-supplier')).hide();
        await api.delete(`/suppliers/${pendingDeleteId}`);
        pendingDeleteId = null;
        showToast(t.deleted);
        await loadSuppliers(currentPage);
    });

    // ---- Helpers ----
    function showFormError(el, res) {
        const errors = res.errors ? Object.values(res.errors).flat().join(' ') : null;
        el.textContent = errors ?? res.message ?? t.index.error_generic;
        el.classList.remove('d-none');
    }

    const AVATAR_COLORS = [
        ['bg-light-primary',  'text-primary'],
        ['bg-light-success',  'text-success'],
        ['bg-light-info',     'text-info'],
        ['bg-light-warning',  'text-warning'],
        ['bg-light-danger',   'text-danger'],
        ['bg-light-dark',     'text-dark'],
    ];

    function avatarInitials(name) {
        const words = (name ?? '').trim().split(/\s+/).filter(Boolean);
        if (words.length >= 2) return (words[0][0] + words[1][0]).toUpperCase();
        if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
        return '?';
    }

    function avatarColor(name) {
        let hash = 0;
        for (let i = 0; i < (name ?? '').length; i++) hash = (name.charCodeAt(i) + ((hash << 5) - hash)) | 0;
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }

    function avatarHtml(s, size = 40) {
        if (s.avatar_url) {
            return `<div class="symbol symbol-${size}px me-3 flex-shrink-0">
                <img src="${escHtml(s.avatar_url)}" alt="${escHtml(s.name ?? '')}"
                     style="width:${size}px;height:${size}px;object-fit:cover;border-radius:50%;" />
            </div>`;
        }
        const [bg, text] = avatarColor(s.name ?? '');
        return `<div class="symbol symbol-${size}px me-3 flex-shrink-0">
            <div class="symbol-label rounded-circle ${bg} fw-bold fs-6 ${text}">${avatarInitials(s.name)}</div>
        </div>`;
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Поставщик сам поставил получение запросов на паузу (accepting_requests=false).
    function pausedBadge(s) {
        if (s.accepting_requests !== false) return '';
        return `<span class="badge badge-light-warning fs-8" title="${t.paused_title}">
                    <i class="ki-outline ki-pause fs-8 me-1"></i>${t.paused}
                </span>`;
    }

    function portalBadge(s) {
        if (!s.uses_portal) return '';
        return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                     title="${t.portal_title}" style="flex-shrink:0;cursor:default" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${t.portal_title}">
                    <circle cx="12" cy="12" r="10" fill="#0095F6"/>
                    <path d="M7 12.5l3.5 3.5 6.5-7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
    }
</script>
@endpush
