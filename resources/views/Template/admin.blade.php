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
    <div class="flex min-h-screen">
        <aside class="w-64 bg-black text-white min-h-screen flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 font-bold text-xl border-b border-gray-800 flex items-center gap-3">
                    <span>Menu Admin</span>
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

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
