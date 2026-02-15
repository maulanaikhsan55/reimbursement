<?php

namespace Database\Seeders;

use App\Models\Coa;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode_coa' => '1001',
                'nama_coa' => 'BCA - Current Account',
                'tipe_akun' => 'asset',
                'is_active' => true,
            ],
            [
                'kode_coa' => '1002',
                'nama_coa' => 'Mandiri - Current Account',
                'tipe_akun' => 'asset',
                'is_active' => true,
            ],
            [
                'kode_coa' => '6101',
                'nama_coa' => 'Biaya Transport',
                'tipe_akun' => 'expense',
                'is_active' => true,
            ],
            [
                'kode_coa' => '6102',
                'nama_coa' => 'Biaya Konsumsi & Makan',
                'tipe_akun' => 'expense',
                'is_active' => true,
            ],
            [
                'kode_coa' => '6103',
                'nama_coa' => 'Biaya Tiket & Akomodasi',
                'tipe_akun' => 'expense',
                'is_active' => true,
            ],
            [
                'kode_coa' => '6104',
                'nama_coa' => 'Biaya Internet & Telepon',
                'tipe_akun' => 'expense',
                'is_active' => true,
            ],
            [
                'kode_coa' => '1501',
                'nama_coa' => 'Aset Tetap - Peralatan',
                'tipe_akun' => 'asset',
                'is_active' => true,
            ],
            [
                'kode_coa' => '6105',
                'nama_coa' => 'Biaya Operasional Lainnya',
                'tipe_akun' => 'expense',
                'is_active' => true,
            ],
        ];

        foreach ($data as $item) {
            Coa::updateOrCreate(
                ['kode_coa' => $item['kode_coa']],
                $item
            );
        }
    }
}
