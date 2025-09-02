<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use App\Models\Pelanggan;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KatalogController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PelangganController as AdminPelangganController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\AturUlangPasswordController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;

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

use App\Http\Middleware\AuthenticateAdmin;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware(AuthenticateAdmin::class)
        ->name('logout');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return view('Admin.verifiedEmailSuccess');
    })->middleware([AuthenticateAdmin::class, 'signed'])->name('verification.verify');

    Route::get('/lupa-password-admin', [AturUlangPasswordController::class, 'showFormRequest'])->name('password.request');
    Route::post('/lupa-password-admin', [AturUlangPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password-admin/{token}', [AturUlangPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password-admin', [AturUlangPasswordController::class, 'reset'])->name('password.update');
    Route::get('/reset-password-sukses-admin', fn () => view('Admin.resetPasswordBerhasil'))->name('password.reset.success');

    Route::middleware(AuthenticateAdmin::class)->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

        Route::get('pelanggan', [AdminPelangganController::class, 'index'])->name('pelanggan.index');

        Route::get('pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
        Route::get('pemesanan/{id}', [PemesananController::class, 'show'])->name('pemesanan.show');
        Route::get('pemesanan/{id}/edit', [PemesananController::class, 'edit'])->name('pemesanan.edit');

        Route::patch('pemesanan/{id}', [PemesananController::class, 'updateStatus'])->name('pemesanan.update');
        Route::put('pemesanan/{id}/status', [PemesananController::class, 'updateStatus'])->name('pemesanan.update_status');

        Route::get('pemesanan/{id}/kebutuhan', [PemesananController::class, 'editKebutuhan'])->name('pemesanan.kebutuhan.edit');
        Route::post('pemesanan/{id}/kebutuhan', [PemesananController::class, 'storeKebutuhan'])->name('pemesanan.kebutuhan.store');

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        Route::get('profil', [AdminProfilController::class, 'index'])->name('profil');
        Route::post('profil', [AdminProfilController::class, 'update'])->name('profil.update');

        Route::get('ubah-password', [AdminProfilController::class, 'passwordForm'])->name('profil.password');
        Route::put('ubah-password', [AdminProfilController::class, 'updatePassword'])->name('profil.update-password');
    });
});

Route::middleware('guest:pelanggan')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('lupa-password', [PermintaanResetController::class, 'showForm'])->name('password.request');
Route::post('lupa-password/kirim', [PermintaanResetController::class, 'kirimEmail'])->name('password.email');
Route::get('atur-ulang-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
Route::post('atur-ulang-password', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('password-berhasil', fn () => view('pelanggan.resetPasswordBerhasil'))->name('password.berhasil');

Route::get('/', fn () => redirect()->route('beranda'));
Route::get('beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('katalog', [KatalogPelangganController::class, 'index'])->name('katalog');
Route::get('produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

Route::get('email/verify', fn () => view('Pelanggan.verifikasiEmail'))->name('verification.notice');

Route::get('email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = Pelanggan::findOrFail($id);
    if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
        abort(403, 'Link verifikasi tidak valid.');
    }
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }
    return redirect()->route('login')->with('success', 'Email berhasil diverifikasi. Silakan login.');
})->middleware(['signed'])->name('verification.verify');

Route::post('email/verification-notification', function (Request $request) {
    $validated = $request->validate(['email' => 'required|email']);
    $user = Pelanggan::where('email', $validated['email'])->first();
    if (! $user) {
        return back()->withErrors(['email' => 'Email tidak terdaftar.']);
    }
    if ($user->hasVerifiedEmail()) {
        return redirect()->route('login')->with('success', 'Email sudah terverifikasi. Silakan login.');
    }
    $user->sendEmailVerificationNotification();
    return back()->with('success', 'Link verifikasi telah dikirim ulang. Cek kotak masuk Anda.');
})->name('verification.send');

Route::middleware('auth:pelanggan')->group(function () {
    Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::post('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::get('ubah-password', fn () => view('pelanggan.resetPasswordProfil'))->name('profil.password');
    Route::put('ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');

    Route::get('keranjang', [PemesananDetailController::class, 'index'])->name('keranjang.index');
    Route::post('keranjang/tambah', [PemesananDetailController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('keranjang/{id}/jumlah', [PemesananDetailController::class, 'ubahJumlah'])->name('keranjang.ubah_jumlah');
    Route::delete('keranjang/hapus/{id}', [PemesananDetailController::class, 'hapus'])->name('keranjang.hapus');
    Route::post('keranjang/pesan', [PemesananDetailController::class, 'pesan'])->name('keranjang.pesan');

    Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::post('pesanan/{id}/bayar', [PesananController::class, 'bayar'])->name('pesanan.bayar');
    Route::post('pesanan/{id}/batal', [PesananController::class, 'batal'])->name('pesanan.batal');
    Route::post('pesanan/{id}/ajukan-refund', [PesananController::class, 'ajukanRefund'])->name('pesanan.ajukan_refund');
    Route::post('pesanan/{id}/batalkan-refund', [PesananController::class, 'batalkanRefund'])->name('pesanan.batalkan_refund');

    Route::post('pesanan/{id}/snap-token', [PesananController::class, 'createSnapToken'])->name('pesanan.snap-token');
    Route::get('pesanan/{id}/nota', [PesananController::class, 'nota'])->name('pesanan.nota');
});
