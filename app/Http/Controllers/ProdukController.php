<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Pemesanan;
use Midtrans\Snap;
use Midtrans\Config;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);
        return view('pelanggan.produk', compact('produk'));
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();
        $produk = Produk::findOrFail($request->produk_id);

        $panjang = (float) $request->panjang;
        $lebar = (float) $request->lebar;
        $tinggi = (float) $request->tinggi;
        $harga = (float) $request->harga;

        $orderId = 'ORDER-' . uniqid();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $harga,
            ],
            'item_details' => [
                [
                    'id' => $produk->id,
                    'price' => $produk->harga,
                    'quantity' => 1,
                    'name' => $produk->nama,
                ]
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'billing_address' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address
                ],
                'shipping_address' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address
                ]
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        // Simpan ke database
        Pemesanan::create([
            'pelanggan_id' => $user->id,
            'produk_id' => $produk->id,
            'order_id' => $orderId,
            'total_harga' => $harga,
            'panjang' => $panjang,
            'lebar' => $lebar,
            'tinggi' => $tinggi,
            'snap_token' => $snapToken,
            'status' => 'diproses',
        ]);

        return response()->json(['token' => $snapToken]);
    }
}