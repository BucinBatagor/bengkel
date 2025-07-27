<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePemesananTable extends Migration
{
    public function up()
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('order_id')->unique();
            $table->decimal('total_harga', 12, 2);
            $table->decimal('panjang', 8, 2);
            $table->decimal('lebar', 8, 2);
            $table->decimal('tinggi', 8, 2);
            $table->enum('status', ['diproses', 'dikerjakan', 'selesai', 'dibatalkan'])->default('diproses');
            $table->string('snap_token')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pemesanan');
    }
}
