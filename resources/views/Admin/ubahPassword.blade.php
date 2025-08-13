@extends('Template.admin')

@section('title', 'Ubah Password Admin')

@section('content')
<section class="flex flex-col items-center px-6 py-6 w-full">
  <div class="bg-white rounded-lg shadow px-6 py-6 w-full">
    <h1 class="text-2xl font-bold mb-6">Ubah Password Admin</h1>

    @if(session('success'))
      <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.profil.update-password') }}" class="space-y-4" novalidate>
      @csrf
      @method('PUT')

      <div>
        <label for="current_password" class="block font-semibold text-sm mb-1">Password Lama</label>
        <div class="relative">
          <input
            type="password"
            name="current_password"
            id="current_password"
            autocomplete="current-password"
            class="w-full border p-2 rounded bg-white pr-10"
            required
          >
          <button type="button" id="toggleCurrentPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
            <i class="fa-solid fa-eye-slash" id="eyeIcon1"></i>
          </button>
        </div>
        @error('current_password')
          <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="password" class="block font-semibold text-sm mb-1">Password Baru</label>
        <div class="relative">
          <input
            type="password"
            name="password"
            id="password"
            autocomplete="new-password"
            class="w-full border p-2 rounded bg-white pr-10"
            required
          >
          <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
            <i class="fa-solid fa-eye-slash" id="eyeIcon2"></i>
          </button>
        </div>
        @error('password')
          <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="password_confirmation" class="block font-semibold text-sm mb-1">Konfirmasi Password Baru</label>
        <div class="relative">
          <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            autocomplete="new-password"
            class="w-full border p-2 rounded bg-white pr-10"
            required
          >
          <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
            <i class="fa-solid fa-eye-slash" id="eyeIcon3"></i>
          </button>
        </div>
        @error('password_confirmation')
          <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="pt-2">
        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">
          Simpan Password Baru
        </button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function setupToggle(toggleId, inputId, iconId) {
    const toggle = document.getElementById(toggleId);
    const input  = document.getElementById(inputId);
    const icon   = document.getElementById(iconId);
    if (!toggle || !input || !icon) return;
    toggle.addEventListener('click', () => {
      const hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      icon.classList.toggle('fa-eye', hidden);
      icon.classList.toggle('fa-eye-slash', !hidden);
    });
  }
  setupToggle('toggleCurrentPassword', 'current_password', 'eyeIcon1');
  setupToggle('toggleNewPassword', 'password', 'eyeIcon2');
  setupToggle('toggleConfirmPassword', 'password_confirmation', 'eyeIcon3');
});
</script>
@endsection
