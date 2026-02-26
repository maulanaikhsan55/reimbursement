<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Services\TesseractService;
use App\Services\ValidasiAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class ValidasiAIController extends Controller
{
    protected $tesseractService;

    protected $validasiService;

    /**
     * Constructor - Inject required services
     */
    public function __construct(
        TesseractService $tesseractService,
        ValidasiAIService $validasiService
    ) {
        $this->tesseractService = $tesseractService;
        $this->validasiService = $validasiService;
    }

    /**
     * Process uploaded file: Check duplicate & Run OCR
     *
     * Detects duplicate files by hash and runs Tesseract OCR to extract text.
     * Returns OCR confidence scores and extracted data (nominal, date, vendor).
     * Handles multi-invoice receipts with amount detection.
     *
     * @param  Request  $request  Contains: file_bukti, ocr_text (optional), jenis_transaksi (optional)
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException If file validation fails
     */
    public function processFile(Request $request)
    {
        try {
            $request->validate([
                'file_bukti' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
                'ocr_text' => 'nullable|string',
                'jenis_transaksi' => 'nullable|string',
            ]);

            $file = $request->file('file_bukti');
            $ocrText = (string) $request->input('ocr_text', '');
            $jenisTransaksi = $request->input('jenis_transaksi');

            Log::info('ValidasiAI: Processing file', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'jenis_transaksi' => $jenisTransaksi,
            ]);

            // 1. Check for exact file hash duplicate (quick check)
            $hashCheckResult = $this->tesseractService->checkFileDuplicate($file);

            if ($hashCheckResult['is_duplicate'] && $hashCheckResult['type'] === 'hash_match') {
                return response()->json([
                    'success' => true,
                    'is_duplicate' => true,
                    'message' => $hashCheckResult['message'] ?? 'File ini sudah pernah diupload sebelumnya.',
                    'file_hash' => $hashCheckResult['hash'] ?? null,
                    'ocr_data' => null,
                ]);
            }

            // 2. Run OCR (using Tesseract/Groq)
            $ocrResult = $this->tesseractService->processReceiptOCR($file, $ocrText, $jenisTransaksi);

            // 2.5. Check file quality (blur, resolution, etc.)
            $qualityCheck = $this->assessFileQuality($file);

            if (! $ocrResult['success']) {
                Log::warning('OCR processing failed', ['error' => $ocrResult['error'] ?? 'Unknown']);

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi AI OCR gagal. Silakan upload ulang dokumen yang lebih jelas.',
                    'ocr_data' => null,
                    'file_quality' => $qualityCheck,
                ], 200);
            }

            // 3. Check for three-criteria duplicate (file_name + nominal + vendor + tanggal)
            $duplicateResult = $this->tesseractService->checkFileDuplicate($file, $ocrResult['data']);

            if ($duplicateResult['is_duplicate']) {
                return response()->json([
                    'success' => true,
                    'is_duplicate' => true,
                    'message' => 'File dengan vendor, nominal, dan tanggal yang sama sudah pernah diajukan.',
                    'ocr_data' => null,
                    'file_quality' => $qualityCheck,
                ]);
            }

            $ocrData = $ocrResult['data'];
            $multiInvoiceInfo = null;

            if (isset($ocrData['all_detected_totals']) && ! empty($ocrData['all_detected_totals'])) {
                $multiInvoiceInfo = $this->validasiService->extractMultipleInvoiceAmounts($ocrData['all_detected_totals']);
            }

            return response()->json([
                'success' => true,
                'is_duplicate' => false,
                'ocr_data' => $ocrData,
                'multi_invoice' => $multiInvoiceInfo,
                'message' => 'File berhasil diproses',
                'file_quality' => $qualityCheck,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.$e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('ValidasiAI Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate user input against OCR-extracted data
     *
     * Compares user-submitted values (nominal, vendor, date) with OCR results.
     * Delegates validation to the service layer for consistency.
     */
    public function validateInput(Request $request)
    {
        try {
            $request->validate([
                'ocr_data' => 'required|array',
                'nama_vendor' => 'required|string',
                'nominal' => 'required',
                'tanggal_transaksi' => 'required|date',
                'jenis_transaksi' => 'nullable|string',
            ]);

            $ocrData = $request->input('ocr_data');
            $inputData = $request->only(['nama_vendor', 'nominal', 'tanggal_transaksi', 'jenis_transaksi']);

            // Delegate all validation logic to the Service Layer for consistency
            $validationResult = $this->validasiService->validateManualInput(
                $ocrData,
                $inputData,
                auth()->id()
            );

            return response()->json($validationResult);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.$e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('ValidasiAI Input Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan validasi: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assess file image quality (blur, contrast, lighting)
     *
     * Returns quality score 0-100 and recommendations.
     * Used to warn pegawai if file quality is poor for OCR accuracy.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array ['quality_score' => int, 'quality_label' => string, 'warnings' => array]
     */
    private function assessFileQuality($file): array
    {
        try {
            $mimeType = $file->getMimeType();

            // Skip PDF quality check (can't assess easily without rendering)
            if (strpos($mimeType, 'pdf') !== false) {
                return [
                    'quality_score' => null,
                    'quality_label' => 'pdf',
                    'warnings' => [],
                ];
            }

            // For images, analyze using getimagesize
            $filePath = $file->getPathname();
            $imageInfo = @getimagesize($filePath);

            if (! $imageInfo) {
                return [
                    'quality_score' => null,
                    'quality_label' => 'unknown',
                    'warnings' => ['Tidak dapat menganalisis kualitas file gambar'],
                ];
            }

            $warnings = [];
            $qualityScore = 100;

            // 1. Check resolution (minimum 300x300 for decent OCR)
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            if ($width < 300 || $height < 300) {
                $warnings[] = 'Resolusi gambar terlalu rendah (< 300x300px) - dapat mempengaruhi akurasi OCR';
                $qualityScore -= 20;
            } elseif ($width < 600 || $height < 600) {
                $warnings[] = 'Resolusi gambar cukup rendah (< 600x600px) - akurasi OCR mungkin berkurang';
                $qualityScore -= 10;
            }

            // 2. Check file size (if very small, might be compressed/low quality)
            $fileSizeKB = $file->getSize() / 1024;
            if ($fileSizeKB < 50 && ($width < 800 || $height < 800)) {
                $warnings[] = 'File berukuran kecil dengan resolusi rendah - kualitas mungkin kurang baik';
                $qualityScore -= 15;
            }

            // 3. Check aspect ratio (if too extreme, might be screenshot or stretched)
            $aspectRatio = $width / $height;
            if ($aspectRatio > 3 || $aspectRatio < 0.33) {
                $warnings[] = 'Rasio aspek gambar tidak normal - mungkin cropped atau stretched';
                $qualityScore -= 10;
            }

            $qualityScore = max(0, min(100, $qualityScore));

            $qualityLabel = match (true) {
                $qualityScore >= 90 => 'excellent',
                $qualityScore >= 75 => 'good',
                $qualityScore >= 60 => 'fair',
                default => 'poor'
            };

            return [
                'quality_score' => $qualityScore,
                'quality_label' => $qualityLabel,
                'warnings' => $warnings,
            ];

        } catch (\Exception $e) {
            Log::debug('File quality assessment error: '.$e->getMessage());

            return [
                'quality_score' => null,
                'quality_label' => 'unknown',
                'warnings' => [],
            ];
        }
    }
}
