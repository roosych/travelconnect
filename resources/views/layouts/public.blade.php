<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/fav-dark.png') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />
    @stack('styles')
</head>
<body class="bg-body">

    <div class="d-flex flex-column min-vh-100">

        {{-- Logo --}}
        <div class="text-center pt-8 pb-4">
            <img src="{{ asset('assets/media/logos/caspirex_rfq_logo.jpg') }}"
                 alt="Caspirex" style="height:80px; width:auto;">
        </div>

        {{-- Content --}}
        <div class="flex-grow-1">
            <div class="container-md px-4 pb-8">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-muted fs-8 py-6 mt-8 border-top">
            {{ config('app.name') }} &mdash; Confidential supplier link
        </div>

    </div>

    <script>var hostUrl = "{{ asset('assets/') }}/";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    @stack('scripts')
</body>
</html>
