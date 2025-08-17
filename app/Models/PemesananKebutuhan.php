<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_kebutuhan';

    protected $fillable = [
        'pemesanan_id',
        'produk_id',
        'kategori',
        'nama',
        'kuantitas',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'kuantitas' => 'decimal:2',
        'harga'     => 'integer',
        'subtotal'  => 'integer',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function scopeBahanBesi($query)
    {
        return $query->where('kategori', 'bahan_besi');
    }

    public function scopeBahanLainnya($query)
    {
        return $query->where('kategori', 'bahan_lainnya');
    }

    public function scopeJasa($query)
    {
        return $query->where('kategori', 'jasa');
    }

    public function getHargaXKeuntunganAttribute(): int
    {
        $k = (int) optional($this->pemesanan)->keuntungan ?: 1;
        return (int) $this->harga * $k;
    }

    public function getSubtotalXKeuntunganAttribute(): int
    {
        $k = (int) optional($this->pemesanan)->keuntungan ?: 1;
        return (int) round(((float) $this->kuantitas) * (int) $this->harga * $k);
    }

    public function getIsBiayaDasarAttribute(): bool
    {
        return in_array($this->kategori, ['bahan_besi', 'bahan_lainnya'], true);
    }
}
