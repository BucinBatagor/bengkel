<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PemesananDetail extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_detail';

    protected $fillable = [
        'pelanggan_id',
        'pemesanan_id',
        'produk_id',
        'nama_produk',
        'jumlah',
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    /**
     * Relasi ke pesanan induk.
     */
    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    /**
     * Relasi ke pelanggan (pemilik keranjang / pembuat pesanan).
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Relasi ke produk yang dipilih pada baris detail.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Kebutuhan (bahan/jasa) yang terkait khusus ke baris detail ini.
     * Catatan: FK memakai nullOnDelete di migration.
     */
    public function kebutuhan(): HasMany
    {
        return $this->hasMany(PemesananKebutuhan::class, 'pemesanan_detail_id');
    }
}
