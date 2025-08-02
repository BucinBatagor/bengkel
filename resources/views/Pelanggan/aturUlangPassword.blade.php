@extends('Template.authPelanggan')

@section('title', 'Atur Ulang Password')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Atur Ulang Password</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="relative">
                <label for="password" class="block text-sm font-medium mb-1">Password Baru</label>
                <input type="password" name="password" id="password" required
                    class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200">
                <span class="absolute right-3 top-[33px] cursor-pointer text-gray-500" id="togglePassword">
                    <i class="fa-solid fa-eye-slash" id="eyeIcon1"></i>
                </span>
            </div>

            <div class="relative">
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200">
                <span class="absolute right-3 top-[33px] cursor-pointer text-gray-500" id="toggleConfirmPassword">
                    <i class="fa-solid fa-eye-slash" id="eyeIcon2"></i>
                </span>
            </div>

            <button type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">
                Simpan Password Baru
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Kembali ke Login</a>
        </p>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const eyeIcon1 = document.getElementById('eyeIcon1');
        const eyeIcon2 = document.getElementById('eyeIcon2');

        function toggleVisibility(input, icon) {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            if (isHidden) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        togglePassword.addEventListener('click', function () {
            toggleVisibility(passwordInput, eyeIcon1);
        });

        toggleConfirmPassword.addEventListener('click', function () {
            toggleVisibility(confirmPasswordInput, eyeIcon2);
        });
    });
</script>
@endsection
