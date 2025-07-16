<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::latest()->paginate(12);
        return view('Admin.Produk.index', compact('produks'));
    }

    public function create()
    {
        return view('Admin.Produk.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'kategori' => 'required',
            'harga' => 'required|numeric',
            'gambar' => 'required|image',
            'deskripsi' => 'nullable',
        ]);

        $data['gambar'] = $request->file('gambar')->store('produk');

        Produk::create($data);
        return redirect()->route('admin.produk.index')->with('success', 'Produk ditambahkan');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
