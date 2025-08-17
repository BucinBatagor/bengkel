<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategori = Kategori::orderByDesc('created_at')->paginate(10);
        return view('Admin.kelolaKategori', compact('kategori'));
    }

    public function create()
    {
        return view('Admin.tambahKategori');
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nama' => ['required', 'string', 'max:255', 'unique:kategori,nama'],
            ],
            [
                'nama.required' => 'Nama kategori wajib diisi.',
                'nama.string'   => 'Nama kategori tidak valid.',
                'nama.max'      => 'Nama kategori maksimal 255 karakter.',
                'nama.unique'   => 'Nama kategori sudah digunakan.',
            ]
        );

        Kategori::create([
            'nama' => $data['nama'],
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('Admin.editKategori', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $data = $request->validate(
            [
                'nama' => ['required', 'string', 'max:255', Rule::unique('kategori', 'nama')->ignore($kategori->id)],
            ],
            [
                'nama.required' => 'Nama kategori wajib diisi.',
                'nama.string'   => 'Nama kategori tidak valid.',
                'nama.max'      => 'Nama kategori maksimal 255 karakter.',
                'nama.unique'   => 'Nama kategori sudah digunakan.',
            ]
        );

        $kategori->fill([
            'nama' => $data['nama'],
        ]);

        if (!$kategori->isDirty()) {
            return redirect()
                ->route('admin.kategori.edit', $kategori->id)
                ->with('info', 'Tidak ada perubahan yang disimpan.');
        }

        $kategori->save();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
