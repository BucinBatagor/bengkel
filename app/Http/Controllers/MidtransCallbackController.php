<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function receive(Request $request)
    {
        Log::info('Midtrans callback received', $request->all());

        $serverKey = config('midtrans.server_key');

        $signature = hash('sha512',
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($signature !== $request->signature_key) {
            Log::warning("Signature tidak valid untuk order_id {$request->order_id}");
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $pemesanan = Pemesanan::where('order_id', $request->order_id)->first();

        if (!$pemesanan) {
            Log::error("Pemesanan tidak ditemukan: {$request->order_id}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        switch ($request->transaction_status) {
            case 'settlement':
            case 'capture':
                $pemesanan->status = 'menunggu';
                break;
            case 'pending':
                $pemesanan->status = 'pending';
                break;
            case 'deny':
            case 'expire':
            case 'cancel':
                $pemesanan->status = 'gagal';
                break;
            default:
                Log::info("Status tidak dikenal: {$request->transaction_status}");
                break;
        }

        $pemesanan->midtrans_response = $request->all();
        $pemesanan->save();

        Log::info("Status pemesanan {$pemesanan->order_id} diupdate menjadi: {$pemesanan->status}");

        return response()->json(['message' => 'Callback processed'], 200);
    }
}
