@extends('layouts.app')

@section('title', __('agencies.breadcrumb_list'))
@section('page-title', __('agencies.breadcrumb_list'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('agencies.breadcrumb_list') }}</li>
@endsection

@section('toolbar-actions')
    <button class="btn btn-primary btn-sm" id="btn-open-create-agency">
        <i class="ki-outline ki-plus fs-2"></i> {{ __('agencies.index.add') }}
    </button>
@endsection

@section('content')

<div class="card card-flush">
    {{-- Quick-filter chips (activity counts) --}}
    <div class="card-header border-0 pt-6 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="agencies-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('common.loading') }}</span>
        </div>
    </div>

    {{-- Search + country + sort --}}
    <div class="card-header align-items-center py-5 gap-2 gap-md-5 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="agency-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('agencies.index.search_ph') }}" />
            </div>
        </div>
        <div class="card-toolbar d-flex align-items-center flex-nowrap gap-3">
            <select id="agency-country-filter" class="form-select form-select-solid w-175px flex-shrink-0">
                <option value="">{{ __('agencies.index.all_countries') }}</option>
            </select>
            <select id="agency-sort" class="form-select form-select-solid w-175px flex-shrink-0">
                <option value="">{{ __('agencies.index.sort.name_asc') }}</option>
                <option value="name_desc">{{ __('agencies.index.sort.name_desc') }}</option>
                <option value="bookings_desc">{{ __('agencies.index.sort.bookings') }}</option>
                <option value="requests_desc">{{ __('agencies.index.sort.requests') }}</option>
                <option value="newest">{{ __('agencies.index.sort.newest') }}</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="agencies-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

