<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/beranda');
});

Route::get('/beranda', function () {
    return view('beranda');
});
