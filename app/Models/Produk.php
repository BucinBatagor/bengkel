<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama',
        'kategori_id',
        'kategori',
        'deskripsi',
    ];

    public function kategoriRel()
    {
        return $this->belongsTo(\App\Models\Kategori::class, 'kategori_id');
    }

    public function gambar()
    {
        return $this->hasMany(\App\Models\ProdukGambar::class, 'produk_id');
    }

    protected static function booted()
    {
        static::saving(function (self $produk) {
            if ($produk->kategori_id && empty($produk->kategori)) {
                $produk->kategori = \App\Models\Kategori::whereKey($produk->kategori_id)->value('nama') ?? $produk->kategori;
            }
        });
    }
}
