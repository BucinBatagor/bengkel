@php
$cartCount = 0;
$isAuth = auth()->check();
if ($isAuth) {
    $cartCount = \App\Models\PemesananDetail::where('pelanggan_id', auth()->id())
        ->whereNull('pemesanan_id')
        ->count();
}
@endphp

<style>
  .nav-link { position: relative; padding-bottom: .25rem; display:inline-flex; align-items:center; }
  .nav-link:hover::after { content:""; position:absolute; left:-6px; right:-6px; bottom:-2px; height:3px; background:#9ca3af; border-radius:2px; }
  .nav-link--active { font-weight:700; color:#111827; }
  .nav-link--active::after { content:""; position:absolute; left:-6px; right:-6px; bottom:-2px; height:3px; background:#000; border-radius:2px; }
</style>

<nav
  class="bg-gray-100"
  x-data="{ mobileMenu: false, closeOnDesktop(){ if (window.innerWidth >= 1024) this.mobileMenu = false } }"
  @resize.window="closeOnDesktop()"
  x-effect="document.body.classList.toggle('overflow-hidden', mobileMenu)"
>
  <div class="flex max-w-screen-xl mx-auto py-4 px-4 items-center justify-between">
    <div class="flex items-center space-x-4">
      @php $homeLink = $isAuth ? url('/katalog') : url('/beranda'); @endphp
      <a href="{{ $homeLink }}" aria-label="Home">
        <img src="/assets/LogoBengkel.png" alt="Logo" class="h-10 w-10 rounded-full object-cover">
      </a>
    </div>

    <div class="lg:hidden">
      <button @click="mobileMenu = !mobileMenu" class="focus:outline-none" aria-label="Toggle menu">
        <svg x-show="!mobileMenu" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="mobileMenu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="hidden lg:flex justify-between items-center w-full ml-10">
      <ul class="flex items-center text-sm font-semibold text-black">
        @if (!$isAuth)
          {{-- Guest: Beranda | Katalog (1 garis di tengah) --}}
          <li class="mr-4">
            <a href="/beranda" class="nav-link text-base {{ request()->is('beranda') ? 'nav-link--active' : '' }}">
              Beranda
            </a>
          </li>
          <li aria-hidden="true" class="w-px h-5 bg-black mx-2"></li>
          <li class="ml-4">
            <a href="/katalog" class="nav-link text-base {{ request()->is('katalog') || request()->is('produk/*') ? 'nav-link--active' : '' }}">
              Katalog
            </a>
          </li>
        @else
          {{-- Login: | Katalog | (garis kiri & kanan) --}}
          <li aria-hidden="true" class="w-px h-5 bg-black mr-4"></li>
          <li>
            <a href="/katalog" class="nav-link text-base {{ request()->is('katalog') || request()->is('produk/*') ? 'nav-link--active' : '' }}">
              Katalog
            </a>
          </li>
        @endif
      </ul>

      <div class="flex items-center space-x-4 text-sm font-semibold">
        @if ($isAuth)
          <a href="{{ route('keranjang.index') }}" class="relative" aria-label="Keranjang">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-700 hover:text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 10H6L5 8zM9 8V6a3 3 0 016 0v2"/></svg>
            <span id="cartBadge"
              x-show="$store.cart && $store.cart.count > 0"
              x-text="$store.cart ? $store.cart.count : 0"
              class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"></span>
          </a>

          <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none" aria-haspopup="menu" :aria-expanded="open">
              <img src="/assets/user.png" alt="Profil" class="h-10 w-10 rounded-full object-cover border border-gray-300 hover:border-black transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 min-w-[160px] bg-white shadow-lg border rounded-md py-2 z-50 space-y-1">
              <a href="/profil" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Profil</a>
              <a href="/pesanan" class="block px-4 py-2 hover:bg-gray-100 text-sm font-medium rounded-md">Riwayat Pesanan</a>
              <hr class="my-1 border-black">
              <form method="POST" action="/logout">@csrf<button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-medium rounded-md">Logout</button></form>
            </div>
          </div>
        @else
          <a href="{{ route('login') }}" class="nav-link text-base {{ request()->is('login') ? 'nav-link--active' : '' }}">Login</a>
          <a href="{{ route('register') }}" class="text-base border border-black px-3 py-1 rounded bg-white">Daftar</a>
        @endif
      </div>
    </div>
  </div>

  <div class="lg:hidden">
    <div x-show="mobileMenu" x-cloak class="fixed inset-0 bg-black/30 z-40" @click="mobileMenu = false" aria-hidden="true"></div>
    <div x-show="mobileMenu" x-cloak class="fixed right-0 top-0 h-full w-72 max-w-full bg-gray-100 z-50 shadow-lg overflow-y-auto p-4" @keydown.window.escape="mobileMenu = false" @click.stop role="dialog" aria-modal="true" aria-label="Menu">
      <div class="flex justify-end mb-2">
        <button @click="mobileMenu = false" class="text-gray-600" aria-label="Tutup menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="space-y-2 text-sm">
        @if (!$isAuth) 
          <a href="/beranda" @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Beranda</a> 
        @endif
        <a href="/katalog" @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Katalog</a>
      </div>

      <div class="space-y-2 text-sm mt-3">
        @if ($isAuth)
          <a href="{{ route('keranjang.index') }}" @click="mobileMenu=false" class="flex items-center justify-between w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">
            <span>Keranjang</span>
            <span id="cartBadgeMobile"
              x-show="$store.cart && $store.cart.count > 0"
              x-text="$store.cart ? $store.cart.count : 0"
              class="bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"></span>
          </a>
          <a href="/profil"  @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Profil</a>
          <a href="/pesanan" @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Riwayat Pesanan</a>
          <form method="POST" action="/logout" class="mt-1">@csrf<button type="submit" class="block w-full text-left px-3 py-2 border border-black rounded bg-white font-medium text-red-600">Logout</button></form>
        @else
          <a href="{{ route('login') }}" @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Login</a>
          <a href="{{ route('register') }}" @click="mobileMenu=false" class="block w-full px-3 py-2 border border-black rounded bg-white font-medium text-black">Daftar</a>
        @endif
      </div>
    </div>
  </div>
</nav>

<script>
  document.addEventListener('alpine:init', () => {
    const initialCount = {{ (int) $cartCount }};
    try {
      if (Alpine.store('cart')) Alpine.store('cart').count = initialCount;
      else Alpine.store('cart', { count: initialCount });
    } catch (e) {}
  });
</script>
