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

        $serverKey = config('midtrans.server_key');
        $calc = hash(
            'sha512',
            ($payload['order_id'] ?? '') .
            ($payload['status_code'] ?? '') .
            ($payload['gross_amount'] ?? '') .
            $serverKey
        );
        if (!isset($payload['signature_key']) || strtolower($payload['signature_key']) !== strtolower($calc)) {
            return response()->json(['message' => 'ignored: invalid signature'], 200);
        }

        $orderId = $payload['order_id'] ?? null;
        if (!$orderId) return response()->json(['message' => 'ignored: no order_id'], 200);

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
                if ($baseOrderId) $pemesanan = Pemesanan::where('order_id', $baseOrderId)->first();
            }
            if (!$pemesanan) $pemesanan = Pemesanan::where('order_id', $orderId)->first();
        }
        if (!$pemesanan) return response()->json(['message' => 'ignored: order not found'], 200);

        $trx   = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? '';
        $txid  = $payload['transaction_id'] ?? null;

        $mappedStatus = null;
        switch ($trx) {
            case 'capture': $mappedStatus = ($fraud === 'accept') ? 'di_proses' : 'belum_bayar'; break;
            case 'settlement': $mappedStatus = 'di_proses'; break;
            case 'pending': $mappedStatus = 'belum_bayar'; break;
            case 'deny':
            case 'expire': $mappedStatus = 'gagal'; break;
            case 'cancel': $mappedStatus = 'batal'; break;
            case 'refund':
            case 'partial_refund': $mappedStatus = 'pengembalian_selesai'; break;
        }

        $rawAmount = (string) ($payload['gross_amount'] ?? '0');
        $amount = (float) preg_replace('/[^\d.]/', '', $rawAmount);

        if (in_array($trx, ['capture','settlement'])) {
            DB::transaction(function () use ($pemesanan, $amount, $payload, $mappedStatus, $txid, $trx) {
                $p = Pemesanan::lockForUpdate()->find($pemesanan->id);
                if (!$p) return;

                $prev = $p->midtrans_response;
                $prevTx = null;
                $prevStatus = null;
                if (is_array($prev)) {
                    $prevTx = $prev['transaction_id'] ?? null;
                    $prevStatus = $prev['transaction_status'] ?? null;
                } elseif (is_string($prev)) {
                    $decoded = json_decode($prev, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $prevTx = $decoded['transaction_id'] ?? null;
                        $prevStatus = $decoded['transaction_status'] ?? null;
                    }
                }

                $alreadySettled = in_array($prevStatus, ['capture','settlement'], true);
                if ($txid && $prevTx && $txid === $prevTx && $alreadySettled) {
                    if ($mappedStatus && $p->status !== $mappedStatus) $p->status = $mappedStatus;
                    $p->midtrans_response = $payload;
                    $p->save();
                    return;
                }

                if ($mappedStatus && $p->status !== $mappedStatus) $p->status = $mappedStatus;

                $total   = (float) ($p->total_harga ?? 0);
                $dpNow   = (float) ($p->dp ?? 0);
                $sisaNow = max(0, $total - $dpNow);

                $useAmount = $amount > 0 ? $amount : $sisaNow;
                $useAmount = min($useAmount, $sisaNow);

                if ($total > 0 && $useAmount > 0) {
                    $newDp = $dpNow + $useAmount;
                    if ($newDp > $total) $newDp = $total;
                    $p->dp   = $newDp;
                    $p->sisa = max(0, $total - $newDp);
                }

                $p->midtrans_response = $payload;
                $p->save();
            });
        } else {
            if ($mappedStatus && $pemesanan->status !== $mappedStatus) $pemesanan->status = $mappedStatus;
            $pemesanan->midtrans_response = $payload;
            $pemesanan->save();
        }

        return response()->json(['message' => 'ok'], 200);
    }
}
