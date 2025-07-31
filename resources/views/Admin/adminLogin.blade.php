@extends('Template.authAdmin')

@section('title', 'Login')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login Admin</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
            </div>

            <div class="mb-6">
                <label for="password" class="block mb-1 font-medium">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
            </div>

            <button type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-800 transition-colors duration-200">
                Login
            </button>
        </form>
    </div>
</section>
@endsection
