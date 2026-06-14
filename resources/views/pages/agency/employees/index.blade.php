@extends('layouts.agency')
@section('title', __('nav.employees'))
@section('page-title', __('nav.employees'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('nav.employees') }}</li>
@endsection

@section('toolbar-actions')
    @if($agency)
    <button class="btn btn-success btn-sm" id="btn-invite">
        <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('employees.invite') }}
    </button>
    @endif
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <i class="ki-outline ki-people fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('employees.team_title') }}</h3>
                <div class="text-muted fs-7">{{ $agency?->name ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">

        <div id="members-loading" class="text-center py-10">
            <span class="spinner-border text-primary"></span>
        </div>

        <div id="members-table" class="d-none">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted fs-7 text-uppercase">
                        <th>{{ __('employees.cols.employee') }}</th>
                        <th>{{ __('employees.cols.email') }}</th>
                        <th>{{ __('employees.cols.role') }}</th>
                        <th>{{ __('employees.cols.added') }}</th>
                        <th class="text-end">{{ __('employees.cols.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="members-tbody"></tbody>
            </table>
        </div>

        <div id="members-empty" class="text-center py-10 d-none">
            <i class="ki-outline ki-people fs-3x text-muted mb-4 d-block"></i>
            <div class="text-muted fs-6">{{ __('employees.empty') }}</div>
        </div>

    </div>
</div>

{{-- Invite modal --}}
<div class="modal fade" id="modal-invite" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('employees.invite') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold required">{{ __('employees.email') }}</label>
                    <input type="email" id="invite-email" class="form-control form-control-solid"
                           placeholder="ivanov@example.com" />
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('employees.modal.name_opt') }}</label>
                    <input type="text" id="invite-name" class="form-control form-control-solid"
                           placeholder="{{ __('employees.modal.name_ph') }}" />
                </div>
                <div>
                    <label class="form-label fw-semibold required">{{ __('employees.role') }}</label>
                    <select id="invite-role" class="form-select form-select-solid">
                        <option value="manager">{{ __('employees.roles.manager') }}</option>
                        <option value="staff">{{ __('employees.roles.staff') }}</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-invite-save">
                    <span class="indicator-label">{{ __('employees.modal.invite') }}</span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-1"></span>{{ __('employees.modal.sending') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const agencyId = {{ $agency?->id ?? 'null' }};

const L = @json(__('employees'));
const roleLabel = { owner: L.roles.owner, manager: L.roles.manager, staff: L.roles.staff };
const roleBadge = {
    owner:   'badge-light-warning',
    manager: 'badge-light-primary',
    staff:   'badge-light-secondary',
};

async function loadMembers() {
    if (!agencyId) return;
    try {
        const data = await api.get(`/agencies/${agencyId}/members`);
        const members = data.data ?? [];
        renderMembers(members);
    } catch {
        showToast(L.toast.load_error, 'error');
    } finally {
        document.getElementById('members-loading').classList.add('d-none');
    }
}

function renderMembers(list) {
    const tbody = document.getElementById('members-tbody');
    tbody.innerHTML = '';

    if (!list.length) {
        document.getElementById('members-empty').classList.remove('d-none');
        document.getElementById('members-table').classList.add('d-none');
        return;
    }

    document.getElementById('members-table').classList.remove('d-none');

    const currentUserId = {{ auth()->id() }};

    list.forEach(m => {
        const initials = m.name.trim().split(/\s+/).map(w => w[0]?.toUpperCase() ?? '').slice(0, 2).join('');
        const avatar   = m.avatar_url
            ? `<img src="${m.avatar_url}" class="rounded-circle" width="35" height="35" />`
            : `<div class="symbol-label bg-light-success text-success fw-bold fs-7 w-35px h-35px d-flex align-items-center justify-content-center rounded-circle">${initials}</div>`;

        const isOwner  = m.role === 'owner';
        const isSelf   = m.id === currentUserId;
        const actions  = (isOwner || isSelf) ? '' :
            `<button class="btn btn-sm btn-icon btn-light-danger btn-remove" data-id="${m.id}" title="${@json(__('common.delete'))}">
                <i class="ki-outline ki-trash fs-5"></i>
             </button>`;

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        ${avatar}
                        <span class="fw-semibold text-gray-800">${m.name}</span>
                    </div>
                </td>
                <td><span class="text-muted fs-7">${m.email ?? '—'}</span></td>
                <td><span class="badge ${roleBadge[m.role] ?? 'badge-light-secondary'}">${roleLabel[m.role] ?? m.role}</span></td>
                <td><span class="text-muted fs-7">${m.joined_at ?? '—'}</span></td>
                <td class="text-end">${actions}</td>
            </tr>
        `);
    });

    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', () => removeMember(parseInt(btn.dataset.id)));
    });
}

async function removeMember(userId) {
    if (!confirm(L.toast.remove_confirm)) return;
    try {
        await api.delete(`/agencies/${agencyId}/members/${userId}`);
        showToast(L.toast.removed);
        loadMembers();
    } catch (err) {
        showToast(err?.message ?? L.toast.remove_error, 'error');
    }
}

document.getElementById('btn-invite')?.addEventListener('click', () => {
    document.getElementById('invite-email').value = '';
    document.getElementById('invite-name').value  = '';
    document.getElementById('invite-role').value  = 'manager';
    new bootstrap.Modal(document.getElementById('modal-invite')).show();
});

document.getElementById('btn-invite-save').addEventListener('click', async function () {
    const email = document.getElementById('invite-email').value.trim();
    const name  = document.getElementById('invite-name').value.trim();
    const role  = document.getElementById('invite-role').value;

    if (!email) { showToast(L.toast.email_required, 'error'); return; }

    this.disabled = true;
    this.querySelector('.indicator-label').classList.add('d-none');
    this.querySelector('.indicator-progress').classList.remove('d-none');

    try {
        await api.post(`/agencies/${agencyId}/members`, { email, name, role });
        bootstrap.Modal.getInstance(document.getElementById('modal-invite')).hide();
        showToast(L.toast.invited);
        loadMembers();
    } catch (err) {
        showToast(err?.message ?? L.toast.invite_error, 'error');
    } finally {
        this.disabled = false;
        this.querySelector('.indicator-label').classList.remove('d-none');
        this.querySelector('.indicator-progress').classList.add('d-none');
    }
});

loadMembers();
</script>
@endpush
