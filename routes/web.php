<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\KatalogController as AdminKatalog;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Pelanggan\LoginController;
use App\Http\Controllers\Pelanggan\RegisterController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfilController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Login & Logout
    Route::get('/login', [AdminAuth::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuth::class, 'login']);
    Route::post('/logout', [AdminAuth::class, 'logout'])->middleware('auth:admin')->name('logout');

    // Admin Area (requires login)
    Route::middleware('auth:admin')->group(function () {
        Route::delete('/katalog-gambar/{id}', [AdminKatalog::class, 'hapusGambar'])->name('katalog.gambar.hapus');
        Route::resource('katalog', AdminKatalog::class)->except(['show']);
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

        // Pemesanan Masuk (Admin
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::patch('/pemesanan/{id}', [PemesananController::class, 'updateStatus'])->name('pemesanan.update');

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
    // Beranda
    Route::get('/', fn() => redirect()->route('beranda'));
    Route::get('beranda', [BerandaController::class, 'index'])->name('beranda');

    // Katalog & Produk
    Route::get('katalog', [KatalogController::class, 'index'])->name('katalog');
    Route::get('produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    // Auth
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    
    // Akun (requires login)
    Route::middleware('auth:web')->group(function () {
        // Profil
        Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::post('profil', [ProfilController::class, 'update'])->name('profil.update');

        // Ganti Password
        Route::get('ubah-password', fn() => view('pelanggan.ubahPassword'))->name('profil.password');
        Route::put('ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');

        // Checkout
        Route::post('checkout', [ProdukController::class, 'checkout'])->name('checkout');
    });
});
