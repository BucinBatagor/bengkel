<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Pemesanan;

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

        $pemesanan = null;
        $pemesananId = $payload['custom_field2'] ?? null;
        $baseOrderId = $payload['custom_field3'] ?? null;

        if ($pemesananId) {
            $pemesanan = Pemesanan::find($pemesananId);
        } elseif ($baseOrderId) {
            $pemesanan = Pemesanan::where('order_id', $baseOrderId)->first();
        } else {
            if (preg_match('/^(.*)-(DP|PELUNASAN)-[A-Z0-9]{6}$/', $orderId, $m)) {
                $baseOrderId = $m[1] ?? null;
                if ($baseOrderId) {
                    $pemesanan = Pemesanan::where('order_id', $baseOrderId)->first();
                }
            }
            if (!$pemesanan) {
                $pemesanan = Pemesanan::where('order_id', $orderId)->first();
            }
        }

        if (!$pemesanan) {
            Log::warning('Order not found', ['order_id' => $orderId, 'base' => $baseOrderId, 'pid' => $pemesananId]);
            return response()->json(['message' => 'ignored: order not found'], 200);
        }

        $trx = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? '';

        $mappedStatus = null;
        switch ($trx) {
            case 'capture':
                $mappedStatus = ($fraud === 'accept') ? 'di_proses' : 'belum_bayar';
                break;
            case 'settlement':
                $mappedStatus = 'di_proses';
                break;
            case 'pending':
                $mappedStatus = 'belum_bayar';
                break;
            case 'deny':
            case 'expire':
                $mappedStatus = 'gagal';
                break;
            case 'cancel':
                $mappedStatus = 'batal';
                break;
            case 'refund':
            case 'partial_refund':
                $mappedStatus = 'pengembalian_selesai';
                break;
        }

        $tipe = $payload['custom_field1'] ?? null;
        $amount = (float) ($payload['gross_amount'] ?? 0);

        if (in_array($trx, ['capture','settlement'])) {
            DB::transaction(function () use ($pemesanan, $tipe, $amount, $payload, $mappedStatus) {
                $p = Pemesanan::lockForUpdate()->find($pemesanan->id);
                if (!$p) return;

                if ($mappedStatus && $p->status !== $mappedStatus) {
                    $p->status = $mappedStatus;
                }

                if ($tipe === 'DP') {
                    if ((float) $p->dp <= 0) {
                        $p->dp = $amount;
                        $p->sisa = max(0, (float) $p->total_harga - (float) $p->dp);
                    }
                } elseif ($tipe === 'PELUNASAN') {
                    $p->sisa = max(0, (float) $p->sisa - $amount);
                }

                $p->midtrans_response = $payload;
                $p->save();
            });
        } else {
            if ($mappedStatus && $pemesanan->status !== $mappedStatus) {
                $pemesanan->status = $mappedStatus;
            }
            $pemesanan->midtrans_response = $payload;
            $pemesanan->save();
        }

        Log::info('Order status updated', ['order_id' => $orderId, 'status' => $mappedStatus]);

        return response()->json(['message' => 'ok'], 200);
    }
}
