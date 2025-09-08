@extends('Template.admin')

@section('title', 'Kelola Pesanan')

@section('content')
<section class="flex flex-col items-center px-6 py-6" x-data="ordersRealtime()" x-init="start()">
  <style>[x-cloak]{display:none!important}</style>
  <style>
    table.orders td, table.orders th { vertical-align: top }
  </style>

  <div class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow flex-1 min-h-[600px]">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Kelola Pesanan</h1>
      <div class="text-xs text-gray-500" x-text="statusText"></div>
    </div>

    @if(session('success'))
      <div class="mb-4 p-3 rounded bg-green-50 text-green-800 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-4 p-3 rounded bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.pemesanan.index') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 w-full">
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative w-full sm:max-w-md">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pelanggan..." class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8">
          @if(request('search'))
            <button type="button" onclick="window.location.href='{{ route('admin.pemesanan.index', array_merge(request()->except(['search','page'])) ) }}'" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black text-lg">&times;</button>
          @endif
        </div>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">Cari</button>
      </div>

      <div class="w-full sm:w-64 relative">
        @php
          $filterOptions = [
            'butuh_cek_ukuran'     => 'Butuh Cek Ukuran',
            'belum_bayar'          => 'Belum Bayar',
            'di_proses'            => 'Di Proses',
            'dikerjakan'           => 'Dikerjakan',
            'selesai'              => 'Selesai',
            'pengembalian_dana'    => 'Pengembalian Dana',
            'pengembalian_selesai' => 'Pengembalian Selesai',
            'batal'                => 'Batal',
            'gagal'                => 'Gagal',
          ];
        @endphp
        <select name="status" onchange="this.form.submit()" class="appearance-none w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black">
          <option value="">Semua Status</option>
          @foreach($filterOptions as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-600">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.085l3.71-3.855a.75.75 0 111.08 1.04l-4.25 4.418a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </div>
      </div>
    </form>

    @php
      $labels = [
        'butuh_cek_ukuran'     => 'Butuh Cek Ukuran',
        'belum_bayar'          => 'Belum Bayar',
        'di_proses'            => 'Di Proses',
        'dikerjakan'           => 'Dikerjakan',
        'selesai'              => 'Selesai',
        'pengembalian_dana'    => 'Pengembalian Dana',
        'pengembalian_selesai' => 'Pengembalian Selesai',
        'batal'                => 'Batal',
        'gagal'                => 'Gagal',
      ];
    @endphp

    <div id="ordersTableWrap" class="overflow-x-auto rounded">
      <table class="orders min-w-full border border-gray-300 text-sm text-left">
        <thead class="bg-black text-white uppercase text-xs tracking-wider">
          <tr>
            <th class="px-5 py-3 border-r">Tanggal</th>
            <th class="px-5 py-3 border-r">Nama Pelanggan</th>
            <th class="px-5 py-3 border-r">Produk</th>
            <th class="px-5 py-3 border-r">Jumlah</th>
            <th class="px-5 py-3 border-r">Total Harga</th>
            <th class="px-5 py-3 border-r">Pembayaran</th>
            <th class="px-5 py-3 border-r">Status</th>
            <th class="px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          @forelse ($pemesanan as $pesanan)
            @php
              $badge = match($pesanan->status) {
                'butuh_cek_ukuran', 'belum_bayar' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
                'di_proses', 'dikerjakan' => 'bg-blue-100 text-blue-800 ring-1 ring-blue-200',
                'selesai', 'pengembalian_selesai' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
                'pengembalian_dana' => 'bg-orange-100 text-orange-800 ring-1 ring-orange-200',
                'batal','gagal' => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200',
                default => 'bg-slate-100 text-slate-800 ring-1 ring-slate-200',
              };
              $blockedStates = ['butuh_cek_ukuran','belum_bayar','batal','gagal'];
              $isBlocked = in_array($pesanan->status, $blockedStates, true);
              if ($pesanan->status === 'di_proses') {
                $options = ['dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'];
              } elseif ($pesanan->status === 'dikerjakan') {
                $options = ['di_proses' => 'Di Proses', 'selesai' => 'Selesai'];
              } elseif ($pesanan->status === 'selesai') {
                $options = ['di_proses' => 'Di Proses', 'dikerjakan' => 'Dikerjakan'];
              } elseif ($pesanan->status === 'pengembalian_dana') {
                $options = ['pengembalian_selesai' => 'Pengembalian Selesai'];
              } elseif ($pesanan->status === 'pengembalian_selesai') {
                $options = ['pengembalian_dana' => 'Pengembalian Dana'];
              } else {
                $options = [];
              }
              $hasKebutuhan = ($pesanan->kebutuhan->count() ?? 0) > 0;

              $total = (float)($pesanan->total_harga ?? 0);
              $dp    = (float)($pesanan->dp ?? 0);
              $sisa  = $total > 0 ? max(0, $total - $dp) : 0;
              if ($total <= 0) {
                $payText = '—';
                $payClass = 'bg-gray-100 text-gray-700 ring-1 ring-gray-200';
              } elseif ($dp <= 0) {
                $payText = 'Belum Bayar';
                $payClass = 'bg-rose-100 text-rose-800 ring-1 ring-rose-200';
              } elseif ($sisa <= 0) {
                $payText = 'Lunas';
                $payClass = 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200';
              } else {
                $payText = 'Sisa Rp '.number_format($sisa,0,',','.');
                $payClass = 'bg-amber-100 text-amber-800 ring-1 ring-amber-200';
              }
            @endphp

            <tr
              class="hover:bg-gray-100 border-b border-gray-300"
              x-data="statusMenu({ id: {{ $pesanan->id }}, current: '{{ $pesanan->status }}', blocked: {{ $isBlocked ? 'true' : 'false' }}, options: @js($options), labelMap: @js($labels) })"
            >
              <td class="px-5 py-3 border-r whitespace-nowrap align-top">{{ optional($pesanan->created_at)->format('d/m/Y') }}</td>
              <td class="px-5 py-3 border-r align-top">{{ $pesanan->pelanggan->name ?? '-' }}</td>

              @php $details = $pesanan->detail; @endphp
              <td class="px-5 py-3 border-r align-top">
                <div class="space-y-1">
                  @foreach ($details as $d)
                    <div class="h-6 leading-6 truncate max-w-[220px]">{{ $d->nama_produk ?? $d->produk->nama ?? '-' }}</div>
                  @endforeach
                </div>
              </td>
              <td class="px-5 py-3 border-r whitespace-nowrap align-top">
                <div class="space-y-1">
                  @foreach ($details as $d)
                    <div class="h-6 leading-6">{{ (int)($d->jumlah ?? 1) }}</div>
                  @endforeach
                </div>
              </td>

              <td class="px-5 py-3 border-r whitespace-nowrap align-top">
                @if((float)$pesanan->total_harga > 0)
                  <div>Rp {{ number_format($pesanan->total_harga,0,',','.') }}</div>
                @else
                  <span>—</span>
                @endif
              </td>

              <td class="px-5 py-3 border-r whitespace-nowrap align-top">
                <span class="px-2.5 py-0.5 rounded leading-tight {{ $payClass }}">{{ $payText }}</span>
              </td>

              <td class="px-5 py-3 border-r align-top">
                <div class="inline-block">
                  <button
                    x-ref="btn"
                    type="button"
                    @click.stop="toggle($event.currentTarget)"
                    :disabled="blocked || Object.keys(options).length === 0"
                    :aria-expanded="open ? 'true' : 'false'"
                    class="px-3 py-1 rounded leading-tight {{ $badge }} disabled:opacity-60 disabled:cursor-not-allowed hover:ring-2 hover:ring-offset-2 hover:ring-gray-300 flex items-start gap-1 text-left max-w-[180px] whitespace-normal break-words"
                  >
                    <span class="inline-block" x-text="labelMap[value] ?? value"></span>
                    <svg x-show="!blocked && Object.keys(options).length" class="w-4 h-4 self-start mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5.25 7.5L10 12.25 14.75 7.5H5.25z"/></svg>
                  </button>
                </div>
                <form x-ref="form" method="POST" action="{{ route('admin.pemesanan.update_status', $pesanan->id) }}" class="hidden">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="status" x-model="value">
                </form>
              </td>

              @php
                $productsForUpload = $details->map(function($d){
                  $pid = $d->produk->id ?? null;
                  $name = $d->nama_produk ?? $d->produk->nama ?? 'Produk';
                  return $pid ? ['id' => $pid, 'name' => $name] : null;
                })->filter()->values();
              @endphp

              <td class="px-5 py-3 align-top">
                @if ($pesanan->status === 'belum_bayar' && $hasKebutuhan)
                  <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2">
                    <a href="#" @click.prevent="$dispatch('open-order-detail', { id: {{ $pesanan->id }} })" class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Detail</a>
                    <a href="{{ route('admin.pemesanan.kebutuhan.edit', $pesanan->id) }}" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Edit</a>
                    <button type="button" @click="$dispatch('open-delete', { url: '{{ route('admin.pemesanan.kebutuhan.destroy', $pesanan->id) }}' })" class="px-3 py-1 rounded bg-rose-600 text-white hover:bg-rose-700">Hapus</button>
                  </div>
                @elseif (in_array($pesanan->status, ['di_proses','dikerjakan'], true) && $hasKebutuhan)
                  <a href="#" @click.prevent="$dispatch('open-order-detail', { id: {{ $pesanan->id }} })" class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Detail</a>
                @elseif ($pesanan->status === 'selesai')
                  <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2" x-data="{ uploadUrl: '{{ route('admin.pemesanan.upload_gambar', $pesanan->id) }}', products: @js($productsForUpload) }">
                    @if($hasKebutuhan)
                      <a href="#" @click.prevent="$dispatch('open-order-detail', { id: {{ $pesanan->id }} })" class="px-3 py-1 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Detail</a>
                    @endif
                    <button type="button" @click="$dispatch('open-upload', { url: uploadUrl, products: products })" class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Upload</button>
                  </div>
                @elseif($pesanan->status !== 'batal' && ($pesanan->status === 'butuh_cek_ukuran' || ((float)$pesanan->total_harga) <= 0))
                  <a href="{{ route('admin.pemesanan.kebutuhan.edit', $pesanan->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Kebutuhan</a>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>
            </tr>

            <tr class="hidden">
              <td colspan="8">
                <div id="detail-content-{{ $pesanan->id }}">
                  @php
                    $kVal = (float)($pesanan->keuntungan ?? 0);
                    $kStr = fmod($kVal,1.0)==0.0 ? number_format($kVal,0,',','.') : rtrim(rtrim(number_format($kVal,1,',','.'),'0'),',');
                  @endphp

                  <div class="space-y-6">
                    <div class="rounded-xl overflow-hidden ring-1 ring-gray-200 bg-white">
                      <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold">Pelanggan</h3>
                      </div>
                      <div class="p-5 overflow-x-auto">
                        <table class="min-w-[600px] w-full border border-gray-300 text-sm border-collapse">
                          <thead class="bg-black text-white uppercase text-xs tracking-wider">
                            <tr>
                              <th class="px-4 py-2 border border-gray-300 text-left w-1/4">Nama</th>
                              <th class="px-4 py-2 border border-gray-300 text-left w-1/4">No. HP</th>
                              <th class="px-4 py-2 border border-gray-300 text-left w-2/4">Alamat</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td class="px-4 py-2 border border-gray-300">{{ $pesanan->pelanggan->name ?? '-' }}</td>
                              <td class="px-4 py-2 border border-gray-300">{{ $pesanan->pelanggan->phone ?? '-' }}</td>
                              <td class="px-4 py-2 border border-gray-300">{{ $pesanan->pelanggan->address ?? '-' }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    @foreach($pesanan->detail as $detail)
                      @php
                        $rows = ($pesanan->kebutuhan ?? collect())->where('pemesanan_detail_id', $detail->id);
                        $gambar = $detail->produk?->gambar->first()?->gambar;
                        $sumBahan = 0;
                      @endphp
                      <div class="rounded-xl overflow-hidden ring-1 ring-gray-200 bg-white">
                        <div class="px-5 py-3 border-b border-gray-200 bg-gray-50 flex items-start gap-4">
                          <div class="w-14 h-14 rounded overflow-hidden bg-gray-200">
                            @if($gambar)
                              <img src="{{ asset('storage/'.$gambar) }}" alt="Produk" class="w-full h-full object-cover">
                            @endif
                          </div>
                          <div>
                            <div class="font-semibold">{{ $detail->nama_produk ?? $detail->produk->nama ?? 'Produk' }}</div>
                            <div class="text-sm text-gray-600">Jumlah {{ (int)($detail->jumlah ?? 1) }}</div>
                          </div>
                        </div>
                        <div class="p-5 overflow-x-auto">
                          @if($rows->isEmpty())
                            <div class="text-gray-500 text-sm">Belum ada kebutuhan untuk produk ini.</div>
                          @else
                            <table class="min-w-[980px] w-full border border-gray-300 border-collapse text-sm">
                              <thead class="bg-black text-white uppercase text-xs tracking-wider">
                                <tr>
                                  <th class="px-5 py-3 border border-gray-300 w-48 text-left">Kategori</th>
                                  <th class="px-5 py-3 border border-gray-300 text-left">Nama Kebutuhan</th>
                                  <th class="px-5 py-3 border border-gray-300 w-40 text-left">Kuantitas</th>
                                  <th class="px-5 py-3 border border-gray-300 w-44 text-left">Harga</th>
                                  <th class="px-5 py-3 border border-gray-300 w-44 text-left">Total</th>
                                </tr>
                              </thead>
                              <tbody class="text-gray-700">
                                @foreach($rows as $r)
                                  @php
                                    $q = (float)($r->kuantitas ?? 0);
                                    $qtyStr = fmod($q,1.0)==0.0 ? number_format($q,0,',','.') : rtrim(rtrim(number_format($q,1,',','.'),'0'),',');
                                    $harga = (int)($r->harga ?? 0);
                                    $subtotal = (int)($r->subtotal ?? round($q * $harga));
                                    if (in_array($r->kategori, ['bahan_besi','bahan_lainnya'], true)) { $sumBahan += $subtotal; }
                                  @endphp
                                  <tr class="hover:bg-gray-100/60">
                                    <td class="px-5 py-3 border border-gray-300">{{ ucfirst(str_replace('_',' ', $r->kategori)) }}</td>
                                    <td class="px-5 py-3 border border-gray-300">{{ $r->nama }}</td>
                                    <td class="px-5 py-3 border border-gray-300">{{ $qtyStr }}</td>
                                    <td class="px-5 py-3 border border-gray-300">Rp {{ number_format($harga,0,',','.') }}</td>
                                    <td class="px-5 py-3 border border-gray-300">Rp {{ number_format($subtotal,0,',','.') }}</td>
                                  </tr>
                                @endforeach
                              </tbody>
                              <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                  <td class="px-5 py-3 border border-gray-300" colspan="4">Total Bahan</td>
                                  <td class="px-5 py-3 border border-gray-300">Rp {{ number_format($sumBahan,0,',','.') }}</td>
                                </tr>
                              </tfoot>
                            </table>
                          @endif
                        </div>
                      </div>
                    @endforeach

                    <div class="inline-block w-max border border-gray-200 rounded-xl bg-gray-50 shadow-sm">
                      <div class="px-4 py-2 text-sm">
                        <span class="text-gray-600">Keuntungan</span>
                        <span class="font-semibold ml-2">x{{ $kStr }}</span>
                      </div>
                      <div class="border-t border-gray-200"></div>
                      <div class="px-4 py-2 text-sm">
                        <span class="text-gray-600">Total Harga</span>
                        <span class="font-semibold ml-2">Rp {{ number_format((int)($pesanan->total_harga ?? 0),0,',','.') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-10 text-gray-500 font-semibold">Tidak ada data pemesanan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div id="paginationWrap" class="flex justify-center mt-8">
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
          if ($end - $start < 4) { $start = max(1, $end - 4); }
        @endphp
        @for ($i = $start; $i <= $end; $i++)
          <li><a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}" class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">{{ $i }}</a></li>
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

  <div x-data="detailsModal()" x-on:open-order-detail.window="openById($event.detail.id)">
    <div x-show="open" x-cloak class="fixed inset-0 z-[200]">
      <div class="absolute inset-0 bg-black/40" @click="close()"></div>
      <div class="relative h-full w-full flex items-center justify-center p-4">
        <div class="bg-white rounded-xl overflow-hidden shadow-2xl ring-1 ring-gray-200 w-full max-w-screen-xl" @click.stop>
          <div class="flex items-center justify-between border-b px-5 py-3">
            <h2 class="font-semibold">Detail Pesanan</h2>
            <button @click="close()" class="text-gray-500 hover:text-black text-xl leading-none">&times;</button>
          </div>
          <div class="p-5 max-h-[75vh] overflow-y-auto">
            <div x-html="html"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-data="deleteConfirm()" x-on:open-delete.window="openModal($event.detail.url)">
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-[250] flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-lg w-[90%] max-w-md p-6 text-center">
        <h2 class="text-lg font-bold mb-2">Konfirmasi Penghapusan</h2>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus kebutuhan pada pesanan ini?</p>
        <form :action="url" method="POST" class="flex items-center justify-center gap-3">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-5 py-2 rounded bg-red-600 text-white hover:bg-red-700">Hapus</button>
          <button type="button" @click="closeModal()" class="px-5 py-2 rounded bg-gray-200 text-gray-800 hover:bg-gray-300">Batal</button>
        </form>
      </div>
    </div>
  </div>

  <div x-data="uploadModal()" x-on:open-upload.window="open($event.detail)">
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-[260] flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-lg">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold">Upload Gambar Produk</h2>
          <button @click="close()" class="text-gray-500 hover:text-black text-xl leading-none">&times;</button>
        </div>
        <form x-ref="form" :action="action" method="POST" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <div>
            <label class="block text-sm font-semibold mb-1">Pilih Produk</label>
            <select name="produk_id" x-model="selected" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
              <template x-for="p in products" :key="p.id">
                <option :value="p.id" x-text="p.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-500 mt-1" x-show="products.length > 1">Pesanan memiliki beberapa produk. Pilih salah satu yang ingin ditambahkan gambar.</p>
          </div>
          <div>
            <label class="block text-sm font-semibold mb-1">Gambar</label>
            <input x-ref="file" type="file" name="gambar[]" accept="image/png,image/jpeg" multiple class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
            <p class="text-xs text-gray-500 mt-1">Maks 2MB per gambar. Tipe yang didukung: JPG, JPEG, PNG.</p>
          </div>
          <div x-show="err" class="text-red-600 text-sm" x-text="err"></div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="close()" class="px-4 py-2 rounded bg-gray-200 text-gray-800 hover:bg-gray-300">Batal</button>
            <button type="button" @click="submit()" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Upload</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
function ordersRealtime(){
  return {
    timer: null,
    period: 10000,
    statusText: 'Sinkron otomatis aktif',
    ctrl: null,
    start(){
      this.loop();
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) { clearTimeout(this.timer); this.statusText = 'Jeda sinkron (tab tidak aktif)'; }
        else { this.statusText = 'Sinkron otomatis aktif'; this.loop(); }
      });
    },
    loop(){
      this.timer = setTimeout(() => this.refresh().finally(() => this.loop()), this.period);
    },
    async refresh(){
      try{
        this.ctrl?.abort();
        this.ctrl = new AbortController();
        this.statusText = 'Menyinkronkan...';
        const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: this.ctrl.signal });
        if (res.status === 401 || res.redirected) { window.location.reload(); return; }
        const html = await res.text();
        const doc  = new DOMParser().parseFromString(html, 'text/html');
        const newTable = doc.getElementById('ordersTableWrap');
        const tableWrap = document.getElementById('ordersTableWrap');
        if (newTable && tableWrap) { tableWrap.innerHTML = newTable.innerHTML; if (window.Alpine?.initTree) Alpine.initTree(tableWrap); }
        const newPag = doc.getElementById('paginationWrap');
        const pagWrap = document.getElementById('paginationWrap');
        if (newPag && pagWrap) { pagWrap.innerHTML = newPag.innerHTML; if (window.Alpine?.initTree) Alpine.initTree(pagWrap); }
        this.statusText = 'Terakhir sinkron: ' + new Date().toLocaleTimeString();
      } catch(e){
        if (e.name !== 'AbortError') this.statusText = 'Gagal sinkron, mencoba lagi...';
      }
    }
  }
}

