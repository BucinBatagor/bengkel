@extends('Template.authPelanggan')

@section('title', 'Daftar')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-white pt-24 pb-12">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Daftar Akun</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label class="block mb-1 font-medium">Nama</label>
                <input type="text" name="name" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">No. HP</label>
                <input type="text" name="phone" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">Alamat</label>
                <textarea name="address" rows="3" required
                          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"></textarea>
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">Password</label>
                <input type="password" name="password" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <button type="submit"
                    class="w-full bg-black text-white py-2 rounded hover:bg-gray-700">
                Daftar
            </button>
        </form>

        <p class="mt-4 text-center text-sm">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</section>
@endsection
