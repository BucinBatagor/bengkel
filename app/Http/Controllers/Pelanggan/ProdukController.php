<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Admin;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);

        $adminPhoneRaw = Admin::whereNotNull('phone')->orderBy('id')->value('phone');
        $waAdmin = $this->toWaNumber($adminPhoneRaw) ?? '6289644819899';

        return view('Pelanggan.produk', compact('produk', 'waAdmin'));
    }

    private function toWaNumber(?string $phone): ?string
    {
        if ($phone === null) return null;

        $phone = trim($phone);
        if ($phone === '') return null;

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') return null;

        if (strpos($digits, '62') === 0) return $digits;
        if ($digits[0] === '0') return '62' . substr($digits, 1);
        if ($digits[0] === '8') return '62' . $digits;

        return $digits;
    }
}
