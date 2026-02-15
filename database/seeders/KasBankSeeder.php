<?php

namespace Database\Seeders;

use App\Models\KasBank;
use Illuminate\Database\Seeder;

class KasBankSeeder extends Seeder
{
    public function run(): void
    {
        $kasBanks = [
            [
                'kode_kas_bank' => 'KAS-BCA-01',
                'nama_kas_bank' => 'BCA - Rekening Operasional',
                'currency_code' => 'IDR',
                'is_active' => true,
                'coa_id' => 1,
            ],
            [
                'kode_kas_bank' => 'KAS-MANDIRI',
                'nama_kas_bank' => 'Mandiri - Kas Kecil',
                'currency_code' => 'IDR',
                'is_active' => true,
                'coa_id' => 2,
            ],
            [
                'kode_kas_bank' => 'KAS-BRI',
                'nama_kas_bank' => 'BRI - Petty Cash',
                'currency_code' => 'IDR',
                'is_active' => true,
                'coa_id' => 1,
            ],
        ];

        foreach ($kasBanks as $kasBank) {
            KasBank::create($kasBank);
        }
    }
}
