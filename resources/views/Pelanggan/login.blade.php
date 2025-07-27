@extends('Template.authPelanggan')

@section('title', 'Login')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="next" value="{{ request('next') }}">

            <div class="mb-4">
                <label class="block mb-1 font-medium">Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-medium">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
            </div>

            <button
                type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-700">
                Login
            </button>
        </form>

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="mt-4 text-center text-sm">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Daftar</a>
        </p>
    </div>
</section>
@endsection
