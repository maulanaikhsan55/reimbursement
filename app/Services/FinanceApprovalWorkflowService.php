<?php

namespace App\Services;

use App\Enums\PengajuanStatus;
use App\Enums\ValidationStatus;
use App\Models\COA;
use App\Models\KasBank;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceApprovalWorkflowService
{
    public function __construct(
        protected AccurateService $accurateService,
        protected NotifikasiService $notifikasiService,
        protected AuditTrailService $auditTrailService,
        protected JurnalService $jurnalService,
    ) {}

    public function sendToAccurate(Pengajuan $pengajuan, array $validated, User $actor): array
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return ['success' => false, 'message' => 'Pengajuan tidak dalam status menunggu finance'];
        }

        $coa = COA::findOrFail((int) $validated['coa_id']);
        $kasBank = KasBank::findOrFail((int) $validated['kas_bank_id']);

        $balanceError = $this->getInsufficientBalanceError(
            pengajuan: $pengajuan,
            kasBank: $kasBank,
            actor: $actor,
            event: 'pengajuan.send_blocked_insufficient_balance',
            description: 'Pengiriman ke Accurate diblokir karena saldo kas/bank tidak cukup.',
            kasBankId: (int) $validated['kas_bank_id']
        );
        if ($balanceError !== null) {
            return ['success' => false, 'message' => $balanceError];
        }

        $statusFrom = $pengajuan->status->value;
        $canAutoApprove = $this->isEligibleForAutoApproval($pengajuan);

        $pengajuan->update([
            'coa_id' => $validated['coa_id'],
            'kas_bank_id' => $validated['kas_bank_id'],
            'catatan_finance' => $validated['catatan_finance'] ?? null,
        ]);

        $response = $this->accurateService->sendTransaction(
            $pengajuan,
            $coa->kode_coa,
            $kasBank->kode_kas_bank
        );

        if (! ($response['success'] ?? false)) {
            $this->notifikasiService->notifyFailedToAccurate($pengajuan, $response['error_message'] ?? $response['message']);
            $this->auditTrailService->logPengajuan(
                event: 'pengajuan.failed_to_send_accurate',
                pengajuan: $pengajuan,
                actor: $actor,
                description: 'Finance gagal mengirim pengajuan ke Accurate.',
                context: [
                    'status_from' => $statusFrom,
                    'error_message' => $response['error_message'] ?? $response['message'],
                    'coa_id' => (int) $validated['coa_id'],
                    'kas_bank_id' => (int) $validated['kas_bank_id'],
                ]
            );

            return ['success' => false, 'message' => $response['message']];
        }

        $transactionId = (string) $response['transaction_id'];
        try {
            $this->finalizeSuccessfulSync(
                pengajuan: $pengajuan,
                transactionId: $transactionId,
                actor: $actor,
                statusFrom: $statusFrom,
                strictJournalValidation: true,
                notifyCurrentFinanceUser: true,
                financeSuccessMessage: "Pengajuan #{$pengajuan->nomor_pengajuan} berhasil dikirim ke Accurate dengan ID: {$transactionId}",
                auditEvent: 'pengajuan.sent_to_accurate',
                auditDescription: 'Finance mengirim pengajuan ke Accurate.',
                auditContext: [
                    'coa_id' => (int) $validated['coa_id'],
                    'kas_bank_id' => (int) $validated['kas_bank_id'],
                    'auto_approval_eligible' => $canAutoApprove,
                ]
            );
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'PENGAJUAN_STATUS_CHANGED') {
                return ['success' => false, 'message' => 'Status pengajuan berubah oleh proses lain. Silakan refresh halaman dan cek status terbaru.'];
            }

            throw $e;
        }

        return [
            'success' => true,
            'message' => "Pengajuan berhasil dikirim ke Accurate Online dengan nomor jurnal #{$transactionId} dan jurnal lokal telah dibuat",
        ];
    }

    public function retrySendToAccurate(Pengajuan $pengajuan, User $actor): array
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return ['success' => false, 'message' => 'Pengajuan tidak dapat di-retry'];
        }

        if (! $pengajuan->coa_id || ! $pengajuan->kas_bank_id) {
            return ['success' => false, 'message' => 'COA dan Kas/Bank harus dipilih sebelum retry'];
        }

        $coa = COA::find($pengajuan->coa_id);
        $kasBank = KasBank::find($pengajuan->kas_bank_id);
        if (! $coa || ! $kasBank) {
            return ['success' => false, 'message' => 'Data COA atau Kas/Bank tidak valid'];
        }

        $statusFrom = $pengajuan->status->value;
        $check = $this->accurateService->checkTransactionExists($pengajuan->nomor_pengajuan);
        if (($check['success'] ?? false) && ($check['exists'] ?? false)) {
            $transactionId = (string) ($check['data']['number'] ?? $check['data']['id']);

            $this->finalizeSuccessfulSync(
                pengajuan: $pengajuan,
                transactionId: $transactionId,
                actor: $actor,
                statusFrom: $statusFrom,
                strictJournalValidation: false,
                notifyCurrentFinanceUser: false,
                financeSuccessMessage: '',
                auditEvent: 'pengajuan.retry_synced_existing_accurate',
                auditDescription: 'Retry finance menemukan transaksi sudah ada di Accurate.',
                auditContext: []
            );

            return [
                'success' => true,
                'message' => "Transaksi ternyata sudah ada di Accurate (#{$transactionId}). Status telah disinkronkan.",
            ];
        }

        $balanceError = $this->getInsufficientBalanceError(
            pengajuan: $pengajuan,
            kasBank: $kasBank,
            actor: $actor,
            event: 'pengajuan.retry_blocked_insufficient_balance',
            description: 'Retry ke Accurate diblokir karena saldo kas/bank tidak cukup.',
            kasBankId: (int) $pengajuan->kas_bank_id
        );
        if ($balanceError !== null) {
            return ['success' => false, 'message' => $balanceError];
        }

        $response = $this->accurateService->sendTransaction(
            $pengajuan,
            $coa->kode_coa,
            $kasBank->kode_kas_bank
        );

        if (! ($response['success'] ?? false)) {
            $this->notifikasiService->notifyFailedToAccurate($pengajuan, $response['message']);
            $this->auditTrailService->logPengajuan(
                event: 'pengajuan.retry_failed_to_send_accurate',
                pengajuan: $pengajuan,
                actor: $actor,
                description: 'Retry finance gagal mengirim pengajuan ke Accurate.',
                context: [
                    'status_from' => $statusFrom,
                    'error_message' => $response['message'],
                ]
            );

            return ['success' => false, 'message' => $response['message']];
        }

        $transactionId = (string) $response['transaction_id'];
        try {
            $this->finalizeSuccessfulSync(
                pengajuan: $pengajuan,
                transactionId: $transactionId,
                actor: $actor,
                statusFrom: $statusFrom,
                strictJournalValidation: true,
                notifyCurrentFinanceUser: true,
                financeSuccessMessage: "Pengajuan #{$pengajuan->nomor_pengajuan} berhasil dikirim ke Accurate dengan ID: {$transactionId} (Retry)",
                auditEvent: 'pengajuan.retry_sent_to_accurate',
                auditDescription: 'Retry finance berhasil mengirim pengajuan ke Accurate.',
                auditContext: []
            );
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'PENGAJUAN_STATUS_CHANGED') {
                return ['success' => false, 'message' => 'Status pengajuan berubah oleh proses lain. Silakan refresh halaman dan cek status terbaru.'];
            }

            throw $e;
        }

        return [
            'success' => true,
            'message' => "Retry berhasil. Pengajuan terkirim ke Accurate Online dengan nomor jurnal #{$transactionId} dan jurnal lokal telah dibuat",
        ];
    }

    public function rejectByFinance(Pengajuan $pengajuan, string $catatanFinance, User $actor): array
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return ['success' => false, 'message' => 'Pengajuan tidak dalam status menunggu finance'];
        }

        $pengajuan->update([
            'status' => PengajuanStatus::DITOLAK_FINANCE,
            'disetujui_finance_oleh' => $actor->id,
            'tanggal_disetujui_finance' => now(),
            'catatan_finance' => $catatanFinance,
        ]);

        $this->notifikasiService->notifyRejectedByFinance($pengajuan);
        $this->auditTrailService->logPengajuan(
            event: 'pengajuan.rejected_by_finance',
            pengajuan: $pengajuan,
            actor: $actor,
            description: 'Finance menolak pengajuan.',
            context: [
                'status_from' => PengajuanStatus::MENUNGGU_FINANCE->value,
                'status_to' => $pengajuan->status->value,
                'catatan_finance' => $catatanFinance,
            ]
        );

        return ['success' => true, 'message' => 'Pengajuan berhasil ditolak'];
    }

    private function finalizeSuccessfulSync(
        Pengajuan $pengajuan,
        string $transactionId,
        User $actor,
        string $statusFrom,
        bool $strictJournalValidation,
        bool $notifyCurrentFinanceUser,
        string $financeSuccessMessage,
        string $auditEvent,
        string $auditDescription,
        array $auditContext
    ): void {
        DB::transaction(function () use (
            $pengajuan,
            $transactionId,
            $actor,
            $statusFrom,
            $strictJournalValidation,
            $auditEvent,
            $auditDescription,
            $auditContext
        ) {
            $lockedPengajuan = Pengajuan::query()
                ->whereKey($pengajuan->pengajuan_id)
                ->lockForUpdate()
                ->first();

            if (! $lockedPengajuan || $lockedPengajuan->status?->value !== $statusFrom) {
                throw new \RuntimeException('PENGAJUAN_STATUS_CHANGED');
            }

            $lockedPengajuan->update([
                'status' => PengajuanStatus::TERKIRIM_ACCURATE,
                'accurate_transaction_id' => $transactionId,
                'disetujui_finance_oleh' => $actor->id,
                'tanggal_disetujui_finance' => now(),
            ]);

            $jurnalResult = $this->jurnalService->createJurnalFromPengajuan($lockedPengajuan, $transactionId);
            if ($strictJournalValidation && ! ($jurnalResult['success'] ?? false)) {
                throw new \Exception('Berhasil ke Accurate tapi gagal buat jurnal lokal: '.($jurnalResult['error'] ?? 'unknown'));
            }

            $this->notifikasiService->notifySentToAccurate($lockedPengajuan);

            $this->auditTrailService->logPengajuan(
                event: $auditEvent,
                pengajuan: $lockedPengajuan,
                actor: $actor,
                description: $auditDescription,
                context: array_merge([
                    'status_from' => $statusFrom,
                    'status_to' => $lockedPengajuan->status->value,
                    'accurate_transaction_id' => $transactionId,
                ], $auditContext)
            );
        });

        Cache::forget('finance_disbursement_summary');

        if ($notifyCurrentFinanceUser) {
            $this->notifikasiService->notifyUserImmediate(
                userId: (int) $actor->id,
                pengajuanId: (int) $pengajuan->pengajuan_id,
                tipe: 'success',
                judul: 'Kirim Accurate Berhasil',
                pesan: $financeSuccessMessage
            );
        }
    }

    private function getInsufficientBalanceError(
        Pengajuan $pengajuan,
        KasBank $kasBank,
        User $actor,
        string $event,
        string $description,
        int $kasBankId
    ): ?string {
        if (! $kasBank->accurate_id) {
            return null;
        }

        $balanceResult = $this->accurateService->getAccountBalance($kasBank->accurate_id);
        if (($balanceResult['success'] ?? false) && ($balanceResult['balance'] ?? 0) < $pengajuan->nominal) {
            $this->auditTrailService->logPengajuan(
                event: $event,
                pengajuan: $pengajuan,
                actor: $actor,
                description: $description,
                context: [
                    'kas_bank_id' => $kasBankId,
                    'available_balance' => (float) $balanceResult['balance'],
                    'required_nominal' => (float) $pengajuan->nominal,
                ]
            );

            return 'Gagal: Saldo '.$kasBank->nama_kas_bank.' di Accurate tidak mencukupi (Sisa: Rp '.number_format($balanceResult['balance'], 0, ',', '.').')';
        }

        return null;
    }

    private function isEligibleForAutoApproval(Pengajuan $pengajuan): bool
    {
        try {
            if ($pengajuan->status_validasi !== ValidationStatus::VALID) {
                return false;
            }

            $nominalValidasi = $pengajuan->validasiAi->where('jenis_validasi', 'nominal')->first();
            $tanggalValidasi = $pengajuan->validasiAi->where('jenis_validasi', 'tanggal')->first();

            $nominalScore = $nominalValidasi ? $nominalValidasi->confidence_score : 0;
            $tanggalScore = $tanggalValidasi ? $tanggalValidasi->confidence_score : 0;

            if ($nominalScore < 100 || $tanggalScore < 100) {
                return false;
            }

            if ($pengajuan->departemen) {
                $budgetLimit = (float) ($pengajuan->departemen->budget_limit ?? 0);
                if ($budgetLimit > 0) {
                    $startOfMonth = $pengajuan->tanggal_transaksi->copy()->startOfMonth();
                    $endOfMonth = $pengajuan->tanggal_transaksi->copy()->endOfMonth();

                    $currentMonthUsage = Pengajuan::where('departemen_id', $pengajuan->departemen_id)
                        ->whereBetween('tanggal_transaksi', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                        ->whereNotIn('status', [PengajuanStatus::DITOLAK_ATASAN->value, PengajuanStatus::DITOLAK_FINANCE->value])
                        ->sum('nominal');

                    if ($currentMonthUsage > $budgetLimit) {
                        return false;
                    }
                }
            }

            return $pengajuan->coa_id !== null;
        } catch (\Exception $e) {
            Log::warning('Auto-approval eligibility check failed: '.$e->getMessage());

            return false;
        }
    }
}
