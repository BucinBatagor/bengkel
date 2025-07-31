<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->enum('status', [
                'pending',
                'menunggu',
                'dikerjakan',
                'selesai',
                'gagal',
                'menunggu_refund',
                'refund_diterima'
            ])->default('pending');
            $table->decimal('total_harga', 12, 2);
            $table->string('snap_token')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
