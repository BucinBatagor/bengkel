<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Nota {{ $order->order_id }}</title>
  <style>
    @page { size: A5 portrait; margin: 12mm 10mm; }
    * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
    body { font-size: 12px; color: #111; margin: 0; line-height: 1.35; }
    .wrap { padding: 0; }

    .titlebar { margin: 0 0 6px; }
    .titlebar .line-top { font-weight:700; letter-spacing:.4px; }
    .titlebar .line-date { color:#555; margin-top:2px; }

    .infobar {
      display:flex; justify-content:space-between; align-items:center;
      gap:12px; margin:8px 0 10px; padding-bottom:6px; border-bottom:1px solid #e5e5e5;
      flex-wrap:wrap;
    }
    .infobar .label { color:#555; margin-right:6px; }
    .infobar .item { display:flex; align-items:center; gap:4px; min-width: 160px; }

    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #444; padding:6px 6px; vertical-align:top; text-align:left; }
    th { background:#f2f2f2; font-weight:700; }
    .subrow td { background:transparent; }

    .sum { margin-top:10px; }
    .sumrow { display:flex; align-items:flex-start; padding:3px 0; }
    .sumrow .label { flex:1; padding-right:8px; }
    .sumrow .val { min-width:160px; text-align:right; }

    .paynote { margin-top:8px; padding-top:8px; border-top:1px dashed #d7d7d7; }
    .paynote .text { font-weight:700; }
  </style>
</head>
<body>
  <div class="wrap">

    <div class="titlebar">
      <div class="line-top">NOTA NO. {{ $order->order_id }}</div>
      <div class="line-date">
        {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->translatedFormat('d M Y') }}
      </div>
    </div>

    @php
      $pelNama  = $pelanggan->name ?? $pelanggan->nama ?? '-';
      $pelPhone = $pelanggan->telepon ?? $pelanggan->phone ?? $pelanggan->no_hp ?? null;

      $fmt = fn($n) => 'Rp '.number_format((float)$n, 0, ',', '.');
      $fmtQty = function($q) {
        $q = (float)$q;
        $s = number_format($q, 2, ',', '.');
        $s = rtrim(rtrim($s, '0'), ',');
        return $s === '' ? '0' : $s;
      };
    @endphp

    <div class="infobar">
      <div class="item"><span class="label">Nama:</span><span>{{ $pelNama }}</span></div>
      <div class="item"><span class="label">No. HP:</span><span>{{ $pelPhone ?: '—' }}</span></div>
    </div>

    @php
      $needs = ($kebutuhan ?? collect())->sortBy(['produk_id','kategori','id']);
      $sumBesi = 0.0; $sumLain = 0.0;
      $totalJumlahKolom = 0.0;
    @endphp

    <table>
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th style="width:16%;">Kuantitas</th>
          <th style="width:20%;">Harga</th>
          <th style="width:20%;">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach(($order->detail ?? collect()) as $d)
          @php
            $prodName   = $d->nama_produk ?? $d->produk?->nama ?? 'Produk';
            $qtyProduk  = (int)($d->jumlah ?? 1);
            $prodId     = $d->produk_id ?? ($d->produk->id ?? null);
            $subNeeds   = $needs->where('produk_id', $prodId);
          @endphp
          <tr>
            <td>{{ $prodName }}</td>
            <td>{{ $qtyProduk }}</td>
            <td>—</td>
            <td>—</td>
          </tr>

          @foreach($subNeeds as $n)
            @if($n->kategori !== 'jasa')
              @php
                $ku = (float)($n->kuantitas ?? 0);
                $hr = (float)($n->harga ?? 0);
                $sb = isset($n->subtotal) ? (float)$n->subtotal : ($ku * $hr);

                if ($n->kategori === 'bahan_besi')        $sumBesi += $sb;
                elseif ($n->kategori === 'bahan_lainnya') $sumLain += $sb;

                $totalJumlahKolom += $sb;
              @endphp
              <tr class="subrow">
                <td>{{ $n->nama ?? '-' }}</td>
                <td>{{ $ku > 0 ? $fmtQty($ku) : '—' }}</td>
                <td>{{ $hr > 0 ? $fmt($hr) : '—' }}</td>
                <td>{{ $fmt($sb) }}</td>
              </tr>
            @endif
          @endforeach
        @endforeach

        @php $unlinked = $needs->whereNull('produk_id'); @endphp
        @if($unlinked->count() > 0)
          <tr>
            <td>Lainnya (Umum)</td>
            <td>—</td>
            <td>—</td>
            <td>—</td>
          </tr>
          @foreach($unlinked as $n)
            @if($n->kategori !== 'jasa')
              @php
                $ku = (float)($n->kuantitas ?? 0);
                $hr = (float)($n->harga ?? 0);
                $sb = isset($n->subtotal) ? (float)$n->subtotal : ($ku * $hr);

                if ($n->kategori === 'bahan_besi')        $sumBesi += $sb;
                elseif ($n->kategori === 'bahan_lainnya') $sumLain += $sb;

                $totalJumlahKolom += $sb;
              @endphp
              <tr class="subrow">
                <td>{{ $n->nama ?? '-' }}</td>
                <td>{{ $ku > 0 ? $fmtQty($ku) : '—' }}</td>
                <td>{{ $hr > 0 ? $fmt($hr) : '—' }}</td>
                <td>{{ $fmt($sb) }}</td>
              </tr>
            @endif
          @endforeach
        @endif
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">Total Jumlah</td>
          <td>{{ $fmt($totalJumlahKolom) }}</td>
        </tr>
      </tfoot>
    </table>

    @php
      $bahanTotal   = $sumBesi + $sumLain;
      $kVal         = is_numeric($order->keuntungan ?? null) ? (float)$order->keuntungan : 3.0;
      if ($kVal < 1) $kVal = 1.0;

      $jumlahRumus  = $bahanTotal * $kVal;
      $kDisp        = rtrim(rtrim(number_format($kVal, 2, ',', '.'), '0'), ',');

      $totalTagihan = is_numeric($total ?? null) ? (float)$total
                     : (is_numeric($order->total_harga ?? null) ? (float)$order->total_harga : $jumlahRumus);

      $uangMukaReq  = $dp   ?? $order->dp   ?? 0;
      $sisaReq      = $sisa ?? $order->sisa ?? null;

      $uangMuka     = (float)$uangMukaReq;
      $sisaTagihan  = is_numeric($sisaReq) ? max(0, (float)$sisaReq) : max(0, $totalTagihan - $uangMuka);
    @endphp

    <div class="sum">
      <div class="sumrow">
        <div class="label">({{ $fmt($sumBesi) }} + {{ $fmt($sumLain) }}) x {{ $kDisp }} = {{ $fmt($jumlahRumus) }}</div>
        <div class="val"></div>
      </div>
      <div class="sumrow">
        <div class="label">Total Harga = {{ $fmt($totalTagihan) }}</div>
        <div class="val"></div>
      </div>

      @if($uangMuka > 0 && $sisaTagihan > 0)
        <div class="sumrow">
          <div class="label">Uang Muka = {{ $fmt($uangMuka) }}</div>
          <div class="val"></div>
        </div>
        <div class="sumrow">
          <div class="label">Sisa = {{ $fmt($sisaTagihan) }}</div>
          <div class="val"></div>
        </div>
      @endif
    </div>

    @php
      $pembayaranText = null;
      if ($sisaTagihan <= 0) {
        $pembayaranText = 'Sudah dibayar penuh.';
      } elseif ($uangMuka > 0) {
        $pembayaranText = 'Bayar di muka: '.$fmt($uangMuka).'.';
      }
    @endphp

    @if($pembayaranText)
      <div class="paynote">
        <div class="text">{{ $pembayaranText }}</div>
      </div>
    @endif

  </div>
</body>
</html>
