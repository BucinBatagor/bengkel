@extends('Template.admin')

@section('title', 'Kelola Kategori')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full" x-data="{ show: false, deleteUrl: '' }">
  <div class="bg-white rounded-lg shadow px-6 py-6 w-full max-w-screen-xl min-h-[600px]">
    <h1 class="text-2xl font-bold mb-6">KELOLA KATEGORI</h1>

    <div class="block md:hidden mb-6 space-y-4">
      <form method="GET" action="{{ route('admin.kategori.index') }}" class="flex w-full" novalidate>
        <div class="relative flex w-full">
          <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama kategori..."
            class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring focus:border-black pr-10"
          >
          @if(request('search'))
            <button
              type="button"
              onclick="window.location.href='{{ route('admin.kategori.index', array_merge(request()->except(['search','page']))) }}'"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black text-lg"
            >&times;</button>
          @endif
        </div>
        <button
          type="submit"
          class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800 border border-l-0 border-gray-300"
        >Cari</button>
      </form>

      <a
        href="{{ route('admin.kategori.create') }}"
        class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 shadow w-full text-center"
      >+ Tambah Kategori</a>
    </div>

    <div class="hidden md:flex md:items-center md:justify-between mb-6">
      <a
        href="{{ route('admin.kategori.create') }}"
        class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 shadow"
      >+ Tambah Kategori</a>

      <form method="GET" action="{{ route('admin.kategori.index') }}" class="flex" novalidate>
        <div class="relative flex w-[280px]">
          <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama kategori..."
            class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring focus:border-black pr-10"
          >
          @if(request('search'))
            <button
              type="button"
              onclick="window.location.href='{{ route('admin.kategori.index', array_merge(request()->except(['search','page']))) }}'"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black text-lg"
            >&times;</button>
          @endif
        </div>
        <button
          type="submit"
          class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800 border border-l-0 border-gray-300"
        >Cari</button>
      </form>
    </div>

    <div class="overflow-x-auto rounded">
      <table class="min-w-full border border-gray-300 text-sm text-left">
        <thead class="bg-black text-white uppercase text-xs tracking-wider">
          <tr>
            <th class="px-5 py-3 border-r">#</th>
            <th class="px-5 py-3 border-r">Nama Kategori</th>
            <th class="px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          @forelse ($kategori as $item)
            <tr class="hover:bg-gray-50 border-b border-gray-300">
              <td class="px-5 py-3 border-r">{{ $kategori->firstItem() + $loop->index }}</td>
              <td class="px-5 py-3 border-r font-medium">{{ $item->nama }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <a href="{{ route('admin.kategori.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                  <button
                    @click="show = true; deleteUrl = '{{ route('admin.kategori.destroy', $item->id) }}'"
                    class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-gray-500 py-10 font-semibold">Belum ada kategori.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-[90%] max-w-md text-center">
        <h2 class="text-lg font-bold mb-4">Konfirmasi Penghapusan</h2>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus kategori ini?</p>
        <form :action="deleteUrl" method="POST" class="flex justify-center gap-4">
          @csrf
          @method('DELETE')
          <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Hapus</button>
          <button type="button" @click="show = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
        </form>
      </div>
    </div>
  </div>

  <div class="w-full max-w-screen-xl mx-auto">
    <div class="flex justify-center mt-8">
      <ul class="flex flex-wrap items-center gap-1 text-sm">
        @if ($kategori->onFirstPage())
          <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
          <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
        @else
          <li><a href="{{ $kategori->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
          <li><a href="{{ $kategori->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
        @endif

        @php
          $current = $kategori->currentPage();
          $last = $kategori->lastPage();
          $start = max(1, $current - 2);
          $end = min($last, $start + 4);
          if ($end - $start < 4) { $start = max(1, $end - 4); }
        @endphp

        @for ($i = $start; $i <= $end; $i++)
          <li>
            <a href="{{ $kategori->url($i) }}"
               class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
              {{ $i }}
            </a>
          </li>
        @endfor

        @if ($kategori->hasMorePages())
          <li><a href="{{ $kategori->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
          <li><a href="{{ $kategori->url($kategori->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
        @else
          <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
          <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
        @endif
      </ul>
    </div>
  </div>
</section>
@endsection
