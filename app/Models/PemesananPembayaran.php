<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemesananPembayaran extends Model
{
    protected $table = 'pemesanan_pembayaran';

    protected $fillable = [
        'pemesanan_id',
        'order_id',
        'transaction_id',
        'transaction_status',
        'fraud_status',
        'payment_type',
        'gross_amount',
        'currency',
        'va_bank',
        'va_number',
        'biller_code',
        'permata_va',
        'transaction_time',
        'settlement_time',
        'pdf_url',
        'raw_payload',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'transaction_time' => 'datetime',
        'settlement_time' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }
}
