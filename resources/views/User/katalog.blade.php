@extends('Template.user')

@section('title', 'Katalog')

@section('content')
<section class="py-10 px-5">
    <div class="max-w-screen-xl mx-auto">

        <form method="GET" id="filterForm" class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="relative">
                <select name="kategori" onchange="document.getElementById('filterForm').submit();"
                    class="appearance-none bg-black text-white font-semibold py-2 px-4 pr-8 rounded leading-tight focus:outline-none h-[42px]">
                    <option value="Semua" {{ request('kategori') === 'Semua' ? 'selected' : '' }}>Semua</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori }}" {{ request('kategori') === $kategori ? 'selected' : '' }}>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                    <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                        <path d="M5.516 7.548l4.484 4.484 4.484-4.484-1.032-1.032L10 9.968 6.548 6.516z" />
                    </svg>
                </div>
            </div>

            <div class="relative w-full max-w-md">
                <input
                    type="text"
                    name="q"
                    id="searchInput"
                    value="{{ request('q') }}"
                    placeholder="Cari Produk"
                    class="w-full px-4 pr-16 py-2 bg-gray-100 border rounded-lg h-[42px] text-sm focus:outline-none" />

                @if(request('q'))
                    <button type="button" onclick="clearSearch()"
                        class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-black focus:outline-none">
                        &times;
                    </button>
                @endif

                <button type="submit"
                    class="absolute right-0 top-0 bottom-0 bg-black text-white rounded-r-lg px-3 flex items-center justify-center hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </form>

        <div class="min-h-[500px] flex flex-col justify-between">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10">
                @forelse ($produks as $produk)
                    <a href="{{ route('produk.show', $produk->id) }}"
                        class="block border rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                        <img
                            src="{{ asset(optional($produk->gambars->first())->gambar ?? 'assets/default.jpg') }}"
                            alt="{{ $produk->nama }}"
                            class="w-full h-48 object-cover" />
                        <div class="p-4">
                            <h3 class="text-sm font-semibold mb-1">{{ $produk->nama }}</h3>
                            <p class="text-xs text-gray-500 mb-1">{{ $produk->kategori }}</p>
                            <p class="text-sm text-gray-700">
                                Rp. {{ number_format($produk->harga, 0, ',', '.') }} / m<sup>2</sup>
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full flex items-center justify-center text-gray-500 min-h-[300px]">
                        <p class="text-center">Tidak ada produk ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <div class="flex justify-center mt-auto">
                <ul class="inline-flex space-x-1">
                    @if ($produks->onFirstPage())
                        <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                    @else
                        <li><a href="{{ $produks->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
                    @endif

                    @php
                        $current = $produks->currentPage();
                        $last = $produks->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $start + 4);
                        if ($end - $start < 4) {
                            $start = max(1, $end - 4);
                        }
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        <li>
                            <a href="{{ $produks->url($i) }}"
                                class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                                {{ $i }}
                            </a>
                        </li>
                    @endfor

                    @if ($produks->hasMorePages())
                        <li><a href="{{ $produks->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
                    @else
                        <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                    @endif
                </ul>
            </div>
        </div>

    </div>
</section>

<script>
    function clearSearch() {
        const input = document.getElementById('searchInput');
        input.value = '';
        document.getElementById('filterForm').submit();
    }
</script>
@endsection
