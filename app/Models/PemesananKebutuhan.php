<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemesananKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_kebutuhan';

    protected $fillable = [
        'pemesanan_id',
        'pemesanan_detail_id', // relasi ke baris detail (nullable)
        'produk_id',
        'kategori',
        'nama',
        'kuantitas',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'kuantitas' => 'float',
        'harga'     => 'integer',
        'subtotal'  => 'integer',
    ];

    /**
     * Relasi ke pesanan induk (level pesanan).
     */
    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    /**
     * Relasi ke baris detail (level produk di dalam pesanan).
     * Null jika kebutuhan ini berada di level pesanan (mis. ongkir).
     */
    public function detail(): BelongsTo
    {
        return $this->belongsTo(PemesananDetail::class, 'pemesanan_detail_id');
    }

    /**
     * Relasi ke produk (opsional, bisa redundant jika sudah via detail->produk).
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /* ===========================
     * Scopes kategori
     * =========================== */
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

    /* ===========================
     * Accessors / Helpers
     * =========================== */

    /**
     * Harga dikalikan faktor keuntungan pesanan.
     */
    public function getHargaXKeuntunganAttribute(): int
    {
        $k = (int) optional($this->pemesanan)->keuntungan ?: 1;
        return (int) $this->harga * $k;
    }

    /**
     * Subtotal (qty * harga) dikalikan faktor keuntungan pesanan.
     */
    public function getSubtotalXKeuntunganAttribute(): int
    {
        $k = (int) optional($this->pemesanan)->keuntungan ?: 1;
        return (int) round(((float) $this->kuantitas) * (int) $this->harga * $k);
    }

    /**
     * True jika kategori merupakan biaya dasar (bahan).
     */
    public function getIsBiayaDasarAttribute(): bool
    {
        return in_array($this->kategori, ['bahan_besi', 'bahan_lainnya'], true);
    }

    /**
     * True jika kebutuhan berada di level pesanan (tidak terikat baris detail).
     */
    public function getIsLevelPesananAttribute(): bool
    {
        return is_null($this->pemesanan_detail_id);
    }
}
