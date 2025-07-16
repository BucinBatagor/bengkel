<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfilController;

// === Admin ===
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuth::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuth::class, 'login']);
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('admin.logout');

    Route::get('/katalog', fn() => view('Admin.katalog'))
        ->middleware('auth:admin')
        ->name('admin.katalog');
});

// === User ===
Route::prefix('/')->group(function () {
    // --- Auth ---
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // --- beranda ---
    Route::get('/', action: fn() => redirect()->route('beranda'));
    Route::get('beranda', [BerandaController::class, 'index'])->name('beranda');

    // --- Katalog ---
    Route::get('katalog', [KatalogController::class, 'index'])->name('katalog');

    // --- Produk ---
    Route::get('produk/{id}', [ProdukController::class, 'show'])->name('produk.show');

    // --- Harus Login ---
    Route::middleware('auth')->group(function () {
        // --- Profil ---
        Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::post('profil', [ProfilController::class, 'update'])->name('profil.update');
        // --- Ganti Password ---
        Route::get('ubah-password', fn() => view('User.ubahPassword'))->name('profil.password');
        Route::put('ubah-password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');
    });
});
