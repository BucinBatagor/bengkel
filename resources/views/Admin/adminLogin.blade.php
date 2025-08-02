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

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-black">
            </div>

            <div class="relative">
                <label for="password" class="block mb-1 font-medium">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:border-black">
                <span class="absolute top-[38px] right-3 cursor-pointer text-gray-500" id="togglePassword">
                    <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                </span>
            </div>

            <div class="flex justify-between items-center text-sm">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="remember" class="form-checkbox">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit"
                class="w-full bg-black text-white py-2 rounded hover:bg-gray-800 transition">
                Login
            </button>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
