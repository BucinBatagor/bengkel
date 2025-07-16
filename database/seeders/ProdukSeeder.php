<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\ProdukGambar;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produks = [
            [
                'nama' => 'Pagar Minimalis Hitam',
                'kategori' => 'Pagar',
                'deskripsi' => 'Pagar besi warna hitam elegan untuk rumah modern.',
                'harga' => 950000,
                'gambar' => [
                    'assets/PagarMinimalis1.jpg',
                    'assets/PagarMinimalis2.jpg',
                    'assets/PagarMinimalis3.jpg',
                    'assets/PagarMinimalis4.jpg',
                    'assets/PagarMinimalis5.jpg',
                    'assets/PagarMinimalis6.jpg',
                    'assets/PagarMinimalis7.jpg',
                    'assets/PagarMinimalis8.jpg',
                    'assets/PagarMinimalis1.jpg',
                    'assets/PagarMinimalis2.jpg',
                ],
            ],
            // [
            //     'nama' => 'Pagar Motif Garis Putih',
            //     'kategori' => 'Dekorasi',
            //     'deskripsi' => 'Desain garis horizontal cocok untuk pagar rumah minimalis.',
            //     'harga' => 980000,
            //     'gambar' => ['assets/PagarMinimalis2.jpg', 'assets/PagarMinimalis3.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Besi Abu Elegan',
            //     'kategori' => 'Eksterior',
            //     'deskripsi' => 'Pagar abu dengan pola kotak-kotak minimalis.',
            //     'harga' => 1000000,
            //     'gambar' => ['assets/PagarMinimalis4.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Hitam Vertikal',
            //     'kategori' => 'Modern',
            //     'deskripsi' => 'Model pagar garis vertikal dengan struktur kuat.',
            //     'harga' => 920000,
            //     'gambar' => ['assets/PagarMinimalis5.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Baja Modern',
            //     'kategori' => 'Pagar',
            //     'deskripsi' => 'Menggunakan material baja ringan untuk daya tahan lebih lama.',
            //     'harga' => 1100000,
            //     'gambar' => ['assets/PagarMinimalis6.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Roster Putih',
            //     'kategori' => 'Dekorasi',
            //     'deskripsi' => 'Kombinasi pagar dan roster putih untuk sirkulasi udara.',
            //     'harga' => 1030000,
            //     'gambar' => ['assets/PagarMinimalis7.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Kombinasi Kayu & Besi',
            //     'kategori' => 'Eksterior',
            //     'deskripsi' => 'Desain artistik dengan kayu dan besi untuk tampilan natural.',
            //     'harga' => 1150000,
            //     'gambar' => ['assets/PagarMinimalis8.jpg'],
            // ],
            // [
            //     'nama' => 'Pagar Sliding Minimalis',
            //     'kategori' => 'Modern',
            //     'deskripsi' => 'Pagar dorong geser, cocok untuk carport dan garasi.',
            //     'harga' => 1200000,
            //     'gambar' => ['assets/PagarMinimalis9.jpg'],
            // ],
        ];

        foreach ($produks as $data) {
            $gambar = $data['gambar'];
            unset($data['gambar']);

            $produk = Produk::create($data);

            foreach ($gambar as $path) {
                ProdukGambar::create([
                    'produk_id' => $produk->id,
                    'gambar' => $path,
                ]);
            }
        }
    }
}
