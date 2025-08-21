<!DOCTYPE html>
<html lang="id" x-data="sidebarHandler()" x-init="init()" class="h-screen">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title')</title>
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
    input[type="password"]::-webkit-contacts-auto-fill-button {
      display: none !important;visibility: hidden !important;
    }
  </style>
</head>
<body class="h-screen flex flex-col bg-gray-100 text-gray-800">
  <div class="md:hidden fixed top-0 left-0 w-full z-[100] bg-black text-white flex items-center p-4 justify-between">
    <button @click="sidebarOpen = true" x-show="!sidebarOpen" x-cloak class="focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <button @click="sidebarOpen = false" x-show="sidebarOpen" x-cloak class="focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <div class="flex flex-1 overflow-hidden pt-16 md:pt-0">
    <aside
      x-show="sidebarOpen"
      x-cloak
      :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
      class="fixed inset-y-0 left-0 z-40 w-64 h-screen bg-black text-white transform transition-transform duration-300 ease-in-out flex flex-col md:relative md:translate-x-0">
      <div class="md:hidden flex justify-end p-4">
        <button @click="sidebarOpen = false">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto">
        <div class="px-6 py-4 font-bold text-xl border-b border-gray-800">Menu Admin</div>
        <nav class="text-sm mt-2 space-y-1">
          <a href="{{ route('admin.pelanggan.index') }}"
             class="block w-full px-6 py-3 flex items-center gap-3 transition {{ request()->routeIs('admin.pelanggan.*') ? 'bg-gray-600 text-white' : 'hover:bg-gray-800 text-white' }}">
            <i class="fa-solid fa-users"></i>
            Akun Pelanggan
          </a>
          <a href="{{ route('admin.kategori.index') }}"
             class="block w-full px-6 py-3 flex items-center gap-3 transition {{ request()->routeIs('admin.kategori.*') ? 'bg-gray-600 text-white' : 'hover:bg-gray-800 text-white' }}">
            <i class="fa-solid fa-tags"></i>
            Kelola Kategori
          </a>
          <a href="{{ route('admin.katalog.index') }}"
             class="block w-full px-6 py-3 flex items-center gap-3 transition {{ request()->routeIs('admin.katalog.*') ? 'bg-gray-600 text-white' : 'hover:bg-gray-800 text-white' }}">
            <i class="fa-solid fa-box"></i>
            Katalog Produk
          </a>
          <a href="{{ route('admin.pemesanan.index') }}"
             class="block w-full px-6 py-3 flex items-center gap-3 transition {{ request()->routeIs('admin.pemesanan.*') ? 'bg-gray-600 text-white' : 'hover:bg-gray-800 text-white' }}">
            <i class="fa-solid fa-clipboard-list"></i>
            Pesanan Masuk
          </a>
          <a href="{{ route('admin.laporan.index') }}"
             class="block w-full px-6 py-3 flex items-center gap-3 transition {{ request()->routeIs('admin.laporan.*') ? 'bg-gray-600 text-white' : 'hover:bg-gray-800 text-white' }}">
            <i class="fa-solid fa-chart-column"></i>
            Laporan Pendapatan
          </a>
        </nav>
      </div>

      <div>
        <nav class="text-sm space-y-1">
          @php
            $profilOpen = request()->routeIs('admin.profil') || request()->routeIs('admin.profil.password');
          @endphp
          <div
            x-data="{ open: @js($profilOpen), lock: @js($profilOpen) }"
            class="relative"
          >
            <button
              @click="lock ? open = true : open = !open"
              class="block w-full px-6 py-3 flex items-center justify-between gap-3 text-white hover:bg-gray-800 rounded transition"
            >
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-user"></i>
                Profil
              </div>
              <i :class="open ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="w-4 h-4 transition-transform"></i>
            </button>

            <div
              x-show="open"
              x-cloak
              @click.away="if(!lock) open = false"
              x-transition
              class="absolute bottom-full left-0 mb-1 w-full bg-black text-white rounded shadow py-1"
            >
              <a href="{{ route('admin.profil') }}" class="block w-full px-6 py-2 hover:bg-gray-800 transition {{ request()->routeIs('admin.profil') ? 'bg-gray-600' : '' }}">
                Ubah Profil
              </a>
              <a href="{{ route('admin.profil.password') }}" class="block w-full px-6 py-2 hover:bg-gray-800 transition {{ request()->routeIs('admin.profil.password') ? 'bg-gray-600' : '' }}">
                Ubah Password
              </a>
            </div>
          </div>

          <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="block w-full px-6 py-3 flex items-center gap-3 text-red-400 hover:text-white hover:bg-red-800 rounded transition">
              <i class="fa-solid fa-right-from-bracket"></i>
              Logout
            </button>
          </form>
        </nav>
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-6">
      @yield('content')
    </main>
  </div>

  <script>
    function sidebarHandler() {
      return {
        sidebarOpen: window.innerWidth >= 768,
        init() {
          window.addEventListener('resize', () => {
            this.sidebarOpen = window.innerWidth >= 768;
          });
        }
      }
    }
  </script>
</body>
</html>
