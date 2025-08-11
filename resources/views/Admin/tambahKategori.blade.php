@extends('Template.admin')

@section('title', 'Tambah Kategori')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full">
  <div class="bg-white rounded-lg shadow px-6 py-6 w-full max-w-screen-xl">
    <div class="mb-4">
      <a href="{{ route('admin.kategori.index') }}"
         class="inline-flex items-center text-sm font-medium text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded hover:bg-gray-100 transition">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
      </a>
    </div>

    <h1 class="text-2xl font-bold mb-6">Tambah Kategori</h1>

    <form action="{{ route('admin.kategori.store') }}" method="POST" class="space-y-6" novalidate>
      @csrf

      <div>
        <label for="nama" class="block mb-2 font-medium">Nama Kategori</label>
        <input
          type="text"
          id="nama"
          name="nama"
          value="{{ old('nama') }}"
          class="w-full rounded px-4 py-2 focus:outline-none focus:ring {{ $errors->has('nama') ? 'border border-red-500 focus:border-red-500 focus:ring-red-500' : 'border border-gray-300 focus:border-black' }}"
          placeholder="Masukkan nama kategori..."
        >
        @error('nama')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Simpan
        </button>
      </div>
    </form>
  </div>
</section>
@endsection
