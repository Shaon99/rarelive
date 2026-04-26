<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> {{ @$general->sitename }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ getFile('favicon', @$general->favicon) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100..900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        roboto: ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#0A0B0F',
                            dark: '#070809',
                            light: '#141518'
                        },
                        accent: '#CCFF00',
                        darkBg: '#0A0B0F'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-white">
    @yield('content')

    @stack('scripts')
</body>

</html>
