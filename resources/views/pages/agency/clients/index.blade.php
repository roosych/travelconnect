@extends('layouts.agency')
@section('title', 'Клиенты')
@section('page-title', 'Клиенты')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Клиенты</li>
@endsection

@section('toolbar-actions')
    @if($agency)
    <button class="btn btn-success btn-sm" id="btn-add-client">
        <i class="ki-outline ki-plus fs-4 me-1"></i>Добавить клиента
    </button>
    @endif
@endsection

@section('content')

<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-3">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="client-search"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Поиск клиентов..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">

        <div id="clients-loading" class="text-center py-10">
            <span class="spinner-border text-primary"></span>
        </div>

        <div id="clients-table" class="d-none">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-muted fs-7 text-uppercase">
                        <th>Имя</th>
                        <th>Контакты</th>
                        <th>Гражданство</th>
                        <th>Возраст</th>
                        <th>Добавлен</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody id="clients-tbody"></tbody>
            </table>
        </div>

        <div id="clients-empty" class="text-center py-10 d-none">
            <i class="ki-outline ki-profile-user fs-3x text-muted mb-4 d-block"></i>
            <div class="text-muted fs-6 mb-3">Нет клиентов</div>
            <button class="btn btn-sm btn-light-success" id="btn-add-first">Добавить первого клиента</button>
        </div>

    </div>
</div>

{{-- Client modal --}}
<div class="modal fade" id="modal-client" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-client-title">Добавить клиента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="client-id" />
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold required">Имя</label>
                        <input type="text" id="client-name" class="form-control form-control-solid"
                               placeholder="Иван Иванов" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" id="client-email" class="form-control form-control-solid"
                               placeholder="ivan@example.com" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Телефон</label>
                        <input type="text" id="client-phone" class="form-control form-control-solid js-phone"
                               placeholder="+994 50 000 00 00" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Гражданство (ISO)</label>
                        <input type="text" id="client-nationality" class="form-control form-control-solid"
                               placeholder="AZ" maxlength="2" style="text-transform:uppercase" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Дата рождения</label>
                        <input type="date" id="client-dob" class="form-control form-control-solid" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Номер паспорта</label>
                        <input type="text" id="client-passport" class="form-control form-control-solid"
                               placeholder="AA1234567" />
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Примечания</label>
                        <textarea id="client-notes" class="form-control form-control-solid" rows="2"
                                  placeholder="Дополнительная информация..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-client-save">
                    <span class="indicator-label">Сохранить</span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle me-1"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const agencyId  = {{ $agency?->id ?? 'null' }};
let allClients  = [];

async function loadClients() {
    if (!agencyId) return;
    try {
        const data = await api.get(`/clients?agency_id=${agencyId}`);
        allClients = Array.isArray(data?.data) ? data.data : (data ?? []);
        renderClients(allClients);
    } catch {
        showToast('Не удалось загрузить клиентов', 'error');
    } finally {
        document.getElementById('clients-loading').classList.add('d-none');
    }
}

function renderClients(list) {
    const tbody = document.getElementById('clients-tbody');
    tbody.innerHTML = '';

    if (!list.length) {
        document.getElementById('clients-empty').classList.remove('d-none');
        document.getElementById('clients-table').classList.add('d-none');
        return;
    }

    document.getElementById('clients-table').classList.remove('d-none');
    document.getElementById('clients-empty').classList.add('d-none');

    list.forEach(c => {
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td><span class="fw-semibold text-gray-800">${c.name}</span></td>
                <td>
                    <div class="text-gray-700 fs-7">${c.email ?? '—'}</div>
                    <div class="text-muted fs-8">${c.phone ?? ''}</div>
                </td>
                <td><span class="text-muted fs-7">${c.nationality ?? '—'}</span></td>
                <td><span class="text-muted fs-7">${c.age != null ? c.age + ' лет' : '—'}</span></td>
                <td><span class="text-muted fs-7">${c.created_at ?? '—'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-icon btn-light-primary btn-edit me-1" data-id="${c.id}" title="Редактировать">
                        <i class="ki-outline ki-pencil fs-5"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light-danger btn-delete" data-id="${c.id}" title="Удалить">
                        <i class="ki-outline ki-trash fs-5"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => openEdit(parseInt(btn.dataset.id)));
    });
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => deleteClient(parseInt(btn.dataset.id)));
    });
}

