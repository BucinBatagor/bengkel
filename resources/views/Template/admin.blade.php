<!DOCTYPE html>
<html lang="id" x-data="sidebarHandler()" x-init="init()" class="h-screen">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('assets/LogoBengkel.png') }}" type="image/png">
</head>
<body class="h-screen flex flex-col bg-gray-100 text-gray-800">

    <div class="md:hidden fixed top-0 left-0 w-full z-[100] bg-black text-white flex items-center p-4 justify-between">
        <button @click="sidebarOpen = true" x-show="!sidebarOpen" x-cloak class="focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <button @click="sidebarOpen = false" x-show="sidebarOpen" x-cloak class="focus:outline-none">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex flex-1 overflow-hidden pt-16 md:pt-0">
        <aside
            x-show="sidebarOpen"
            x-cloak
            :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
            class="fixed inset-y-0 left-0 z-40 w-64 h-screen bg-black text-white transform transition-transform duration-300 ease-in-out
                   flex flex-col justify-between md:translate-x-0 md:relative md:flex"
        >
            <div class="md:hidden flex justify-end p-4">
                <button @click="sidebarOpen = false">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 flex flex-col overflow-y-auto">
                <div class="px-6 py-4 font-bold text-xl border-b border-gray-800 flex items-center gap-3">
                    <span class="hidden md:inline" :class="{ 'inline': sidebarOpen }">Menu Admin</span>
                </div>

                <nav class="text-sm mt-2 space-y-1">
                    <a href="{{ route('admin.katalog.index') }}"
                       class="flex items-center gap-3 px-6 py-3 font-medium transition
                       {{ request()->routeIs('admin.katalog.*') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 text-white' }}">
                        <i class="fa-solid fa-box"></i>
                        Katalog Produk
                    </a>
                    <a href="{{ route('admin.pelanggan.index') }}"
                       class="flex items-center gap-3 px-6 py-3 font-medium transition
                       {{ request()->routeIs('admin.pelanggan.*') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 text-white' }}">
                        <i class="fa-solid fa-users"></i>
                        Akun Pelanggan
                    </a>
                    <a href="{{ route('admin.pemesanan.index') }}"
                       class="flex items-center gap-3 px-6 py-3 font-medium transition
                       {{ request()->routeIs('admin.pemesanan.*') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 text-white' }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        Pesanan Masuk
                    </a>
                    <a href="{{ route('admin.laporan.index') }}"
                       class="flex items-center gap-3 px-6 py-3 font-medium transition
                       {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 text-white' }}">
                        <i class="fa-solid fa-chart-column"></i>
                        Laporan Pendapatan
                    </a>
                </nav>
            </div>

            <form method="POST" action="{{ route('admin.logout') }}" class="px-6 py-4 border-t border-gray-800">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 text-red-400 hover:text-white hover:bg-red-600 px-3 py-2 rounded transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </button>
            </form>
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
