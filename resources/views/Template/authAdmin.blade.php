<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('assets/LogoBengkel.png') }}" type="image/png">
</head>
<body class="bg-gray-100 text-gray-800">
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>
