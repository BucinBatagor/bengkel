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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
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
            @foreach ($pemesanan as $pesanan)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($pesanan->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $pesanan->pelanggan->name }}</td>
                    <td>{{ $pesanan->produk->nama }}</td>
                    <td class="text-right">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($pesanan->status) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total Pendapatan</strong></td>
                <td colspan="2" class="text-right">
                    <strong>Rp {{ number_format($pemesanan->sum('total_harga'), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>
</html>
