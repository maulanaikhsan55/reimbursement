<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TesseractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OCRController extends Controller
{
    protected TesseractService $tesseractService;

    public function __construct(TesseractService $tesseractService)
    {
        $this->tesseractService = $tesseractService;
        $this->middleware('auth');
    }

    public function processBukti(Request $request)
    {
        try {
            Log::info('OCRController: processBukti request received');

            $request->validate([
                'bukti' => 'required|mimes:jpeg,png,webp,pdf|max:5120',
                'ocr_text' => 'required|string|min:10',
            ], [
                'bukti.required' => 'File bukti wajib diupload',
                'bukti.mimes' => 'Format file harus JPG, PNG, WebP, atau PDF',
                'bukti.max' => 'Ukuran file maksimal 5MB',
                'ocr_text.required' => 'OCR text dari frontend wajib ada',
                'ocr_text.min' => 'Gambar tidak terbaca dengan baik',
            ]);

            $file = $request->file('bukti');
            $ocrText = $request->input('ocr_text');

            $isPdf = $this->tesseractService->isPdfFile($file);
            if ($isPdf) {
                Log::info('OCRController: PDF file detected, skipping OCR processing');

                return response()->json([
                    'success' => true,
                    'message' => 'File PDF diterima. Silakan isi data vendor, nominal, dan tanggal secara manual.',
                    'is_duplicate' => false,
                    'is_pdf' => true,
                    'data' => [
                        'vendor' => '',
                        'nominal' => 0,
                        'tanggal' => '',
                        'confidence_score' => 0,
                        'raw_text' => '[PDF File - Manual Entry Required]',
                    ],
                ]);
            }

            $fileSizeValidation = $this->tesseractService->validateFileSize($file);
            if (! $fileSizeValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $fileSizeValidation['message'],
                ], 422);
            }

            $fileTypeValidation = $this->tesseractService->validateFileType($file);
            if (! $fileTypeValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $fileTypeValidation['message'],
                ], 422);
            }

            Log::info('OCRController: Processing with OCR text', ['text_length' => strlen($ocrText)]);

            $duplicateCheck = $this->tesseractService->checkFileDuplicate($file);

            if ($duplicateCheck['is_duplicate']) {
                Log::warning('OCRController: Duplicate file detected');

                return response()->json([
                    'success' => false,
                    'message' => $duplicateCheck['message'],
                    'is_duplicate' => true,
                ], 400);
            }

            $ocrResult = $this->tesseractService->processReceiptOCR($file, $ocrText);

            if (! $ocrResult['success']) {
                Log::warning('OCRController: OCR processing failed', [
                    'error' => $ocrResult['error'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $ocrResult['error'],
                    'is_duplicate' => false,
                ], 400);
            }

            Log::info('OCRController: OCR processing success', [
                'vendor' => $ocrResult['data']['vendor'] ?? null,
                'nominal' => $ocrResult['data']['nominal'] ?? null,
            ]);

            // Semua file yang berhasil diproses dan tidak duplicate dapat di-submit
            // Detail matching akan divalidasi di frontend dan backend saat submit
            return response()->json([
                'success' => true,
                'can_submit' => true,  // File processed successfully & not duplicate = can proceed
                'message' => 'Struk berhasil divalidasi - Verifikasi data dan submit pengajuan',
                'is_duplicate' => false,
                'data' => [
                    'vendor' => $ocrResult['data']['vendor'] ?? '',
                    'nominal' => $ocrResult['data']['nominal'] ?? 0,
                    'tanggal' => $ocrResult['data']['tanggal'] ?? '',
                    'confidence_score' => $ocrResult['data']['confidence_score'] ?? 0,
                    'raw_text' => $ocrResult['data']['raw_text'] ?? '',
                    'file_hash' => $duplicateCheck['hash'] ?? null,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('OCRController: Validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi file gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('OCRController: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error memproses struk: '.$e->getMessage(),
            ], 500);
        }
    }

    public function validateData(Request $request)
    {
        try {
            Log::info('OCRController: validateData request received');

            $request->validate([
                'ocr_data' => 'required|array',
                'ocr_data.vendor' => 'required|string',
                'ocr_data.nominal' => 'required|numeric',
                'ocr_data.tanggal' => 'required|string',
                'input_data' => 'required|array',
                'input_data.nama_vendor' => 'required|string',
                'input_data.nominal' => 'required|numeric',
                'input_data.tanggal_transaksi' => 'required|string',
            ]);

            $validationResult = $this->tesseractService->validateOCRData(
                $request->input('ocr_data'),
                $request->input('input_data')
            );

            if (! $validationResult['success']) {
                Log::warning('OCRController: Data validation failed', [
                    'error' => $validationResult['error'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validationResult['error'],
                ], 400);
            }

            Log::info('OCRController: Data validation success');

            return response()->json([
                'success' => true,
                'message' => 'Validasi data berhasil',
                'data' => $validationResult['data'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('OCRController: Validation failed', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi input gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('OCRController: Exception occurred', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error validasi: '.$e->getMessage(),
            ], 500);
        }
    }
}
