@extends('Template.admin')

@section('title', 'Profil Admin')

@section('content')
@php
    // Ambil nilai phone (DB simpan 08...), tampilkan di input sebagai "rest" (tanpa 0 / 62 / +62)
    $rawPhone = old('phone', $admin->phone);
    $rest = '';
    if ($rawPhone) {
        if (strpos($rawPhone, '+62') === 0)      $rest = substr($rawPhone, 3);
        elseif (strpos($rawPhone, '62') === 0)   $rest = substr($rawPhone, 2);
        elseif ($rawPhone[0] === '0')            $rest = substr($rawPhone, 1);
        else                                     $rest = $rawPhone;
    }
@endphp

<section class="flex flex-col items-center px-6 py-6 w-full">
    <div class="bg-white rounded-lg shadow px-6 py-6 w-full"
         x-data="{
            rest: @js($rest),
            updateHidden(){
                // Hanya angka
                this.rest = (this.rest || '').replace(/[^0-9]/g, '');
                // Hilangkan nol di depan tampilan
                this.rest = this.rest.replace(/^0+/, '');
                // Batasi panjang wajar (maks 13 digit setelah kode negara)
                if (this.rest.length > 13) this.rest = this.rest.slice(0, 13);
                // KIRIM ke server dalam format lokal 08…
                this.$refs.phone.value = this.rest ? ('0' + this.rest) : '';
            },
            init(){ this.updateHidden(); }
         }">
        <h1 class="text-2xl font-bold mb-6">Profil Admin</h1>

        {{-- Pesan “tidak ada perubahan” --}}
        @if($errors->has('profil'))
            <div class="mb-4 text-red-700 bg-red-100 p-3 rounded">
                {{ $errors->first('profil') }}
            </div>
        @endif

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.profil.update') }}"
              novalidate
              class="space-y-4">
            @csrf

            {{-- Nama --}}
            <div>
                <label class="block font-semibold text-sm mb-1">Nama</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $admin->name) }}"
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-black/50"
                    required
                >
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email + Status Verifikasi --}}
            <div>
                <label class="block font-semibold text-sm mb-1 flex items-center gap-2">
                    Email
                    @if($admin->email_verified_at)
                        <span class="text-green-700 text-xs bg-green-100 px-2 py-0.5 rounded-full">Terverifikasi</span>
                    @else
                        <span class="text-red-700 text-xs bg-red-100 px-2 py-0.5 rounded-full">Belum diverifikasi</span>
                    @endif
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $admin->email) }}"
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-black/50"
                    required
                >
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nomor HP (phone) dengan prefix +62, kirim 08… --}}
            <div>
                <label class="block font-semibold text-sm mb-1">Nomor HP</label>

                <div class="relative">
                    {{-- Prefix +62 (tetap tampil +62) --}}
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-700 select-none">+62</span>

                    {{-- Input tampilan: hanya sisa digitnya --}}
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="81234567890"
                        x-model="rest"
                        @input="updateHidden()"
                        @paste="setTimeout(() => updateHidden(), 0)"
                        class="w-full border rounded-lg pl-12 pr-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-black/50"
                        aria-label="Nomor HP tanpa +62"
                        required
                    >

                    {{-- Input yang dikirim ke server (08…) --}}
                    <input type="hidden" name="phone" x-ref="phone">
                </div>

                @error('phone')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
