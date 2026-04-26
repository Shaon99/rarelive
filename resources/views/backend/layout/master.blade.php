<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" type="image/png" href="{{ getFile('favicon', $general->favicon) }}">
    <title>
        {{ __(@$pageTitle) }}@if(!empty($general->sitename)) - {{ __($general->sitename) }}@endif
    </title>

    @include('backend.layout.css')
    @stack('style')
</head>

<body class="{{ $sidebar_gone??'' }}">

    <div id="toastContainer" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1050; width: 100%; max-width: 350px; padding: 0 10px; box-sizing: border-box;"></div>

    @include('backend.layout.navbar')
    @include('backend.layout.sidebar')

    @yield('content')

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top btn btn-primary rounded-circle shadow"
        style="position: fixed; bottom: 30px; right: 30px; display: none; z-index: 999;">
        <i class="fas fa-arrow-up"></i>
    </a>
    @include('backend.layout.footer')

    @include('backend.layout.modal')

    @include('backend.layout.js')

    @stack('script')
</body>

</html>
