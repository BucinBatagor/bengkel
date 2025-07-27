<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\ProdukGambar;
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
            [
                'nama' => 'Rak Dinding Besi',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Rak besi dinding untuk pajangan rumah.',
                'harga' => 500000.00,
                'gambar' => ['rak1.jpg', 'rak2.jpg']
            ],
            [
                'nama' => 'Kursi Industrial',
                'kategori' => 'Furniture',
                'deskripsi' => 'Kursi besi dengan gaya industrial.',
                'harga' => 750000.00,
                'gambar' => ['kursi1.jpg', 'kursi2.jpg']
            ],
            [
                'nama' => 'Kanopi Baja Ringan',
                'kategori' => 'Bangunan',
                'deskripsi' => 'Kanopi dari baja ringan, cocok untuk halaman.',
                'harga' => 2500000.00,
                'gambar' => ['kanopi1.jpg', 'kanopi2.jpg']
            ],
            [
                'nama' => 'Rak Sepatu Besi',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Rak sepatu dari besi dengan 3 susun.',
                'harga' => 400000.00,
                'gambar' => ['sepatu1.jpg', 'sepatu2.jpg']
            ],
            [
                'nama' => 'Tempat Sampah Besi',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Tempat sampah outdoor berbahan besi anti karat.',
                'harga' => 350000.00,
                'gambar' => ['sampah1.jpg', 'sampah2.jpg']
            ],
            [
                'nama' => 'Rangka Plafon Hollow',
                'kategori' => 'Bangunan',
                'deskripsi' => 'Rangka plafon dari hollow galvanis.',
                'harga' => 2200000.00,
                'gambar' => ['plafon1.jpg', 'plafon2.jpg']
            ],
            [
                'nama' => 'Rak Besi Serbaguna',
                'kategori' => 'Furniture',
                'deskripsi' => 'Rak serbaguna dari besi untuk dapur.',
                'harga' => 900000.00,
                'gambar' => ['serbaguna1.jpg', 'serbaguna2.jpg']
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