function statusMenu({id, current, blocked=false, options={}, labelMap={}}){
  return {
    id, blocked, options, labelMap, open: false, value: current, pos: { top: 0, left: 0, width: 0 },
    toggle(btn){
      if(this.blocked || Object.keys(this.options).length === 0) return;
      if(this.open){ this.close(); return; }
      const r = btn.getBoundingClientRect();
      this.pos = { top: r.bottom + window.scrollY + 6, left: r.left + window.scrollX, width: r.width };
      this.open = true; this.bind();
    },
    close(){
      this.open = false;
      document.removeEventListener('click', this._onDocClick, true);
      window.removeEventListener('scroll', this._realign, true);
      window.removeEventListener('resize', this._realign, true);
      const overlay = document.getElementById(this.overlayId());
      const menu = document.getElementById(this.menuId());
      if (overlay) overlay.remove();
      if (menu) menu.remove();
    },
    overlayId(){ return `status-overlay-${this.id}`; },
    menuId(){ return `status-menu-${this.id}`; },
    bind(){
      this._realign = () => {
        if(!this.open) return;
        const btn = this.$refs.btn; if(!btn) return;
        const r = btn.getBoundingClientRect();
        this.pos = { top: r.bottom + window.scrollY + 6, left: r.left + window.scrollX, width: r.width };
        const menu = document.getElementById(this.menuId());
        if(menu){ menu.style.top = this.pos.top+'px'; menu.style.left = this.pos.left+'px'; menu.style.minWidth = this.pos.width+'px'; }
      };
      this._onDocClick = (e) => {
        const menu = document.getElementById(this.menuId());
        if (!menu || menu.contains(e.target)) return;
        const btn = this.$refs.btn; if (btn && btn.contains(e.target)) return;
        this.close();
      };
      const overlay = document.createElement('div');
      overlay.id = this.overlayId();
      overlay.style.position = 'fixed'; overlay.style.inset = '0'; overlay.style.zIndex = '100';
      overlay.addEventListener('click', () => this.close(), { passive: true });
      const menu = document.createElement('div');
      menu.id = this.menuId();
      menu.style.position = 'fixed'; menu.style.top = this.pos.top+'px'; menu.style.left = this.pos.left+'px'; menu.style.minWidth = this.pos.width+'px'; menu.style.zIndex = '101';
      menu.className = 'bg-white border border-gray-200 rounded-lg shadow';
      const wrap = document.createElement('div'); wrap.className = 'py-1';
      Object.entries(this.options).forEach(([val, label]) => {
        const btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'w-full text-left px-3 py-2 text-sm hover:bg-gray-100'; btn.textContent = label;
        if (this.value === val) btn.classList.add('font-semibold');
        btn.addEventListener('click', (ev) => { ev.stopPropagation(); if (val === this.value) { this.close(); return; } this.value = val; this.$nextTick(() => this.$refs.form.submit()); this.close(); });
        wrap.appendChild(btn);
      });
      menu.appendChild(wrap);
      document.body.appendChild(overlay); document.body.appendChild(menu);
      document.addEventListener('click', this._onDocClick, true);
      window.addEventListener('scroll', this._realign, true);
      window.addEventListener('resize', this._realign);
    }
  }
}

