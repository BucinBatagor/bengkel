@extends('Template.pelanggan')

@section('title', 'Ubah Password')

@section('content')
<div class="max-w-screen-xl mx-auto py-10 min-h-screen grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white shadow rounded-xl p-4 space-y-2">
            <a href="{{ route('profil.edit') }}"
                class="block px-3 py-2 rounded {{ request()->routeIs('profil.edit') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                👤 Profil Saya
            </a>
            <a href="{{ route('profil.password') }}"
                class="block px-3 py-2 rounded {{ request()->routeIs('profil.password') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                🔒 Ganti Password
            </a>
        </div>
    </div>

    <div class="md:col-span-3">
        <div class="bg-white shadow-lg rounded-xl p-6">
            <h1 class="text-2xl font-bold mb-6">Ganti Password</h1>

            @if(session('success'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 text-red-600 font-medium">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profil.update-password') }}" method="POST" class="space-y-4" autocomplete="off">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium">Password Baru</label>
                    <input type="password" name="password" required
                        class="w-full border p-2 rounded" autocomplete="new-password">
                </div>

                <div>
                    <label class="block font-medium">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border p-2 rounded" autocomplete="new-password">
                </div>

                <button type="submit"
                    class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                    Ubah Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
