<?php

use Illuminate\Support\Facades\Route;

// Controller Admin
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\LaporanController;

// Controller Pelanggan
use App\Http\Controllers\Pelanggan\LoginController;
use App\Http\Controllers\Pelanggan\RegisterController;
use App\Http\Controllers\Pelanggan\LupaPasswordController;
use App\Http\Controllers\Pelanggan\AturUlangPasswordController;
use App\Http\Controllers\Pelanggan\BerandaController;
use App\Http\Controllers\Pelanggan\KatalogController as KatalogPelangganController;
use App\Http\Controllers\Pelanggan\ProdukController;
use App\Http\Controllers\Pelanggan\ProfilController;
use App\Http\Controllers\Pelanggan\PesananController;
use App\Http\Controllers\Pelanggan\PemesananDetailController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Login Admin
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Logout Admin (hanya jika sudah login)
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:admin')->name('logout');

    // Halaman admin yang butuh login
    Route::middleware('auth:admin')->group(function () {
        // Kelola Katalog Produk
        Route::delete('/katalog-gambar/{id}', [KatalogController::class, 'hapusGambar'])->name('katalog.gambar.hapus');
        Route::resource('katalog', KatalogController::class)->except(['show']);

        // Kelola Data Pelanggan
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

        // Kelola Pemesanan Masuk
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::patch('/pemesanan/{id}', [PemesananController::class, 'updateStatus'])->name('pemesanan.update');

        // Laporan Pendapatan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    });
});

/*
|--------------------------------------------------------------------------
| Pelanggan Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/')->group(function () {
    // Login Pelanggan
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register Pelanggan
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Lupa Password
    Route::get('/lupa-password', [LupaPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/lupa-password/kirim', [LupaPasswordController::class, 'kirimEmail'])->name('password.email');

    // Atur Ulang Password
    Route::get('/atur-ulang-password/{token}', [AturUlangPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/atur-ulang-password', [AturUlangPasswordController::class, 'reset'])->name('password.update');

    // Logout Pelanggan
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Beranda Pelanggan
    Route::get('/', fn() => redirect()->route('beranda'));
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');

    // Katalog & Produk Detail
    Route::get('/katalog', [KatalogPelangganController::class, 'index'])->name('katalog');
    Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    // Halaman yang butuh login pelanggan
    Route::middleware('auth:pelanggan')->group(function () {
        // Profil & Ubah Password
        Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::get('/ubah-password', fn() => view('pelanggan.ubahPassword'))->name('profil.password');
        Route::put('/ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');

        // Keranjang
        Route::get('/keranjang', [PemesananDetailController::class, 'index'])->name('keranjang.index');
        Route::post('/keranjang/tambah', [PemesananDetailController::class, 'tambah'])->name('keranjang.tambah');
        Route::delete('/keranjang/hapus/{id}', [PemesananDetailController::class, 'hapus'])->name('keranjang.hapus');
        Route::post('/keranjang/checkout', [PemesananDetailController::class, 'checkout'])->name('keranjang.checkout');

        // Pesanan Pelanggan
        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::post('/pesanan/{id}/bayar', [PesananController::class, 'bayar'])->name('pesanan.bayar');
        Route::post('/pesanan/{id}/batal', [PesananController::class, 'batal'])->name('pesanan.batal');
        Route::post('/pesanan/{id}/batalkan-refund', [PesananController::class, 'batalkanRefund'])->name('pesanan.batalkan_refund');
    });
});
