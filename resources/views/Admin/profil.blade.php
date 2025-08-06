@extends('Template.admin')

@section('title', 'Profil Admin')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full">
    <div class="bg-white rounded-lg shadow px-6 py-6 w-full">
        <h1 class="text-2xl font-bold mb-6">Profil Admin</h1>

        {{-- Pesan “tidak ada perubahan” --}}
        @if($errors->has('profil'))
            <div class="mb-4 text-red-700 bg-red-100 p-3 rounded">
                {{ $errors->first('profil') }}
            </div>
        @endif

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.profil.update') }}"
              novalidate
              class="space-y-4">
            @csrf

            {{-- Nama --}}
            <div>
                <label class="block font-semibold text-sm mb-1">Nama</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $admin->name) }}"
                    class="w-full border p-2 rounded bg-white"
                >
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email + Status Verifikasi --}}
            <div>
                <label class="block font-semibold text-sm mb-1 flex items-center gap-2">
                    Email
                    @if($admin->email_verified_at)
                        <span class="text-green-600 text-xs bg-green-100 px-2 py-0.5 rounded-full">
                            Terverifikasi
                        </span>
                    @else
                        <span class="text-red-600 text-xs bg-red-100 px-2 py-0.5 rounded-full">
                            Belum diverifikasi
                        </span>
                    @endif
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $admin->email) }}"
                    class="w-full border p-2 rounded bg-white"
                >
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
