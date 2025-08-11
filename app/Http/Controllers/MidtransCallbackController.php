<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pemesanan;
use App\Models\PemesananPembayaran;

class MidtransCallbackController extends Controller
{
    public function receive(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();
        Log::info('Midtrans callback', $payload);

        $serverKey = config('midtrans.server_key');
        $calc = hash(
            'sha512',
            ($payload['order_id'] ?? '') .
            ($payload['status_code'] ?? '') .
            ($payload['gross_amount'] ?? '') .
            $serverKey
        );

        if (!isset($payload['signature_key']) || strtolower($payload['signature_key']) !== strtolower($calc)) {
            Log::warning('Invalid Midtrans signature', ['order_id' => $payload['order_id'] ?? null]);
            return response()->json(['message' => 'ignored: invalid signature'], 200);
        }

        $orderId = $payload['order_id'] ?? null;
        if (!$orderId) {
            return response()->json(['message' => 'ignored: no order_id'], 200);
        }

        $pemesanan = Pemesanan::where('order_id', $orderId)->first();
        if (!$pemesanan) {
            Log::warning('Order not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'ignored: order not found'], 200);
        }

        $trx = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? '';
        $newStatus = null;

        switch ($trx) {
            case 'capture':
                $newStatus = ($fraud === 'accept') ? 'di_proses' : 'belum_bayar';
                break;
            case 'settlement':
                $newStatus = 'di_proses';
                break;
            case 'pending':
                $newStatus = 'belum_bayar';
                break;
            case 'deny':
            case 'expire':
                $newStatus = 'gagal';
                break;
            case 'cancel':
                $newStatus = 'batal';
                break;
            case 'refund':
            case 'partial_refund':
                $newStatus = 'pengembalian_selesai';
                break;
        }

        if ($newStatus && $pemesanan->status !== $newStatus) {
            $pemesanan->status = $newStatus;
        }

        if (schema_has_column($pemesanan->getTable(), 'midtrans_response')) {
            $pemesanan->midtrans_response = $payload;
        }
        $pemesanan->save();

        try {
            if (class_exists(PemesananPembayaran::class)) {
                $gross = (int) round((float) ($payload['gross_amount'] ?? 0));
                $vaBank = $vaNumber = $permataVa = $billerCode = null;

                if (($payload['payment_type'] ?? '') === 'bank_transfer') {
                    if (!empty($payload['va_numbers'][0]['bank'])) {
                        $vaBank = $payload['va_numbers'][0]['bank'] ?? null;
                        $vaNumber = $payload['va_numbers'][0]['va_number'] ?? null;
                    } elseif (!empty($payload['permata_va_number'])) {
                        $vaBank = 'permata';
                        $vaNumber = $payload['permata_va_number'];
                        $permataVa = $payload['permata_va_number'];
                    }
                    $billerCode = $payload['biller_code'] ?? null;
                }

                PemesananPembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'order_id' => $orderId,
                    'transaction_id' => $payload['transaction_id'] ?? null,
                    'transaction_status' => $trx,
                    'fraud_status' => $fraud,
                    'payment_type' => $payload['payment_type'] ?? null,
                    'gross_amount' => $gross,
                    'currency' => $payload['currency'] ?? 'IDR',
                    'va_bank' => $vaBank,
                    'va_number' => $vaNumber,
                    'biller_code' => $billerCode,
                    'permata_va' => $permataVa,
                    'transaction_time' => isset($payload['transaction_time']) ? date('Y-m-d H:i:s', strtotime($payload['transaction_time'])) : null,
                    'settlement_time' => isset($payload['settlement_time']) ? date('Y-m-d H:i:s', strtotime($payload['settlement_time'])) : null,
                    'pdf_url' => $payload['pdf_url'] ?? ($payload['actions'][1]['url'] ?? null),
                    'raw_payload' => $payload,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Store payment log failed', ['e' => $e->getMessage()]);
        }

        Log::info('Order status updated', ['order_id' => $orderId, 'status' => $pemesanan->status]);

        return response()->json(['message' => 'ok'], 200);
    }
}

if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
