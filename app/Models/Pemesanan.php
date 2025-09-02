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
        'keuntungan',
        'total_harga',
        'dp',
        'sisa',
        'snap_token',
        'midtrans_response',
        'payment_expire_at',
    ];

    protected $casts = [
        'total_harga' => 'string',
        'dp' => 'string',
        'sisa' => 'string',
        'midtrans_response' => 'array',
        'payment_expire_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($p) {
            $total = (float) ($p->total_harga ?? 0);
            $dp = (float) ($p->dp ?? 0);
            $p->sisa = number_format(max(0, $total - $dp), 2, '.', '');
        });
    }

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

    public function setTotalHargaAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['total_harga'] = '0.00';
            $this->attributes['sisa'] = '0.00';
            return;
        }
        $formatted = number_format((float) $value, 2, '.', '');
        $this->attributes['total_harga'] = $formatted;
        $dp = (float) ($this->attributes['dp'] ?? 0);
        $this->attributes['sisa'] = number_format(max(0, (float) $formatted - $dp), 2, '.', '');
    }

    public function getTotalHargaAttribute($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function setDpAttribute($value): void
    {
        $formatted = number_format((float) ($value ?? 0), 2, '.', '');
        $this->attributes['dp'] = $formatted;
        $total = (float) ($this->attributes['total_harga'] ?? 0);
        $this->attributes['sisa'] = number_format(max(0, $total - (float) $formatted), 2, '.', '');
    }

    public function getDpAttribute($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function setSisaAttribute($value): void
    {
        $this->attributes['sisa'] = number_format((float) ($value ?? 0), 2, '.', '');
    }

    public function getSisaAttribute($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function getStatusPembayaranAttribute(): string
    {
        if ((float) $this->sisa <= 0) return 'lunas';
        if ((float) $this->dp > 0) return 'sebagian';
        return 'belum_bayar';
    }

    public function hitungUlangTotalDariKebutuhan(): string
    {
        $sum = (float) $this->kebutuhan()->sum('subtotal');
        $this->attributes['total_harga'] = number_format($sum, 2, '.', '');
        $dp = (float) ($this->attributes['dp'] ?? 0);
        $this->attributes['sisa'] = number_format(max(0, $sum - $dp), 2, '.', '');
        $this->save();
        return (string) $this->attributes['total_harga'];
    }
}
