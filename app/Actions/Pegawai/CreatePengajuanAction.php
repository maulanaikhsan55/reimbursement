<?php

namespace App\Actions\Pegawai;

use App\Enums\PengajuanStatus;
use App\Enums\ValidationStatus;
use App\Models\Pengajuan;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\NotifikasiService;
use App\Services\ValidasiAIService;
use App\Traits\HandlesImageUpload;
use App\Traits\HandlesPengajuanStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreatePengajuanAction
{
    use HandlesImageUpload, HandlesPengajuanStore;

    public function __construct(
        protected ValidasiAIService $validasiAIService,
        protected NotifikasiService $notifikasiService,
        protected AuditTrailService $auditTrailService
    ) {}

    /**
     * Execute the action to create a new pengajuan
     */
    public function execute(User $user, array $validated, $file, ?string $ocrDataJson = null): array
    {
        // 1. Security check: ensure user has atasan (if role is pegawai)
        if ($user->role === 'pegawai' && ! $user->atasan_id) {
            $this->auditTrailService->log(
                event: 'pengajuan.submit_blocked_no_atasan',
                actor: $user,
                description: 'Submit pengajuan ditolak karena pegawai belum memiliki atasan.',
                context: [
                    'user_id' => $user->id,
                    'role' => $user->role,
                ]
            );

            return [
                'success' => false,
                'message' => 'Akun Anda belum terhubung dengan Atasan. Silakan hubungi Finance untuk melakukan setting Atasan.',
            ];
        }

        // 2. Budget Validation (Information Only - Non-Blocking)
        // We let the user submit even if over budget, Finance will review it.
        $isOverBudget = false;
        if ($user->departemen_id) {
            $budgetStatus = Pengajuan::getBudgetStatus($user->departemen_id, $validated['nominal']);
            $isOverBudget = $budgetStatus && $budgetStatus['is_over'];
        }

        return DB::transaction(function () use ($user, $validated, $file, $ocrDataJson, $isOverBudget) {
            // 3. Handle File Upload & Duplicate Check
            $originalName = $file->getClientOriginalName();
            $fileHash = md5_file($file->getRealPath());

            $duplicateCheck = $this->handleFileHashDuplicate($fileHash);
            if ($duplicateCheck['is_duplicate']) {
                return [
                    'success' => false,
                    'message' => $duplicateCheck['message'],
                ];
            }

            $filePath = $this->uploadCompressedFile($file, 'pengajuan');
            $nomorPengajuan = $this->generateNomorPengajuan();
            $standardizedVendor = $this->normalizeVendorName($validated['nama_vendor'] ?? '');

            // 4. Create Initial Record
            $pengajuan = Pengajuan::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'user_id' => $user->id,
                'departemen_id' => $user->departemen_id,
                'kategori_id' => $validated['kategori_id'],
                'tanggal_pengajuan' => now()->toDateString(),
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'judul' => $validated['judul'],
                'nama_vendor' => $standardizedVendor,
                'jenis_transaksi' => 'other',
                'deskripsi' => $validated['deskripsi'],
                'nominal' => $validated['nominal'],
                'file_bukti' => $filePath,
                'file_name' => $originalName,
                'file_hash' => $fileHash,
                'catatan_pegawai' => $validated['catatan_pegawai'] ?? null,
                'status' => PengajuanStatus::VALIDASI_AI,
                'status_validasi' => ValidationStatus::PENDING,
            ]);

            // 5. Capture Initial OCR Data
            if ($ocrDataJson) {
                try {
                    $ocrData = json_decode($ocrDataJson, true);
                    if ($ocrData) {
                        $this->validasiAIService->saveInitialOCR($pengajuan, $ocrData);
                    }
                } catch (\Exception $e) {
                    Log::warning('Action: Failed to save initial OCR: '.$e->getMessage());
                }
            }

            // 6. Run AI Validation
            try {
                $validationResult = $this->validasiAIService->validatePengajuan($pengajuan);

                if (\in_array($validationResult['overall_status'], ['pass', 'warning'])) {
                    $nextStatus = Pengajuan::getInitialStatus($user);

                    $pengajuan->update([
                        'status' => $nextStatus,
                        'status_validasi' => ($validationResult['overall_status'] === 'warning') ? ValidationStatus::INVALID : ValidationStatus::VALID,
                    ]);

                    if ($nextStatus === PengajuanStatus::MENUNGGU_ATASAN->value) {
                        try {
                            $this->notifikasiService->notifyNewPengajuanToAtasan($pengajuan);
                        } catch (\Exception $notifError) {
                            Log::warning('Action: Notification failed: '.$notifError->getMessage());
                        }
                    } elseif ($nextStatus === PengajuanStatus::MENUNGGU_FINANCE->value) {
                        try {
                            $this->notifikasiService->notifyNewPengajuanToFinance($pengajuan);
                        } catch (\Exception $notifError) {
                            Log::warning('Action: Notification failed (Finance): '.$notifError->getMessage());
                        }
                    }

                    $this->auditTrailService->logPengajuan(
                        event: 'pengajuan.submitted',
                        pengajuan: $pengajuan,
                        actor: $user,
                        description: 'Pengajuan berhasil disubmit dan diproses ke tahap berikutnya.',
                        context: [
                            'validation_overall_status' => $validationResult['overall_status'],
                            'status_to' => $nextStatus,
                            'is_over_budget' => $isOverBudget,
                            'nominal' => (float) $validated['nominal'],
                        ]
                    );

                    return [
                        'success' => true,
                        'pengajuan' => $pengajuan,
                        'message' => ($validationResult['overall_status'] === 'warning')
                            ? 'Pengajuan berhasil dikirim dengan peringatan validasi AI.'
                            : 'Pengajuan berhasil dikirim.',
                        'type' => 'success',
                    ];
                } else {
                    // Blocked - Cleanup
                    $failedStatus = $validationResult['overall_status'] ?? 'invalid';
                    Storage::disk('local')->delete($filePath);
                    $pengajuan->validasiAi()->delete();
                    $pengajuan->forceDelete();

                    $this->auditTrailService->log(
                        event: 'pengajuan.validation_blocked',
                        actor: $user,
                        description: 'Pengajuan diblokir karena validasi AI gagal.',
                        context: [
                            'overall_status' => $failedStatus,
                            'nominal' => (float) $validated['nominal'],
                            'tanggal_transaksi' => $validated['tanggal_transaksi'],
                            'vendor' => $validated['nama_vendor'],
                        ]
                    );

                    $errors = [];

                    $nominalStatus = $validationResult['results']['nominal']['status'];
                    $nominalStatusValue = $nominalStatus instanceof ValidationStatus ? $nominalStatus->value : $nominalStatus;
                    if ($nominalStatusValue !== ValidationStatus::VALID->value) {
                        $errors[] = 'Nominal yang Anda input berbeda dengan struk';
                    }

                    $tanggalStatus = $validationResult['results']['tanggal']['status'];
                    $tanggalStatusValue = $tanggalStatus instanceof ValidationStatus ? $tanggalStatus->value : $tanggalStatus;
                    if ($tanggalStatusValue !== ValidationStatus::VALID->value) {
                        $errors[] = 'Tanggal tidak cocok dengan struk';
                    }

                    $vendorStatus = $validationResult['results']['vendor']['status'];
                    $vendorStatusValue = $vendorStatus instanceof ValidationStatus ? $vendorStatus->value : $vendorStatus;
                    if ($vendorStatusValue === ValidationStatus::INVALID->value) {
                        $errors[] = 'Nama vendor tidak cocok';
                    }

                    $errorMsg = '<b>Validasi Gagal:</b><br>';
                    $errorMsg .= implode('<br>', $errors);
                    $errorMsg .= '<br><br><b>💡 Tips:</b><br>';
                    $errorMsg .= '- Pastikan foto struk terang dan tidak buram<br>';
                    $errorMsg .= '- Semua teks di struk terlihat jelas, tidak terpotong<br>';
                    $errorMsg .= '- Input data sesuai persis dengan yang tertera di struk';

                    return [
                        'success' => false,
                        'message' => $errorMsg,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Action: Auto-validation failed: '.$e->getMessage(), [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'exception' => $e,
                ]);
                Storage::disk('local')->delete($filePath);
                $pengajuan->forceDelete();

                $this->auditTrailService->log(
                    event: 'pengajuan.validation_exception',
                    actor: $user,
                    description: 'Terjadi exception saat validasi AI pengajuan.',
                    context: [
                        'error_message' => $e->getMessage(),
                        'nominal' => (float) $validated['nominal'],
                        'tanggal_transaksi' => $validated['tanggal_transaksi'],
                    ]
                );

                return [
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat validasi AI. Silakan coba lagi.',
                ];
            }
        });
    }

    private function normalizeVendorName(string $vendor): string
    {
        $vendor = trim($vendor);
        $vendor = preg_replace('/\s+/', ' ', $vendor ?? '');

        return $vendor ?: '-';
    }
}
