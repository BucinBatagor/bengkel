<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukGambar extends Model
{
    protected $table = 'produk_gambar'; // ✅ tambahkan ini

    protected $fillable = ['produk_id', 'gambar']; // ✅ ini juga penting

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
