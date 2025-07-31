@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\PemesananDetail;

    $cartCount = Auth::check()
        ? PemesananDetail::where('pelanggan_id', Auth::id())->whereNull('pemesanan_id')->count()
        : 0;
@endphp

@vite('resources/js/app.js')

<nav class="bg-gray-100">
    <div class="flex max-w-screen-xl mx-auto py-4 items-center justify-between">
        <!-- Logo dan Menu -->
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

        <!-- Bagian Kanan -->
        <div class="flex items-center space-x-4 text-sm font-semibold">
            @if (Auth::check())
                <!-- Ikon Keranjang -->
                <a href="{{ route('keranjang.index') }}" class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-gray-700 hover:text-black" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 8h14l-1 10H6L5 8zM9 8V6a3 3 0 016 0v2" />
                    </svg>
                    @if($cartCount > 0)
                        <span id="cartBadge"
                            class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                <!-- Dropdown Profil -->
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
                        <a href="/profil" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Profil</a>
                        <a href="/pesanan" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Pesanan Saya</a>
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
                <!-- Tombol Login dan Daftar -->
                <a href="{{ route('login', ['next' => request()->path()]) }}"
                    class="text-base pb-1 {{ request()->is('login') ? 'border-b-3 border-black font-bold' : 'hover:border-b-3 hover:border-gray-400' }}">
                    Login
                </a>
                <a href="{{ route('register') }}"
                    class="text-base pb-1 border border-black px-3 py-1 rounded {{ request()->is('register') ? 'border-b-3 border-black font-bold' : 'hover:bg-gray-200' }}">
                    Daftar
                </a>
            @endif
        </div>
    </div>
</nav>
