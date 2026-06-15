@extends($layout)

@section('title', __('profile.title'))
@section('page-title', __('profile.title'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">{{ __('profile.title') }}</li>
@endsection

@section('content')

<div class="row g-6">

    {{-- Personal data --}}
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header py-5">
                <div class="card-title">
                    <i class="ki-outline ki-profile-circle fs-2x text-primary me-3"></i>
                    <h3 class="card-label fw-bold fs-4 mb-0">{{ __('profile.personal_data') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <form id="profile-form" novalidate>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">{{ __('profile.name') }}</label>
                        <input type="text" id="pf-name" class="form-control form-control-solid"
                               value="{{ $user->name }}" maxlength="255" autocomplete="name" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-semibold required">{{ __('profile.email') }}</label>
                        <input type="email" id="pf-email" class="form-control form-control-solid"
                               value="{{ $user->email }}" maxlength="255" autocomplete="email" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">{{ __('profile.phone') }}</label>
                        <input type="text" id="pf-phone" class="form-control form-control-solid js-phone"
                               value="{{ $user->phone }}" maxlength="40" placeholder="+994 ..." autocomplete="tel" />
                    </div>
                    <div id="profile-error" class="alert alert-danger mt-4 d-none"></div>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" id="btn-save-profile" class="btn btn-primary btn-sm">
                            <span class="indicator-label"><i class="ki-outline ki-check fs-4 me-1"></i>{{ __('common.save') }}</span>
                            <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Security / password --}}
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header py-5">
                <div class="card-title">
                    <i class="ki-outline ki-lock fs-2x text-warning me-3"></i>
                    <h3 class="card-label fw-bold fs-4 mb-0">{{ __('profile.security') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="mb-5">
                    <label class="form-label fw-semibold required">{{ __('profile.current_password') }}</label>
                    <input type="password" id="pwd-current" class="form-control form-control-solid"
                           placeholder="{{ __('profile.current_password_ph') }}" autocomplete="current-password" />
                </div>
                <div class="mb-5">
                    <label class="form-label fw-semibold required">{{ __('profile.new_password') }}</label>
                    <input type="password" id="pwd-new" class="form-control form-control-solid"
                           placeholder="{{ __('profile.new_password_ph') }}" autocomplete="new-password" />
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold required">{{ __('profile.confirm_password') }}</label>
                    <input type="password" id="pwd-confirm" class="form-control form-control-solid"
                           placeholder="{{ __('profile.confirm_password_ph') }}" autocomplete="new-password" />
                </div>
                <div class="d-flex justify-content-end mt-5">
                    <button id="btn-save-password" class="btn btn-warning btn-sm">
                        <span class="indicator-label"><i class="ki-outline ki-lock fs-4 me-1"></i>{{ __('profile.change_password') }}</span>
                        <span class="indicator-progress">{{ __('common.saving') }} <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications (hidden for agencies — moved to Настройки → Уведомления) --}}
    @if($showNotifications ?? true)
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header py-5">
                <div class="card-title">
                    <i class="ki-outline ki-notification-status fs-2x text-info me-3"></i>
                    <h3 class="card-label fw-bold fs-4 mb-0">{{ __('common.notifications') }}</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                @include('partials.notification-settings', ['accent' => 'info'])
            </div>
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
@if($showNotifications ?? true)
@include('partials.notification-settings-scripts')
@endif
<script>
    const tp = @json(__('profile'));
    const byId = id => document.getElementById(id);

    // ── Personal data ──────────────────────────────────────────────────────
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
            err.textContent = tp.err_required;
            err.classList.remove('d-none');
            return;
        }
        btnLoading(btn, true);
        err.classList.add('d-none');
        try {
            const res = await api.patch('/me', payload);
            if (res.data) {
                showToast(tp.toast_saved);
            } else {
                err.textContent = res.message ?? tp.err_save;
                err.classList.remove('d-none');
            }
        } catch (ex) {
            const msg = ex?.errors ? Object.values(ex.errors).flat().join(' ') : (ex?.message ?? tp.err_save);
            err.textContent = msg;
            err.classList.remove('d-none');
        } finally {
            btnLoading(btn, false);
        }
    });

    // ── Password ───────────────────────────────────────────────────────────
    byId('btn-save-password').addEventListener('click', async function () {
        const btn     = this;
        const current = byId('pwd-current').value;
        const pwd     = byId('pwd-new').value;
        const confirm = byId('pwd-confirm').value;

        if (!current || !pwd || !confirm) { showToast(tp.pwd_fill_all, 'error'); return; }
        if (pwd.length < 8)               { showToast(tp.pwd_min, 'error'); return; }
        if (pwd !== confirm)              { showToast(tp.pwd_mismatch, 'error'); return; }

        btnLoading(btn, true);
        try {
            await api.patch('/me/password', {
                current_password:      current,
                password:              pwd,
                password_confirmation: confirm,
            });
            showToast(tp.pwd_changed);
            byId('pwd-current').value = byId('pwd-new').value = byId('pwd-confirm').value = '';
        } catch (err) {
            showToast(err?.message ?? tp.pwd_error, 'error');
        } finally {
            btnLoading(btn, false);
        }
    });
</script>
@endpush
