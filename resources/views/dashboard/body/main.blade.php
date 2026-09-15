<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>POS Dash</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/backend.css?v=1.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <style>
        @media (min-width: 768px) {
            /* The sidebar owns the desktop logo; keep the navbar as a single control bar. */
            .iq-top-navbar .iq-navbar-logo .header-logo {
                display: none;
            }
        }

        @media (min-width: 1300px) {
            body.sidebar-main .iq-sidebar-logo {
                width: 60px;
                margin-right: 0;
                padding: 15px 0;
                transform: none;
            }

            body.sidebar-main .iq-sidebar-logo .iq-menu-bt-sidebar {
                width: 60px;
                margin: 0 auto !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            body.sidebar-main .iq-sidebar-logo .wrapper-menu {
                margin: 0;
            }
        }

        /* Keep the dashboard shell side-by-side on tablet and desktop screens. */
        @media (min-width: 768px) and (max-width: 1299px) {
            .iq-sidebar {
                display: block;
                left: 0;
                width: 260px;
            }

            .iq-top-navbar {
                width: calc(100% - 260px);
            }

            .content-page {
                margin-left: 260px;
            }

            .iq-sidebar-logo .iq-menu-bt-sidebar {
                display: block;
            }

            .iq-top-navbar .iq-navbar-logo .iq-menu-bt-sidebar {
                display: none;
            }

            body.sidebar-main .iq-sidebar {
                left: 0;
                width: 60px;
            }

            body.sidebar-main .iq-sidebar-logo {
                justify-content: center;
                width: 60px;
                margin-right: 0;
                padding: 15px 0;
            }

            body.sidebar-main .iq-sidebar-logo .header-logo {
                display: none;
            }

            body.sidebar-main .iq-sidebar-logo .iq-menu-bt-sidebar {
                width: 60px;
                margin: 0 auto !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            body.sidebar-main .iq-sidebar-logo .wrapper-menu {
                margin: 0;
            }

            body.sidebar-main .iq-sidebar-menu .iq-menu li a span,
            body.sidebar-main .iq-sidebar-menu .iq-menu li a .iq-arrow-right {
                display: none;
            }

            body.sidebar-main .iq-sidebar-menu .iq-menu li a {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            body.sidebar-main .iq-sidebar-menu .iq-menu .iq-submenu {
                display: none;
            }

            body.sidebar-main .iq-top-navbar {
                width: calc(100% - 70px);
            }

            body.sidebar-main .content-page {
                margin-left: 70px;
            }
        }
    </style>

    @yield('specificpagestyles')
</head>

<body>
    <!-- loader Start -->
    {{-- <div id="loading">
        <div id="loading-center"></div>
    </div> --}}
    <!-- loader END -->

    <!-- Wrapper Start -->
    <div class="wrapper">
        @include('dashboard.body.sidebar')

        @include('dashboard.body.navbar')

        <div class="content-page">
            @yield('container')
        </div>
    </div>
    <!-- Wrapper End-->

    @include('dashboard.body.footer')

    <!-- Backend Bundle JavaScript -->
    <script src="{{ asset('assets/js/backend-bundle.min.js') }}"></script>

    @yield('specificpagescripts')

    <!-- App JavaScript -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
