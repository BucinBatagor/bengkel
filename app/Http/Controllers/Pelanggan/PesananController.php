<?php

namespace App\Http\Controllers\Pelanggan;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function createSnapToken(Request $request, $id)
    {
        $request->validate([
            'tipe'   => 'required|in:DP,PELUNASAN',
            'amount' => 'nullable|integer|min:1',
        ]);

        $q = Pemesanan::with('pelanggan')
            ->where('id', $id)
            ->where('pelanggan_id', Auth::id());

        if ($request->tipe === 'DP') {
            $q->where('status', 'belum_bayar');
        } else {
            $q->whereIn('status', ['belum_bayar','di_proses','dikerjakan']);
        }

        $pemesanan = $q->firstOrFail();

        $total = (int) round((float) ($pemesanan->total_harga ?? 0));
        $dp    = (int) round((float) ($pemesanan->dp ?? 0));
        $sisa  = max(0, $total - $dp);

        if ($total <= 0) {
            return response()->json(['message' => 'Total belum tersedia.'], 422);
        }

        if ($sisa <= 0) {
            return response()->json(['message' => 'Pesanan sudah lunas'], 422);
        }

        if ($request->tipe === 'DP') {
            if ($dp > 0) {
                return response()->json(['message' => 'DP sudah dilakukan, lanjutkan pelunasan'], 422);
            }
            $reqAmt = (int) ($request->amount ?? 0);
            $amount = max(1000, min($reqAmt, $sisa));
            if ($amount < 1 || $amount > $sisa) {
                return response()->json(['message' => 'Nominal DP tidak valid'], 422);
            }
        } else {
            $amount = (int) $sisa;
        }

        $pel = $pemesanan->pelanggan;
        $firstName   = $pel->nama ?? $pel->name ?? 'Pelanggan';
        $email       = $pel->email ?? null;
        $phone       = $pel->no_hp ?? $pel->telepon ?? $pel->phone ?? null;
        $addressLine = $pel->alamat ?? $pel->address ?? '-';

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

        $durationMinutes = 60 * 24;
        $startTime = now();

        $uniqueOrderId = $pemesanan->order_id . '-' . strtoupper($request->tipe) . '-' . Str::upper(Str::random(6));

        $params = [
            'transaction_details' => [
                'order_id'     => $uniqueOrderId,
                'gross_amount' => $amount,
            ],
            'item_details' => [[
                'id'       => (string) $pemesanan->id,
                'price'    => $amount,
                'quantity' => 1,
                'name'     => ($request->tipe === 'DP' ? 'DP ' : 'Pelunasan ') . $pemesanan->order_id,
            ]],
            'customer_details' => $customer,
            'custom_field1' => $request->tipe,
            'custom_field2' => (string) $pemesanan->id,
            'custom_field3' => $pemesanan->order_id,
            'credit_card' => ['secure' => true],
            'expiry' => [
                'start_time' => $startTime->format('Y-m-d H:i:s O'),
                'unit'       => 'minutes',
                'duration'   => $durationMinutes,
            ],
        ];

        try {
            $token = Snap::getSnapToken($params);
            if (!$token) {
                return response()->json(['message' => 'Gagal membuat token pembayaran'], 500);
            }

            $pemesanan->snap_token = $token;
            $pemesanan->payment_expire_at = $startTime->copy()->addMinutes($durationMinutes);
            $pemesanan->save();

            return response()->json([
                'token' => $token,
                'order_id' => $uniqueOrderId,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memproses pembayaran'], 422);
        }
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
        $firstName   = $pel->nama ?? $pel->name ?? 'Pelanggan';
        $email       = $pel->email ?? null;
        $phone       = $pel->no_hp ?? $pel->telepon ?? $pel->phone ?? null;
        $addressLine = $pel->alamat ?? $pel->address ?? '-';

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

    public function nota($id)
    {
        $pemesanan = \App\Models\Pemesanan::with(['pelanggan','detail.produk','kebutuhan'])
            ->where('id', $id)
            ->where('pelanggan_id', \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        $kebutuhan = $pemesanan->kebutuhan()->orderBy('kategori')->orderBy('id')->get();

        $total = (float) $pemesanan->total_harga;
        $dp    = (float) ($pemesanan->dp ?? 0);
        $sisa  = (float) ($pemesanan->sisa ?? max(0, $total - $dp));

        $data = [
            'order'      => $pemesanan,
            'pelanggan'  => $pemesanan->pelanggan,
            'kebutuhan'  => $kebutuhan,
            'total'      => $total,
            'dp'         => $dp,
            'sisa'       => $sisa,
            'judul'      => $pemesanan->detail->pluck('nama_produk')->filter()->implode(', '),
            'appName'    => config('app.name'),
            'printedAt'  => now(),
        ];

        $pdf = Pdf::loadView('Pelanggan.nota', $data)->setPaper('A5', 'portrait');
        $filename = 'Nota-'.$pemesanan->order_id.'.pdf';

        return $pdf->stream($filename);
    }
}
