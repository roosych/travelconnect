{{--
    Переключатель языка интерфейса для user-menu (паттерн Metronic «Language»).
    Активная локаль подсвечена и вынесена меткой рядом с заголовком.
    Локали и подписи берутся из config('app.available_locales').
--}}
@php
    $__flags = [
        'az' => 'azerbaijan.svg',
        'ru' => 'russia.svg',
        'en' => 'united-kingdom.svg',
    ];
    $__locales = config('app.available_locales', []);
    $__current = app()->getLocale();
    $__currentLabel = $__locales[$__current] ?? strtoupper($__current);
@endphp

<div class="menu-item px-5"
     data-kt-menu-trigger="{default:'click',lg:'hover'}"
     data-kt-menu-placement="left-start"
     data-kt-menu-offset="-15px,0">
    <a href="#" class="menu-link px-5">
        <span class="menu-title position-relative">{{ __('common.language') }}
            <span class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0 d-flex align-items-center">
                {{ $__currentLabel }}
                @if (isset($__flags[$__current]))
                    <img class="w-15px h-15px rounded-1 ms-2"
                         src="{{ asset('ui_template/assets/media/flags/' . $__flags[$__current]) }}" alt="" />
                @endif
            </span>
        </span>
    </a>
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded
                menu-title-gray-700 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-175px"
         data-kt-menu="true">
        @foreach ($__locales as $__code => $__name)
            <div class="menu-item px-3">
                <a href="{{ route('lang.switch', $__code) }}"
                   class="menu-link d-flex px-5 {{ $__code === $__current ? 'active' : '' }}">
                    <span class="symbol symbol-20px me-4">
                        @if (isset($__flags[$__code]))
                            <img class="rounded-1"
                                 src="{{ asset('ui_template/assets/media/flags/' . $__flags[$__code]) }}" alt="" />
                        @endif
                    </span>{{ $__name }}
                </a>
            </div>
        @endforeach
    </div>
</div>
