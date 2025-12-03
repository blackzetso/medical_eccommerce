<!DOCTYPE html>
@php
    $locale = session('locale', app()->getLocale());
    $dir = in_array($locale, ['ar', 'ar_SA', 'ar-EG']) ? 'rtl' : 'ltr';
@endphp
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    @inertiaHead

    <meta name="keywords" content="HTML5 Template" />
    <meta name="description" content="Porto - Bootstrap eCommerce Template">
    <meta name="author" content="SW-THEMES">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('front/theme1/images/icons/favicon.png') }}">

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

    <script>
        WebFontConfig = {
            google: { families: [ 'Open+Sans:300,400,600,700,800', 'Poppins:300,400,500,600,700' ] }
        };
        ( function ( d ) {
            var wf = d.createElement( 'script' ), s = d.scripts[ 0 ];
            wf.src = '{{ asset('front/theme1/js/webfont.js') }}';
            wf.async = true;
            s.parentNode.insertBefore( wf, s );
        } )( document );
    </script>

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('front/theme1/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/theme1/css/demo3.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('front/theme1/vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('front/theme1/vendor/simple-line-icons/css/simple-line-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/theme1/css/demo/custome.css') }}">

    <style>
        body,h1,h2,h3,h4,h5,h6,a,p,span,div,button,input,textarea, select , label , optgroup, option , li , td , th , a , ul , nav {
            font-family: 'Cairo', sans-serif !important;
        }
    </style>
</head>

<body class="{{ $dir === 'rtl' ? 'client-rtl' : 'client-ltr' }}">
    <!-- End .page-wrapper -->
    @inertia

    <!-- End .mobile-menu-container -->
    <!-- Plugins JS File -->
    <script src="{{ asset('front/theme1/js/jquery.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/plugins.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/jquery.appear.min.js') }}"></script>
    <script src="{{ asset('front/theme1/js/main.min.js') }}"></script>
</body>

</html>
