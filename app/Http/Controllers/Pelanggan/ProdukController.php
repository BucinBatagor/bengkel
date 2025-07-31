<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::with('gambar')->findOrFail($id);
        return view('pelanggan.produk', compact('produk'));
    }

    public function produk(Request $request)
    {
        $user = auth()->user();
        $produk = Produk::findOrFail($request->produk_id);

        $panjang = (float) $request->panjang;
        $lebar   = (float) $request->lebar;
        $tinggi  = (float) $request->tinggi;

        $harga = ($panjang + $lebar + $tinggi) * $produk->harga;
        $orderId = 'ORDER-' . uniqid();

        DB::beginTransaction();
        try {
            $pemesanan = Pemesanan::create([
                'order_id'         => $orderId,
                'pelanggan_id'     => $user->id,
                'status'           => 'pending',
                'total_harga'      => $harga,
                'snap_token'       => null,
                'midtrans_response'=> null,
            ]);

            PemesananDetail::create([
                'pemesanan_id' => $pemesanan->id,
                'pelanggan_id' => $user->id,
                'produk_id'    => $produk->id,
                'nama_produk'  => $produk->nama,
                'panjang'      => $panjang,
                'lebar'        => $lebar,
                'tinggi'       => $tinggi,
                'harga'        => $harga,
            ]);

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $harga,
                ],
                'item_details' => [
                    [
                        'id'       => $produk->id,
                        'price'    => $harga,
                        'quantity' => 1,
                        'name'     => $produk->nama . " (Ukuran {$panjang}x{$lebar}x{$tinggi})",
                    ]
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'billing_address' => [
                        'first_name' => $user->name,
                        'email'      => $user->email,
                        'phone'      => $user->phone,
                        'address'    => $user->address,
                    ],
                    'shipping_address' => [
                        'first_name' => $user->name,
                        'email'      => $user->email,
                        'phone'      => $user->phone,
                        'address'    => $user->address,
                    ]
                ]
            ];

            $snapToken = Snap::getSnapToken($params);
            $pemesanan->update(['snap_token' => $snapToken]);

            DB::commit();
            return response()->json(['token' => $snapToken]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
