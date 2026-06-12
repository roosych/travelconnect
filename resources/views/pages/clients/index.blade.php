@extends('layouts.app')

@section('title', 'Клиенты')
@section('page-title', 'Клиенты')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Клиенты</li>
@endsection

@section('toolbar-actions')
    <button class="btn btn-primary btn-sm" id="btn-open-create-client">
        <i class="ki-outline ki-plus fs-2"></i> Добавить клиента
    </button>
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="client-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Поиск клиентов..." />
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-3">
            <select id="client-agency-filter" class="form-select form-select-solid w-200px">
                <option value="">Все агентства</option>
            </select>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="clients-table-container">
            <div class="text-center py-10">
                <span class="spinner-border text-primary"></span>
            </div>
        </div>
    </div>
</div>

{{-- Create Client Modal --}}
<div class="modal fade" id="modal-create-client" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">Добавить клиента</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-create-client">
                    @include('pages.clients._form')
                    <div id="create-client-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                <button type="button" id="btn-save-client" class="btn btn-primary">
                    <span class="indicator-label">Сохранить клиента</span>
                    <span class="indicator-progress d-none">Сохранение... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Client Modal --}}
<div class="modal fade" id="modal-edit-client" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">Изменить клиента</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-edit-client">
                    <input type="hidden" id="edit-client-id" />
                    @include('pages.clients._form')
                    <div id="edit-client-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                <button type="button" id="btn-update-client" class="btn btn-primary">
                    <span class="indicator-label">Сохранить клиента</span>
                    <span class="indicator-progress d-none">Сохранение... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let allClients = [];
    let allAgencies = [];

    Promise.all([loadClients(), loadAgencies()]);

    async function loadClients() {
        try {
            const data = await api.get('/clients');
            allClients = data.data ?? data ?? [];
            applyFilters();
        } catch {
            document.getElementById('clients-table-container').innerHTML =
                `<div class="alert alert-danger">Не удалось загрузить клиентов. Обновите страницу.</div>`;
        }
    }

    async function loadAgencies() {
        try {
            const data = await api.get('/agencies');
            allAgencies = data.data ?? data ?? [];
            const sel = document.getElementById('client-agency-filter');
            allAgencies.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.company_name || a.name;
                sel.appendChild(opt);
            });

            // Populate agency dropdowns in forms
            document.querySelectorAll('select[name="agency_id"]').forEach(select => {
                // Keep "select agency" placeholder, add options
                allAgencies.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.textContent = a.company_name || a.name;
                    select.appendChild(opt);
                });
            });
        } catch { /* non-critical */ }
    }

    document.getElementById('client-search').addEventListener('input', applyFilters);
    document.getElementById('client-agency-filter').addEventListener('change', applyFilters);

    function applyFilters() {
        const search = document.getElementById('client-search').value.toLowerCase();
        const agencyId = document.getElementById('client-agency-filter').value;

        const filtered = allClients.filter(c =>
            (!search ||
                (c.name ?? '').toLowerCase().includes(search) ||
                (c.email ?? '').toLowerCase().includes(search) ||
                (c.passport_number ?? '').toLowerCase().includes(search) ||
                (c.phone ?? '').toLowerCase().includes(search)) &&
            (!agencyId || String(c.agency_id) === agencyId)
        );

        renderTable(filtered);
    }

    function renderTable(clients) {
        const container = document.getElementById('clients-table-container');

        if (!clients.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-profile-user fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">Клиенты не найдены.</span>
                </div>`;
            return;
        }

        const rows = clients.map(c => {
            const agency = allAgencies.find(a => a.id === c.agency_id);
            return `
            <tr>
                <td class="fw-bold text-gray-800">#${c.id}</td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        ${avatarHtml(c.name, 35)}
                        <div>
                            <div class="fw-semibold text-gray-800">${escHtml(c.name)}</div>
                            <div class="text-muted fs-7">${escHtml(c.email ?? '—')}</div>
                        </div>
                    </div>
                </td>
                <td class="text-muted">${escHtml(c.phone ?? '—')}</td>
                <td class="text-muted">
                    ${c.nationality ? `<span class="badge badge-light-secondary fs-8">${escHtml(c.nationality)}</span>` : '—'}
                </td>
                <td class="text-muted fs-7">${c.date_of_birth ?? '—'}</td>
                <td>
                    ${agency
                        ? `<a href="/admin/agencies/${agency.id}" class="fw-bold text-gray-800 text-hover-primary">${escHtml(agency.company_name ?? agency.name)}</a>
                           <div class="text-muted fs-7">${escHtml(countryName(agency.country))}</div>`
                        : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary"
                       data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        Действия <i class="ki-outline ki-down fs-5 ms-1"></i>
                    </a>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3"
                               onclick="openEditClient(${JSON.stringify(c).replace(/"/g,'&quot;')}); return false;">
                                <i class="ki-outline ki-pencil fs-6 me-2"></i>Изменить
                            </a>
                        </div>
                        <div class="separator my-1 opacity-75"></div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3 text-danger"
                               onclick="deleteClient(${c.id}); return false;">
                                <i class="ki-outline ki-trash fs-6 me-2"></i>Удалить
                            </a>
                        </div>
                    </div>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-50px">ID</th>
                        <th class="min-w-220px">Клиент</th>
                        <th class="min-w-110px">Телефон</th>
                        <th class="min-w-90px">Гражданство</th>
                        <th class="min-w-110px">Дата рождения</th>
                        <th class="min-w-160px">Агентство</th>
                        <th class="text-end min-w-100px">Действия</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;

        KTMenu.init();
    }

    // ---- Create ----
    document.getElementById('btn-open-create-client').addEventListener('click', () => {
        document.getElementById('form-create-client').reset();
        document.getElementById('create-client-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-create-client')).show();
    });

    document.getElementById('btn-save-client').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-create-client');
        const errorEl = document.getElementById('create-client-error');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        setLoading(btn, true);
        errorEl.classList.add('d-none');
        const res = await api.post('/clients', buildPayload(new FormData(form)));
        setLoading(btn, false);

        if (res.data?.id ?? res.id) {
            bootstrap.Modal.getInstance(document.getElementById('modal-create-client')).hide();
            showToast('Клиент добавлен.');
            await loadClients();
        } else {
            showErr(errorEl, res);
        }
    });

    // ---- Edit ----
    function openEditClient(c) {
        const form = document.getElementById('form-edit-client');
        document.getElementById('edit-client-id').value = c.id;
        form.querySelector('[name="agency_id"]').value       = c.agency_id ?? '';
        form.querySelector('[name="name"]').value            = c.name ?? '';
        form.querySelector('[name="email"]').value           = c.email ?? '';
        window.setPhoneValue(form.querySelector('[name="phone"]'), c.phone ?? '');
        form.querySelector('[name="nationality"]').value     = c.nationality ?? '';
        form.querySelector('[name="date_of_birth"]').value   = c.date_of_birth ?? '';
        form.querySelector('[name="passport_number"]').value = c.passport_number ?? '';
        form.querySelector('[name="notes"]').value           = c.notes ?? '';
        document.getElementById('edit-client-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-edit-client')).show();
    }

    document.getElementById('btn-update-client').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-edit-client');
        const errorEl = document.getElementById('edit-client-error');
        const id = document.getElementById('edit-client-id').value;

        setLoading(btn, true);
        errorEl.classList.add('d-none');
        const res = await api.patch(`/clients/${id}`, buildPayload(new FormData(form)));
        setLoading(btn, false);

        if (res.data?.id ?? res.id) {
            bootstrap.Modal.getInstance(document.getElementById('modal-edit-client')).hide();
            showToast('Клиент обновлён.');
            await loadClients();
        } else {
            showErr(errorEl, res);
        }
    });

    async function deleteClient(id) {
        if (!confirm('Удалить этого клиента?')) return;
        await api.delete(`/clients/${id}`);
        showToast('Клиент удалён.');
        allClients = allClients.filter(c => c.id !== id);
        applyFilters();
    }

    function buildPayload(fd) {
        return {
            agency_id:       fd.get('agency_id') || null,
            name:            fd.get('name'),
            email:           fd.get('email') || null,
            phone:           fd.get('phone') || null,
            nationality:     fd.get('nationality') || null,
            date_of_birth:   fd.get('date_of_birth') || null,
            passport_number: fd.get('passport_number') || null,
            notes:           fd.get('notes') || null,
        };
    }

    function setLoading(btn, state) {
        btn.disabled = state;
        btn.querySelector('.indicator-label').classList.toggle('d-none', state);
        btn.querySelector('.indicator-progress').classList.toggle('d-none', !state);
    }

    function showErr(el, res) {
        const errors = res.errors ? Object.values(res.errors).flat().join(' ') : null;
        el.textContent = errors ?? res.message ?? 'Произошла ошибка.';
        el.classList.remove('d-none');
    }

    const AVATAR_COLORS = [
        ['bg-light-primary', 'text-primary'], ['bg-light-success', 'text-success'],
        ['bg-light-info', 'text-info'], ['bg-light-warning', 'text-warning'],
        ['bg-light-danger', 'text-danger'], ['bg-light-dark', 'text-dark'],
    ];
    function avatarInitials(name) {
        const w = (name ?? '').trim().split(/\s+/).filter(Boolean);
        return w.length >= 2 ? (w[0][0] + w[1][0]).toUpperCase() : (w[0] ?? '?').slice(0, 2).toUpperCase();
    }
    function avatarColor(name) {
        let h = 0;
        for (let i = 0; i < (name ?? '').length; i++) h = (name.charCodeAt(i) + ((h << 5) - h)) | 0;
        return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length];
    }
    function avatarHtml(name, size = 35) {
        const [bg, text] = avatarColor(name ?? '');
        return `<div class="symbol symbol-${size}px flex-shrink-0"><div class="symbol-label ${bg} fw-bold fs-8 ${text}">${avatarInitials(name)}</div></div>`;
    }

    function escHtml(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    const _intlRegion = new Intl.DisplayNames(['ru'], { type: 'region' });
    function countryName(code) {
        if (!code) return '';
        try { return _intlRegion.of(code); } catch { return code; }
    }
</script>
@endpush
