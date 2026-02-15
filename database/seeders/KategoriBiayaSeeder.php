<?php

namespace Database\Seeders;

use App\Models\KategoriBiaya;
use Illuminate\Database\Seeder;

class KategoriBiayaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode_kategori' => 'TRANSPORT',
                'nama_kategori' => 'Transport',
                'deskripsi' => 'Biaya transportasi (Grab, Gojek, Bensin, Tiket, dll)',
                'is_active' => true,
            ],
            [
                'kode_kategori' => 'KONSUMSI',
                'nama_kategori' => 'Konsumsi',
                'deskripsi' => 'Biaya makan, minum, kopi',
                'is_active' => true,
            ],
            [
                'kode_kategori' => 'TIKET',
                'nama_kategori' => 'Tiket & Akomodasi',
                'deskripsi' => 'Biaya tiket pesawat, kereta, hotel, dll',
                'is_active' => true,
            ],
            [
                'kode_kategori' => 'TAGIHAN',
                'nama_kategori' => 'Tagihan',
                'deskripsi' => 'Biaya internet, listrik, telepon, dll',
                'is_active' => true,
            ],
            [
                'kode_kategori' => 'ASSET',
                'nama_kategori' => 'Asset',
                'deskripsi' => 'Pembelian aset perusahaan',
                'is_active' => true,
            ],
            [
                'kode_kategori' => 'OPERASIONAL',
                'nama_kategori' => 'Operasional',
                'deskripsi' => 'Biaya operasional lainnya',
                'is_active' => true,
            ],
        ];

        foreach ($data as $item) {
            KategoriBiaya::updateOrCreate(
                ['kode_kategori' => $item['kode_kategori']],
                $item
            );
        }
    }
}
