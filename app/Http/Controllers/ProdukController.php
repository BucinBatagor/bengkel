<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::with('gambars')->findOrFail($id);
        return view('user.produk', compact('produk'));
    }
}
