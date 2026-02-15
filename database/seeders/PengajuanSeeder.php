<?php

namespace Database\Seeders;

use App\Models\KategoriBiaya;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = User::where('role', 'pegawai')->get();
        $atasans = User::where('role', 'atasan')->get();
        $kategoris = KategoriBiaya::all();

        if ($pegawais->isEmpty() || $atasans->isEmpty() || $kategoris->isEmpty()) {
            $this->command->info('Data User atau Kategori Biaya belum lengkap. Skip seeding pengajuan.');

            return;
        }

        // 1. Pegawai -> Menunggu Atasan
        foreach ($pegawais as $index => $pegawai) {
            $this->createPengajuan(
                $pegawai,
                'menunggu_atasan',
                'Reimbursement Operasional '.($index + 1),
                $kategoris
            );
        }

        // 2. Pegawai -> Menunggu Finance (Sudah diapprove Atasan)
        foreach ($pegawais as $index => $pegawai) {
            // Cari atasan pegawai ini
            $atasan = User::find($pegawai->atasan_id);
            if (! $atasan) {
                continue;
            }

            $this->createPengajuan(
                $pegawai,
                'menunggu_finance',
                'Reimbursement Meeting Luar Kota '.($index + 1),
                $kategoris,
                $atasan
            );
        }

        // 3. Atasan -> Menunggu Finance (Bypass Logic)
        foreach ($atasans as $index => $atasan) {
            $this->createPengajuan(
                $atasan,
                'menunggu_finance',
                'Reimbursement Managerial '.($index + 1),
                $kategoris
            );
        }

        // 4. Selesai (History)
        if ($pegawais->first()) {
            $this->createPengajuan(
                $pegawais->first(),
                'dicairkan',
                'Reimbursement Bulan Lalu',
                $kategoris,
                User::find($pegawais->first()->atasan_id),
                true // isPaid
            );
        }
    }

    private function createPengajuan($user, $status, $keterangan, $kategoris, $approver = null, $isPaid = false)
    {
        $date = Carbon::now()->subDays(rand(1, 10));
        $kategori = $kategoris->random();
        $nominal = rand(50000, 500000);

        Pengajuan::create([
            'nomor_pengajuan' => 'RMB-'.date('Ymd').'-'.rand(1000, 9999),
            'user_id' => $user->id,
            'departemen_id' => $user->departemen_id,
            'kategori_id' => $kategori->kategori_id,
            'tanggal_pengajuan' => $date,
            'tanggal_transaksi' => $date->copy()->subDays(rand(1, 5)),
            'nama_vendor' => 'Vendor '.rand(1, 10),
            'deskripsi' => $keterangan.' - '.$kategori->nama_kategori,
            'nominal' => $nominal,
            'file_bukti' => 'dummy_receipt.jpg',
            'status_validasi' => 'pending',
            'catatan_pegawai' => $keterangan,
            'status' => $status,
            'disetujui_atasan_oleh' => $approver ? $approver->id : null,
            'tanggal_disetujui_atasan' => $approver ? $date->copy()->addHour() : null,
            'disetujui_finance_oleh' => $isPaid ? 1 : null, // Asumsi ID 1 admin/finance
            'tanggal_disetujui_finance' => $isPaid ? $date->copy()->addHours(2) : null,
            'tanggal_pencairan' => $isPaid ? $date->copy()->addDays(1) : null,
        ]);
    }
}
