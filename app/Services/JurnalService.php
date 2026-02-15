<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JurnalService
{
    public function createJurnalFromPengajuan(Pengajuan $pengajuan, ?string $refNumber = null): array
    {
        try {
            return DB::transaction(function () use ($pengajuan, $refNumber) {
                $pengajuan->load('kasBank');
                $entries = [];
                $totalPosted = 0;

                // Create Debit Entry (Expense)
                $debitEntry = $this->createEntry($pengajuan, $refNumber, 'debit');
                if ($debitEntry) {
                    $entries[] = $debitEntry;
                    $totalPosted += $debitEntry->nominal;
                }

                // Create Credit Entry (Cash/Bank)
                $creditEntry = $this->createEntry($pengajuan, $refNumber, 'credit');
                if ($creditEntry) {
                    $entries[] = $creditEntry;
                }

                // Balance Validation
                $totalDebit = collect($entries)->where('tipe_posting', 'debit')->sum('nominal');
                $totalCredit = collect($entries)->where('tipe_posting', 'credit')->sum('nominal');

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new \Exception("Journal entries are not balanced (Debit: $totalDebit, Credit: $totalCredit)");
                }

                Log::info('Jurnal entries created', [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'total_entries' => count($entries),
                    'total_amount' => $totalPosted,
                ]);

                return [
                    'success' => true,
                    'entries' => $entries,
                    'total_amount' => $totalPosted,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to create Jurnal entries', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function createEntry(Pengajuan $pengajuan, ?string $refNumber, string $tipoPosting = 'debit'): ?Jurnal
    {
        if ($tipoPosting === 'debit') {
            if (! $pengajuan->coa_id) {
                Log::warning('COA not assigned for pengajuan', [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                ]);

                return null;
            }
            $coa_id = $pengajuan->coa_id;
        } else {
            if (! $pengajuan->kasBank || ! $pengajuan->kasBank->coa_id) {
                Log::warning('Kas/Bank COA not assigned for pengajuan', [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                ]);

                return null;
            }
            $coa_id = $pengajuan->kasBank->coa_id;
        }

        $nomor_ref = $refNumber ?? 'RB-'.now()->format('Y-m-d').'-'.str_pad($pengajuan->pengajuan_id, 6, '0', STR_PAD_LEFT);

        // Format deskripsi: [Deskripsi] - [Nama] ([Departemen])
        $userDisplay = $pengajuan->user->name ?? 'Unknown';
        $deptDisplay = $pengajuan->departemen ? " ({$pengajuan->departemen->nama_departemen})" : '';
        $fullDescription = ($pengajuan->deskripsi ?? 'Biaya reimbursement')." - {$userDisplay}{$deptDisplay}";

        $entry = Jurnal::create([
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'coa_id' => $coa_id,
            'nominal' => $pengajuan->nominal,
            'tipe_posting' => $tipoPosting,
            'tanggal_posting' => $pengajuan->tanggal_pencairan ?? now()->toDateString(),
            'nomor_ref' => $nomor_ref,
            'deskripsi' => $fullDescription,
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);

        return $entry;
    }

    public function getJurnalByPengajuan(Pengajuan $pengajuan)
    {
        return Jurnal::where('pengajuan_id', $pengajuan->pengajuan_id)
            ->with('coa', 'postedBy')
            ->get();
    }
}
