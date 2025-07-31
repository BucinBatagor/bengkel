<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>LAPORAN PENDAPATAN</h2>

    @php
    $groupedByYear = $pemesanan->groupBy(function($item) {
        return \Carbon\Carbon::parse($item->created_at)->format('Y');
    });
    $totalKeseluruhan = $pemesanan->sum('total_harga');
    @endphp

    @foreach ($groupedByYear as $year => $itemsInYear)
        <h2>Tahun: {{ $year }}</h2>

        @php
        $groupedByMonth = $itemsInYear->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('m');
        });
        @endphp

        @foreach ($groupedByMonth as $month => $itemsInMonth)
            <h3>
                Bulan: {{ \Carbon\Carbon::createFromFormat('!m', $month)->translatedFormat('F') }}
            </h3>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 15%">Tanggal</th>
                        <th style="width: 25%">Nama Pelanggan</th>
                        <th style="width: 20%">Produk</th>
                        <th style="width: 20%">Total Harga</th>
                        <th style="width: 15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsInMonth as $index => $pesanan)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($pesanan->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $pesanan->pelanggan->name }}</td>
                            <td>
                                @foreach ($pesanan->details as $detail)
                                    <div>{{ $detail->nama_produk ?? $detail->produk?->nama ?? '-' }}</div>
                                @endforeach
                            </td>
                            <td class="text-right">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($pesanan->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total Pendapatan Bulan Ini</strong></td>
                        <td colspan="2" class="text-right">
                            <strong>Rp {{ number_format($itemsInMonth->sum('total_harga'), 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
    @endforeach

    <h2 style="margin-top: 30px; border-top: 2px solid #000; padding-top: 10px;">
        Total Pendapatan Keseluruhan: Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}
    </h2>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>
</html>
