@extends('Template.admin')

@section('title', 'Pesanan Masuk')

@section('content')
<section class="flex flex-col items-center px-6 py-6">
    <div class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow flex-1">
        <h1 class="text-2xl font-bold mb-6">PESANAN MASUK</h1>

        <form method="GET" action="{{ route('admin.pemesanan.index') }}"
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 w-full">

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:max-w-md">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama pelanggan..."
                        class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8">
                    @if(request('search'))
                    <button type="button"
                        onclick="window.location.href='{{ route('admin.pemesanan.index', array_merge(request()->except(['search', 'page'])) ) }}'"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-black text-lg"
                        aria-label="Clear search">
                        &times;
                    </button>
                    @endif
                </div>
                <button type="submit"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                    Cari
                </button>
            </div>

            <div class="w-full sm:w-48 relative">
                <select name="status" onchange="this.form.submit()"
                    class="appearance-none w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black">
                    <option value="">Semua Status</option>
                    @foreach(['menunggu', 'dikerjakan', 'selesai', 'menunggu_refund', 'refund_diterima'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                    </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.085l3.71-3.855a.75.75 0 111.08 1.04l-4.25 4.418a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded">
            <table class="min-w-full border border-gray-300 text-sm text-left">
                <thead class="bg-black text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-3 border-r border-gray-400">#</th>
                        <th class="px-5 py-3 border-r border-gray-400">Nama Pelanggan</th>
                        <th class="px-5 py-3 border-r border-gray-400">Alamat</th>
                        <th class="px-5 py-3 border-r border-gray-400">Produk</th>
                        <th class="px-5 py-3 border-r border-gray-400">Ukuran <br> (Panjang x lebar x tinggi)</th>
                        <th class="px-5 py-3 border-r border-gray-400">Total Harga</th>
                        <th class="px-5 py-3 border-r border-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($pemesanan as $i => $pesanan)
                    @php
                    $statusColor = match($pesanan->status) {
                        'menunggu' => 'bg-yellow-100 text-yellow-800',
                        'dikerjakan' => 'bg-blue-100 text-blue-800',
                        'selesai' => 'bg-green-100 text-green-800',
                        'dibatalkan', 'gagal' => 'bg-red-100 text-red-800',
                        'menunggu_refund' => 'bg-red-100 text-red-800',
                        'refund_diterima' => 'bg-green-100 text-green-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                    @endphp
                    <tr class="hover:bg-gray-100 border-b border-gray-300">
                        <td class="px-5 py-3 border-r">{{ $pemesanan->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3 border-r align-top">{{ $pesanan->pelanggan->name }}</td>
                        <td class="px-5 py-3 border-r align-top">{{ $pesanan->pelanggan->address ?? '-' }}</td>
                        <td class="px-5 py-3 border-r align-top">
                            @foreach ($pesanan->details as $detail)
                            <div class="mb-2">
                                {{ $detail->nama_produk ?? $detail->produk?->nama ?? '-' }}
                            </div>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 border-r align-top whitespace-nowrap">
                            @foreach ($pesanan->details as $detail)
                            <div class="mb-2">
                                {{ (float) $detail->panjang ?? 0 }} x {{ (float) $detail->lebar ?? 0 }} x {{ (float) $detail->tinggi ?? 0 }}
                            </div>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 border-r align-top whitespace-nowrap">
                            Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 border-r border-l border-black align-top whitespace-nowrap">
                            @if (in_array($pesanan->status, ['selesai', 'dibatalkan', 'gagal', 'refund_diterima']))
                            <span class="px-3 py-1 text-sm rounded {{ $statusColor }}">
                                {{ ucfirst(str_replace('_', ' ', $pesanan->status)) }}
                            </span>
                            @else
                            <form action="{{ route('admin.pemesanan.update', $pesanan->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @php
                                $statusOptions = match($pesanan->status) {
                                    'menunggu_refund', 'refund_diterima' => ['menunggu_refund', 'refund_diterima'],
                                    default => ['menunggu', 'dikerjakan', 'selesai'],
                                };
                                @endphp
                                <select name="status" onchange="this.form.submit()"
                                    class="border rounded px-2 py-1 text-sm {{ $statusColor }}">
                                    @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" {{ $pesanan->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-gray-500 font-semibold">Tidak ada data pemesanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center mt-8">
        <ul class="inline-flex items-center text-sm">
            <div class="inline-flex space-x-1 mr-2">
                @if ($pemesanan->onFirstPage())
                <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                @else
                <li><a href="{{ $pemesanan->appends(request()->except('page'))->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
                <li><a href="{{ $pemesanan->appends(request()->except('page'))->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
                @endif
            </div>

            <div class="inline-flex space-x-1 mx-2">
                @php
                $current = $pemesanan->currentPage();
                $last = $pemesanan->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $start + 4);
                if ($end - $start < 4) {
                    $start = max(1, $end - 4);
                }
                @endphp

                @for ($i = $start; $i <= $end; $i++)
                <li>
                    <a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}"
                        class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                        {{ $i }}
                    </a>
                </li>
                @endfor
            </div>

            <div class="inline-flex space-x-1 ml-2">
                @if ($pemesanan->hasMorePages())
                <li><a href="{{ $pemesanan->appends(request()->except('page'))->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
                <li><a href="{{ $pemesanan->appends(request()->except('page'))->url($pemesanan->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
                @else
                <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                @endif
            </div>
        </ul>
    </div>
</section>
@endsection
