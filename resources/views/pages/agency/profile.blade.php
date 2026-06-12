@extends('layouts.agency')

@section('title', 'Профиль')
@section('page-title', 'Профиль')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Профиль</li>
@endsection

@php
    $__words = array_values(array_filter(explode(' ', trim($agencyName ?? ''))));
    $__ini   = count($__words) >= 2
        ? mb_strtoupper(mb_substr($__words[0], 0, 1) . mb_substr(end($__words), 0, 1))
        : mb_strtoupper(mb_substr($agencyName ?? '?', 0, 2));
@endphp

@section('content')

<div class="card card-flush">

    {{-- Tabs: Информация · Аватар · Безопасность --}}
    <div class="card-header card-header-stretch border-bottom">
        <div class="card-toolbar m-0">
            <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tab-info" role="tab">
                        <i class="ki-outline ki-profile-circle fs-4"></i>Информация
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tab-avatar" role="tab">
                        <i class="ki-outline ki-picture fs-4"></i>Аватар
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tab-security" role="tab">
                        <i class="ki-outline ki-lock fs-4"></i>Безопасность
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ── Информация ─────────────────────────────────────────── --}}
            <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                <form id="profile-form" class="mw-500px" novalidate>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Имя</label>
                        <input type="text" id="pf-name" class="form-control form-control-solid"
                               value="{{ $user->name }}" maxlength="255" autocomplete="name" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Email</label>
                        <input type="email" id="pf-email" class="form-control form-control-solid"
                               value="{{ $user->email }}" maxlength="255" autocomplete="email" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Телефон</label>
                        <input type="text" id="pf-phone" class="form-control form-control-solid js-phone"
                               value="{{ $user->phone }}" maxlength="40" placeholder="+994 ..." autocomplete="tel" />
                    </div>
                    <div id="profile-error" class="alert alert-danger mt-4 d-none"></div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" id="btn-save-profile" class="btn btn-primary btn-sm">
                            <span class="indicator-label"><i class="ki-outline ki-check fs-4 me-1"></i>Сохранить</span>
                            <span class="indicator-progress d-none">Сохранение... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Аватар ─────────────────────────────────────────────── --}}
            <div class="tab-pane fade" id="tab-avatar" role="tabpanel">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-6"
                     data-agency-id="{{ $agencyId }}">
                    <div class="position-relative">
                        <div id="avatar-preview" class="symbol symbol-125px symbol-circle">
                            @if($agencyAvatar)
                                <img id="avatar-img" src="{{ $agencyAvatar }}" alt="Логотип" class="object-fit-cover" />
                            @else
                                <span id="avatar-initials" class="symbol-label bg-light-success text-success fw-bold fs-2x">{{ $__ini }}</span>
                            @endif
                        </div>
                        <span id="avatar-spinner" class="position-absolute top-50 start-50 translate-middle d-none">
                            <span class="spinner-border text-success"></span>
                        </span>
                    </div>

                    <div class="flex-grow-1">
                        <div class="fw-bold fs-4 text-gray-800">{{ $agencyName ?? '—' }}</div>
                        <div class="text-muted fs-7 mb-4">Логотип отображается в шапке кабинета. JPG, PNG или WEBP, до 2&nbsp;МБ.</div>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-avatar-upload" class="btn btn-sm btn-light-success">
                                <i class="ki-outline ki-cloud-add fs-5 me-1"></i>Загрузить логотип
                            </button>
                            <button type="button" id="btn-avatar-remove"
                                    class="btn btn-sm btn-light-danger {{ $agencyAvatar ? '' : 'd-none' }}">
                                <i class="ki-outline ki-trash fs-5 me-1"></i>Удалить
                            </button>
                        </div>
                        <input type="file" id="avatar-file" accept="image/jpeg,image/png,image/webp" class="d-none" />
                    </div>
                </div>
            </div>

            {{-- ── Безопасность ───────────────────────────────────────── --}}
            <div class="tab-pane fade" id="tab-security" role="tabpanel">
                <div class="mw-500px">
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Текущий пароль</label>
                        <input type="password" id="pwd-current" class="form-control form-control-solid"
                               placeholder="Введите текущий пароль" autocomplete="current-password" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">Новый пароль</label>
                        <input type="password" id="pwd-new" class="form-control form-control-solid"
                               placeholder="Минимум 8 символов" autocomplete="new-password" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold required">Повторите новый пароль</label>
                        <input type="password" id="pwd-confirm" class="form-control form-control-solid"
                               placeholder="Повторите новый пароль" autocomplete="new-password" />
                    </div>
                    <div class="d-flex justify-content-end mt-5">
                        <button id="btn-save-password" class="btn btn-warning btn-sm">
                            <span class="indicator-label"><i class="ki-outline ki-lock fs-4 me-1"></i>Изменить пароль</span>
                            <span class="indicator-progress d-none">Сохранение... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const byId = id => document.getElementById(id);

    function setBtnLoading(btn, on) {
        btn.disabled = on;
        btn.querySelector('.indicator-label').classList.toggle('d-none', on);
        btn.querySelector('.indicator-progress').classList.toggle('d-none', !on);
    }

    // ── Личные данные ──────────────────────────────────────────────────────
    byId('profile-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = byId('btn-save-profile');
        const err = byId('profile-error');
        const payload = {
            name:  byId('pf-name').value.trim(),
            email: byId('pf-email').value.trim(),
            phone: byId('pf-phone').value.trim() || null,
        };
        if (!payload.name || !payload.email) {
            err.textContent = 'Имя и email обязательны.';
            err.classList.remove('d-none');
            return;
        }
        setBtnLoading(btn, true);
        err.classList.add('d-none');
        try {
            const res = await api.patch('/me', payload);
            if (res.data) {
                showToast('Профиль обновлён.');
            } else {
                err.textContent = res.message ?? 'Не удалось сохранить.';
                err.classList.remove('d-none');
            }
        } catch (ex) {
            const msg = ex?.errors ? Object.values(ex.errors).flat().join(' ') : (ex?.message ?? 'Ошибка сохранения.');
            err.textContent = msg;
            err.classList.remove('d-none');
        } finally {
            setBtnLoading(btn, false);
        }
    });

    // ── Пароль ─────────────────────────────────────────────────────────────
    byId('btn-save-password').addEventListener('click', async function () {
        const btn     = this;
        const current = byId('pwd-current').value;
        const pwd     = byId('pwd-new').value;
        const confirm = byId('pwd-confirm').value;

        if (!current || !pwd || !confirm) { showToast('Заполните все поля пароля', 'error'); return; }
        if (pwd.length < 8)               { showToast('Новый пароль должен содержать минимум 8 символов', 'error'); return; }
        if (pwd !== confirm)              { showToast('Пароли не совпадают', 'error'); return; }

        setBtnLoading(btn, true);
        try {
            await api.patch('/me/password', {
                current_password:      current,
                password:              pwd,
                password_confirmation: confirm,
            });
            showToast('Пароль успешно изменён.');
            byId('pwd-current').value = byId('pwd-new').value = byId('pwd-confirm').value = '';
        } catch (err) {
            showToast(err?.message ?? 'Не удалось изменить пароль', 'error');
        } finally {
            setBtnLoading(btn, false);
        }
    });

    // ── Аватар агентства ───────────────────────────────────────────────────
    (function () {
        const wrap = document.querySelector('[data-agency-id]');
        if (!wrap) return;
        const agencyId  = wrap.dataset.agencyId;
        const fileInput = byId('avatar-file');
        const btnUpload = byId('btn-avatar-upload');
        const btnRemove = byId('btn-avatar-remove');
        const preview   = byId('avatar-preview');
        const spinner   = byId('avatar-spinner');
        const token     = localStorage.getItem('auth_token');

        const setBusy = (on) => {
            spinner.classList.toggle('d-none', !on);
            btnUpload.disabled = on;
            btnRemove.disabled = on;
        };
        const showImage = (url) => {
            preview.innerHTML = `<img id="avatar-img" src="${url}" alt="Логотип" class="object-fit-cover" />`;
            btnRemove.classList.remove('d-none');
        };

        btnUpload.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                showToast('Файл больше 2 МБ.', 'error');
                this.value = '';
                return;
            }
            const fd = new FormData();
            fd.append('avatar', file);
            setBusy(true);
            try {
                const res = await fetch(`/api/agencies/${agencyId}/avatar`, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok && data.avatar_url) {
                    showImage(data.avatar_url);
                    showToast('Логотип обновлён.');
                } else {
                    showToast(data.message ?? 'Не удалось загрузить логотип.', 'error');
                }
            } catch {
                showToast('Ошибка загрузки. Попробуйте снова.', 'error');
            } finally {
                setBusy(false);
                this.value = '';
            }
        });

        btnRemove.addEventListener('click', async function () {
            if (!confirm('Удалить логотип агентства?')) return;
            setBusy(true);
            try {
                const res = await fetch(`/api/agencies/${agencyId}/avatar`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    location.reload();
                } else {
                    showToast('Не удалось удалить логотип.', 'error');
                }
            } catch {
                showToast('Ошибка. Попробуйте снова.', 'error');
            } finally {
                setBusy(false);
            }
        });
    })();
</script>
@endpush
