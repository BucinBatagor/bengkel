@extends('Template.authAdmin')

@section('title', 'Atur Ulang Password')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 bg-gray-100">
  <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Atur Ulang Password</h2>

    @if(session('status'))
      <div class="mb-4 bg-green-100 text-green-800 text-sm px-4 py-3 rounded">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4" novalidate>
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      @error('token')
        <p class="text-red-600 text-sm -mt-2">{{ $message }}</p>
      @enderror
      @error('email')
        <p class="text-red-600 text-sm -mt-2">{{ $message }}</p>
      @enderror

      <div class="relative">
        <label for="password" class="block text-sm font-medium mb-1">Password Baru</label>
        <input
          type="password"
          name="password"
          id="password"
          required
          autocomplete="new-password"
          class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200 @error('password') border-red-500 @enderror"
        >
        <button type="button" class="absolute top-[34px] right-3 text-gray-500" id="togglePassword" aria-label="Lihat/Sembunyikan password">
          <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
        </button>
        @error('password')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="relative">
        <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
        <input
          type="password"
          name="password_confirmation"
          id="password_confirmation"
          required
          autocomplete="new-password"
          class="w-full border rounded px-3 py-2 pr-10 focus:outline-none focus:ring focus:ring-blue-200 @error('password_confirmation') border-red-500 @enderror"
        >
        <button type="button" class="absolute top-[34px] right-3 text-gray-500" id="togglePassword2" aria-label="Lihat/Sembunyikan konfirmasi password">
          <i class="fa-solid fa-eye-slash" id="eyeIcon2"></i>
        </button>
        @error('password_confirmation')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-700 transition">
        Simpan
      </button>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function bindToggle(inputId, btnId, iconId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    const icon = document.getElementById(iconId);
    btn.addEventListener('click', function () {
      const hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      icon.classList.toggle('fa-eye', hidden);
      icon.classList.toggle('fa-eye-slash', !hidden);
    });
  }
  bindToggle('password', 'togglePassword', 'eyeIcon');
  bindToggle('password_confirmation', 'togglePassword2', 'eyeIcon2');
});
</script>
@endsection
