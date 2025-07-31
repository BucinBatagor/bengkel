<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use App\Models\Produk;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class PemesananDetailController extends Controller
{
    public function index()
    {
        $items = PemesananDetail::with('produk.gambar')
            ->where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id'        => $item->id,
                    'produk_id' => $item->produk->id,
                    'nama'      => $item->produk->nama,
                    'harga'     => $item->produk->harga,
                    'gambar'    => $item->produk->gambar->first()
                        ? asset('storage/' . $item->produk->gambar->first()->gambar)
                        : asset('assets/default.jpg'),
                    'panjang'   => $item->panjang,
                    'lebar'     => $item->lebar,
                    'tinggi'    => $item->tinggi,
                ];
            });

        $sort = request('sort');
        return view('pelanggan.pemesananDetail', compact('items', 'sort'));
    }

    public function tambah(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id'
        ]);

        $produk = Produk::findOrFail($request->produk_id);

        PemesananDetail::create([
            'pelanggan_id' => auth()->id(),
            'produk_id'    => $produk->id,
            'nama_produk'  => $produk->nama,
            'harga'        => $produk->harga,
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function hapus($id)
    {
        PemesananDetail::where('id', $id)
            ->where('pelanggan_id', auth()->id())
            ->whereNull('pemesanan_id')
            ->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request)
    {
        $data = $request->selected_items;

        if (empty($data)) {
            return response()->json(['error' => 'Tidak ada produk yang dipilih.'], 400);
        }

        $totalHarga = collect($data)->reduce(function ($sum, $item) {
            return $sum + ($item['panjang'] + $item['lebar'] + $item['tinggi']) * $item['harga'];
        }, 0);

        $orderId = 'ORDER-' . uniqid();

        DB::beginTransaction();
        try {
            $pemesanan = Pemesanan::create([
                'order_id'     => $orderId,
                'pelanggan_id' => auth()->id(),
                'status'       => 'pending',
                'total_harga'  => $totalHarga,
            ]);

            $itemDetails = [];

            foreach ($data as $item) {
                $subtotal = ($item['panjang'] + $item['lebar'] + $item['tinggi']) * $item['harga'];

                PemesananDetail::where('id', $item['id'])
                    ->where('pelanggan_id', auth()->id())
                    ->update([
                        'pemesanan_id' => $pemesanan->id,
                        'panjang'      => $item['panjang'],
                        'lebar'        => $item['lebar'],
                        'tinggi'       => $item['tinggi'],
                        'harga'        => $subtotal,
                    ]);

                $itemDetails[] = [
                    'id'       => $item['produk_id'],
                    'price'    => $subtotal,
                    'quantity' => 1,
                    'name'     => $item['nama'],
                ];
            }

            $pelanggan = auth()->user();

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $totalHarga,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $pelanggan->name,
                    'email'      => $pelanggan->email,
                    'phone'      => $pelanggan->phone,
                    'billing_address' => [
                        'first_name' => $pelanggan->name,
                        'email'      => $pelanggan->email,
                        'phone'      => $pelanggan->phone,
                        'address'    => $pelanggan->address,
                    ],
                    'shipping_address' => [
                        'first_name' => $pelanggan->name,
                        'email'      => $pelanggan->email,
                        'phone'      => $pelanggan->phone,
                        'address'    => $pelanggan->address,
                    ],
                ]
            ];

            $snapToken = Snap::getSnapToken($params);
            $pemesanan->update(['snap_token' => $snapToken]);

            DB::commit();
            return response()->json(['success' => true, 'token' => $snapToken]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
