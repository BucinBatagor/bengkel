@extends('Template.authPelanggan')

@section('title', 'Lupa Password')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Lupa Password</h2>

        @if (session('status'))
        <div class="mb-4 text-green-600 text-sm">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 text-red-600 text-sm">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium">Alamat Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition"
            >
                Kirim Link Atur Ulang Password
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Kembali ke Login</a>
        </p>
    </div>
</section>
@endsection
