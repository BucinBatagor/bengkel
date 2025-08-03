@extends('Template.pelanggan')

@section('title', 'Detail Produk')

@section('content')
<section class="py-10 bg-gray-200 min-h-screen"
    x-data="{ 
        showLoginPrompt: false, 
        showSuccessModal: {{ session('success') ? 'true' : 'false' }} 
    }">

    <div class="max-w-screen-xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Gambar Produk -->
                <div>
                    <div class="relative mb-4 border rounded-md overflow-hidden h-[400px]">
                        @php
                            $gambarUtama = $produk->gambar->first();
                            $gambarPath = $gambarUtama
                                ? 'storage/' . $gambarUtama->gambar
                                : 'assets/default.jpg';
                        @endphp
                        <img id="mainImage" src="{{ asset($gambarPath) }}"
                            class="w-full h-full object-cover transition duration-300 ease-in-out" />
                    </div>

                    @if ($produk->gambar->count() > 1)
                    <div class="mt-4">
                        <div class="w-full">
                            <div id="thumbnailContainer"
                                class="flex {{ $produk->gambar->count() < 6 ? 'justify-center' : 'justify-start' }} 
                                    gap-2 overflow-x-auto scroll-smooth hide-scrollbar">
                                @foreach ($produk->gambar as $index => $gambar)
                                <img id="thumb-{{ $index }}"
                                    src="{{ asset('storage/' . $gambar->gambar) }}"
                                    onclick="setImage({{ $index }})"
                                    class="w-20 h-20 object-cover border cursor-pointer hover:border-black shrink-0 transition" />
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Detail Produk -->
                <div class="flex flex-col justify-between h-fit p-6 border rounded-md shadow-md bg-white">
                    <div>
                        <h1 class="text-xl font-bold mb-4">{{ $produk->nama }}</h1>
                        <p class="text-lg font-semibold text-black mb-4">
                            Rp {{ number_format($produk->harga, 0, ',', '.') }} / m<sup>2</sup>
                        </p>
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Deskripsi Produk</h2>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                {{ $produk->deskripsi }}
                            </p>
                        </div>

                        <!-- Tombol Masukkan Keranjang -->
                        <div class="mt-6">
                            @auth
                            <form action="{{ route('keranjang.tambah') }}" method="POST">
                                @csrf
                                <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 w-full py-3 px-6 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14l-1 10H6L5 8zM9 8V6a3 3 0 016 0v2" />
                                    </svg>
                                    Masukkan ke Keranjang
                                </button>
                            </form>
                            @else
                            <button @click="showLoginPrompt = true"
                                class="inline-flex items-center justify-center gap-2 w-full py-3 px-6 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14l-1 10H6L5 8zM9 8V6a3 3 0 016 0v2" />
                                </svg>
                                Masukkan ke Keranjang
                            </button>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Login -->
    <div x-show="showLoginPrompt" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
            <h2 class="text-lg font-bold mb-4">Anda belum login</h2>
            <p class="text-sm text-gray-600 mb-6">
                Silakan login terlebih dahulu untuk memasukkan produk ke keranjang.
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login', ['next' => request()->fullUrl()]) }}"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Login</a>
                <button @click="showLoginPrompt = false"
                    class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
            </div>
        </div>
    </div>

    <!-- Modal Sukses -->
    <div x-show="showSuccessModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
            <p class="text-sm text-gray-600 mb-6">{{ session('success') }}</p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('keranjang.index') }}"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Lihat Keranjang</a>
                <button @click="showSuccessModal = false"
                    class="px-4 py-2 border rounded hover:bg-gray-100">Lanjut Belanja</button>
            </div>
        </div>
    </div>
</section>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    const thumbnails = Array.from(document.querySelectorAll('#thumbnailContainer img'));
    let currentIndex = 0;

    function setImage(index) {
        currentIndex = index;
        const mainImage = document.getElementById('mainImage');
        const activeThumb = document.getElementById('thumb-' + currentIndex);
        const thumbnailContainer = document.getElementById('thumbnailContainer');
        mainImage.src = activeThumb.src;
        thumbnails.forEach((thumb, i) => {
            document.getElementById('thumb-' + i).classList.remove('ring', 'ring-black', 'border-black');
        });
        activeThumb.classList.add('ring', 'ring-black', 'border-black');
        const scrollTo = activeThumb.offsetLeft - (thumbnailContainer.clientWidth / 2) + (activeThumb.clientWidth / 2);
        thumbnailContainer.scrollTo({ left: scrollTo, behavior: 'smooth' });
    }

    window.addEventListener('DOMContentLoaded', () => {
        if (thumbnails.length) {
            setImage(0);
        }
    });
</script>
@endsection
