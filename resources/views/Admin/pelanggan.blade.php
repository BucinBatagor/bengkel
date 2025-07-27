@extends('Template.admin')

@section('title', 'Data Pelanggan')

@section('content')
<section class="min-h-[700px] flex flex-col items-center px-6 py-6">
    <div class="w-full max-w-screen-xl bg-white px-8 py-6 rounded-lg shadow flex-1">
        <h1 class="text-2xl font-bold mb-6">DATA PELANGGAN</h1>

        <form method="GET" action="{{ route('admin.pelanggan.index') }}" class="flex items-center gap-2 mb-4 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelanggan..."
                class="border px-3 py-2 rounded focus:outline-none focus:ring focus:border-black pr-8">

            @if(request('search'))
            <button type="button"
                onclick="window.location.href='{{ route('admin.pelanggan.index') }}'"
                class="absolute left-50 text-gray-400 hover:text-black text-lg px-1">
                &times;
            </button>
            @endif

            <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                Cari
            </button>
        </form>

        <div class="overflow-x-auto rounded">
            <table class="min-w-full border border-gray-300 text-sm text-left">
                <thead class="bg-black text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-5 py-3 border-r border-gray-400">#</th>

                        @php
                        $columns = ['name' => 'Nama', 'email' => 'Email', 'phone' => 'No HP', 'address' => 'Alamat'];
                        $currentSort = request('sort');
                        $currentOrder = request('order') === 'asc' ? 'asc' : 'desc';
                        @endphp

                        @foreach ($columns as $key => $label)
                        <th class="px-5 py-3 border-r border-gray-400">
                            <a href="{{ route('admin.pelanggan.index', array_merge(request()->all(), ['sort' => $key, 'order' => ($currentSort === $key && $currentOrder === 'asc') ? 'desc' : 'asc'])) }}"
                                class="flex items-center gap-1">
                                {{ $label }}
                                @if ($currentSort === $key)
                                <i class="fas {{ $currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse ($users as $i => $user)
                    <tr class="hover:bg-gray-100 border-b border-gray-300">
                        <td class="px-5 py-3 border-r border-gray-200">{{ $users->firstItem() + $i }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $user->name }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $user->email }}</td>
                        <td class="px-5 py-3 border-r border-gray-200">{{ $user->phone ?? '-' }}</td>
                        <td class="px-5 py-3">{{ $user->address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-10 text-base font-semibold">
                            Tidak ada data pelanggan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-center mt-10">
        <ul class="inline-flex items-center text-sm">
            {{-- Panah ke kiri --}}
            <div class="inline-flex space-x-1 mr-2">
                @if ($users->onFirstPage())
                <li><span class="px-3 py-2 border rounded text-gray-400">&laquo;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&lt;</span></li>
                @else
                <li>
                    <a href="{{ $users->appends(request()->except('page'))->url(1) }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&laquo;</a>
                </li>
                <li>
                    <a href="{{ $users->appends(request()->except('page'))->previousPageUrl() }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&lt;</a>
                </li>
                @endif
            </div>

            {{-- Nomor halaman --}}
            <div class="inline-flex space-x-1 mx-2">
                @php
                $current = $users->currentPage();
                $last = $users->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $start + 4);
                if ($end - $start < 4) {
                    $start=max(1, $end - 4);
                    }
                    @endphp

                    @for ($i=$start; $i <=$end; $i++)
                    <li>
                    <a href="{{ $users->appends(request()->except('page'))->url($i) }}"
                        class="px-3 py-2 border rounded {{ $i == $current ? 'bg-black text-white' : 'hover:bg-gray-200' }}">
                        {{ $i }}
                    </a>
                    </li>
                    @endfor
            </div>

            {{-- Panah ke kanan --}}
            <div class="inline-flex space-x-1 ml-2">
                @if ($users->hasMorePages())
                <li>
                    <a href="{{ $users->appends(request()->except('page'))->nextPageUrl() }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&gt;</a>
                </li>
                <li>
                    <a href="{{ $users->appends(request()->except('page'))->url($users->lastPage()) }}"
                        class="px-3 py-2 border rounded hover:bg-gray-200">&raquo;</a>
                </li>
                @else
                <li><span class="px-3 py-2 border rounded text-gray-400">&gt;</span></li>
                <li><span class="px-3 py-2 border rounded text-gray-400">&raquo;</span></li>
                @endif
            </div>
        </ul>
    </div>

</section>
@endsection