function detailsModal(){
  return {
    open: false, html: '',
    openById(id){
      const el = document.getElementById('detail-content-' + id);
      this.html = el ? el.innerHTML : '<div class="text-red-600 text-sm">Data tidak ditemukan.</div>';
      this.open = true;
    },
    close(){ this.open = false; this.html = ''; }
  }
}

function deleteConfirm(){
  return {
    isOpen: false,
    url: '',
    openModal(u){ this.url = u; this.isOpen = true },
    closeModal(){ this.isOpen = false; this.url = '' }
  }
}

function uploadModal(){
  return {
    isOpen: false,
    action: '',
    products: [],
    selected: '',
    err: '',
    open(detail){
      this.action = detail.url || '';
      this.products = Array.isArray(detail.products) ? detail.products : [];
      this.selected = this.products.length ? String(this.products[0].id) : '';
      this.err = '';
      this.isOpen = true;
      if (this.$refs.file) this.$refs.file.value = '';
    },
    close(){
      this.isOpen = false;
      this.action = '';
      this.products = [];
      this.selected = '';
      this.err = '';
      if (this.$refs.file) this.$refs.file.value = '';
    },
    validate(){
      if (!this.selected) { this.err = 'Pilih produk.'; return false; }
      const f = this.$refs.file;
      if (!f || !f.files || f.files.length === 0) { this.err = 'Pilih minimal satu gambar.'; return false; }
      this.err = '';
      return true;
    },
    submit(){
      if (!this.validate()) return;
      this.$refs.form.submit();
    }
  }
}
</script>
@endsection
