<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $kategoris = Produk::select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $produks = Produk::when($request->kategori && $request->kategori !== 'Semua', function ($query) use ($request) {
                $query->where('kategori', $request->kategori);
            })
            ->when($request->q, function ($query) use ($request) {
                $query->where('nama', 'like', '%' . $request->q . '%');
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('pelanggan.katalog', compact('produks', 'kategoris'));
    }
}
