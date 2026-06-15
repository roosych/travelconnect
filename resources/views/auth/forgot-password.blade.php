@extends('layouts.auth')

@section('title', __('auth.forgot_title'))

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid h-100">

    {{-- Left branding panel --}}
    <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-2 order-lg-1"
         style="background-image: linear-gradient(145deg, rgba(13,19,40,.35) 0%, rgba(13,19,40,.10) 100%), url('{{ asset('assets/media/misc/auth-bg.png') }}'); background-size: cover; background-position: center;">
        <div class="d-flex flex-column flex-center py-15 px-10 w-100">
            <div class="text-center mb-12">
                <h1 class="text-white fw-bold fs-2qx mb-2 lh-1">{{ config('app.name') }}</h1>
            </div>
            <div class="d-flex flex-center w-100">
                <img src="{{ asset('assets/media/auth/casp_transparent.png') }}"
                     alt="{{ config('app.name') }}" class="img-fluid" style="max-width:270px;width:70%;" />
            </div>
        </div>
    </div>

    {{-- Right form panel --}}
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-1 order-lg-2">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10">

                <div class="text-center mb-10">
                    <h1 class="text-gray-900 fw-bolder mb-3">{{ __('auth.forgot_title') }}</h1>
                    <div class="text-gray-500 fw-semibold fs-6">{{ __('auth.forgot_subtitle') }}</div>
                </div>

                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-3 mb-7">
                        <i class="ki-outline ki-check-circle fs-3 text-success flex-shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-3 mb-7">
                        <i class="ki-outline ki-cross-circle fs-3 text-danger flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="fv-row mb-8">
                        <label class="form-label fw-semibold text-gray-900 fs-6">{{ __('auth.email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="{{ __('auth.email_placeholder') }}"
                               autocomplete="email"
                               class="form-control form-control-lg form-control-solid"
                               required autofocus />
                    </div>

                    <div class="d-grid mb-8">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ __('auth.send_reset_link') }}
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="link-primary fw-semibold fs-6">
                            {{ __('auth.back_to_login') }}
                        </a>
                    </div>

                </form>

            </div>
        </div>

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
