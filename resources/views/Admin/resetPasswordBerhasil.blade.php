@extends('Template.authAdmin')

@section('title', 'Password Berhasil Diperbarui')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg bg-white p-6 sm:p-8 rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-bold mb-4 text-black">Password Berhasil Diubah</h2>
        <p class="text-gray-700">Password Anda telah berhasil diperbarui. Silakan login kembali dengan password baru Anda.</p>
        <a href="{{ route('admin.login') }}" class="mt-6 inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            Ke Halaman Login
        </a>
    </div>
</section>
@endsection
