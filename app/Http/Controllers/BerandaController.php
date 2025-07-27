<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class BerandaController extends Controller
{
    public function index()
    {
        $produkSemua = Produk::with('gambar')->latest()->get();

        $kategoriUnikTerbaru = $produkSemua
            ->pluck('kategori')
            ->unique()
            ->take(3);

        $kategoriList = $kategoriUnikTerbaru->map(function ($kategori) use ($produkSemua) {
            $produk = $produkSemua->firstWhere('kategori', $kategori);

            return [
                'nama'        => ucfirst($kategori),
                'img'         => optional(value: $produk->gambar->first())->gambar ?? 'assets/default.jpg',
                'slug'        => $kategori,
                'id'          => $produk->id,
                'nama_produk' => $produk->nama,
                'harga'       => $produk->harga,
            ];
        });

        return view('pelanggan.beranda', compact('kategoriList'));
    }
}
