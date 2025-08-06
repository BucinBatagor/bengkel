@extends('Template.authAdmin')

@section('title', 'Atur Ulang Password Admin')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Atur Ulang Password Admin</h2>

        @if (session('status'))
            <div class="mb-4 bg-green-100 text-green-800 text-sm px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password Baru</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
                >
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
                >
                @error('password_confirmation')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">
                Simpan Password Baru
            </button>
        </form>
    </div>
</section>
@endsection
