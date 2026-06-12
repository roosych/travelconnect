@extends('layouts.supplier')

@section('title', __('suppliers.cabinet.employees.title'))
@section('page-title', __('suppliers.cabinet.employees.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('suppliers.cabinet.employees.title') }}</li>
@endsection

@section('toolbar-actions')
    @if($supplier)
    <button class="btn btn-primary btn-sm" id="btn-invite">
        <i class="ki-outline ki-plus fs-4 me-1"></i>{{ __('suppliers.cabinet.employees.invite') }}
    </button>
    @endif
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center pt-6 pb-2 border-0">
        <div class="card-title">
            <i class="ki-outline ki-people fs-2x text-primary me-3"></i>
            <div>
                <h3 class="card-label fw-bold fs-4 mb-0">{{ __('suppliers.cabinet.employees.team') }}</h3>
                <div class="text-muted fs-7">{{ $supplier?->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Role chips with counts --}}
    <div class="card-header border-0 pt-2 pb-2">
        <div class="d-flex align-items-center gap-2 flex-wrap" id="role-chips">
            <span class="text-muted fs-7 fw-semibold">{{ __('suppliers.cabinet.employees.loading') }}</span>
        </div>
    </div>

    {{-- Search --}}
    <div class="card-header align-items-center py-4 border-0">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="members-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="{{ __('suppliers.cabinet.employees.search_ph') }}" />
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
                        <th>{{ __('suppliers.cabinet.employees.cols.member') }}</th>
                        <th>{{ __('suppliers.cabinet.employees.cols.email') }}</th>
                        <th>{{ __('suppliers.cabinet.employees.cols.role') }}</th>
                        <th>{{ __('suppliers.cabinet.employees.cols.joined') }}</th>
                        <th class="text-end">{{ __('suppliers.cabinet.employees.cols.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="members-tbody"></tbody>
            </table>
        </div>

        <div id="members-empty" class="text-center py-10 d-none"></div>

    </div>
</div>

{{-- Invite modal --}}
<div class="modal fade" id="modal-invite" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">{{ __('suppliers.cabinet.employees.modal.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold required">{{ __('suppliers.cabinet.employees.modal.email') }}</label>
                    <input type="email" id="invite-email" class="form-control form-control-solid"
                           placeholder="ivanov@example.com" />
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('suppliers.cabinet.employees.modal.name') }}</label>
                    <input type="text" id="invite-name" class="form-control form-control-solid"
                           placeholder="{{ __('suppliers.cabinet.employees.modal.name_ph') }}" />
                </div>
                <div>
                    <label class="form-label fw-semibold required">{{ __('suppliers.cabinet.employees.modal.role') }}</label>
                    <select id="invite-role" class="form-select form-select-solid">
                        <option value="manager">{{ __('suppliers.cabinet.employees.roles.manager') }}</option>
                        <option value="staff">{{ __('suppliers.cabinet.employees.roles.staff') }}</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ __('suppliers.cabinet.employees.modal.cancel') }}</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-invite-save">
                    <span class="indicator-label">{{ __('suppliers.cabinet.employees.modal.invite') }}</span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-1"></span>{{ __('suppliers.cabinet.employees.modal.sending') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const supplierId = {{ $supplier?->id ?? 'null' }};
const currentUserId = {{ auth()->id() }};

const L = @json(__('suppliers.cabinet.employees'));

const roleLabel = L.roles;
const roleBadge = {
    owner:   'badge-light-primary',
    manager: 'badge-light-primary',
    staff:   'badge-light-secondary',
};
// Чипы-роли: «Все» + роли в фиксированном порядке, с цветом и множественным лейблом (из lang).
const ROLE_CHIPS = [
    { role: 'owner',   label: L.roles_pl.owner,   color: 'primary'   },
    { role: 'manager', label: L.roles_pl.manager, color: 'info'      },
    { role: 'staff',   label: L.roles_pl.staff,   color: 'secondary' },
];

let allMembers  = [];
let searchQuery = '';
let activeRole  = '';

async function loadMembers() {
    if (!supplierId) {
        document.getElementById('members-loading').classList.add('d-none');
        document.getElementById('role-chips').innerHTML = '';
        allMembers = [];
        renderAll();
        return;
    }
    try {
        const data = await api.get(`/suppliers/${supplierId}/members`);
        allMembers = data.data ?? [];
        renderAll();
    } catch (err) {
        console.error('[employees] loadMembers error:', err);
        showToast(L.toast.load_err, 'error');
    } finally {
        document.getElementById('members-loading').classList.add('d-none');
    }
}

// Сотрудники под текущий поиск (без фильтра роли) — основа для счётчиков чипов.
function baseFiltered() {
    const q = searchQuery.trim().toLowerCase();
    if (!q) return allMembers;
    return allMembers.filter(m =>
        (m.name ?? '').toLowerCase().includes(q) ||
        (m.email ?? '').toLowerCase().includes(q)
    );
}

function renderChips() {
    const base = baseFiltered();
    const counts = {};
    base.forEach(m => { counts[m.role] = (counts[m.role] ?? 0) + 1; });

    const defs = [{ role: '', label: L.chip_all, color: 'dark', n: base.length }];
    ROLE_CHIPS.forEach(c => {
        if (allMembers.some(m => m.role === c.role)) {
            defs.push({ role: c.role, label: c.label, color: c.color, n: counts[c.role] ?? 0 });
        }
    });

    document.getElementById('role-chips').innerHTML = defs.map(c => {
        const active = c.role === activeRole;
        const cls = active ? `badge-${c.color}` : `badge-light-${c.color}`;
        return `<span class="badge ${cls} fs-7 py-2 px-3 cursor-pointer"
                      onclick="setRole('${c.role}')">${escHtml(c.label)}: ${c.n}</span>`;
    }).join('');
}

function setRole(role) {
    activeRole = role;
    renderAll();
}

function resetFilters() {
    searchQuery = '';
    activeRole  = '';
    document.getElementById('members-search').value = '';
    renderAll();
}

function renderAll() {
    renderChips();
    const base = baseFiltered();
    renderMembers(activeRole ? base.filter(m => m.role === activeRole) : base);
}

function renderMembers(list) {
    const tbody = document.getElementById('members-tbody');
    const empty = document.getElementById('members-empty');
    tbody.innerHTML = '';

    if (!list.length) {
        document.getElementById('members-table').classList.add('d-none');
        empty.innerHTML = allMembers.length
            ? `<i class="ki-outline ki-magnifier fs-3x text-muted mb-4 d-block"></i>
               <div class="text-muted fs-6 mb-4">${L.empty.not_found}</div>
               <button class="btn btn-light btn-sm" onclick="resetFilters()">
                   <i class="ki-outline ki-arrows-circle fs-5 me-1"></i>${L.empty.reset}
               </button>`
            : `<i class="ki-outline ki-people fs-3x text-muted mb-4 d-block"></i>
               <div class="text-muted fs-6">${L.empty.none}</div>`;
        empty.classList.remove('d-none');
        return;
    }

    empty.classList.add('d-none');
    document.getElementById('members-table').classList.remove('d-none');

    list.forEach(m => {
        const initials = m.name.trim().split(/\s+/).map(w => w[0]?.toUpperCase() ?? '').slice(0, 2).join('');
        const avatar   = m.avatar_url
            ? `<img src="${m.avatar_url}" class="rounded-circle" width="35" height="35" />`
            : `<div class="symbol-label bg-light-primary text-primary fw-bold fs-7 w-35px h-35px d-flex align-items-center justify-content-center rounded-circle">${initials}</div>`;

        const isOwner = m.role === 'owner';
        const isSelf  = m.id === currentUserId;
        const actions = (isOwner || isSelf) ? '' :
            `<button class="btn btn-sm btn-icon btn-light-danger btn-remove" data-id="${m.id}" title="${L.remove}">
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
                <td><span class="text-muted fs-7">${formatDate(m.joined_at)}</span></td>
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
        await api.delete(`/suppliers/${supplierId}/members/${userId}`);
        showToast(L.toast.removed);
        loadMembers();
    } catch (err) {
        showToast(err?.message ?? L.toast.remove_err, 'error');
    }
}

let membersSearchDebounce = null;
document.getElementById('members-search')?.addEventListener('input', function () {
    clearTimeout(membersSearchDebounce);
    const v = this.value;
    membersSearchDebounce = setTimeout(() => { searchQuery = v; renderAll(); }, 150);
});

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
        await api.post(`/suppliers/${supplierId}/members`, { email, name, role });
        bootstrap.Modal.getInstance(document.getElementById('modal-invite')).hide();
        showToast(L.toast.invited);
        loadMembers();
    } catch (err) {
        showToast(err?.message ?? L.toast.invite_err, 'error');
    } finally {
        this.disabled = false;
        this.querySelector('.indicator-label').classList.remove('d-none');
        this.querySelector('.indicator-progress').classList.add('d-none');
    }
});

loadMembers();
</script>
@endpush
