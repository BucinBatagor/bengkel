<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'order_id',
        'pelanggan_id',
        'status',
        'total_harga',
        'snap_token',
        'midtrans_response',
        'payment_expire_at',
    ];

    protected $casts = [
        'total_harga' => 'string',
        'midtrans_response' => 'array',
        'payment_expire_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function detail()
    {
        return $this->hasMany(PemesananDetail::class, 'pemesanan_id');
    }

    public function kebutuhan()
    {
        return $this->hasMany(PemesananKebutuhan::class, 'pemesanan_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(PemesananPembayaran::class, 'pemesanan_id');
    }

    public function setTotalHargaAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['total_harga'] = '0.00';
            return;
        }
        $this->attributes['total_harga'] = number_format((float) $value, 2, '.', '');
    }

    public function getTotalHargaAttribute($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function hitungUlangTotalDariKebutuhan(): string
    {
        $sum = (float) $this->kebutuhan()->sum('subtotal');
        $this->attributes['total_harga'] = number_format($sum, 2, '.', '');
        $this->save();

        return (string) $this->attributes['total_harga'];
    }
}
