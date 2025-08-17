<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Laporan Pendapatan</title>
  <style>
    @page { size: A4 landscape; margin: 14mm 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; color: #111; line-height: 1.35; }
    h1, h2, h3 { margin: 0 0 8px 0; line-height: 1.35; }
    h1 { text-align: center; font-size: 18px; letter-spacing: .5px; }
    h2 { font-size: 15px; margin-top: 16px; }
    h3 { font-size: 13px; margin-top: 12px; margin-bottom: 6px; }
    .muted { color: #555; }
    .small { font-size: 11px; }

    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    thead { display: table-header-group; }
    tr, td, th { page-break-inside: avoid; }
    th, td {
      border: 1px solid #444;
      padding: 6px 6px;
      vertical-align: top;
      line-height: 1.35;
      word-break: normal;
      overflow-wrap: break-word;
      hyphens: none;
    }
    th { background: #f2f2f2; font-weight: bold; }
    tbody tr:nth-child(even) { background: #fafafa; }

    .col-idx  { width: 5%; text-align: center; white-space: nowrap; }
    .col-date { white-space: nowrap; }
    .col-cust { }
    .col-prod { }
    .col-num, .col-num-s, .col-keu { text-align: right; white-space: nowrap; }
    .wrap-anywhere { word-break: break-word; overflow-wrap: anywhere; }

    .summary { margin: 10px 0 14px; }
    .hr { height: 2px; background: #000; margin: 14px 0 8px; }
    .footer { margin-top: 16px; font-size: 11px; text-align: right; color: #333; }
    .break-before { page-break-before: always; }

    .subtable { width:100%; border-collapse:collapse; margin-top:6px; font-size:11px; }
    .subtable th, .subtable td { border:1px solid #bbb; padding:4px 6px; line-height:1.3; word-break: normal; overflow-wrap: break-word; }
    .subtable th { background:#f7f7f7; white-space: nowrap; }
    .subtable .col-cat        { width:auto; }
    .subtable .col-name       { width:50%; }
    .subtable .col-kuantitas  { width:auto; text-align:right; white-space: nowrap; }
    .subtable .col-price      { width:auto; text-align:right; white-space: nowrap; }
    .subtable .col-sub        { width:auto; text-align:right; white-space: nowrap; }

    .calcbox { margin-top:6px; border:1px solid #bbb; padding:6px 8px; font-size:11px; background:#fcfcfc; }
    .calcbox .line { margin:2px 0; }
    .calcbox strong { font-weight:bold; }
  </style>
</head>
<body>
  <h1>LAPORAN PENDAPATAN</h1>

  @php
    if (empty($ringkasan)) {
      $totalBesiAll = 0.0;
      $totalLainAll = 0.0;
      $totalJasaAll = 0.0;
      $grossAll     = 0.0;

      foreach ($pemesanan as $o) {
        $besi = 0.0; $lain = 0.0; $jasa = 0.0;
        foreach ($o->kebutuhan as $k) {
          $sub = isset($k->subtotal) ? (float) $k->subtotal : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));
          if ($k->kategori === 'bahan_besi')        $besi += $sub;
          elseif ($k->kategori === 'bahan_lainnya') $lain += $sub;
          elseif ($k->kategori === 'jasa')          $jasa += $sub;
        }
        $totalBesiAll += $besi;
        $totalLainAll += $lain;
        $totalJasaAll += $jasa;

        if (isset($o->total_harga) && is_numeric($o->total_harga)) {
          $grossAll += (float) $o->total_harga;
        } else {
          $kVal = isset($o->keuntungan) && is_numeric($o->keuntungan) ? (float) $o->keuntungan : 3.0;
          if ($kVal < 1) $kVal = 1.0;
          $grossAll += ($besi + $lain) * $kVal;
        }
      }

      $ringkasan = [
        'count'               => $pemesanan->count(),
        'gross'               => $grossAll,
        'total_bahan_besi'    => $totalBesiAll,
        'total_bahan_lainnya' => $totalLainAll,
        'total_jasa'          => $totalJasaAll,
        'net'                 => $grossAll - $totalBesiAll - $totalLainAll - $totalJasaAll,
      ];
    }
  @endphp

  <div class="summary">
    @if(!empty($start) && !empty($end))
      <div class="small muted">
        Periode:
        <strong>{{ \Carbon\Carbon::parse($start)->locale('id')->translatedFormat('d F Y') }}</strong>
        s/d
        <strong>{{ \Carbon\Carbon::parse($end)->locale('id')->translatedFormat('d F Y') }}</strong>
      </div>
    @endif
  </div>

  <div class="hr"></div>

  @php
    $groupedByYear = $pemesanan->groupBy(fn($item) => \Carbon\Carbon::parse($item->created_at)->format('Y'));
    $firstYear = true;
  @endphp

  @foreach ($groupedByYear as $year => $itemsInYear)
    <div class="{{ $firstYear ? '' : 'break-before' }}">
      @php
        $groupedByMonth = $itemsInYear->groupBy(fn($item) => \Carbon\Carbon::parse($item->created_at)->format('m'));
      @endphp

      @foreach ($groupedByMonth as $month => $itemsInMonth)
        @php
          $totalBesiBulan = 0.0;
          $totalLainBulan = 0.0;
          $totalJasaBulan = 0.0;
          $grossBulan     = 0.0;

          foreach ($itemsInMonth as $o) {
            $besi = 0.0; $lain = 0.0; $jasa = 0.0;
            foreach ($o->kebutuhan as $k) {
              $sub = isset($k->subtotal) ? (float) $k->subtotal : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));
              if ($k->kategori === 'bahan_besi')        $besi += $sub;
              elseif ($k->kategori === 'bahan_lainnya') $lain += $sub;
              elseif ($k->kategori === 'jasa')          $jasa += $sub;
            }
            $totalBesiBulan += $besi;
            $totalLainBulan += $lain;
            $totalJasaBulan += $jasa;

            if (isset($o->total_harga) && is_numeric($o->total_harga)) {
              $grossBulan += (float) $o->total_harga;
            } else {
              $kVal = isset($o->keuntungan) && is_numeric($o->keuntungan) ? (float) $o->keuntungan : 3.0;
              if ($kVal < 1) $kVal = 1.0;
              $grossBulan += ($besi + $lain) * $kVal;
            }
          }

          $netBulan = $grossBulan - $totalBesiBulan - $totalLainBulan - $totalJasaBulan;
        @endphp

        <h2>{{ \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->locale('id')->translatedFormat('F Y') }}</h2>

        <table>
          <thead>
            <tr>
              <th class="col-idx">#</th>
              <th class="col-date">Tanggal</th>
              <th class="col-cust">Pelanggan</th>
              <th class="col-prod">Produk</th>
              <th class="col-num">Bahan Besi</th>
              <th class="col-num">Bahan Lainnya</th>
              <th class="col-num-s">Jasa</th>
              <th class="col-keu">Keuntungan</th>
              <th class="col-num">Total Harga</th>
              <th class="col-num">Bersih</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($itemsInMonth as $index => $pesanan)
              @php
                $sumBesi = 0.0; $sumLain = 0.0; $sumJasa = 0.0;
                foreach ($pesanan->kebutuhan as $k) {
                  $sub = isset($k->subtotal) ? (float) $k->subtotal : ((float) ($k->kuantitas ?? 0) * (float) ($k->harga ?? 0));
                  if ($k->kategori === 'bahan_besi')        $sumBesi += $sub;
                  elseif ($k->kategori === 'bahan_lainnya') $sumLain += $sub;
                  elseif ($k->kategori === 'jasa')          $sumJasa += $sub;
                }

                $kUsed = isset($pesanan->keuntungan) && is_numeric($pesanan->keuntungan) ? (float) $pesanan->keuntungan : 3.0;
                if ($kUsed < 1) $kUsed = 1.0;
                $kDisp = rtrim(rtrim(number_format($kUsed, 2, ',', '.'), '0'), ',');

                $totalHarga = isset($pesanan->total_harga) && is_numeric($pesanan->total_harga)
                  ? (float) $pesanan->total_harga
                  : (($sumBesi + $sumLain) * $kUsed);

                $bersih = $totalHarga - $sumBesi - $sumLain - $sumJasa;
              @endphp
              <tr>
                <td class="col-idx">{{ $index + 1 }}</td>
                <td class="col-date">{{ \Carbon\Carbon::parse($pesanan->created_at)->locale('id')->translatedFormat('d/m/Y') }}</td>
                <td class="col-cust">{{ $pesanan->pelanggan->name ?? '-' }}</td>
                <td class="col-prod wrap-anywhere">
                  @foreach ($pesanan->detail as $detail)
                    <div>{{ $detail->nama_produk ?? $detail->produk?->nama ?? '-' }}</div>
                  @endforeach
                </td>
                <td class="col-num">Rp {{ number_format($sumBesi, 0, ',', '.') }}</td>
                <td class="col-num">Rp {{ number_format($sumLain, 0, ',', '.') }}</td>
                <td class="col-num-s">Rp {{ number_format($sumJasa, 0, ',', '.') }}</td>
                <td class="col-keu">×{{ $kDisp }}</td>
                <td class="col-num">Rp {{ number_format($totalHarga, 0, ',', '.') }}</td>
                <td class="col-num"><strong>Rp {{ number_format($bersih, 0, ',', '.') }}</strong></td>
              </tr>

              @if($pesanan->kebutuhan->count())
                <tr>
                  <td></td>
                  <td colspan="9" class="small">
                    <table class="subtable">
                      <thead>
                        <tr>
                          <th class="col-cat">Kategori</th>
                          <th class="col-name">Nama</th>
                          <th class="col-kuantitas">Kuantitas</th>
                          <th class="col-price">Harga</th>
                          <th class="col-sub">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($pesanan->kebutuhan as $k)
                          @php
                            $kuantitas = (float) ($k->kuantitas ?? 0);
                            $harga     = (float) ($k->harga ?? 0);
                            $subtotal  = isset($k->subtotal) ? (float) $k->subtotal : ($kuantitas * $harga);

                            $kuantitasDisp = $kuantitas > 0 ? rtrim(rtrim(number_format($kuantitas, 2, ',', '.'), '0'), ',') : '—';
                            $hargaDisp     = $harga > 0 ? 'Rp '.number_format($harga, 0, ',', '.') : '—';

                            $labelKat = $k->kategori === 'bahan_besi'
                              ? 'Bahan Besi'
                              : ($k->kategori === 'bahan_lainnya' ? 'Bahan Lainnya' : 'Jasa');
                          @endphp
                          <tr>
                            <td>{{ $labelKat }}</td>
                            <td class="wrap-anywhere">{{ $k->nama ?? 'Item' }}</td>
                            <td class="col-kuantitas">{{ $kuantitasDisp }}</td>
                            <td class="col-price">{{ $hargaDisp }}</td>
                            <td class="col-sub">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>

                    @php
                      $bahanTotal  = $sumBesi + $sumLain;
                      $grossHitung = $bahanTotal * $kUsed;
                    @endphp
                    <div class="calcbox">
                      <div class="line">
                        <strong>Total Harga:</strong>
                        (Bahan Besi <strong>Rp {{ number_format($sumBesi, 0, ',', '.') }}</strong>
                        + Bahan Lainnya <strong>Rp {{ number_format($sumLain, 0, ',', '.') }}</strong>)
                        × <strong>{{ $kDisp }}</strong>
                        = <strong>Rp {{ number_format($grossHitung, 0, ',', '.') }}</strong>
                      </div>
                      <div class="line">
                        <strong>Bersih:</strong>
                        Rp {{ number_format($totalHarga, 0, ',', '.') }}
                        − (Rp {{ number_format($sumBesi, 0, ',', '.') }}
                        + Rp {{ number_format($sumLain, 0, ',', '.') }}
                        + Rp {{ number_format($sumJasa, 0, ',', '.') }})
                        = <strong>Rp {{ number_format($bersih, 0, ',', '.') }}</strong>
                      </div>
                    </div>
                  </td>
                </tr>
              @endif
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="col-num"><strong>Total Bulan Ini</strong></td>
              <td class="col-num"><strong>Rp {{ number_format($totalBesiBulan, 0, ',', '.') }}</strong></td>
              <td class="col-num"><strong>Rp {{ number_format($totalLainBulan, 0, ',', '.') }}</strong></td>
              <td class="col-num-s"><strong>Rp {{ number_format($totalJasaBulan, 0, ',', '.') }}</strong></td>
              <td class="col-keu">—</td>
              <td class="col-num"><strong>Rp {{ number_format($grossBulan, 0, ',', '.') }}</strong></td>
              <td class="col-num"><strong>Rp {{ number_format($netBulan, 0, ',', '.') }}</strong></td>
            </tr>
          </tfoot>
        </table>
      @endforeach
    </div>
    @php $firstYear = false; @endphp
  @endforeach

  <div class="footer">
    Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y, H:i') }}
  </div>
</body>
</html>
