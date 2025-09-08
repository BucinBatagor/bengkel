@extends('Template.pelanggan')

@section('title', 'Riwayat Pesanan')

@section('content')
<section class="bg-gray-200 py-12 min-h-screen" x-data="pesananApp()">
  <style>[x-cloak]{display:none !important}</style>

  <div class="max-w-screen-xl mx-auto px-4">
    <div class="bg-white rounded-lg shadow p-6 min-h-[550px] px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold mb-6 text-left">Riwayat Pesanan</h1>

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
              $totalQty   = $pesanan->detail->sum(fn($d) => (int)($d->jumlah ?? 1));
              $sisaInt    = (int) ceil((float) $pesanan->sisa);
              $dpSudah    = (float) $pesanan->dp > 0;
              $hasAction  = in_array($pesanan->status, ['butuh_cek_ukuran','belum_bayar','di_proses','dikerjakan']);
              $totalFloat = (float) $pesanan->total_harga;
              $dpFloat    = (float) $pesanan->dp;
              $dp2Amount  = max(0, $totalFloat - $dpFloat);
            @endphp

            <div class="border rounded-xl p-4 sm:p-6 bg-white shadow">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3 flex-wrap">
                  <span class="inline-flex items-center gap-2 text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-800">
                    <i class="fas fa-receipt"></i>
                    <span class="font-semibold">Order ID:</span>
                    <span class="font-mono">{{ $pesanan->order_id }}</span>
                  </span>
                  <span class="text-gray-600 font-medium">{{ $pesanan->created_at->format('d M Y') }}</span>
                </div>
                <span class="text-sm px-3 py-1 rounded-full {{ $statusColor }}">{{ $statusText }}</span>
              </div>

              <div class="my-4 h-px w-full bg-gray-200"></div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 space-y-4">
                  @foreach ($pesanan->detail as $detail)
                    @php $gambar = $detail->produk?->gambar->first()?->gambar; @endphp
                    <div class="flex items-start gap-4 border rounded-lg p-3">
                      <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                        @if ($gambar)
                          <img src="{{ asset('storage/'.$gambar) }}" alt="Produk" class="w-full h-full object-cover">
                        @else
                          <div class="flex items-center justify-center h-full text-gray-400 text-xs">No Gambar</div>
                        @endif
                      </div>
                      <div class="flex-1">
                        <p class="font-semibold text-gray-800 leading-tight">
                          {{ $detail->nama_produk }}
                          <span class="text-gray-500 font-normal">×{{ (int)($detail->jumlah ?? 1) }}</span>
                        </p>
                        <p class="text-gray-600 text-sm mt-0.5">{{ $detail->produk?->kategori ?? '-' }}</p>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="md:col-span-1">
                  <div class="rounded-xl border bg-gray-50 p-4 sm:p-5 flex flex-col {{ $hasAction ? 'min-h-[200px]' : '' }}">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Ringkasan Pesanan</h3>

                    @if ($pesanan->status === 'butuh_cek_ukuran')
                      <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                        Menunggu cek ukuran oleh admin.
                      </div>
                    @endif

                    <div class="space-y-3">
                      <div class="flex items-center justify-between">
                        <p class="text-gray-600 text-sm">Jumlah Pesanan</p>
                        <p class="text-base font-semibold">{{ $totalQty }}</p>
                      </div>
                      <div class="flex items-center justify-between">
                        <p class="text-gray-600 text-sm">Total Harga</p>
                        <p class="text-xl font-bold">Rp {{ number_format($pesanan->total_harga,0,',','.') }}</p>
                      </div>

                      @if ((float)$pesanan->total_harga > 0 && $sisaInt === 0)
                        <div class="flex items-center justify-between">
                          <p class="text-gray-600 text-sm">Pembayaran</p>
                          <p class="text-base font-semibold">Sudah bayar penuh</p>
                        </div>
                      @else
                        @if ($dpSudah)
                          <div class="flex items-center justify-between">
                            <p class="text-gray-600 text-sm">Uang Muka</p>
                            <p class="text-base font-semibold">Rp {{ number_format($pesanan->dp,0,',','.') }}</p>
                          </div>
                        @endif
                        @if ($dpSudah && $sisaInt > 0)
                          <div class="flex items-center justify-between">
                            <p class="text-gray-600 text-sm">Sisa</p>
                            <p class="text-base font-semibold">Rp {{ number_format($pesanan->sisa,0,',','.') }}</p>
                          </div>
                        @endif
                      @endif
                    </div>

                    @if ((float) $pesanan->total_harga > 0)
                      <div class="mt-3">
                        <a href="{{ route('pesanan.nota', $pesanan->id) }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-900 py-2.5 rounded-lg font-medium border border-gray-300">
                          Nota (PDF)
                        </a>
                      </div>
                    @endif

                    @if ($hasAction)
                      <div class="mt-4 space-y-2">
                        @if ($pesanan->status === 'butuh_cek_ukuran')
                          <form action="{{ route('pesanan.batal', $pesanan->id) }}" method="POST"
                                @submit.prevent="confirmAction('Batalkan Pesanan','Yakin ingin membatalkan pesanan ini?',() => $el.submit())">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg font-medium">
                              Batalkan Pesanan
                            </button>
                          </form>

                        @elseif ($pesanan->status === 'belum_bayar')
                          @if (!$dpSudah && $sisaInt > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                              <button @click="payFull('{{ $pesanan->id }}')"
                                      class="w-full inline-flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white py-2.5 rounded-lg font-medium border border-transparent">
                                Bayar Penuh
                              </button>
                              <button @click="openDp('{{ $pesanan->id }}', {{ $sisaInt }})"
                                      class="w-full inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-900 py-2.5 rounded-lg font-medium border border-gray-300">
                                Bayar Uang Muka
                              </button>
                            </div>
                          @elseif ($dpSudah && $sisaInt > 0)
                            <button @click="payPelunasan('{{ $pesanan->id }}', {{ $sisaInt }})"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white py-2.5 rounded-lg font-medium border border-transparent">
                              Bayar Pelunasan
                            </button>
                          @endif

                        @elseif (in_array($pesanan->status, ['di_proses','dikerjakan']))
                          @if ($pesanan->status === 'di_proses' && $sisaInt > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                              <button @click="payPelunasan('{{ $pesanan->id }}', {{ $sisaInt }})"
                                      class="w-full inline-flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white py-2.5 rounded-lg font-medium border border-transparent">
                                Bayar Pelunasan
                              </button>
                              <form action="{{ route('pesanan.ajukan_refund', $pesanan->id) }}" method="POST"
                                    @submit.prevent="confirmAction('Ajukan Refund','Ajukan pengembalian dana untuk pesanan ini?',() => $el.submit())">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-900 py-2.5 rounded-lg font-medium border border-gray-300">
                                  Ajukan Refund
                                </button>
                              </form>
                            </div>
                          @elseif ($pesanan->status === 'di_proses' && $sisaInt <= 0)
                            <form action="{{ route('pesanan.ajukan_refund', $pesanan->id) }}" method="POST"
                                  @submit.prevent="confirmAction('Ajukan Refund','Ajukan pengembalian dana untuk pesanan ini?',() => $el.submit())">
                              @csrf
                              <button type="submit"
                                      class="w-full inline-flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white py-2.5 rounded-lg font-medium border border-transparent">
                                Ajukan Refund
                              </button>
                            </form>
                          @elseif ($pesanan->status === 'dikerjakan' && $sisaInt > 0)
                            <button @click="payPelunasan('{{ $pesanan->id }}', {{ $sisaInt }})"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-black hover:bg-gray-900 text-white py-2.5 rounded-lg font-medium border border-transparent">
                              Bayar Pelunasan
                            </button>
                          @endif
                        @endif
                      </div>
                    @else
                      @if ($pesanan->status === 'pengembalian_dana')
                        <div class="text-sm text-gray-600 mt-3">Menunggu proses pengembalian dana.</div>
                      @endif
                    @endif
                  </div>
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

          @php
            $current = method_exists($pemesanan, 'currentPage') ? $pemesanan->currentPage() : 1;
            $last    = method_exists($pemesanan, 'lastPage') ? $pemesanan->lastPage() : 1;
            $start   = max(1, $current - 2);
            $end     = min($last, $start + 4);
            if ($end - $start < 4) { $start = max(1, $end - 4); }
          @endphp
          <div class="inline-flex space-x-1 mx-2">
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
    <div class="absolute inset-0 bg-black/50" x-transition.opacity
         @click="popup.type==='confirm' ? (popup.onCancel ? popup.onCancel() : popup.show=false) : (popup.show=false)"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6" x-transition.scale.origin.center role="dialog" aria-modal="true" :aria-label="popup.title || 'Dialog'">
      <h2 class="text-lg font-semibold mb-2" x-text="popup.title || 'Pemberitahuan'"></h2>
      <p class="text-sm text-gray-700 mb-6" x-text="popup.message || ''"></p>
      <div class="flex justify-end gap-2">
        <template x-if="popup.type === 'confirm'">
          <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100" @click="popup.onCancel ? popup.onCancel() : (popup.show = false)">Batal</button>
        </template>
        <button type="button" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-900"
                @click="popup.type === 'confirm' ? (popup.onConfirm ? popup.onConfirm() : null) : (popup.show = false)">
          <span x-text="popup.type === 'confirm' ? 'Ya' : 'Tutup'"></span>
        </button>
      </div>
    </div>
  </div>

  <div x-show="dpModal.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="dpModal.show=false">
    <div class="absolute inset-0 bg-black/50" x-transition.opacity @click="dpModal.show=false"></div>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-md p-6" x-transition.scale.origin.center>
      <h2 class="text-lg font-semibold mb-3">Nominal Uang Muka</h2>
      <div class="mb-4">
        <input type="text" inputmode="numeric" x-model="dpModal.amountStr" @input="dpModal.onInput()" @blur="dpModal.onBlur()" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring" placeholder="Contoh: 100.000">
        <template x-if="dpModal.error">
          <p class="text-xs text-red-600 mt-1" x-text="dpModal.error"></p>
        </template>
        <p class="text-xs text-gray-600 mt-1">Minimal uang muka <span class="font-medium">Rp 100.000</span></p>
        <p class="text-xs text-gray-600 mt-1">Sisa tagihan: <span x-text="formatRp(dpModal.sisa)"></span></p>
      </div>
      <div class="flex justify-end gap-2">
        <button class="px-4 py-2 border rounded hover:bg-gray-100" @click="dpModal.show=false">Batal</button>
        <button class="px-4 py-2 bg-black text-white rounded hover:bg-gray-900" @click="submitDp()" :disabled="dpModal.loading">
          <span x-show="!dpModal.loading">Bayar</span>
          <span x-show="dpModal.loading">Memproses…</span>
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

    dpModal: {
      show: false,
      id: null,
      sisa: 0,
      amount: 0,
      amountStr: '',
      error: '',
      loading: false,
      onInput(){
        const digits = String(this.amountStr || '').replace(/\D/g, '');
        this.amount = digits ? parseInt(digits, 10) : 0;
        this.amountStr = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.error = '';
      },
      onBlur(){
        this.onInput();
      }
    },

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

    info(title, message) {
      this.popup = { show: true, type: '', title, message, onConfirm: null, onCancel: null };
    },

    formatRp(n){ n = parseInt(n||0,10); return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },

    snapUrl(id){ return @json(route('pesanan.snap-token', ['id' => '__ID__'])).replace('__ID__', id); },

    async requestToken(id, payload){
      const res = await fetch(this.snapUrl(id), {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrf(), 'Accept':'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json().catch(() => ({}));
      if(!res.ok) throw new Error(data.message || 'Gagal membuat token pembayaran');
      if(!data.token) throw new Error('Token pembayaran tidak tersedia');
      return data.token;
    },

    async payFull(id){
      try{
        const token = await this.requestToken(id, { tipe: 'PELUNASAN' });
        window.snap.pay(token, {
          onSuccess: () => location.reload(),
          onPending: () => location.reload(),
          onError: () => this.info('Gagal Pembayaran', 'Terjadi kesalahan saat pembayaran.'),
          onClose: () => {}
        });
      }catch(e){ this.info('Gagal Memulai Pembayaran', e.message || 'Tidak bisa memulai pembayaran.'); }
    },

    async payPelunasan(id, sisa){
      try{
        if(parseInt(sisa||0,10) <= 0) { this.info('Tidak Bisa Membayar', 'Tagihan sudah lunas.'); return; }
        const token = await this.requestToken(id, { tipe: 'PELUNASAN' });
        window.snap.pay(token, {
          onSuccess: () => location.reload(),
          onPending: () => location.reload(),
          onError: () => this.info('Gagal Pembayaran', 'Terjadi kesalahan saat pembayaran.'),
          onClose: () => {}
        });
      }catch(e){ this.info('Gagal Memulai Pembayaran', e.message || 'Tidak bisa memulai pembayaran.'); }
    },

    openDp(id, sisa){
      this.dpModal.show = true;
      this.dpModal.id = id;
      this.dpModal.sisa = parseInt(sisa||0,10);
      this.dpModal.amount = 0;
      this.dpModal.amountStr = '';
      this.dpModal.error = '';
      this.dpModal.loading = false;
    },

    async submitDp(){
      try{
        this.dpModal.error = '';
        const amt = parseInt(this.dpModal.amount || 0, 10);
        if(isNaN(amt) || amt < 100000){ this.dpModal.error = 'Nominal uang muka minimal Rp 100.000'; return; }
        if(amt > this.dpModal.sisa){ this.dpModal.error = 'Nominal melebihi sisa tagihan'; return; }
        this.dpModal.loading = true;
        const token = await this.requestToken(this.dpModal.id, { tipe: 'DP', amount: amt });
        this.dpModal.show = false;
        window.snap.pay(token, {
          onSuccess: () => location.reload(),
          onPending: () => location.reload(),
          onError: () => this.info('Gagal Pembayaran', 'Terjadi kesalahan saat pembayaran.'),
          onClose: () => {}
        });
      }catch(e){
        this.dpModal.error = e.message || 'Gagal membuat token';
      }finally{
        this.dpModal.loading = false;
      }
    }
  };
}
</script>
@endsection
