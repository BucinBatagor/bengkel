@extends('Template.pelanggan')

@section('title', 'Pesanan Saya')

@section('content')
<section class="bg-gray-200 py-12 min-h-screen" x-data="pesananApp()">
  <style>[x-cloak]{display:none !important}</style>

  <div class="max-w-screen-xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6 min-h-[550px] px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold mb-6 text-left">Status Pesanan Saya</h1>

      @if (session('success'))
        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
          {{ session('success') }}
        </div>
      @endif

      @if (session('error'))
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
          {{ session('error') }}
        </div>
      @endif

      @if ($pemesanan->count() === 0)
        <div class="text-gray-500">Belum ada pesanan.</div>
      @else
        <div class="space-y-8">
          @foreach ($pemesanan as $pesanan)
            @php
              $statusText = match($pesanan->status) {
                'butuh_cek_ukuran'     => 'Butuh Cek Ukuran',
                'batal'                => 'Batal',
                'belum_bayar'          => 'Belum Bayar',
                'gagal'                => 'Gagal',
                'di_proses'            => 'Di Proses',
                'dikerjakan'           => 'Dikerjakan',
                'selesai'              => 'Selesai',
                'pengembalian_dana'    => 'Pengembalian Dana',
                'pengembalian_selesai' => 'Pengembalian Selesai',
                default                => ucfirst(str_replace('_',' ',$pesanan->status)),
              };
              $statusColor = match($pesanan->status) {
                'butuh_cek_ukuran'     => 'bg-yellow-50 text-yellow-800',
                'belum_bayar'          => 'bg-yellow-100 text-yellow-800',
                'di_proses'            => 'bg-blue-100 text-blue-800',
                'dikerjakan'           => 'bg-indigo-100 text-indigo-800',
                'selesai'              => 'bg-green-100 text-green-800',
                'pengembalian_dana'    => 'bg-orange-100 text-orange-800',
                'pengembalian_selesai' => 'bg-green-50 text-green-800',
                'batal','gagal'        => 'bg-red-100 text-red-800',
                default                => 'bg-gray-100 text-gray-800',
              };
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border rounded-lg p-6 bg-white shadow">
              <div class="md:col-span-3 flex justify-between items-center">
                <span class="text-gray-600 font-medium">{{ $pesanan->created_at->format('d M Y') }}</span>
                <span class="text-sm px-3 py-1 rounded-full {{ $statusColor }}">{{ $statusText }}</span>
              </div>

              <div class="md:col-span-3">
                <div class="my-4 h-px w-full bg-gray-200"></div>
              </div>

              <div class="md:col-span-2 space-y-4">
                @foreach ($pesanan->detail as $detail)
                  @php $gambar = $detail->produk?->gambar->first()?->gambar; @endphp
                  <div class="flex items-center gap-4 border-b border-gray-200 pb-4 last:border-b-0">
                    <div class="w-20 h-20 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                      @if ($gambar)
                        <img src="{{ asset('storage/'.$gambar) }}" alt="Produk" class="w-full h-full object-cover">
                      @else
                        <div class="flex items-center justify-center h-full text-gray-400 text-xs">No Gambar</div>
                      @endif
                    </div>
                    <div class="flex-1">
                      <p class="font-semibold text-gray-800">{{ $detail->nama_produk }}</p>
                      <p class="text-gray-600 text-sm">{{ $detail->produk?->kategori ?? '-' }}</p>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="flex flex-col justify-between items-end">
                <div class="space-y-2">
                  <p class="text-gray-600 text-sm">Total Harga</p>
                  <p class="text-xl font-bold">Rp {{ number_format($pesanan->total_harga,0,',','.') }}</p>
                </div>
                <div class="mt-4 w-full">
                  @if ($pesanan->status === 'butuh_cek_ukuran')
                    <form action="{{ route('pesanan.batal', $pesanan->id) }}" method="POST" @submit.prevent="confirmAction('Batalkan Pesanan','Yakin ingin membatalkan pesanan ini?',() => $el.submit())">
                      @csrf
                      <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded">Batalkan Pesanan</button>
                    </form>
                  @elseif ($pesanan->status === 'belum_bayar')
                    <button @click="pay('{{ $pesanan->id }}')" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">Bayar Sekarang</button>
                  @elseif ($pesanan->status === 'di_proses')
                    <form action="{{ route('pesanan.ajukan_refund', $pesanan->id) }}" method="POST" @submit.prevent="confirmAction('Ajukan Refund','Ajukan pengembalian dana untuk pesanan ini?',() => $el.submit())">
                      @csrf
                      <button type="submit" class="w-full bg-gray-700 hover:bg-gray-800 text-white py-2 rounded">Ajukan Refund</button>
                    </form>
                  @elseif ($pesanan->status === 'pengembalian_dana')
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    @if ($pemesanan->count() > 0)
      <div class="mt-8 flex justify-center">
        <ul class="inline-flex items-center text-sm">
          <div class="inline-flex space-x-1 mr-2">
            @if (method_exists($pemesanan, 'onFirstPage') && $pemesanan->onFirstPage())
              <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
              <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
            @else
              <li><a href="{{ $pemesanan->appends(request()->except('page'))->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
              <li><a href="{{ $pemesanan->appends(request()->except('page'))->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
            @endif
          </div>

          <div class="inline-flex space-x-1 mx-2">
            @php
              $current = method_exists($pemesanan, 'currentPage') ? $pemesanan->currentPage() : 1;
              $last    = method_exists($pemesanan, 'lastPage') ? $pemesanan->lastPage() : 1;
              $start   = max(1, $current - 2);
              $end     = min($last, $start + 4);
              if ($end - $start < 4) { $start = max(1, $end - 4); }
            @endphp
            @for ($i = $start; $i <= $end; $i++)
              <li>
                <a href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}" class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">{{ $i }}</a>
              </li>
            @endfor
          </div>

          <div class="inline-flex space-x-1 ml-2">
            @if (method_exists($pemesanan, 'hasMorePages') && $pemesanan->hasMorePages())
              <li><a href="{{ $pemesanan->appends(request()->except('page'))->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
              <li><a href="{{ $pemesanan->appends(request()->except('page'))->url($pemesanan->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
            @else
              <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
              <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
            @endif
          </div>
        </ul>
      </div>
    @endif
  </div>

  <div x-show="popup.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="popup.show = false">
    <div class="absolute inset-0 bg-black/50" x-transition.opacity @click="popup.type==='confirm' ? (popup.onCancel ? popup.onCancel() : popup.show=false) : (popup.show=false)"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6" x-transition.scale.origin.center role="dialog" aria-modal="true" :aria-label="popup.title || 'Dialog'">
      <h2 class="text-lg font-semibold mb-2" x-text="popup.title || 'Pemberitahuan'"></h2>
      <p class="text-sm text-gray-700 mb-6" x-text="popup.message || ''"></p>
      <div class="flex justify-end gap-2">
        <template x-if="popup.type === 'confirm'">
          <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100" @click="popup.onCancel ? popup.onCancel() : (popup.show = false)">Batal</button>
        </template>
        <button type="button" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800" @click="popup.type === 'confirm' ? (popup.onConfirm ? popup.onConfirm() : null) : (popup.show = false)">
          <span x-text="popup.type === 'confirm' ? 'Ya' : 'Tutup'"></span>
        </button>
      </div>
    </div>
  </div>
