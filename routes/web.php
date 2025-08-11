<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Admin controllers
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\AturUlangPasswordController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;

// Pelanggan controllers
use App\Http\Controllers\Pelanggan\LoginController;
use App\Http\Controllers\Pelanggan\RegisterController;
use App\Http\Controllers\Pelanggan\PermintaanResetController;
use App\Http\Controllers\Pelanggan\ResetPasswordController;
use App\Http\Controllers\Pelanggan\BerandaController;
use App\Http\Controllers\Pelanggan\KatalogController as KatalogPelangganController;
use App\Http\Controllers\Pelanggan\ProdukController;
use App\Http\Controllers\Pelanggan\ProfilController;
use App\Http\Controllers\Pelanggan\PesananController;
use App\Http\Controllers\Pelanggan\PemesananDetailController;

// Middleware admin (pakai class langsung)
use App\Http\Middleware\AuthenticateAdmin;

// ======================= Admin routes =======================
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth admin
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware(AuthenticateAdmin::class)
        ->name('logout');

    // Verifikasi email admin
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return view('Admin.verifiedEmailSuccess');
    })->middleware([AuthenticateAdmin::class, 'signed'])->name('verification.verify');

    // Reset password admin
    Route::get('/lupa-password-admin', [AturUlangPasswordController::class, 'showFormRequest'])->name('password.request');
    Route::post('/lupa-password-admin', [AturUlangPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password-admin/{token}', [AturUlangPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password-admin', [AturUlangPasswordController::class, 'reset'])->name('password.update');
    Route::get('/reset-password-sukses-admin', fn () => view('Admin.resetPasswordBerhasil'))->name('password.reset.success');

    // Halaman admin (wajib login)
    Route::middleware(AuthenticateAdmin::class)->group(function () {
        // Katalog
        Route::resource('katalog', KatalogController::class)
            ->except(['show'])
            ->names([
                'index'   => 'katalog.index',
                'create'  => 'katalog.create',
                'store'   => 'katalog.store',
                'edit'    => 'katalog.edit',
                'update'  => 'katalog.update',
                'destroy' => 'katalog.destroy',
            ]);
        Route::delete('katalog/{produk}/gambar/{gambar}', [KatalogController::class, 'hapusGambar'])->name('katalog.gambar.hapus');

        // Kategori
        Route::resource('kategori', KategoriController::class)
            ->except(['show'])
            ->names([
                'index'   => 'kategori.index',
                'create'  => 'kategori.create',
                'store'   => 'kategori.store',
                'edit'    => 'kategori.edit',
                'update'  => 'kategori.update',
                'destroy' => 'kategori.destroy',
            ]);

        // Pelanggan (listing)
        Route::get('pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

        // Pemesanan
        Route::get('pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('pemesanan/{id}', [PemesananController::class, 'show'])->name('pemesanan.show');
        Route::get('pemesanan/{id}/edit', [PemesananController::class, 'edit'])->name('pemesanan.edit');

        // Update status pesanan
        Route::patch('pemesanan/{id}', [PemesananController::class, 'updateStatus'])->name('pemesanan.update');
        Route::put('pemesanan/{id}/status', [PemesananController::class, 'updateStatus'])->name('pemesanan.update_status');

        // Kebutuhan pesanan
        Route::get('pemesanan/{id}/kebutuhan', [PemesananController::class, 'editKebutuhan'])->name('pemesanan.kebutuhan.edit');
        Route::post('pemesanan/{id}/kebutuhan', [PemesananController::class, 'storeKebutuhan'])->name('pemesanan.kebutuhan.store');

        // Laporan
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        // Profil admin
        Route::get('profil', [AdminProfilController::class, 'index'])->name('profil');
        Route::post('profil', [AdminProfilController::class, 'update'])->name('profil.update');

        // Ubah password (profil admin)
        Route::get('ubah-password', [AdminProfilController::class, 'passwordForm'])->name('profil.password');
        Route::put('ubah-password', [AdminProfilController::class, 'updatePassword'])->name('profil.update-password');
    });
});

// ======================= Pelanggan routes =======================
Route::group([], function () {
    // Auth pelanggan
    Route::middleware('guest:pelanggan')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
    });
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Reset password pelanggan
    Route::get('lupa-password', [PermintaanResetController::class, 'showForm'])->name('password.request');
    Route::post('lupa-password/kirim', [PermintaanResetController::class, 'kirimEmail'])->name('password.email');
    Route::get('atur-ulang-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('atur-ulang-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    Route::get('password-berhasil', fn () => view('pelanggan.resetPasswordBerhasil'))->name('password.berhasil');

    // Halaman publik
    Route::get('/', fn () => redirect()->route('beranda'));
    Route::get('beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('katalog', [KatalogPelangganController::class, 'index'])->name('katalog');
    Route::get('produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    // Area pelanggan (wajib login)
    Route::middleware('auth:pelanggan')->group(function () {
        // Verifikasi email pelanggan
        Route::get('email/verify', fn () => auth('pelanggan')->user()->hasVerifiedEmail()
            ? redirect()->route('beranda')
            : view('pelanggan.verifikasiEmail'))
            ->name('verification.notice');

        Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();
            return view('pelanggan.verifikasiBerhasil');
        })->middleware('signed')->name('verification.verify');

        Route::post('email/verification-notification', function (Request $request) {
            $request->user('pelanggan')->sendEmailVerificationNotification();
            return back()->with('success', 'Link verifikasi telah dikirim ulang!');
        })->middleware('throttle:6,1')->name('verification.send');

        // Profil pelanggan
        Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::post('profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::get('ubah-password', fn () => view('pelanggan.resetPasswordProfil'))->name('profil.password');
        Route::put('ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');

        // Keranjang
        Route::get('keranjang', [PemesananDetailController::class, 'index'])->name('keranjang.index');
        Route::post('keranjang/tambah', [PemesananDetailController::class, 'tambah'])->name('keranjang.tambah');
        Route::delete('keranjang/hapus/{id}', [PemesananDetailController::class, 'hapus'])->name('keranjang.hapus');
        Route::post('keranjang/pesan', [PemesananDetailController::class, 'checkout'])->name('keranjang.pesan');

        // Pesanan pelanggan
        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::post('pesanan/{id}/bayar', [PesananController::class, 'bayar'])->name('pesanan.bayar');
        Route::post('pesanan/{id}/batal', [PesananController::class, 'batal'])->name('pesanan.batal');
        Route::post('pesanan/{id}/ajukan-refund', [PesananController::class, 'ajukanRefund'])->name('pesanan.ajukan_refund');
        Route::post('pesanan/{id}/batalkan-refund', [PesananController::class, 'batalkanRefund'])->name('pesanan.batalkan_refund');
    });
});
