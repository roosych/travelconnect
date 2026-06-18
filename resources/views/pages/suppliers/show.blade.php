@extends('layouts.app')

@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
<style>
    .filepond--root { font-family: inherit; }
    .filepond--panel-root { background: #f9f9f9; border: 2px dashed #e4e6ef; border-radius: 8px; }
    .filepond--drop-label { color: #a1a5b7; }
    .filepond--image-preview-wrapper { display: block !important; }
    .filepond--image-preview { display: block !important; opacity: 1 !important; }
    .filepond--item-panel { background: #1e1e2d; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
@endpush

@section('title', __('suppliers.show_title'))
@section('page-title', __('suppliers.show_title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.suppliers.index') }}" class="text-muted text-hover-primary">{{ __('suppliers.breadcrumb_list') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted" id="bc-supplier-name">{{ __('common.loading') }}</li>
@endsection

@section('toolbar-actions')
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-light-primary btn-sm" id="btn-edit-supplier">
            <i class="ki-outline ki-pencil fs-4 me-1"></i> {{ __('common.edit') }}
        </button>
        <button class="btn btn-light-danger btn-sm" id="btn-delete-supplier">
            <i class="ki-outline ki-trash fs-4 me-1"></i> {{ __('common.delete') }}
        </button>
    </div>
@endsection

@section('content')

<div id="supplier-content">
    <div class="text-center py-20">
        <span class="spinner-border text-primary"></span>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="modal-edit-supplier-show" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('suppliers.show.edit_modal_title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-edit-supplier-show">
                    @include('pages.suppliers._form')
                    <div id="edit-show-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-edit-show" class="btn btn-primary">
                    <span class="indicator-label">{{ __('common.save') }}</span>
                    <span class="indicator-progress d-none">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="modal-add-member" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('suppliers.add_modal.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="row g-5">
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('suppliers.add_modal.email') }}</label>
                        <input type="email" id="member-email" class="form-control form-control-solid"
                               placeholder="employee@company.com" />
                        <div class="form-text">{{ __('suppliers.add_modal.email_hint') }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('suppliers.add_modal.name') }} <span class="text-muted fs-7">{{ __('suppliers.add_modal.name_hint') }}</span></label>
                        <input type="text" id="member-name" class="form-control form-control-solid"
                               placeholder="{{ __('suppliers.add_modal.name_ph') }}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('suppliers.add_modal.role') }}</label>
                        <select id="member-role" class="form-select form-select-solid">
                            <option value="staff" selected>{{ __('suppliers.roles.staff') }}</option>
                            <option value="manager">{{ __('suppliers.roles.manager') }}</option>
                            <option value="owner">{{ __('suppliers.roles.owner') }}</option>
                        </select>
                    </div>
                    <div id="add-member-error" class="col-12 alert alert-danger d-none"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-member" class="btn btn-primary">
                    <span class="indicator-label">{{ __('suppliers.add_modal.add') }}</span>
                    <span class="indicator-progress d-none">{{ __('suppliers.add_modal.adding') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Generic Delete Confirmation Modal --}}
<div class="modal fade" id="modal-confirm-delete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold" id="confirm-delete-title">{{ __('suppliers.delete.title') }}</h5>
            </div>
            <div class="modal-body text-muted py-3" id="confirm-delete-body">{{ __('suppliers.delete.body') }}</div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-confirm-delete-ok" class="btn btn-danger">{{ __('suppliers.delete.btn') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const supplierId = {{ $id }};
    let supplier = null;
    let services = [];

    const t  = @json(__('suppliers'));
    const tc = @json(__('common'));

    // Нейтральные бейджи + динамические лейблы из каталога (см. js-helpers).
    const SERVICE_META = Object.fromEntries(Object.entries(window.SERVICE_LABELS).map(([k, v]) =>
        [k, { label: v, color: 'secondary' }]));


    // ---- Delete confirmation helper ----
    let confirmDeleteCallback = null;

    function showDeleteConfirm(message, callback) {
        document.getElementById('confirm-delete-body').textContent = message;
        confirmDeleteCallback = callback;
        new bootstrap.Modal(document.getElementById('modal-confirm-delete')).show();
    }

    document.getElementById('btn-confirm-delete-ok').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('modal-confirm-delete')).hide();
        if (confirmDeleteCallback) confirmDeleteCallback();
        confirmDeleteCallback = null;
    });

    (async () => {
        await loadSupplier();
        await Promise.all([loadServices(), loadMembers(), loadIncidents()]);
    })();

    async function loadSupplier() {
        const res = await api.get(`/suppliers/${supplierId}`);
        supplier = res.data ?? res;
        renderSupplier(supplier);
        document.getElementById('bc-supplier-name').textContent = supplier.name ?? '—';
    }

    function renderSupplier(s) {
        const types = (s.service_types ?? []).map(t => {
            const meta = SERVICE_META[t] ?? { label: t, color: 'secondary' };
            return `<span class="badge badge-light-${meta.color} me-1">${escHtml(meta.label)}</span>`;
        }).join('') || '—';

        const activeBadge = s.is_active
            ? `<span class="badge badge-light-success">${t.form.active}</span>`
            : `<span class="badge badge-light-danger">${t.form.inactive}</span>`;

        // Поставщик сам приостановил приём запросов.
        const pausedBadge = s.accepting_requests === false
            ? `<span class="badge badge-light-warning ms-2" title="${t.paused_title}"><i class="ki-outline ki-pause fs-8 me-1"></i>${t.paused}</span>`
            : '';

        const offers = (s.offers ?? []);
        const offersRows = offers.length
            ? offers.map(o => `
                <tr>
                    <td><a href="/admin/offers/${o.id}" class="text-hover-primary">${o.id}</a></td>
                    <td>${escHtml(o.rfq?.title ?? '—')}</td>
                    <td>${escHtml(o.unit_price ?? '—')} ${escHtml(o.currency ?? '')}</td>
                    <td>${o.status_label ? `<span class="badge ${o.status_badge_class}">${escHtml(o.status_label)}</span>` : `<span class="badge badge-light">${escHtml(o.status ?? '—')}</span>`}</td>
                    <td class="text-muted">${o.valid_until ?? '—'}</td>
                </tr>`).join('')
            : `<tr><td colspan="5" class="text-center text-muted py-6">${t.show.offers.empty}</td></tr>`;

        document.getElementById('supplier-content').innerHTML = `
            <div class="row g-6">

                {{-- Profile Card --}}
                <div class="col-lg-4">
                    <div class="card card-flush h-100">
                        <div class="card-body text-center pt-10">
                            <div class="d-inline-block mb-4">
                                ${s.avatar_url
                                    ? `<img src="${escHtml(s.avatar_url)}" class="rounded-circle"
                                           style="width:100px;height:100px;object-fit:cover;">`
                                    : `<div class="symbol symbol-100px symbol-circle mx-auto">
                                           <div class="symbol-label rounded-circle ${avatarColor(s.name)[0]} fw-bold fs-2 ${avatarColor(s.name)[1]}">
                                               ${avatarInitials(s.name)}
                                           </div>
                                       </div>`
                                }
                            </div>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                <h3 class="fw-bold text-gray-800 mb-0">${escHtml(s.name)}</h3>
                                ${s.uses_portal ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                     data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${t.portal_title}" style="cursor:default;flex-shrink:0">
                                    <circle cx="12" cy="12" r="10" fill="#0095F6"/>
                                    <path d="M7 12.5l3.5 3.5 6.5-7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>` : ''}
                            </div>
                            <p class="text-muted mb-0">${escHtml(s.email ?? '')}</p>
                            <p class="text-muted mb-4">${escHtml(s.phone ?? '')}</p>
                            <div class="mb-4">${activeBadge}${pausedBadge}</div>
                            <button class="btn btn-sm btn-light-${s.is_active ? 'warning' : 'success'}"
                                    onclick="toggleActive()">
                                ${s.is_active ? t.show.deactivate : t.show.activate}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Details + Tabs --}}
                <div class="col-lg-8">
                    <div class="card card-flush">
                        <div class="card-header pt-6">
                            <div class="card-title">
                                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 fw-semibold border-0">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-details">${t.show.tabs.details}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#tab-members">
                                            ${t.show.tabs.members}
                                            <span class="badge badge-circle badge-sm badge-light-primary ms-2" id="members-badge">${s.members_count ?? 0}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tab-details">
                                    <table class="table table-borderless fs-6">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted fw-semibold w-175px">${t.show.details.website}</td>
                                                <td>${s.website
                                                    ? `<a href="${escHtml(s.website)}" target="_blank" class="text-hover-primary">${escHtml(s.website)}</a>`
                                                    : '—'}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">${t.show.details.service_types}</td>
                                                <td>${types}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">${t.show.details.description}</td>
                                                <td class="text-gray-700">${escHtml(s.description ?? '—')}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted fw-semibold">${t.show.details.created}</td>
                                                <td class="text-muted">${s.created_at ?? '—'}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="tab-members">
                                    <div class="d-flex justify-content-end mb-4">
                                        <button class="btn btn-sm btn-primary" id="btn-add-member">
                                            <i class="ki-outline ki-plus fs-4 me-1"></i> ${t.members.add}
                                        </button>
                                    </div>
                                    <div id="members-container">
                                        <div class="text-center py-6">
                                            <span class="spinner-border spinner-border-sm text-primary"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Services Catalog --}}
                <div class="col-12">
                    <div class="card card-flush">
                        <div class="card-header">
                            <div class="card-title"><h4 class="fw-bold">${t.show.catalog.title}</h4></div>
                            <div class="card-toolbar">
                                <span class="text-muted fs-8">${t.show.catalog.subtitle}</span>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div id="services-table-container">
                                <div class="text-center py-6">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Offers --}}
                <div class="col-12">
                    <div class="card card-flush">
                        <div class="card-header">
                            <div class="card-title"><h4 class="fw-bold">${t.show.offers.title}</h4></div>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table align-middle table-row-dashed fs-6 gy-4">
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase">
                                        <th>${t.show.offers.col_id}</th><th>${t.show.offers.col_request}</th><th>${t.show.offers.col_price}</th><th>${t.show.offers.col_status}</th><th>${t.show.offers.col_valid}</th>
                                    </tr>
                                </thead>
                                <tbody>${offersRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Incidents --}}
                <div class="col-12">
                    <div class="card card-flush">
                        <div class="card-header">
                            <div class="card-title">
                                <h4 class="fw-bold">${t.show.incidents.title}</h4>
                            </div>
                        </div>
                        <div class="card-body pt-0" id="incidents-container">
                            <div class="text-center py-6">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>`;

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        document.getElementById('btn-add-member')?.addEventListener('click', () => {
            document.getElementById('member-email').value = '';
            document.getElementById('member-name').value  = '';
            document.getElementById('member-role').value  = 'staff';
            document.getElementById('add-member-error').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('modal-add-member')).show();
        });
    }

    // ---- Edit ----
    document.getElementById('btn-edit-supplier').addEventListener('click', () => {
        if (!supplier) return;
        const form = document.getElementById('form-edit-supplier-show');
        form.querySelector('[name="name"]').value = supplier.name ?? '';
        form.querySelector('[name="email"]').value = supplier.email ?? '';
        window.setPhoneValue(form.querySelector('[name="phone"]'), supplier.phone ?? '');
        form.querySelector('[name="description"]').value = supplier.description ?? '';
        form.querySelector('[name="website"]').value = supplier.website ?? '';
        const activeTypes = supplier.service_types ?? [];
        form.querySelectorAll('input[name="service_types[]"]').forEach(cb => {
            cb.checked = activeTypes.includes(cb.value);
        });
        const isActive = supplier.is_active ?? true;
        const activeRadio = form.querySelector(`input[name="is_active"][value="${isActive ? '1' : '0'}"]`);
        if (activeRadio) activeRadio.checked = true;
        document.getElementById('edit-show-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-edit-supplier-show')).show();
    });

    document.getElementById('btn-save-edit-show').addEventListener('click', async function() {
        const btn = this;
        const form = document.getElementById('form-edit-supplier-show');
        const errorEl = document.getElementById('edit-show-error');
        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            description: fd.get('description') || null, website: fd.get('website') || null,
            service_types: [...form.querySelectorAll('input[name="service_types[]"]:checked')].map(el => el.value),
            is_active: fd.get('is_active') === '1',
        };

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        const showError = (res) => {
            const errors = res?.errors ? Object.values(res.errors).flat().join(' ') : null;
            errorEl.textContent = errors ?? res?.message ?? t.index.error_generic;
            errorEl.classList.remove('d-none');
        };

        try {
            const res = await api.patch(`/suppliers/${supplierId}`, payload);
            if (res.data?.id ?? res.id) {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-supplier-show')).hide();
                showToast(t.updated);
                await loadSupplier();
            } else {
                showError(res);
            }
        } catch (err) {
            showError(err?.data ?? null);
        } finally {
            btn.disabled = false;
            btn.querySelector('.indicator-label').classList.remove('d-none');
            btn.querySelector('.indicator-progress').classList.add('d-none');
        }
    });

    // ---- Toggle Active ----
    async function toggleActive() {
        const res = await api.patch(`/suppliers/${supplierId}/toggle-active`);
        if (res.is_active !== undefined) {
            showToast(res.is_active ? t.toggle.activated : t.toggle.deactivated);
            await loadSupplier();
        }
    }

    // ===========================================================================
    // Members
    // ===========================================================================

    async function loadMembers() {
        const container = document.getElementById('members-container');
        if (!container) return;
        try {
            const res = await api.get(`/suppliers/${supplierId}/members`);
            const members = res.data ?? res ?? [];
            renderMembersTable(members);
            const badge = document.getElementById('members-badge');
            if (badge) badge.textContent = members.length;
        } catch(e) {
            if (container) container.innerHTML = `<div class="alert alert-danger">${t.members.load_error}</div>`;
        }
    }

    const ROLE_LABELS = t.roles;

    function renderMembersTable(members) {
        const container = document.getElementById('members-container');
        if (!container) return;

        if (!members.length) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="ki-outline ki-people fs-3x text-gray-300 mb-3 d-block"></i>
                    <span class="text-muted">${t.members.empty}</span>
                </div>`;
            return;
        }

        const rows = members.map(m => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${m.avatar_url
                            ? `<div class="symbol symbol-35px me-3"><img src="${escHtml(m.avatar_url)}" class="rounded-circle" /></div>`
                            : `<div class="symbol symbol-35px me-3"><div class="symbol-label rounded-circle bg-light-primary text-primary fw-bold fs-7">${escHtml((m.name ?? '?')[0].toUpperCase())}</div></div>`
                        }
                        <div>
                            <span class="fw-semibold text-gray-800">${escHtml(m.name ?? '—')}</span>
                            <div class="text-muted fs-7">${escHtml(m.email ?? '')}</div>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-light-primary">${escHtml(ROLE_LABELS[m.role] ?? m.role)}</span></td>
                <td class="text-muted fs-7">${m.joined_at ?? '—'}</td>
                <td class="text-end">
                    ${m.role !== 'owner' ? `
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger" title="${t.members.remove}"
                            onclick="removeMember(${m.id})">
                        <i class="ki-outline ki-trash fs-5"></i>
                    </button>` : ''}
                </td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-3">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">${t.members.col_member}</th>
                        <th class="min-w-100px">${t.members.col_role}</th>
                        <th class="min-w-120px">${t.members.col_joined}</th>
                        <th class="min-w-80px text-end">${t.members.col_actions}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    function removeMember(userId) {
        showDeleteConfirm(t.members.remove_confirm, async () => {
            await api.delete(`/suppliers/${supplierId}/members/${userId}`);
            showToast(t.members.removed);
            await loadMembers();
        });
    }

    document.getElementById('btn-save-member').addEventListener('click', async function() {
        const btn     = this;
        const email   = document.getElementById('member-email').value.trim();
        const name    = document.getElementById('member-name').value.trim();
        const role    = document.getElementById('member-role').value;
        const errorEl = document.getElementById('add-member-error');

        if (!email) { errorEl.textContent = t.add_modal.email_required; errorEl.classList.remove('d-none'); return; }

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        const res = await api.post(`/suppliers/${supplierId}/members`, { email, name: name || null, role });

        btn.disabled = false;
        btn.querySelector('.indicator-label').classList.remove('d-none');
        btn.querySelector('.indicator-progress').classList.add('d-none');

        if (res.data) {
            bootstrap.Modal.getInstance(document.getElementById('modal-add-member')).hide();
            showToast(t.members.added);
            await loadMembers();
        } else {
            const errors = res.errors ? Object.values(res.errors).flat().join(' ') : null;
            errorEl.textContent = errors ?? res.message ?? t.add_modal.error_generic;
            errorEl.classList.remove('d-none');
        }
    });

    // ---- Delete ----
    document.getElementById('btn-delete-supplier').addEventListener('click', () => {
        showDeleteConfirm(t.delete.confirm, async () => {
            await api.delete(`/suppliers/${supplierId}`);
            showToast(t.deleted);
            setTimeout(() => window.location.href = '{{ route("admin.suppliers.index") }}', 1000);
        });
    });

    // ===========================================================================
    // Services Catalog
    // ===========================================================================

    async function loadServices() {
        const res = await api.get(`/suppliers/${supplierId}/services`);
        services = res.data ?? res ?? [];
        renderServicesTable(services);
    }

    function renderServicesTable(list) {
        const container = document.getElementById('services-table-container');
        if (!container) return;

        if (!list.length) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="ki-outline ki-information-5 fs-3x text-gray-300 mb-3 d-block"></i>
                    <span class="text-muted">${t.show.catalog.empty}</span>
                </div>`;
            return;
        }

        const rows = list.map(svc => {
            const avBadge = svc.is_available
                ? `<span class="badge badge-light-success">${t.show.catalog.available}</span>`
                : `<span class="badge badge-light-secondary">${t.show.catalog.unavailable}</span>`;

            const thumb = svc.photos?.length
                ? `<img src="${escHtml(svc.photos[0].url)}" class="rounded object-fit-cover" style="width:40px;height:40px" title="${t.show.catalog.photos.replace(':n', svc.photos.length)}">`
                : `<span class="symbol symbol-40px"><span class="symbol-label bg-light text-muted fs-8"><i class="ki-outline ki-picture fs-4 text-muted"></i></span></span>`;

            return `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        ${thumb}
                        <div>
                            <span class="fw-semibold text-gray-800">${escHtml(svc.name)}</span>
                            <div class="mt-1">
                                ${(() => { const m = SERVICE_META[svc.type] ?? { label: svc.type_label ?? svc.type, color: 'secondary' }; return `<span class="badge badge-light-${m.color}">${escHtml(m.label)}</span>`; })()}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-muted">${svc.capacity ? svc.capacity + ' ' + t.show.catalog.pax : '—'}</td>
                <td class="fw-semibold text-gray-800">
                    ${svc.base_price.toFixed(2)} ${escHtml(svc.currency)}
                    <span class="text-muted fw-normal fs-7">/ ${escHtml(svc.price_unit_label)}</span>
                </td>
                <td>${avBadge}</td>
                <td class="text-muted fs-7">${escHtml(svc.description ?? '—').substring(0, 60)}${(svc.description?.length ?? 0) > 60 ? '…' : ''}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-250px">${t.show.catalog.col_service}</th>
                        <th class="min-w-80px">${t.show.catalog.col_capacity}</th>
                        <th class="min-w-160px">${t.show.catalog.col_price}</th>
                        <th class="min-w-100px">${t.show.catalog.col_status}</th>
                        <th class="min-w-200px">${t.show.catalog.col_description}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    // ===========================================================================
    // Incidents
    // ===========================================================================

    async function loadIncidents() {
        const res = await api.get(`/suppliers/${supplierId}/incidents`);
        renderIncidents(res.data ?? []);
    }

    function renderIncidents(incidents) {
        const container = document.getElementById('incidents-container');
        if (!container) return;

        if (!incidents.length) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="ki-outline ki-shield-tick fs-3x text-gray-300 mb-3 d-block"></i>
                    <span class="text-muted">${t.show.incidents.empty}</span>
                </div>`;
            return;
        }

        const FROM_STATUS_LABELS = t.show.incidents.from;

        const rows = incidents.map(inc => `
            <tr>
                <td class="text-muted fs-7">${escHtml(inc.created_at ?? '—')}</td>
                <td>${escHtml(inc.type_label)}</td>
                <td><span class="badge ${escHtml(inc.severity_badge_class)}">${escHtml(inc.severity_label)}</span></td>
                <td class="text-muted fs-7">
                    ${inc.context?.from_status
                        ? `${t.show.incidents.from_status} <strong>${escHtml(FROM_STATUS_LABELS[inc.context.from_status] ?? inc.context.from_status)}</strong>`
                        : '—'}
                    ${inc.context?.rfq_id
                        ? ` · <a href="/admin/rfqs/${inc.context.rfq_id}" class="text-hover-primary">${t.show.incidents.rfq_ref.replace(':id', inc.context.rfq_id)}</a>`
                        : ''}
                    ${inc.subject_id
                        ? ` · <a href="/admin/offers/${inc.subject_id}" class="text-hover-primary">${t.show.incidents.offer_ref.replace(':id', inc.subject_id)}</a>`
                        : ''}
                </td>
                <td class="text-muted fst-italic fs-7">${escHtml(inc.notes ?? '—')}</td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-130px">${t.show.incidents.col_date}</th>
                        <th class="min-w-160px">${t.show.incidents.col_type}</th>
                        <th class="min-w-100px">${t.show.incidents.col_severity}</th>
                        <th>${t.show.incidents.col_details}</th>
                        <th class="min-w-150px">${t.show.incidents.col_note}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
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

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

</script>
@endpush
