<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Produk;
use App\Models\ProdukGambar;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with('gambar');

        if ($search = $request->input('search')) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        $sort = $request->get('sort');
        $order = $request->get('order') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, ['nama', 'kategori', 'harga'])) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest();
        }

        $produks = $query->paginate(10)->appends($request->query());

        return view('Admin.katalog', compact('produks'));
    }

    public function create()
    {
        return view('Admin.tambahKatalog');
    }

    public function store(Request $request)
    {
        $request->merge([
            'harga' => str_replace('.', '', $request->harga)
        ]);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric',
            'deskripsi' => 'nullable|string',
            'gambar.*' => 'nullable|image|max:2048',
        ]);

        $produk = Produk::create([
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'harga' => $validated['harga'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $path = $file->store('produk', 'public');
                ProdukGambar::create([
                    'produk_id' => $produk->id,
                    'gambar' => $path,
                ]);
            }
        }

        return redirect()
            ->route('admin.katalog.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);
        return view('Admin.editKatalog', compact('produk'));
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'harga' => str_replace('.', '', $request->harga)
        ]);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric',
            'deskripsi' => 'nullable|string',
            'gambar.*' => 'nullable|image|max:2048',
        ]);

        $produk = Produk::findOrFail($id);
        $produk->update($validated);

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $path = $file->store('produk', 'public');
                ProdukGambar::create([
                    'produk_id' => $produk->id,
                    'gambar' => $path,
                ]);
            }
        }

        return redirect()
            ->route('admin.katalog.edit', $produk->id)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function hapusGambar($id)
    {
        $gambar = ProdukGambar::findOrFail($id);
        Storage::disk('public')->delete($gambar->gambar);
        $gambar->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function destroy($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);

        foreach ($produk->gambar as $gambar) {
            Storage::disk('public')->delete($gambar->gambar);
        }

        $produk->gambar()->delete();
        $produk->delete();

        return redirect()
            ->route('admin.katalog.index')
            ->with('success', 'Produk dan semua gambar terkait berhasil dihapus.');
    }
}
