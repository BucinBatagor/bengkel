<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\PemesananDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class PesananController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function keranjang()
    {
        $pemesanan = Pemesanan::where('pelanggan_id', Auth::id())
            ->where('status', 'keranjang')
            ->with('details.produk')
            ->first();

        return view('pelanggan.keranjang', compact('pemesanan'));
    }

    public function tambahKeranjang($produkId)
    {
        $user = Auth::user();

        $pemesanan = Pemesanan::firstOrCreate(
            [
                'pelanggan_id' => $user->id,
                'status' => 'keranjang'
            ],
            [
                'order_id' => uniqid('ORD-'),
                'total_harga' => 0
            ]
        );

        PemesananDetail::create([
            'pemesanan_id' => $pemesanan->id,
            'produk_id' => $produkId,
            'jumlah' => 1,
            'panjang' => 0,
            'lebar' => 0,
            'tinggi' => 0,
            'harga' => 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function hapusKeranjang($id)
    {
        PemesananDetail::where('id', $id)->delete();
        return redirect()->route('keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function updateKeranjang(Request $request)
    {
        foreach ($request->items as $item) {
            PemesananDetail::where('id', $item['id'])->update([
                'jumlah' => $item['jumlah'],
                'panjang' => $item['panjang'],
                'lebar' => $item['lebar'],
                'tinggi' => $item['tinggi'],
                'harga' => $item['harga'],
            ]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function checkoutKeranjang()
    {
        $pemesanan = Pemesanan::where('pelanggan_id', Auth::id())
            ->where('status', 'keranjang')
            ->with('details.produk')
            ->firstOrFail();

        $totalHarga = $pemesanan->details->sum(function ($item) {
            return $item->harga * $item->jumlah;
        });

        $params = [
            'transaction_details' => [
                'order_id' => $pemesanan->order_id,
                'gross_amount' => $totalHarga,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->nama,
                'email' => Auth::user()->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        $pemesanan->update([
            'total_harga' => $totalHarga,
            'status' => 'pending',
            'snap_token' => $snapToken,
        ]);

        return response()->json(['token' => $snapToken]);
    }

    public function index()
    {
        $pemesanan = Pemesanan::where('pelanggan_id', Auth::id())
            ->where('status', '!=', 'keranjang')
            ->with('details.produk')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pelanggan.pesanan', compact('pemesanan'));
    }

    public function bayar($id)
    {
        $pemesanan = Pemesanan::where('id', $id)
            ->where('pelanggan_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        if (!$pemesanan->snap_token) {
            return response()->json(['error' => 'Token pembayaran tidak ditemukan.'], 422);
        }

        return response()->json([
            'snap_token' => $pemesanan->snap_token
        ]);
    }

    public function batal($id)
    {
        try {
            $pesanan = Pemesanan::where('id', $id)
                ->where('pelanggan_id', Auth::id())
                ->whereIn('status', ['menunggu'])
                ->firstOrFail();

            $pesanan->update([
                'status' => 'menunggu_refund',
            ]);

            return redirect()->back()->with('success', 'Permintaan refund telah dikirim.');
        } catch (\Exception $e) {
            \Log::error('Error batal pesanan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan pesanan: ');
        }
    }

    public function batalkanRefund($id)
    {
        $pemesanan = Pemesanan::where('id', $id)
            ->where('pelanggan_id', auth()->id())
            ->where('status', 'menunggu_refund')
            ->firstOrFail();

        $pemesanan->status = 'menunggu';
        $pemesanan->save();

        return redirect()->back()->with('success', 'Pengajuan refund dibatalkan.');
    }
}
