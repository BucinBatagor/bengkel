@extends('Template.pelanggan')

@section('title', 'Katalog')

@section('content')
@php
    $appBase = url('/');
    $backUrl = request()->query('back');

    if ($backUrl && !\Illuminate\Support\Str::startsWith($backUrl, $appBase)) {
        $backUrl = null;
    }

    if (!$backUrl) {
        $ref = url()->previous();
        if ($ref && \Illuminate\Support\Str::startsWith($ref, $appBase) && $ref !== url()->current()) {
            $refPath = parse_url($ref, PHP_URL_PATH) ?? '/';
            if (\Illuminate\Support\Str::startsWith($refPath, '/katalog')) {
                $backUrl = $ref;
            }
        }
    }

    $backUrl = $backUrl ?: url('/katalog');
    $waAdmin = $waAdmin ?? '6289644819899';
@endphp

<section
  class="py-10 bg-gray-200 min-h-screen"
  x-data="{
    showLoginPrompt: false,
    showImageModal: false,
    imageModalUrl: '',
    showConfirmModal: false,
    showWaitingModal: false,
    isSubmitting: false,
    emailSentAdmin: false,

    showAddedModal: false,
    addToCartLoading: false,

    setCartCount(n) {
      if (window.Alpine && Alpine.store('cart')) {
        Alpine.store('cart').count = n;
      } else {
        const ids = ['cartBadge','cartBadgeMobile'];
        ids.forEach(id => {
          const el = document.getElementById(id);
          if (!el) return;
          el.textContent = n > 0 ? String(n) : '';
          el.style.display = n > 0 ? 'flex' : 'none';
        });
      }
    },

    async addToCart() {
      if (this.addToCartLoading) return;
      this.addToCartLoading = true;
      try {
        const res = await fetch('{{ route('keranjang.tambah') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ produk_id: {{ $produk->id }} })
        });

        const ct = res.headers.get('content-type') || '';
        const isJson = ct.includes('application/json');
        const data = isJson ? await res.json() : null;

        if (!res.ok) {
          if (res.status === 401) { this.showLoginPrompt = true; return; }
          if (res.status === 419) { alert('Sesi kedaluwarsa. Muat ulang halaman.'); return; }
          alert((data && (data.message || data.error)) || 'Gagal menambahkan ke keranjang.');
          return;
        }

        if (data && typeof data.cart_count !== 'undefined') {
          const parsed = parseInt(data.cart_count, 10);
          this.setCartCount(Number.isNaN(parsed) ? 0 : parsed);
        }
        this.showAddedModal = true;
      } catch (_) {
        alert('Terjadi kesalahan jaringan.');
      } finally {
        this.addToCartLoading = false;
      }
    },

    async pesanSekarang() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      this.emailSentAdmin = false;
      try {
        const res = await fetch('{{ route('keranjang.pesan') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ items: [{{ $produk->id }}], kirim_email: true, buy_now: true })
        });

        const ct = res.headers.get('content-type') || '';
        const isJson = ct.includes('application/json');
        const data = isJson ? await res.json() : null;

        if (!res.ok) {
          if (res.status === 401) { this.showLoginPrompt = true; return; }
          if (res.status === 419) { alert('Sesi kedaluwarsa. Muat ulang halaman.'); return; }
          alert((data && (data.message || data.error)) || 'Gagal memproses pesanan.');
          return;
        }

        if (data && data.success) {
          // Buy Now tidak menyentuh keranjang
          this.emailSentAdmin = !!(data.email_sent_admin ?? false);
          this.showWaitingModal = true;
        } else {
          alert((data && (data.error || data.message)) || 'Gagal membuat pesanan.');
        }
      } catch (_) {
        alert('Terjadi kesalahan jaringan.');
      } finally {
        this.isSubmitting = false;
        this.showConfirmModal = false;
      }
    }
  }">
  <div class="max-w-screen-xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6 min-h-[550px]">
      <div class="mb-4">
        <a href="{{ $backUrl }}" class="inline-flex items-center text-sm font-medium text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded hover:bg-gray-100 transition">
          <i class="fas fa-arrow-left mr-2"></i>
          Kembali
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start">
        <div class="flex flex-col h-full">
          <div class="relative mb-4 border rounded-md overflow-hidden h-[400px]">
            @php
              $utama = $produk->gambar->first();
              $path  = $utama ? 'storage/' . $utama->gambar : 'assets/default.jpg';
            @endphp
            <img id="mainImage" src="{{ asset($path) }}" @click="imageModalUrl = $el.src; showImageModal = true" class="w-full h-full object-cover cursor-pointer transition">
          </div>

          @if ($produk->gambar->count() >= 1)
            <div class="mt-4 md:mt-auto">
              <div id="thumbnailContainer" class="flex {{ $produk->gambar->count() < 6 ? 'justify-center' : 'justify-start' }} gap-2 overflow-x-auto hide-scrollbar">
                @foreach ($produk->gambar as $i => $g)
                  <img id="thumb-{{ $i }}" src="{{ asset('storage/' . $g->gambar) }}" onclick="setImage({{ $i }})" class="w-20 h-20 object-cover border cursor-pointer hover:border-black shrink-0 transition">
                @endforeach
              </div>
            </div>
          @endif
        </div>

        <div class="self-start border rounded-md shadow-md bg-white h-auto md:h-[400px] flex flex-col">
          <div class="p-6 pb-2">
            <h1 class="text-xl font-bold">{{ $produk->nama }}</h1>
          </div>

          <div class="px-6 flex-1 min-h-0 overflow-y-auto">
            <p class="text-sm text-gray-700 leading-relaxed">
              {{ $produk->deskripsi }}
            </p>
          </div>

          <div class="p-6 pt-4 mt-auto">
            <div class="flex w-full gap-2 mb-3">
              @auth
                <button
                  type="button"
                  @click="addToCart()"
                  :disabled="addToCartLoading"
                  class="w-1/2 py-3 px-4 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition disabled:opacity-60"
                >
                  <span x-show="!addToCartLoading">Masukkan ke Keranjang</span>
                  <span x-show="addToCartLoading">Menambahkan...</span>
                </button>

                <button
                  type="button"
                  @click="showConfirmModal = true"
                  class="w-1/2 py-3 px-4 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition"
                >
                  Pesan Sekarang
                </button>
              @else
                <button type="button" @click="showLoginPrompt = true" class="w-1/2 py-3 px-4 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition">
                  Masukkan ke Keranjang
                </button>
                <button type="button" @click="showLoginPrompt = true" class="w-1/2 py-3 px-4 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition">
                  Pesan Sekarang
                </button>
              @endauth
            </div>

            @auth
              <a
                href="https://wa.me/{{ $waAdmin }}?text={{ urlencode('Halo, saya ingin konsultasi mengenai produk ' . $produk->nama) }}"
                target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 py-3 px-6 font-semibold text-black bg-white border border-gray-300 rounded-lg shadow hover:bg-gray-100 transition"
              >
                <i class="fab fa-whatsapp"></i>
                Konsultasi
              </a>
            @else
              <button
                type="button"
                @click="showLoginPrompt = true"
                class="w-full inline-flex items-center justify-center gap-2 py-3 px-6 font-semibold text-black bg-white border border-gray-300 rounded-lg shadow hover:bg-gray-100 transition"
              >
                <i class="fab fa-whatsapp"></i>
                Konsultasi
              </button>
            @endauth
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="showImageModal" @click.self="showImageModal = false" x-cloak class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="relative inline-block max-w-[90vw] max-h-[90vh]">
      <img :src="imageModalUrl" class="block w-auto h-auto max-w-[90vw] max-h-[90vh] object-contain rounded-md shadow-lg">
      <button @click="showImageModal = false" class="absolute top-2 right-2 text-white bg-black/60 rounded-full p-2 hover:bg-black/80 transition focus:outline-none focus:ring">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <div x-show="showLoginPrompt" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
      <h2 class="text-lg font-bold mb-4">Anda belum login</h2>
      <p class="text-sm text-gray-600 mb-6">Silakan login terlebih dahulu untuk melanjutkan.</p>
      <div class="flex justify-center gap-4">
        <a href="{{ route('login') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Login</a>
        <button @click="showLoginPrompt = false" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
      </div>
    </div>
  </div>

  <div x-show="showConfirmModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-2">Konfirmasi Pesanan</h2>
      <p class="text-gray-700 mb-6">Apakah Anda yakin ingin memesan produk ini sekarang?</p>
      <div class="flex justify-center gap-3">
        <button @click="showConfirmModal = false" class="px-5 py-2 border rounded hover:bg-gray-100">Batal</button>
        <button @click="pesanSekarang()" :disabled="isSubmitting" class="px-5 py-2 bg-black text-white rounded hover:bg-gray-800 disabled:opacity-60">
          <span x-show="!isSubmitting">Ya, Pesan</span>
          <span x-show="isSubmitting">Memproses...</span>
        </button>
      </div>
    </div>
  </div>

  <div x-show="showWaitingModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 shadow-lg max-w-md w-full text-center">
      <h2 class="text-xl font-semibold mb-3">Pesanan Diterima</h2>
      <p class="text-gray-700 mb-4">Terima kasih, pesanan Anda telah kami terima. Pihak bengkel akan menghubungi Anda melalui WhatsApp maksimal 1×24 jam.</p>
      <a href="{{ route('pesanan.index') }}" class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800">Lihat Pesanan</a>
    </div>
  </div>

  <div x-show="showAddedModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
      <h2 class="text-lg font-bold mb-3">Berhasil Ditambahkan</h2>
      <p class="text-sm text-gray-700 mb-6">
        Produk sudah masuk ke keranjang Anda.
      </p>
      <div class="flex justify-center gap-4">
        <a href="{{ route('keranjang.index') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Lihat Keranjang
        </a>
        <button @click="showAddedModal = false" class="px-4 py-2 border rounded hover:bg-gray-100">
          Lanjut Belanja
        </button>
      </div>
    </div>
  </div>
</section>

<style>
.hide-scrollbar::-webkit-scrollbar{display:none}
.hide-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
</style>

<script>
const thumbnails = Array.from(document.querySelectorAll('#thumbnailContainer img'));
let currentIndex = 0;
function setImage(index){
  currentIndex = index;
  const mainImage = document.getElementById('mainImage');
  const thumb = document.getElementById('thumb-'+index);
  mainImage.src = thumb.src;
  thumbnails.forEach((_, i) => {
    const el = document.getElementById('thumb-'+i);
    if (el) el.classList.remove('ring','ring-black','border-black');
  });
  if (thumb) thumb.classList.add('ring','ring-black','border-black');
  const container = document.getElementById('thumbnailContainer');
  if (container && thumb){
    const pos = thumb.offsetLeft - (container.clientWidth/2) + (thumb.clientWidth/2);
    container.scrollTo({ left: pos, behavior: 'smooth' });
  }
}
window.addEventListener('DOMContentLoaded', () => {
  if (thumbnails.length) setImage(0);
});
</script>
@endsection