function openModal(title = 'Добавить клиента') {
    document.getElementById('modal-client-title').textContent = title;
    new bootstrap.Modal(document.getElementById('modal-client')).show();
}

function clearModal() {
    ['client-id','client-name','client-email','client-phone',
     'client-nationality','client-dob','client-passport','client-notes']
        .forEach(id => { document.getElementById(id).value = ''; });
}

function openEdit(id) {
    const c = allClients.find(x => x.id === id);
    if (!c) return;
    document.getElementById('client-id').value          = c.id;
    document.getElementById('client-name').value        = c.name ?? '';
    document.getElementById('client-email').value       = c.email ?? '';
    window.setPhoneValue('#client-phone', c.phone ?? '');
    document.getElementById('client-nationality').value = c.nationality ?? '';
    document.getElementById('client-dob').value         = c.date_of_birth ?? '';
    document.getElementById('client-passport').value    = c.passport_number ?? '';
    document.getElementById('client-notes').value       = c.notes ?? '';
    openModal('Редактировать клиента');
}

async function deleteClient(id) {
    if (!confirm('Удалить клиента?')) return;
    try {
        await api.delete(`/clients/${id}`);
        showToast('Клиент удалён');
        loadClients();
    } catch (err) {
        showToast(err?.message ?? 'Не удалось удалить клиента', 'error');
    }
}

document.getElementById('btn-add-client')?.addEventListener('click', () => {
    clearModal();
    openModal('Добавить клиента');
});
document.getElementById('btn-add-first')?.addEventListener('click', () => {
    clearModal();
    openModal('Добавить клиента');
});

document.getElementById('btn-client-save').addEventListener('click', async function () {
    const id      = document.getElementById('client-id').value;
    const name    = document.getElementById('client-name').value.trim();
    const payload = {
        agency_id:       agencyId,
        name:            name,
        email:           document.getElementById('client-email').value.trim() || null,
        phone:           document.getElementById('client-phone').value.trim() || null,
        nationality:     document.getElementById('client-nationality').value.trim().toUpperCase() || null,
        date_of_birth:   document.getElementById('client-dob').value || null,
        passport_number: document.getElementById('client-passport').value.trim() || null,
        notes:           document.getElementById('client-notes').value.trim() || null,
    };

    if (!name) { showToast('Введите имя клиента', 'error'); return; }

    this.disabled = true;
    this.querySelector('.indicator-label').classList.add('d-none');
    this.querySelector('.indicator-progress').classList.remove('d-none');

    try {
        if (id) {
            await api.patch(`/clients/${id}`, payload);
            showToast('Клиент обновлён');
        } else {
            await api.post('/clients', payload);
            showToast('Клиент добавлен');
        }
        bootstrap.Modal.getInstance(document.getElementById('modal-client')).hide();
        loadClients();
    } catch (err) {
        showToast(err?.message ?? 'Не удалось сохранить клиента', 'error');
    } finally {
        this.disabled = false;
        this.querySelector('.indicator-label').classList.remove('d-none');
        this.querySelector('.indicator-progress').classList.add('d-none');
    }
});

// Search filter
document.getElementById('client-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    renderClients(q ? allClients.filter(c =>
        c.name.toLowerCase().includes(q) ||
        (c.email ?? '').toLowerCase().includes(q) ||
        (c.phone ?? '').includes(q)
    ) : allClients);
});

loadClients();
</script>
@endpush
