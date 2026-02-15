<?php

namespace App\Traits;

use App\Models\Pengajuan;
use Carbon\Carbon;

trait HandlesPengajuanStore
{
    /**
     * Check if receipt is too old based on policy
     */
    protected function checkReceiptPolicy($tanggalTransaksi)
    {
        $maxAge = config('reimbursement.policy.max_receipt_age_days', 15);
        $date = Carbon::parse($tanggalTransaksi);

        if ($date->startOfDay()->diffInDays(now()->startOfDay()) > $maxAge) {
            return [
                'allowed' => false,
                'message' => "Pengajuan ditolak: Tanggal transaksi sudah lebih dari $maxAge hari.",
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Handle duplicate file hash logic
     */
    protected function handleFileHashDuplicate($fileHash)
    {
        $duplicate = Pengajuan::where('file_hash', $fileHash)->first();

        if ($duplicate) {
            $statusValue = $duplicate->status instanceof \App\Enums\PengajuanStatus
                ? $duplicate->status->value
                : $duplicate->status;

            if (in_array($statusValue, ['ditolak_atasan', 'ditolak_finance', 'void_accurate'])) {
                // If rejected or void, allow re-use by modifying old hash to free up the original hash
                $duplicate->update(['file_hash' => $fileHash.'_REJECTED_'.time()]);

                return ['is_duplicate' => false];
            }

            return [
                'is_duplicate' => true,
                'message' => 'File bukti ini sudah pernah diajukan sebelumnya (No: '.$duplicate->nomor_pengajuan.').',
            ];
        }

        return ['is_duplicate' => false];
    }

    /**
     * Generate a unique nomor pengajuan with monthly sequence
     */
    protected function generateNomorPengajuan(): string
    {
        $date = now();
        $year = $date->year;
        $month = str_pad($date->month, 2, '0', STR_PAD_LEFT);
        $dateKey = "{$year}-{$month}";

        // Use Atomic Lock to prevent race conditions during sequence generation
        // block(5) will wait for up to 5 seconds for the lock to become available
        return \Cache::lock("pengajuan_no_lock_{$dateKey}", 10)->block(5, function () use ($year, $month, $date) {
            $lastPengajuan = Pengajuan::whereYear('tanggal_pengajuan', $year)
                ->whereMonth('tanggal_pengajuan', $date->month)
                ->orderBy('pengajuan_id', 'desc')
                ->select('nomor_pengajuan')
                ->first();

            $sequence = $lastPengajuan ? ((int) substr($lastPengajuan->nomor_pengajuan, -4)) + 1 : 1;
            $sequenceStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);

            return "PJ{$year}{$month}{$sequenceStr}";
        });
    }
}
