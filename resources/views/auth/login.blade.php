@extends('layouts.auth')

@section('title', __('auth.page_title'))

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid h-100">

    {{-- Left branding panel --}}
    <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-2 order-lg-1"
         style="background-image: linear-gradient(145deg, rgba(13,19,40,.35) 0%, rgba(13,19,40,.10) 100%), url('{{ asset('ui_template/assets/media/misc/auth-bg.png') }}'); background-size: cover; background-position: center;">
        <div class="d-flex flex-column flex-center py-15 px-10 w-100">

            {{-- Brand --}}
            <div class="text-center mb-12">
                <h1 class="text-white fw-bold fs-2qx mb-2 lh-1">{{ config('app.name') }}</h1>
                {{-- слоган скрыт
                <p class="text-white opacity-60 fs-5 mb-0">Туры. Партнёры. Результат.</p>
                --}}
            </div>

            {{-- Logo --}}
            <div class="d-flex flex-center w-100">
                <img src="{{ asset('ui_template/assets/media/auth/casp_transparent.png') }}"
                     alt="{{ config('app.name') }}" class="img-fluid" style="max-width:270px;width:70%;" />
            </div>

        </div>
    </div>

    {{-- Right login form panel --}}
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-1 order-lg-2">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10">

                <div class="text-center mb-10">
                    <h1 class="text-gray-900 fw-bolder mb-3">{{ __('auth.sign_in_title') }}</h1>
                </div>

                <div id="login-alert" class="alert alert-danger d-none align-items-center gap-3 mb-7">
                    <i class="ki-outline ki-cross-circle fs-3 text-danger flex-shrink-0"></i>
                    <span id="login-alert-text"></span>
                </div>

                <form id="login-form" novalidate>

                    <div class="fv-row mb-6">
                        <label class="form-label fw-semibold text-gray-900 fs-6">{{ __('auth.email') }}</label>
                        <input type="email"
                               id="login-email"
                               name="email"
                               placeholder="{{ __('auth.email_placeholder') }}"
                               autocomplete="email"
                               class="form-control form-control-lg form-control-solid"
                               required />
                    </div>

                    <div class="fv-row mb-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold text-gray-900 fs-6 mb-0">{{ __('auth.password') }}</label>
                        </div>
                        <div class="position-relative">
                            <input type="password"
                                   id="login-password"
                                   name="password"
                                   placeholder="{{ __('auth.password_placeholder') }}"
                                   autocomplete="current-password"
                                   class="form-control form-control-lg form-control-solid pe-12"
                                   required />
                            <button type="button" id="toggle-password"
                                    class="btn btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y me-1"
                                    onclick="togglePasswordVisibility()"
                                    tabindex="-1">
                                <i class="ki-outline ki-eye fs-4 text-gray-400" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-8">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="remember-me" />
                            <label class="form-check-label text-gray-600 fs-6" for="remember-me">{{ __('auth.remember_me') }}</label>
                        </div>
                    </div>

                    <div class="d-grid mb-8">
                        <button type="submit" id="login-btn" class="btn btn-primary btn-lg">
                            {{ __('auth.sign_in') }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

        {{-- Language switcher --}}
        @php
            $_curLocale = app()->getLocale();
            $_langs = ['az' => ['az', 'AZ'], 'ru' => ['ru', 'RU'], 'en' => ['gb', 'EN']];
        @endphp
        <div class="d-flex flex-center flex-wrap px-5 mb-8">
            <div class="d-flex fw-semibold fs-base">
                @foreach($_langs as $code => $lang)
                    <a href="{{ route('lang.switch', $code) }}"
                       class="px-4 d-flex align-items-center {{ $_curLocale === $code ? 'text-primary fw-bold' : 'text-gray-500' }}">
                        <span class="symbol symbol-20px me-1"><img class="rounded-1" src="{{ asset('flags/' . $lang[0] . '.svg') }}" alt=""></span>
                        {{ $lang[1] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="d-flex flex-center flex-wrap px-5 pb-4">
            <div class="text-gray-500 fw-semibold fs-7 text-center">
                &copy; {{ date('Y') }} CASPIREX
            </div>
        </div>
    </div>

</div>
@endsection

@php
    $jsI18n = [
        'signing_in'          => __('auth.signing_in'),
        'sign_in'             => __('auth.sign_in'),
        'invalid_credentials' => __('auth.invalid_credentials'),
        'connection_error'    => __('auth.connection_error'),
    ];
@endphp
@push('scripts')
<script>
    // Локализованные строки для JS (сервер уже знает активную локаль).
    const I18N = @json($jsI18n);

    function togglePasswordVisibility() {
        const input = document.getElementById('login-password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ki-outline ki-eye-slash fs-4 text-gray-400';
        } else {
            input.type = 'password';
            icon.className = 'ki-outline ki-eye fs-4 text-gray-400';
        }
    }

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn      = document.getElementById('login-btn');
        const alertEl  = document.getElementById('login-alert');
        const alertTxt = document.getElementById('login-alert-text');

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm align-middle me-2"></span>${I18N.signing_in}`;
        alertEl.classList.add('d-none');
        alertEl.classList.remove('d-flex');

        const email    = document.getElementById('login-email').value.trim();
        const password = document.getElementById('login-password').value;

        let redirecting = false;

        try {
            const res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ email, password }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                redirecting = true;
                const role = data.data?.user?.role;
                if (role === 'agency')         window.location.href = '/agency/dashboard';
                else if (role === 'supplier')  window.location.href = '/supplier/dashboard';
                else                           window.location.href = '/admin/dashboard';
                return;
            }

            alertTxt.textContent = data.message ?? I18N.invalid_credentials;
            alertEl.classList.remove('d-none');
            alertEl.classList.add('d-flex');

        } catch (err) {
            alertTxt.textContent = I18N.connection_error;
            alertEl.classList.remove('d-none');
            alertEl.classList.add('d-flex');
        } finally {
            if (!redirecting) {
                btn.disabled = false;
                btn.innerHTML = I18N.sign_in;
            }
        }
    });

    // Allow Enter key from email field to move to password
    document.getElementById('login-email').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('login-password').focus();
        }
    });
</script>
@endpush
