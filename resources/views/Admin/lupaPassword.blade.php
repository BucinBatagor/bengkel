@extends('Template.authAdmin')

@section('title', 'Lupa Password Admin')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Lupa Password Admin</h2>

        @if (session('status'))
            <div class="mb-4 bg-green-100 text-green-800 text-sm px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">

            @csrf

            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
                >
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">
                Kirim Link Reset
            </button>
        </form>
    </div>
</section>
@endsection
