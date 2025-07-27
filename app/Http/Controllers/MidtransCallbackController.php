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
        $signatureKey = hash(
            'sha512',
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($signatureKey !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Temukan pemesanan berdasarkan order_id
        $pemesanan = Pemesanan::where('order_id', $request->order_id)->first();

        if (!$pemesanan) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Tangani status dari Midtrans
        switch ($request->transaction_status) {
            case 'capture':
            case 'settlement':
                $pemesanan->status = 'diproses';
                break;

            case 'pending':
                // Jangan simpan status pending
                return response()->json(['message' => 'Pending status ignored'], 200);

            case 'expire':
            case 'cancel':
            case 'deny':
                $pemesanan->status = 'gagal';
                break;
        }

        $pemesanan->midtrans_response = $request->all();
        $pemesanan->save();

        return response()->json(['message' => 'Notification handled'], 200);
    }
}
