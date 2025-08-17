@extends('Template.admin')

@section('title', 'Kelola Katalog')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full" x-data="{ show: false, deleteUrl: '', detailShow: false, detail: { id: null, nama: '', kategori: '', deskripsi: '', gambar: [] } }">
    <div class="bg-white rounded-lg shadow px-6 py-6 w-full max-w-screen-xl mx-auto min-h-[600px]">
        <h1 class="text-2xl font-bold mb-6">KELOLA KATALOG PRODUK</h1>

        <div class="block md:hidden mb-6 space-y-4">
            <form method="GET" action="{{ route('admin.katalog.index') }}" class="flex w-full">
                <div class="relative flex w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama produk..."
                        class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring focus:border-black pr-10"
                    >
                    @if(request('search'))
                        <button
                            type="button"
                            onclick="window.location.href='{{ route('admin.katalog.index', array_merge(request()->except(['search','page'])) ) }}'"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-black text-lg"
                        >&times;</button>
                    @endif
                </div>
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="order" value="{{ request('order') }}">
                <button
                    type="submit"
                    class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800 border border-l-0 border-gray-300"
                >Cari</button>
            </form>

            <a
                href="{{ route('admin.katalog.create') }}"
                class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 shadow w-full text-center"
            >+ Tambah Produk</a>
        </div>

        <div class="hidden md:flex md:items-center md:justify-between mb-6">
            <a
                href="{{ route('admin.katalog.create') }}"
                class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 shadow"
            >+ Tambah Produk</a>

            <form method="GET" action="{{ route('admin.katalog.index') }}" class="flex">
                <div class="relative flex w-[250px]">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama produk..."
                        class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring focus:border-black pr-10"
                    >
                    @if(request('search'))
                        <button
                            type="button"
                            onclick="window.location.href='{{ route('admin.katalog.index', array_merge(request()->except(['search','page'])) ) }}'"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-black text-lg"
                        >&times;</button>
                    @endif
                </div>
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="order" value="{{ request('order') }}">
                <button
                    type="submit"
                    class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800 border border-l-0 border-gray-300"
                >Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto rounded">
            <table class="min-w-full border border-gray-300 text-sm text-left">
                <thead class="bg-black text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-3 border-r border-gray-400">#</th>
                        <th class="px-5 py-3 border-r border-gray-400">Gambar</th>
                        <th class="px-5 py-3 border-r border-gray-400">
                            @php $nextOrder = request('sort') === 'nama' && request('order') === 'asc' ? 'desc' : 'asc'; @endphp
                            <a
                                href="{{ route('admin.katalog.index', ['sort' => 'nama', 'order' => $nextOrder, 'search' => request('search')]) }}"
                                class="flex items-center gap-1"
                            >Nama
                                @if (request('sort') === 'nama')
                                    {!! request('order') === 'asc' ? '&#9650;' : '&#9660;' !!}
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 border-r border-gray-400">
                            @php $nextOrder = request('sort') === 'kategori' && request('order') === 'asc' ? 'desc' : 'asc'; @endphp
                            <a
                                href="{{ route('admin.katalog.index', ['sort' => 'kategori', 'order' => $nextOrder, 'search' => request('search')]) }}"
                                class="flex items-center gap-1"
                            >Kategori
                                @if (request('sort') === 'kategori')
                                    {!! request('order') === 'asc' ? '&#9650;' : '&#9660;' !!}
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 border-r border-gray-400">Deskripsi</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($produks as $produk)
                        @php
                            $gambarUtama = $produk->gambar->first();
                            $images = $produk->gambar->map(fn($g) => asset('storage/' . $g->gambar))->values();
                        @endphp
                        <tr class="hover:bg-gray-100 border-b border-gray-300">
                            <td class="px-5 py-3 border-r border-gray-200">
                                {{ $produks->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-3 border-r border-gray-200">
                                <img
                                    src="{{ $gambarUtama ? asset('storage/' . $gambarUtama->gambar) : asset('assets/default.jpg') }}"
                                    alt="Gambar {{ $produk->nama }}"
                                    class="w-20 h-20 object-cover rounded-md shadow-sm border"
                                >
                            </td>
                            <td class="px-5 py-3 font-medium border-r border-gray-200">
                                {{ $produk->nama }}
                            </td>
                            <td class="px-5 py-3 border-r border-gray-200">
                                {{ $produk->kategori }}
                            </td>
                            <td class="px-5 py-3 border-r border-gray-200">
                                {{ \Illuminate\Support\Str::limit($produk->deskripsi, 60) }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-3 items-center">
                                    <button
                                        class="text-gray-700 hover:text-black font-semibold"
                                        @click='detail = { id: {{ $produk->id }}, nama: @json($produk->nama), kategori: @json($produk->kategori), deskripsi: @json($produk->deskripsi ?? ""), gambar: @json($images) }; detailShow = true;'
                                    >Detail</button>
                                    <a
                                        href="{{ route('admin.katalog.edit', $produk->id) }}"
                                        class="text-blue-600 hover:text-blue-800 font-semibold"
                                    >Edit</a>
                                    <button
                                        @click="show = true; deleteUrl = '{{ route('admin.katalog.destroy', $produk->id) }}'"
                                        class="text-red-600 hover:text-red-800 font-semibold"
                                    >Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-10 font-semibold">
                                Belum ada produk yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
                <h2 class="text-lg font-bold mb-4">Konfirmasi Penghapusan</h2>
                <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus produk ini?</p>
                <form :action="deleteUrl" method="POST" class="flex justify-center gap-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hapus</button>
                    <button type="button" @click="show = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                </form>
            </div>
        </div>

        <div x-show="detailShow" x-cloak class="fixed inset-0 z-[9999]" @keydown.escape.window="detailShow=false">
            <div class="absolute inset-0 bg-black/50 z-0" @click="detailShow=false"></div>

            <div class="relative z-10 flex min-h-full items-start md:items-center justify-center p-4 md:p-6">
                <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg max-h-[90vh] overflow-y-auto overflow-x-hidden p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Detail Produk</h2>
                        <button class="text-gray-500 hover:text-black text-xl leading-none" @click="detailShow=false">×</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 items-stretch">
                        <div class="min-w-0 h-full flex flex-col"
                            x-data="{
                                idx: 0,
                                setIdx(i){
                                    this.idx = i;
                                    this.$nextTick(() => {
                                        const c = this.$refs.tcontainer;
                                        const t = c?.querySelector(`[data-i='${i}']`);
                                        if (!c || !t) return;
                                        if (c.scrollWidth <= c.clientWidth) { c.scrollLeft = 0; return; }
                                        if (i === 0) { c.scrollTo({ left: 0, behavior: 'smooth' }); return; }
                                        const cRect = c.getBoundingClientRect();
                                        const tRect = t.getBoundingClientRect();
                                        const delta = tRect.left - (cRect.left + c.clientWidth/2 - t.clientWidth/2);
                                        let target = c.scrollLeft + delta;
                                        const max = c.scrollWidth - c.clientWidth;
                                        if (target < 0) target = 0;
                                        if (target > max) target = max;
                                        c.scrollTo({ left: target, behavior: 'smooth' });
                                    });
                                }
                            }"
                            x-init="$nextTick(()=> setIdx(0))"
                        >
                            <template x-if="detail.gambar && detail.gambar.length">
                                <img :src="detail.gambar[idx]" class="w-full h-64 md:h-72 object-cover object-center rounded-lg border">
                            </template>
                            <template x-if="!detail.gambar || !detail.gambar.length">
                                <img src="{{ asset('assets/default.jpg') }}" class="w-full h-64 md:h-72 object-cover object-center rounded-lg border">
                            </template>

                            <div class="mt-3 md:mt-auto md:pt-4">
                                <div x-ref="tcontainer"
                                    class="w-full flex gap-2 overflow-x-auto hide-scrollbar"
                                    :class="(detail.gambar && detail.gambar.length < 6) ? 'justify-center' : 'justify-start'">
                                    <template x-for="(src, i) in detail.gambar" :key="i">
                                        <img :src="src" :data-i="i" @click="setIdx(i)"
                                            class="w-16 h-16 sm:w-20 sm:h-20 object-cover object-center border cursor-pointer hover:border-black shrink-0 transition"
                                            :class="{ 'ring-2 ring-black border-black': i === idx }">
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 min-w-0 h-full flex flex-col">
                            <div>
                                <div class="text-sm text-gray-500">Nama Produk</div>
                                <div class="text-lg font-semibold break-words" x-text="detail.nama"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Kategori</div>
                                <div class="font-medium break-words" x-text="detail.kategori || '-'"></div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-500 mb-1">Deskripsi</div>
                                <div class="border rounded p-3 max-h-56 md:max-h-64 overflow-auto whitespace-pre-line text-sm break-words"
                                    x-text="detail.deskripsi || '-'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-screen-xl mx-auto">
        <div class="flex justify-center mt-8">
            <ul class="flex flex-wrap items-center gap-1 text-sm">
                @if ($produks->onFirstPage())
                    <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                    <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                @else
                    <li><a href="{{ $produks->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
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
                    <li><a href="{{ $produks->url($produks->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
                @else
                    <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                    <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                @endif
            </ul>
        </div>
    </div>
</section>
@endsection
