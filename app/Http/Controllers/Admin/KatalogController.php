<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\ProdukGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with(['gambar']);

        if ($search = $request->input('search')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $sort = $request->get('sort');
        $order = $request->get('order') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'nama') {
            $query->orderBy('nama', $order);
        } elseif ($sort === 'kategori') {
            $query->orderBy('kategori', $order);
        } else {
            $query->latest();
        }

        $produks = $query->paginate(10)->appends($request->query());

        return view('Admin.katalog', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama')->get();
        return view('Admin.tambahKatalog', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nama'        => ['required', 'string', 'max:255'],
                'kategori_id' => ['required', 'exists:kategori,id'],
                'deskripsi'   => ['required', 'string'],
                'gambar'      => ['nullable', 'array'],
                'gambar.*'    => ['file', 'mimes:jpg,jpeg,png', 'max:2048'],
            ],
            [
                'nama.required'        => 'Nama produk wajib diisi.',
                'kategori_id.required' => 'Pilih kategori.',
                'kategori_id.exists'   => 'Kategori tidak ditemukan.',
                'deskripsi.required'   => 'Deskripsi produk harus diisi.',
                'gambar.*.mimes'       => 'Format gambar harus JPG, JPEG, atau PNG.',
                'gambar.*.max'         => 'Ukuran tiap gambar maksimal 2MB.',
            ]
        );

        DB::transaction(function () use ($validated, $request) {
            $kategoriNama = Kategori::whereKey($validated['kategori_id'])->value('nama');

            $produk = Produk::create([
                'nama'        => trim($validated['nama']),
                'kategori_id' => $validated['kategori_id'],
                'kategori'    => $kategoriNama,
                'deskripsi'   => trim($validated['deskripsi']),
            ]);

            foreach ((array) $request->file('gambar', []) as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('produk', 'public');
                    ProdukGambar::create([
                        'produk_id' => $produk->id,
                        'gambar'    => $path,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.katalog.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);
        $kategoris = Kategori::orderBy('nama')->get();

        return view('Admin.editKatalog', compact('produk', 'kategoris'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'nama'        => ['required', 'string', 'max:255'],
                'kategori_id' => ['required', 'exists:kategori,id'],
                'deskripsi'   => ['required', 'string'],
                'gambar'      => ['nullable', 'array'],
                'gambar.*'    => ['file', 'mimes:jpg,jpeg,png', 'max:2048'],
            ],
            [
                'nama.required'        => 'Nama produk wajib diisi.',
                'kategori_id.required' => 'Pilih kategori.',
                'kategori_id.exists'   => 'Kategori tidak ditemukan.',
                'deskripsi.required'   => 'Deskripsi produk harus diisi.',
                'gambar.*.mimes'       => 'Format gambar harus JPG, JPEG, atau PNG.',
                'gambar.*.max'         => 'Ukuran tiap gambar maksimal 2MB.',
            ]
        );

        $produk = Produk::findOrFail($id);
        $kategoriNamaBaru = Kategori::whereKey($validated['kategori_id'])->value('nama');

        $baru = [
            'nama'        => trim($validated['nama']),
            'kategori_id' => $validated['kategori_id'],
            'kategori'    => $kategoriNamaBaru,
            'deskripsi'   => trim($validated['deskripsi']),
        ];

        $produk->fill($baru);

        $hasAttrChange = $produk->isDirty(['nama', 'kategori_id', 'kategori', 'deskripsi']);

        $files = (array) $request->file('gambar', []);
        $hasNewImages = false;
        foreach ($files as $f) {
            if ($f && $f->isValid()) {
                $hasNewImages = true;
                break;
            }
        }

        if (!$hasAttrChange && !$hasNewImages) {
            return redirect()
                ->route('admin.katalog.edit', $produk->id)
                ->with('info', 'Tidak ada perubahan yang disimpan.');
        }

        DB::transaction(function () use ($produk, $files, $hasAttrChange) {
            if ($hasAttrChange) {
                $produk->save();
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('produk', 'public');
                    ProdukGambar::create([
                        'produk_id' => $produk->id,
                        'gambar'    => $path,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.katalog.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function hapusGambar(Produk $produk, ProdukGambar $gambar)
    {
        abort_if($gambar->produk_id !== $produk->id, 404);

        Storage::disk('public')->delete($gambar->gambar);
        $gambar->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function destroy($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);

        foreach ($produk->gambar as $g) {
            Storage::disk('public')->delete($g->gambar);
        }

        $produk->gambar()->delete();
        $produk->delete();

        return redirect()
            ->route('admin.katalog.index')
            ->with('success', 'Produk dan semua gambarnya berhasil dihapus.');
    }
}
