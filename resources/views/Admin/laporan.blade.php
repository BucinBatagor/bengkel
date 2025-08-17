@extends('Template.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<section
  class="flex flex-col items-center px-4 sm:px-6 py-6"
  x-data="{
    from: @js(request('from')),
    to:   @js(request('to')),
    validateRange(e) {
      if (!this.from || !this.to) return;
      if (this.to < this.from) {
        e.preventDefault();
        alert('Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.');
      }
    }
  }"
>
  <div class="w-full max-w-screen-xl bg-white px-4 sm:px-8 py-6 rounded-2xl shadow-md">
    @if (session('success'))
      <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        {{ session('error') }}
      </div>
    @endif

    <div class="mb-4">
      <h1 class="text-2xl font-bold tracking-wide">LAPORAN PENDAPATAN</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto_auto] gap-3 md:gap-4 mb-6 items-end">
      <form
        method="GET"
        action="{{ route('admin.laporan.index') }}"
        class="contents"
        @submit="validateRange($event)"
      >
        <div class="w-full">
          <label class="block text-sm font-medium mb-1">Tanggal Mulai</label>
          <input
            type="date"
            name="from"
            x-model="from"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-black"
          >
        </div>
        <div class="w-full">
          <label class="block text-sm font-medium mb-1">Tanggal Selesai</label>
          <input
            type="date"
            name="to"
            x-model="to"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:border-black"
          >
        </div>
        <div class="w-full md:w-auto">
          <button
            type="submit"
            class="w-full md:w-auto bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition"
          >
            Tampilkan
          </button>
        </div>
      </form>

      @if($hasRange && $ringkasan)
        <form method="GET" action="{{ route('admin.laporan.export') }}" class="w-full md:w-auto">
          <input type="hidden" name="from" value="{{ request('from') }}">
          <input type="hidden" name="to"   value="{{ request('to') }}">
          <button
            type="submit"
            class="w-full md:w-auto bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
          >
            Export PDF
          </button>
        </form>
      @endif
    </div>

    @if($hasRange && $ringkasan)
      <div id="ordersTableWrap" class="overflow-x-auto rounded">
        <table class="min-w-full border border-gray-300 text-sm text-left">
          <thead class="bg-black text-white uppercase text-xs tracking-wider">
            <tr>
              <th class="px-5 py-3 border-r">#</th>
              <th class="px-5 py-3 border-r">Tanggal</th>
              <th class="px-5 py-3 border-r">Pelanggan</th>
              <th class="px-5 py-3 border-r">Produk</th>
              <th class="px-5 py-3 border-r">Bahan Besi</th>
              <th class="px-5 py-3 border-r">Bahan Lainnya</th>
              <th class="px-5 py-3 border-r">Jasa</th>
              <th class="px-5 py-3 border-r">Keuntungan</th>
              <th class="px-5 py-3 border-r">Total Harga</th>
              <th class="px-5 py-3">Bersih</th>
            </tr>
          </thead>
          <tbody class="text-gray-700">
            @forelse ($orders as $index => $pesanan)
              @php
                $sumBesi = 0.0; $sumLain = 0.0; $sumJasa = 0.0;
                foreach ($pesanan->kebutuhan as $k) {
                    $sub = isset($k->subtotal)
                        ? (float) $k->subtotal
                        : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));
                    if ($k->kategori === 'bahan_besi')        $sumBesi += $sub;
                    elseif ($k->kategori === 'bahan_lainnya') $sumLain += $sub;
                    elseif ($k->kategori === 'jasa')          $sumJasa += $sub;
                }
                $kUsed = (isset($pesanan->keuntungan) && is_numeric($pesanan->keuntungan))
                    ? (float) $pesanan->keuntungan
                    : 3.0;
                if ($kUsed < 1) $kUsed = 1.0;
                $kDisp = rtrim(rtrim(number_format($kUsed, 2, ',', '.'), '0'), ',');
                $totalHarga = (isset($pesanan->total_harga) && is_numeric($pesanan->total_harga))
                    ? (float) $pesanan->total_harga
                    : (($sumBesi + $sumLain) * $kUsed);
                $bersih = $totalHarga - $sumBesi - $sumLain - $sumJasa;
              @endphp
              <tr class="hover:bg-gray-100 border-b border-gray-300">
                <td class="px-5 py-3 border-r whitespace-nowrap">{{ $index + 1 }}</td>
                <td class="px-5 py-3 border-r whitespace-nowrap">{{ \Carbon\Carbon::parse($pesanan->created_at)->format('d/m/Y') }}</td>
                <td class="px-5 py-3 border-r">{{ $pesanan->pelanggan->name ?? '-' }}</td>
                <td class="px-5 py-3 border-r">
                  @foreach ($pesanan->detail as $detail)
                    <div class="mb-1">{{ $detail->nama_produk ?? $detail->produk?->nama ?? '-' }}</div>
                  @endforeach
                </td>
                <td class="px-5 py-3 border-r whitespace-nowrap">Rp {{ number_format($sumBesi, 0, ',', '.') }}</td>
                <td class="px-5 py-3 border-r whitespace-nowrap">Rp {{ number_format($sumLain, 0, ',', '.') }}</td>
                <td class="px-5 py-3 border-r whitespace-nowrap">Rp {{ number_format($sumJasa, 0, ',', '.') }}</td>
                <td class="px-5 py-3 border-r whitespace-nowrap">×{{ $kDisp }}</td>
                <td class="px-5 py-3 border-r whitespace-nowrap">Rp {{ number_format($totalHarga, 0, ',', '.') }}</td>
                <td class="px-5 py-3 whitespace-nowrap"><strong>Rp {{ number_format($bersih, 0, ',', '.') }}</strong></td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center py-10 text-gray-500 font-semibold">
                  Tidak ada transaksi pada rentang tanggal ini.
                </td>
              </tr>
            @endforelse
          </tbody>

          @if($orders->count() > 0)
            <tfoot>
              <tr class="bg-gray-50 font-semibold">
                <td class="px-5 py-3 border-t border-gray-300 text-right" colspan="4">Total</td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">
                  Rp {{ number_format($ringkasan['total_bahan_besi'] ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">
                  Rp {{ number_format($ringkasan['total_bahan_lainnya'] ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">
                  Rp {{ number_format($ringkasan['total_jasa'] ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">—</td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">
                  Rp {{ number_format($ringkasan['gross'] ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-5 py-3 border-t border-gray-300 whitespace-nowrap">
                  Rp {{ number_format($ringkasan['net'] ?? 0, 0, ',', '.') }}
                </td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    @else
      <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-gray-500">
        Silakan pilih tanggal mulai dan tanggal selesai, lalu tekan <strong>Tampilkan</strong> untuk melihat laporan.
      </div>
    @endif
  </div>
</section>
@endsection
