@extends('Template.pelanggan')

@section('title', 'Ubah Password')

@section('content')
<section class="py-10 min-h-screen bg-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-6">

        <!-- Sidebar -->
        <aside class="md:col-span-1">
            <div class="bg-white shadow rounded-xl p-4 space-y-2">
                <a href="{{ route('profil.edit') }}"
                    class="block px-3 py-2 rounded font-medium transition
                           {{ request()->routeIs('profil.edit') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                    <i class="fas fa-user mr-2"></i> Profil Saya
                </a>
                <a href="{{ route('profil.password') }}"
                    class="block px-3 py-2 rounded font-medium transition
                           {{ request()->routeIs('profil.password') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                    <i class="fas fa-lock mr-2"></i> Ganti Password
                </a>
            </div>
        </aside>

        <!-- Form Ubah Password -->
        <div class="md:col-span-3">
            <div class="bg-white shadow-lg rounded-xl p-6">
                <h1 class="text-2xl font-bold mb-6">Ganti Password</h1>

                @if(session('success'))
                <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-4 text-red-600 font-medium">
                    @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif


                <form action="{{ route('profil.update-password') }}" method="POST" class="space-y-4" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="relative">
                        <label class="block font-medium text-sm mb-1">Password Baru</label>
                        <input type="password" name="password" id="password"
                            class="w-full border p-2 rounded pr-10 bg-white" autocomplete="new-password" required>
                        <span class="absolute right-3 top-[38px] cursor-pointer text-gray-500" id="togglePassword">
                            <i class="fa-solid fa-eye-slash" id="eyeIcon1"></i>
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block font-medium text-sm mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full border p-2 rounded pr-10 bg-white" autocomplete="new-password" required>
                        <span class="absolute right-3 top-[38px] cursor-pointer text-gray-500" id="toggleConfirmPassword">
                            <i class="fa-solid fa-eye-slash" id="eyeIcon2"></i>
                        </span>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
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

        togglePassword.addEventListener('click', function() {
            toggleVisibility(passwordInput, eyeIcon1);
        });

        toggleConfirmPassword.addEventListener('click', function() {
            toggleVisibility(confirmPasswordInput, eyeIcon2);
        });
    });
</script>
@endsection