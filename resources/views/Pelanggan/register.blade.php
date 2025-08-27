@extends('Template.authPelanggan')

@section('title', 'Daftar')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 py-12">
  <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Daftar</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block mb-1 font-medium">Nama</label>
        <input
          type="text"
          name="name"
          value="{{ old('name') }}"
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200 @error('name') border-red-500 @enderror">
        @error('name')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block mb-1 font-medium">Email</label>
        <input
          type="email"
          name="email"
          value="{{ old('email') }}"
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200 @error('email') border-red-500 @enderror">
        @error('email')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block mb-1 font-medium">No. HP</label>
        <div class="flex rounded border overflow-hidden focus-within:ring focus-within:ring-blue-200 @error('phone') border-red-500 @enderror">
          <span class="bg-gray-200 px-3 py-2 text-gray-700 text-sm flex items-center select-none">+62</span>
          <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone') }}"
            maxlength="15"
            inputmode="numeric"
            pattern="[0-9]*"
            placeholder="Masukkan nomor WhatsApp aktif Anda"
            class="w-full px-3 py-2 focus:outline-none">
        </div>
        @error('phone')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block mb-1 font-medium">Alamat</label>
        <textarea
          name="address"
          rows="3"
          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
        @error('address')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="relative">
        <label class="block mb-1 font-medium">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200 @error('password') border-red-500 @enderror">
        <span class="absolute right-3 top-[38px] cursor-pointer text-gray-500" id="togglePassword">
          <i class="fa-solid fa-eye-slash" id="eyeIcon1"></i>
        </span>
        @error('password')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="relative">
        <label class="block mb-1 font-medium">Konfirmasi Password</label>
        <input
          type="password"
          id="password_confirmation"
          name="password_confirmation"
          class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200 @error('password_confirmation') border-red-500 @enderror">
        <span class="absolute right-3 top-[38px] cursor-pointer text-gray-500" id="toggleConfirmPassword">
          <i class="fa-solid fa-eye-slash" id="eyeIcon2"></i>
        </span>
        @error('password_confirmation')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">Daftar</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      Sudah punya akun?
      <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
    </p>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const phoneInput = document.getElementById('phone');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('password_confirmation');
  const togglePassword = document.getElementById('togglePassword');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  const eyeIcon1 = document.getElementById('eyeIcon1');
  const eyeIcon2 = document.getElementById('eyeIcon2');

  phoneInput.addEventListener('keypress', function (e) {
    const char = String.fromCharCode(e.which);
    if (!/[0-9]/.test(char)) e.preventDefault();
  });

  phoneInput.addEventListener('input', function () {
    if (this.value.startsWith('0')) this.value = this.value.substring(1);
  });

  function setupToggle(input, icon) {
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.classList.toggle('fa-eye-slash', !isHidden);
    icon.classList.toggle('fa-eye', isHidden);
  }

  togglePassword.addEventListener('click', function () {
    setupToggle(passwordInput, eyeIcon1);
  });

  toggleConfirmPassword.addEventListener('click', function () {
    setupToggle(confirmPasswordInput, eyeIcon2);
  });
});
</script>
@endsection
