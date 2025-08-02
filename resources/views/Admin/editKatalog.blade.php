@extends('Template.admin')

@section('title', 'Edit Produk')

@section('content')
<section class="w-full px-6 py-6">
    <div class="bg-white w-full px-6 py-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6">EDIT PRODUK</h1>

        @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.katalog.update', $produk->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block font-semibold mb-1">Nama Produk</label>
                    <input type="text" name="nama" value="{{ old('nama', $produk->nama) }}" required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Kategori</label>
                    <input type="text" name="kategori" value="{{ old('kategori', $produk->kategori) }}" required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Harga <span class="text-gray-600 text-sm">/ m²</span></label>
                    <input type="text" name="harga" id="harga" value="{{ number_format(old('harga', $produk->harga), 0, ',', '.') }}" required
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Tambah Gambar Baru</label>
                    <input type="file" name="gambar[]" multiple
                        class="w-full border px-4 py-2 rounded focus:outline-none focus:ring focus:border-black">
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                    Simpan
                </button>
            </div>
        </form>

        <div x-data="{ show: false, deleteUrl: '' }" class="mt-10">
            <label class="block font-semibold mb-2">Gambar Saat Ini</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($produk->gambar ?? [] as $gambar)
                @if (!empty($gambar) && isset($gambar->id))
                <div class="relative w-full h-32 rounded group overflow-visible">
                    <img src="{{ asset('storage/' . $gambar->gambar) }}" alt="Gambar"
                        class="w-full h-full object-cover border rounded shadow relative z-10">
                    <button type="button"
                        @click="show = true; deleteUrl = '{{ route('admin.katalog.gambar.hapus', $gambar->id) }}'"
                        class="absolute top-1 right-1 z-20 w-6 h-6 bg-red-600 text-white text-sm font-bold flex items-center justify-center rounded-full shadow hover:bg-red-700">
                        &times;
                    </button>
                </div>
                @endif
                @endforeach
            </div>

            <div x-show="show" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div x-transition.scale class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
                    <h2 class="text-lg font-bold mb-4">Konfirmasi Penghapusan</h2>
                    <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus gambar ini?</p>
                    <form :action="deleteUrl" method="POST" class="flex justify-center gap-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hapus</button>
                        <button type="button" @click="show = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    </form>
                </div>
            </div>
        </div>
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
