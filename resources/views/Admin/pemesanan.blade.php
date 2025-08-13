@extends('Template.admin')

@section('title', 'Pesanan Masuk')

@section('content')
<section class="flex flex-col items-center px-6 py-6" x-data="ordersRealtime()" x-init="start()">
  <div class="w-full max-w-screen-xl bg-white px-6 sm:px-8 py-6 rounded-lg shadow flex-1 min-h-[600px]">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">PESANAN MASUK</h1>
      <div class="text-xs text-gray-500" x-text="statusText"></div>
    </div>

    @if(session('success'))
      <div class="mb-4 p-3 rounded bg-green-50 text-green-800 border border-green-200">
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="mb-4 p-3 rounded bg-red-50 text-red-800 border border-red-200">
        {{ session('error') }}
      </div>
    @endif

    <form method="GET" action="{{ route('admin.pemesanan.index') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 w-full">
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative w-full sm:max-w-md">
          <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama pelanggan..."
            class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8"
          >
          @if(request('search'))
            <button
              type="button"
              onclick="window.location.href='{{ route('admin.pemesanan.index', array_merge(request()->except(['search','page'])) ) }}'"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black text-lg"
            >&times;</button>
          @endif
        </div>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
          Cari
        </button>
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
        <select
          name="status"
          onchange="this.form.submit()"
          class="appearance-none w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black"
        >
          <option value="">Semua Status</option>
          @foreach($filterOptions as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-600">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M5.23 7.21a.75.75 0 011.06.02L10 11.085l3.71-3.855a.75.75 0 111.08 1.04l-4.25 4.418a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
              clip-rule="evenodd"
            />
          </svg>
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
      <table class="min-w-full border border-gray-300 text-sm text-left">
        <thead class="bg-black text-white uppercase text-xs tracking-wider">
          <tr>
            <th class="px-5 py-3 border-r">#</th>
            <th class="px-5 py-3 border-r">Nama Pelanggan</th>
            <th class="px-5 py-3 border-r">Alamat</th>
            <th class="px-5 py-3 border-r">Produk</th>
            <th class="px-5 py-3 border-r">Total Harga</th>
            <th class="px-5 py-3 border-r">Status</th>
            <th class="px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          @forelse ($pemesanan as $pesanan)
            @php
              $statusLabel = $labels[$pesanan->status] ?? ucfirst(str_replace('_',' ',$pesanan->status));
              $badge = match($pesanan->status) {
                'butuh_cek_ukuran' => 'bg-yellow-50 text-yellow-800',
                'belum_bayar' => 'bg-yellow-100 text-yellow-800',
                'di_proses' => 'bg-blue-100 text-blue-800',
                'dikerjakan' => 'bg-indigo-100 text-indigo-800',
                'selesai' => 'bg-green-100 text-green-800',
                'pengembalian_dana' => 'bg-orange-100 text-orange-800',
                'pengembalian_selesai' => 'bg-green-50 text-green-800',
                'batal','gagal' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-800',
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

              try {
                $hasKeuntunganCol = \Illuminate\Support\Facades\Schema::hasColumn($pesanan->getTable(), 'keuntungan');
              } catch (\Throwable $e) {
                $hasKeuntunganCol = false;
              }
            @endphp

            <tr
              class="hover:bg-gray-100 border-b border-gray-300"
              x-data="statusMenu({
                id: {{ $pesanan->id }},
                current: '{{ $pesanan->status }}',
                blocked: {{ $isBlocked ? 'true' : 'false' }},
                options: @js($options),
                labelMap: @js($labels)
              })"
            >
              <td class="px-5 py-3 border-r">{{ $pemesanan->firstItem() + $loop->index }}</td>
              <td class="px-5 py-3 border-r">{{ $pesanan->pelanggan->name ?? '-' }}</td>
              <td class="px-5 py-3 border-r">{{ $pesanan->pelanggan->address ?? '-' }}</td>
              <td class="px-5 py-3 border-r">
                @foreach ($pesanan->detail as $d)
                  <div class="mb-1">{{ $d->nama_produk ?? $d->produk->nama ?? '-' }}</div>
                @endforeach
              </td>

              <td class="px-5 py-3 border-r whitespace-nowrap">
                @if((float)$pesanan->total_harga > 0)
                  <div>Rp {{ number_format($pesanan->total_harga,0,',','.') }}</div>
                @else
                  <span title="Menunggu perincian kebutuhan">—</span>
                @endif
              </td>

              <td class="px-5 py-3 border-r">
                <div class="inline-block">
                  <button
                    x-ref="btn"
                    type="button"
                    @click.stop="toggle($event.currentTarget)"
                    :disabled="blocked || Object.keys(options).length === 0"
                    :aria-expanded="open ? 'true' : 'false'"
                    class="px-3 py-1 rounded {{ $badge }} disabled:opacity-60 disabled:cursor-not-allowed hover:ring-2 hover:ring-offset-2 hover:ring-gray-300 flex items-center gap-1"
                  >
                    <span x-text="labelMap[value] ?? value"></span>
                    <svg
                      x-show="!blocked && Object.keys(options).length"
                      class="w-4 h-4"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path d="M5.25 7.5L10 12.25 14.75 7.5H5.25z" />
                    </svg>
                  </button>
                </div>

                <form x-ref="form" method="POST" action="{{ route('admin.pemesanan.update_status', $pesanan->id) }}" class="hidden">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="status" x-model="value">
                </form>
              </td>

              <td class="px-5 py-3">
                @if($pesanan->status === 'butuh_cek_ukuran' || ((float)$pesanan->total_harga) <= 0)
                  <a
                    href="{{ route('admin.pemesanan.kebutuhan.edit', $pesanan->id) }}"
                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
                  >
                    Kebutuhan
                  </a>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-10 text-gray-500 font-semibold">
                Tidak ada data pemesanan.
              </td>
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
          if ($end - $start < 4) {
            $start = max(1, $end - 4);
          }
        @endphp

        @for ($i = $start; $i <= $end; $i++)
          <li>
            <a
              href="{{ $pemesanan->appends(request()->except('page'))->url($i) }}"
              class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}"
            >{{ $i }}</a>
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
        const res = await fetch(window.location.href, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          signal: this.ctrl.signal
        });

        if (res.status === 401 || res.redirected) {
          window.location.reload();
          return;
        }

        const html = await res.text();
        const doc  = new DOMParser().parseFromString(html, 'text/html');

        const newTable = doc.getElementById('ordersTableWrap');
        const tableWrap = document.getElementById('ordersTableWrap');
        if (newTable && tableWrap) {
          tableWrap.innerHTML = newTable.innerHTML;
          if (window.Alpine?.initTree) Alpine.initTree(tableWrap);
        }

        const newPag = doc.getElementById('paginationWrap');
        const pagWrap = document.getElementById('paginationWrap');
        if (newPag && pagWrap) {
          pagWrap.innerHTML = newPag.innerHTML;
          if (window.Alpine?.initTree) Alpine.initTree(pagWrap);
        }

        this.statusText = 'Terakhir sinkron: ' + new Date().toLocaleTimeString();
      } catch(e){
        if (e.name !== 'AbortError') this.statusText = 'Gagal sinkron, mencoba lagi...';
      }
    }
  }
}

