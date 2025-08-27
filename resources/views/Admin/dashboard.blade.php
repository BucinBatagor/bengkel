@extends('Template.admin')

@section('title', 'Dashboard')

@section('content')
<section class="flex flex-col items-center px-6 py-6">
  <div class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow">

    {{-- Judul --}}
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Dashboard</h1>
    </div>

    {{-- Baris: Jumlah Pelanggan (sejajar & sama lebar dengan grid 3 kolom) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="p-4 rounded-lg border bg-white min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Jumlah Pelanggan</div>
        <div class="text-2xl font-bold mt-1 leading-tight">
          {{ number_format($totalCustomers ?? 0, 0, ',', '.') }}
        </div>
      </div>
      <div class="hidden sm:block"></div>
      <div class="hidden sm:block"></div>
    </div>

    {{-- Baris 1: Batal, Perlu Ukur, Belum Bayar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="p-4 rounded-lg border bg-red-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Batal</div>
        <div class="text-2xl font-bold">{{ number_format($orderCounts['batal'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="p-4 rounded-lg border bg-yellow-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Perlu Ukur</div>
        <div class="text-2xl font-bold">{{ number_format($orderCounts['perlu_diukur'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="p-4 rounded-lg border bg-amber-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Belum Bayar</div>
        <div class="text-2xl font-bold">{{ number_format($unpaidOrderCount ?? 0, 0, ',', '.') }}</div>
      </div>
    </div>

    {{-- Baris 2: Refund, Proses, Selesai --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="p-4 rounded-lg border bg-orange-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Refund</div>
        <div class="text-2xl font-bold">{{ number_format($orderCounts['refund'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="p-4 rounded-lg border bg-blue-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Proses</div>
        <div class="text-2xl font-bold">{{ number_format($orderCounts['proses'] ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="p-4 rounded-lg border bg-green-50 min-h-[96px] flex flex-col justify-center">
        <div class="text-sm text-gray-600">Selesai</div>
        <div class="text-2xl font-bold">{{ number_format($orderCounts['selesai'] ?? 0, 0, ',', '.') }}</div>
      </div>
    </div>

    {{-- Produk Terjual per Kategori (status selesai) - Tanpa Persentase --}}
    <div class="bg-white border rounded-lg p-5" x-data="{ view: 'grid' }">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Produk Terjual per Kategori</h2>

        {{-- Toggle tampilan --}}
        <div class="inline-flex rounded border overflow-hidden text-xs">
          <button
            type="button"
            class="px-3 py-1 hover:bg-gray-100"
            :class="view==='grid' ? 'bg-black text-white hover:bg-black' : ''"
            @click="view='grid'"
          >
            Grid
          </button>
          <button
            type="button"
            class="px-3 py-1 hover:bg-gray-100 border-l"
            :class="view==='table' ? 'bg-black text-white hover:bg-black' : ''"
            @click="view='table'"
          >
            Tabel
          </button>
        </div>
      </div>

      @php
        $data = collect($kategori ?? []);
      @endphp

      @if ($data->isEmpty())
        <div class="text-gray-500">Belum ada data penjualan selesai.</div>
      @else
        {{-- GRID (tanpa persen/bar) --}}
        <div x-show="view==='grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach ($data as $row)
            <div class="border rounded-lg p-4 hover:shadow-sm transition">
              <div class="font-medium truncate mb-1" title="{{ $row->kategori }}">{{ $row->kategori }}</div>
              <div class="text-2xl font-bold leading-none">{{ (int)$row->total }}</div>
            </div>
          @endforeach
        </div>

        {{-- TABEL (tanpa kolom persentase) --}}
        <div x-show="view==='table'" x-cloak class="overflow-x-auto">
          <table class="min-w-full text-sm border border-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 border">#</th>
                <th class="px-3 py-2 border text-left">Kategori</th>
                <th class="px-3 py-2 border text-center">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data as $idx => $row)
                <tr class="hover:bg-gray-50">
                  <td class="px-3 py-2 border text-center">{{ $idx + 1 }}</td>
                  <td class="px-3 py-2 border">{{ $row->kategori }}</td>
                  <td class="px-3 py-2 border text-center font-semibold">{{ (int)$row->total }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

  </div>
</section>
@endsection
