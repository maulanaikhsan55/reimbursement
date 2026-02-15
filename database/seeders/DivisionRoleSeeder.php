<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DivisionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a division
        $departemen = Departemen::firstOrCreate(
            ['kode_departemen' => 'FIN'],
            [
                'nama_departemen' => 'Finance',
                'deskripsi' => 'Departemen Keuangan',
            ]
        );

        // Create Atasan
        $atasan = User::updateOrCreate(
            ['email' => 'atasan.fin@example.com'],
            [
                'name' => 'Atasan Finance',
                'password' => Hash::make('password123'),
                'role' => 'atasan',
                'departemen_id' => $departemen->departemen_id,
                'is_active' => true,
            ]
        );

        // Create Pegawai (under the atasan)
        User::updateOrCreate(
            ['email' => 'pegawai.fin@example.com'],
            [
                'name' => 'Pegawai Finance',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
                'departemen_id' => $departemen->departemen_id,
                'atasan_id' => $atasan->id,
                'is_active' => true,
            ]
        );

        // Create Finance Role (in the same division)
        User::updateOrCreate(
            ['email' => 'finance.fin@example.com'],
            [
                'name' => 'Staff Finance',
                'password' => Hash::make('password123'),
                'role' => 'finance',
                'departemen_id' => $departemen->departemen_id,
                'is_active' => true,
            ]
        );
    }
}
