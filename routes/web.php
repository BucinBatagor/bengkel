<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Controller untuk Admin
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\LaporanController;

// Controller untuk Pelanggan
use App\Http\Controllers\Pelanggan\LoginController;
use App\Http\Controllers\Pelanggan\RegisterController;
use App\Http\Controllers\Pelanggan\permintaanResetController;
use App\Http\Controllers\Pelanggan\resetPasswordController;
use App\Http\Controllers\Pelanggan\BerandaController;
use App\Http\Controllers\Pelanggan\KatalogController as KatalogPelangganController;
use App\Http\Controllers\Pelanggan\ProdukController;
use App\Http\Controllers\Pelanggan\ProfilController;
use App\Http\Controllers\Pelanggan\PesananController;
use App\Http\Controllers\Pelanggan\PemesananDetailController;

/*
|--------------------------------------------------------------------------
| ROUTE UNTUK ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Autentikasi Admin
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:admin')->name('logout');

    // Area Admin (butuh login)
    Route::middleware('auth:admin')->group(function () {
        // Manajemen Katalog Produk
        Route::delete('/katalog-gambar/{id}', [KatalogController::class, 'hapusGambar'])->name('katalog.gambar.hapus');
        Route::resource('katalog', KatalogController::class)->except(['show']);

        // Manajemen Pelanggan
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

        // Manajemen Pemesanan
        Route::get('/pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::patch('/pemesanan/{id}', [PemesananController::class, 'updateStatus'])->name('pemesanan.update');

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    });
});

/*
|--------------------------------------------------------------------------
| ROUTE UNTUK PELANGGAN
|--------------------------------------------------------------------------
*/
Route::prefix('/')->group(function () {
    // Autentikasi Pelanggan
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Reset Password Pelanggan
    Route::get('/lupa-password', [permintaanResetController::class, 'showForm'])->name('password.request');
    Route::post('/lupa-password/kirim', [permintaanResetController::class, 'kirimEmail'])->name('password.email');
    Route::get('/atur-ulang-password/{token}', [resetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/atur-ulang-password', [resetPasswordController::class, 'reset'])->name('password.update');
    Route::get('/password-berhasil', function () {
    return view('pelanggan.resetPasswordBerhasil');
})->name('password.berhasil');


    // Halaman Publik
    Route::get('/', fn() => redirect()->route('beranda'));
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/katalog', [KatalogPelangganController::class, 'index'])->name('katalog');
    Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    // Area Login Pelanggan
    Route::middleware('auth:pelanggan')->group(function () {
        // Verifikasi Email
        Route::get('/email/verify', function () {
            if (auth('pelanggan')->user()?->hasVerifiedEmail()) {
                return redirect('/beranda');
            }
            return view('pelanggan.verifikasiEmail');
        })->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();
            return view('pelanggan.verifikasiBerhasil');
        })->middleware(['signed'])->name('verification.verify');

        Route::post('/email/verification-notification', function (Request $request) {
            $request->user('pelanggan')->sendEmailVerificationNotification();
            return back()->with('success', 'Link verifikasi telah dikirim ulang!');
        })->middleware('throttle:6,1')->name('verification.send');

        // Profil
        Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::get('/ubah-password', fn() => view('pelanggan.resetPasswordProfil'))->name('profil.password');
        Route::put('/ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');

        // Keranjang
        Route::get('/keranjang', [PemesananDetailController::class, 'index'])->name('keranjang.index');
        Route::post('/keranjang/tambah', [PemesananDetailController::class, 'tambah'])->name('keranjang.tambah');
        Route::delete('/keranjang/hapus/{id}', [PemesananDetailController::class, 'hapus'])->name('keranjang.hapus');
        Route::post('/keranjang/checkout', [PemesananDetailController::class, 'checkout'])->name('keranjang.checkout');

        // Pesanan
        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::post('/pesanan/{id}/bayar', [PesananController::class, 'bayar'])->name('pesanan.bayar');
        Route::post('/pesanan/{id}/batal', [PesananController::class, 'batal'])->name('pesanan.batal');
        Route::post('/pesanan/{id}/batalkan-refund', [PesananController::class, 'batalkanRefund'])->name('pesanan.batalkan_refund');
    });
});