{{-- Quick-view drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="agency-drawer" style="width:460px;max-width:95vw">
    <div class="offcanvas-header border-bottom px-7 py-5">
        <h5 class="offcanvas-title fw-bold" id="ag-drawer-title">{{ __('agencies.index.drawer.default_title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body px-7 py-6" id="ag-drawer-body"></div>
    <div class="offcanvas-footer border-top px-7 py-4 d-flex gap-2" id="ag-drawer-footer"></div>
</div>

{{-- Create Agency Modal --}}
<div class="modal fade" id="modal-create-agency" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('agencies.index.add') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-create-agency">
                    @include('pages.agencies._form')
                    <div id="create-agency-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-agency" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label">{{ __('common.save') }}</span>
                    <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Credentials Modal (показывается один раз после создания) --}}
<div class="modal fade" id="modal-credentials" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('agencies.credentials.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="alert alert-warning d-flex align-items-center mb-6">
                    <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                    <span class="fs-7">{{ __('agencies.credentials.notice') }}</span>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted">{{ __('agencies.credentials.login') }}</label>
                    <div class="input-group">
                        <input type="text" id="cred-login" class="form-control form-control-solid" readonly />
                        <button class="btn btn-icon btn-light" type="button" onclick="copyCred('cred-login')" title="{{ __('agencies.credentials.copied') }}">
                            <i class="ki-outline ki-copy fs-3"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold text-muted">{{ __('agencies.credentials.password') }}</label>
                    <div class="input-group">
                        <input type="text" id="cred-password" class="form-control form-control-solid fw-bold" readonly />
                        <button class="btn btn-icon btn-light" type="button" onclick="copyCred('cred-password')" title="{{ __('agencies.credentials.copied') }}">
                            <i class="ki-outline ki-copy fs-3"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('agencies.credentials.done') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Agency Modal --}}
<div class="modal fade" id="modal-edit-agency" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('agencies.edit_modal.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-edit-agency">
                    <input type="hidden" id="edit-agency-id" />
                    @include('pages.agencies._form')
                    <div id="edit-agency-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-update-agency" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label">{{ __('agencies.edit_modal.submit') }}</span>
                    <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const t  = @json(__('agencies'));
    const tc = @json(__('common'));

    let allAgencies   = [];   // current page (drawer / edit read from this)
    let currentPage   = 1;
    let currentSearch = '';
    let currentCountry= '';
    let currentFilter = '';   // '' | with_bookings | with_requests | dormant
    let currentSort   = '';

    async function loadAgencies(page = 1) {
        currentPage = page;
        const container = document.getElementById('agencies-table-container');
        container.innerHTML = `<div class="text-center py-10"><span class="spinner-border text-primary"></span></div>`;

        const params = new URLSearchParams({ page, per_page: 20 });
        if (currentSearch)  params.set('search', currentSearch);
        if (currentCountry) params.set('country', currentCountry);
        if (currentFilter)  params.set('filter', currentFilter);
        if (currentSort)    params.set('sort', currentSort);

        try {
            const data = await api.get(`/agencies?${params}`);
            allAgencies = data.data ?? [];
            renderChips(data.meta);
            renderTable(allAgencies, data.meta);
        } catch(e) {
            container.innerHTML =
                `<div class="alert alert-danger">${t.index.load_error}</div>`;
        }
    }

    loadAgencies();
    populateCountryFilter();

    let _searchTimer;
    document.getElementById('agency-search').addEventListener('input', function () {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(() => { currentSearch = this.value.trim(); loadAgencies(1); }, 300);
    });
    document.getElementById('agency-country-filter').addEventListener('change', function () {
        currentCountry = this.value; loadAgencies(1);
    });
    document.getElementById('agency-sort').addEventListener('change', function () {
        currentSort = this.value; loadAgencies(1);
    });

    function setFilter(filter) {
        currentFilter = filter || '';
        loadAgencies(1);
    }

    function renderChips(meta) {
        const c = meta?.counts ?? {};
        const ch = t.index.chips;
        const defs = [
            { key: '',              label: ch.all,           cls: 'secondary', n: c.all ?? 0 },
            { key: 'with_bookings', label: ch.with_bookings, cls: 'success',   n: c.with_bookings ?? 0 },
            { key: 'with_requests', label: ch.with_requests, cls: 'primary',   n: c.with_requests ?? 0 },
            { key: 'dormant',       label: ch.dormant,       cls: 'warning',   n: c.dormant ?? 0 },
        ];
        const chips = defs.map(d => {
            const active = d.key === currentFilter;
            const cls = active ? `badge-${d.cls}` : `badge-light-${d.cls}`;
            return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                          onclick="setFilter('${d.key}')">${d.label}: ${d.n}</span>`;
        }).join('');
        document.getElementById('agencies-chips').innerHTML = chips;
    }

    // Country filter lists only countries actually in use (one full-list call).
    async function populateCountryFilter() {
        try {
            const data = await api.get('/agencies');
            const codes = [...new Set((data.data ?? []).map(a => a.country).filter(Boolean))];
            const sel = document.getElementById('agency-country-filter');
            const render = () => {
                const sorted = [...codes].sort((a, b) =>
                    String(countryLabel(a) ?? a).localeCompare(String(countryLabel(b) ?? b), window.APP_LOCALE || 'ru'));
                sel.innerHTML = `<option value="">${t.index.all_countries}</option>` +
                    sorted.map(code => `<option value="${escHtml(code)}" ${code === currentCountry ? 'selected' : ''}>${escHtml(countryLabel(code) ?? code)}</option>`).join('');
            };
            render();
            // Re-render once country names are loaded so labels are localized.
            window._renderCountryFilter = render;
        } catch (e) { /* leave default */ }
    }

    function renderTable(agencies, meta) {
        const container = document.getElementById('agencies-table-container');

        if (!agencies.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-people fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.index.empty}</span>
                </div>`;
            return;
        }

        const rows = agencies.map(a => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${avatarHtml(a.name, a.avatar_url, 40)}
                        <div>
                            <a href="{{ url('/admin/agencies') }}/${a.id}" class="text-gray-800 text-hover-primary fw-bold d-block">${escHtml(a.name)}</a>
                            ${a.country ? `<span class="text-muted fs-7">${escHtml(countryLabel(a.country))}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td>
                    ${a.email ? `<a href="mailto:${escHtml(a.email)}" class="text-muted text-hover-primary fs-7 d-block">${escHtml(a.email)}</a>` : '<span class="text-muted">—</span>'}
                    ${a.phone ? `<span class="text-muted fs-7">${escHtml(a.phone)}</span>` : ''}
                </td>
                <td class="text-center">
                    <a href="{{ url('/admin/agencies') }}/${a.id}#tab-requests" class="fw-bold text-gray-800 text-hover-primary">${a.requests_count ?? 0}</a>
                </td>
                <td class="text-center fw-semibold text-gray-800">${a.bookings_count ?? 0}</td>
                <td class="text-center fw-semibold text-gray-800">${a.members_count ?? 0}</td>
                <td class="text-muted fs-7">${a.created_at ? formatDate(a.created_at) : '—'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-primary me-1" title="${t.index.quick_view}"
                            onclick="openAgencyDrawer(${a.id})">
                        <i class="ki-outline ki-eye fs-4"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-warning me-1" title="${tc.edit}"
                            onclick="openEditAgency(${JSON.stringify(a).replace(/"/g,'&quot;')})">
                        <i class="ki-outline ki-pencil fs-4"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-danger" title="${tc.delete}"
                            onclick="deleteAgency(${a.id})">
                        <i class="ki-outline ki-trash fs-4"></i>
                    </button>
                </td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-220px">${t.index.cols.agency}</th>
                        <th class="min-w-180px">${t.index.cols.contacts}</th>
                        <th class="min-w-80px text-center">${t.index.cols.requests}</th>
                        <th class="min-w-80px text-center">${t.index.cols.bookings}</th>
                        <th class="min-w-80px text-center">${t.index.cols.members}</th>
                        <th class="min-w-100px">${t.index.cols.registered}</th>
                        <th class="min-w-100px text-end">${t.index.cols.actions}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>
            ${meta && meta.last_page > 1 ? renderPagination(meta) : ''}`;

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
                <a class="page-link" href="#" onclick="event.preventDefault();loadAgencies(${p})">${p}</a>
            </li>`;
        }).join('');

        return `
        <div class="d-flex justify-content-between align-items-center pt-4 px-1">
            <div class="text-muted fs-7">${t.index.pagination.replace(':from', from).replace(':to', to).replace(':total', total)}</div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadAgencies(${cur - 1})"><i class="ki-outline ki-arrow-left fs-7"></i></a>
                </li>
                ${items}
                <li class="page-item ${cur === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault();loadAgencies(${cur + 1})"><i class="ki-outline ki-arrow-right fs-7"></i></a>
                </li>
            </ul>
        </div>`;
    }

    // ---- Quick-view drawer ----
    function openAgencyDrawer(id) {
        const a = allAgencies.find(x => x.id === id);
        if (!a) return;

        document.getElementById('ag-drawer-title').textContent = a.name ?? t.index.drawer.default_title;

        const stats = `
        <div class="row g-3 mb-5">
            <div class="col-4">
                <div class="bg-light-primary rounded p-3 text-center">
                    <div class="fw-bolder fs-3 text-primary">${a.requests_count ?? 0}</div>
                    <div class="text-muted fs-8">${t.index.drawer.stat_requests}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-success rounded p-3 text-center">
                    <div class="fw-bolder fs-3 text-success">${a.bookings_count ?? 0}</div>
                    <div class="text-muted fs-8">${t.index.drawer.stat_bookings}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light-info rounded p-3 text-center">
                    <div class="fw-bolder fs-3 text-info">${a.members_count ?? 0}</div>
                    <div class="text-muted fs-8">${t.index.drawer.stat_members}</div>
                </div>
            </div>
        </div>`;

        const contactRow = (icon, value, href) => value
            ? `<div class="d-flex align-items-center gap-2 fs-7 mb-2">
                   <i class="ki-outline ${icon} fs-5 text-gray-400"></i>
                   ${href ? `<a href="${href}" class="text-gray-800 text-hover-primary">${escHtml(value)}</a>` : `<span class="text-gray-800">${escHtml(value)}</span>`}
               </div>`
            : '';

        document.getElementById('ag-drawer-body').innerHTML = `
            <div class="d-flex align-items-center gap-3 mb-5">
                ${avatarHtml(a.name, a.avatar_url, 50)}
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        ${a.currency_code ? `<span class="badge badge-light-secondary">${escHtml(a.currency_code)}</span>` : ''}
                        ${a.country ? `<span class="text-muted fs-7">${escHtml(countryLabel(a.country) ?? a.country)}</span>` : ''}
                    </div>
                    <div class="text-muted fs-8 mt-1">${t.index.drawer.since.replace(':date', a.created_at ? formatDate(a.created_at) : '—')}</div>
                </div>
            </div>

            ${stats}

            <div class="text-muted fs-8 fw-bold text-uppercase mb-2">${t.index.drawer.contacts}</div>
            ${contactRow('ki-sms', a.email, a.email ? `mailto:${a.email}` : null)}
            ${contactRow('ki-phone', a.phone, a.phone ? `tel:${a.phone}` : null)}
            ${(!a.email && !a.phone) ? `<div class="text-muted fs-7 mb-2">${t.index.drawer.not_specified}</div>` : ''}`;

        document.getElementById('ag-drawer-footer').innerHTML = `
            <a href="{{ url('/admin/agencies') }}/${a.id}" class="btn btn-light-primary btn-sm flex-fill">
                <i class="ki-outline ki-arrow-right fs-5 me-1"></i>${t.index.drawer.open_card}
            </a>
            <button class="btn btn-light btn-sm flex-fill" onclick="openEditAgencyById(${a.id})">
                <i class="ki-outline ki-pencil fs-5 me-1"></i>${tc.edit}
            </button>`;

        bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('agency-drawer')).show();
    }

    function openEditAgencyById(id) {
        const a = allAgencies.find(x => x.id === id);
        if (!a) return;
        bootstrap.Offcanvas.getInstance(document.getElementById('agency-drawer'))?.hide();
        openEditAgency(a);
    }

    // ---- Create ----
    document.getElementById('btn-open-create-agency').addEventListener('click', () => {
        const form = document.getElementById('form-create-agency');
        form.reset();
        populateCurrencySelect(form.querySelector('[name="currency_code"]'), 'AZN');
        populateCountrySelect(form.querySelector('[name="country"]'), '');
        document.getElementById('create-agency-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-create-agency')).show();
    });

    document.getElementById('btn-save-agency').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-create-agency');
        const errorEl = document.getElementById('create-agency-error');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            country: fd.get('country') || null,
            currency_code: fd.get('currency_code') || null,
        };

        btnLoading(btn, true);
        errorEl.classList.add('d-none');

        try {
            const res = await api.post('/agencies', payload);
            const created = res.data ?? res;
            if (created?.id) {
                // Реквизиты показываем после полного закрытия формы — иначе бэкдропы наслаиваются.
                const createEl = document.getElementById('modal-create-agency');
                if (created.generated_password) {
                    createEl.addEventListener('hidden.bs.modal',
                        () => showCredentials(created.email, created.generated_password), { once: true });
                }
                bootstrap.Modal.getInstance(createEl).hide();
                showToast(t.index.created);
                currentSort = 'newest';
                document.getElementById('agency-sort').value = 'newest';
                await loadAgencies(1);
            } else {
                showFormError(errorEl, res);
            }
        } catch (err) {
            showFormError(errorEl, err?.data ?? { message: t.index.error_generic });
        } finally {
            btnLoading(btn, false);
        }
    });

    // ---- Edit ----
    function openEditAgency(a) {
        const form = document.getElementById('form-edit-agency');
        document.getElementById('edit-agency-id').value = a.id;
        form.querySelector('[name="name"]').value = a.name ?? '';
        form.querySelector('[name="email"]').value = a.email ?? '';
        window.setPhoneValue(form.querySelector('[name="phone"]'), a.phone ?? '');
        populateCountrySelect(form.querySelector('[name="country"]'), a.country ?? '');
        populateCurrencySelect(form.querySelector('[name="currency_code"]'), a.currency_code ?? '');
        document.getElementById('edit-agency-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-edit-agency')).show();
    }

    document.getElementById('btn-update-agency').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-edit-agency');
        const errorEl = document.getElementById('edit-agency-error');
        const id = document.getElementById('edit-agency-id').value;

        if (!form.checkValidity()) { form.reportValidity(); return; }

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            country: fd.get('country') || null,
            currency_code: fd.get('currency_code') || null,
        };

        btnLoading(btn, true);
        errorEl.classList.add('d-none');

        try {
            const res = await api.patch(`/agencies/${id}`, payload);
            if (res.data?.id ?? res.id) {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-agency')).hide();
                showToast(t.edit_modal.updated);
                await loadAgencies(currentPage);
            } else {
                showFormError(errorEl, res);
            }
        } catch (err) {
            showFormError(errorEl, err?.data ?? { message: t.index.error_generic });
        } finally {
            btnLoading(btn, false);
        }
    });

    // ---- Delete ----
    async function deleteAgency(id) {
        if (!confirm(t.index.delete_confirm)) return;
        await api.delete(`/agencies/${id}`);
        showToast(t.delete.done);
        await loadAgencies(currentPage);
    }

    // ---- Helpers ----
    // Реквизиты владельца: показываем один раз после создания (пароль виден только тут).
    function showCredentials(login, password) {
        if (!password) return;
        document.getElementById('cred-login').value    = login ?? '';
        document.getElementById('cred-password').value = password;
        new bootstrap.Modal(document.getElementById('modal-credentials')).show();
    }

    function copyCred(id) {
        const input = document.getElementById(id);
        navigator.clipboard?.writeText(input.value)
            .then(() => showToast(t.credentials.copied))
            .catch(() => { input.select(); document.execCommand('copy'); });
    }

    function showFormError(el, res) {
        const errors = res.errors ? Object.values(res.errors).flat().join(' ') : null;
        el.textContent = errors ?? res.message ?? t.index.error_generic;
        el.classList.remove('d-none');
    }

    const AVATAR_COLORS = [
        ['bg-light-primary','text-primary'],['bg-light-success','text-success'],
        ['bg-light-info','text-info'],['bg-light-warning','text-warning'],
        ['bg-light-danger','text-danger'],['bg-light-dark','text-dark'],
    ];
    function avatarInitials(name) {
        const w = (name ?? '').trim().split(/\s+/).filter(Boolean);
        return w.length >= 2 ? (w[0][0]+w[1][0]).toUpperCase() : (w[0] ?? '?').slice(0,2).toUpperCase();
    }
    function avatarColor(name) {
        let h = 0; for (let i=0;i<(name??'').length;i++) h=(name.charCodeAt(i)+((h<<5)-h))|0;
        return AVATAR_COLORS[Math.abs(h)%AVATAR_COLORS.length];
    }
    function avatarHtml(name, avatarUrl, size=40) {
        if (avatarUrl) {
            return `<div class="symbol symbol-${size}px me-3 flex-shrink-0">
                        <img src="${escHtml(avatarUrl)}" alt="${escHtml(name ?? '')}" class="rounded-circle object-fit-cover" style="width:${size}px;height:${size}px;" />
                    </div>`;
        }
        const [bg, text] = avatarColor(name ?? '');
        return `<div class="symbol symbol-${size}px me-3 flex-shrink-0">
                    <div class="symbol-label rounded-circle ${bg} fw-bold fs-6 ${text}">${avatarInitials(name)}</div>
                </div>`;
    }

    function formatDate(d) {
        if (!d) return '—';
        const dt = new Date(d);
        const dd = String(dt.getDate()).padStart(2, '0');
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        const yyyy = dt.getFullYear();
        return `${dd}.${mm}.${yyyy}`;
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    let COUNTRY_NAMES = {};
    fetch('/data/countries.json')
        .then(r => r.json())
        .then(list => {
            COUNTRY_NAMES = Object.fromEntries(list.map(c => [c.code, c.name]));
            document.querySelectorAll('.js-country-select').forEach(sel => populateCountrySelect(sel, sel.value));
            window._renderCountryFilter?.();
        });

    let ACTIVE_CURRENCIES = [];
    api.get('/settings/currencies/active')
        .then(res => {
            ACTIVE_CURRENCIES = res.data ?? [];
            document.querySelectorAll('.js-currency-select').forEach(sel => populateCurrencySelect(sel, sel.value));
        })
        .catch(() => {});

    // Состав — коды из countries.json; названия/сортировка/поиск — общий хелпер
    // (Intl, локаль) + select2 с поиском и автозакрытием (dropdownParent = модалка).
    function populateCountrySelect(sel, selectedCode = '') {
        const current = selectedCode || sel.value;
        fillCountrySelect(sel, Object.keys(COUNTRY_NAMES), current, { emptyLabel: t.select_none });
        initCountrySelect(sel, { placeholder: t.select_none, allowClear: true });
    }

    function countryLabel(code) {
        if (!code) return null;
        return countryName(code) || COUNTRY_NAMES[code] || code;
    }

    function populateCurrencySelect(sel, selectedCode = '') {
        const current = selectedCode || sel.value;
        sel.innerHTML = `<option value="">${t.select_none}</option>` +
            ACTIVE_CURRENCIES.map(c =>
                `<option value="${c.code}" ${c.code === current ? 'selected' : ''}>${escHtml(c.code)} — ${escHtml(currencyName(c.code, c.name))}</option>`
            ).join('');
    }
</script>
@endpush
