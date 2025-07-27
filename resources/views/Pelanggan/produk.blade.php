@extends('Template.pelanggan')

@section('title', 'Detail Produk')

@section('content')
<section class="py-10 px-5 min-h-screen"
    x-data="{
        showLoginPrompt: false,
        showOrderModal: window.location.hash === '#pesan'
    }">
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 h-full min-h-screen">
        <div>
            <div class="relative mb-4 border rounded-md overflow-hidden h-[400px]">
                @php
                    $gambarUtama = $produk->gambar->first();
                    $gambarPath = $gambarUtama ? 'storage/' . $gambarUtama->gambar : 'assets/default.jpg';
                @endphp
                <img id="mainImage" src="{{ asset($gambarPath) }}" class="w-full h-full object-cover transition duration-300 ease-in-out" />
            </div>

            @if ($produk->gambar->count() > 1)
            <div class="mt-4">
                <div id="thumbnailContainer" class="flex {{ $produk->gambar->count() < 6 ? 'justify-center' : 'justify-start' }} gap-2 px-4 overflow-x-auto scroll-smooth">
                    @foreach ($produk->gambar as $index => $gambar)
                        <img id="thumb-{{ $index }}" src="{{ asset('storage/' . $gambar->gambar) }}" onclick="setImage({{ $index }})"
                            class="w-20 h-20 object-cover border cursor-pointer hover:border-black shrink-0 transition" />
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-col justify-between h-fit p-6 border rounded-md shadow-md">
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

                <div class="mt-6">
                    <button
                        @click.prevent="
                            @if(auth()->check())
                                showOrderModal = true
                            @else
                                showLoginPrompt = true
                            @endif
                        "
                        class="inline-flex items-center justify-center gap-2 w-full py-3 px-6 font-semibold text-white bg-black rounded-lg shadow hover:bg-gray-800 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h2l.4 2M7 13h10l1-2H6l1 2zm1 4h8l1.4-2.8M5 5h14a2 2 0 012 2v2a2 2 0 01-2 2H6l-1.5 3M6 16a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Login Prompt --}}
    <div x-show="showLoginPrompt" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
            <h2 class="text-lg font-bold mb-4">Anda belum login</h2>
            <p class="text-sm text-gray-600 mb-6">Silakan login terlebih dahulu untuk melakukan pemesanan.</p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login', ['next' => request()->fullUrl() . '#pesan']) }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Login</a>
                <button @click="showLoginPrompt = false" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
            </div>
        </div>
    </div>

    {{-- Modal Pemesanan --}}
    <div x-show="showOrderModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-lg"
            x-data="{
                panjang: 0,
                lebar: 0,
                tinggi: 0,
                harga: {{ $produk->harga }},
                get total() {
                    return ((this.panjang + this.lebar + this.tinggi) * this.harga).toFixed(0);
                },
                bayar() {
                    const totalHarga = (this.panjang + this.lebar + this.tinggi) * this.harga;

                    fetch(`{{ route('checkout') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            harga: totalHarga,
                            produk_id: {{ $produk->id }},
                            panjang: this.panjang,
                            lebar: this.lebar,
                            tinggi: this.tinggi
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        snap.pay(data.token, {
                            onSuccess: function(result) {
                                alert('Pembayaran berhasil');
                                console.log(result);
                            },
                            onPending: function(result) {
                                alert('Menunggu pembayaran Anda');
                                console.log(result);
                            },
                            onError: function(result) {
                                alert('Pembayaran gagal');
                                console.log(result);
                            },
                            onClose: function() {
                                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                            }
                        });
                    });
                }
            }">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Form Pemesanan</h2>
                <button @click="showOrderModal = false" class="text-gray-500 hover:text-black text-xl font-bold">&times;</button>
            </div>

            <form @submit.prevent>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Panjang (m)</label>
                        <input type="number" min="0" step="any" x-model.number="panjang"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Lebar (m)</label>
                        <input type="number" min="0" step="any" x-model.number="lebar"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tinggi (m)</label>
                        <input type="number" min="0" step="any" x-model.number="tinggi"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700">Total Harga:</p>
                    <p class="text-lg font-bold text-black">
                        Rp <span x-text="(panjang > 0 && lebar > 0 && tinggi > 0) ? Number(total).toLocaleString('id-ID') : ''"></span>
                    </p>
                </div>

                <a href="#"
                    @click.prevent="(panjang > 0 && lebar > 0 && tinggi > 0) ? bayar() : alert('Harap isi semua ukuran terlebih dahulu!')"
                    class="bg-black text-white px-5 py-2 rounded hover:bg-gray-800">Checkout</a>
            </form>
        </div>
    </div>
</section>

{{-- Snap.js --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

{{-- Image preview logic --}}
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
        thumbnailContainer.scrollTo({
            left: scrollTo,
            behavior: 'smooth'
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        if (thumbnails.length) {
            setImage(0);
        }
    });
</script>
@endsection
