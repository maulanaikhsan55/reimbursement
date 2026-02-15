<?php

namespace Database\Seeders;

use App\Models\Departemen;
use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode_departemen' => 'IT',
                'nama_departemen' => 'Information Technology',
                'deskripsi' => 'Departemen Teknologi Informasi',
            ],
            [
                'kode_departemen' => 'HR',
                'nama_departemen' => 'Human Resources',
                'deskripsi' => 'Departemen Sumber Daya Manusia',
            ],
            [
                'kode_departemen' => 'FIN',
                'nama_departemen' => 'Finance',
                'deskripsi' => 'Departemen Keuangan',
            ],
            [
                'kode_departemen' => 'SALES',
                'nama_departemen' => 'Sales & Marketing',
                'deskripsi' => 'Departemen Penjualan dan Pemasaran',
            ],
            [
                'kode_departemen' => 'OPS',
                'nama_departemen' => 'Operations',
                'deskripsi' => 'Departemen Operasional',
            ],
        ];

        foreach ($data as $item) {
            Departemen::updateOrCreate(
                ['kode_departemen' => $item['kode_departemen']],
                $item
            );
        }
    }
}
