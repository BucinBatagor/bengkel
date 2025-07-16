<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('users', 'user');
        Schema::rename('admins', 'admin');
    }

    public function down(): void
    {
        Schema::rename('user', 'users');
        Schema::rename('admin', 'admins');
    }
};
