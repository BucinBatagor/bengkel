@extends('Template.admin')

@section('title', 'Pesanan Masuk')

@section('content')
<section class="min-h-[700px] flex flex-col items-center px-6 py-6">
    <div class="w-full max-w-screen-xl bg-white px-8 py-6 rounded-lg shadow flex-1">
        <h1 class="text-2xl font-bold mb-6">PESANAN MASUK</h1>

        {{-- Filter & Pencarian --}}
        <form method="GET" action="{{ route('admin.pemesanan.index') }}"
            class="flex flex-wrap justify-between items-center gap-4 mb-4">
            <div class="flex items-center gap-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan..."
                    class="border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8 w-64">
                @if(request('search'))
                <button type="button"
                    onclick="window.location.href='{{ route('admin.pemesanan.index', array_merge(request()->except(['search', 'page'])) ) }}'"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-black text-lg">
                    &times;
                </button>
                @endif
                <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                    Cari
                </button>
            </div>

            <div class="w-48">
                <select name="status" onchange="this.form.submit()"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black">
                    <option value="">Semua Status</option>
                    @foreach(['diproses', 'dikerjakan', 'selesai'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="overflow-x-auto rounded">
            <table class="min-w-full border border-gray-300 text-sm text-left">
                <thead class="bg-black text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-3 border-r border-gray-400">#</th>
                        <th class="px-5 py-3 border-r border-gray-400">Nama Pelanggan</th>
                        <th class="px-5 py-3 border-r border-gray-400">Alamat</th>
                        <th class="px-5 py-3 border-r border-gray-400">Produk</th>
                        <th class="px-5 py-3 border-r border-gray-400">Panjang</th>
                        <th class="px-5 py-3 border-r border-gray-400">Lebar</th>
                        <th class="px-5 py-3 border-r border-gray-400">Tinggi</th>
                        <th class="px-5 py-3 border-r border-gray-400">Total Harga</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($pemesanan as $i => $pesanan)
                    @php
                    $statusColor = match($pesanan->status) {
                    'diproses' => 'bg-yellow-100 text-yellow-800',
                    'dikerjakan' => 'bg-blue-100 text-blue-800',
                    'selesai' => 'bg-green-100 text-green-800',
                    default => 'bg-gray-100 text-gray-800',
                    };
                    @endphp


                    <tr class="hover:bg-gray-100 border-b border-gray-300">
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pemesanan->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pesanan->pelanggan->name }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pesanan->pelanggan->address ?? '-' }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pesanan->produk->nama }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ rtrim(rtrim(number_format($pesanan->panjang, 2, '.', ''), '0'), '.') }}m</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ rtrim(rtrim(number_format($pesanan->lebar, 2, '.', ''), '0'), '.') }}m</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ rtrim(rtrim(number_format($pesanan->tinggi, 2, '.', ''), '0'), '.') }}m</td>
                        <td class="px-5 py-3 border-r border-gray-200">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                        <td class="px-5 py-3">
                            <form action="{{ route('admin.pemesanan.update', $pesanan->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm {{ $statusColor }}">
                                    @foreach(['diproses', 'dikerjakan', 'selesai'] as $status)
                                    <option value="{{ $status }}" {{ $pesanan->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-500 py-10 text-base font-semibold">
                            Tidak ada data pemesanan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mt-10">
        <ul class="inline-flex items-center text-sm">
            {{-- Panah ke halaman pertama dan sebelumnya --}}
            <div class="inline-flex space-x-1 mr-2">
                @if ($pemesanan->onFirstPage())
                <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                @else
                <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->url(1) }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a>
                </li>
                <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->previousPageUrl() }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a>
                </li>
                @endif
            </div>

            {{-- Nomor halaman --}}
            <div class="inline-flex space-x-1 mx-2">
                @php
                $current = $pemesanan->currentPage();
                $last = $pemesanan->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $start + 4);
                if ($end - $start < 4) {
                    $start=max(1, $end - 4);
                    }
                    @endphp

                    @for ($i=$start; $i <=$end; $i++)
                    <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}"
                        class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                        {{ $i }}
                    </a>
                    </li>
                    @endfor
            </div>

            {{-- Panah ke halaman berikutnya dan terakhir --}}
            <div class="inline-flex space-x-1 ml-2">
                @if ($pemesanan->hasMorePages())
                <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->nextPageUrl() }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a>
                </li>
                <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->url($pemesanan->lastPage()) }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a>
                </li>
                @else
                <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                @endif
            </div>
        </ul>
    </div>
</section>
@endsection