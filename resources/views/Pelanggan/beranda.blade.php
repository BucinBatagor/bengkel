@extends('Template.pelanggan')

@section('title', 'Beranda')

@section('content')
<section class="relative flex h-screen items-center justify-center bg-cover bg-[center_72%] text-white" style="background-image: url('/assets/Jumbotron.jpg');">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-screen-xl mx-auto w-full flex justify-center">
        <div class="text-center px-4">
            <h1 class="text-4xl font-light">Temukan Solusi Kebutuhan Las Anda</h1>
            <h2 class="text-4xl font-extrabold leading-tight">Kami Siap Membantu Wujudkan Proyek Anda</h2>
        </div>
    </div>
</section>

<section class="bg-gray-300 py-16">
    <div class="max-w-screen-xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Layanan Bengkel Las Terbaik</h2>
        <p class="text-gray-700 mb-6 max-w-xl mx-auto">
            Lihat berbagai pilihan produk kami yang siap menunjang kebutuhan konstruksi dan dekorasi Anda.
        </p>
        <a href="/katalog" class="inline-block bg-black text-white px-6 py-3 rounded-full font-semibold hover:bg-gray-700 transition">
            Lihat Semua Katalog
        </a>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-screen-xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10 text-center">Produk Terbaru</h2>

        <div class="flex flex-wrap justify-center gap-6">
            @if(count($kategoriList))
                @foreach ($kategoriList as $kategori)
                    <a href="{{ route('produk.show', $kategori['id']) }}"
                        class="w-full sm:w-[47%] md:w-[30%] border rounded-lg overflow-hidden shadow hover:shadow-lg transition bg-white flex flex-col">
                        @php
                            $imgPath = !empty($kategori['img']) && file_exists(public_path('storage/' . $kategori['img']))
                                ? 'storage/' . $kategori['img']
                                : 'assets/default.jpg';
                        @endphp
                        <img src="{{ asset($imgPath) }}"
                            alt="{{ $kategori['nama_produk'] }}"
                            class="w-full h-48 object-cover" />
                        <div class="p-4 flex flex-col h-full justify-between">
                            <div>
                                <h3 class="text-lg font-bold mb-1">{{ $kategori['nama_produk'] }}</h3>
                                <p class="text-sm text-gray-500 mb-2">{{ $kategori['nama'] }}</p>
                                <p class="text-sm text-gray-700 mb-4">
                                    Rp {{ number_format($kategori['harga'], 0, ',', '.') }}/ m<sup>2</sup>
                                </p>
                            </div>
                            <div class="text-sm text-center text-blue-600 font-semibold hover:underline mt-auto">
                                Lihat Detail
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <p class="text-gray-600 text-center w-full">Tidak ada produk ditemukan.</p>
            @endif
        </div>
    </div>
</section>
@endsection
