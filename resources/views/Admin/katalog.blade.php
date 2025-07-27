    @extends('Template.admin')

    @section('title', 'Kelola Katalog')

    @section('content')
    <section class="min-h-[700px] flex flex-col items-center px-6 py-6" x-data="{ show: false, deleteUrl: '' }">
        <div class="w-full max-w-screen-xl bg-white px-8 py-6 rounded-lg shadow flex-1">
            <h1 class="text-2xl font-bold mb-6">KELOLA KATALOG PRODUK</h1>

            <div class="flex justify-between items-center mb-4">
                <a href="{{ route('admin.katalog.create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 shadow">
                    + Tambah Produk
                </a>

                <form method="GET" action="{{ route('admin.katalog.index') }}" class="flex items-center gap-2 relative">
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        placeholder="Cari nama produk..."
                        class="border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8">

                    @if(request('search'))
                    <button type="button"
                        onclick="window.location.href='{{ route('admin.katalog.index') }}'"
                        class="absolute right-20 text-gray-400 hover:text-black text-lg px-1">
                        &times;
                    </button>
                    @endif

                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="order" value="{{ request('order') }}">
                    <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                        Cari
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full border border-gray-300 text-sm text-left">
                    <thead class="bg-black text-white uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-3 border-r border-gray-400">#</th>
                            <th class="px-5 py-3 border-r border-gray-400">Gambar</th>
                            <th class="px-5 py-3 border-r border-gray-400">
                                @php
                                $nextOrder = request('sort') === 'nama' && request('order') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('admin.katalog.index', ['sort' => 'nama', 'order' => $nextOrder, 'search' => request('search')]) }}" class="flex items-center gap-1">
                                    Nama
                                    @if (request('sort') === 'nama')
                                    {!! request('order') === 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
                            <th class="px-5 py-3 border-r border-gray-400">
                                @php
                                $nextOrder = request('sort') === 'kategori' && request('order') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('admin.katalog.index', ['sort' => 'kategori', 'order' => $nextOrder, 'search' => request('search')]) }}" class="flex items-center gap-1">
                                    Kategori
                                    @if (request('sort') === 'kategori')
                                    {!! request('order') === 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
                            <th class="px-5 py-3 border-r border-gray-400">
                                @php
                                $nextOrder = request('sort') === 'harga' && request('order') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('admin.katalog.index', ['sort' => 'harga', 'order' => $nextOrder, 'search' => request('search')]) }}" class="flex items-center gap-1">
                                    Harga
                                    @if (request('sort') === 'harga')
                                    {!! request('order') === 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">
                        @forelse ($produks as $produk)
                        <tr class="hover:bg-gray-100 border-b border-gray-300">
                            <td class="px-5 py-3 border-r border-gray-200">
                                {{ $produks->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-3 border-r border-gray-200">
                                @php
                                $gambarUtama = $produk->gambar->firstWhere(fn($g) => !empty($g->gambar));
                                @endphp
                                <img src="{{ $gambarUtama ? asset('storage/' . $gambarUtama->gambar) : asset('assets/default.jpg') }}"
                                    onerror="this.onerror=null;this.src='{{ asset('assets/default.jpg') }}';"
                                    alt="Gambar"
                                    class="w-20 h-20 object-cover rounded-md shadow-sm border">

                            </td>
                            <td class="px-5 py-3 font-medium border-r border-gray-200">{{ $produk->nama }}</td>
                            <td class="px-5 py-3 border-r border-gray-200">{{ $produk->kategori }}</td>
                            <td class="px-5 py-3 border-r border-gray-200">Rp{{ number_format($produk->harga, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.katalog.edit', $produk->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                                    <button @click="show = true; deleteUrl = '{{ route('admin.katalog.destroy', $produk->id) }}'" class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-10 text-base font-semibold">Belum ada produk yang tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-center mt-auto pt-10">
            <ul class="inline-flex items-center text-sm">
                {{-- Panah ke kiri --}}
                <div class="inline-flex space-x-1 mr-2">
                    @if ($produks->onFirstPage())
                    <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                    <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                    @else
                    <li><a href="{{ $produks->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
                    <li><a href="{{ $produks->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
                    @endif
                </div>

                {{-- Nomor halaman --}}
                <div class="inline-flex space-x-1 mx-2">
                    @php
                    $current = $produks->currentPage();
                    $last = $produks->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $start + 4);
                    if ($end - $start < 4) {
                        $start=max(1, $end - 4);
                        }
                        @endphp

                        @for ($i=$start; $i <=$end; $i++)
                        <li>
                        <a href="{{ $produks->url($i) }}"
                            class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                            {{ $i }}
                        </a>
                        </li>
                        @endfor
                </div>

                {{-- Panah ke kanan --}}
                <div class="inline-flex space-x-1 ml-2">
                    @if ($produks->hasMorePages())
                    <li><a href="{{ $produks->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
                    <li><a href="{{ $produks->url($produks->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
                    @else
                    <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                    <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                    @endif
                </div>
            </ul>
        </div>


        <div x-show="show" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div x-transition.scale class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
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
    </section>
    @endsection