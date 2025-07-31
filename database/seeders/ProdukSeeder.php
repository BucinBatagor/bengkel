<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produkData = [
            [
                'nama' => 'Meja Besi Minimalis',
                'kategori' => 'Furniture',
                'deskripsi' => 'Meja besi dengan desain minimalis dan kokoh.',
                'harga' => 1500000.00,
                'gambar' => ['meja1.jpg', 'meja2.jpg']
            ],
            [
                'nama' => 'Railing Tangga Stainless',
                'kategori' => 'Bangunan',
                'deskripsi' => 'Railing tangga berbahan stainless steel anti karat.',
                'harga' => 1200000.00,
                'gambar' => ['railing1.jpg', 'railing2.jpg']
            ],
            [
                'nama' => 'Pintu Besi Lipat',
                'kategori' => 'Bangunan',
                'deskripsi' => 'Pintu besi lipat yang tahan lama dan aman.',
                'harga' => 3000000.00,
                'gambar' => ['pintu1.jpg', 'pintu2.jpg']
            ],
        ];

        foreach ($produkData as $data) {
            $produk = Produk::create([
                'nama' => $data['nama'],
                'kategori' => $data['kategori'],
                'deskripsi' => $data['deskripsi'],
                'harga' => $data['harga'],
            ]);

            foreach ($data['gambar'] as $img) {
                $produk->gambar()->create([
                    'gambar' => $img,
                ]);
            }
        }
    }
}
