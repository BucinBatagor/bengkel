<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="{{ asset('assets/LogoBengkel.png') }}" type="image/png">
</head>

<body class="bg-white">
    @yield('content')
</body>

</html>