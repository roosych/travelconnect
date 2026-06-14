{{-- In-app notifications bell. Requires the layout's window.api + Metronic menu JS. --}}
@php
    // Шестерёнка ведёт на настройки уведомлений в кабинете текущей роли.
    $__nu = auth()->user();
    $notifSettingsUrl = match (true) {
        $__nu?->isAgency()   && \Route::has('agency.notifications')   => route('agency.notifications'),
        $__nu?->isSupplier() && \Route::has('supplier.notifications') => route('supplier.notifications'),
        $__nu?->isOperator() && \Route::has('admin.profile')         => route('admin.profile'),
        default => null,
    };
@endphp
<div class="app-navbar-item ms-1 ms-lg-3">

    <div class="cursor-pointer symbol symbol-35px symbol-md-40px position-relative"
         data-kt-menu-trigger="click"
         data-kt-menu-attach="parent"
         data-kt-menu-placement="bottom-end">
        <div class="symbol-label bg-light">
            <i class="ki-outline ki-notification fs-2 text-gray-600"></i>
        </div>
        <span id="notif-bell-badge"
              class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger d-none"
              style="font-size:9px;min-width:16px;height:16px;padding:0 4px;line-height:16px">0</span>
    </div>

    <div class="menu menu-sub menu-sub-dropdown menu-column w-300px w-lg-350px" data-kt-menu="true">
        <div class="d-flex align-items-center justify-content-between px-5 py-4 border-bottom border-gray-200">
            <span class="fw-bold fs-6 text-gray-800">{{ __('notifications.bell.title') }}</span>
            <div class="d-flex align-items-center gap-2">
                <button id="notif-mark-all" class="btn btn-sm btn-light-primary py-1 px-3 fs-8 d-none">
                    {{ __('notifications.bell.mark_all') }}
                </button>
                @if($notifSettingsUrl)
                    <a href="{{ $notifSettingsUrl }}" class="btn btn-icon btn-sm btn-active-light-primary"
                       title="{{ __('notifications.bell.settings') }}">
                        <i class="ki-outline ki-setting-2 fs-4 text-gray-600"></i>
                    </a>
                @endif
            </div>
        </div>
        <div id="notif-bell-list" class="scroll-y mh-325px">
            <div class="text-center py-8">
                <span class="spinner-border spinner-border-sm text-primary"></span>
            </div>
        </div>
    </div>

</div>
