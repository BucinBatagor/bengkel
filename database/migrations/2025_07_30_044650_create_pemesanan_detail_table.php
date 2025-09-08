<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')
                ->constrained('pelanggan')
                ->cascadeOnDelete();
            $table->foreignId('pemesanan_id')
                ->nullable()
                ->constrained('pemesanan')
                ->cascadeOnDelete();
            $table->foreignId('produk_id')
                ->nullable()
                ->constrained('produk')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('nama_produk');
            $table->unsignedInteger('jumlah')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_detail');
    }
};