</section>

@php
  $isProd = config('midtrans.is_production', false);
  $clientKey = config('midtrans.client_key');
@endphp
<script src="https://app{{ $isProd ? '' : '.sandbox' }}.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>

<script>
window.pesananApp = function() {
  return {
    popup: { show: false, type: '', title: '', message: '', onConfirm: null, onCancel: null },
    csrf() {
      const m = document.querySelector('meta[name="csrf-token"]');
      return m ? m.getAttribute('content') : @json(csrf_token());
    },
    confirmAction(title, message, onConfirm = () => {}) {
      this.popup = {
        show: true,
        type: 'confirm',
        title,
        message,
        onConfirm: () => { try { onConfirm(); } finally { this.popup.show = false; } },
        onCancel: () => { this.popup.show = false; }
      };
    },
    async pay(id) {
      try {
        const url = @json(route('pesanan.bayar', ['id' => '__ID__'])).replace('__ID__', id);
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' }
        });
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.error || 'Gagal mengambil token pembayaran.');
        }
        const { snap_token } = await res.json();
        if (!snap_token || !window.snap) throw new Error('Token atau Snap.js tidak tersedia.');
        window.snap.pay(snap_token, {
          onSuccess: () => { location.reload(); },
          onPending: () => {},
          onError: (e) => { alert((e && e.status_message) ? e.status_message : 'Terjadi kesalahan saat pembayaran.'); },
          onClose: () => {}
        });
      } catch (e) {
        alert(e.message || 'Tidak bisa memulai pembayaran.');
      }
    }
  };
}
</script>
@endsection
