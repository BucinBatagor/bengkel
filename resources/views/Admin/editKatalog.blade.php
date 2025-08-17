@extends('Template.admin')

@section('title', 'Edit Produk')

@section('content')
<section class="w-full px-6 py-6">
  <div class="bg-white rounded-lg shadow px-6 py-6 w-full max-w-screen-xl mx-auto">
    <div class="mb-4">
      <a href="{{ route('admin.katalog.index') }}"
        class="inline-flex items-center text-sm font-medium text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded hover:bg-gray-100 transition">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
      </a>
    </div>

    <h1 class="text-2xl font-bold mb-3">EDIT PRODUK</h1>

    @if (session('info'))
      <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        {{ session('info') }}
      </div>
    @endif

    @if(session('success'))
      <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
      </div>
    @endif

    <form action="{{ route('admin.katalog.update', $produk->id) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6"
          novalidate>
      @csrf
      @method('PUT')

      <div>
        <label for="nama" class="block font-medium mb-2">Nama Produk</label>
        <input
          type="text"
          id="nama"
          name="nama"
          value="{{ old('nama', $produk->nama) }}"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('nama') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
          placeholder="Masukkan nama produk...">
        @error('nama')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="kategori_id" class="block font-medium mb-2">Kategori</label>
        <select
          id="kategori_id"
          name="kategori_id"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('kategori_id') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}">
          <option value="" disabled {{ old('kategori_id', $produk->kategori_id) ? '' : 'selected' }}>-- Pilih Kategori --</option>
          @foreach($kategoris as $kat)
            <option value="{{ $kat->id }}" @selected(old('kategori_id', $produk->kategori_id) == $kat->id)>
              {{ $kat->nama }}
            </option>
          @endforeach
        </select>
        @error('kategori_id')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="deskripsi" class="block font-medium mb-2">Deskripsi</label>
        <textarea
          id="deskripsi"
          name="deskripsi"
          rows="4"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('deskripsi') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
          placeholder="Tulis deskripsi produk...">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
        @error('deskripsi')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block font-medium mb-2">Tambah Gambar Baru</label>

        <div class="border-2 border-dashed rounded-lg px-4 py-5 hover:border-black transition">
          <label for="gambar" class="block text-sm text-gray-700 mb-3">
            Pilih file gambar (bisa lebih dari satu)
          </label>

          <input
            type="file"
            id="gambar"
            name="gambar[]"
            multiple
            accept="image/jpeg,image/png,.jpg,.jpeg,.png"
            class="block w-full text-sm text-gray-700
                   file:mr-4 file:py-2 file:px-3
                   file:rounded file:border-0
                   file:bg-black file:text-white
                   file:hover:opacity-90 file:cursor-pointer">
        </div>

        @if($errors->has('gambar.*'))
          <p class="mt-2 text-sm text-red-600">{{ $errors->first('gambar.*') }}</p>
        @endif

        <p class="mt-2 text-xs text-gray-500">
          Maks 2MB per gambar.<br>Tipe yang didukung: JPG, JPEG, PNG.
        </p>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
          Simpan Perubahan
        </button>
      </div>
    </form>

    <div x-data="{ show: false, deleteUrl: '' }" class="mt-10">
      <label class="block font-semibold mb-2">Gambar Saat Ini</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @forelse($produk->gambar as $gambar)
          <div class="relative w-full h-32 rounded overflow-hidden">
            <img
              src="{{ asset('storage/' . $gambar->gambar) }}"
              alt="Gambar {{ $produk->nama }}"
              class="w-full h-full object-cover border rounded shadow">
            <button
              @click="show = true; deleteUrl = '{{ route('admin.katalog.gambar.hapus', ['produk' => $produk->id, 'gambar' => $gambar->id]) }}'"
              class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700"
              title="Hapus gambar">&times;</button>
          </div>
        @empty
          <p class="text-sm text-gray-500">Belum ada gambar untuk produk ini.</p>
        @endforelse
      </div>

      <div x-show="show" x-cloak x-transition.opacity class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
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
@endsection
