<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Supplier Portal') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('ui_template/assets/media/logos/fav-dark.png') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('ui_template/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('ui_template/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('ui_template/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>
<body id="kt_app_body"
    data-kt-app-header-stacked="true"
    data-kt-app-header-primary-enabled="true"
    data-kt-app-header-secondary-enabled="true"
    data-kt-app-toolbar-enabled="true"
    class="app-default">

    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            {{-- ─── Stacked Header ─────────────────────────────────────────── --}}
            <div id="kt_app_header" class="app-header">

                {{-- Primary header: logo + user navbar --}}
                <div class="app-header-primary"
                     data-kt-sticky="true"
                     data-kt-sticky-name="app-header-primary"
                     data-kt-sticky-offset="{default: '200px', lg: '300px'}"
                     data-kt-sticky-animation="false"
                     data-kt-sticky-class="shadow-sm"
                     data-kt-sticky-release-class="shadow-none">
                    <div class="app-container container-xxl d-flex align-items-stretch justify-content-between"
                         id="kt_app_header_primary_container">

                        {{-- Logo (desktop) --}}
                        <div class="d-none d-lg-flex align-items-center me-4">
                            <a href="{{ route('supplier.dashboard') }}" class="d-flex align-items-center text-decoration-none gap-3">
                                <span class="text-gray-800 fw-bolder fs-2">{{ config('app.name') }}</span>
                                <span class="badge badge-light-primary fs-7 fw-semibold">Supplier</span>
                            </a>
                        </div>

                        {{-- Mobile: menu toggle + brand --}}
                        <div class="d-flex d-lg-none align-items-center me-auto gap-2">
                            <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                                 id="kt_app_header_menu_toggle">
                                <i class="ki-outline ki-abstract-14 fs-2"></i>
                            </div>
                            <a href="{{ route('supplier.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                                <span class="d-flex align-items-center justify-content-center
                                             w-35px h-35px rounded-2 bg-primary text-white fw-bold fs-6">
                                    SP
                                </span>
                            </a>
                        </div>

                        {{-- Navbar: user menu --}}
                        <div class="app-navbar flex-shrink-0" id="kt_app_header_navbar">

                            {{-- Notifications bell --}}
                            @include('partials.notification-bell')

                            {{-- User menu --}}
                            <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                                @php
                                    $__u        = auth()->user();
                                    $__supplier = $__u?->suppliers()->first();
                                    $__avatar   = $__supplier?->getFirstMediaUrl('avatar') ?: null;
                                    $__words    = array_values(array_filter(explode(' ', trim($__supplier->name ?? $__u->name ?? ''))));
                                    $__ini      = count($__words) >= 2
                                        ? strtoupper(mb_substr($__words[0], 0, 1) . mb_substr(end($__words), 0, 1))
                                        : strtoupper(mb_substr($__supplier->name ?? $__u->name ?? '?', 0, 2));
                                @endphp
                                <div class="cursor-pointer symbol symbol-circle symbol-35px symbol-md-40px"
                                     data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                     data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    @if($__avatar)
                                        <img src="{{ $__avatar }}" alt="{{ __('suppliers.cabinet.profile.logo_alt') }}" class="object-fit-cover" />
                                    @else
                                        <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">{{ $__ini }}</div>
                                    @endif
                                </div>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded
                                            menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                     data-kt-menu="true">

                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-circle symbol-50px me-5">
                                                @if($__avatar)
                                                    <img src="{{ $__avatar }}" alt="{{ __('suppliers.cabinet.profile.logo_alt') }}" class="object-fit-cover" />
                                                @else
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-3">{{ $__ini }}</div>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold fs-5 text-gray-800 mb-1">{{ $__u->name ?? '' }}</div>
                                                <span class="text-muted text-hover-primary fs-7">{{ $__u->email ?? '' }}</span>
                                                <span class="badge badge-light-primary fw-bold fs-8 mt-1">{{ __('common.role_supplier') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-2"></div>

                                    <div class="menu-item px-5">
                                        <a href="{{ route('supplier.profile') }}"
                                           class="menu-link px-5 {{ request()->routeIs('supplier.profile') ? 'active' : '' }}">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-outline ki-profile-circle fs-3"></i>
                                            </span>
                                            <span class="menu-title">{{ __('common.profile') }}</span>
                                        </a>
                                    </div>

                                    {{-- Раздел «Валюты» временно скрыт: валюту задаёт админ, для поставщика это read-only витрина без действий. Вернуть — раскомментировать (роут supplier.currency на месте).
                                    <div class="menu-item px-5">
                                        <a href="{{ route('supplier.currency') }}"
                                           class="menu-link px-5 {{ request()->routeIs('supplier.currency') ? 'active' : '' }}">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-outline ki-dollar fs-3"></i>
                                            </span>
                                            <span class="menu-title">{{ __('common.currency') }}</span>
                                        </a>
                                    </div>
                                    --}}

                                    <div class="menu-item px-5">
                                        <a href="{{ route('supplier.service-types') }}"
                                           class="menu-link px-5 {{ request()->routeIs('supplier.service-types') ? 'active' : '' }}">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-outline ki-category fs-3"></i>
                                            </span>
                                            <span class="menu-title">{{ __('common.service_types') }}</span>
                                        </a>
                                    </div>

                                    <div class="menu-item px-5">
                                        <a href="{{ route('supplier.notifications') }}"
                                           class="menu-link px-5 {{ request()->routeIs('supplier.notifications') ? 'active' : '' }}">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-outline ki-notification-status fs-3"></i>
                                            </span>
                                            <span class="menu-title">{{ __('common.notifications') }}</span>
                                        </a>
                                    </div>

                                    <div class="separator my-2"></div>

                                    <div class="menu-item px-5"
                                         data-kt-menu-trigger="{default:'click',lg:'hover'}"
                                         data-kt-menu-placement="left-start"
                                         data-kt-menu-offset="-15px,0">
                                        <a href="#" class="menu-link px-5">
                                            <span class="menu-title position-relative">{{ __('common.theme') }}
                                                <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                                    <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                                    <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                                                </span>
                                            </span>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded
                                                    menu-title-gray-700 menu-icon-gray-500 menu-active-bg
                                                    menu-state-color fw-semibold py-4 fs-base w-150px"
                                             data-kt-menu="true" data-kt-element="theme-mode-menu">
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2"
                                                   data-kt-element="mode" data-kt-value="light">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-outline ki-night-day fs-2"></i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_light') }}</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2"
                                                   data-kt-element="mode" data-kt-value="dark">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-outline ki-moon fs-2"></i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_dark') }}</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2"
                                                   data-kt-element="mode" data-kt-value="system">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-outline ki-screen fs-2"></i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_system') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Language switcher --}}
                                    @include('partials.language-switcher')

                                    <div class="separator my-2"></div>

                                    <div class="menu-item px-5">
                                        <a href="#" class="menu-link px-5"
                                           onclick="event.preventDefault(); api.logout()">
                                            <i class="ki-outline ki-exit-right fs-4 me-2 text-danger"></i>
                                            {{ __('common.logout') }}
                                        </a>
                                    </div>

                                </div>
                            </div>

                        </div>
                        {{-- /Navbar --}}

                    </div>
                </div>
                {{-- /Primary header --}}

                {{-- Secondary header: nav menu --}}
                <div class="app-header-secondary">
                    <div class="app-container container-xxl d-flex align-items-stretch"
                         id="kt_app_header_secondary_container">

                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch flex-grow-1 bg-white rounded-2 px-5"
                             data-kt-drawer="true"
                             data-kt-drawer-name="app-header-menu"
                             data-kt-drawer-activate="{default: true, lg: false}"
                             data-kt-drawer-overlay="true"
                             data-kt-drawer-width="250px"
                             data-kt-drawer-direction="start"
                             data-kt-drawer-toggle="#kt_app_header_menu_toggle"
                             data-kt-swapper="true"
                             data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                             data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_secondary_container'}">

                            <div class="menu menu-rounded menu-active-bg menu-state-primary
                                        menu-column menu-lg-row menu-title-gray-700 menu-icon-gray-500
                                        menu-arrow-gray-500 menu-bullet-gray-500
                                        my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                                 id="kt_app_header_menu"
                                 data-kt-menu="true">

                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}"
                                       href="{{ route('supplier.dashboard') }}">
                                        <span class="menu-icon me-2">
                                            <i class="ki-outline ki-home-2 fs-4"></i>
                                        </span>
                                        <span class="menu-title">{{ __('nav.dashboard') }}</span>
                                    </a>
                                </div>

                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('supplier.rfqs.*') ? 'active' : '' }}"
                                       href="{{ route('supplier.rfqs.index') }}">
                                        <span class="menu-icon me-2">
                                            <i class="ki-outline ki-document fs-4"></i>
                                        </span>
                                        <span class="menu-title">{{ __('nav.supplier.rfqs') }}</span>
                                        @if (($supplierMenuBadges['rfqs'] ?? 0) > 0)
                                            <span class="badge badge-circle badge-primary ms-2"
                                                  title="{{ __('nav.supplier.rfqs_badge') }}">{{ $supplierMenuBadges['rfqs'] }}</span>
                                        @endif
                                    </a>
                                </div>

                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('supplier.offers.*') ? 'active' : '' }}"
                                       href="{{ route('supplier.offers.index') }}">
                                        <span class="menu-icon me-2">
                                            <i class="ki-outline ki-book-open fs-4"></i>
                                        </span>
                                        <span class="menu-title">{{ __('nav.supplier.offers') }}</span>
                                    </a>
                                </div>

                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('supplier.catalog.*') ? 'active' : '' }}"
                                       href="{{ route('supplier.catalog.index') }}">
                                        <span class="menu-icon me-2">
                                            <i class="ki-outline ki-category fs-4"></i>
                                        </span>
                                        <span class="menu-title">{{ __('nav.supplier.catalog') }}</span>
                                    </a>
                                </div>

                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('supplier.employees') ? 'active' : '' }}"
                                       href="{{ route('supplier.employees') }}">
                                        <span class="menu-icon me-2">
                                            <i class="ki-outline ki-people fs-4"></i>
                                        </span>
                                        <span class="menu-title">{{ __('nav.employees') }}</span>
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                {{-- /Secondary header --}}

            </div>
            {{-- ─── /Stacked Header ────────────────────────────────────────── --}}

            {{-- ─── App Wrapper ──────────────────────────────────────────────── --}}
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <div class="app-container container-xxl d-flex flex-row flex-column-fluid">
                    <div class="app-main flex-column flex-row-fluid pt-0" id="kt_app_main">
                        <div class="d-flex flex-column flex-column-fluid">

                            {{-- Toolbar / breadcrumbs --}}
                            <div id="kt_app_toolbar" class="app-toolbar pt-lg-9 pb-6">
                                <div id="kt_app_toolbar_container"
                                     class="container container-fluid d-flex flex-stack flex-wrap">
                                    <div class="d-flex flex-stack flex-wrap gap-4 w-100">
                                        <div class="page-title d-flex flex-column gap-3 me-3">
                                            <h1 class="page-heading d-flex flex-column justify-content-center
                                                        text-gray-900 fw-bolder fs-2x my-0">
                                                @yield('page-title')
                                            </h1>
                                            @hasSection('breadcrumb')
                                            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                                <li class="breadcrumb-item">
                                                    <a href="{{ route('supplier.dashboard') }}"
                                                       class="text-muted text-hover-primary">
                                                        <i class="ki-outline ki-home fs-6 text-muted me-1"></i>{{ __('dashboard.supplier.home') }}
                                                    </a>
                                                </li>
                                                <li class="breadcrumb-item">
                                                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                                                </li>
                                                @yield('breadcrumb')
                                            </ul>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-3 gap-lg-5">
                                            @yield('toolbar-actions')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Page content --}}
                            <div id="kt_app_content" class="app-content flex-column-fluid">
                                <div id="kt_app_content_container" class="container container-xxl">
                                    @yield('content')
                                </div>
                            </div>

                        </div>

                        @include('partials.footer')
                    </div>
                </div>
            </div>
            {{-- ─── /App Wrapper ─────────────────────────────────────────────── --}}

        </div>
    </div>

    {{-- Toast notifications --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div id="rfq-toast" class="toast align-items-center border-0"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="rfq-toast-message"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script>var hostUrl = "{{ asset('ui_template/assets/') }}/";</script>
    <script src="{{ asset('ui_template/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('ui_template/assets/js/scripts.bundle.js') }}"></script>

    <script>
        window.api = {
            headers() {
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                };
            },

            logout() {
                window.location.href = '{{ route("logout") }}';
            },

            async _handleResponse(res) {
                if (res.status === 401) { this.logout(); return; }
                const json = await res.json();
                if (!res.ok) {
                    const err = new Error(json?.message ?? `HTTP ${res.status}`);
                    err.data = json;
                    throw err;
                }
                return json;
            },

            async get(url) {
                const res = await fetch('/api' + url, { headers: this.headers(), credentials: 'same-origin' });
                return this._handleResponse(res);
            },

            async post(url, data) {
                const res = await fetch('/api' + url, {
                    method: 'POST', headers: this.headers(), credentials: 'same-origin', body: JSON.stringify(data)
                });
                return this._handleResponse(res);
            },

            async patch(url, data = {}) {
                const res = await fetch('/api' + url, {
                    method: 'PATCH', headers: this.headers(), credentials: 'same-origin', body: JSON.stringify(data)
                });
                return this._handleResponse(res);
            },

            async delete(url) {
                const res = await fetch('/api' + url, {
                    method: 'DELETE', headers: this.headers(), credentials: 'same-origin'
                });
                return this._handleResponse(res);
            },
        };

        window.showToast = function(message, type = 'success') {
            const toastEl = document.getElementById('rfq-toast');
            const msgEl   = document.getElementById('rfq-toast-message');
            msgEl.textContent = message;
            toastEl.className = 'toast align-items-center border-0 text-bg-'
                + (type === 'error' ? 'danger' : type);
            new bootstrap.Toast(toastEl, { delay: 3500 }).show();
        };

        // Глобальный лоадер кнопки (Metronic data-kt-indicator): у кнопки должны
        // быть .indicator-label / .indicator-progress (без d-none — Metronic сам
        // переключает видимость по data-kt-indicator).
        window.btnLoading = function(btn, loading) {
            if (!btn) return;
            btn.disabled = loading;
            btn.setAttribute('data-kt-indicator', loading ? 'on' : 'off');
        };

        // Локализованное название валюты по ISO-коду на языке смотрящего.
        // Сначала наш словарь (lang/*/currency_names.php — надёжно для az),
        // затем Intl.DisplayNames (для прочих кодов), затем фолбэк/код.
        window.APP_LOCALE = @json(app()->getLocale());
        window.CURRENCY_NAMES = @json(__('currency_names'));
        window.currencyName = function(code, fallback) {
            if (!code) return fallback || '';
            const cc = String(code).toUpperCase();
            if (window.CURRENCY_NAMES && window.CURRENCY_NAMES[cc]) return window.CURRENCY_NAMES[cc];
            try {
                const n = new Intl.DisplayNames([window.APP_LOCALE], { type: 'currency' }).of(cc);
                if (n && n.toUpperCase() !== cc) return n;
            } catch (e) {}
            return fallback || code;
        };
    </script>

    @include('partials.js-helpers')

    @include('partials.notification-bell-scripts')

    @include('partials.phone-input')

    @stack('scripts')
</body>
</html>
