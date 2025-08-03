@extends('Template.admin')

@section('title', 'Tambah Produk')

@section('content')
<section class="w-full px-6 py-6">
    <div class="bg-white w-full px-6 py-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6">TAMBAH PRODUK</h1>

        <form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="nama" class="block font-semibold mb-1">Nama Produk</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama') }}"
                        required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black"
                    >
                </div>

                <div>
                    <label for="kategori" class="block font-semibold mb-1">Kategori</label>
                    <input
                        type="text"
                        name="kategori"
                        id="kategori"
                        value="{{ old('kategori') }}"
                        required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black"
                    >
                </div>

                <div>
                    <label for="deskripsi" class="block font-semibold mb-1">Deskripsi</label>
                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        rows="4"
                        required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black"
                    >{{ old('deskripsi') }}</textarea>
                </div>

                <div>
                    <label for="harga" class="block font-semibold mb-1">
                        Harga <span class="text-gray-600 text-sm">/ m²</span>
                    </label>
                    <input
                        type="text"
                        name="harga"
                        id="harga"
                        value="{{ old('harga') }}"
                        required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black"
                    >
                </div>

                <div>
                    <label for="gambar" class="block font-semibold mb-1">Gambar Produk (bisa lebih dari satu)</label>
                    <input
                        type="file"
                        name="gambar[]"
                        id="gambar"
                        multiple
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black"
                    >
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    const hargaInput = document.getElementById('harga');
    hargaInput.addEventListener('input', function () {
        let value = this.value.replace(/\./g, '').replace(/\D/g, '');
        this.value = value ? parseInt(value).toLocaleString('id-ID') : '';
    });
</script>
@endsection
