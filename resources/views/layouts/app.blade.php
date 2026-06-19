<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'B2B Travel') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/fav-dark.png') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    @stack('styles')
    <style>
        /* ════════════════════════════════════════════════════════════
           HEADER NAV — горизонтальное меню на тёмном фоне
           ════════════════════════════════════════════════════════════ */
        @media (min-width: 992px) {

            /* Неактивный пункт верхнего уровня */
            #kt_app_header_menu > .menu-item > .menu-link .menu-title {
                color: rgba(255,255,255,0.65) !important;
                font-weight: 600;
                font-size: 1.05rem;
                letter-spacing: 0.01em;
                transition: color .15s ease;
            }
            #kt_app_header_menu > .menu-item > .menu-link .menu-arrow {
                color: rgba(255,255,255,0.4) !important;
                transition: color .15s ease;
            }

            /* Hover верхнего уровня */
            #kt_app_header_menu > .menu-item > .menu-link:hover .menu-title {
                color: #ffffff !important;
            }
            #kt_app_header_menu > .menu-item > .menu-link:hover .menu-arrow {
                color: rgba(255,255,255,0.75) !important;
            }

            /* Активная секция (here/show) и прямая ссылка .active */
            #kt_app_header_menu > .menu-item.here  > .menu-link .menu-title,
            #kt_app_header_menu > .menu-item.show  > .menu-link .menu-title,
            #kt_app_header_menu > .menu-item > .menu-link.active .menu-title {
                color: #1e2129 !important;
                font-weight: 700;
            }
            #kt_app_header_menu > .menu-item.here  > .menu-link .menu-arrow,
            #kt_app_header_menu > .menu-item.show  > .menu-link .menu-arrow {
                color: rgba(30,33,41,0.6) !important;
            }

            /* Фон для активной секции */
            #kt_app_header_menu > .menu-item.here  > .menu-link,
            #kt_app_header_menu > .menu-item.show  > .menu-link,
            #kt_app_header_menu > .menu-item > .menu-link.active {
                background-color: rgba(255,255,255,0.88) !important;
                border-radius: 6px;
            }

            /* ── Dropdown items ── */

            /* Неактивный */
            #kt_app_header_menu .menu-sub .menu-link .menu-title {
                color: #4b5675 !important;
                font-weight: 500;
                font-size: 1rem;
                transition: color .15s ease;
            }
            #kt_app_header_menu .menu-sub .menu-link .bullet {
                background-color: #c4c8d8 !important;
                transition: background-color .15s ease, width .15s, height .15s;
            }

            /* Hover */
            #kt_app_header_menu .menu-sub .menu-link:hover .menu-title {
                color: var(--bs-primary) !important;
            }
            #kt_app_header_menu .menu-sub .menu-link:hover .bullet {
                background-color: var(--bs-primary) !important;
            }

            /* Активный */
            #kt_app_header_menu .menu-sub .menu-link.active .menu-title {
                color: var(--bs-primary) !important;
                font-weight: 500;
            }
            #kt_app_header_menu .menu-sub .menu-link.active .bullet {
                background-color: var(--bs-primary) !important;
                width: 6px !important;
                height: 6px !important;
            }
            #kt_app_header_menu .menu-sub .menu-link.active {
                background-color: var(--bs-primary-light, #e8f0fe) !important;
                border-radius: 6px;
            }
        }

        /* ════════════════════════════════════════════════════════════
           SIDEBAR — иконочный рельс + flyout
           ════════════════════════════════════════════════════════════ */

        /* Иконка — неактивная */
        #kt_app_sidebar_menu .menu-link .menu-icon i {
            color: #99a1b7;
            transition: color .15s ease;
        }

        /* Иконка — hover */
        #kt_app_sidebar_menu .menu-item > .menu-link.menu-center:hover {
            background-color: var(--bs-primary) !important;
            border-radius: 10px;
        }
        #kt_app_sidebar_menu .menu-item > .menu-link.menu-center:hover .menu-icon i {
            color: #ffffff !important;
        }

        /* Иконка — активная секция (here) */
        #kt_app_sidebar_menu .menu-item.here > .menu-link.menu-center,
        #kt_app_sidebar_menu .menu-item > .menu-link.menu-center.active {
            background-color: var(--bs-primary) !important;
            border-radius: 10px;
        }
        #kt_app_sidebar_menu .menu-item.here > .menu-link.menu-center .menu-icon i,
        #kt_app_sidebar_menu .menu-item > .menu-link.menu-center.active .menu-icon i {
            color: #ffffff !important;
        }

        /* Flyout — заголовок секции */
        #kt_app_sidebar_menu .menu-sub .menu-content .menu-section {
            color: #a1a5b7 !important;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* Flyout — неактивный пункт */
        #kt_app_sidebar_menu .menu-sub .menu-link .menu-title {
            color: #4b5675 !important;
            font-weight: 500;
            font-size: 1rem;
            transition: color .15s ease;
        }
        #kt_app_sidebar_menu .menu-sub .menu-link .bullet {
            background-color: #c4c8d8 !important;
            transition: background-color .15s ease, width .15s, height .15s;
        }

        /* Flyout — hover */
        #kt_app_sidebar_menu .menu-sub .menu-link:hover .menu-title {
            color: var(--bs-primary) !important;
        }
        #kt_app_sidebar_menu .menu-sub .menu-link:hover .bullet {
            background-color: var(--bs-primary) !important;
        }
        #kt_app_sidebar_menu .menu-sub .menu-link:hover {
            background-color: transparent !important;
        }

        /* Flyout — активный пункт */
        #kt_app_sidebar_menu .menu-sub .menu-link.active {
            background-color: var(--bs-primary-light, #e8f0fe) !important;
            border-radius: 6px;
        }
        #kt_app_sidebar_menu .menu-sub .menu-link.active .menu-title {
            color: var(--bs-primary) !important;
            font-weight: 500;
        }
        #kt_app_sidebar_menu .menu-sub .menu-link.active .bullet {
            background-color: var(--bs-primary) !important;
            width: 6px !important;
            height: 6px !important;
        }
    </style>
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
            <div id="kt_app_header" class="app-header" style="background-image: url('{{ asset('assets/media/patterns/toolbar-bg.png') }}'); background-size: cover; background-position: center;">

                {{-- ════════════════════════════════════════════════════════════
                     Primary header: logo + notifications + quick links + chat
                                     + user menu + primary action
                     ════════════════════════════════════════════════════════════ --}}
                <div class="app-header-primary"
                     data-kt-sticky="true"
                     data-kt-sticky-name="app-header-primary"
                     data-kt-sticky-offset="{default: '200px', lg: '300px'}"
                     data-kt-sticky-animation="false"
                     data-kt-sticky-class="shadow-sm"
                     data-kt-sticky-release-class="shadow-none">
                    <div class="app-container container-xxl d-flex align-items-stretch justify-content-between"
                         id="kt_app_header_primary_container">

                        {{-- ── Logo area ── --}}
                        <div class="d-flex flex-grow-1 flex-lg-grow-0 align-items-center me-lg-4">

                            {{-- Mobile: hamburger toggle + small logo --}}
                            <div class="d-flex align-items-center gap-2 d-block d-sm-none">
                                <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                                     id="kt_app_header_menu_toggle">
                                    <i class="ki-duotone ki-abstract-14 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                                    <div class="d-flex align-items-center justify-content-center w-30px h-30px rounded-2 bg-primary text-white fw-bold fs-6">
                                        TO
                                    </div>
                                </a>
                            </div>

                            {{-- Desktop: full logo --}}
                            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none d-none d-sm-flex">
                                <span class="text-white fw-bolder fs-2">{{ config('app.name') }}</span>
                            </a>

                        </div>
                        {{-- /Logo area --}}

                        {{-- ── Right navbar ── --}}
                        <div class="app-navbar flex-shrink-0" id="kt_app_header_navbar">

                            {{-- 0. Живые часы (пояс пользователя) --}}
                            @include('partials.header-clock', ['onDark' => true])

                            {{-- 1. Notifications --}}
                            <div class="app-navbar-item ms-1 ms-lg-3">
                                <div class="btn btn-icon btn-custom btn-active-light-primary
                                            w-35px h-35px w-md-40px h-md-40px position-relative"
                                     data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                     data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    <i class="ki-outline ki-notification-bing fs-2"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle
                                                 badge badge-circle badge-sm badge-danger fs-10"
                                          id="notif-badge" style="display:none">0</span>
                                </div>

                                <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
                                     data-kt-menu="true">
                                    <div class="d-flex flex-stack px-8 py-5 border-bottom">
                                        <h3 class="fw-bold text-gray-800 mb-0 fs-5">Уведомления</h3>
                                        <span class="text-muted fs-7" id="notif-unread-label"></span>
                                    </div>
                                    <div class="scroll-y mh-350px" id="notif-list">
                                        <div class="text-center py-10 text-muted fs-7">Загрузка...</div>
                                    </div>
                                    <div class="py-3 text-center border-top">
                                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-color-gray-600 btn-active-color-primary btn-sm fw-semibold">
                                            Показать все
                                            <i class="ki-outline ki-arrow-right fs-5 ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {{-- /Notifications --}}

                            @php
                                $__u       = auth()->user();
                                $__words   = array_values(array_filter(explode(' ', trim($__u->name ?? ''))));
                                $__ini     = count($__words) >= 2
                                    ? strtoupper(mb_substr($__words[0], 0, 1) . mb_substr(end($__words), 0, 1))
                                    : strtoupper(mb_substr($__u->name ?? '?', 0, 2));
                                $__role    = $__u->role?->value ?? '';
                                $__rlabel  = ['operator' => 'Оператор', 'agency' => 'Агентство', 'supplier' => 'Поставщик'][$__role] ?? $__role;
                                $__rcls    = ['operator' => 'badge-light-primary', 'agency' => 'badge-light-success', 'supplier' => 'badge-light-warning'][$__role] ?? 'badge-light-secondary';
                            @endphp
                            {{-- 4. User menu --}}
                            <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                                <div class="cursor-pointer symbol symbol-35px symbol-md-40px"
                                     data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                     data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">{{ $__ini }}</div>
                                </div>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded
                                            menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                     data-kt-menu="true">

                                    {{-- Profile header --}}
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <div class="symbol-label bg-light-primary text-primary fw-bold fs-3">{{ $__ini }}</div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold fs-5 text-gray-800 mb-1">{{ $__u->name ?? '' }}</div>
                                                <span class="text-muted text-hover-primary fs-7">{{ $__u->email ?? '' }}</span>
                                                <span class="badge fw-bold fs-8 mt-1 {{ $__rcls }}">{{ $__rlabel }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-2"></div>

                                    {{-- My Profile --}}
                                    <div class="menu-item px-5">
                                        <a href="{{ route('admin.profile') }}" class="menu-link px-5">{{ __('common.my_profile') }}</a>
                                    </div>

                                    <div class="separator my-2"></div>

                                    {{-- Mode toggle --}}
                                    <div class="menu-item px-5"
                                         data-kt-menu-trigger="{default:'click',lg:'hover'}"
                                         data-kt-menu-placement="left-start"
                                         data-kt-menu-offset="-15px,0">
                                        <a href="#" class="menu-link px-5">
                                            <span class="menu-title position-relative">{{ __('common.theme') }}
                                                <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                                    <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                                        <span class="path1"></span><span class="path2"></span>
                                                        <span class="path3"></span><span class="path4"></span>
                                                        <span class="path5"></span><span class="path6"></span>
                                                        <span class="path7"></span><span class="path8"></span>
                                                        <span class="path9"></span><span class="path10"></span>
                                                    </i>
                                                    <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
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
                                                        <i class="ki-duotone ki-night-day fs-2">
                                                            <span class="path1"></span><span class="path2"></span>
                                                            <span class="path3"></span><span class="path4"></span>
                                                            <span class="path5"></span><span class="path6"></span>
                                                            <span class="path7"></span><span class="path8"></span>
                                                            <span class="path9"></span><span class="path10"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_light') }}</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2"
                                                   data-kt-element="mode" data-kt-value="dark">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-duotone ki-moon fs-2">
                                                            <span class="path1"></span><span class="path2"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_dark') }}</span>
                                                </a>
                                            </div>
                                            <div class="menu-item px-3 my-0">
                                                <a href="#" class="menu-link px-3 py-2"
                                                   data-kt-element="mode" data-kt-value="system">
                                                    <span class="menu-icon" data-kt-element="icon">
                                                        <i class="ki-duotone ki-screen fs-2">
                                                            <span class="path1"></span><span class="path2"></span>
                                                            <span class="path3"></span><span class="path4"></span>
                                                        </i>
                                                    </span>
                                                    <span class="menu-title">{{ __('common.theme_system') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- /Mode toggle --}}

                                    {{-- Language switcher --}}
                                    @include('partials.language-switcher')

                                    <div class="separator my-2"></div>

                                    {{-- Sign Out --}}
                                    <div class="menu-item px-5">
                                        <a href="#" class="menu-link px-5"
                                           onclick="event.preventDefault(); api.logout()">
                                            <i class="ki-duotone ki-exit-right fs-4 me-2 text-danger">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                            {{ __('common.logout') }}
                                        </a>
                                    </div>

                                </div>
                            </div>
                            {{-- /User menu --}}


                        </div>
                        {{-- /Right navbar --}}

                    </div>
                </div>
                {{-- /Primary header --}}

                {{-- ════════════════════════════════════════════════════════════
                     Secondary header: nav menu + search bar
                     ════════════════════════════════════════════════════════════ --}}
                <div class="app-header-secondary">
                    <div class="app-container container-xxl d-flex align-items-stretch"
                         id="kt_app_header_secondary_container">

                        {{-- Nav menu (drawer on mobile) --}}
                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch"
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

                                {{-- ── Дашборд ── --}}
                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                       href="{{ route('admin.dashboard') }}">
                                        <span class="menu-title">{{ __('nav.dashboard') }}</span>
                                    </a>
                                </div>

                                {{-- ── Сделки (конвейер: заявка → закупка → продажа) ── --}}
                                @php
                                    $bRequests  = (int) ($menuBadges['requests']  ?? 0);
                                    $bOffers    = (int) ($menuBadges['offers']     ?? 0);
                                    $bProposals = (int) ($menuBadges['proposals']  ?? 0);
                                    $bDeals     = $bRequests + $bOffers + $bProposals;
                                @endphp
                                <div data-kt-menu-trigger="{default:'click', lg:'hover'}"
                                     data-kt-menu-placement="bottom-start"
                                     class="menu-item menu-lg-down-accordion menu-sub-lg-down-indented me-0 me-lg-2
                                            {{ request()->routeIs('admin.requests.*') || request()->routeIs('admin.rfqs.*') || request()->routeIs('admin.offers.*') || request()->routeIs('admin.proposals.*') || request()->routeIs('admin.bookings.*') ? 'here show' : '' }}">
                                    <span class="menu-link py-3">
                                        <span class="menu-title">{{ __('nav.operator.deals') }}</span>
                                        @if ($bDeals > 0)
                                            <span class="badge badge-circle badge-primary ms-2">{{ $bDeals }}</span>
                                        @endif
                                        <span class="menu-arrow d-lg-none"></span>
                                    </span>
                                    <div class="menu-sub menu-sub-lg-dropdown menu-sub-indented p-0 w-225px">

                                        {{-- Входящее --}}
                                        <div class="menu-item">
                                            <div class="menu-content pt-2 pb-1">
                                                <span class="menu-section text-muted text-uppercase fs-8 ls-1">{{ __('nav.operator.sec_incoming') }}</span>
                                            </div>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}"
                                               href="{{ route('admin.requests.index') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.requests') }}</span>
                                                @if ($bRequests > 0)
                                                    <span class="badge badge-light-primary ms-auto">{{ $bRequests }}</span>
                                                @endif
                                            </a>
                                        </div>

                                        {{-- Закупка у поставщиков --}}
                                        <div class="menu-item">
                                            <div class="menu-content pt-3 pb-1">
                                                <span class="menu-section text-muted text-uppercase fs-8 ls-1">{{ __('nav.operator.sec_purchase') }}</span>
                                            </div>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.rfqs.*') ? 'active' : '' }}"
                                               href="{{ route('admin.rfqs.index') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.rfqs') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}"
                                               href="{{ route('admin.offers.index') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.offers') }}</span>
                                                @if ($bOffers > 0)
                                                    <span class="badge badge-light-primary ms-auto">{{ $bOffers }}</span>
                                                @endif
                                            </a>
                                        </div>

                                        {{-- Продажа агентству --}}
                                        <div class="menu-item">
                                            <div class="menu-content pt-3 pb-1">
                                                <span class="menu-section text-muted text-uppercase fs-8 ls-1">{{ __('nav.operator.sec_sales') }}</span>
                                            </div>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.proposals.*') ? 'active' : '' }}"
                                               href="{{ route('admin.proposals.index') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.proposals') }}</span>
                                                @if ($bProposals > 0)
                                                    <span class="badge badge-light-primary ms-auto">{{ $bProposals }}</span>
                                                @endif
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
                                               href="{{ route('admin.bookings.index') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.bookings') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                {{-- /Сделки --}}

                                {{-- ── Поставщики ── --}}
                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
                                       href="{{ route('admin.suppliers.index') }}">
                                        <span class="menu-title">{{ __('nav.operator.suppliers') }}</span>
                                    </a>
                                </div>

                                {{-- ── Агентства ── --}}
                                <div class="menu-item me-0 me-lg-2">
                                    <a class="menu-link py-3 {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}"
                                       href="{{ route('admin.agencies.index') }}">
                                        <span class="menu-title">{{ __('nav.operator.agencies') }}</span>
                                    </a>
                                </div>

                                {{-- ── Отчёты ── --}}
                                <div data-kt-menu-trigger="{default:'click', lg:'hover'}"
                                     data-kt-menu-placement="bottom-start"
                                     class="menu-item menu-lg-down-accordion menu-sub-lg-down-indented me-0 me-lg-2
                                            {{ request()->routeIs('admin.reports.*') ? 'here show' : '' }}">
                                    <span class="menu-link py-3">
                                        <span class="menu-title">{{ __('nav.operator.reports') }}</span>
                                        <span class="menu-arrow d-lg-none"></span>
                                    </span>
                                    <div class="menu-sub menu-sub-lg-dropdown menu-sub-indented p-0 w-200px">
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.reports.margin') ? 'active' : '' }}"
                                               href="{{ route('admin.reports.margin') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.reports_margin') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.reports.funnel') ? 'active' : '' }}"
                                               href="{{ route('admin.reports.funnel') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.reports_funnel') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.reports.suppliers') ? 'active' : '' }}"
                                               href="{{ route('admin.reports.suppliers') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.reports_suppliers') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Settings ── --}}
                                <div data-kt-menu-trigger="{default:'click', lg:'hover'}"
                                     data-kt-menu-placement="bottom-start"
                                     class="menu-item menu-lg-down-accordion menu-sub-lg-down-indented me-0 me-lg-2
                                            {{ request()->routeIs('admin.settings.*') ? 'here show' : '' }}">
                                    <span class="menu-link py-3">
                                        <span class="menu-title">{{ __('nav.operator.settings') }}</span>
                                        <span class="menu-arrow d-lg-none"></span>
                                    </span>
                                    <div class="menu-sub menu-sub-lg-dropdown menu-sub-indented p-0 w-200px">
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.settings.currencies') ? 'active' : '' }}"
                                               href="{{ route('admin.settings.currencies') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.settings_currencies') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.settings.geo') ? 'active' : '' }}"
                                               href="{{ route('admin.settings.geo') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.settings_geo') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.settings.services') ? 'active' : '' }}"
                                               href="{{ route('admin.settings.services') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.settings_services') }}</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link {{ request()->routeIs('admin.settings.operators') ? 'active' : '' }}"
                                               href="{{ route('admin.settings.operators') }}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">{{ __('nav.operator.settings_operators') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                {{-- /Settings --}}

                            </div>
                        </div>
                        {{-- /Nav menu --}}

                    </div>
                </div>
                {{-- /Secondary header --}}

            </div>
            {{-- ─── /Stacked Header ────────────────────────────────────────── --}}

            {{-- ─── App Wrapper (no sidebar) ──────────────────────────────── --}}
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <div class="container container-xxl d-flex flex-row flex-column-fluid">
                    <div class="app-main flex-column flex-row-fluid pt-0" id="kt_app_main">
                        <div class="d-flex flex-column flex-column-fluid">

                            {{-- Toolbar / breadcrumbs --}}
                            <div id="kt_app_toolbar" class="app-toolbar pt-lg-9 pb-6">
                                <div id="kt_app_toolbar_container"
                                     class="app-container container-fluid d-flex flex-stack flex-wrap">
                                    <div class="d-flex flex-stack flex-wrap gap-4 w-100">
                                        <div class="page-title d-flex flex-column gap-3 me-3">
                                            <h1 class="page-heading d-flex flex-column justify-content-center
                                                        text-gray-900 fw-bolder fs-2x my-0">
                                                @yield('page-title')
                                            </h1>
                                            @hasSection('breadcrumb')
                                            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                                <li class="breadcrumb-item">
                                                    <a href="{{ route('admin.dashboard') }}"
                                                       class="text-muted text-hover-primary">
                                                        <i class="ki-outline ki-home fs-6 text-muted me-1"></i>Главная
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
                                <div id="kt_app_content_container" class="app-container container-xxl">
                                    @yield('content')
                                </div>
                            </div>

                        </div>

                        @include('partials.footer')
                    </div>
                </div>
            </div>
            {{-- ─── /App Wrapper ────────────────────────────────────────────── --}}

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

    <script>var hostUrl = "{{ asset('assets/') }}/";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

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

            async put(url, data = {}) {
                const res = await fetch('/api' + url, {
                    method: 'PUT', headers: this.headers(), credentials: 'same-origin', body: JSON.stringify(data)
                });
                return this._handleResponse(res);
            },

            async delete(url) {
                const res = await fetch('/api' + url, {
                    method: 'DELETE', headers: this.headers(), credentials: 'same-origin'
                });
                return this._handleResponse(res);
            },

            async upload(url, formData) {
                const res = await fetch('/api' + url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                    body: formData,
                });
                if (res.status === 401) { this.logout(); return; }
                return res.json();
            }
        };

        window.renderStepper = function(elOrId, steps) {
            const el = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
            if (!el || !steps.length) return;
            const N = steps.length;
            const halfStep = 100 / (2 * N);
            const trackW   = (N - 1) / N * 100;
            const filledIdx = (() => {
                const ai = steps.findIndex(s => s.active);
                return ai !== -1 ? ai : steps.reduce((acc, s, i) => s.done ? i : acc, -1);
            })();
            const progressW = filledIdx <= 0 ? 0 : (filledIdx / (N - 1)) * trackW;
            const html = steps.map((s, i) => {
                const circleStyle = s.done
                    ? 'background:#50cd89;color:#fff'
                    : s.active
                        ? 'background:#009ef7;color:#fff;box-shadow:0 0 0 5px rgba(0,158,247,.18)'
                        : 'background:#f5f8fa;color:#b5b5c3;border:2px solid #e4e6ef';
                const inner = s.done
                    ? `<i class="ki-outline ki-check fs-4" style="color:#fff"></i>`
                    : (s.active && s.icon)
                        ? `<i class="ki-outline ${s.icon} fs-4" style="color:#fff"></i>`
                        : `<span class="fw-bold fs-7" style="color:${s.active ? '#fff' : '#b5b5c3'}">${i + 1}</span>`;
                const labelColor  = s.done ? '#50cd89' : s.active ? '#009ef7' : '#b5b5c3';
                const labelWeight = s.active ? '700' : s.done ? '600' : '500';
                return `
                    <div class="d-flex flex-column align-items-center flex-fill gap-3">
                        <div class="w-55px h-55px rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="${circleStyle}">
                            ${inner}
                        </div>
                        <span class="fs-8 text-center lh-sm" style="color:${labelColor};font-weight:${labelWeight}">${s.label}</span>
                    </div>`;
            }).join('');
            el.innerHTML = `
                <div class="position-relative px-4 pt-6 pb-3">
                    <div class="position-absolute rounded-pill bg-gray-200"
                         style="height:3px;top:55px;left:${halfStep}%;right:${halfStep}%;z-index:0"></div>
                    <div class="position-absolute rounded-pill"
                         style="height:3px;top:55px;left:${halfStep}%;width:${progressW.toFixed(1)}%;z-index:0;background:#50cd89;transition:width .4s ease"></div>
                    <div class="d-flex position-relative" style="z-index:1">${html}</div>
                </div>`;
        };

        // Глобальный лоадер кнопки (Metronic data-kt-indicator): кнопка с
        // .indicator-label / .indicator-progress (без d-none) и data-kt-indicator.
        window.btnLoading = function(btn, loading) {
            if (!btn) return;
            btn.disabled = loading;
            btn.setAttribute('data-kt-indicator', loading ? 'on' : 'off');
        };

        window.showToast = function(message, type = 'success') {
            const toastEl = document.getElementById('rfq-toast');
            const msgEl   = document.getElementById('rfq-toast-message');
            msgEl.textContent = message;
            toastEl.className = 'toast align-items-center border-0 text-bg-'
                + (type === 'error' ? 'danger' : type);
            new bootstrap.Toast(toastEl, { delay: 3500 }).show();
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


        // ── Notifications ──────────────────────────────────────────────
        (async function loadNotifications() {
            function timeAgo(dateStr) {
                const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
                if (diff < 60)   return diff + ' сек. назад';
                if (diff < 3600) return Math.floor(diff / 60) + ' мин. назад';
                if (diff < 86400) return Math.floor(diff / 3600) + ' ч. назад';
                return Math.floor(diff / 86400) + ' д. назад';
            }

            try {
                const res   = await api.get('/notifications');
                if (!res || res.success === false) return;
                const data  = res;
                const items = data.data ?? data ?? [];
                const unread = items.filter(n => !n.read).length;

                const badge = document.getElementById('notif-badge');
                const label = document.getElementById('notif-unread-label');
                const list  = document.getElementById('notif-list');

                if (unread > 0) {
                    badge.textContent = unread > 9 ? '9+' : unread;
                    badge.style.display = '';
                }
                label.textContent = unread > 0 ? unread + ' непрочитанных' : 'Всё прочитано';

                if (!items.length) {
                    list.innerHTML = `<div class="text-center py-10 text-muted fs-7">Уведомлений пока нет.</div>`;
                    return;
                }

                list.innerHTML = items.map((n, i) => {
                    const isLast = i === items.length - 1;
                    return `
                    <a href="${n.url ?? '#'}" data-id="${n.id}"
                       class="notif-item d-flex flex-stack px-8 py-4 ${isLast ? '' : 'border-bottom border-gray-200 border-bottom-dashed'} ${n.read ? '' : 'bg-light-primary bg-opacity-25'}">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-4 flex-shrink-0">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ${n.icon ?? 'ki-notification'} fs-3 text-primary"></i>
                                </span>
                            </div>
                            <div>
                                <span class="fs-7 fw-bold text-gray-800 d-block">${n.title ?? '—'}</span>
                                ${n.message ? `<div class="text-muted fs-8">${n.message}</div>` : ''}
                            </div>
                        </div>
                        <span class="text-muted fs-8 ms-3 flex-shrink-0">${n.created_at ? timeAgo(n.created_at) : ''}</span>
                    </a>`;
                }).join('');

                list.querySelectorAll('.notif-item').forEach(el => {
                    el.addEventListener('click', async function (e) {
                        const href = this.getAttribute('href');
                        if (!href || href === '#') e.preventDefault();
                        try { await api.patch(`/notifications/${this.dataset.id}/read`); } catch (_) {}
                    });
                });
            } catch {
                document.getElementById('notif-list').innerHTML =
                    `<div class="text-center py-8 text-muted fs-7">Не удалось загрузить уведомления.</div>`;
            }
        })();
    </script>

    @include('partials.js-helpers')

    @include('partials.phone-input')

    @stack('scripts')
</body>
</html>
