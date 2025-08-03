@php
use Illuminate\Support\Facades\Auth;
use App\Models\PemesananDetail;

$cartCount = Auth::check()
    ? PemesananDetail::where('pelanggan_id', Auth::id())->whereNull('pemesanan_id')->count()
    : 0;
@endphp

@vite('resources/js/app.js')

<nav class="bg-gray-100" x-data="{ mobileMenu: false }">
    <div class="flex max-w-screen-xl mx-auto py-4 px-4 items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/beranda">
                <img src="/assets/LogoBengkel.png" alt="Logo" class="h-10 w-10 rounded-full object-cover" />
            </a>
        </div>

        <div class="lg:hidden">
            <button @click="mobileMenu = !mobileMenu" class="focus:outline-none">
                <svg x-show="!mobileMenu" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="hidden lg:flex justify-between items-center w-full ml-10">
            <ul class="flex items-center space-x-4 text-sm font-semibold text-black">
                <li>
                    <a href="/beranda" class="text-base pb-1 {{ request()->is('beranda') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                        Beranda
                    </a>
                </li>
                <li class="border h-5 border-black"></li>
                <li>
                    <a href="/katalog" class="text-base pb-1 {{ request()->is('katalog') || request()->is('produk/*') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                        Katalog
                    </a>
                </li>
            </ul>

            <div class="flex items-center space-x-4 text-sm font-semibold">
                @if (Auth::check())
                <a href="{{ route('keranjang.index') }}" class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-700 hover:text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 10H6L5 8zM9 8V6a3 3 0 016 0v2" />
                    </svg>
                    @if($cartCount > 0)
                    <span id="cartBadge" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        {{ $cartCount }}
                    </span>
                    @endif
                </a>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="/assets/user.png" alt="Profil" class="h-10 w-10 rounded-full object-cover border border-gray-300 hover:border-black transition" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 min-w-[160px] bg-white shadow-lg border rounded-md py-2 z-50 space-y-1">
                        <a href="/profil" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Profil</a>
                        <a href="/pesanan" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Status Pesanan</a>
                        <hr class="my-1 border-black" />
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-medium rounded-md">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login', ['next' => request()->path()]) }}" class="text-base pb-1 {{ request()->is('login') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                    Login
                </a>
                <a href="{{ route('register') }}" class="text-base pb-1 border border-black px-3 py-1 rounded {{ request()->is('register') ? 'border-b-3 border-black font-bold' : 'hover:bg-gray-200' }}">
                    Daftar
                </a>
                @endif
            </div>
        </div>
    </div>

    <div x-show="mobileMenu" x-cloak class="fixed right-0 top-0 h-full w-72 max-w-full bg-gray-100 z-50 shadow-lg overflow-y-auto p-6 lg:hidden space-y-6" @keydown.window.escape="mobileMenu = false">
        <div class="flex justify-end">
            <button @click="mobileMenu = false" class="text-gray-600 hover:text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-3 text-sm">
            <a href="/beranda" class="block px-4 py-2 rounded font-medium {{ request()->is('beranda') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                Beranda
            </a>
            <a href="/katalog" class="block px-4 py-2 rounded font-medium {{ request()->is('katalog') || request()->is('produk/*') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                Katalog
            </a>
        </div>

        <hr class="border-black my-3">

        <div class="space-y-3 text-sm">
            @if (Auth::check())
            <a href="/profil" class="block px-4 py-2 rounded font-medium {{ request()->is('profil') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                Profil
            </a>
            <a href="{{ route('keranjang.index') }}" class="flex items-center justify-between px-4 py-2 rounded font-medium {{ request()->is('keranjang') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                <span>Keranjang</span>
                @if($cartCount > 0)
                <span class="bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                    {{ $cartCount }}
                </span>
                @endif
            </a>
            <a href="/pesanan" class="block px-4 py-2 rounded font-medium {{ request()->is('pesanan') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                Status Pesanan
            </a>
            <hr class="border-black my-3">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 rounded text-red-600 hover:bg-red-100">
                    Logout
                </button>
            </form>
            @else
            <a href="{{ route('login', ['next' => request()->path()]) }}" class="block px-4 py-2 rounded font-medium {{ request()->is('login') ? 'text-black bg-gray-200 ring-1 ring-black/10' : 'text-black hover:bg-gray-200' }}">
                Login
            </a>
            <a href="{{ route('register') }}" class="block px-4 py-2 rounded font-medium text-black hover:bg-gray-200 transition {{ request()->is('register') ? 'bg-gray-200 ring-1 ring-black/10' : '' }}">
                Daftar
            </a>
            @endif
        </div>
    </div>
</nav>
