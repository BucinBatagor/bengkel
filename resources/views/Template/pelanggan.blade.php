<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Beranda')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('assets/LogoBengkel.png') }}" type="image/png">
  <style>
    .hide-scrollbar{scrollbar-width:none;-ms-overflow-style:none}
    .hide-scrollbar::-webkit-scrollbar{display:none}
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-clear-button,
    input[type="password"]::-webkit-inner-spin-button,
    input[type="password"]::-webkit-contacts-auto-fill-button{
      display:none!important;visibility:hidden!important
    }
  </style>
</head>
<body class="flex flex-col min-h-screen bg-white">
  @include('Template.navbar')
  <main class="flex-grow">
    @yield('content')
  </main>
  @include('Template.footer')
</body>
</html>
