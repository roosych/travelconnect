<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Sign In') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('ui_template/assets/media/logos/fav-dark.png') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('ui_template/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('ui_template/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank app-blank">

    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        @yield('content')
    </div>

    <script>var hostUrl = "{{ asset('ui_template/assets/') }}/";</script>
    <script src="{{ asset('ui_template/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('ui_template/assets/js/scripts.bundle.js') }}"></script>
    @stack('scripts')
</body>
</html>
