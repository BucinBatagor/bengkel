@extends('Template.authPelanggan')

@section('title', 'Login')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h2>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="next" value="{{ request('next') }}">

            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input
                    type="text"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
                >
                @error('email')
                    @if ($message !== 'Email atau password salah.')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @endif
                @enderror
            </div>

            <div class="relative">
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200"
                >
                <span
                    id="togglePassword"
                    class="absolute top-[33px] right-3 cursor-pointer text-gray-500"
                >
                    <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                </span>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                @if ($errors->has('email') && old('email') && $errors->first('email') === 'Email atau password salah.')
                    <p class="text-red-600 text-sm mt-1">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <div class="flex justify-between items-center text-sm">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="remember" class="form-checkbox">
                    <span>Ingat Saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Lupa Password?</a>
            </div>

            <button
                type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">
                Login
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Daftar</a>
        </p>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';

            if (isHidden) {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });
    });
</script>
@endsection
