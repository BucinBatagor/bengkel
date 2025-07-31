<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransCallbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan semua route API untuk aplikasi Anda.
| File ini dimuat oleh RouteServiceProvider dalam grup route "api".
| Semua route di sini secara otomatis diawali dengan prefix "/api".
|
*/

// Route notifikasi Midtrans (webhook)
Route::post('/midtrans/callback', [MidtransCallbackController::class, 'receive']);
