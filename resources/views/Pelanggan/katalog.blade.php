@extends('Template.pelanggan')

@section('title', 'Katalog')

@section('content')
@php
    session(['last_catalog_url' => request()->fullUrl()]);
@endphp

<section class="py-10 bg-gray-200 min-h-screen">
    <div class="max-w-screen-xl mx-auto px-4 space-y-6">

        <!-- Kotak putih tanpa tinggi tetap & tanpa overflow internal -->
        <div class="bg-white rounded-lg shadow p-6 min-h-[550px]">
            <form method="GET" id="filterForm" class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div class="relative">
                    <select name="kategori" onchange="document.getElementById('filterForm').submit();" class="appearance-none bg-black text-white font-semibold py-2 px-4 pr-8 rounded leading-tight focus:outline-none h-[42px]">
                        <option value="Semua" {{ request('kategori') === 'Semua' ? 'selected' : '' }}>Semua</option>
                        @foreach ($kategori as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') === $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                            <path d="M5.516 7.548l4.484 4.484 4.484-4.484-1.032-1.032L10 9.968 6.548 6.516z"/>
                        </svg>
                    </div>
                </div>

                <div class="relative w-full max-w-md">
                    <input type="text" name="q" id="searchInput" value="{{ request('q') }}" placeholder="Cari nama produk..." class="w-full px-4 pr-16 py-2 bg-gray-100 border rounded-lg h-[42px] text-sm focus:outline-none">
                    @if (request('q'))
                        <button type="button" onclick="clearSearch()" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-black focus:outline-none">&times;</button>
                    @endif
                    <button type="submit" class="absolute right-0 top-0 bottom-0 bg-black text-white rounded-r-lg px-3 flex items-center justify-center hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Grid langsung, tanpa overflow-y-auto -->
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse ($produks as $produk)
                        <a href="{{ route('produk.show', $produk->id) }}?back={{ urlencode(request()->fullUrl()) }}" class="block border rounded-lg overflow-hidden shadow hover:shadow-lg transition bg-white">
                            <img
                                src="{{ $produk->gambar->first() ? asset('storage/' . $produk->gambar->first()->gambar) : asset('assets/default.jpg') }}"
                                onerror="this.onerror=null;this.src='{{ asset('assets/default.jpg') }}';"
                                alt="{{ $produk->nama }}"
                                class="w-full h-48 object-cover"
                            >
                            <div class="p-4">
                                <div class="space-y-1">
                                    <!-- Hapus 'truncate' agar tidak jadi "..." -->
                                    <h3 class="text-base font-semibold text-gray-800 leading-snug">{{ $produk->nama }}</h3>
                                    <p class="text-sm text-gray-500 leading-snug">{{ $produk->kategori }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full flex items-center justify-center text-gray-500 min-h-[300px]">
                            <p class="text-center">Tidak ada produk ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            <ul class="inline-flex items-center text-sm">
                <div class="inline-flex space-x-1 mr-2">
                    @if ($produks->onFirstPage())
                        <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                        <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                    @else
                        <li><a href="{{ $produks->appends(request()->except('page'))->url(1) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a></li>
                        <li><a href="{{ $produks->appends(request()->except('page'))->previousPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a></li>
                    @endif
                </div>

                <div class="inline-flex space-x-1 mx-2">
                    @php
                        $current = $produks->currentPage();
                        $last = $produks->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $start + 4);
                        if ($end - $start < 4) {
                            $start = max(1, $end - 4);
                        }
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        <li>
                            <a href="{{ $produks->appends(request()->except('page'))->url($i) }}" class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">{{ $i }}</a>
                        </li>
                    @endfor
                </div>

                <div class="inline-flex space-x-1 ml-2">
                    @if ($produks->hasMorePages())
                        <li><a href="{{ $produks->appends(request()->except('page'))->nextPageUrl() }}" class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a></li>
                        <li><a href="{{ $produks->appends(request()->except('page'))->url($produks->lastPage()) }}" class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a></li>
                    @else
                        <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                        <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                    @endif
                </div>
            </ul>
        </div>
    </div>
</section>

<script>
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterForm').submit();
}

(function () {
  const appBase = "{{ url('/') }}";
  const ref = document.referrer || "";
  if (ref.startsWith(appBase)) {
    try {
      const refUrl = new URL(ref);
      const fromBeranda = refUrl.pathname === "/beranda" || refUrl.pathname === "/";
      if (fromBeranda) {
        history.pushState({}, "", location.href);
      }
    } catch (_) {}
  }

  if ("scrollRestoration" in history) {
    history.scrollRestoration = "manual";
  }
  const key = "katalog:scroll:" + (location.search || "");
  window.addEventListener("pageshow", function () {
    const y = sessionStorage.getItem(key);
    if (y !== null) window.scrollTo(0, parseInt(y, 10) || 0);
  });
  window.addEventListener("pagehide", function () {
    const y = window.scrollY || document.documentElement.scrollTop || 0;
    sessionStorage.setItem(key, String(y));
  });
})();
</script>
@endsection
