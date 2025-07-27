@extends('Template.pelanggan')

@section('title', 'Profil')

@section('content')
<div class="max-w-screen-xl mx-auto py-10 min-h-screen grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white shadow rounded-xl p-4 space-y-2">
            <a href="{{ route('profil.edit') }}"
               class="block px-3 py-2 rounded {{ request()->routeIs('profil.edit') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                👤 Profil
            </a>
            <a href="{{ route('profil.password') }}"
               class="block px-3 py-2 rounded {{ request()->routeIs('profil.password') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                🔒 Ganti Password
            </a>
        </div>
    </div>

    <div class="md:col-span-3">
        <div class="bg-white shadow-lg rounded-xl p-6">
            <h1 class="text-2xl font-bold mb-6">Profil</h1>

            @if(session('success'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="/profil" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block font-medium">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $pelanggan->name) }}"
                           class="w-full border p-2 rounded" required>
                </div>

                <div>
                    <label class="block font-medium">Email</label>
                    <input type="email" name="email" value="{{ old('email', $pelanggan->email) }}"
                           class="w-full border p-2 rounded" required>
                </div>

                <div>
                    <label class="block font-medium">No. HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $pelanggan->phone) }}"
                           class="w-full border p-2 rounded">
                </div>

                <div>
                    <label class="block font-medium">Alamat</label>
                    <textarea name="address" class="w-full border p-2 rounded">{{ old('address', $pelanggan->address) }}</textarea>
                </div>

                <button type="submit"
                        class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
