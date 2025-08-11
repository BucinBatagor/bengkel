{{-- resources/views/Admin/adminLogin.blade.php --}}
@extends('Template.authAdmin')

@section('title', 'Login')

@section('content')
<section class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Login Admin</h2>

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-4" novalidate>
      @csrf

      @php
        $credError = $errors->first('email') === 'Email atau password salah.' ? $errors->first('email') : null;
      @endphp

      {{-- Email --}}
      <div>
        <label for="email" class="block mb-1 font-medium">Email</label>
        <input
          type="email"
          name="email"
          id="email"
          value="{{ old('email') }}"
          autocomplete="username"
          autofocus
          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-black"
          required
        >
        @if(!$credError)
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        @endif
      </div>

      {{-- Password --}}
      <div class="relative">
        <label for="password" class="block mb-1 font-medium">Password</label>
        <input
          type="password"
          name="password"
          id="password"
          autocomplete="current-password"
          class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:border-black"
          required
        >
        <span class="absolute top-[38px] right-3 cursor-pointer text-gray-500" id="togglePassword" title="Lihat/Sembunyikan">
          <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
        </span>

        @error('password')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @else
          @if($credError)
            <p class="mt-1 text-sm text-red-600">{{ $credError }}</p>
          @endif
        @enderror
      </div>

      <div class="flex justify-between items-center text-sm">
        <label class="flex items-center space-x-2">
          <input type="checkbox" name="remember" class="form-checkbox">
          <span>Ingat Saya</span>
        </label>

        <a href="{{ route('admin.password.request') }}" class="text-blue-600 hover:underline">
          Lupa Password?
        </a>
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
    const hidden = passwordInput.type === 'password';
    passwordInput.type = hidden ? 'text' : 'password';
    eyeIcon.classList.toggle('fa-eye', hidden);
    eyeIcon.classList.toggle('fa-eye-slash', !hidden);
  });
});
</script>
@endsection
