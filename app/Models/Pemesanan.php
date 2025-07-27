<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $fillable = [
        'pelanggan_id',
        'produk_id',
        'order_id',
        'total_harga',
        'panjang',
        'lebar',
        'tinggi',
        'status',
        'snap_token',
        'midtrans_response',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
