@extends('Template.pelanggan')

@section('title', 'Profil')

@section('content')
<section class="py-10 min-h-screen bg-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-6">

        <!-- Sidebar Navigasi Profil -->
        <aside class="md:col-span-1">
            <div class="bg-white shadow rounded-xl p-4 space-y-2">
                <a href="{{ route('profil.edit') }}"
                    class="block px-3 py-2 rounded font-medium transition 
                          {{ request()->routeIs('profil.edit') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <a href="{{ route('profil.password') }}"
                    class="block px-3 py-2 rounded font-medium transition 
                          {{ request()->routeIs('profil.password') ? 'bg-black text-white' : 'hover:bg-gray-100' }}">
                    <i class="fas fa-lock mr-2"></i> Ganti Password
                </a>
            </div>
        </aside>

        <!-- Form Edit Profil -->
        <div class="md:col-span-3">
            <div class="bg-white shadow-lg rounded-xl p-6">
                <h1 class="text-2xl font-bold mb-6">Profil</h1>

                {{-- Pesan sukses atau tidak ada perubahan --}}
                @if(session('success'))
                @if(session('success') === 'Tidak ada perubahan pada profil Anda.')
                <div class="mb-4 text-red-700 bg-red-100 p-3 rounded">
                    {{ session('success') }}
                </div>
                @else
                <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
                    {{ session('success') }}
                </div>
                @endif
                @endif

                {{-- Menampilkan error validasi --}}
                @if ($errors->any())
                <div class="mb-4 text-red-700 bg-red-100 p-3 rounded">
                    @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form action="/profil" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-semibold text-sm mb-1">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $pelanggan->name) }}"
                            class="w-full border p-2 rounded bg-white" required>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $pelanggan->email) }}"
                            class="w-full border p-2 rounded bg-white" required>
                    </div>

                    <div>
    <label class="block font-semibold text-sm mb-1">No. HP</label>
    <div class="flex rounded border overflow-hidden focus-within:ring focus-within:ring-blue-200">
        <span class="bg-gray-200 px-3 py-2 text-gray-700 text-sm flex items-center select-none">+62</span>
        <input
            type="text"
            id="phone"
            name="phone"
            maxlength="15"
            inputmode="numeric"
            pattern="[0-9]*"
            value="{{ old('phone', ltrim($pelanggan->phone, '0')) }}"
            required
            class="w-full px-3 py-2 focus:outline-none bg-white">
    </div>
</div>



                    <div>
                        <label class="block font-semibold text-sm mb-1">Alamat</label>
                        <textarea name="address" rows="4"
                            class="w-full border p-2 rounded bg-white resize-none">{{ old('address', $pelanggan->address) }}</textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="bg-black text-white px-6 py-2 rounded hover:bg-gray-800 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const phoneInput = document.getElementById('phone');

    phoneInput.addEventListener('keypress', function (e) {
        const char = String.fromCharCode(e.which);
        if (!/[0-9]/.test(char)) e.preventDefault(); // cegah huruf
    });

    phoneInput.addEventListener('input', function () {
        if (this.value.startsWith('0')) {
            this.value = this.value.substring(1); // hapus angka 0 di awal
        }
    });
});
</script>

@endsection