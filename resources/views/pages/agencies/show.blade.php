@extends('layouts.app')

@section('title', __('agencies.title'))
@section('page-title', __('agencies.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('admin.agencies.index') }}" class="text-muted text-hover-primary">{{ __('agencies.breadcrumb_list') }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-500 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted" id="bc-agency-name">{{ __('common.loading') }}</li>
@endsection

@section('toolbar-actions')
    <button class="btn btn-light-primary btn-sm" id="btn-edit-agency">
        <i class="ki-outline ki-pencil fs-4 me-1"></i> {{ __('common.edit') }}
    </button>
    <button class="btn btn-light-danger btn-sm ms-2" id="btn-delete-agency">
        <i class="ki-outline ki-trash fs-4 me-1"></i> {{ __('common.delete') }}
    </button>
@endsection

@section('content')

{{-- Hidden file input for avatar upload --}}
<input type="file" id="agency-avatar-input" accept="image/*" class="d-none" />

{{-- Request quick-view drawer --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="request-drawer" style="width:500px">
    <div class="offcanvas-header border-bottom py-5 px-7">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1" id="drawer-request-title">{{ __('agencies.drawer.title') }}</h5>
            <span id="drawer-request-status"></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-7" id="drawer-request-body">
        <div class="text-center py-10"><span class="spinner-border text-primary"></span></div>
    </div>
</div>

{{-- Profile header --}}
<div class="card card-flush mb-6" id="agency-header-card">
    <div class="card-body py-8">
        <div class="text-center py-6"><span class="spinner-border text-primary"></span></div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-5 fw-semibold mb-6">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-requests">
            {{ __('agencies.tabs.requests') }} <span class="badge badge-light-primary ms-2" id="tab-requests-count">—</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-proposals">
            {{ __('agencies.tabs.proposals') }} <span class="badge badge-light-success ms-2" id="tab-proposals-count">—</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-members">
            {{ __('agencies.tabs.members') }} <span class="badge badge-light-dark ms-2" id="tab-members-count">—</span>
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- Requests Tab --}}
    <div class="tab-pane fade show active" id="tab-requests">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <h3 class="card-title fw-bold">{{ __('agencies.requests.card_title') }}</h3>
            </div>
            <div class="card-body pt-0">
                <div id="requests-container">
                    <div class="text-center py-8"><span class="spinner-border text-primary"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Proposals Tab --}}
    <div class="tab-pane fade" id="tab-proposals">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <h3 class="card-title fw-bold">{{ __('agencies.proposals.card_title') }}</h3>
            </div>
            <div class="card-body pt-0">
                <div id="proposals-container">
                    <div class="text-center py-8"><span class="spinner-border text-success"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Members Tab --}}
    <div class="tab-pane fade" id="tab-members">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5">
                <h3 class="card-title fw-bold">{{ __('agencies.members.card_title') }}</h3>
                <div class="card-toolbar">
                    <button class="btn btn-primary btn-sm" id="btn-add-member">
                        <i class="ki-outline ki-plus fs-4 me-1"></i> {{ __('agencies.members.add') }}
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="members-container">
                    <div class="text-center py-8"><span class="spinner-border text-dark"></span></div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="modal-add-member" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('agencies.add_modal.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <div class="row g-5">
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('agencies.add_modal.email') }}</label>
                        <input type="email" id="member-email" class="form-control form-control-solid"
                               placeholder="employee@company.com" />
                        <div class="form-text">{{ __('agencies.add_modal.email_hint') }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('agencies.add_modal.name') }} <span class="text-muted fs-7">{{ __('agencies.add_modal.name_hint') }}</span></label>
                        <input type="text" id="member-name" class="form-control form-control-solid"
                               placeholder="{{ __('agencies.add_modal.name_ph') }}" />
                    </div>
                    <div class="col-12">
                        <label class="form-label required fw-semibold">{{ __('agencies.add_modal.role') }}</label>
                        <select id="member-role" class="form-select form-select-solid">
                            <option value="staff">{{ __('agencies.roles.staff') }}</option>
                            <option value="manager">{{ __('agencies.roles.manager') }}</option>
                            <option value="owner">{{ __('agencies.roles.owner') }}</option>
                        </select>
                    </div>
                    <div id="add-member-error" class="col-12 alert alert-danger d-none"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-member" class="btn btn-primary">
                    <span class="indicator-label">{{ __('common.add') }}</span>
                    <span class="indicator-progress d-none">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="modal-edit-agency-show" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="fw-bold">{{ __('agencies.edit_modal.title') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-6 px-7">
                <form id="form-edit-agency-show">
                    @include('pages.agencies._form')
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold">
                            {{ __('agencies.edit_modal.new_password') }} <span class="text-muted fs-7">{{ __('agencies.edit_modal.new_password_hint') }}</span>
                        </label>
                        <input type="password" name="password" class="form-control form-control-solid" />
                    </div>
                    <div id="edit-agency-show-error" class="alert alert-danger mt-4 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-save-edit-agency-show" class="btn btn-primary">
                    <span class="indicator-label">{{ __('agencies.edit_modal.submit') }}</span>
                    <span class="indicator-progress d-none">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const agencyId = {{ $id }};
    let agency = null;
    let agencyRequests = [];

    // Локализация: словарь страницы + общий. Хелперы (serviceBadge, statusBadge,
    // formatDate, formatCurrency, countryName, escHtml) — общие из partials/js-helpers;
    // currencyName — глобальный из layout.
    const t  = @json(__('agencies'));
    const tc = @json(__('common'));

    // Маршрут по странам (сегментная модель legs): флаг + страна + города.
    function renderRoute(r) {
        const legs = Array.isArray(r.legs) ? r.legs : [];
        if (!legs.length) {
            return r.destination
                ? `<div class="text-muted fs-7 mt-1"><i class="ki-outline ki-geolocation fs-8 me-1"></i>${escHtml(r.destination)}</div>`
                : '';
        }
        const lines = legs.map(leg => {
            const cities = Array.isArray(leg.destinations) && leg.destinations.length
                ? `<div class="text-muted fs-8" style="margin-left:26px">${leg.destinations.map(escHtml).join(' · ')}</div>`
                : '';
            return `
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${leg.country_flag}" alt=""
                             style="width:18px;height:13px;object-fit:cover;border-radius:2px;flex-shrink:0"
                             onerror="this.style.display='none'">
                        <span class="text-gray-800 fw-semibold fs-7">${escHtml(leg.country_name)}</span>
                    </div>
                    ${cities}
                </div>`;
        }).join('');
        return `<div class="d-flex flex-column gap-1 mt-1">${lines}</div>`;
    }

    // Объединение типов услуг по сегментам (fallback на legacy services_needed).
    function renderServices(r) {
        const legs = Array.isArray(r.legs) ? r.legs : [];
        let types = [];
        if (legs.length) {
            const seen = new Set();
            legs.forEach(l => (l.services || []).forEach(s => {
                if (!seen.has(s.type)) { seen.add(s.type); types.push(s.type); }
            }));
        } else {
            types = Array.isArray(r.services_needed) ? r.services_needed : [];
        }
        return types.length ? types.map(s => serviceBadge(s)).join('') : '<span class="text-muted fs-8">—</span>';
    }

    (async function init() {
        await loadAgency();
        await Promise.all([loadRequests(), loadClients(), loadMembers()]);
    })();

    // ---- Agency ----
    async function loadAgency() {
        try {
            const res = await api.get(`/agencies/${agencyId}`);
            agency = res.data ?? res;
            renderHeaderCard(agency);
            document.getElementById('bc-agency-name').textContent = agency.name ?? '—';
        } catch {
            document.getElementById('agency-header-card').querySelector('.card-body').innerHTML =
                `<div class="alert alert-danger">${t.header.load_error}</div>`;
        }
    }

    function renderHeaderCard(a) {
        const words = (a.name ?? '?').trim().split(/\s+/).filter(Boolean);
        const initials = words.slice(0, 2).map(w => w[0].toUpperCase()).join('');

        const avatarContent = a.avatar_url
            ? `<img src="${escHtml(a.avatar_url)}" alt="${escHtml(a.name ?? '')}" class="rounded-circle object-fit-cover" style="width:90px;height:90px;" />`
            : `<div class="symbol-label rounded-circle bg-light-success text-success fw-bold fs-1">${escHtml(initials)}</div>`;

        document.getElementById('agency-header-card').innerHTML = `
            <div class="card-body py-7 px-8">
                <div class="d-flex flex-wrap gap-7 align-items-start">

                    {{-- Avatar with upload --}}
                    <div class="position-relative flex-shrink-0" id="agency-avatar-trigger" style="cursor:pointer" title="${t.header.avatar_hint}">
                        <div class="symbol symbol-90px symbol-circle">${avatarContent}</div>
                        <div class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:30px;height:30px;background:#009ef7;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.2);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 4L7.17 6H4C2.9 6 2 6.9 2 8v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-3.17L15 4H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z" fill="#fff"/>
                                <circle cx="12" cy="14" r="3" fill="#fff" fill-opacity=".4"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Info block --}}
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-baseline gap-3 mb-1">
                            <h2 class="fw-bold text-gray-900 mb-0 fs-2">${escHtml(a.name)}</h2>
                            ${a.company_name ? `<span class="text-muted fs-5">${escHtml(a.company_name)}</span>` : ''}
                        </div>

                        <div class="d-flex flex-wrap gap-5 text-muted fs-6 mb-5">
                            ${a.email ? `<a href="mailto:${escHtml(a.email)}" class="d-flex align-items-center gap-1 text-muted text-hover-primary">
                                <i class="ki-outline ki-sms fs-5"></i>${escHtml(a.email)}</a>` : ''}
                            ${a.phone ? `<span class="d-flex align-items-center gap-1">
                                <i class="ki-outline ki-phone fs-5"></i>${escHtml(a.phone)}</span>` : ''}
                            ${a.country ? `<span class="d-flex align-items-center gap-1">
                                <i class="ki-outline ki-geolocation fs-5"></i>${escHtml(countryName(a.country) || a.country)}</span>` : ''}
                            ${a.currency_code ? `<span class="d-flex align-items-center gap-1">
                                <i class="ki-outline ki-dollar fs-5"></i>${escHtml(a.currency_code)}</span>` : ''}
                        </div>

                        <div class="d-flex flex-wrap gap-4">
                            <div class="border border-dashed border-gray-300 rounded min-w-90px py-3 px-4 text-center">
                                <div class="fw-bold fs-2 text-gray-900">${a.requests_count ?? 0}</div>
                                <div class="text-muted fs-8 text-uppercase">${t.header.stat_requests}</div>
                            </div>
                            <div class="border border-dashed border-gray-300 rounded min-w-90px py-3 px-4 text-center">
                                <div class="fw-bold fs-2 text-gray-900">${a.bookings_count ?? 0}</div>
                                <div class="text-muted fs-8 text-uppercase">${t.header.stat_bookings}</div>
                            </div>
                            <div class="border border-dashed border-gray-300 rounded min-w-90px py-3 px-4 text-center" id="header-stat-clients">
                                <div class="fw-bold fs-2 text-gray-900" id="header-clients-val">—</div>
                                <div class="text-muted fs-8 text-uppercase">${t.header.stat_clients}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="flex-shrink-0 text-end">
                        <div class="text-muted fs-7">
                            <i class="ki-outline ki-calendar fs-7 me-1"></i>
                            ${t.header.member_since.replace(':date', a.created_at ? formatDate(a.created_at) : '—')}
                        </div>
                    </div>

                </div>
            </div>`;

        // Re-bind avatar upload trigger after re-render
        document.getElementById('agency-avatar-trigger')?.addEventListener('click', () => {
            document.getElementById('agency-avatar-input').click();
        });
    }

    // Avatar upload
    document.getElementById('agency-avatar-input').addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('avatar', file);
        try {
            const res = await api.upload(`/agencies/${agencyId}/avatar`, fd);
            if (res?.avatar_url) {
                agency.avatar_url = res.avatar_url;
                renderHeaderCard(agency);
                showToast(t.header.photo_updated);
            } else {
                showToast(t.header.photo_error, 'error');
            }
        } catch {
            showToast(t.header.photo_error, 'error');
        }
        this.value = '';
    });

    // ---- Requests ----
    async function loadRequests() {
        try {
            const data = await api.get(`/requests?agency_id=${agencyId}`);
            agencyRequests = data.data ?? data ?? [];
            document.getElementById('tab-requests-count').textContent = agencyRequests.length;
            renderRequestsTable(agencyRequests);
            // Proposals depend on requests, load after
            loadProposals(agencyRequests);
        } catch {
            document.getElementById('requests-container').innerHTML =
                `<div class="alert alert-danger">${t.requests.load_error}</div>`;
        }
    }

    function renderRequestsTable(requests) {
        const container = document.getElementById('requests-container');

        if (!requests.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-document fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.requests.empty}</span>
                </div>`;
            return;
        }

        const rows = requests.map(r => {
            const datesStr = r.travel_date_from
                ? formatDate(r.travel_date_from) + (r.travel_date_to ? ' — ' + formatDate(r.travel_date_to) : '')
                : '—';

            return `
            <tr>
                <td>
                    <a href="/admin/requests/${r.id}" class="fw-semibold text-gray-800 text-hover-primary">${escHtml(r.title ?? r.destination ?? t.requests.default_title.replace(':id', r.id))}</a>
                    ${renderRoute(r)}
                </td>
                <td><div class="d-flex flex-column align-items-start gap-1">${renderServices(r)}</div></td>
                <td class="text-muted fs-7 text-center">${r.pax_count ?? '—'}</td>
                <td class="text-muted fs-7">${datesStr}</td>
                <td>${statusBadge(r)}</td>
                <td class="text-muted fs-7">${formatDate(r.created_at)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-icon btn-light me-1" title="${t.requests.quick_view}"
                            onclick="openRequestDrawer(${r.id})">
                        <i class="ki-outline ki-eye fs-4"></i>
                    </button>
                    <a href="/admin/requests/${r.id}" class="btn btn-sm btn-icon btn-light" title="${t.requests.open_page}">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed table-row-hover fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">${t.requests.col_request}</th>
                        <th class="min-w-150px">${t.requests.col_services}</th>
                        <th class="min-w-60px text-center">${t.requests.col_pax}</th>
                        <th class="min-w-175px">${t.requests.col_dates}</th>
                        <th class="min-w-110px">${t.requests.col_status}</th>
                        <th class="min-w-100px">${t.requests.col_created}</th>
                        <th class="text-end min-w-100px">${t.requests.col_actions}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    // ---- Proposals ----
    async function loadProposals(requests) {
        try {
            const withProposals = (requests ?? agencyRequests).filter(r => (r.proposals_count ?? 1) > 0);
            const groups = await Promise.all(
                withProposals.map(r =>
                    api.get(`/requests/${r.id}/proposals`)
                        .then(d => { const p = d.data ?? d ?? []; p.forEach(x => { x._request = r; }); return p; })
                        .catch(() => [])
                )
            );
            const proposals = groups.flat();
            document.getElementById('tab-proposals-count').textContent = proposals.length;
            renderProposalsTable(proposals);
        } catch {
            document.getElementById('proposals-container').innerHTML =
                `<div class="alert alert-danger">${t.proposals.load_error}</div>`;
        }
    }

    function renderProposalsTable(proposals) {
        const container = document.getElementById('proposals-container');

        if (!proposals.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-book-open fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.proposals.empty}</span>
                </div>`;
            return;
        }

        const rows = proposals.map(p => `
            <tr>
                <td>
                    <a href="/admin/proposals/${p.id}" class="fw-semibold text-gray-800 text-hover-primary d-block">
                        ${escHtml(p.title ?? t.proposals.default_title.replace(':id', p.id))}
                    </a>
                </td>
                <td>
                    <a href="/admin/requests/${p._request?.id}" class="text-muted text-hover-primary fs-7">
                        ${escHtml(p._request?.title ?? p._request?.destination ?? '—')}
                    </a>
                </td>
                <td>${moneyAznAgency(p.amount_azn ?? p.total_price, p.agency_amount, p.agency_currency)}</td>
                <td>${statusBadge(p)}</td>
                <td class="text-muted fs-7">${formatDate(p.created_at)}</td>
                <td class="text-end">
                    <a href="/admin/proposals/${p.id}" class="btn btn-sm btn-icon btn-light" title="${t.proposals.col_open}">
                        <i class="ki-outline ki-arrow-right fs-4"></i>
                    </a>
                </td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">${t.proposals.col_title}</th>
                        <th class="min-w-175px">${t.proposals.col_request}</th>
                        <th class="min-w-120px">${t.proposals.col_amount}</th>
                        <th class="min-w-100px">${t.proposals.col_status}</th>
                        <th class="min-w-100px">${t.proposals.col_created}</th>
                        <th class="text-end min-w-60px">${t.proposals.col_open}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    // ---- Clients (вкладка убрана; обновляем только KPI «Клиенты» в шапке) ----
    async function loadClients() {
        try {
            const data = await api.get(`/clients?agency_id=${agencyId}`);
            const clients = data.data ?? data ?? [];
            const val = document.getElementById('header-clients-val');
            if (val) val.textContent = clients.length;
        } catch { /* оставляем «—» */ }
    }

    // ---- Members ----
    async function loadMembers() {
        try {
            const data = await api.get(`/agencies/${agencyId}/members`);
            const members = data.data ?? data ?? [];
            document.getElementById('tab-members-count').textContent = members.length;
            renderMembersTable(members);
        } catch {
            document.getElementById('members-container').innerHTML =
                `<div class="alert alert-danger">${t.members.load_error}</div>`;
        }
    }

    const ROLE_BADGES = { owner: 'badge-light-primary', manager: 'badge-light-warning', staff: 'badge-light-secondary' };

    function renderMembersTable(members) {
        const container = document.getElementById('members-container');

        if (!members.length) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="ki-outline ki-people fs-3x text-gray-300 mb-4 d-block"></i>
                    <span class="text-muted fs-6">${t.members.empty}</span>
                </div>`;
            return;
        }

        const rows = members.map(m => `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        ${m.avatar_url
                            ? `<div class="symbol symbol-40px"><img src="${escHtml(m.avatar_url)}" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"></div>`
                            : `<div class="symbol symbol-40px"><div class="symbol-label rounded-circle bg-light-primary text-primary fw-bold">${escHtml((m.name??'?').slice(0,2).toUpperCase())}</div></div>`
                        }
                        <div>
                            <div class="fw-semibold text-gray-800">${escHtml(m.name)}</div>
                            ${m.email ? `<div class="text-muted fs-7">${escHtml(m.email)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td><span class="badge ${ROLE_BADGES[m.role] ?? 'badge-light-secondary'}">${t.roles[m.role] ?? m.role}</span></td>
                <td class="text-muted fs-7">${m.joined_at ? formatDate(m.joined_at) : '—'}</td>
                <td class="text-end">
                    ${m.role !== 'owner' ? `
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-danger" title="${t.members.remove}"
                            onclick="removeMember(${m.id})">
                        <i class="ki-outline ki-trash fs-4"></i>
                    </button>` : ''}
                </td>
            </tr>`).join('');

        container.innerHTML = `
            <table class="table align-middle table-row-dashed fs-6 gy-4">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-220px">${t.members.col_member}</th>
                        <th class="min-w-120px">${t.members.col_role}</th>
                        <th class="min-w-120px">${t.members.col_joined}</th>
                        <th class="text-end min-w-80px">${t.members.col_actions}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">${rows}</tbody>
            </table>`;
    }

    document.getElementById('btn-add-member').addEventListener('click', () => {
        document.getElementById('member-email').value = '';
        document.getElementById('member-name').value  = '';
        document.getElementById('member-role').value  = 'staff';
        document.getElementById('add-member-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-add-member')).show();
    });

    document.getElementById('btn-save-member').addEventListener('click', async function () {
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

        const res = await api.post(`/agencies/${agencyId}/members`, { email, name: name || null, role });

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

    async function removeMember(userId) {
        if (!confirm(t.members.remove_confirm)) return;
        await api.delete(`/agencies/${agencyId}/members/${userId}`);
        showToast(t.members.removed);
        await loadMembers();
    }

    // ---- Edit ----
    document.getElementById('btn-edit-agency').addEventListener('click', () => {
        if (!agency) return;
        const form = document.getElementById('form-edit-agency-show');
        form.querySelector('[name="name"]').value = agency.name ?? '';
        form.querySelector('[name="email"]').value = agency.email ?? '';
        window.setPhoneValue(form.querySelector('[name="phone"]'), agency.phone ?? '');
        populateCountrySelect(form.querySelector('[name="country"]'), agency.country ?? '');
        populateCurrencySelect(form.querySelector('[name="currency_code"]'), agency.currency_code ?? '');
        form.querySelector('[name="password"]').value = '';
        document.getElementById('edit-agency-show-error').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('modal-edit-agency-show')).show();
    });

    document.getElementById('btn-save-edit-agency-show').addEventListener('click', async function () {
        const btn = this;
        const form = document.getElementById('form-edit-agency-show');
        const errorEl = document.getElementById('edit-agency-show-error');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'), email: fd.get('email') || null,
            phone: fd.get('phone') || null,
            country: fd.get('country') || null,
            currency_code: fd.get('currency_code') || null,
        };

        btn.disabled = true;
        btn.querySelector('.indicator-label').classList.add('d-none');
        btn.querySelector('.indicator-progress').classList.remove('d-none');
        errorEl.classList.add('d-none');

        const showError = (res) => {
            const errors = res?.errors ? Object.values(res.errors).flat().join(' ') : null;
            errorEl.textContent = errors ?? res?.message ?? t.edit_modal.error_generic;
            errorEl.classList.remove('d-none');
        };

        try {
            const res = await api.patch(`/agencies/${agencyId}`, payload);
            if (res.data?.id ?? res.id) {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-agency-show')).hide();
                showToast(t.edit_modal.updated);
                await loadAgency();
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

    // ---- Delete ----
    document.getElementById('btn-delete-agency').addEventListener('click', async () => {
        if (!confirm(t.delete.confirm)) return;
        await api.delete(`/agencies/${agencyId}`);
        showToast(t.delete.done);
        setTimeout(() => window.location.href = '{{ route("admin.agencies.index") }}', 1000);
    });

    // ---- Request drawer ----
    function openRequestDrawer(requestId) {
        const request = agencyRequests.find(r => r.id === requestId);
        if (!request) return;

        const titleEl  = document.getElementById('drawer-request-title');
        const statusEl = document.getElementById('drawer-request-status');
        const bodyEl   = document.getElementById('drawer-request-body');

        titleEl.textContent  = request.title ?? request.destination ?? t.requests.default_title.replace(':id', request.id);
        statusEl.innerHTML   = statusBadge(request);

        const datesStr = request.travel_date_from
            ? formatDate(request.travel_date_from) + (request.travel_date_to ? ' — ' + formatDate(request.travel_date_to) : '')
            : '—';

        const route = renderRoute(request);
        const routeBlock = route
            ? `<div class="mb-5">
                   <div class="text-muted fw-semibold fs-7 text-uppercase mb-2">${t.drawer.route}</div>
                   ${route}
               </div>`
            : '';

        const deadline = request.deadline_at
            ? `<div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.deadline}</div>
                <div class="col-7">${formatDate(request.deadline_at)}</div>
               </div>`
            : '';

        const notes = request.notes
            ? `<div class="separator separator-dashed my-5"></div>
               <h6 class="fw-bold text-gray-700 mb-3">${t.drawer.notes}</h6>
               <div class="text-gray-600 fs-6">${escHtml(request.notes)}</div>`
            : '';

        bodyEl.innerHTML = `
            ${routeBlock}
            <div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.services}</div>
                <div class="col-7"><div class="d-flex flex-wrap gap-1">${renderServices(request)}</div></div>
            </div>
            <div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.pax}</div>
                <div class="col-7 fw-semibold text-gray-800">${request.pax_count ?? '—'}</div>
            </div>
            <div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.dates}</div>
                <div class="col-7">${datesStr}</div>
            </div>
            ${deadline}
            <div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.created}</div>
                <div class="col-7 text-muted">${formatDate(request.created_at)}</div>
            </div>
            <div class="row mb-4">
                <div class="col-5 text-muted fw-semibold">${t.drawer.rfqs}</div>
                <div class="col-7 fw-semibold text-gray-800">${request.rfqs_count ?? 0}</div>
            </div>
            <div class="row mb-6">
                <div class="col-5 text-muted fw-semibold">${t.drawer.proposals}</div>
                <div class="col-7 fw-semibold text-gray-800">${request.proposals_count ?? 0}</div>
            </div>
            ${notes}
            <div class="separator separator-dashed my-5"></div>
            <a href="/admin/requests/${request.id}" class="btn btn-primary w-100">
                <i class="ki-outline ki-arrow-right fs-4 me-1"></i> ${t.drawer.open_request}
            </a>`;

        new bootstrap.Offcanvas(document.getElementById('request-drawer')).show();
    }

    // ---- Helpers ----
    // formatDate / formatCurrency / statusBadge / serviceBadge / countryName / escHtml —
    // общие из partials/js-helpers; currencyName — глобальный из layout.

    // Оператор видит AZN сверху + валюту агентства ниже (если задана).
    function moneyAznAgency(amountAzn, agencyAmount, agencyCurrency) {
        const top = `<div class="fw-bold text-gray-900">${formatCurrency(amountAzn, 'AZN')}</div>`;
        if (agencyAmount != null && agencyCurrency && agencyCurrency !== 'AZN') {
            return top + `<div class="text-muted fs-8">${formatCurrency(agencyAmount, agencyCurrency)}</div>`;
        }
        return top;
    }

    let COUNTRY_NAMES = {};
    fetch('/data/countries.json')
        .then(r => r.json())
        .then(list => {
            COUNTRY_NAMES = Object.fromEntries(list.map(c => [c.code, c.name]));
            document.querySelectorAll('.js-country-select').forEach(sel => populateCountrySelect(sel, sel.value));
            if (agency) renderHeaderCard(agency);
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

    function populateCurrencySelect(sel, selectedCode = '') {
        const current = selectedCode || sel.value;
        sel.innerHTML = `<option value="">${t.select_none}</option>` +
            ACTIVE_CURRENCIES.map(c =>
                `<option value="${c.code}" ${c.code === current ? 'selected' : ''}>${escHtml(c.code)} — ${escHtml(currencyName(c.code, c.name))}</option>`
            ).join('');
    }
</script>
@endpush
