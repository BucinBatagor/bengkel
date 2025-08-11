{{-- resources/views/Admin/tambahKatalog.blade.php --}}
@extends('Template.admin')

@section('title', 'Tambah Katalog')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full">
  <div class="bg-white rounded-lg shadow px-6 py-6 w-full max-w-screen-xl">
    {{-- Tombol Kembali --}}
    <div class="mb-4">
      <a href="{{ route('admin.katalog.index') }}"
        class="inline-flex items-center text-sm font-medium text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded hover:bg-gray-100 transition">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
      </a>
    </div>

    <h1 class="text-2xl font-bold mb-6">Tambah Katalog</h1>

    <form action="{{ route('admin.katalog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
      @csrf

      {{-- Nama Produk --}}
      <div>
        <label for="nama" class="block mb-2 font-medium">Nama Produk</label>
        <input
          type="text"
          id="nama"
          name="nama"
          value="{{ old('nama') }}"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('nama') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
          placeholder="Masukkan nama produk..."
        >
        @error('nama')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- Kategori --}}
      <div>
        <label for="kategori_id" class="block mb-2 font-medium">Kategori</label>
        <select
          id="kategori_id"
          name="kategori_id"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('kategori_id') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
        >
          <option value="" disabled {{ old('kategori_id') ? '' : 'selected' }}>-- Pilih Kategori --</option>
          @foreach($kategoris as $kat)
            <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
              {{ $kat->nama }}
            </option>
          @endforeach
        </select>
        @error('kategori_id')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- Deskripsi (wajib) --}}
      <div>
        <label for="deskripsi" class="block mb-2 font-medium">Deskripsi</label>
        <textarea
          id="deskripsi"
          name="deskripsi"
          rows="4"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('deskripsi') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
          placeholder="Tulis deskripsi produk..."
        >{{ old('deskripsi') }}</textarea>
        @error('deskripsi')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      {{-- Gambar Produk (multi) --}}
      <div>
        <label class="block mb-2 font-medium">Gambar Produk</label>

        <div class="border-2 border-dashed rounded-lg px-4 py-5 hover:border-black transition">
          <label for="gambar" class="block text-sm text-gray-700 mb-3">
            Pilih file gambar (bisa lebih dari satu)
          </label>

          <input
            type="file"
            id="gambar"
            name="gambar[]"
            multiple
            accept="image/jpeg,image/png,.jpg,.jpeg,.png" {{-- hanya JPG/JPEG/PNG --}}
            class="block w-full text-sm text-gray-700
                   file:mr-4 file:py-2 file:px-3
                   file:rounded file:border-0
                   file:bg-black file:text-white
                   file:hover:opacity-90 file:cursor-pointer"
          >
        </div>

        {{-- Error untuk tiap file --}}
        @if($errors->has('gambar.*'))
          <p class="mt-2 text-sm text-red-600">{{ $errors->first('gambar.*') }}</p>
        @endif

        <p class="mt-2 text-xs text-gray-500">
          Maks 2MB per gambar.<br>Tipe yang didukung: JPG, JPEG, PNG.
        </p>
      </div>

      {{-- Aksi --}}
      <div class="flex justify-end">
        <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Simpan
        </button>
      </div>
    </form>
  </div>
</section>
@endsection
