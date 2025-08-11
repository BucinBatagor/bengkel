<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PemesananKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_kebutuhan';

    protected $fillable = [
        'pemesanan_id',
        'pelanggan_id',
        'produk_id',
        'kategori',
        'nama',
        'kuantitas',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'kuantitas' => 'decimal:2',
        'harga' => 'integer',
        'subtotal' => 'integer',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function scopeBahanBesi($query)
    {
        return $query->where('kategori', 'bahan_besi');
    }

    public function getHargaX3Attribute(): int
    {
        return (int) $this->harga * 3;
    }

    public function getSubtotalX3Attribute(): int
    {
        return (int) round(((float) $this->kuantitas) * (int) $this->harga * 3);
    }
}
