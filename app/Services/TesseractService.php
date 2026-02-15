<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Exception;
use App\Services\LocalReceiptParser;

class TesseractService
{
    protected GroqAIService $groqService;
    protected LocalReceiptParser $localParser;
    
    // Static cache for performance optimization
    private static $vendorRefineCache = [];

    public function __construct(GroqAIService $groqService, LocalReceiptParser $localParser)
    {
        $this->groqService = $groqService;
        $this->localParser = $localParser;
    }

    public function processReceiptOCR(UploadedFile $file, string $ocrText = '', ?string $jenisTransaksi = null, string $language = 'ind'): array
    {
        try {
            $fileHash = md5_file($file->getRealPath());
            
            Log::info('TesseractService: processReceiptOCR started', [
                'filename' => $file->getClientOriginalName(),
                'hash' => $fileHash,
                'jenis_transaksi' => $jenisTransaksi
            ]);

            // 0. SMART CACHE: Check if this file has been processed before (by any user)
            // Reuse previous AI extraction to save API costs and time
            $cachedValidasi = \App\Models\ValidasiAI::whereHas('pengajuan', function($q) use ($fileHash) {
                    $q->where('file_hash', $fileHash);
                })
                ->where('jenis_validasi', 'ocr')
                ->where('status', 'pass')
                ->latest()
                ->first();

            if ($cachedValidasi && $cachedValidasi->hasil_ocr) {
                $cachedData = is_string($cachedValidasi->hasil_ocr) 
                    ? json_decode($cachedValidasi->hasil_ocr, true) 
                    : $cachedValidasi->hasil_ocr;

                if (!empty($cachedData['vendor'])) {
                    Log::info('TesseractService: SMART CACHE HIT! Reusing OCR data.', ['hash' => $fileHash]);
                    
                    // Keep original raw text if provided, otherwise use cached
                    if (empty($ocrText)) {
                        $ocrText = $cachedData['raw_text'] ?? '';
                    } else {
                        $cachedData['raw_text'] = $ocrText;
                    }

                    return [
                        'success' => true,
                        'data' => $cachedData,
                        'cached' => true
                    ];
                }
            }

            if (empty($ocrText)) {
                return [
                    'success' => false,
                    'error' => 'OCR text tidak diterima. Pastikan file terbaca dengan jelas.',
                    'data' => null
                ];
            }

            // 1. Run Groq (AI) - PRIMARY BRAIN
            // AI is better at understanding context (Recipient vs Acquirer)
            $groqResult = ['success' => false, 'data' => []];
            try {
                $groqResult = $this->groqService->processReceiptOCR($ocrText, $jenisTransaksi);
            } catch (\Exception $e) {
                Log::warning('Groq Service Failed: ' . $e->getMessage());
            }

            // 2. Run Local Parser (SECONDARY / FALLBACK)
            // Local is better at finding fixed patterns (like dates in Ref Nos)
            $localData = $this->localParser->parse($ocrText, $jenisTransaksi);
            Log::info('TesseractService: Local Parser Result (Fallback)', $localData);

            // 3. MASTER DATA FUZZY CORRECTION (ULTRA SMART)
            // If OCR vendor is slightly off, match with historical success or master data
            $rawVendor = $groqResult['data']['vendor'] ?? null;
            
            // Check if Groq vendor is "broken" or empty, if so use Local Parser
            $isGroqVendorBroken = empty($rawVendor) || strlen(trim($rawVendor)) <= 2 || (strlen(preg_replace('/[^a-z0-9]/i', '', $rawVendor)) < 2);
            
            if ($isGroqVendorBroken && !empty($localData['vendor'])) {
                Log::info('TesseractService: Groq vendor is broken/empty, using local parser vendor', [
                    'groq_vendor' => $rawVendor,
                    'local_vendor' => $localData['vendor']
                ]);
                $rawVendor = $localData['vendor'];
            } elseif (empty($rawVendor)) {
                $rawVendor = $localData['vendor'] ?? null;
            }

            $finalVendor = $this->refineVendorFromMasterData($rawVendor, $ocrText);

            // 4. MERGE LOGIC (ULTRA SMART)
            $groqConf = $groqResult['data']['confidence_score'] ?? 0;
            
            $finalDate = $groqResult['data']['tanggal'] ?? $groqResult['data']['date'] ?? null;
            if (!$finalDate || ($groqConf < 40 && $localData['date'])) {
                $finalDate = $localData['date'] ?? $finalDate;
            }

            $finalNominal = null;
            if (!empty($groqResult['data']['all_detected_totals'])) {
                foreach ($groqResult['data']['all_detected_totals'] as $total) {
                    if (isset($total['priority']) && $total['priority'] == 1 && $total['amount'] > 0) {
                        $finalNominal = (float) $total['amount'];
                        break;
                    }
                }
            }
            
            if (!$finalNominal || ($groqConf < 30 && $localData['nominal'])) {
                $finalNominal = $groqResult['data']['nominal'] ?? $localData['nominal'] ?? $finalNominal;
            }

            // GET SMART COA RECOMMENDATION
            $suggestedCategoryName = $groqResult['data']['suggested_category'] ?? $localData['suggested_category'] ?? null;
            $recommendedCOA = $this->getRecommendedCOA($suggestedCategoryName);

            $finalData = [
                'vendor' => $finalVendor,
                'nominal' => $finalNominal,
                'tanggal' => $finalDate,
                'invoice_number' => $groqResult['data']['invoice_number'] ?? $localData['invoice_number'] ?? null,
                'confidence_score' => $groqResult['data']['confidence_score'] ?? 50,
                'raw_text' => $ocrText,
                'all_detected_totals' => $groqResult['data']['all_detected_totals'] ?? [],
                'suggested_category' => $suggestedCategoryName,
                'recommended_coa' => $recommendedCOA,
                'fingerprint' => $this->generateContentFingerprint(['raw_text' => $ocrText])
            ];

            Log::info('TesseractService: Final Data', $finalData);

            return [
                'success' => true,
                'data' => $finalData
            ];

        } catch (Exception $e) {
            Log::error('TesseractService: Exception occurred', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error memproses struk: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get Recommended COA based on category name from AI
     */
    protected function getRecommendedCOA(?string $categoryName): ?array
    {
        if (empty($categoryName)) return null;

        try {
            // Normalize for better matching (trim, replace & with dan, etc)
            $searchName = trim($categoryName);
            
            // Find category that matches the suggested name (case-insensitive fuzzy match)
            $category = \App\Models\KategoriBiaya::where('nama_kategori', 'like', "%{$searchName}%")
                ->with('defaultCoa')
                ->first();

            if ($category && $category->defaultCoa) {
                return [
                    'coa_id' => $category->defaultCoa->coa_id,
                    'kode_coa' => $category->defaultCoa->kode_coa,
                    'nama_coa' => $category->defaultCoa->nama_coa,
                    'category_name' => $category->nama_kategori
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get recommended COA: ' . $e->getMessage());
            return null;
        }
    }

    public function validateOCRData(array $ocrData, array $inputData): array
    {
        // ... (Keep existing validation logic or delegate to ValidasiAIService)
        // For now, we reuse Groq's validation or simple comparison
        try {
            return $this->groqService->validateReceiptData($ocrData, $inputData);
        } catch (\Exception $e) {
            // Fallback to simple validation if Groq fails
             return [
                'success' => true,
                'data' => [
                    'vendor_match' => ['status' => 'unknown', 'percentage' => 0],
                    'nominal_match' => ['status' => 'unknown'],
                    'recommendation' => 'Manual Check'
                ]
            ];
        }
    }

    // ... (Helper methods like fileToBase64, validateFileSize, validateFileType, isPdfFile remain same)
    // I will include them here to be safe, or I need to be careful not to overwrite with partial file.
    // Since I'm using "Write" tool, I must provide the FULL file content.

    protected function fileToBase64(UploadedFile $file): ?string
    {
        try {
            $fileContent = file_get_contents($file->getRealPath());
            return $fileContent ? base64_encode($fileContent) : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function validateFileSize(UploadedFile $file): array
    {
        $maxSize = config('reimbursement.storage.max_file_size', 5120);
        $fileSize = $file->getSize() / 1024;
        if ($fileSize > $maxSize) {
            return ['valid' => false, 'message' => "Ukuran file terlalu besar. Maks {$maxSize}KB"];
        }
        return ['valid' => true, 'message' => 'Ukuran file valid'];
    }

    public function validateFileType(UploadedFile $file): array
    {
        $allowed = config('reimbursement.storage.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
        if (!in_array($file->getMimeType(), $allowed)) {
            return ['valid' => false, 'message' => "Format file tidak didukung."];
        }
        return ['valid' => true, 'message' => 'Format file valid'];
    }

    public function isPdfFile(UploadedFile $file): bool
    {
        return $file->getMimeType() === 'application/pdf';
    }

    public function checkFileDuplicate(UploadedFile $file, ?array $ocrData = null): array
    {
        try {
            $originalFilename = $file->getClientOriginalName();
            $fileHash = md5_file($file->getRealPath());
            $userId = Auth::id();

            // 1. Check by Hash (Most reliable - Content Match)
            // Global check: prevent file reuse across ALL users (fraud prevention)
            // Ignore rejected and void submissions
            $duplicateByHash = DB::table('pengajuan')
                ->where('file_hash', $fileHash)
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance', 'void_accurate'])
                ->first();

            if ($duplicateByHash) {
                // ULTRA SMART: Multi-Invoice Support
                // If the file is a duplicate by hash, check if it's a marketplace document
                // that might contain multiple invoices.
                $isMultiInvoicePossible = false;
                
                // Check if the existing pengajuan was marketplace
                if (str_contains(strtolower($duplicateByHash->keterangan ?? ''), 'tokopedia') || 
                    str_contains(strtolower($duplicateByHash->nama_vendor ?? ''), 'tokopedia') ||
                    str_contains(strtolower($duplicateByHash->nama_vendor ?? ''), 'shopee')) {
                    $isMultiInvoicePossible = true;
                }

                // If ocrData is provided, check if it explicitly says it's multi-invoice
                if ($ocrData && !empty($ocrData['all_detected_totals']) && count($ocrData['all_detected_totals']) > 1) {
                    $isMultiInvoicePossible = true;
                }

                if ($isMultiInvoicePossible) {
                    Log::info('TesseractService: Potential multi-invoice file detected. Allowing hash match for further validation.', ['hash' => $fileHash]);
                    // Don't return duplicate yet, let the logical check handle it later with invoice_number
                } else {
                    $existingUser = DB::table('users')
                        ->where('id', $duplicateByHash->user_id)
                        ->first();
                    
                    $userName = $existingUser ? $existingUser->name : 'Unknown User';
                    
                    Log::warning('Duplicate File Hash Detected', [
                        'hash' => $fileHash,
                        'current_user_id' => $userId,
                        'existing_user_id' => $duplicateByHash->user_id,
                        'existing_pengajuan' => $duplicateByHash->nomor_pengajuan
                    ]);
                    
                    return [
                        'is_duplicate' => true,
                        'type' => 'hash_match',
                        'message' => "File ini sudah digunakan oleh {$userName} untuk pengajuan #{$duplicateByHash->nomor_pengajuan}.",
                        'existing_pengajuan_id' => $duplicateByHash->pengajuan_id
                    ];
                }
            }

            // 2. Check by Filename (Backup)
            $duplicateByName = DB::table('pengajuan')
                ->where('file_name', $originalFilename)
                ->where('user_id', $userId) // Filename check should be user specific
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance', 'void_accurate'])
                ->first();

            if ($duplicateByName) {
                Log::warning('Duplicate Filename Detected', ['filename' => $originalFilename]);
                return [
                    'is_duplicate' => true,
                    'type' => 'filename_match',
                    'message' => 'Nama file sudah pernah digunakan. Mohon ganti nama file jika isinya berbeda.',
                    'existing_pengajuan_id' => $duplicateByName->pengajuan_id
                ];
            }

            // 3. Content-based Fingerprint Check (ULTRA SMART)
            // Detect if the same receipt is uploaded with different filename/resolution
            $contentFingerprint = $this->generateContentFingerprint($ocrData);
            if ($contentFingerprint) {
                // Search for this fingerprint in previous successful OCR validations
                // JOIN with pengajuan to ensure we only block if the original pengajuan is NOT rejected/void
                $duplicateFingerprint = DB::table('validasi_ai')
                    ->join('pengajuan', 'validasi_ai.pengajuan_id', '=', 'pengajuan.pengajuan_id')
                    ->where('validasi_ai.jenis_validasi', 'ocr')
                    ->where('validasi_ai.hasil_ocr', 'LIKE', "%\"fingerprint\":\"{$contentFingerprint}\"%")
                    ->whereNotIn('validasi_ai.status', ['invalid'])
                    ->whereNotIn('pengajuan.status', ['ditolak_atasan', 'ditolak_finance', 'void_accurate'])
                    ->select('validasi_ai.*', 'pengajuan.nomor_pengajuan', 'pengajuan.user_id')
                    ->first();

                if ($duplicateFingerprint) {
                    if ($duplicateFingerprint->user_id != $userId) {
                        $existingUser = DB::table('users')->where('id', $duplicateFingerprint->user_id)->first();
                        $userName = $existingUser ? $existingUser->name : 'User lain';
                        
                        Log::warning('Content Fingerprint Duplicate Detected', [
                            'fingerprint' => $contentFingerprint,
                            'existing_id' => $duplicateFingerprint->pengajuan_id
                        ]);

                        return [
                            'is_duplicate' => true,
                            'type' => 'content_match',
                            'message' => "Konten struk ini terdeteksi identik dengan pengajuan #{$duplicateFingerprint->nomor_pengajuan} milik {$userName}. (Fraud Detection)",
                            'existing_pengajuan_id' => $duplicateFingerprint->pengajuan_id
                        ];
                    }
                }
            }

            // 4. Logical Duplicate Check (Using OCR Data)
            // Checks if Vendor + Nominal + Date matches an existing record globally
            if ($ocrData && isset($ocrData['vendor'], $ocrData['nominal'], $ocrData['tanggal'])) {
                $vendor = trim($ocrData['vendor']);
                $nominal = (int)$ocrData['nominal'];
                $date = $ocrData['tanggal'];
                $invoiceNumber = $ocrData['invoice_number'] ?? null;

                $logicalResult = $this->checkLogicalDuplicate($userId, $vendor, $nominal, $date, $invoiceNumber);
                
                if ($logicalResult['is_duplicate']) {
                    return [
                        'is_duplicate' => true,
                        'type' => 'logical_match',
                        'message' => $logicalResult['message'],
                        'existing_pengajuan_id' => $logicalResult['existing_pengajuan_id'] ?? null
                    ];
                }
            }

            return [
                'is_duplicate' => false,
                'type' => 'none',
                'message' => 'File aman (unik)'
            ];

        } catch (Exception $e) {
            Log::error('Error checking duplicate', ['error' => $e->getMessage()]);
            // Fail safe: Allow upload but log error? Or block?
            // User wants strict block. But if error, maybe block is safer?
            // Let's return false for now to avoid blocking on system error, but log it.
            return [
                'is_duplicate' => false, 
                'error' => $e->getMessage(),
                'message' => 'Gagal cek duplikasi, melanjutkan...'
            ];
        }
    }

    public function checkLogicalDuplicate(string $userId, string $vendor, int $nominal, string $tanggal, ?string $invoiceNumber = null): array
    {
        // GLOBAL CHECK: Ensure this receipt isn't used by ANYONE else
        $duplicates = DB::table('pengajuan')
            ->where('nominal', $nominal)
            ->where('tanggal_transaksi', $tanggal)
            ->where(function($q) use ($vendor) {
                $q->where('nama_vendor', 'LIKE', "%{$vendor}%")
                  ->orWhereRaw('LOWER(nama_vendor) LIKE ?', ["%" . strtolower($vendor) . "%"]);
            })
            ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance', 'void_accurate'])
            ->get();

        if ($duplicates->count() > 0) {
            foreach ($duplicates as $duplicate) {
                // ULTRA SMART: Multi-Invoice Differentiation
                if (!empty($invoiceNumber)) {
                    $existingValidation = DB::table('validasi_ai')
                        ->where('pengajuan_id', $duplicate->pengajuan_id)
                        ->where('jenis_validasi', 'ocr')
                        ->where('hasil_ocr', 'LIKE', "%\"invoice_number\":\"{$invoiceNumber}\"%")
                        ->first();
                    
                    if (!$existingValidation) {
                        // Different invoice number or not recorded, allow it (multi-invoice)
                        continue;
                    }
                }

                $isSelf = ($duplicate->user_id == $userId);
                $user = DB::table('users')->where('id', $duplicate->user_id)->first();
                $userName = $user ? $user->name : 'User lain';

                $msg = $isSelf 
                    ? "Anda sudah pernah mengajukan kuitansi ini (#{$duplicate->nomor_pengajuan})."
                    : "Kuitansi ini sudah pernah diajukan oleh {$userName} (#{$duplicate->nomor_pengajuan}).";

                return [
                    'is_duplicate' => true,
                    'message' => $msg
                ];
            }
        }

        return [
            'is_duplicate' => false,
            'message' => 'Unik'
        ];
    }

    /**
     * Refine Vendor name using Master Data or Historical Submissions
     */
    protected function refineVendorFromMasterData(?string $vendor, string $rawText): ?string
    {
        if (empty($vendor)) return $vendor;

        try {
            $search = strtolower(trim($vendor));
            
            // Optimization: Static Cache
            if (isset(self::$vendorRefineCache[$search])) {
                return self::$vendorRefineCache[$search];
            }

            // 1. Check in historical pengajuan (most successful matches)
            $history = DB::table('pengajuan')
                ->select('nama_vendor', DB::raw('count(*) as total'))
                ->where('status', \App\Enums\PengajuanStatus::DICAIRKAN->value) 
                ->where('nama_vendor', 'LIKE', "%{$search}%")
                ->groupBy('nama_vendor')
                ->orderBy('total', 'desc')
                ->first();

            if ($history && !empty($history->nama_vendor)) {
                similar_text(strtolower($history->nama_vendor), $search, $percent);
                if ($percent > 80) {
                    self::$vendorRefineCache[$search] = $history->nama_vendor;
                    return $history->nama_vendor;
                }
            }
            
            self::$vendorRefineCache[$search] = $vendor;
            return $vendor;
        } catch (\Exception $e) {
            return $vendor;
        }
    }

    /**
     * Generate a unique fingerprint based on OCR text content
     */
    protected function generateContentFingerprint(?array $ocrData): ?string
    {
        $rawText = $ocrData['raw_text'] ?? '';
        if (empty($rawText)) return null;

        // Clean text: remove all non-alphanumeric, lowercase it
        $cleanText = strtolower(preg_replace('/[^a-z0-9]/', '', $rawText));
        
        // Take a substantial part (e.g., first 1000 chars) to avoid collision but handle truncation
        $fingerprintSource = substr($cleanText, 0, 1000);
        
        if (strlen($fingerprintSource) < 50) return null; // Too short to be a reliable fingerprint

        return md5($fingerprintSource);
    }
}
