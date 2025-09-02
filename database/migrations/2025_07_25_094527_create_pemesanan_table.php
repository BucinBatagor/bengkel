<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->enum('status', [
                'butuh_cek_ukuran',
                'batal',
                'belum_bayar',
                'gagal',
                'di_proses',
                'dikerjakan',
                'selesai',
                'pengembalian_dana',
                'pengembalian_selesai',
            ])->default('butuh_cek_ukuran')->index();
            $table->unsignedInteger('keuntungan')->default(3);
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->decimal('dp', 12, 2)->default(0);
            $table->decimal('sisa', 12, 2)->default(0);
            $table->string('snap_token', 64)->nullable()->index();
            $table->timestamp('payment_expire_at')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
