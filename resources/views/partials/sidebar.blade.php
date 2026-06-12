{{-- Sidebar: 100px icon rail + flyout dropdowns (Keen template pattern) --}}
<div id="kt_app_sidebar" class="app-sidebar flex-column"
    data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="100px"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    {{-- Logo --}}
    <div class="app-sidebar-logo d-none d-lg-flex flex-center pt-8 mb-3" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center justify-content-center
            w-40px h-40px rounded-2 bg-primary text-white fw-bold fs-5 text-decoration-none">
            TO
        </a>
    </div>

    {{-- Menu --}}
    <div class="app-sidebar-menu d-flex flex-center overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper"
            class="app-sidebar-wrapper d-flex hover-scroll-overlay-y scroll-ms my-5"
            data-kt-scroll="true"
            data-kt-scroll-activate="true"
            data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu, #kt_app_sidebar"
            data-kt-scroll-offset="5px">

            <div class="menu menu-column menu-rounded menu-active-bg menu-title-gray-700
                        menu-arrow-gray-500 menu-icon-gray-500 menu-bullet-gray-500
                        menu-state-primary my-auto"
                id="kt_app_sidebar_menu"
                data-kt-menu="true"
                data-kt-menu-expand="false">

                {{-- ── Operations flyout ──────────────────────────────── --}}
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                     data-kt-menu-placement="right-start"
                     class="menu-item py-2 {{ request()->routeIs('dashboard|requests.*|rfqs.*|offers.*|proposals.*|bookings.*') ? 'here show' : '' }}">

                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-element-11 fs-2x"></i>
                        </span>
                    </span>

                    <div class="menu-sub menu-sub-dropdown px-2 py-4 w-250px mh-75 overflow-auto">
                        <div class="menu-item">
                            <div class="menu-content">
                                <span class="menu-section fs-5 fw-bolder ps-1 py-1">Операции</span>
                            </div>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                               href="{{ route('admin.dashboard') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Дашборд</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('requests.*') ? 'active' : '' }}"
                               href="{{ route('admin.requests.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Заявки на тур</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('rfqs.*') ? 'active' : '' }}"
                               href="{{ route('admin.rfqs.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Запросы поставщикам</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('offers.*') ? 'active' : '' }}"
                               href="{{ route('admin.offers.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Предложения</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('proposals.*') ? 'active' : '' }}"
                               href="{{ route('admin.proposals.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Коммерч. предложения</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}"
                               href="{{ route('admin.bookings.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Бронирования</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── Поставщики ──────────────────────────────────────── --}}
                <div class="menu-item py-2">
                    <a class="menu-link menu-center {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
                       href="{{ route('admin.suppliers.index') }}"
                       data-bs-toggle="tooltip"
                       data-bs-placement="right"
                       title="Поставщики">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-truck fs-2x"></i>
                        </span>
                    </a>
                </div>

                {{-- ── Агентства ────────────────────────────────────────── --}}
                <div class="menu-item py-2">
                    <a class="menu-link menu-center {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}"
                       href="{{ route('admin.agencies.index') }}"
                       data-bs-toggle="tooltip"
                       data-bs-placement="right"
                       title="Агентства">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-office-bag fs-2x"></i>
                        </span>
                    </a>
                </div>

                {{-- ── Settings flyout ─────────────────────────────── --}}
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                     data-kt-menu-placement="right-start"
                     class="menu-item py-2 {{ request()->routeIs('settings.*') ? 'here show' : '' }}">

                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-setting-2 fs-2x"></i>
                        </span>
                    </span>

                    <div class="menu-sub menu-sub-dropdown px-2 py-4 w-250px mh-75 overflow-auto">
                        <div class="menu-item">
                            <div class="menu-content">
                                <span class="menu-section fs-5 fw-bolder ps-1 py-1">Настройки</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.settings.services') ? 'active' : '' }}"
                               href="{{ route('admin.settings.services') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Типы услуг</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.settings.currencies') ? 'active' : '' }}"
                               href="{{ route('admin.settings.currencies') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Валюты</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.settings.geo') ? 'active' : '' }}"
                               href="{{ route('admin.settings.geo') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Страны и направления</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Footer: settings shortcut --}}
    <div class="app-sidebar-footer d-flex flex-center flex-column-auto pt-6 mb-7"
         id="kt_app_sidebar_footer">
        <a href="{{ route('admin.settings.services') }}"
           class="btn btn-icon btn-custom btn-active-color-primary w-35px h-35px"
           title="Настройки"
           data-bs-toggle="tooltip"
           data-bs-placement="right"
           data-bs-dismiss="click">
            <i class="ki-outline ki-setting-2 fs-2 text-gray-500"></i>
        </a>
    </div>

</div>
