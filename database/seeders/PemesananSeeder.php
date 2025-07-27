<?php

namespace Database\Seeders;

use App\Models\Pemesanan;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class PemesananSeeder extends Seeder
{
    public function run(): void
    {
        $statusList = ['diproses', 'dikerjakan', 'selesai', 'dibatalkan']; // tanpa 'pending'

        $pesanan = [
            ['order_id' => 'ORD-0001', 'pelanggan_id' => 1, 'produk_id' => 1],
            ['order_id' => 'ORD-0002', 'pelanggan_id' => 2, 'produk_id' => 2],
            ['order_id' => 'ORD-0003', 'pelanggan_id' => 3, 'produk_id' => 3],
            ['order_id' => 'ORD-0004', 'pelanggan_id' => 4, 'produk_id' => 4],
            ['order_id' => 'ORD-0005', 'pelanggan_id' => 5, 'produk_id' => 5],
            ['order_id' => 'ORD-0006', 'pelanggan_id' => 6, 'produk_id' => 6],
            ['order_id' => 'ORD-0007', 'pelanggan_id' => 7, 'produk_id' => 7],
            ['order_id' => 'ORD-0008', 'pelanggan_id' => 8, 'produk_id' => 8],
            ['order_id' => 'ORD-0009', 'pelanggan_id' => 9, 'produk_id' => 9],
            ['order_id' => 'ORD-0010', 'pelanggan_id' => 10, 'produk_id' => 10],
        ];

        foreach ($pesanan as $i => $p) {
            $panjang = rand(100, 200);
            $lebar = rand(50, 150);
            $tinggi = rand(60, 180);
            $produk = Produk::find($p['produk_id']);
            $total = $produk->harga * ($panjang + $lebar + $tinggi) / 100;

            Pemesanan::create([
                'order_id' => $p['order_id'],
                'pelanggan_id' => $p['pelanggan_id'],
                'produk_id' => $p['produk_id'],
                'panjang' => $panjang,
                'lebar' => $lebar,
                'tinggi' => $tinggi,
                'total_harga' => $total,
                'status' => $statusList[$i % count($statusList)],
                'snap_token' => 'snap-token-' . $i,
                'midtrans_response' => null,
            ]);
        }
    }
}
