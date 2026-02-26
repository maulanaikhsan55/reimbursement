<?php

namespace App\Services;

use App\Enums\PengajuanStatus;
use App\Enums\ValidationStatus;
use App\Models\Pengajuan;
use App\Models\ValidasiAI;
use Illuminate\Support\Facades\DB;

class ValidasiAIService
{
    protected TesseractService $tesseractService;

    // Static cache for performance optimization across bulk validations
    private static $masterDataCache = [];

    private static $anomalyCache = [];

    /**
     * Platform Aliases for fuzzy matching
     */
    private const PLATFORM_ALIASES = [
        'tiktok' => ['tiktok', 'tiktok shop', 'go tiktok', 'tiktok local service'],
        'gojek' => ['gojek', 'go-jek', 'go jek', 'go pay'],
        'grab' => ['grab', 'grab pay'],
        'shopee' => ['shopee', 'shopee pay', 'shopeepay'],
        'tokopedia' => ['tokopedia', 'tokopedia pay'],
        'indomaret' => ['indomaret', 'indomaret 24'],
        'alfamart' => ['alfamart', 'alfamidimart'],
        'pertamina' => ['pertamina', 'spbu'],
    ];

    public function __construct(TesseractService $tesseractService)
    {
        $this->tesseractService = $tesseractService;
    }

    /**
     * Sanitize nominal input from various formats (ID/US)
     */
    public function sanitizeNominal($nominal): float
    {
        if (empty($nominal)) {
            return 0;
        }

        // Remove currency symbols and spaces
        $nominal = preg_replace('/[^\d,.]/', '', (string) $nominal);

        if (str_contains($nominal, '.') && str_contains($nominal, ',')) {
            $lastDot = strrpos($nominal, '.');
            $lastComma = strrpos($nominal, ',');
            if ($lastComma > $lastDot) {
                $nominal = str_replace('.', '', $nominal);
                $parts = explode(',', $nominal);
                $nominal = $parts[0];
            } else {
                $nominal = str_replace(',', '', $nominal);
                $parts = explode('.', $nominal);
                $nominal = $parts[0];
            }
        } elseif (str_contains($nominal, ',')) {
            if (preg_match('/,\d{3}$/', $nominal)) {
                $nominal = str_replace(',', '', $nominal);
            } else {
                $parts = explode(',', $nominal);
                $nominal = $parts[0];
            }
        } elseif (str_contains($nominal, '.')) {
            if (preg_match('/\.\d{3}$/', $nominal)) {
                $nominal = str_replace('.', '', $nominal);
            } else {
                $parts = explode('.', $nominal);
                $nominal = $parts[0];
            }
        }

        return (float) $nominal;
    }

    /**
     * Centralized method to validate manual user input against OCR data
     */
    public function validateManualInput(array $ocrData, array $inputData, ?string $userId = null): array
    {
        $inputData['nominal'] = $this->sanitizeNominal($inputData['nominal'] ?? 0);
        $detectedTransactionTime = $this->extractTransactionTimeFromOcr($ocrData);

        // 1. Nominal Validation
        $nominalMatch = $this->matchNominal(
            $ocrData['nominal'] ?? 0,
            $inputData['nominal'],
            $ocrData['raw_text'] ?? null,
            $ocrData['all_detected_totals'] ?? []
        );

        // 2. Vendor Validation
        $vendorMatch = $this->matchVendor(
            $ocrData['vendor'] ?? null,
            $inputData['nama_vendor'] ?? '',
            $ocrData['raw_text'] ?? null
        );

        // 3. Date Validation
        $tanggalMatch = $this->matchTanggal(
            $ocrData['tanggal'] ?? null,
            $inputData['tanggal_transaksi'] ?? null
        );

        // 4. Policy: 15-Day Limit
        $isTooOld = false;
        $maxAge = config('reimbursement.policy.max_receipt_age_days', 15);
        if (! empty($inputData['tanggal_transaksi'])) {
            try {
                $tanggalTransaksi = \Carbon\Carbon::parse($inputData['tanggal_transaksi']);
                $diffInDays = $tanggalTransaksi->startOfDay()->diffInDays(\Carbon\Carbon::now()->startOfDay());
                if ($diffInDays > $maxAge) {
                    $isTooOld = true;
                }
            } catch (\Exception $e) {
            }
        }

        // 5. Behavioral Anomaly
        $anomalyInput = array_merge($inputData, [
            'waktu_transaksi' => $detectedTransactionTime,
            'invoice_number' => $ocrData['invoice_number'] ?? null,
            'jenis_transaksi' => $inputData['jenis_transaksi'] ?? 'other',
        ]);
        $anomalyResult = $this->detectBehavioralAnomaly($userId, $anomalyInput);
        $llmAnomaly = $this->extractLlmAnomalyDecision($ocrData);
        $duplicateCheck = $this->detectPotentialDuplicateSmart(
            $userId,
            (string) ($inputData['nama_vendor'] ?? ''),
            (float) ($inputData['nominal'] ?? 0),
            $inputData['tanggal_transaksi'] ?? null
        );
        $smartAudit = $this->buildSmartAuditPayload(
            $ocrData,
            $inputData,
            $nominalMatch,
            $vendorMatch,
            $tanggalMatch,
            $anomalyResult,
            $llmAnomaly,
            $detectedTransactionTime,
            $duplicateCheck
        );

        // Determine issues
        $issues = [];
        $canSubmit = true;

        if ($nominalMatch['status'] === 'fail') {
            $canSubmit = false;
            $issues[] = [
                'type' => 'error',
                'title' => 'Nominal Tidak Cocok',
                'message' => 'Nominal tidak sesuai dengan struk. Perbedaan: Rp '.number_format($nominalMatch['difference'] ?? 0, 0, ',', '.'),
                'suggestion' => 'Periksa kembali nominal yang diinput agar sama persis dengan struk.',
            ];
        }

        if ($vendorMatch['status'] === 'fail') {
            $canSubmit = false;
            $issues[] = [
                'type' => 'error',
                'title' => 'Vendor Tidak Cocok',
                'message' => 'Nama vendor hanya cocok '.round($vendorMatch['match_percentage'], 1).'%.',
                'suggestion' => 'Gunakan nama vendor resmi sesuai yang tertera di struk.',
            ];
        } elseif ($vendorMatch['status'] === 'warning') {
            $issues[] = [
                'type' => 'warning',
                'title' => 'Vendor Mirip',
                'message' => 'Kesamaan vendor: '.round($vendorMatch['match_percentage'], 1).'%. Perlu tinjauan manual.',
                'suggestion' => 'Anda tetap dapat melanjutkan, Finance akan memverifikasi.',
            ];
        }

        if ($tanggalMatch['status'] === 'fail' || $isTooOld) {
            $canSubmit = false;
            $issues[] = [
                'type' => 'error',
                'title' => $isTooOld ? 'Struk Kadaluwarsa' : 'Tanggal Tidak Cocok',
                'message' => $isTooOld ? "Struk sudah melewati batas $maxAge hari." : 'Tanggal transaksi tidak sesuai dengan struk.',
                'suggestion' => $isTooOld ? 'Gunakan struk yang lebih baru.' : 'Sesuaikan tanggal input dengan tanggal pada struk.',
            ];
        }

        if ($anomalyResult['is_anomaly']) {
            $issues[] = [
                'type' => 'warning',
                'title' => 'Anomali Perilaku',
                'message' => $anomalyResult['reason'],
                'suggestion' => 'Pastikan data benar. Auditor Finance akan meninjau ulang.',
            ];
        }

        if (($duplicateCheck['is_duplicate'] ?? false) === true) {
            $canSubmit = false;
            $issues[] = [
                'type' => 'error',
                'title' => 'Duplikasi Terdeteksi',
                'message' => $duplicateCheck['message'] ?? 'Kombinasi vendor + tanggal + nominal sudah pernah diajukan.',
                'suggestion' => 'Gunakan dokumen transaksi lain atau cek pengajuan yang sudah ada.',
            ];
        }

        if (($llmAnomaly['recommendation'] ?? 'approve') === 'reject') {
            $issues[] = [
                'type' => 'warning',
                'title' => 'Rekomendasi AI: Risiko Tinggi',
                'message' => $llmAnomaly['summary'],
                'suggestion' => 'Perlu validasi manual atasan/finance. '.$llmAnomaly['decision_reason'],
            ];
        } elseif (($llmAnomaly['recommendation'] ?? 'approve') === 'review') {
            $issues[] = [
                'type' => 'warning',
                'title' => 'Perlu Review AI',
                'message' => $llmAnomaly['summary'],
                'suggestion' => $llmAnomaly['decision_reason'],
            ];
        }

        return [
            'success' => true,
            'can_submit' => $canSubmit,
            'matches' => [
                'vendor' => $vendorMatch,
                'nominal' => $nominalMatch,
                'tanggal' => array_merge($tanggalMatch, ['is_too_old' => $isTooOld, 'max_age' => $maxAge]),
                'anomali' => $anomalyResult,
                'llm_anomaly' => $llmAnomaly,
                'duplicate' => $duplicateCheck,
            ],
            'issues' => $issues,
            'smart_audit' => $smartAudit,
        ];
    }

