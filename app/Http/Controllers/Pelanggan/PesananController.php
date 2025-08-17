<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PesananController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function index()
    {
        $pemesanan = Pemesanan::where('pelanggan_id', Auth::id())
            ->whereHas('detail')
            ->with('detail.produk')
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        return view('Pelanggan.pesanan', compact('pemesanan'));
    }

    public function bayar($id)
    {
        $pemesanan = Pemesanan::with('pelanggan')
            ->where('id', $id)
            ->where('pelanggan_id', Auth::id())
            ->where('status', 'belum_bayar')
            ->firstOrFail();

        $gross = (int) round((float) $pemesanan->total_harga);
        if ($gross <= 0) {
            return response()->json(['error' => 'Total tagihan tidak valid.'], 422);
        }

        if ($pemesanan->snap_token) {
            try {
                $st        = Transaction::status($pemesanan->order_id);
                $trxStatus = $this->mid($st, 'transaction_status');
                $payType   = $this->mid($st, 'payment_type');
                $trxTimeStr = $this->mid($st, 'transaction_time');
                $trxTime    = $trxTimeStr ? Carbon::parse($trxTimeStr) : null;
                $ageSec     = $trxTime ? $trxTime->diffInSeconds(now()) : null;

                if ($trxStatus === 'pending' && $payType === 'qris') {
                    if ($ageSec !== null && $ageSec <= 60) {
                        return response()->json(['snap_token' => $pemesanan->snap_token]);
                    }
                    try {
                        Transaction::cancel($pemesanan->order_id);
                    } catch (\Throwable $e) {
                    }
                    $pemesanan->order_id = $pemesanan->order_id . '-QR' . now()->format('His');
                    $pemesanan->snap_token = null;
                    $pemesanan->payment_expire_at = null;
                    $pemesanan->save();
                } else {
                    if (!$pemesanan->payment_expire_at || now()->lt($pemesanan->payment_expire_at)) {
                        return response()->json(['snap_token' => $pemesanan->snap_token]);
                    }
                }
            } catch (\Throwable $e) {
                if (!$pemesanan->payment_expire_at || now()->lt($pemesanan->payment_expire_at)) {
                    return response()->json(['snap_token' => $pemesanan->snap_token]);
                }
            }
        }

        $durationMinutes = 60 * 24;
        $startTime = now();

        $pel = $pemesanan->pelanggan;
        $firstName   = $pel->name ?? 'Pelanggan';
        $email       = $pel->email ?? null;
        $phone       = $pel->telepon ?? $pel->phone ?? null;
        $addressLine = $pel->address ?? '-';

        $customer = [
            'first_name' => $firstName,
            'email'      => $email,
            'phone'      => $phone,
            'billing_address' => [
                'first_name' => $firstName,
                'phone'      => $phone,
                'address'    => $addressLine,
            ],
            'shipping_address' => [
                'first_name' => $firstName,
                'phone'      => $phone,
                'address'    => $addressLine,
            ],
        ];

        $params = [
            'transaction_details' => [
                'order_id'     => $pemesanan->order_id,
                'gross_amount' => $gross,
            ],
            'item_details' => [[
                'id'       => (string) $pemesanan->id,
                'price'    => $gross,
                'quantity' => 1,
                'name'     => 'Pembayaran Pesanan',
            ]],
            'customer_details' => $customer,
            'credit_card' => ['secure' => true],
            'expiry' => [
                'start_time' => $startTime->format('Y-m-d H:i:s O'),
                'unit'       => 'minutes',
                'duration'   => $durationMinutes,
            ],
        ];

        try {
            $snap = Snap::createTransaction($params);
            $token = is_array($snap) ? ($snap['token'] ?? null) : ($snap->token ?? null);
            if (!$token) {
                return response()->json(['error' => 'Gagal membuat token pembayaran.'], 500);
            }

            $pemesanan->snap_token = $token;
            $pemesanan->payment_expire_at = $startTime->copy()->addMinutes($durationMinutes);
            $pemesanan->save();

            return response()->json(['snap_token' => $token]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Transaksi sedang menunggu pembayaran. Coba lagi beberapa saat.'], 422);
        }
    }

    private function mid($status, $key)
    {
        return is_array($status) ? ($status[$key] ?? null) : ($status->$key ?? null);
    }

    public function batal($id)
    {
        $pesanan = Pemesanan::where('id', $id)
            ->where('pelanggan_id', Auth::id())
            ->where('status', 'butuh_cek_ukuran')
            ->firstOrFail();

        $pesanan->update(['status' => 'batal']);

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function ajukanRefund($id)
    {
        $pesanan = Pemesanan::where('id', $id)
            ->where('pelanggan_id', Auth::id())
            ->where('status', 'di_proses')
            ->firstOrFail();

        $pesanan->update(['status' => 'pengembalian_dana']);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan refund berhasil dikirim. Silakan tunggu konfirmasi dari admin dan cek status pengembaliannya.');
    }
}
