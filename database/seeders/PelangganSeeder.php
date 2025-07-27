<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Hash;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Ahmad', 'email' => 'ahmad@example.com'],
            ['name' => 'Siti', 'email' => 'siti@example.com'],
            ['name' => 'Budi', 'email' => 'budi@example.com'],
            ['name' => 'Dewi', 'email' => 'dewi@example.com'],
            ['name' => 'Rizal', 'email' => 'rizal@example.com'],
            ['name' => 'Nina', 'email' => 'nina@example.com'],
            ['name' => 'Dian', 'email' => 'dian@example.com'],
            ['name' => 'Tono', 'email' => 'tono@example.com'],
            ['name' => 'Fitri', 'email' => 'fitri@example.com'],
            ['name' => 'Arif', 'email' => 'arif@example.com'],
        ];

        foreach ($data as $i => $user) {
            Pelanggan::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => '08123' . rand(100000, 999999),
                'address' => 'Alamat ' . $user['name'],
                'password' => Hash::make('password123'),
            ]);
        }
    }
}