function statusMenu({id, current, blocked=false, options={}, labelMap={}}){
  return {
    id,
    blocked,
    options,
    labelMap,
    open: false,
    value: current,
    pos: { top: 0, left: 0, width: 0 },
    toggle(btn){
      if(this.blocked || Object.keys(this.options).length === 0) return;
      if(this.open){ this.close(); return; }
      const r = btn.getBoundingClientRect();
      this.pos = { top: r.bottom + window.scrollY + 6, left: r.left + window.scrollX, width: r.width };
      this.open = true;
      this.bind();
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
        const btn = this.$refs.btn;
        if(!btn) return;
        const r = btn.getBoundingClientRect();
        this.pos = { top: r.bottom + window.scrollY + 6, left: r.left + window.scrollX, width: r.width };
        const menu = document.getElementById(this.menuId());
        if(menu){
          menu.style.top = this.pos.top+'px';
          menu.style.left = this.pos.left+'px';
          menu.style.minWidth = this.pos.width+'px';
        }
      };
      this._onDocClick = (e) => {
        const menu = document.getElementById(this.menuId());
        if (!menu || menu.contains(e.target)) return;
        const btn = this.$refs.btn;
        if (btn && btn.contains(e.target)) return;
        this.close();
      };

      const overlay = document.createElement('div');
      overlay.id = this.overlayId();
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.zIndex = '100';
      overlay.addEventListener('click', () => this.close(), { passive: true });

      const menu = document.createElement('div');
      menu.id = this.menuId();
      menu.style.position = 'fixed';
      menu.style.top = this.pos.top+'px';
      menu.style.left = this.pos.left+'px';
      menu.style.minWidth = this.pos.width+'px';
      menu.style.zIndex = '101';
      menu.className = 'bg-white border border-gray-200 rounded-lg shadow';

      const wrap = document.createElement('div');
      wrap.className = 'py-1';
      Object.entries(this.options).forEach(([val, label]) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'w-full text-left px-3 py-2 text-sm hover:bg-gray-100';
        btn.textContent = label;
        if (this.value === val) btn.classList.add('font-semibold');
        btn.addEventListener('click', (ev) => {
          ev.stopPropagation();
          if (val === this.value) { this.close(); return; }
          this.value = val;
          this.$nextTick(() => this.$refs.form.submit());
          this.close();
        });
        wrap.appendChild(btn);
      });
      menu.appendChild(wrap);

      document.body.appendChild(overlay);
      document.body.appendChild(menu);

      document.addEventListener('click', this._onDocClick, true);
      window.addEventListener('scroll', this._realign, true);
      window.addEventListener('resize', this._realign);
    }
  }
}
</script>
@endsection
