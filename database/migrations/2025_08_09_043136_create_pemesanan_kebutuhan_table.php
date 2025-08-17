<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan_kebutuhan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('produk')->nullOnDelete()->cascadeOnUpdate();
            $table->enum('kategori', ['bahan_besi', 'bahan_lainnya', 'jasa'])->index();
            $table->string('nama');
            $table->decimal('kuantitas', 10, 2)->default(0);
            $table->unsignedBigInteger('harga')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
            $table->index(['pemesanan_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_kebutuhan');
    }
};
