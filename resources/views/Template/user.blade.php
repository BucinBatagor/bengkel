<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Beranda')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('assets/LogoBengkel.png') }}" type="image/png">
</head>

<body class="flex flex-col min-h-screen bg-white">
    @include('Template.navbar')

    <main class="flex-grow">
        @yield('content')
    </main>

    @include('Template.footer')
</body>
</html>
