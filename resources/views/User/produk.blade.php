@extends('Template.user')

@section('title', 'Detail Produk')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<section class="py-10 px-5 min-h-screen"
    x-data="{
        showLoginPrompt: false,
        showOrderModal: window.location.hash === '#pesan'
    }">
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 h-full min-h-screen">
        <div>
            <div class="relative mb-4 border rounded-md overflow-hidden h-[400px]">
                <img
                    id="mainImage"
                    src="{{ asset(optional($produk->gambars->first())->gambar ?? 'assets/default.jpg') }}"
                    class="w-full h-full object-cover transition duration-300 ease-in-out" />
            </div>

            @if ($produk->gambars->count() > 1)
                @php $thumbnailCount = $produk->gambars->count(); @endphp
                <div class="mt-4">
                    <div id="thumbnailContainer"
                        class="flex {{ $thumbnailCount < 6 ? 'justify-center' : 'justify-start' }} gap-2 px-4 overflow-x-auto scroll-smooth">
                        @foreach ($produk->gambars as $index => $gambar)
                            <img
                                id="thumb-{{ $index }}"
                                src="{{ asset($gambar->gambar) }}"
                                onclick="setImage({{ $index }})"
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

    <div x-show="showLoginPrompt" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
            <h2 class="text-lg font-bold mb-4">Anda belum login</h2>
            <p class="text-sm text-gray-600 mb-6">Silakan login terlebih dahulu untuk melakukan pemesanan.</p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login', ['next' => request()->fullUrl() . '#pesan']) }}"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Login</a>
                <button @click="showLoginPrompt = false"
                    class="px-4 py-2 border rounded hover:bg-gray-100">Batal</button>
            </div>
        </div>
    </div>

    <div x-show="showOrderModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Pemesanan</h2>
                <button @click="showOrderModal = false"
                    class="text-gray-500 hover:text-black text-xl font-bold">&times;</button>
            </div>
            <form>
                <div class="text-sm text-gray-600">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Cum animi laboriosam at commodi nam aliquam asperiores maxime modi, minima suscipit incidunt. Perferendis architecto quia mollitia nesciunt, reiciendis temporibus corrupti veniam.</div>
            </form>
        </div>
    </div>
</section>

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
        setImage(0);
    });
</script>
@endsection
