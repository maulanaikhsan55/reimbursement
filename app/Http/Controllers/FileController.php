<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Services\AuditTrailService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        protected AuditTrailService $auditTrailService
    ) {}

    /**
     * Serve private reimbursement proof files with authorization
     */
    public function showProof(Request $request, Pengajuan $pengajuan): StreamedResponse
    {
        $this->authorize('viewProof', $pengajuan);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $disk->exists($pengajuan->file_bukti)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        $mimeType = $disk->mimeType($pengajuan->file_bukti) ?: 'application/octet-stream';
        $originalName = basename($pengajuan->file_bukti);
        $isDownload = $request->boolean('download');

        if ($isDownload) {
            $downloadName = $this->resolveDownloadName($request->query('filename'), $originalName, $pengajuan->pengajuan_id);
            $this->auditTrailService->logPengajuan(
                event: 'pengajuan.proof_downloaded',
                pengajuan: $pengajuan,
                actor: $request->user(),
                description: 'Bukti transaksi diunduh oleh user.',
                context: [
                    'download_name' => $downloadName,
                    'mime_type' => $mimeType,
                ]
            );

            return $disk->download(
                $pengajuan->file_bukti,
                $downloadName,
                [
                    'Content-Type' => $mimeType,
                ]
            );
        }

        $this->auditTrailService->logPengajuan(
            event: 'pengajuan.proof_previewed',
            pengajuan: $pengajuan,
            actor: $request->user(),
            description: 'Bukti transaksi dibuka (preview inline).',
            context: [
                'file_name' => $originalName,
                'mime_type' => $mimeType,
            ]
        );

        return $disk->download(
            $pengajuan->file_bukti,
            $originalName,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.$originalName.'"',
            ]
        );
    }

    private function resolveDownloadName(?string $requestedName, string $originalName, $pengajuanId): string
    {
        $cleanRequested = $this->sanitizeFileName($requestedName ?? '');
        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'dat';

        if ($cleanRequested === '' || $cleanRequested === 'bukti-transaksi' || $cleanRequested === 'bukti_transaksi') {
            $timestamp = now(config('app.timezone', 'Asia/Jakarta'))->format('Ymd_Hisv');
            $token = strtolower(substr((string) Str::ulid(), -4));
            $safeId = $this->sanitizeFileName((string) $pengajuanId) ?: 'unknown';

            return "bukti-transaksi_{$safeId}_{$timestamp}_{$token}.{$extension}";
        }

        $requestedBaseName = pathinfo($cleanRequested, PATHINFO_FILENAME);
        $requestedBaseName = $this->sanitizeFileName($requestedBaseName);

        if ($requestedBaseName === '') {
            $requestedBaseName = 'bukti-transaksi';
        }

        return "{$requestedBaseName}.{$extension}";
    }

    private function sanitizeFileName(string $value): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9._-]+/', '_', trim($value)) ?? '';
        $clean = preg_replace('/_+/', '_', $clean) ?? '';
        $clean = trim($clean, '_');

        return substr($clean, 0, 120);
    }
}
