@extends('Template.authPelanggan')

@section('title', 'Verifikasi Email')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-lg bg-white p-6 sm:p-8 rounded-lg shadow-md text-center">
    <h2 class="text-2xl font-bold mb-3 text-gray-900">Verifikasi Email</h2>
    <p class="text-gray-700">
      Kami telah mengirimkan link verifikasi ke alamat email Anda.
      Silakan periksa kotak masuk lalu klik link tersebut untuk mengaktifkan akun.
      Jika tidak menemukan email, coba cek folder Spam atau Promotion.
    </p>
  </div>
</section>
@endsection
