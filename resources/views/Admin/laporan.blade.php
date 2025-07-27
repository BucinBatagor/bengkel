@extends('Template.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<section class="min-h-[700px] flex flex-col items-center px-6 py-6">
    <div class="w-full max-w-screen-xl bg-white px-8 py-6 rounded-lg shadow flex-1">
        <h1 class="text-2xl font-bold mb-6">LAPORAN PENDAPATAN</h1>

        {{-- Form Filter Tanggal --}}
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-wrap gap-4 items-center mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="border px-3 py-2 rounded w-full focus:outline-none focus:ring focus:border-black">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="border px-3 py-2 rounded w-full focus:outline-none focus:ring focus:border-black">
            </div>
            <div class="self-end">
                <button type="submit"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 mt-1">Filter</button>
            </div>
        </form>

        {{-- Tampilkan Rentang Tanggal & Tombol Export --}}
        @if(request('from') && request('to') && $pemesanan->count())
        <p class="text-sm text-gray-600 mb-4">
            Menampilkan laporan dari
            <strong>{{ \Carbon\Carbon::parse(request('from'))->translatedFormat('d F Y') }}</strong>
            sampai
            <strong>{{ \Carbon\Carbon::parse(request('to'))->translatedFormat('d F Y') }}</strong>
        </p>

        <div class="mb-4 flex gap-3">
            <a href="{{ route('admin.laporan.export', ['from' => request('from'), 'to' => request('to'), 'format' => 'pdf']) }}"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Export PDF</a>
        </div>

        @elseif(request('from') && request('to'))
        <p class="text-sm text-gray-600 mb-4">
            Tidak ada data <strong>selesai</strong> dalam rentang waktu
            <strong>{{ \Carbon\Carbon::parse(request('from'))->translatedFormat('d F Y') }}</strong>
            sampai
            <strong>{{ \Carbon\Carbon::parse(request('to'))->translatedFormat('d F Y') }}</strong>.
        </p>
        @endif

        {{-- Tabel Laporan --}}
        <div class="overflow-x-auto rounded">
            <table class="min-w-full border border-gray-300 text-sm text-left">
                <thead class="bg-black text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-3 border-r border-gray-400">#</th>
                        <th class="px-5 py-3 border-r border-gray-400">Tanggal</th>
                        <th class="px-5 py-3 border-r border-gray-400">Nama Pelanggan</th>
                        <th class="px-5 py-3 border-r border-gray-400">Produk</th>
                        <th class="px-5 py-3 border-r border-gray-400">Total Harga</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($pemesanan as $i => $pesanan)
                    <tr class="hover:bg-gray-100 border-b border-gray-300">
                        <td class="px-5 py-3 border-r border-gray-200">
                            {{ $pemesanan->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ \Carbon\Carbon::parse($pesanan->created_at)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pesanan->pelanggan->name }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $pesanan->produk->nama }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">
                            Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                @switch($pesanan->status)
                                    @case('selesai') bg-green-100 text-green-800 @break
                                    @case('dikerjakan') bg-purple-100 text-purple-800 @break
                                    @case('diproses') bg-blue-100 text-blue-800 @break
                                    @default bg-yellow-100 text-yellow-800
                                @endswitch
                            ">
                                {{ ucfirst($pesanan->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-10 text-base font-semibold">
                            Tidak ada data pemesanan yang tersedia.
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