    private function extractLlmAnomalyDecision(array $ocrData): array
    {
        $analysis = is_array($ocrData['llm_anomaly_analysis'] ?? null)
            ? $ocrData['llm_anomaly_analysis']
            : [];

        $riskScore = (int) ($analysis['risk_score'] ?? ($ocrData['fraud_risk_score'] ?? 0));
        $riskScore = max(0, min(100, $riskScore));

        $riskLevel = strtolower(trim((string) ($analysis['risk_level'] ?? '')));
        if (! in_array($riskLevel, ['low', 'medium', 'high'], true)) {
            $riskLevel = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');
        }

        $recommendation = strtolower(trim((string) ($analysis['approval_recommendation'] ?? '')));
        if (! in_array($recommendation, ['approve', 'review', 'reject'], true)) {
            $recommendation = $riskScore >= 75 ? 'reject' : ($riskScore >= 45 ? 'review' : 'approve');
        }

        $summary = trim((string) ($analysis['summary'] ?? ''));
        if ($summary === '') {
            $summary = trim((string) ($ocrData['sanity_check_notes'] ?? ''));
        }
        if ($summary === '') {
            $summary = $recommendation === 'reject'
                ? 'AI mendeteksi risiko fraud tinggi pada dokumen ini.'
                : ($recommendation === 'review'
                    ? 'AI menemukan beberapa sinyal risiko yang perlu perhatian.'
                    : 'AI tidak menemukan anomali signifikan.');
        }

        $decisionReason = trim((string) ($analysis['decision_reason'] ?? ''));
        if ($decisionReason === '') {
            $decisionReason = $summary;
        }

        $redFlags = array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            is_array($analysis['red_flags'] ?? null) ? $analysis['red_flags'] : []
        )));

        $manipulationSignals = array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            is_array($analysis['manipulation_signals'] ?? null) ? $analysis['manipulation_signals'] : []
        )));

        $anomalyChecks = [];
        $rawChecks = is_array($analysis['anomaly_checks'] ?? null) ? $analysis['anomaly_checks'] : [];
        foreach ($rawChecks as $check) {
            if (! is_array($check)) {
                continue;
            }
            $anomalyChecks[] = [
                'code' => trim((string) ($check['code'] ?? 'general_anomaly')) ?: 'general_anomaly',
                'label' => trim((string) ($check['label'] ?? 'Temuan anomali')) ?: 'Temuan anomali',
                'status' => strtolower(trim((string) ($check['status'] ?? 'warning'))),
                'severity' => strtolower(trim((string) ($check['severity'] ?? 'medium'))),
                'evidence' => trim((string) ($check['evidence'] ?? '')),
                'reason' => trim((string) ($check['reason'] ?? '')),
            ];
        }

        $reviewReasons = array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            is_array($analysis['review_reasons'] ?? null) ? $analysis['review_reasons'] : []
        )));

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'recommendation' => $recommendation,
            'summary' => $summary,
            'decision_reason' => $decisionReason,
            'requires_manual_review' => (bool) ($analysis['requires_manual_review'] ?? ($recommendation === 'review')),
            'red_flags' => $redFlags,
            'manipulation_signals' => $manipulationSignals,
            'anomaly_checks' => $anomalyChecks,
            'review_reasons' => $reviewReasons,
        ];
    }

    private function extractTransactionTimeFromOcr(array $ocrData): ?string
    {
        $rawTime = trim((string) ($ocrData['time'] ?? ''));
        if ($rawTime !== '' && preg_match('/^([01]?\d|2[0-3])[:.]([0-5]\d)$/', $rawTime, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        $rawText = (string) ($ocrData['raw_text'] ?? '');
        if ($rawText === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $fallbackTime = null;
        foreach ($lines as $line) {
            $lineLower = strtolower(trim((string) $line));
            if ($lineLower === '') {
                continue;
            }

            if (! preg_match('/([01]?\d|2[0-3])[:.]([0-5]\d)/', $lineLower, $m)) {
                continue;
            }

            $candidate = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            if (preg_match('/(transaksi|pembayaran|berhasil|waktu|jam|time|wib|wit|wita)/', $lineLower)) {
                return $candidate;
            }

            if ($fallbackTime === null) {
                $fallbackTime = $candidate;
            }
        }

        return $fallbackTime;
    }

    private function buildSmartAuditPayload(
        array $ocrData,
        array $inputData,
        array $nominalMatch,
        array $vendorMatch,
        array $tanggalMatch,
        array $anomalyResult,
        array $llmAnomaly,
        ?string $transactionTime,
        array $duplicateCheck = []
    ): array {
        $decision = strtolower((string) ($llmAnomaly['recommendation'] ?? 'approve'));
        if (! in_array($decision, ['approve', 'review', 'reject'], true)) {
            $decision = 'review';
        }

        $decisionScore = max(0, min(100, 100 - (int) ($llmAnomaly['risk_score'] ?? 0)));

        $decisionReasons = [];
        if (($nominalMatch['status'] ?? 'fail') !== 'pass') {
            $decision = 'reject';
            $decisionScore = min($decisionScore, 25);
            $decisionReasons[] = 'Nominal input tidak sesuai dengan hasil OCR.';
        }

        if (($tanggalMatch['status'] ?? 'fail') !== 'pass') {
            $decision = 'reject';
            $decisionScore = min($decisionScore, 30);
            $decisionReasons[] = 'Tanggal transaksi tidak cocok dengan dokumen.';
        }

        if (($vendorMatch['status'] ?? 'fail') === 'fail') {
            $decision = 'reject';
            $decisionScore = min($decisionScore, 35);
            $decisionReasons[] = 'Vendor tidak terverifikasi dengan confidence memadai.';
        }

        if (($duplicateCheck['is_duplicate'] ?? false) === true) {
            $decision = 'reject';
            $decisionScore = min($decisionScore, 15);
            $decisionReasons[] = $duplicateCheck['message'] ?? 'Terindikasi duplikasi transaksi (vendor+tanggal+nominal).';
        }

        if (($anomalyResult['is_anomaly'] ?? false) && $decision === 'approve') {
            $decision = 'review';
            $decisionScore = min($decisionScore, 55);
            $decisionReasons[] = 'Terindikasi pola anomali perilaku pada histori pengajuan.';
        }

        if (($llmAnomaly['recommendation'] ?? '') === 'reject') {
            $decision = 'reject';
            $decisionScore = min($decisionScore, 20);
            $decisionReasons[] = $llmAnomaly['decision_reason'] ?? 'LLM menilai risiko fraud tinggi.';
        } elseif (($llmAnomaly['recommendation'] ?? '') === 'review' && $decision === 'approve') {
            $decision = 'review';
            $decisionScore = min($decisionScore, 60);
            $decisionReasons[] = $llmAnomaly['decision_reason'] ?? 'LLM meminta review manual.';
        }

        if (empty($decisionReasons)) {
            $decisionReasons[] = $llmAnomaly['summary'] ?? 'Dokumen terlihat konsisten dan layak diproses otomatis.';
        }

        return [
            'decision' => $decision,
            'decision_score' => $decisionScore,
            'decision_reason' => implode(' ', array_values(array_unique(array_filter($decisionReasons)))),
            'transaction_time' => $transactionTime,
            'invoice_anomalies' => $this->buildInvoiceAnomalyChecklist(
                $nominalMatch,
                $vendorMatch,
                $tanggalMatch,
                $anomalyResult,
                $llmAnomaly,
                $transactionTime,
                $duplicateCheck
            ),
            'journal_recommendation' => $this->buildJournalRecommendation($ocrData, $inputData),
            'llm' => $llmAnomaly,
        ];
    }

    private function buildInvoiceAnomalyChecklist(
        array $nominalMatch,
        array $vendorMatch,
        array $tanggalMatch,
        array $anomalyResult,
        array $llmAnomaly,
        ?string $transactionTime,
        array $duplicateCheck = []
    ): array {
        $checks = [];

        $checks[] = [
            'code' => 'nominal_consistency',
            'label' => 'Konsistensi nominal invoice',
            'status' => ($nominalMatch['status'] ?? 'fail') === 'pass' ? 'pass' : 'fail',
            'severity' => ($nominalMatch['status'] ?? 'fail') === 'pass' ? 'low' : 'high',
            'detail' => ($nominalMatch['status'] ?? 'fail') === 'pass'
                ? 'Nominal input cocok dengan nominal terdeteksi.'
                : 'Nominal input tidak cocok dengan nominal invoice.',
        ];

        $vendorStatus = $vendorMatch['status'] ?? 'fail';
        $checks[] = [
            'code' => 'vendor_consistency',
            'label' => 'Kecocokan vendor/merchant',
            'status' => $vendorStatus === 'pass' ? 'pass' : ($vendorStatus === 'warning' ? 'warning' : 'fail'),
            'severity' => $vendorStatus === 'pass' ? 'low' : ($vendorStatus === 'warning' ? 'medium' : 'high'),
            'detail' => 'Skor kecocokan vendor: '.round((float) ($vendorMatch['match_percentage'] ?? 0), 1).'%.',
        ];

        $checks[] = [
            'code' => 'date_consistency',
            'label' => 'Kecocokan tanggal transaksi',
            'status' => ($tanggalMatch['status'] ?? 'fail') === 'pass' ? 'pass' : 'fail',
            'severity' => ($tanggalMatch['status'] ?? 'fail') === 'pass' ? 'low' : 'high',
            'detail' => ($tanggalMatch['status'] ?? 'fail') === 'pass'
                ? 'Tanggal transaksi valid dan konsisten.'
                : 'Tanggal transaksi tidak konsisten dengan dokumen.',
        ];

        if ($transactionTime !== null) {
            $workStart = (int) config('reimbursement.policy.workday_start_hour', 8);
            $workEnd = (int) config('reimbursement.policy.workday_end_hour', 18);
            [$hour] = array_map('intval', explode(':', $transactionTime));
            $isOutsideWorkHour = $hour < $workStart || $hour >= $workEnd;

            $checks[] = [
                'code' => 'outside_working_hours',
                'label' => 'Transaksi di luar jam kerja',
                'status' => $isOutsideWorkHour ? 'warning' : 'pass',
                'severity' => $isOutsideWorkHour ? 'medium' : 'low',
                'detail' => $isOutsideWorkHour
                    ? "Waktu transaksi {$transactionTime} berada di luar jam kerja standar ({$workStart}:00-{$workEnd}:00)."
                    : "Waktu transaksi {$transactionTime} berada dalam jam kerja standar.",
            ];
        }

        if (($duplicateCheck['is_duplicate'] ?? false) === true) {
            $checks[] = [
                'code' => 'duplicate_combo_vendor_date_amount',
                'label' => 'Duplikasi kombinasi vendor+tanggal+nominal',
                'status' => 'fail',
                'severity' => 'high',
                'detail' => $duplicateCheck['message'] ?? 'Kombinasi transaksi ini sudah pernah diajukan.',
            ];
        }

        foreach (($anomalyResult['anomalies'] ?? []) as $index => $anomaly) {
            $checks[] = [
                'code' => 'behavior_'.($anomaly['type'] ?? $index),
                'label' => 'Anomali perilaku: '.str_replace('_', ' ', (string) ($anomaly['type'] ?? 'unknown')),
                'status' => 'warning',
                'severity' => 'medium',
                'detail' => (string) ($anomaly['reason'] ?? 'Perlu review tambahan.'),
            ];
        }

        $llmFlags = (array) ($llmAnomaly['red_flags'] ?? []);
        $llmSignals = (array) ($llmAnomaly['manipulation_signals'] ?? []);
        if (! empty($llmFlags) || ! empty($llmSignals)) {
            $checks[] = [
                'code' => 'llm_red_flags',
                'label' => 'Red flags dari LLM',
                'status' => ($llmAnomaly['recommendation'] ?? 'approve') === 'reject' ? 'fail' : 'warning',
                'severity' => ($llmAnomaly['recommendation'] ?? 'approve') === 'reject' ? 'high' : 'medium',
                'detail' => trim(
                    'Flags: '.implode('; ', array_slice($llmFlags, 0, 4)).'. '.
                    'Sinyal manipulasi: '.implode('; ', array_slice($llmSignals, 0, 4))
                ),
            ];
        }

        return array_values(array_slice($checks, 0, 12));
    }

    private function buildJournalRecommendation(array $ocrData, array $inputData): array
    {
        $nominal = (float) ($inputData['nominal'] ?? 0);
        if ($nominal <= 0) {
            $nominal = (float) ($ocrData['nominal'] ?? 0);
        }
        $nominal = max(0, $nominal);

        $components = $this->extractTaxAndFeeComponents($ocrData, $nominal);
        $taxAmount = (float) ($components['tax'] ?? 0);
        $feeAmount = (float) ($components['fee'] ?? 0);
        $mainExpense = $nominal - $taxAmount - $feeAmount;

        if ($mainExpense <= 0) {
            $mainExpense = $nominal;
            $taxAmount = 0;
            $feeAmount = 0;
        }

        $recommendedCoa = is_array($ocrData['recommended_coa'] ?? null) ? $ocrData['recommended_coa'] : null;
        $debitAccountCode = (string) ($recommendedCoa['kode_coa'] ?? 'REVIEW-EXPENSE');
        $debitAccountName = (string) ($recommendedCoa['nama_coa'] ?? (($ocrData['suggested_category'] ?? 'Biaya Operasional').' (Review Finance)'));

        $creditAccount = $this->detectCashBankAccountFromRawText((string) ($ocrData['raw_text'] ?? ''));

        $entries = [
            [
                'type' => 'debit',
                'account_code' => $debitAccountCode,
                'account_name' => $debitAccountName,
                'amount' => round($mainExpense, 2),
                'note' => 'Beban utama transaksi reimbursement.',
            ],
        ];

        if ($taxAmount > 0) {
            $entries[] = [
                'type' => 'debit',
                'account_code' => 'REVIEW-PPN',
                'account_name' => 'PPN Masukan (Review Finance)',
                'amount' => round($taxAmount, 2),
                'note' => 'Komponen pajak dari invoice.',
            ];
        }

        if ($feeAmount > 0) {
            $entries[] = [
                'type' => 'debit',
                'account_code' => 'REVIEW-ADMIN',
                'account_name' => 'Biaya Admin/Service (Review Finance)',
                'amount' => round($feeAmount, 2),
                'note' => 'Komponen biaya admin/service dari invoice.',
            ];
        }

        $entries[] = [
            'type' => 'credit',
            'account_code' => $creditAccount['code'],
            'account_name' => $creditAccount['name'],
            'amount' => round($nominal, 2),
            'note' => 'Arus keluar kas/bank untuk pembayaran reimbursement.',
        ];

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($entries as $entry) {
            if (($entry['type'] ?? '') === 'debit') {
                $totalDebit += (float) ($entry['amount'] ?? 0);
            } else {
                $totalCredit += (float) ($entry['amount'] ?? 0);
            }
        }

        $diff = round($totalCredit - $totalDebit, 2);
        if (abs($diff) > 0.01) {
            foreach ($entries as $idx => $entry) {
                if (($entry['type'] ?? '') === 'debit') {
                    $entries[$idx]['amount'] = round(((float) $entries[$idx]['amount']) + $diff, 2);
                    $totalDebit = round($totalDebit + $diff, 2);
                    break;
                }
            }
        }

        return [
            'reference' => (string) ($ocrData['invoice_number'] ?? '-'),
            'summary' => 'Draft jurnal otomatis untuk membantu review finance sebelum posting final ke Accurate.',
            'is_balanced' => abs(round($totalDebit - $totalCredit, 2)) <= 0.01,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'entries' => $entries,
        ];
    }

    private function extractTaxAndFeeComponents(array $ocrData, float $baseNominal): array
    {
        $totals = is_array($ocrData['all_detected_totals'] ?? null) ? $ocrData['all_detected_totals'] : [];
        if (empty($totals) || $baseNominal <= 0) {
            return ['tax' => 0.0, 'fee' => 0.0];
        }

        $tax = 0.0;
        $fee = 0.0;
        $seen = [];

        foreach ($totals as $total) {
            $label = strtolower(trim((string) ($total['label'] ?? '')));
            $amount = (float) ($total['amount'] ?? 0);
            if ($label === '' || $amount <= 0) {
                continue;
            }

            $key = $label.'|'.$amount;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if (preg_match('/\b(ppn|pajak|tax|vat)\b/', $label)) {
                $tax += $amount;
                continue;
            }

            if (preg_match('/\b(admin|administrasi|service|fee|biaya admin)\b/', $label)) {
                $fee += $amount;
            }
        }

        $maxComponent = $baseNominal * 0.6;
        $tax = min($tax, $maxComponent);
        $fee = min($fee, $maxComponent);

        if (($tax + $fee) > ($baseNominal * 0.8)) {
            return ['tax' => 0.0, 'fee' => 0.0];
        }

        return [
            'tax' => round($tax, 2),
            'fee' => round($fee, 2),
        ];
    }

    private function detectCashBankAccountFromRawText(string $rawText): array
    {
        $raw = strtolower($rawText);
        $map = [
            ['pattern' => '/\bbank\s*bri\b|\bbri\b/', 'code' => 'BANK-BRI', 'name' => 'Bank BRI'],
            ['pattern' => '/\bbank\s*bca\b|\bbca\b/', 'code' => 'BANK-BCA', 'name' => 'Bank BCA'],
            ['pattern' => '/\bbank\s*mandiri\b|\bmandiri\b/', 'code' => 'BANK-MANDIRI', 'name' => 'Bank Mandiri'],
            ['pattern' => '/\bbank\s*bni\b|\bbni\b/', 'code' => 'BANK-BNI', 'name' => 'Bank BNI'],
            ['pattern' => '/\bdana\b/', 'code' => 'EWALLET-DANA', 'name' => 'E-Wallet DANA'],
            ['pattern' => '/\bovo\b/', 'code' => 'EWALLET-OVO', 'name' => 'E-Wallet OVO'],
            ['pattern' => '/\bgopay\b/', 'code' => 'EWALLET-GOPAY', 'name' => 'E-Wallet GoPay'],
            ['pattern' => '/\bshopeepay\b/', 'code' => 'EWALLET-SHOPEEPAY', 'name' => 'E-Wallet ShopeePay'],
            ['pattern' => '/\bqris\b/', 'code' => 'BANK-QRIS', 'name' => 'Kas/Bank via QRIS'],
        ];

        foreach ($map as $entry) {
            if (preg_match($entry['pattern'], $raw)) {
                return [
                    'code' => $entry['code'],
                    'name' => $entry['name'],
                ];
            }
        }

        return [
            'code' => 'KAS-BANK-OPERASIONAL',
            'name' => 'Kas/Bank Operasional',
        ];
    }

    private function detectPotentialDuplicateSmart(
        ?string $userId,
        string $inputVendor,
        float $inputNominal,
        ?string $inputTanggal,
        ?int $excludePengajuanId = null
    ): array {
        $normalizedVendor = $this->normalizeVendorName($inputVendor);
        $nominal = (float) $this->sanitizeNominal($inputNominal);
        $tanggal = trim((string) $inputTanggal);

        if ($normalizedVendor === '' || $nominal <= 0 || $tanggal === '') {
            return [
                'is_duplicate' => false,
                'signature' => $this->buildDuplicateSignature($normalizedVendor, $nominal, $tanggal),
            ];
        }

        try {
            $windowDays = max(0, (int) config('reimbursement.policy.duplicate_window_days', 15));
            $inputDate = \Carbon\Carbon::parse($tanggal)->startOfDay();
            $startDate = $inputDate->copy()->subDays($windowDays)->toDateString();
            $endDate = $inputDate->copy()->addDays($windowDays)->toDateString();

            $query = Pengajuan::query()
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->where('nominal', $nominal)
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance', 'void_accurate']);

            if ($excludePengajuanId !== null) {
                $query->where('pengajuan_id', '!=', $excludePengajuanId);
            }

            $candidates = $query->limit(30)->get(['pengajuan_id', 'nomor_pengajuan', 'nama_vendor', 'tanggal_transaksi', 'user_id']);
            if ($candidates->isEmpty()) {
                return [
                    'is_duplicate' => false,
                    'signature' => $this->buildDuplicateSignature($normalizedVendor, $nominal, $tanggal),
                    'window_days' => $windowDays,
                ];
            }

            $best = null;
            $bestScore = 0.0;
            $bestDayDiff = PHP_INT_MAX;
            foreach ($candidates as $candidate) {
                $match = $this->matchVendor($candidate->nama_vendor, $inputVendor);
                $score = (float) ($match['match_percentage'] ?? 0);
                $candidateDate = \Carbon\Carbon::parse((string) $candidate->tanggal_transaksi)->startOfDay();
                $dayDiff = abs($candidateDate->diffInDays($inputDate, false));

                if ($score > $bestScore || ($score === $bestScore && $dayDiff < $bestDayDiff)) {
                    $bestScore = $score;
                    $best = $candidate;
                    $bestDayDiff = $dayDiff;
                }
            }

            // Smart threshold: duplicates trigger when vendor similarity >= 75% within configured day window.
            if ($best && $bestScore >= 75) {
                $owner = $best->user_id === $userId ? 'Anda sendiri' : 'pengguna lain';
                $dateContext = $bestDayDiff === 0
                    ? 'tanggal yang sama'
                    : "selisih {$bestDayDiff} hari";

                return [
                    'is_duplicate' => true,
                    'signature' => $this->buildDuplicateSignature($normalizedVendor, $nominal, $tanggal),
                    'similarity' => round($bestScore, 2),
                    'window_days' => $windowDays,
                    'day_difference' => $bestDayDiff,
                    'existing_pengajuan_id' => $best->pengajuan_id,
                    'existing_nomor_pengajuan' => $best->nomor_pengajuan,
                    'message' => "Kombinasi vendor+tanggal+nominal terdeteksi duplikat ({$bestScore}%, {$dateContext}) pada pengajuan #{$best->nomor_pengajuan} milik {$owner}.",
                ];
            }

            return [
                'is_duplicate' => false,
                'signature' => $this->buildDuplicateSignature($normalizedVendor, $nominal, $tanggal),
                'similarity' => round($bestScore, 2),
                'window_days' => $windowDays,
                'day_difference' => $bestDayDiff === PHP_INT_MAX ? null : $bestDayDiff,
            ];
        } catch (\Throwable $e) {
            \Log::warning('Smart duplicate detection failed: '.$e->getMessage());

            return [
                'is_duplicate' => false,
                'signature' => $this->buildDuplicateSignature($normalizedVendor, $nominal, $tanggal),
            ];
        }
    }

    private function buildDuplicateSignature(string $normalizedVendor, float $nominal, string $tanggal): string
    {
        $payload = trim($normalizedVendor).'|'.(int) $nominal.'|'.$tanggal;
        return hash('sha256', $payload);
    }

    public function validatePengajuan(Pengajuan $pengajuan): array
    {
        // 1. Check if we already have OCR data stored (from create step)
        $existingOCR = ValidasiAI::where('pengajuan_id', $pengajuan->pengajuan_id)
            ->where('jenis_validasi', 'ocr')
            ->first();

        $ocrData = null;
        if ($existingOCR && $existingOCR->hasil_ocr) {
            $ocrData = is_string($existingOCR->hasil_ocr) ? json_decode($existingOCR->hasil_ocr, true) : $existingOCR->hasil_ocr;
        }

        // 2. If no OCR data, try to perform it (legacy/fallback)
        if (! $ocrData) {
            $ocrData = $this->performOCR($pengajuan);
        }

        // Safety: ensure ocrData is an array to prevent crashes in following steps
        if (! is_array($ocrData)) {
            $ocrData = [
                'vendor' => $pengajuan->nama_vendor,
                'nominal' => $pengajuan->nominal,
                'tanggal' => $pengajuan->tanggal_transaksi->toDateString(),
                'confidence_score' => 0,
                'raw_text' => '',
            ];
        }

        // 3. Prepare Input Data from Pengajuan Model
        $inputData = [
            'nama_vendor' => $pengajuan->nama_vendor,
            'nominal' => $pengajuan->nominal,
            'tanggal_transaksi' => $pengajuan->tanggal_transaksi->toDateString(),
            'jenis_transaksi' => $pengajuan->jenis_transaksi,
        ];

        // 4. Validate using the centralized logic
        // Use validateOCRData logic but manually since we have the data already
        $results = [
            'ocr' => [
                'status' => ValidationStatus::VALID, // OCR itself is just data extraction
                'confidence' => $ocrData['confidence_score'] ?? 0,
                'message' => 'OCR Data Loaded',
                'data' => $ocrData,
            ],
        ];

        // Vendor Validation
        $vendorMatch = $this->matchVendor(
            $ocrData['vendor'] ?? null,
            $inputData['nama_vendor'],
            $ocrData['raw_text'] ?? null
        );

        $vendorMsg = 'Vendor Match: '.$vendorMatch['match_percentage'].'%';
        if ($vendorMatch['status'] === 'warning') {
            $vendorMsg = "⚠️ Perhatian: Nama vendor mirip ({$vendorMatch['match_percentage']}%). Perlu pengecekan manual.";
        } elseif ($vendorMatch['status'] === 'fail') {
            $vendorMsg = "❌ Vendor tidak cocok ({$vendorMatch['match_percentage']}%). Minimum 75% similarity.";
        }

        $results['vendor'] = [
            'status' => $vendorMatch['status'] === 'pass' ? ValidationStatus::VALID : ($vendorMatch['status'] === 'warning' ? ValidationStatus::INVALID : ValidationStatus::INVALID),
            'confidence' => $vendorMatch['match_percentage'],
            'message' => $vendorMsg,
            'raw_status' => $vendorMatch['status'], // Store raw status for overall check
        ];

        // Nominal Validation
        $nominalMatch = $this->matchNominal(
            $ocrData['nominal'] ?? 0,
            $inputData['nominal'],
            $ocrData['raw_text'] ?? null,
            $ocrData['all_detected_totals'] ?? []
        );
        $results['nominal'] = [
            'status' => $nominalMatch['status'] === 'pass' ? ValidationStatus::VALID : ValidationStatus::INVALID,
            'confidence' => 100, // Binary check mostly
            'message' => isset($nominalMatch['note']) ? $nominalMatch['note'] : ('Difference: '.($nominalMatch['difference'] ?? 0)),
        ];

        // Date Validation
        $dateMatch = $this->matchTanggal($ocrData['tanggal'] ?? null, $inputData['tanggal_transaksi'], $ocrData['raw_text'] ?? null);
        $results['tanggal'] = [
            'status' => $dateMatch['status'] === 'pass' ? ValidationStatus::VALID : ValidationStatus::INVALID,
            'confidence' => $dateMatch['status'] === 'pass' ? 100 : 0,
            'message' => $dateMatch['status'] === 'pass' ? 'Date Matched' : ($dateMatch['status'] === 'warning' ? 'Date Missing from OCR' : 'Date Mismatch'),
        ];

        // 5. Behavioral Anomaly
        $anomalyResult = $this->detectBehavioralAnomaly(
            $pengajuan->user_id,
            array_merge($inputData, ['waktu_transaksi' => $ocrData['time'] ?? null]),
            $pengajuan->pengajuan_id
        );
        $llmAnomaly = $this->extractLlmAnomalyDecision($ocrData);

        $anomalyMsg = 'Tidak ada anomali perilaku';
        if ($anomalyResult['is_anomaly']) {
            $anomalyMsg = '⚠️ Terdeteksi '.count($anomalyResult['anomalies']).' anomali: '.$anomalyResult['reason'];
        }
        $anomalyMsg .= ' | LLM Risk: '.($llmAnomaly['risk_score'] ?? 0).'% ('.strtoupper($llmAnomaly['risk_level'] ?? 'low').') - '.strtoupper($llmAnomaly['recommendation'] ?? 'approve');
        if (!empty($llmAnomaly['summary'])) {
            $anomalyMsg .= '. '.$llmAnomaly['summary'];
        }

        $results['anomali'] = [
            'status' => $anomalyResult['is_anomaly'] ? ValidationStatus::INVALID : ValidationStatus::VALID,
            'confidence' => $anomalyResult['is_anomaly'] ? max(10, 100 - (count($anomalyResult['anomalies']) * 20)) : 100,
            'message' => $anomalyMsg,
            'anomalies' => $anomalyResult['anomalies'] ?? [],
            'llm_anomaly' => $llmAnomaly,
            'raw_status' => $llmAnomaly['recommendation'] ?? 'approve',
        ];

        // 6. Tax Consistency
        $taxResult = $this->verifyTaxConsistency($ocrData);
        $results['pajak'] = [
            'status' => ($taxResult['status'] ?? 'pass') === 'pass' ? ValidationStatus::VALID : ValidationStatus::INVALID,
            'confidence' => ($taxResult['status'] ?? 'pass') === 'pass' ? 100 : 40,
            'message' => $taxResult['message'] ?? 'Validasi pajak selesai',
        ];

        // 7. Sequential Invoice
        $sequentialResult = $this->checkSequentialInvoice(
            $pengajuan->user_id,
            $inputData['nama_vendor'],
            $ocrData['invoice_number'] ?? null,
            $inputData['tanggal_transaksi'],
            $pengajuan->pengajuan_id
        );
        $results['sekuensial'] = [
            'status' => $sequentialResult['is_sequential'] ? ValidationStatus::INVALID : ValidationStatus::VALID,
            'confidence' => $sequentialResult['is_sequential'] ? 10 : 100,
            'message' => $sequentialResult['message'] ?? 'Invoice tidak berurutan',
        ];

        // 8. Check Duplicate
        $dupResult = $this->checkDuplicate($pengajuan);
        $results['duplicate'] = array_merge($dupResult, [
            'confidence' => ($dupResult['status'] ?? 'pass') === 'pass' ? 100 : 0,
        ]);

        $this->storeValidationResults($pengajuan, $results);

        return $this->getOverallResult($results);
    }

    public function saveInitialOCR(Pengajuan $pengajuan, array $ocrData): void
    {
        if (empty($ocrData)) {
            \Log::warning('ValidasiAIService: Attempted to save empty initial OCR data');

            return;
        }

        // Save the OCR result immediately as 'ocr' type
        ValidasiAI::updateOrCreate(
            [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'jenis_validasi' => 'ocr',
            ],
            [
                'status' => ValidationStatus::VALID,
                'confidence_score' => $ocrData['confidence_score'] ?? 0,
                'hasil_ocr' => $ocrData,
                'pesan_validasi' => 'Initial OCR Data Captured',
                'is_blocking' => false,
                'validated_at' => now(),
            ]
        );
    }

    /**
     * Check for duplicate file based on vendor + nominal + tanggal
     */
    public function checkFileDuplicate($file): array
    {
        // Legacy method kept for backward compatibility
        // Actual duplicate check is done in TesseractService
        return [
            'is_duplicate' => false,
        ];
    }

    /**
     * Parse OCR text from client (called by Controller)
     */
    public function validateOCR($file, ?string $ocrText = null): array
    {
        // Delegate to TesseractService (OCR + Groq/LLM pipeline)
        return $this->tesseractService->processReceiptOCR($file, $ocrText ?? '');
    }

    /**
     * Match vendor name with OCR data
     */
    public function matchVendor(?string $ocrVendor, ?string $inputVendor, ?string $rawText = null): array
    {
        $ocrVendor = strtolower(trim($ocrVendor ?? ''));
        $inputVendor = strtolower(trim($inputVendor ?? ''));

        if (empty($inputVendor)) {
            return ['status' => 'fail', 'match_percentage' => 0];
        }

        // 1. SECURITY: If OCR vendor is broken/incomplete, verify in raw text
        if (! $this->verifyVendorInRawText($ocrVendor, $inputVendor, $rawText)) {
            return ['status' => 'fail', 'match_percentage' => 0];
        }

        // 2. Normalize: remove hyphens, double spaces, decode HTML
        $ocrClean = $this->normalizeVendorName($ocrVendor);
        $inputClean = $this->normalizeVendorName($inputVendor);

        if (empty($ocrVendor) && empty($rawText)) {
            return ['status' => 'warning', 'match_percentage' => 0];
        }

        // 3. Platform Detection & Bonus
        $inputPlatform = $this->detectPlatform($inputClean);
        $ocrPlatform = $this->detectPlatform($ocrClean);
        $platformBonus = ($inputPlatform && $ocrPlatform && $inputPlatform === $ocrPlatform) ? 8 : 0;

        // 4. Calculate Similarity
        $finalPercent = $this->calculateVendorSimilarity($ocrClean, $inputClean, $rawText, $platformBonus);

        // 5. Determine Status
        $status = $this->getVendorStatus($finalPercent);

        return [
            'status' => $status,
            'match_percentage' => $finalPercent,
        ];
    }

    private function normalizeVendorName(string $name): string
    {
        $name = strtolower(trim($name));

        // Remove leading/trailing symbols and artifacts (e.g. -.-.-.- or |)
        $name = preg_replace('/^[^a-z0-9]+|[^a-z0-9]+$/i', '', $name);

        $name = html_entity_decode($name);
        $name = str_replace('u0026', '&', $name);
        $name = str_replace(['-', '_', '.', '|', ':'], ' ', $name);

        // Remove trailing long numeric IDs often attached in transfer receipts.
        $name = preg_replace('/(?:\s+\d{5,})+$/', '', $name) ?? $name;

        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function verifyVendorInRawText(string $ocrVendor, string $inputVendor, ?string $rawText): bool
    {
        if (empty($inputVendor)) {
            return false;
        }

        $ocrAlphanumeric = strlen(preg_replace('/[^a-z0-9]/i', '', $ocrVendor));
        if ($ocrAlphanumeric < 3) {
            if (empty($rawText) || stripos($rawText, $inputVendor) === false) {
                return false;
            }
        }

        return true;
    }

    private function detectPlatform(string $name): ?string
    {
        foreach (self::PLATFORM_ALIASES as $platform => $aliases) {
            foreach ($aliases as $alias) {
                if ($name === $alias || str_contains($name, $platform)) {
                    return $platform;
                }
            }
        }

        return null;
    }

    private function calculateVendorSimilarity(string $ocrClean, string $inputClean, ?string $rawText, int $platformBonus): float
    {
        similar_text($ocrClean, $inputClean, $percent);

        $ocrCore = $this->stripVendorNoise($ocrClean);
        $inputCore = $this->stripVendorNoise($inputClean);
        if ($ocrCore !== '' && $inputCore !== '') {
            similar_text($ocrCore, $inputCore, $corePercent);
            $percent = max($percent, $corePercent);
            if ($ocrCore === $inputCore) {
                $percent = max($percent, 98.0);
            }
        }

        $bonus = 0;
        if (! empty($ocrClean) && (str_contains($ocrClean, $inputClean) || str_contains($inputClean, $ocrClean))) {
            $lenRatio = strlen($inputClean) / max(strlen($ocrClean), 1);
            if ($lenRatio > 0.6) {
                $bonus += 3;
            }
            if ($percent >= 70 && str_starts_with($ocrClean, $inputClean)) {
                $bonus += 4;
            }
        }

        // Handle "Merchant (Platform)" format
        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $ocrClean, $matches)) {
            similar_text(trim($matches[1]), $inputClean, $p1);
            similar_text(trim($matches[2]), $inputClean, $p2);
            $percent = max($percent, $p1 + 2, $p2);
        }

        $finalPercent = $percent + $bonus + $platformBonus;

        // Reverse Search Bonus
        if (! empty($rawText) && strlen($inputClean) > 3) {
            $inputPattern = preg_replace('/\s+/', '\\s*', preg_quote($inputClean, '/'));
            if (preg_match("/{$inputPattern}/i", $rawText)) {
                $finalPercent += 5;
            }
        }

        if (! empty($rawText) && strlen($inputCore) > 2) {
            $inputCorePattern = preg_replace('/\s+/', '\\s*', preg_quote($inputCore, '/'));
            if (preg_match("/{$inputCorePattern}/i", strtolower($rawText))) {
                $finalPercent += 6;
            }
        }

        // Levenshtein Fallback for short names if similarity is borderline
        if ($finalPercent < 80 && $finalPercent > 60) {
            $lev = levenshtein($ocrClean, $inputClean);
            $maxLen = max(strlen($ocrClean), strlen($inputClean));
            if ($maxLen > 0) {
                $levPercent = (1 - ($lev / $maxLen)) * 100;
                $finalPercent = max($finalPercent, $levPercent);
            }
        }

        return round(min(100, $finalPercent), 2);
    }

    private function stripVendorNoise(string $name): string
    {
        $clean = strtolower(trim($name));
        if ($clean === '') {
            return '';
        }

        // Remove numeric IDs and common legal words for matching purposes only.
        $clean = preg_replace('/\b\d{4,}\b/', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\b(pt|cv|tbk|persero|indonesia|official|merchant|store|shop)\b/', ' ', $clean) ?? $clean;
        $clean = preg_replace('/[^a-z0-9\s]/', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s+/', ' ', trim($clean)) ?? $clean;

        return trim($clean);
    }

    private function getVendorStatus(float $percent): string
    {
        if ($percent >= 80) {
            return 'pass';
        }
        if ($percent >= 75) {
            return 'warning';
        }

        return 'fail';
    }

    /**
     * Match nominal (called by Controller)
     */
    public function matchNominal($ocrNominal, $inputNominal, ?string $rawText = null, array $allDetectedTotals = []): array
    {
        $ocrNominal = (float) $ocrNominal;
        $inputNominal = (float) $inputNominal;

        // 1. Direct match with OCR nominal
        if ($this->isCloseMatch($ocrNominal, $inputNominal)) {
            return ['status' => 'pass', 'difference' => 0];
        }

        // 2. Check against all detected totals (from AI)
        $smartMatch = $this->findInDetectedTotals($inputNominal, $allDetectedTotals);
        if ($smartMatch) {
            return $smartMatch;
        }

        // 3. Strict mode: only fallback to raw text when OCR nominal is missing.
        if ($ocrNominal <= 0) {
            $rawMatch = $this->findInRawText($inputNominal, $rawText, $ocrNominal);
            if ($rawMatch) {
                return $rawMatch;
            }
        }

        return ['status' => 'fail', 'difference' => (int) abs($ocrNominal - $inputNominal)];
    }

    private function isCloseMatch(float $val1, float $val2): bool
    {
        // Exact nominal match (2 decimal precision)
        return round($val1, 2) === round($val2, 2);
    }

    private function findInDetectedTotals(float $inputNominal, array $allDetectedTotals): ?array
    {
        if (empty($allDetectedTotals)) {
            return null;
        }

        foreach ($allDetectedTotals as $total) {
            $amount = (float) ($total['amount'] ?? 0);
            $label = strtolower(trim((string) ($total['label'] ?? '')));

            // Skip non-final amount labels to avoid matching admin/tax as nominal utama.
            if ($label !== '' && preg_match('/\b(admin|administrasi|fee|service|ppn|pajak|tax|diskon|voucher|potongan)\b/', $label)) {
                continue;
            }

            if ($this->isCloseMatch($amount, $inputNominal)) {
                return [
                    'status' => 'pass',
                    'difference' => 0,
                    'message' => 'Nominal ditemukan sebagai '.($total['label'] ?? 'Total Transaksi'),
                    'note' => 'Nominal cocok dengan: '.($total['label'] ?? 'Total Transaksi'),
                ];
            }
        }

        return null;
    }

    private function findInRawText(float $inputNominal, ?string $rawText, float $ocrNominal): ?array
    {
        if (empty($rawText)) {
            return null;
        }

        $normalizedRaw = preg_replace('/(Rp\.?|IDR)/i', ' ', $rawText);

        // Match numbers with word boundaries to avoid "3" matching "3.000"
        if (preg_match_all('/(?<=\s|^|[\/])[\d.,]+(?=\s|$|[\/])/', $normalizedRaw, $matches)) {
            foreach ($matches[0] as $match) {
                $clean = preg_replace('/[^\d.,]/', '', $match);
                if (empty($clean) || $clean === '.' || $clean === ',') {
                    continue;
                }

                $digits = strlen(preg_replace('/[^\d]/', '', $clean));
                $inputDigits = strlen(preg_replace('/[^\d]/', '', (string) (int) $inputNominal));

                // Strict digit count check to avoid matching 15 with 15.000
                if (abs($digits - $inputDigits) > 1 && $inputNominal > 0) {
                    continue;
                }

                if ($this->isMatchInInterpretations($clean, $inputNominal)) {
                    return [
                        'status' => 'pass',
                        'difference' => abs($ocrNominal - $inputNominal),
                        'message' => 'Nominal ditemukan di teks struk',
                        'note' => 'Nominal ditemukan di dokumen (raw text search)',
                    ];
                }
            }
        }

        return null;
    }

    private function isMatchInInterpretations(string $clean, float $inputNominal): bool
    {
        // Case A: 10.000 (ID)
        $valA = (float) str_replace('.', '', $clean);
        // Case B: 10.000,00 (ID decimal)
        $valB = (float) str_replace(',', '.', str_replace('.', '', $clean));
        // Case C: 10,000.00 (US)
        $valC = (float) str_replace(',', '', $clean);

        return $this->isCloseMatch($valA, $inputNominal) ||
               $this->isCloseMatch($valB, $inputNominal) ||
               $this->isCloseMatch($valC, $inputNominal);
    }

    /**
     * Match date with OCR data
     */
    public function matchTanggal(?string $ocrDate, ?string $inputDate, ?string $rawText = null): array
    {
        if (! $inputDate) {
            return ['status' => 'fail'];
        }
        $inputDate = trim($inputDate);

        // 1. If OCR date is missing, attempt to find it in raw text
        if (empty($ocrDate) && ! empty($rawText)) {
            $ocrDate = $this->extractDateFromRawText($rawText, $inputDate);
        }

        if (! $ocrDate) {
            return ['status' => 'fail'];
        }

        // 2. Normalize and Parse
        try {
            $ocrDateClean = $this->normalizeDateString($ocrDate);

            if (str_contains($ocrDateClean, $inputDate) || str_contains($inputDate, $ocrDateClean)) {
                return ['status' => 'pass'];
            }

            $timestamp = $this->parseDateToTimestamp($ocrDateClean);
            if ($timestamp) {
                $d1 = date('Y-m-d', $timestamp);
                if ($d1 === $inputDate) {
                    return ['status' => 'pass'];
                }
            }

            // 3. SMART HEALING: If AI/OCR misread the date, but we can find the CORRECT date in raw text
            if (! empty($rawText)) {
                $healedDate = $this->extractDateFromRawText($rawText, $inputDate);
                if ($healedDate && $this->parseDateToTimestamp($this->normalizeDateString($healedDate)) === strtotime($inputDate)) {
                    \Log::info('ValidasiAIService: Date healed from raw text', ['original' => $ocrDate, 'healed' => $healedDate]);

                    return ['status' => 'pass', 'healed' => true];
                }
            }

            return ['status' => 'fail'];
        } catch (\Exception $e) {
            if (str_contains($ocrDate, $inputDate)) {
                return ['status' => 'pass'];
            }

            return ['status' => 'fail'];
        }
    }

    private function extractDateFromRawText(string $rawText, string $inputDate): ?string
    {
        $datePatterns = [
            '/\d{1,2}[\s\-\/\.\'\"]+(jan|feb|mar|apr|mei|jun|jul|agt|aug|sep|okt|oct|nov|des|dec)\s+\d{2,4}/i',
            '/\d{1,2}[-\/\.\'\"]\d{1,2}[-\/\.\'\"]\d{2,4}/',
            '/\d{4}[-\/\.\'\"]\d{1,2}[-\/\.\'\"]\d{1,2}/',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $rawText, $matches)) {
                return $matches[0];
            }
        }

        // SMART: Scan Ref Numbers for YYMMDD or DDMMYY
        if (preg_match('/(?:Ref|Referensi|ID)\s*[:]?\s*(\d{6,})/i', $rawText, $matches)) {
            $refNo = $matches[1];
            $targetYYMMDD = substr(str_replace('-', '', $inputDate), 2);
            $targetDDMMYY = date('dmy', strtotime($inputDate));

            if (str_contains($refNo, $targetYYMMDD) || str_contains($refNo, $targetDDMMYY)) {
                return $inputDate; // Found match in Ref No
            }
        }

        return null;
    }

    private function normalizeDateString(string $date): string
    {
        $monthsMap = [
            'januari' => '01', 'jan' => '01', 'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03', 'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05', 'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07', 'agustus' => '08', 'agt' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09', 'oktober' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11', 'desember' => '12', 'des' => '12', 'dec' => '12',
        ];

        $dateLower = strtolower($date);
        foreach ($monthsMap as $name => $num) {
            if (str_contains($dateLower, $name)) {
                $dateLower = str_replace($name, ' '.$num.' ', $dateLower);
                break;
            }
        }

        $clean = preg_replace('/[,\.]/', ' ', $dateLower);
        $clean = preg_replace('/\s+\d{1,2}:\d{2}.*$/', '', $clean); // Remove time

        return preg_replace('/\s+/', '-', trim($clean));
    }

    private function parseDateToTimestamp(string $dateClean): ?int
    {
        $timestamp = strtotime($dateClean);
        if (! $timestamp) {
            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateClean, $matches)) {
                $timestamp = strtotime($matches[3].'-'.$matches[2].'-'.$matches[1]);
            }
        }

        return $timestamp ?: null;
    }

    public function validateAndRefineNominal(?float $ocrNominal, ?float $inputNominal, ?string $rawText = null): array
    {
        if (! $inputNominal) {
            return [
                'nominal' => 0,
                'is_valid' => false,
                'reason' => 'Nominal tidak ditemukan',
                'confidence_adjustment' => -30,
                'sanity_check' => 'FAIL: No nominal provided',
            ];
        }

        $nominal = (float) $inputNominal;
        $adjustments = [];
        $confidenceAdjust = 0;

        if ($nominal < 100) {
            $adjustments[] = 'Nominal terlalu kecil (< Rp 100)';
            $confidenceAdjust -= 20;
        }

        if ($nominal > 500000000) {
            $adjustments[] = 'Nominal sangat besar (> Rp 500 juta) - mungkin ID atau referensi';
            $confidenceAdjust -= 50;
        }

        if ($this->looksLikePhoneNumber((string) (int) $nominal)) {
            $adjustments[] = 'Nominal terlihat seperti nomor telepon';
            $confidenceAdjust -= 60;
        }

        if ($this->looksLikeTransactionId((string) (int) $nominal)) {
            $adjustments[] = 'Nominal terlihat seperti ID transaksi (terlalu panjang)';
            $confidenceAdjust -= 70;
        }

        if ($ocrNominal && abs($ocrNominal - $nominal) > ($nominal * 0.5)) {
            $adjustments[] = sprintf('Nominal input (%.0f) berbeda jauh dengan OCR (%.0f) - perbedaan > 50%%', $nominal, $ocrNominal);
            $confidenceAdjust -= 15;
        }

        if ($this->isLikelyQuantity((int) $nominal)) {
            $adjustments[] = 'Nominal terlihat seperti quantity/jumlah barang, bukan harga';
            $confidenceAdjust -= 40;
        }

        $isValid = $nominal >= 100 && $nominal <= 500000000 && ! $this->looksLikePhoneNumber((string) (int) $nominal) && ! $this->looksLikeTransactionId((string) (int) $nominal);

        return [
            'nominal' => $nominal,
            'is_valid' => $isValid,
            'reasons' => $adjustments ?: ['Nominal valid'],
            'confidence_adjustment' => $confidenceAdjust,
            'sanity_check' => $isValid ? 'PASS' : 'FAIL: '.implode(', ', $adjustments),
        ];
    }

    private function looksLikePhoneNumber(string $num): bool
    {
        $clean = trim($num);
        if (strlen($clean) < 10 || strlen($clean) > 15) {
            return false;
        }

        return preg_match('/^(08|628|\\+628|\\+62|62)/', $clean) === 1;
    }

    private function looksLikeTransactionId(string $num): bool
    {
        $len = strlen(trim($num));

        return $len > 12 || $len > 15;
    }

    private function isLikelyQuantity(int $num): bool
    {
        return $num >= 1 && $num <= 100 && $num !== 50000 && $num !== 25000 && $num !== 100000;
    }

    public function extractMultipleInvoiceAmounts(?array $detectedTotals): array
    {
        if (empty($detectedTotals)) {
            return [
                'has_multiple' => false,
                'amounts' => [],
                'recommended' => null,
            ];
        }

        $amounts = [];
        foreach ($detectedTotals as $total) {
            if (isset($total['amount']) && isset($total['label'])) {
                $amounts[] = [
                    'value' => (int) $total['amount'],
                    'label' => $total['label'],
                    'priority' => $total['priority'] ?? 999,
                    'display' => sprintf('%s - Rp %s', $total['label'], number_format($total['amount'], 0, ',', '.')),
                ];
            }
        }

        if (count($amounts) <= 1) {
            return [
                'has_multiple' => false,
                'amounts' => $amounts,
                'recommended' => $amounts[0] ?? null,
            ];
        }

        usort($amounts, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        $uniqueAmounts = [];
        $seen = [];
        foreach ($amounts as $amount) {
            if (! isset($seen[$amount['value']])) {
                $uniqueAmounts[] = $amount;
                $seen[$amount['value']] = true;
            }
        }

        return [
            'has_multiple' => count($uniqueAmounts) > 1,
            'amounts' => $uniqueAmounts,
            'recommended' => $uniqueAmounts[0] ?? null,
            'instruction' => 'Pilih nominal yang sesuai dengan bukti transaksi. Jika ada TOTAL TAGIHAN, prioritas tertinggi.',
        ];
    }

    public function refineVendorByTransactionType(
        ?string $transactionType,
        ?string $ocrVendor,
        ?string $ocrPlatform,
        ?string $userInputVendor = null
    ): array {
        $refinedVendor = null;
        $suggestion = null;
        $confidence = 0;

        switch ($transactionType) {
            case 'marketplace':
                if ($ocrVendor && $ocrVendor !== $ocrPlatform) {
                    $refinedVendor = $ocrVendor;
                    $suggestion = "Marketplace detected. Using seller name from 'Penjual:' field.";
                    $confidence = 95;
                } elseif ($ocrPlatform) {
                    $refinedVendor = $ocrPlatform;
                    $suggestion = 'Using platform name (seller name not found).';
                    $confidence = 75;
                }
                break;

            case 'transfer_direct':
                $refinedVendor = $ocrVendor ?? $userInputVendor;
                $suggestion = "Transfer detected. Using recipient name from 'Penerima:' or 'Ke:' field.";
                $confidence = 90;
                break;

            case 'transport':
                if ($ocrVendor && $ocrVendor !== $ocrPlatform && strlen($ocrVendor) > 3) {
                    $refinedVendor = $ocrVendor;
                    $suggestion = 'Ride-sharing detected. Using driver name.';
                    $confidence = 90;
                } elseif ($ocrPlatform) {
                    $refinedVendor = $ocrPlatform;
                    $suggestion = 'Using platform name (driver name not found).';
                    $confidence = 85;
                }
                break;

            default:
                $refinedVendor = $userInputVendor ?? $ocrVendor ?? $ocrPlatform;
                $suggestion = 'No specific transaction type. Using OCR result or user input.';
                $confidence = 70;
        }

        return [
            'refined_vendor' => $refinedVendor,
            'suggestion' => $suggestion,
            'confidence' => $confidence,
            'transaction_type' => $transactionType,
            'ocr_vendor' => $ocrVendor,
            'ocr_platform' => $ocrPlatform,
            'user_input' => $userInputVendor,
        ];
    }

    public function detectMultiInvoiceScenario(?string $rawText): array
    {
        if (! $rawText) {
            return [
                'is_multi_invoice' => false,
                'invoice_count' => 1,
                'scenario' => 'unknown',
                'recommended_action' => 'single_invoice',
            ];
        }

        $text = strtoupper($rawText);

        $scenarios = [
            'tokopedia_multi' => [
                'pattern' => '/TOTAL\s+BELANJA\s+\d+\s+INVOICE/',
                'name' => 'Tokopedia Multi-Invoice',
                'priority_total' => 'TOTAL TAGIHAN',
            ],
            'shopee_multi' => [
                'pattern' => '/TOTAL\s+BELANJA.*?INVOICE/',
                'name' => 'Shopee Multi-Invoice',
                'priority_total' => 'TOTAL TAGIHAN',
            ],
            'bundled_purchases' => [
                'pattern' => '/PAKET\s+\d+|BUNDLE|LOT\s+\d+/',
                'name' => 'Bundled/Multi-item Purchase',
                'priority_total' => 'GRAND TOTAL|TOTAL BELANJA',
            ],
        ];

        $detected = [];
        foreach ($scenarios as $key => $scenario) {
            if (preg_match($scenario['pattern'], $text)) {
                $detected[] = [
                    'type' => $key,
                    'name' => $scenario['name'],
                    'priority_total' => $scenario['priority_total'],
                ];
            }
        }

        if (count($detected) > 0) {
            return [
                'is_multi_invoice' => true,
                'scenarios_detected' => $detected,
                'primary_scenario' => $detected[0]['name'],
                'priority_total_label' => $detected[0]['priority_total'],
                'instruction' => 'Use TOTAL TAGIHAN or final combined total, NOT per-item totals',
                'recommended_action' => 'use_final_total',
            ];
        }

        return [
            'is_multi_invoice' => false,
            'invoice_count' => 1,
            'scenario' => 'single_invoice',
            'recommended_action' => 'standard_validation',
        ];
    }

    public function extractMultipleInvoiceTotals(?string $rawText): array
    {
        if (! $rawText) {
            return [
                'found_totals' => [],
                'best_candidate' => null,
                'multi_invoice_detected' => false,
            ];
        }

        $text = $rawText;
        $totals = [];

        $patterns = [
            'total_tagihan' => '/TOTAL\s+TAGIHAN\s*[:=]?\s*Rp\.?\s*([\d.,]+)/i',
            'total_belanja_n' => '/TOTAL\s+BELANJA\s+\d+\s+INVOICE\s*[:=]?\s*Rp\.?\s*([\d.,]+)/i',
            'total_bayar' => '/TOTAL\s+BAYAR\s*[:=]?\s*Rp\.?\s*([\d.,]+)/i',
            'grand_total' => '/GRAND\s+TOTAL\s*[:=]?\s*Rp\.?\s*([\d.,]+)/i',
            'total_belanja' => '/TOTAL\s+BELANJA\s*[:=]?\s*Rp\.?\s*([\d.,]+)/i',
        ];

        foreach ($patterns as $label => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $value = $this->cleanCurrency($match);
                    if ($value) {
                        $totals[] = [
                            'label' => $label,
                            'raw' => $match,
                            'value' => $value,
                            'priority' => array_search($label, array_keys($patterns)),
                        ];
                    }
                }
            }
        }

        if (empty($totals)) {
            return [
                'found_totals' => [],
                'best_candidate' => null,
                'multi_invoice_detected' => false,
            ];
        }

        usort($totals, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return [
            'found_totals' => $totals,
            'best_candidate' => $totals[0] ?? null,
            'multi_invoice_detected' => count($totals) > 1,
            'all_candidates_count' => count($totals),
        ];
    }

    private function cleanCurrency(string $val): ?float
    {
        $clean = trim($val);
        $clean = str_replace(['Rp', '.', ' '], '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function extractVendorFromLines(array $lines): ?string
    {
        $platforms = ['tokopedia', 'shopee', 'bukalapak', 'lazada', 'blibli', 'gojek', 'grab', 'traveloka', 'tiktok', 'dana', 'ovo', 'gopay'];
        $blocklist = ['invoice', 'nota', 'struk', 'pembayaran', 'bukti', 'reimbursement', 'tax', 'bill', 'receipt', 'transaksi berhasil', 'transfer berhasil', 'pembayaran berhasil', 'rincian transaksi', 'detail transaksi', 'sumber dana', 'total bayar', 'total tagihan'];

        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn ($l) => strlen($l) > 2));

        // Strategy 1: Look for explicit markers (Penjual:, Merchant:, etc.)
        foreach ($lines as $index => $line) {
            // Check for "Penjual :" or "Merchant :" pattern
            // Also check for "Diterbitkan atas nama" which usually precedes seller name on Marketplace invoices
            // Also check for "Tujuan" or "Ke" or "Penerima" for Transfers
            if (preg_match('/(penjual|merchant|toko|warung|restoran|outlet|seller|diterbitkan atas nama|tujuan|ke|penerima)\s*[:]?\s*(.*)/i', $line, $matches)) {
                if (! empty($matches[2]) && strlen(trim($matches[2])) > 2) {
                    $vendor = trim($matches[2]);
                    // If the extracted name is just a platform name, ignore it and keep looking
                    // e.g. "Penjual : Tokopedia" (unlikely but possible)
                    $lowerVendor = strtolower($vendor);
                    foreach ($platforms as $p) {
                        if ($lowerVendor === $p) {
                            continue 2;
                        }
                    }

                    return $vendor;
                }
                // Sometimes the name is on the next line
                if (isset($lines[$index + 1])) {
                    $vendor = trim($lines[$index + 1]);
                    // Check if next line is not empty or weird
                    if (strlen($vendor) > 2) {
                        // Double check if the next line is NOT another label or number
                        if (! preg_match('/(total|jumlah|harga|qty|rp|idr|\d+)/i', $vendor)) {
                            return $vendor;
                        }
                    }
                }
            }
        }

        // Strategy 2: If no explicit marker, try to find the best candidate at the top
        foreach ($lines as $line) {
            $lower = strtolower($line);

            // Skip platform names if they appear alone (we want the merchant, not the platform)
            // But if it's "Gojek" invoice for a ride, Gojek might be the vendor.
            // However, usually we prefer specific names.
            $isPlatform = false;
            foreach ($platforms as $platform) {
                if ($lower === $platform || $lower === $platform.' invoice') {
                    $isPlatform = true;
                    break;
                }
            }
            if ($isPlatform) {
                continue;
            }

            // Skip common headers
            $isHeader = false;
            foreach ($blocklist as $block) {
                if (str_contains($lower, $block)) {
                    $isHeader = true;
                    break;
                }
            }
            if ($isHeader) {
                continue;
            }

            // Skip dates or purely numeric lines
            if (preg_match('/^\d+$/', $line) || preg_match('/\d{1,2}[-\/\.]\d{1,2}/', $line)) {
                continue;
            }

            return $line; // First reasonable line
        }

        return $lines[0] ?? null;
    }

    private function extractNominalFromLines(array $lines): ?float
    {
        $maxVal = 0;
        $candidates = [];

        foreach ($lines as $line) {
            // Look for "Total", "Jumlah", "Bayar" to boost confidence
            $isTotalLine = preg_match('/(total|jumlah|bayar|tagihan|amount|charge)/i', $line);

            // Regex to find currency amounts:
            // Matches: Rp 10.000, 10.000,00, 1,000,000.00
            if (preg_match_all('/(?:Rp\.?|IDR)?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?)/i', $line, $matches)) {
                foreach ($matches[1] as $match) {
                    // Clean up string to be float-friendly
                    // Heuristic: If it has dots and commas, determine which is decimal

                    $clean = $match;
                    $hasDot = str_contains($clean, '.');
                    $hasComma = str_contains($clean, ',');

                    // Indonesia standard: dot = thousand, comma = decimal (e.g., 1.000.000,00)
                    // US standard: comma = thousand, dot = decimal (e.g., 1,000,000.00)

                    // Assumption: If both exist, the last one is likely decimal separator unless it appears multiple times.
                    // But standard OCR in Indonesia usually follows ID formatting.

                    if ($hasDot && $hasComma) {
                        // Assume Indonesia format: remove dots, replace comma with dot
                        $clean = str_replace('.', '', $clean);
                        $clean = str_replace(',', '.', $clean);
                    } elseif ($hasDot) {
                        // Check if dot is thousand separator or decimal
                        // If multiple dots, it's definitely thousand separator
                        if (substr_count($clean, '.') > 1) {
                            $clean = str_replace('.', '', $clean);
                        } else {
                            // Single dot. Could be 10.000 (ten thousand) or 10.50 (ten point five)
                            // In IDR, decimals are rare for large amounts, but usually 10.000 means ten thousand.
                            // If followed by 3 digits, likely thousand separator.
                            if (preg_match('/\.\d{3}$/', $clean)) {
                                $clean = str_replace('.', '', $clean);
                            } else {
                                // Likely decimal (rare in IDR context without comma, but possible)
                                // Actually, often OCR reads 50.000 as 50.000. So let's assume dot is thousand sep if result > 1000
                                $temp = str_replace('.', '', $clean);
                                if ((float) $temp > 1000) {
                                    $clean = $temp;
                                }
                            }
                        }
                    } elseif ($hasComma) {
                        // Comma is usually decimal in IDR, or thousand in US
                        // Assume decimal if followed by 1-2 digits
                        $clean = str_replace(',', '.', $clean);
                    }

                    if (is_numeric($clean)) {
                        $val = (float) $clean;

                        // SANITY CHECKS
                        // 1. Too large? (Cap at 10 Billion for now to filter out IDs)
                        if ($val > 10000000000) {
                            continue;
                        }

                        // 2. Too small?
                        if ($val < 100) {
                            continue;
                        }

                        // 3. Looks like a date? (e.g. 2024) - handled by regex context usually but good to be safe
                        if ($val >= 2020 && $val <= 2030 && ! $isTotalLine) {
                            continue;
                        }

                        // 4. Looks like a phone number? (Starts with 08/628 and > 9 digits)
                        // Note: $val is float, leading zeros are gone.
                        // But original match might have it.
                        // Check original match string
                        if (preg_match('/^(08|628)/', str_replace(['.', ','], '', $match)) && strlen(str_replace(['.', ','], '', $match)) > 9) {
                            continue;
                        }

                        $candidates[] = [
                            'val' => $val,
                            'is_total' => $isTotalLine,
                        ];
                    }
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // Sort candidates: Prefer 'is_total' lines, then highest value
        usort($candidates, function ($a, $b) {
            if ($a['is_total'] !== $b['is_total']) {
                return $b['is_total'] <=> $a['is_total']; // True first
            }

            return $b['val'] <=> $a['val']; // Highest value first
        });

        return $candidates[0]['val'];
    }

    private function extractDateFromLines(array $lines): ?string
    {
        // 1. Numeric format: DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD
        $datePatternNumeric = '/\b(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})\b|\b(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})\b/';

        // 2. Text format: DD Month YYYY (Indonesian & English)
        // Need to handle "Agustus", "Januari", etc.
        $months = 'Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec';
        $datePatternText = "/\b(\d{1,2})\s+($months)\s+(\d{4})\b/i";

        foreach ($lines as $line) {
            // Check text format first (more specific)
            if (preg_match($datePatternText, $line, $matches)) {
                $day = $matches[1];
                $monthStr = $matches[2];
                $year = $matches[3];

                $month = $this->monthNameToNumber($monthStr);

                return "$year-$month-$day";
            }

            // Check numeric format
            if (preg_match($datePatternNumeric, $line, $matches)) {
                if (! empty($matches[1])) {
                    // DD-MM-YYYY
                    return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                } else {
                    // YYYY-MM-DD
                    return "{$matches[4]}-{$matches[5]}-{$matches[6]}";
                }
            }
        }

        return null;
    }

    private function monthNameToNumber($monthName)
    {
        $monthName = strtolower(substr($monthName, 0, 3));
        $map = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'mei' => '05', 'may' => '05', 'jun' => '06', 'jul' => '07',
            'agu' => '08', 'aug' => '08', 'sep' => '09', 'okt' => '10', 'oct' => '10',
            'nov' => '11', 'des' => '12', 'dec' => '12',
        ];

        return $map[$monthName] ?? '01';
    }

    private function performOCR(Pengajuan $pengajuan): array
    {
        if (! config('reimbursement.ai_validation.ocr_enabled')) {
            return ['status' => 'disabled', 'confidence' => 0];
        }

        try {
            $filePath = \Storage::disk('local')->path($pengajuan->file_bukti);
            if (! file_exists($filePath)) {
                return ['status' => 'fail', 'confidence' => 0, 'message' => 'File not found'];
            }

            $ocrResult = [
                'vendor' => $this->extractVendorName($pengajuan->nama_vendor),
                'nominal' => $pengajuan->nominal,
                'tanggal' => $pengajuan->tanggal_transaksi->toDateString(),
                'confidence_score' => rand(85, 99),
                'raw_text' => 'Legacy/Fallback OCR',
            ];

            $threshold = config('reimbursement.ai_validation.ocr_confidence_threshold');
            $ocrResult['status'] = $ocrResult['confidence'] >= $threshold ? 'pass' : 'fail';

            return $ocrResult;
        } catch (\Exception $e) {
            return ['status' => 'error', 'confidence' => 0, 'message' => $e->getMessage()];
        }
    }

    private function checkDuplicate(Pengajuan $pengajuan): array
    {
        if (! config('reimbursement.ai_validation.duplicate_check_enabled')) {
            return ['status' => 'disabled'];
        }

        try {
            // Optimization: Use the hash already stored in the model instead of re-calculating md5_file
            $fileHash = $pengajuan->file_hash;

            if (! $fileHash) {
                // Fallback only if model hash is missing
                $filePath = \Storage::disk('local')->path($pengajuan->file_bukti);

                if (file_exists($filePath)) {
                    $fileHash = md5_file($filePath);
                }
            }

            // Check against other pengajuan by hash first (Content Match)
            if ($fileHash) {
                $duplicateByHash = Pengajuan::where('file_hash', $fileHash)
                    ->where('pengajuan_id', '!=', $pengajuan->pengajuan_id)
                    ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance', 'validasi_ai'])
                    ->first();

                if ($duplicateByHash) {
                    return [
                        'status' => 'fail',
                        'duplicate_found' => true,
                        'type' => 'hash',
                        'duplicate_pengajuan_id' => $duplicateByHash->pengajuan_id,
                        'message' => 'File duplikat terdeteksi (Hash Match)',
                    ];
                }
            }

            // Smart duplicate check (vendor fuzzy + nominal + date window)
            $smartDuplicate = $this->detectPotentialDuplicateSmart(
                (string) $pengajuan->user_id,
                (string) $pengajuan->nama_vendor,
                (float) $pengajuan->nominal,
                $pengajuan->tanggal_transaksi ? $pengajuan->tanggal_transaksi->toDateString() : null,
                (int) $pengajuan->pengajuan_id
            );

            if (($smartDuplicate['is_duplicate'] ?? false) === true) {
                return [
                    'status' => 'fail',
                    'duplicate_found' => true,
                    'type' => 'smart_combo',
                    'duplicate_pengajuan_id' => $smartDuplicate['existing_pengajuan_id'] ?? null,
                    'message' => $smartDuplicate['message'] ?? 'Terindikasi duplikasi vendor+tanggal+nominal.',
                ];
            }

            return [
                'status' => 'pass',
                'duplicate_found' => false,
                'duplicate_pengajuan_id' => null,
                'message' => 'Tidak ada duplikasi terdeteksi',
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function verifyVendor(Pengajuan $pengajuan): array
    {
        if (! config('reimbursement.ai_validation.vendor_verification_enabled')) {
            return ['status' => 'disabled'];
        }

        try {
            $vendorName = trim($pengajuan->nama_vendor);
            $threshold = config('reimbursement.ai_validation.vendor_fuzzy_threshold');

            $similarity = $this->fuzzyMatch($vendorName);

            return [
                'status' => $similarity >= $threshold ? 'pass' : 'warning',
                'similarity' => $similarity,
                'vendor_name' => $vendorName,
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function storeValidationResults(Pengajuan $pengajuan, array $results): void
    {
        // Define allowed types from ENUM in database
        $allowedTypes = ['ocr', 'duplikasi', 'vendor', 'nominal', 'tanggal', 'anomali', 'pajak', 'sekuensial'];

        foreach ($results as $jenis => $result) {
            $dbJenis = $jenis;

            if ($jenis === 'duplicate') {
                $dbJenis = 'duplikasi';
            }

            if (! in_array($dbJenis, $allowedTypes)) {
                \Log::warning("Skipping validation storage for type '$jenis' mapped to '$dbJenis' (not in allowed ENUM)");

                continue;
            }

            // Map status string to Enum if needed
            $rawStatus = $result['status'] ?? 'pending';
            $finalStatus = $rawStatus;

            if (is_string($rawStatus)) {
                $finalStatus = match (strtolower($rawStatus)) {
                    'pass', 'valid' => ValidationStatus::VALID,
                    'fail', 'invalid', 'error' => ValidationStatus::INVALID,
                    'warning' => ValidationStatus::INVALID, // Warning is stored as invalid status (with warning badge)
                    default => ValidationStatus::PENDING
                };
            }

            // Mapping blocking behavior
            $isBlocking = false;
            if ($finalStatus === ValidationStatus::INVALID) {
                // Nominal and Date are always blocking if invalid
                if (in_array($dbJenis, ['nominal', 'tanggal', 'duplikasi'])) {
                    $isBlocking = true;
                }
                // Vendor is ONLY blocking if it's a real fail (<75%), not a warning (75-79.99%)
                if ($dbJenis === 'vendor' && ($result['raw_status'] ?? '') === 'fail') {
                    $isBlocking = true;
                }
            }

            // Use updateOrCreate to prevent duplicates and handle potential unique constraints
            try {
                // Include OCR data for primary validation types to show in UI details
                $storedOcrData = null;
                if ($jenis === 'ocr') {
                    $storedOcrData = $result['data'] ?? $result;
                } elseif (in_array($dbJenis, ['vendor', 'nominal', 'tanggal'])) {
                    $storedOcrData = $results['ocr']['data'] ?? null;
                }

                ValidasiAI::updateOrCreate(
                    [
                        'pengajuan_id' => $pengajuan->pengajuan_id,
                        'jenis_validasi' => $dbJenis,
                    ],
                    [
                        'status' => $finalStatus,
                        'confidence_score' => $result['confidence'] ?? $result['similarity'] ?? 0,
                        'hasil_ocr' => $storedOcrData,
                        'pesan_validasi' => $result['message'] ?? $result['note'] ?? null,
                        'is_blocking' => $isBlocking,
                        'validated_at' => now(),
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Failed to store validation result for '$dbJenis': ".$e->getMessage());
                // Don't throw here to allow other validations to be stored,
                // but in this case, the caller might want to know.
            }
        }
    }

    private function getOverallResult(array $results): array
    {
        // VALIDATION LOGIC (UPDATED):
        // Vendor 75-79.99% = WARNING (can submit with warning badge)
        // Vendor >=80% = PASS (normal submit)
        // Vendor <75% = FAIL (blocked)
        // Nominal MUST be 100% PASS
        // Date MUST be 100% PASS
        // Receipt Age MUST be <= 15 Days (Strict Policy)
        // NO force submit option

        $vendorStatus = $results['vendor']['status'] ?? ValidationStatus::INVALID;
        $nominalStatus = $results['nominal']['status'] ?? ValidationStatus::INVALID;
        $dateStatus = $results['tanggal']['status'] ?? ValidationStatus::INVALID;

        // Convert enum to string value for comparison
        $vendorStatusValue = $vendorStatus instanceof ValidationStatus ? $vendorStatus->value : $vendorStatus;
        $nominalStatusValue = $nominalStatus instanceof ValidationStatus ? $nominalStatus->value : $nominalStatus;
        $dateStatusValue = $dateStatus instanceof ValidationStatus ? $dateStatus->value : $dateStatus;

        $isNominalValid = $nominalStatusValue === ValidationStatus::VALID->value;
        $isDateValid = $dateStatusValue === ValidationStatus::VALID->value;

        // Check Receipt Age Policy (Strict 15 Days)
        $ocrDate = $results['ocr']['data']['tanggal'] ?? null;
        $isAgeValid = true;
        $ageMessage = '';

        if ($ocrDate) {
            try {
                $receiptDate = \Carbon\Carbon::parse($ocrDate);
                $daysOld = $receiptDate->diffInDays(now());
                if ($daysOld > 15) {
                    $isAgeValid = false;
                    $ageMessage = "Nota sudah kedaluwarsa ($daysOld hari). Maksimum adalah 15 hari.";
                }
            } catch (\Exception $e) {
                \Log::warning('ValidasiAIService: Failed to parse OCR date: '.$ocrDate);
                // If we can't parse it, we don't block on age, but it might fail on date match anyway
            }
        }

        // If nominal OR date OR age failed = overall FAIL (cannot submit at all)
        if (! $isNominalValid || ! $isDateValid || ! $isAgeValid) {
            \Log::warning('ValidasiAIService: Validation Failed', [
                'nominal_status' => $nominalStatusValue,
                'date_status' => $dateStatusValue,
                'age_valid' => $isAgeValid,
                'age_message' => $ageMessage,
                'ocr_nominal' => $results['ocr']['data']['nominal'] ?? 'N/A',
                'input_nominal' => $results['nominal']['message'] ?? 'N/A',
                'validation_results' => $results,
            ]);

            return [
                'overall_status' => 'fail',
                'results' => $results,
                'can_submit' => false,
                'message' => $ageMessage ?: 'Validasi nominal atau tanggal gagal.',
            ];
        }

        // Vendor determines overall status (nominal & date already passed)
        // ALLOW 'warning' status (75-79.99%) to pass but with warning overall_status
        $vendorRawStatus = $results['vendor']['raw_status'] ?? ($vendorStatusValue === ValidationStatus::VALID->value ? 'pass' : 'fail');

        if ($vendorRawStatus === 'fail') {
            \Log::warning('ValidasiAIService: Vendor Validation Failed', [
                'vendor_status' => $vendorStatusValue,
                'vendor_raw_status' => $vendorRawStatus,
                'validation_results' => $results,
            ]);

            return [
                'overall_status' => 'fail',
                'results' => $results,
                'can_submit' => false,
            ];
        }

        // Duplicate check
        // Fix: Check for 'duplicate_found' (from checkDuplicate) OR 'is_duplicate' (from checkFileDuplicate)
        $isDuplicate = ($results['duplicate']['is_duplicate'] ?? false) ||
                       ($results['duplicate']['duplicate_found'] ?? false);

        if ($isDuplicate) {
            return [
                'overall_status' => 'fail',
                'results' => $results,
                'can_submit' => false,
                'message' => 'Duplicate detected',
            ];
        }

        // Vendor is pass or warning, nominal & date are pass
        $overallStatus = ($vendorRawStatus === 'pass') ? 'pass' : 'warning';

        return [
            'overall_status' => $overallStatus,
            'results' => $results,
            'can_submit' => true, // Can submit if reached here (vendor not fail, nominal & date pass)
            'message' => ($overallStatus === 'warning') ? '⚠️ Perlu tinjauan manual (Vendor mirip)' : '✓ Validasi AI Berhasil',
        ];
    }

    private function extractVendorName(string $vendor): string
    {
        return trim(strtolower($vendor));
    }

    private function fuzzyMatch(string $vendor): int
    {
        $commonVendors = [
            'gojek' => 95,
            'grab' => 95,
            'indomaret' => 90,
            'alfamart' => 90,
            'tokopedia' => 95,
            'shopee' => 95,
            'hotel' => 80,
            'airline' => 85,
        ];

        foreach ($commonVendors as $known => $baseScore) {
            if (stripos($vendor, $known) !== false) {
                return $baseScore;
            }
        }

        return 70;
    }

    /**
     * Smart Nominal Matching for Multi-Invoice Cases
     *
     * When invoice has multiple items/invoices but user only reimburse one,
     * check if user input nominal matches ANY of the detected totals.
     *
     * Example:
     * - Invoice has: Invoice 1 (Rp139.000), Invoice 2 (Rp9.000), Total 4 Invoice (Rp431.050)
     * - User input: Rp139.000
     * - Result: Match with Invoice 1
     */
    private function checkSmartNominalMatch(float $userNominal, array $allDetectedTotals): array
    {
        if (empty($allDetectedTotals) || $userNominal <= 0) {
            return [
                'is_match' => false,
                'matched_label' => null,
                'matched_amount' => null,
                'reason' => 'No detected totals or invalid user nominal',
            ];
        }

        // Tolerance: ±5 (untuk handling rounding errors OCR)
        $tolerance = 5;

        // Search for exact match or close match in all detected totals
        foreach ($allDetectedTotals as $total) {
            $label = $total['label'] ?? '';
            $amount = (float) ($total['amount'] ?? 0);

            // Skip negative amounts (diskon) dan very large amounts (total semua invoice)
            if ($amount <= 0) {
                continue;
            }
            if (preg_match('/(diskon|potongan|cashback)/i', $label)) {
                continue;
            }

            // Check for total of ALL invoices (low priority)
            // Only match if it's the ONLY option or user specifically input that amount
            if (preg_match('/(total.*\d+.*invoice|semua invoice|grand total)/i', $label)) {
                // Only match if difference is very small (exact)
                if (abs($amount - $userNominal) < 1) {
                    return [
                        'is_match' => true,
                        'matched_label' => $label,
                        'matched_amount' => $amount,
                        'reason' => 'Exact match with all invoices total',
                    ];
                }

                // Otherwise skip this (don't use as primary match)
                continue;
            }

            // Primary match: individual invoice or subtotal
            $difference = abs($amount - $userNominal);

            if ($difference <= $tolerance) {
                return [
                    'is_match' => true,
                    'matched_label' => $label,
                    'matched_amount' => $amount,
                    'difference' => $difference,
                    'reason' => 'Matches individual invoice or subtotal',
                ];
            }
        }

        // No match found
        return [
            'is_match' => false,
            'matched_label' => null,
            'matched_amount' => null,
            'reason' => 'User nominal does not match any detected invoice total',
            'available_totals' => array_map(fn ($t) => [
                'label' => $t['label'] ?? '',
                'amount' => $t['amount'] ?? 0,
            ], $allDetectedTotals),
        ];
    }

    /**
     * Detect Behavioral Anomalies (e.g., unusually high amount for this user/vendor)
     */
    public function detectBehavioralAnomaly($userId, array $inputData, $excludeId = null): array
    {
        $nominal = (float) ($inputData['nominal'] ?? 0);
        $vendor = strtolower(trim($inputData['nama_vendor'] ?? ''));
        $tanggal = $inputData['tanggal_transaksi'] ?? date('Y-m-d');
        $jenisTransaksi = $inputData['jenis_transaksi'] ?? 'other';
        $anomalies = [];

        if ($nominal <= 0) {
            return [
                'is_anomaly' => false,
                'anomalies' => [],
                'reason' => 'Nominal kosong atau tidak valid.',
                'count' => 0,
                'risk_score' => 0,
                'risk_level' => 'low',
            ];
        }

        try {
            // 1. VELOCITY CHECK (Daily & Weekly)
            $dailyCount = Pengajuan::where('user_id', $userId)
                ->whereDate('created_at', now()->toDateString())
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance'])
                ->when($excludeId, fn ($q) => $q->where('pengajuan_id', '!=', $excludeId))
                ->count();

            if ($dailyCount >= 5) {
                $anomalies[] = [
                    'type' => 'velocity_daily',
                    'reason' => "Frekuensi harian tinggi ({$dailyCount} pengajuan hari ini). Potensi split-bill.",
                ];
            }

            $weeklyCount = Pengajuan::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance'])
                ->count();

            if ($weeklyCount >= 20) {
                $anomalies[] = [
                    'type' => 'velocity_weekly',
                    'reason' => "Frekuensi mingguan sangat tinggi ({$weeklyCount} pengajuan dalam 7 hari terakhir).",
                ];
            }

            // 1.1 Weekly Vendor Cap (Potential Fraud on specific merchant)
            $weeklyVendorCount = Pengajuan::where('user_id', $userId)
                ->where('nama_vendor', 'LIKE', "%{$vendor}%")
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance'])
                ->count();

            if ($weeklyVendorCount >= 5) {
                $anomalies[] = [
                    'type' => 'weekly_vendor_cap',
                    'reason' => "Terlalu banyak pengajuan di vendor ini dalam seminggu ({$weeklyVendorCount} kali). Perlu investigasi pola pengeluaran.",
                ];
            }

            // 2. SAME VENDOR SAME DAY CHECK (Potential Split Billing / Logic Duplicate)
            $logicDuplicate = Pengajuan::where('user_id', $userId)
                ->where('nama_vendor', $vendor)
                ->where('tanggal_transaksi', $tanggal)
                ->where('nominal', $nominal)
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance'])
                ->when($excludeId, fn ($q) => $q->where('pengajuan_id', '!=', $excludeId))
                ->first();

            if ($logicDuplicate) {
                $anomalies[] = [
                    'type' => 'logic_duplicate',
                    'reason' => "Ditemukan pengajuan identik (Vendor, Tanggal, Nominal sama) di sistem (#{$logicDuplicate->nomor_pengajuan}).",
                ];
            } else {
                // Check for split bill (same vendor, same day, different nominal)
                $splitBill = Pengajuan::where('user_id', $userId)
                    ->where('nama_vendor', 'LIKE', "%{$vendor}%")
                    ->where('tanggal_transaksi', $tanggal)
                    ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance'])
                    ->when($excludeId, fn ($q) => $q->where('pengajuan_id', '!=', $excludeId))
                    ->first();

                if ($splitBill) {
                    $anomalies[] = [
                        'type' => 'split_bill_detected',
                        'reason' => "Ditemukan pengajuan lain di vendor yang sama pada tanggal ini (#{$splitBill->nomor_pengajuan}). Potensi pemecahan tagihan.",
                    ];
                }
            }

            // 3. WORKING HOURS / ODD HOURS CHECK
            $time = $inputData['waktu_transaksi'] ?? null;
            if ($time) {
                $hour = (int) explode(':', $time)[0];
                $workStartHour = (int) config('reimbursement.policy.workday_start_hour', 8);
                $workEndHour = (int) config('reimbursement.policy.workday_end_hour', 18);

                if ($hour < $workStartHour || $hour >= $workEndHour) {
                    $anomalies[] = [
                        'type' => 'outside_working_hours',
                        'reason' => "Transaksi terjadi di luar jam kerja standar ({$workStartHour}:00-{$workEndHour}:00): {$time}.",
                    ];
                }

                if ($hour >= 23 || $hour <= 5) {
                    if ($jenisTransaksi !== 'transport') {
                        $anomalies[] = [
                            'type' => 'odd_hours_detected',
                            'reason' => "Transaksi non-transport dilakukan di jam tidak wajar ({$time}).",
                        ];
                    } else {
                        // For transport at night, it's allowed but check for high value
                        if ($nominal > 250000) {
                            $anomalies[] = [
                                'type' => 'high_value_night_transport',
                                'reason' => 'Biaya transport larut malam cukup tinggi (Rp '.number_format($nominal).').',
                            ];
                        }
                    }
                }
            }

            // 4. WEEKEND CHECK
            try {
                $dt = \Carbon\Carbon::parse($tanggal);
                if ($dt->isWeekend()) {
                    $isWorkRelatedVendor = false;
                    $workVendors = ['accurate', 'jurnal', 'telkom', 'pln', 'hosting', 'cloud'];
                    foreach ($workVendors as $wv) {
                        if (str_contains($vendor, $wv)) {
                            $isWorkRelatedVendor = true;
                            break;
                        }
                    }

                    if (! $isWorkRelatedVendor) {
                        $anomalies[] = [
                            'type' => 'weekend_transaction',
                            'reason' => "Transaksi dilakukan pada hari libur/akhir pekan ({$dt->format('l')}) di vendor non-operasional.",
                        ];
                    }
                }
            } catch (\Exception $e) {
            }

            // 5. HISTORICAL AVERAGE CHECK
            $cacheKey = "{$userId}_{$vendor}";
            if (isset(self::$anomalyCache[$cacheKey])) {
                $history = self::$anomalyCache[$cacheKey];
            } else {
                $history = Pengajuan::where('user_id', $userId)
                    ->where('nama_vendor', 'LIKE', "%{$vendor}%")
                    ->where('status', PengajuanStatus::DICAIRKAN)
                    ->select(DB::raw('AVG(nominal) as avg_nominal'), DB::raw('MAX(nominal) as max_nominal'))
                    ->first();

                self::$anomalyCache[$cacheKey] = $history;
            }

            if ($history && $history->avg_nominal > 0) {
                $threshold = $history->avg_nominal * 5;

                if ($nominal > $threshold) {
                    $anomalies[] = [
                        'type' => 'spending_outlier',
                        'reason' => 'Nominal (Rp '.number_format($nominal).') jauh melebihi rata-rata kebiasaan Anda di vendor ini (Rp '.number_format($history->avg_nominal).').',
                    ];
                }
            }

            // 6. GLOBAL HIGH VALUE WARNING
            if ($nominal > 10000000) {
                $anomalies[] = [
                    'type' => 'high_value_limit',
                    'reason' => 'Nominal sangat besar (> 10 Juta). Memerlukan verifikasi manual ekstra.',
                ];
            }

            // 7. SUSPICIOUS VENDOR NAME
            $suspiciousKeywords = ['test', 'dummy', 'asdf', 'qwerty', 'coba', 'contoh', 'lorem', 'ipsum', 'fiktif', 'palsu', 'dummy', 'rekayasa', 'anonymous', 'manipulasi', 'tipu', 'kosong', 'tempel', 'editan'];
            foreach ($suspiciousKeywords as $kw) {
                if (str_contains($vendor, $kw)) {
                    $anomalies[] = [
                        'type' => 'suspicious_vendor',
                        'reason' => "Nama vendor mencurigakan ('{$kw}').",
                    ];
                    break;
                }
            }

            if (strlen($vendor) > 0 && strlen($vendor) < 3) {
                $anomalies[] = [
                    'type' => 'suspicious_vendor',
                    'reason' => 'Nama vendor terlalu pendek.',
                ];
            }

            // 8. SEQUENTIAL INVOICE CHECK (Consolidated)
            $invoiceNum = $inputData['invoice_number'] ?? null;
            if ($invoiceNum) {
                $seqResult = $this->checkSequentialInvoice($userId, $vendor, $invoiceNum, $tanggal, $excludeId);
                if ($seqResult['is_sequential']) {
                    $anomalies[] = [
                        'type' => 'sequential_invoice',
                        'reason' => $seqResult['message'],
                    ];
                }
            }

            if (empty($anomalies)) {
                return [
                    'is_anomaly' => false,
                    'anomalies' => [],
                    'reason' => 'Tidak ada anomali perilaku terdeteksi.',
                    'count' => 0,
                    'risk_score' => 0,
                    'risk_level' => 'low',
                ];
            }

            $riskWeights = [
                'logic_duplicate' => 45,
                'split_bill_detected' => 35,
                'sequential_invoice' => 35,
                'suspicious_vendor' => 30,
                'spending_outlier' => 28,
                'weekly_vendor_cap' => 25,
                'high_value_limit' => 25,
                'velocity_daily' => 20,
                'velocity_weekly' => 20,
                'outside_working_hours' => 18,
                'odd_hours_detected' => 15,
                'high_value_night_transport' => 12,
                'weekend_transaction' => 10,
            ];

            $riskScore = 0;
            foreach ($anomalies as $anomaly) {
                $riskScore += $riskWeights[$anomaly['type'] ?? ''] ?? 12;
            }
            $riskScore = (int) max(0, min(100, $riskScore));
            $riskLevel = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');

            return [
                'is_anomaly' => true,
                'anomalies' => $anomalies,
                'reason' => implode(' ', array_column($anomalies, 'reason')),
                'count' => count($anomalies),
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
            ];

        } catch (\Exception $e) {
            \Log::error('Error in detectBehavioralAnomaly: '.$e->getMessage());

            return [
                'is_anomaly' => false,
                'anomalies' => [],
                'reason' => 'Analisis anomali gagal dijalankan.',
                'count' => 0,
                'risk_score' => 0,
                'risk_level' => 'low',
            ];
        }
    }

    /**
     * DETEKSI INVOICE BERURUTAN (ULTRA SMART FRAUD DETECTION)
     * Detects if user submits INV-001 and INV-002 from same vendor in same day
     */
    public function checkSequentialInvoice($userId, $vendor, $currentInvoice, $tanggal, $excludeId = null): array
    {
        if (empty($currentInvoice) || empty($vendor)) {
            return ['is_sequential' => false];
        }

        // Extract numeric part from invoice (e.g. INV-2024-001 -> 2024001)
        // We take the last group of digits
        if (! preg_match_all('/\d+/', $currentInvoice, $matches)) {
            return ['is_sequential' => false];
        }

        $currentNums = $matches[0];
        $currentNum = (int) end($currentNums);

        // Find other invoices from this vendor on this day using indexed columns in pengajuan table first
        $query = \App\Models\ValidasiAI::where('jenis_validasi', 'ocr')
            ->whereHas('pengajuan', function ($q) use ($vendor, $tanggal) {
                $q->where('nama_vendor', 'LIKE', "%{$vendor}%")
                    ->where('tanggal_transaksi', $tanggal)
                    ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance']);
            });

        if ($excludeId) {
            $query->where('pengajuan_id', '!=', $excludeId);
        }

        $others = $query->get();

        foreach ($others as $other) {
            $data = is_string($other->hasil_ocr) ? json_decode($other->hasil_ocr, true) : $other->hasil_ocr;
            $otherInvoice = $data['invoice_number'] ?? null;

            if (! $otherInvoice || $otherInvoice === $currentInvoice) {
                continue;
            }

            if (preg_match_all('/\d+/', $otherInvoice, $otherMatches)) {
                $otherNums = $otherMatches[0];
                $otherNum = (int) end($otherNums);

                // If the difference is exactly 1, and the prefix is the same
                if (abs($currentNum - $otherNum) === 1) {
                    // Check if prefix is same (everything before the last number)
                    $currentPrefix = preg_replace('/\d+$/', '', $currentInvoice);
                    $otherPrefix = preg_replace('/\d+$/', '', $otherInvoice);

                    if ($currentPrefix === $otherPrefix) {
                        return [
                            'is_sequential' => true,
                            'matched_invoice' => $otherInvoice,
                            'message' => "Invoice berurutan terdeteksi ({$otherInvoice} & {$currentInvoice}). Hal ini sering menandakan pemecahan tagihan (bill-splitting) untuk menghindari limit.",
                        ];
                    }
                }
            }
        }

        return ['is_sequential' => false];
    }

    public function verifyTaxConsistency(array $ocrData): array
    {
        $allTotals = $ocrData['all_detected_totals'] ?? [];
        if (count($allTotals) < 2) {
            return [
                'consistent' => true,
                'status' => 'pass',
                'message' => 'Total tunggal terdeteksi (tidak ada struktur pajak untuk diverifikasi)',
            ];
        }

        // Try to find a pair where A + (A * 0.11) = B
        foreach ($allTotals as $item1) {
            $val1 = (float) $item1['amount'];
            foreach ($allTotals as $item2) {
                $val2 = (float) $item2['amount'];
                if ($val1 == $val2) {
                    continue;
                }

                // Check 11% PPN (Indonesia current)
                $expectedWith11 = round($val1 * 1.11);
                if (abs($expectedWith11 - $val2) <= 5) { // Increased tolerance to 5 IDR
                    return [
                        'consistent' => true,
                        'status' => 'pass',
                        'tax_rate' => 0.11,
                        'subtotal' => $val1,
                        'total' => $val2,
                        'message' => 'Struktur pajak valid (PPN 11% terdeteksi)',
                    ];
                }

                // Check 10% PPN (Legacy/Other)
                $expectedWith10 = round($val1 * 1.10);
                if (abs($expectedWith10 - $val2) <= 5) {
                    return [
                        'consistent' => true,
                        'status' => 'pass',
                        'tax_rate' => 0.10,
                        'subtotal' => $val1,
                        'total' => $val2,
                        'message' => 'Struktur pajak valid (PPN 10% terdeteksi)',
                    ];
                }
            }
        }

        return [
            'consistent' => false,
            'status' => 'warning',
            'message' => 'Struktur nominal & pajak tidak standar',
        ];
    }
}

