<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->nullable()
                ->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('nama_produk');
            $table->decimal('panjang', 8, 2)->nullable();
            $table->decimal('lebar', 8, 2)->nullable();
            $table->decimal('tinggi', 8, 2)->nullable();
            $table->decimal('harga', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_detail');
    }
};
