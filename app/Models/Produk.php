<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'harga',
    ];

    public function gambar()
    {
        return $this->hasMany(ProdukGambar::class);
    }
}
