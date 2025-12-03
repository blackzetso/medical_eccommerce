<!DOCTYPE html>
@php
    $locale = session('locale', app()->getLocale());
    $currentLanguage = \App\Models\Language::where('code', $locale)
        ->where('status', 'enabled')
        ->first();
    $dir = ($currentLanguage && $currentLanguage->is_rtl) ? 'rtl' : 'ltr';
@endphp
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>


        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="author" content="StackBros">
        <meta name="description" content="Eduport- LMS, Education and Course Theme">

        <!-- Dark mode -->
        <script>
            const storedTheme = localStorage.getItem('theme')

            const getPreferredTheme = () => {
                if (storedTheme) {
                    return storedTheme
                }
                return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'light'
            }

            const setTheme = function (theme) {
                if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.setAttribute('data-bs-theme', 'dark')
                } else {
                    document.documentElement.setAttribute('data-bs-theme', theme)
                }
            }

            setTheme(getPreferredTheme())

            window.addEventListener('DOMContentLoaded', () => {
                var el = document.querySelector('.theme-icon-active');
                if(el != 'undefined' && el != null) {
                    const showActiveTheme = theme => {
                    const activeThemeIcon = document.querySelector('.theme-icon-active use')
                    const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
                    const svgOfActiveBtn = btnToActive.querySelector('.mode-switch use').getAttribute('href')

                    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
                        element.classList.remove('active')
                    })

                    btnToActive.classList.add('active')
                    activeThemeIcon.setAttribute('href', svgOfActiveBtn)
                }

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                    if (storedTheme !== 'light' || storedTheme !== 'dark') {
                        setTheme(getPreferredTheme())
                    }
                })

                showActiveTheme(getPreferredTheme())

                document.querySelectorAll('[data-bs-theme-value]')
                    .forEach(toggle => {
                        toggle.addEventListener('click', () => {
                            const theme = toggle.getAttribute('data-bs-theme-value')
                            localStorage.setItem('theme', theme)
                            setTheme(theme)
                            showActiveTheme(theme)
                        })
                    })

                }
            })

        </script>

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('admin/theme1/images/favicon.ico') }}">

        <!-- Google Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com/">
        <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&amp;display=swap">
        <!-- Plugins CSS -->
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/theme1/vendor/font-awesome/css/all.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/theme1/vendor/bootstrap-icons/bootstrap-icons.css') }}">
        {{-- <link rel="stylesheet" type="text/css" href="{{ asset('admin/theme1/vendor/apexcharts/css/apexcharts.css') }}"> --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/theme1/vendor/overlay-scrollbar/css/overlayscrollbars.min.css') }}">

        <!-- Theme CSS -->
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/theme1/css/style.css') }}">
        <!-- Plugins CSS -->
        
        <!-- Cairo Font Styles -->
        <style>
            body, h1, h2, h3, h4, h5, h6, a, p, span, div, button, input, textarea, select, label, optgroup, option, li, td, th, ul, nav, .navbar, .sidebar, .page-content, .dropdown-menu, .list-group-item, .card, .btn, .form-control {
                font-family: 'Cairo', sans-serif !important;
            }
        </style>

        <!-- RTL Styles for Admin Panel -->
        @if($dir === 'rtl')
        <style>
            /* Base RTL Direction */
            html[dir="rtl"] {
                direction: rtl;
            }

            html[dir="rtl"] body {
                direction: rtl;
                text-align: right;
            }

            /* Sidebar RTL */
            html[dir="rtl"] .sidebar {
                left: auto !important;
                right: 0 !important;
            }

            html[dir="rtl"] .offcanvas-start {
                left: auto !important;
                right: 0 !important;
                transform: translateX(100%) !important;
            }

            html[dir="rtl"] .offcanvas-start.show {
                transform: translateX(0) !important;
            }

            html[dir="rtl"] .offcanvas-start:not(.show) {
                transform: translateX(100%) !important;
            }

            /* Fix offcanvas visibility in RTL - show sidebar content on large screens */
            @media (min-width: 1200px) {
                html[dir="rtl"] .sidebar.navbar-expand-xl .offcanvas {
                    position: static !important;
                    visibility: visible !important;
                    transform: none !important;
                    background-color: transparent !important;
                    border: 0 !important;
                    display: block !important;
                    z-index: auto !important;
                    width: auto !important;
                    height: auto !important;
                }

                html[dir="rtl"] .sidebar.navbar-expand-xl .offcanvas-body {
                    visibility: visible !important;
                    display: flex !important;
                    padding: 0 !important;
                    overflow-y: visible !important;
                }

                html[dir="rtl"] .sidebar.navbar-expand-xl .offcanvas.show {
                    transform: none !important;
                }

                /* Ensure sidebar content is visible */
                html[dir="rtl"] .sidebar.navbar-expand-xl .sidebar-content {
                    display: flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }

                html[dir="rtl"] .sidebar.navbar-expand-xl #navbar-sidebar {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }

                html[dir="rtl"] .sidebar.navbar-expand-xl .navbar-nav {
                    display: block !important;
                    visibility: visible !important;
                }
            }

            /* Page Content RTL */
            html[dir="rtl"] .page-content {
                margin-left: 0 !important;
                margin-right: 280px !important;
            }

            @media (max-width: 1199.98px) {
                html[dir="rtl"] .page-content {
                    margin-right: 0 !important;
                }
            }

            /* Sidebar positioning fix */
            @media (min-width: 1200px) {
                html[dir="rtl"] .sidebar.navbar-expand-xl {
                    position: fixed !important;
                    top: 0 !important;
                    right: 0 !important;
                    left: auto !important;
                    height: 100vh !important;
                    z-index: 1030 !important;
                }
            }

            @media (min-width: 576px) {
                html[dir="rtl"] .sidebar.navbar-expand-sm {
                    left: auto !important;
                    right: 0 !important;
                }
            }

            @media (max-width: 575.98px) {
                html[dir="rtl"] .sidebar.navbar-expand-sm {
                    left: auto !important;
                    right: -300px !important;
                }
            }

            /* Sidebar content padding RTL - main menu items */
            html[dir="rtl"] .sidebar-content .navbar-nav > .nav-item > .nav-link {
                padding-right: 0.75rem !important;
                padding-left: 0 !important;
            }

            /* Submenu items padding RTL - reduce excessive padding */
            html[dir="rtl"] .sidebar-content .navbar-nav .nav.flex-column .nav-link {
                padding-right: 0.5rem !important;
                padding-left: 0 !important;
            }

            /* Nested submenu items - even less padding */
            html[dir="rtl"] .sidebar-content .navbar-nav .nav .nav .nav-link {
                padding-right: 0.5rem !important;
                padding-left: 0 !important;
            }

            /* Collapse arrows RTL */
            html[dir="rtl"] .sidebar-content .nav-item [data-bs-toggle=collapse]:before {
                right: auto !important;
                left: 0px !important;
            }

            html[dir="rtl"] .sidebar-content .nav-item [data-bs-toggle=collapse]:after {
                right: auto !important;
                left: 5px !important;
            }

            /* Text Alignment */
            html[dir="rtl"] .text-start {
                text-align: right !important;
            }

            html[dir="rtl"] .text-end {
                text-align: left !important;
            }

            /* Margins RTL */
            html[dir="rtl"] .me-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
            html[dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
            html[dir="rtl"] .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
            html[dir="rtl"] .me-4 { margin-left: 1.5rem !important; margin-right: 0 !important; }
            html[dir="rtl"] .me-5 { margin-left: 3rem !important; margin-right: 0 !important; }
            html[dir="rtl"] .ms-1 { margin-right: 0.25rem !important; margin-left: 0 !important; }
            html[dir="rtl"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
            html[dir="rtl"] .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
            html[dir="rtl"] .ms-4 { margin-right: 1.5rem !important; margin-left: 0 !important; }
            html[dir="rtl"] .ms-5 { margin-right: 3rem !important; margin-left: 0 !important; }
            html[dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
            html[dir="rtl"] .me-auto { margin-left: auto !important; margin-right: 0 !important; }

            /* Padding RTL */
            html[dir="rtl"] .pe-1 { padding-left: 0.25rem !important; padding-right: 0 !important; }
            html[dir="rtl"] .pe-2 { padding-left: 0.5rem !important; padding-right: 0 !important; }
            html[dir="rtl"] .pe-3 { padding-left: 1rem !important; padding-right: 0 !important; }
            html[dir="rtl"] .pe-4 { padding-left: 1.5rem !important; padding-right: 0 !important; }
            html[dir="rtl"] .pe-5 { padding-left: 3rem !important; padding-right: 0 !important; }
            html[dir="rtl"] .ps-1 { padding-right: 0.25rem !important; padding-left: 0 !important; }
            html[dir="rtl"] .ps-2 { padding-right: 0.5rem !important; padding-left: 0 !important; }
            html[dir="rtl"] .ps-3 { padding-right: 1rem !important; padding-left: 0 !important; }
            html[dir="rtl"] .ps-4 { padding-right: 1.5rem !important; padding-left: 0 !important; }
            html[dir="rtl"] .ps-5 { padding-right: 3rem !important; padding-left: 0 !important; }

            /* Flexbox RTL - only for horizontal flex containers */
            html[dir="rtl"] .d-flex:not(.flex-column):not(.flex-column-reverse) {
                flex-direction: row-reverse;
            }

            html[dir="rtl"] .justify-content-start {
                justify-content: flex-end !important;
            }

            html[dir="rtl"] .justify-content-end {
                justify-content: flex-start !important;
            }

            /* Keep column direction for vertical lists */
            html[dir="rtl"] .flex-column,
            html[dir="rtl"] .navbar-nav.flex-column {
                flex-direction: column !important;
            }

            /* Top Bar RTL */
            html[dir="rtl"] .top-bar {
                direction: rtl;
            }

            html[dir="rtl"] .navbar-toggler {
                margin-left: 0 !important;
                margin-right: auto !important;
            }

            /* Dropdown RTL */
            html[dir="rtl"] .dropdown-menu-end {
                left: 0 !important;
                right: auto !important;
            }

            html[dir="rtl"] .dropdown-menu {
                text-align: right;
            }

            /* Icons RTL */
            html[dir="rtl"] .fa-fw.me-2,
            html[dir="rtl"] .bi.me-2 {
                margin-left: 0.5rem !important;
                margin-right: 0 !important;
            }

            /* Form Controls RTL */
            html[dir="rtl"] .form-control,
            html[dir="rtl"] .form-select {
                text-align: right;
            }

            html[dir="rtl"] .form-check {
                padding-right: 1.5em;
                padding-left: 0;
            }

            html[dir="rtl"] .form-check-input {
                float: right;
                margin-right: -1.5em;
                margin-left: 0;
            }

            /* Table RTL */
            html[dir="rtl"] table {
                direction: rtl;
            }

            html[dir="rtl"] .table th,
            html[dir="rtl"] .table td {
                text-align: right;
            }

            /* Card RTL */
            html[dir="rtl"] .card {
                text-align: right;
            }

            /* Button Groups RTL */
            html[dir="rtl"] .btn-group {
                flex-direction: row-reverse;
            }

            /* Badge RTL */
            html[dir="rtl"] .badge {
                margin-left: 0.25rem;
                margin-right: 0;
            }

            /* List Group RTL */
            html[dir="rtl"] .list-group-item {
                text-align: right;
            }

            /* Navbar Nav RTL - only for horizontal navbars */
            html[dir="rtl"] .navbar-nav:not(.flex-column) {
                flex-direction: row-reverse;
            }

            /* Position Utilities RTL */
            html[dir="rtl"] .start-0 { right: 0 !important; left: auto !important; }
            html[dir="rtl"] .start-50 { right: 50% !important; left: auto !important; }
            html[dir="rtl"] .start-100 { right: 100% !important; left: auto !important; }
            html[dir="rtl"] .end-0 { left: 0 !important; right: auto !important; }
            html[dir="rtl"] .end-50 { left: 50% !important; right: auto !important; }
            html[dir="rtl"] .end-100 { left: 100% !important; right: auto !important; }

            /* Top Bar Container RTL */
            html[dir="rtl"] .container-fluid {
                direction: rtl;
            }

            /* Input Group RTL */
            html[dir="rtl"] .input-group > .form-control,
            html[dir="rtl"] .input-group > .form-select {
                border-radius: 0 var(--bs-border-radius) var(--bs-border-radius) 0;
            }

            html[dir="rtl"] .input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
                margin-right: -1px;
                margin-left: 0;
                border-radius: var(--bs-border-radius) 0 0 var(--bs-border-radius);
            }

            /* Card Header/Footer RTL */
            html[dir="rtl"] .card-header,
            html[dir="rtl"] .card-footer {
                text-align: right;
            }

            /* Alert RTL */
            html[dir="rtl"] .alert {
                text-align: right;
            }

            /* Breadcrumb RTL */
            html[dir="rtl"] .breadcrumb {
                flex-direction: row-reverse;
            }

            /* Pagination RTL */
            html[dir="rtl"] .pagination {
                flex-direction: row-reverse;
            }

            /* Modal RTL */
            html[dir="rtl"] .modal-header .btn-close {
                margin: -0.5rem auto -0.5rem -0.5rem;
            }

            /* Tooltip/Popover RTL */
            html[dir="rtl"] .tooltip {
                direction: rtl;
            }

            /* Nav Tabs RTL */
            html[dir="rtl"] .nav-tabs {
                flex-direction: row-reverse;
            }

            /* Translate RTL */
            html[dir="rtl"] .translate-middle-x {
                transform: translateX(50%) !important;
            }

            /* Border RTL */
            html[dir="rtl"] .border-start { border-right: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color) !important; border-left: 0 !important; }
            html[dir="rtl"] .border-end { border-left: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color) !important; border-right: 0 !important; }

            /* Back to Top RTL */
            html[dir="rtl"] .back-top {
                left: 40px !important;
                right: auto !important;
            }

            @media (max-width: 767.98px) {
                html[dir="rtl"] .back-top {
                    left: 10px !important;
                    right: auto !important;
                }
            }

            /* Sidebar Menu RTL */
            html[dir="rtl"] .sidebar .navbar-nav {
                direction: rtl;
            }

            html[dir="rtl"] .sidebar .nav-link {
                text-align: right;
            }

            /* Search Input RTL */
            html[dir="rtl"] .form-control[type="search"] {
                padding-right: 0.75rem;
                padding-left: 2.5rem;
            }

            html[dir="rtl"] .position-relative .position-absolute.end-0 {
                left: 0 !important;
                right: auto !important;
            }
        </style>
        @endif



        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <!-- Back to top -->
        <div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>

        <!-- Bootstrap JS -->
        <script src="{{ asset('admin/theme1/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>



        <!-- Vendors -->
        {{-- <script src="{{ asset('admin/theme1/vendor/choices/js/choices.min.js') }}"></script> --}}
        <script src="{{ asset('admin/theme1/vendor/glightbox/js/glightbox.js') }}"></script>
        {{-- <script src="{{ asset('admin/theme1/vendor/quill/js/quill.min.js') }}"></script> --}}
        <script src="{{ asset('admin/theme1/vendor/purecounterjs/dist/purecounter_vanilla.js') }}"></script>

        {{-- <script src="{{ asset('admin/theme1/vendor/apexcharts/js/apexcharts.min.js') }}"></script> --}}
        <script src="{{ asset('admin/theme1/vendor/overlay-scrollbar/js/overlayscrollbars.min.js') }}"></script>

        <!-- Template Functions -->
        <script src="{{ asset('admin/theme1/js/functions.js') }}"></script>
    </body>
</html>
