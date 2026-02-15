<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $departemenIT = Departemen::where('kode_departemen', 'IT')->first();
        $departemenHR = Departemen::where('kode_departemen', 'HR')->first();
        $departemenFIN = Departemen::where('kode_departemen', 'FIN')->first();

        if ($departemenFIN) {
            User::firstOrCreate(
                ['email' => 'finance@humplus.id'],
                [
                    'name' => 'Finance Admin',
                    'email' => 'finance@humplus.id',
                    'password' => Hash::make('password123'),
                    'role' => 'finance',
                    'departemen_id' => $departemenFIN->departemen_id,
                    'is_active' => true,
                ]
            );
        }

        if ($departemenIT) {
            $kepalaBudget = User::firstOrCreate(
                ['email' => 'kepala.it@humplus.id'],
                [
                    'name' => 'Kepala IT',
                    'email' => 'kepala.it@humplus.id',
                    'password' => Hash::make('password123'),
                    'role' => 'atasan',
                    'departemen_id' => $departemenIT->departemen_id,
                    'atasan_id' => null,
                    'is_active' => true,
                ]
            );

            User::firstOrCreate(
                ['email' => 'budi@humplus.id'],
                [
                    'name' => 'Budi Santoso',
                    'email' => 'budi@humplus.id',
                    'password' => Hash::make('password123'),
                    'role' => 'pegawai',
                    'departemen_id' => $departemenIT->departemen_id,
                    'atasan_id' => $kepalaBudget->id,
                    'nomor_telepon' => '081234567890',
                    'nama_bank' => 'BCA',
                    'nomor_rekening' => '1234567890',
                    'is_active' => true,
                ]
            );

            User::firstOrCreate(
                ['email' => 'ani@humplus.id'],
                [
                    'name' => 'Ani Wijaya',
                    'email' => 'ani@humplus.id',
                    'password' => Hash::make('password123'),
                    'role' => 'pegawai',
                    'departemen_id' => $departemenIT->departemen_id,
                    'atasan_id' => $kepalaBudget->id,
                    'nomor_telepon' => '081234567891',
                    'nama_bank' => 'Mandiri',
                    'nomor_rekening' => '0987654321',
                    'is_active' => true,
                ]
            );
        }

        if ($departemenHR) {
            User::firstOrCreate(
                ['email' => 'citra@humplus.id'],
                [
                    'name' => 'Citra Dewi',
                    'email' => 'citra@humplus.id',
                    'password' => Hash::make('password123'),
                    'role' => 'pegawai',
                    'departemen_id' => $departemenHR->departemen_id,
                    'atasan_id' => null,
                    'nomor_telepon' => '081234567892',
                    'nama_bank' => 'BNI',
                    'nomor_rekening' => '1122334455',
                    'is_active' => true,
                ]
            );
        }
    }
}
