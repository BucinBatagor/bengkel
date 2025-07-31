<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Hash;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        Pelanggan::create([
            'name' => 'Nizar Khawarizmi',
            'email' => 'nizar.banme@gmail.com',
            'phone' => '089519599386',
            'address' => 'Jalan Kom Yos Sudarso',
            'password' => Hash::make('123456'),
        ]);
    }
}
