@php
use Illuminate\Support\Facades\Auth;
@endphp

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<nav class="bg-gray-100">
    <div class="flex max-w-screen-xl mx-auto py-4 items-center justify-between">
        <div class="flex items-center space-x-5">
            <a href="/beranda">
                <img src="/assets/LogoBengkel.png" alt="Logo" class="h-10 w-10 rounded-full object-cover" />
            </a>

            <ul class="flex items-center space-x-4 text-sm font-semibold text-black">
                <li>
                    <a href="/beranda"
                        class="text-base pb-1 {{ request()->is('beranda') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                        Beranda
                    </a>
                </li>
                <li class="border h-5 border-black"></li>
                <li>
                    <a href="/katalog"
                        class="text-base pb-1 {{ request()->is('katalog') || request()->is('produk/*') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                        Katalog
                    </a>
                </li>
            </ul>
        </div>

        <div class="flex items-center space-x-4 text-sm font-semibold">
            @if (Auth::check())
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                    <img src="/assets/user.png" alt="Profil"
                        class="h-10 w-10 rounded-full object-cover border border-gray-300 hover:border-black transition" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.away="open = false"
                    class="absolute right-0 mt-2 min-w-[160px] bg-white shadow-lg border rounded-md py-2 z-50 space-y-1">

                    <a href="/profil"
                        class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">
                        Profil
                    </a>

                    <a href="/pembayaran"
                        class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">
                        Pesanan
                    </a>

                    <a href="/riwayat"
                        class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">
                        Riwayat Pesanan
                    </a>

                    <hr class="my-1 border-gray-200" />

                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-medium rounded-md">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login', ['next' => request()->path()]) }}"
                class="text-base pb-1 {{ request()->is('login') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                Login
            </a>

            <a href="{{ route('register') }}"
                class="text-base pb-1 border border-black px-3 py-1 rounded 
                          {{ request()->is('register') ? 'border-b-3 border-black font-bold' : 'hover:bg-gray-200' }}">
                Daftar
            </a>
            @endif
        </div>
    </div>
</nav>