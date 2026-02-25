@props(['results', 'pengajuan' => null])

<style>
    .ai-review-wrap {
        display: grid;
        gap: 0.55rem;
    }
    .ai-panel {
        border: 1px solid #dbe6f4;
        border-radius: 12px;
        background: #ffffff;
        overflow: hidden;
        padding: 0;
        margin: 0;
        min-height: 0;
        height: auto;
    }
    .ai-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.62rem 0.78rem;
        background: linear-gradient(125deg, #f8fbff 0%, #eef4ff 100%);
        border-bottom: 1px solid #dbe6f4;
    }
    .ai-panel-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
    }
    .ai-panel-subtitle {
        margin: 0.08rem 0 0 0;
        font-size: 0.84rem;
        color: #64748b;
    }
    .ai-chip {
        border-radius: 999px;
        padding: 0.22rem 0.62rem;
        font-size: 0.74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .ai-chip-primary { background: #e0e7ff; color: #3730a3; }
    .ai-chip-success { background: #dcfce7; color: #166534; }
    .ai-chip-warning { background: #fef3c7; color: #92400e; }
    .ai-chip-danger { background: #fee2e2; color: #991b1b; }

    .ai-kpi-grid {
        padding: 0.6rem 0.75rem;
        display: grid;
        gap: 0.42rem;
        grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
    }
    .ai-kpi {
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fff;
        padding: 0.45rem 0.55rem;
    }
    .ai-kpi-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.04em;
    }
    .ai-kpi-value {
        margin-top: 0.13rem;
        font-size: 1.16rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    .ai-kpi-note {
        margin-top: 0.06rem;
        font-size: 0.82rem;
        color: #64748b;
    }

    .ai-summary-grid {
        padding: 0.58rem 0.75rem;
        display: grid;
        gap: 0.45rem;
        grid-template-columns: 1.2fr 1fr;
    }
    .ai-summary-box {
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fff;
        padding: 0.52rem;
        min-height: 100%;
    }
    .ai-summary-title {
        margin: 0 0 0.28rem 0;
        font-size: 0.76rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ai-fact-grid {
        display: grid;
        gap: 0.3rem;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    }
    .ai-fact {
        border: 1px dashed #dbe6f4;
        border-radius: 8px;
        padding: 0.34rem;
        background: #f8fbff;
    }
    .ai-fact-label {
        font-size: 0.68rem;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 700;
    }
    .ai-fact-value {
        margin-top: 0.09rem;
        font-size: 0.9rem;
        color: #1e293b;
        font-weight: 700;
        word-break: break-word;
    }

    .ai-mini-checks {
        margin-top: 0.35rem;
        display: grid;
        gap: 0.25rem;
        grid-template-columns: repeat(auto-fit, minmax(125px, 1fr));
    }
    .ai-mini-check {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.28rem 0.38rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        color: #334155;
        background: #fff;
    }
    .ai-mini-check strong {
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        padding: 0.08rem 0.28rem;
    }

    .ai-check-grid {
        padding: 0.58rem 0.75rem;
        display: grid;
        gap: 0.34rem;
        grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));
    }
    .ai-check-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 0.42rem;
    }
    .ai-check-head {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.2rem;
    }
    .ai-check-label {
        font-size: 0.86rem;
        font-weight: 800;
        color: #1e293b;
    }
    .ai-check-severity {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
    }
    .ai-check-body {
        margin-top: 0.2rem;
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.4;
    }

    .ai-list-box {
        margin: 0.38rem 0.75rem 0.48rem 0.75rem;
        padding: 0.46rem 0.52rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .ai-list-title {
        margin: 0 0 0.22rem 0;
        font-size: 0.76rem;
        font-weight: 800;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ai-list-box ul {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.1rem;
        font-size: 0.82rem;
    }
    .ai-list-red ul { color: #9f1239; }
    .ai-list-amber ul { color: #92400e; }
    .ai-list-blue ul { color: #334155; }
    .ai-list-policy {
        border-color: #fecdd3;
        background: #fff1f2;
    }
    .ai-list-policy .ai-list-title {
        color: #be123c;
    }
    .ai-list-policy ul {
        color: #9f1239;
    }

    .ai-items-list {
        display: grid;
        gap: 0.08rem;
    }
    .ai-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px dashed #eef2f7;
        padding-bottom: 0.13rem;
    }
    .ai-item-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .ai-item-price {
        color: #64748b;
        font-weight: 700;
        white-space: nowrap;
    }
    .ai-meter-grid {
        padding: 0 0.75rem 0.72rem 0.75rem;
        display: grid;
        gap: 0.34rem;
        grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));
    }
    .ai-meter-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 0.4rem 0.45rem;
    }
    .ai-meter-title {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ai-meter-row {
        margin-top: 0.2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.35rem;
    }
    .ai-meter-value {
        font-size: 1rem;
        color: #1e293b;
        font-weight: 800;
    }
    .ai-meter-track {
        margin-top: 0.25rem;
        width: 100%;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        position: relative;
    }
    .ai-meter-fill {
        height: 100%;
        border-radius: 999px;
    }
    .ai-meter-note {
        margin-top: 0.18rem;
        font-size: 0.8rem;
        color: #64748b;
    }
    @media (max-width: 920px) {
        .ai-summary-grid {
            grid-template-columns: 1fr;
        }
    }
    @media print {
        .ai-review-wrap {
            gap: 0.45rem;
        }
        .ai-panel {
            border-color: #cbd5e1 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }
        .ai-panel-head {
            background: #f8fafc !important;
        }
    }
    .ai-collapse {
        margin: 0.35rem 0.75rem 0.45rem 0.75rem;
        border: 1px solid #dbe6f4;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }
    .ai-collapse summary {
        list-style: none;
        cursor: pointer;
        padding: 0.45rem 0.55rem;
        font-size: 0.78rem;
        color: #334155;
        font-weight: 800;
        text-transform: uppercase;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
    }
    .ai-collapse summary::-webkit-details-marker {
        display: none;
    }
    .ai-collapse .ai-collapse-body {
        padding: 0.45rem 0.55rem;
    }
</style>

<div class="ai-review-wrap">
@if(!$results->isEmpty())
    @php
        $ocrValidasi = $results->where('jenis_validasi', 'ocr')->first();
        $ocrData = null;
        if ($ocrValidasi) {
            $ocrData = is_string($ocrValidasi->hasil_ocr) ? json_decode($ocrValidasi->hasil_ocr, true) : $ocrValidasi->hasil_ocr;
        }
    @endphp

    @if($ocrData)
        @php
            $resultByType = $results->keyBy('jenis_validasi');

            $buildState = function (string $code, string $severity = 'low', bool $blocking = false): array {
                return match ($code) {
                    'pass' => [
                        'code' => 'pass',
                        'label' => 'PASS',
                        'bg' => '#dcfce7',
                        'color' => '#166534',
                        'severity' => strtoupper($severity),
                        'blocking' => false,
                    ],
                    'warning' => [
                        'code' => 'warning',
                        'label' => 'WARNING',
                        'bg' => '#fef3c7',
                        'color' => '#92400e',
                        'severity' => strtoupper($severity),
                        'blocking' => false,
                    ],
                    default => [
                        'code' => 'fail',
                        'label' => 'FAIL',
                        'bg' => '#fee2e2',
                        'color' => '#991b1b',
                        'severity' => strtoupper($severity),
                        'blocking' => $blocking,
                    ],
                };
            };

            $stateFromRecord = function ($record, bool $strictBlocking = false, string $severityWhenFail = 'high') use ($buildState): array {
                if (! $record) {
                    return $buildState('warning', 'medium', false);
                }

                $statusValue = is_object($record->status) && isset($record->status->value)
                    ? (string) $record->status->value
                    : (string) $record->status;

                if ($statusValue === 'valid') {
                    return $buildState('pass');
                }

                $isBlocking = $strictBlocking ? true : (bool) ($record->is_blocking ?? false);
                return $isBlocking
                    ? $buildState('fail', $severityWhenFail, true)
                    : $buildState('warning', 'medium', false);
            };

            $nominalRecord = $resultByType->get('nominal');
            $vendorRecord = $resultByType->get('vendor');
            $tanggalRecord = $resultByType->get('tanggal');
            $duplikasiRecord = $resultByType->get('duplikasi') ?? $resultByType->get('duplicate');
            $anomaliRecord = $resultByType->get('anomali');

            $nominalState = $stateFromRecord($nominalRecord, true, 'high');
            $vendorState = $stateFromRecord($vendorRecord, false, 'high');
            $tanggalState = $stateFromRecord($tanggalRecord, true, 'high');
            $duplikasiState = $stateFromRecord($duplikasiRecord, true, 'high');
            $anomaliState = $stateFromRecord($anomaliRecord, false, 'medium');
            $duplicateAlreadyValidated = ($duplikasiState['code'] ?? '') === 'pass';

            $llmAnomaly = is_array($ocrData['llm_anomaly_analysis'] ?? null) ? $ocrData['llm_anomaly_analysis'] : [];
            $riskScore = (int) ($llmAnomaly['risk_score'] ?? ($ocrData['fraud_risk_score'] ?? 0));
            $riskScore = max(0, min(100, $riskScore));

            $riskLevel = strtolower((string) ($llmAnomaly['risk_level'] ?? ''));
            if (! in_array($riskLevel, ['low', 'medium', 'high'], true)) {
                $riskLevel = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');
            }

            $llmRecommendation = strtolower((string) ($llmAnomaly['approval_recommendation'] ?? ''));
            if (! in_array($llmRecommendation, ['approve', 'review', 'reject'], true)) {
                $llmRecommendation = $riskScore >= 75 ? 'reject' : ($riskScore >= 45 ? 'review' : 'approve');
            }

            $normalizeAiText = function (?string $text): string {
                $value = trim((string) $text);
                if ($value === '') {
                    return '';
                }

                $search = [
                    'heuristik',
                    'Heuristik',
                    'indikator heuristik',
                    'warning heuristik',
                    'histori database',
                ];
                $replace = [
                    'sistem',
                    'Sistem',
                    'indikator sistem',
                    'indikator peringatan',
                    'riwayat pengajuan',
                ];

                return str_replace($search, $replace, $value);
            };

            $llmSummary = $normalizeAiText((string) ($llmAnomaly['summary'] ?? ($ocrData['sanity_check_notes'] ?? '')));
            if ($llmSummary === '') {
                $llmSummary = 'Analisis AI tersedia untuk review manual.';
            }

            $rawText = (string) ($ocrData['raw_text'] ?? '');
            $transactionTime = null;
            if (preg_match('/(?:^|\\s)([01]?\\d|2[0-3])[:.]([0-5]\\d)(?:\\s*(wib|wita|wit))?/i', $rawText, $timeMatch)) {
                $transactionTime = str_pad((string) $timeMatch[1], 2, '0', STR_PAD_LEFT).':'.str_pad((string) $timeMatch[2], 2, '0', STR_PAD_LEFT);
            }
            $outsideWorkingHours = false;
            if ($transactionTime !== null) {
                $hour = (int) substr($transactionTime, 0, 2);
                $outsideWorkingHours = $hour < 8 || $hour >= 18;
            }
            $timeState = $transactionTime === null
                ? $buildState('warning', 'low', false)
                : ($outsideWorkingHours ? $buildState('warning', 'medium', false) : $buildState('pass', 'low', false));

            $blockingFailCount = collect([$nominalState, $vendorState, $tanggalState, $duplikasiState])
                ->filter(fn ($state) => ($state['code'] ?? '') === 'fail' && ($state['blocking'] ?? false))
                ->count();
            $warningCount = collect([$vendorState, $anomaliState, $timeState])
                ->filter(fn ($state) => ($state['code'] ?? '') === 'warning')
                ->count();

            $manualRecommendation = 'Review Manual Standar';
            if ($blockingFailCount > 0) {
                $manualRecommendation = 'Perlu Perbaikan Data (Belum Layak Approve)';
            } elseif ($llmRecommendation === 'reject' || $riskScore >= 70 || $outsideWorkingHours || ($anomaliState['code'] ?? '') !== 'pass') {
                $manualRecommendation = 'Review Manual Ketat (Fraud/Anomali)';
            } elseif ($llmRecommendation === 'review' || $warningCount > 0) {
                $manualRecommendation = 'Review Manual Terarah';
            }

            $decisionScore = max(5, min(
                100,
                100 - ($blockingFailCount * 35) - ($warningCount * 12) - (int) round($riskScore * 0.35) - ($outsideWorkingHours ? 8 : 0)
            ));

            $manualTone = str_contains(strtolower($manualRecommendation), 'ketat')
                || str_contains(strtolower($manualRecommendation), 'perbaikan')
                ? 'danger'
                : (str_contains(strtolower($manualRecommendation), 'terarah') ? 'warning' : 'success');

            $checklist = [
                [
                    'label' => 'Konsistensi nominal invoice',
                    'state' => $nominalState,
                    'detail' => $normalizeAiText((string) ($nominalRecord->pesan_validasi ?? 'Nominal belum tervalidasi.')),
                ],
                [
                    'label' => 'Kecocokan vendor/merchant',
                    'state' => $vendorState,
                    'detail' => $normalizeAiText((string) ($vendorRecord->pesan_validasi ?? 'Vendor belum tervalidasi.')),
                ],
                [
                    'label' => 'Kecocokan tanggal transaksi',
                    'state' => $tanggalState,
                    'detail' => $normalizeAiText((string) ($tanggalRecord->pesan_validasi ?? 'Tanggal belum tervalidasi.')),
                ],
                [
                    'label' => 'Deteksi duplikasi vendor+tanggal+nominal',
                    'state' => $duplikasiState,
                    'detail' => $normalizeAiText((string) ($duplikasiRecord->pesan_validasi ?? 'Tidak ada data duplikasi.')),
                ],
                [
                    'label' => 'Anomali perilaku user',
                    'state' => $anomaliState,
                    'detail' => $normalizeAiText((string) ($anomaliRecord->pesan_validasi ?? 'Tidak ada anomali perilaku signifikan.')),
                ],
                [
                    'label' => 'Waktu transaksi',
                    'state' => $timeState,
                    'detail' => $transactionTime === null
                        ? 'Waktu transaksi tidak terdeteksi dari OCR.'
                        : ($outsideWorkingHours
                            ? "Transaksi terdeteksi pukul {$transactionTime} (di luar jam kerja standar)."
                            : "Transaksi terdeteksi pukul {$transactionTime} (dalam jam kerja standar)."),
                ],
            ];

            $mandatoryComparisons = [
                ['label' => 'Nominal', 'state' => $nominalState],
                ['label' => 'Vendor', 'state' => $vendorState],
                ['label' => 'Tanggal', 'state' => $tanggalState],
                ['label' => 'Duplikasi', 'state' => $duplikasiState],
            ];
            $mandatoryCount = max(1, count($mandatoryComparisons));
            $mandatoryPassCount = collect($mandatoryComparisons)->filter(fn ($item) => ($item['state']['code'] ?? '') === 'pass')->count();
            $mandatoryMatchPercent = (int) round(($mandatoryPassCount / $mandatoryCount) * 100);

            $riskBarColor = $riskScore >= 70 ? '#ef4444' : ($riskScore >= 40 ? '#f59e0b' : '#22c55e');
            $decisionBarColor = $decisionScore < 45 ? '#ef4444' : ($decisionScore < 75 ? '#f59e0b' : '#22c55e');
            $matchBarColor = $mandatoryMatchPercent >= 100 ? '#22c55e' : ($mandatoryMatchPercent >= 75 ? '#f59e0b' : '#ef4444');

            $policyViolations = is_array($ocrData['policy_violations'] ?? null) ? $ocrData['policy_violations'] : [];
            $ocrItems = is_array($ocrData['items'] ?? null) ? $ocrData['items'] : [];
            $displayItems = array_slice($ocrItems, 0, 6);
            $detailTransaksi = trim((string) ($ocrData['detail_transaksi'] ?? ''));
            $hasItemEvidence = count($displayItems) > 0 || $detailTransaksi !== '';
            $confidence = (int) ($ocrData['confidence_score'] ?? 0);
            $category = (string) ($ocrData['suggested_category'] ?? 'Umum');

            $llmAnomalyChecks = is_array($llmAnomaly['anomaly_checks'] ?? null) ? $llmAnomaly['anomaly_checks'] : [];
            $llmAnomalyChecks = array_values(array_filter($llmAnomalyChecks, function ($item) {
                if (! is_array($item)) {
                    return false;
                }

                $code = strtolower((string) ($item['code'] ?? ''));
                return $code !== 'duplicate_reference_signal';
            }));

            $llmAnomalyChecks = array_map(function ($item) use ($normalizeAiText) {
                $code = strtolower((string) ($item['code'] ?? ''));
                if ($code === 'split_bill_signal') {
                    $item['label'] = 'Indikasi pemecahan tagihan';
                }

                $item['label'] = $normalizeAiText((string) ($item['label'] ?? 'Temuan anomali'));
                $item['reason'] = $normalizeAiText((string) ($item['reason'] ?? ''));
                $item['evidence'] = $normalizeAiText((string) ($item['evidence'] ?? ''));

                return $item;
            }, $llmAnomalyChecks);

            $llmReviewReasons = array_values(array_filter(array_map(
                fn ($item) => $normalizeAiText((string) $item),
                is_array($llmAnomaly['review_reasons'] ?? null) ? $llmAnomaly['review_reasons'] : []
            )));
            if ($duplicateAlreadyValidated) {
                $llmReviewReasons = array_values(array_filter($llmReviewReasons, function ($reason) {
                    return ! preg_match('/duplikasi|vendor\+tanggal\+nominal|riwayat pengajuan/i', (string) $reason);
                }));
            }
            $llmRedFlags = array_values(array_filter(array_map(
                fn ($item) => $normalizeAiText((string) $item),
                is_array($llmAnomaly['red_flags'] ?? null) ? $llmAnomaly['red_flags'] : []
            )));
            $llmManipulationSignals = array_values(array_filter(array_map(
                fn ($item) => $normalizeAiText((string) $item),
                is_array($llmAnomaly['manipulation_signals'] ?? null) ? $llmAnomaly['manipulation_signals'] : []
            )));
        @endphp

        <section class="ai-panel">
            <div class="ai-panel-head">
                <div style="display:flex; align-items:center; gap:0.6rem;">
                    <x-icon name="cpu" class="w-5 h-5" style="color:#4338ca;" />
                    <div>
                        <h3 class="ai-panel-title">Smart Audit AI Automation</h3>
                        <p class="ai-panel-subtitle">Ringkasan siap-review untuk atasan/finance. Keputusan akhir tetap manual.</p>
                    </div>
                </div>
                <span class="ai-chip ai-chip-{{ $manualTone }}">{{ $manualRecommendation }}</span>
            </div>

            <div class="ai-kpi-grid">
                <div class="ai-kpi">
                    <div class="ai-kpi-label">Skor Review</div>
                    <div class="ai-kpi-value">{{ $decisionScore }}%</div>
                    <div class="ai-kpi-note">Semakin tinggi, semakin minim risiko.</div>
                </div>
                <div class="ai-kpi">
                    <div class="ai-kpi-label">Fraud Risk LLM</div>
                    <div class="ai-kpi-value">{{ $riskScore }}/100</div>
                    <div class="ai-kpi-note">Level: {{ strtoupper($riskLevel) }} | {{ strtoupper($llmRecommendation) }}</div>
                </div>
                <div class="ai-kpi">
                    <div class="ai-kpi-label">Pencocokan Wajib</div>
                    <div class="ai-kpi-value">{{ $mandatoryPassCount }}/4</div>
                    <div class="ai-kpi-note">Nominal, vendor, tanggal, duplikasi.</div>
                </div>
                <div class="ai-kpi">
                    <div class="ai-kpi-label">Confidence OCR</div>
                    <div class="ai-kpi-value">{{ $confidence }}%</div>
                    <div class="ai-kpi-note">Kategori: {{ $category !== '' ? $category : 'Umum' }}</div>
                </div>
            </div>

            <div class="ai-meter-grid">
                <div class="ai-meter-card">
                    <div class="ai-meter-title">Fraud Risk Meter</div>
                    <div class="ai-meter-row">
                        <span class="ai-meter-value">{{ $riskScore }}/100</span>
                        <span class="ai-chip {{ $riskScore >= 70 ? 'ai-chip-danger' : ($riskScore >= 40 ? 'ai-chip-warning' : 'ai-chip-success') }}">{{ strtoupper($riskLevel) }}</span>
                    </div>
                    <div class="ai-meter-track">
                        <div class="ai-meter-fill" style="width: {{ $riskScore }}%; background: {{ $riskBarColor }};"></div>
                    </div>
                    <div class="ai-meter-note">Semakin tinggi berarti risiko fraud/anomali makin tinggi.</div>
                </div>
                <div class="ai-meter-card">
                    <div class="ai-meter-title">Readiness Decision Meter</div>
                    <div class="ai-meter-row">
                        <span class="ai-meter-value">{{ $decisionScore }}%</span>
                        <span class="ai-chip {{ $decisionScore < 45 ? 'ai-chip-danger' : ($decisionScore < 75 ? 'ai-chip-warning' : 'ai-chip-success') }}">READINESS</span>
                    </div>
                    <div class="ai-meter-track">
                        <div class="ai-meter-fill" style="width: {{ $decisionScore }}%; background: {{ $decisionBarColor }};"></div>
                    </div>
                    <div class="ai-meter-note">Skor kesiapan dokumen untuk lanjut approval manual.</div>
                </div>
                <div class="ai-meter-card">
                    <div class="ai-meter-title">Match Rule Meter</div>
                    <div class="ai-meter-row">
                        <span class="ai-meter-value">{{ $mandatoryMatchPercent }}%</span>
                        <span class="ai-chip {{ $mandatoryMatchPercent >= 100 ? 'ai-chip-success' : ($mandatoryMatchPercent >= 75 ? 'ai-chip-warning' : 'ai-chip-danger') }}">{{ $mandatoryPassCount }}/{{ $mandatoryCount }}</span>
                    </div>
                    <div class="ai-meter-track">
                        <div class="ai-meter-fill" style="width: {{ $mandatoryMatchPercent }}%; background: {{ $matchBarColor }};"></div>
                    </div>
                    <div class="ai-meter-note">Kecocokan rule wajib: nominal, vendor, tanggal, duplikasi.</div>
                </div>
            </div>
        </section>

        <section class="ai-panel">
            <div class="ai-panel-head">
                <h4 class="ai-panel-title">Ringkasan Otomatis Untuk Reviewer</h4>
                <span class="ai-chip ai-chip-primary">AI Evidence Snapshot</span>
            </div>

            <div class="ai-summary-grid">
                <div class="ai-summary-box">
                    <h5 class="ai-summary-title">Fakta Utama Dokumen</h5>
                    <div class="ai-fact-grid">
                        <div class="ai-fact">
                            <div class="ai-fact-label">Vendor OCR</div>
                            <div class="ai-fact-value">{{ $ocrData['vendor'] ?? '-' }}</div>
                        </div>
                        <div class="ai-fact">
                            <div class="ai-fact-label">Nominal OCR</div>
                            <div class="ai-fact-value">Rp {{ number_format((float) ($ocrData['nominal'] ?? 0), 0, ',', '.') }}</div>
                        </div>
                        <div class="ai-fact">
                            <div class="ai-fact-label">Tanggal OCR</div>
                            <div class="ai-fact-value">{{ $ocrData['tanggal'] ?? '-' }}</div>
                        </div>
                        <div class="ai-fact">
                            <div class="ai-fact-label">Waktu OCR</div>
                            <div class="ai-fact-value">{{ $transactionTime ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="ai-mini-checks">
                        @foreach($mandatoryComparisons as $cmp)
                            @php
                                $code = $cmp['state']['code'] ?? 'warning';
                                $chipClass = $code === 'pass' ? 'ai-chip-success' : ($code === 'fail' ? 'ai-chip-danger' : 'ai-chip-warning');
                            @endphp
                            <div class="ai-mini-check">
                                <span>{{ $cmp['label'] }}</span>
                                <strong class="{{ $chipClass }}">{{ strtoupper($code) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="ai-summary-box">
                    <h5 class="ai-summary-title">Kesimpulan AI</h5>
                    <div style="font-size:0.92rem; color:#334155; line-height:1.45;">{{ $llmSummary }}</div>
                    <div style="margin-top:0.5rem; font-size:0.84rem; color:#64748b; line-height:1.4;">
                        {{ $normalizeAiText((string) ($llmAnomaly['decision_reason'] ?? 'Keputusan akhir tetap di reviewer.')) }}
                    </div>
                    <div style="margin-top:0.62rem; font-size:0.82rem; color:#475569;">
                        Fokus review:
                        <strong>{{ $blockingFailCount }} issue kritis</strong>,
                        <strong>{{ $warningCount }} warning</strong>.
                    </div>
                </div>
            </div>
        </section>

        <section class="ai-panel">
            <div class="ai-panel-head">
                <h4 class="ai-panel-title">Checklist Validasi AI</h4>
                <span class="ai-chip ai-chip-primary">No Auto-Approve</span>
            </div>

            <div class="ai-check-grid">
                @foreach($checklist as $item)
                    @php $state = $item['state']; @endphp
                    <div class="ai-check-item">
                        <div class="ai-check-head">
                            <span class="ai-chip" style="background:{{ $state['bg'] }}; color:{{ $state['color'] }};">{{ $state['label'] }}</span>
                            <span class="ai-check-label">{{ $item['label'] }}</span>
                            <span class="ai-check-severity">Severity: {{ $state['severity'] }}</span>
                        </div>
                        <div class="ai-check-body">{{ $item['detail'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        @if(!empty($llmAnomalyChecks))
            <section class="ai-panel">
                <div class="ai-panel-head">
                    <h4 class="ai-panel-title">Temuan Fraud & Anomali LLM</h4>
                    <span class="ai-chip ai-chip-primary">Smart Pattern Detection</span>
                </div>
                <div class="ai-check-grid">
                    @foreach($llmAnomalyChecks as $llmCheck)
                        @php
                            $checkStatus = strtolower((string) ($llmCheck['status'] ?? 'warning'));
                            $checkSeverity = strtoupper((string) ($llmCheck['severity'] ?? 'medium'));
                            if ($checkStatus === 'pass') {
                                $checkBg = '#dcfce7'; $checkColor = '#166534'; $checkLabel = 'PASS';
                            } elseif ($checkStatus === 'fail') {
                                $checkBg = '#fee2e2'; $checkColor = '#991b1b'; $checkLabel = 'FAIL';
                            } else {
                                $checkBg = '#fef3c7'; $checkColor = '#92400e'; $checkLabel = 'WARNING';
                            }
                        @endphp
                        <div class="ai-check-item">
                            <div class="ai-check-head">
                                <span class="ai-chip" style="background:{{ $checkBg }}; color:{{ $checkColor }};">{{ $checkLabel }}</span>
                                <span class="ai-check-label">{{ $llmCheck['label'] ?? 'Temuan anomali' }}</span>
                                <span class="ai-check-severity">Severity: {{ $checkSeverity }}</span>
                            </div>
                            <div class="ai-check-body">
                                {{ $llmCheck['reason'] ?? '-' }}
                                @if(!empty($llmCheck['evidence']))
                                    <div style="margin-top:0.22rem; color:#64748b;"><strong>Bukti:</strong> {{ $llmCheck['evidence'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if(!empty($llmRedFlags) || !empty($llmManipulationSignals) || !empty($llmReviewReasons) || !empty($policyViolations))
            <details class="ai-collapse">
                <summary>
                    <span>Detail Temuan Lanjutan</span>
                    <span class="ai-chip ai-chip-primary">Opsional</span>
                </summary>
                <div class="ai-collapse-body">
                    @if(!empty($llmRedFlags))
                        <div class="ai-list-box ai-list-red" style="margin:0 0 0.35rem 0;">
                            <h5 class="ai-list-title">Red Flags</h5>
                            <ul>
                                @foreach($llmRedFlags as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($llmManipulationSignals))
                        <div class="ai-list-box ai-list-amber" style="margin:0 0 0.35rem 0;">
                            <h5 class="ai-list-title">Sinyal Manipulasi OCR / Dokumen</h5>
                            <ul>
                                @foreach($llmManipulationSignals as $signal)
                                    <li>{{ $signal }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($llmReviewReasons))
                        <div class="ai-list-box ai-list-blue" style="margin:0 0 0.35rem 0;">
                            <h5 class="ai-list-title">Poin Review Manual</h5>
                            <ul>
                                @foreach($llmReviewReasons as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($policyViolations))
                        <div class="ai-list-box ai-list-policy" style="margin:0;">
                            <h5 class="ai-list-title">Policy Violations</h5>
                            <ul>
                                @foreach($policyViolations as $violation)
                                    <li>{{ is_array($violation) ? ($violation['item'] ?? $violation['reason'] ?? 'Violation') : $violation }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </details>
        @endif

        @if($hasItemEvidence)
            <details class="ai-collapse">
                <summary>
                    <span>Detail Item OCR</span>
                    <span class="ai-chip ai-chip-primary">Auto-Extracted</span>
                </summary>
                <div class="ai-collapse-body">
                    @if(count($displayItems) > 0)
                        <div class="ai-items-list">
                            @foreach($displayItems as $item)
                                @php
                                    $name = is_array($item) ? (string) ($item['name'] ?? '-') : (string) $item;
                                    $qty = is_array($item) ? (int) ($item['qty'] ?? 1) : 1;
                                    $price = is_array($item) ? (float) ($item['price'] ?? 0) : 0;
                                @endphp
                                <div class="ai-item-row">
                                    <span>{{ $qty }}x {{ $name }}</span>
                                    <span class="ai-item-price">{{ $price > 0 ? 'Rp '.number_format($price, 0, ',', '.') : '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size:0.82rem; color:#475569; line-height:1.4;">{{ $detailTransaksi }}</div>
                    @endif
                </div>
            </details>
        @endif
    @else
        <div class="ai-panel" style="padding:1rem;">
            <p style="margin:0; font-size:0.92rem; color:#64748b;">Data OCR utama belum tersedia, silakan cek kembali proses ekstraksi dokumen.</p>
        </div>
    @endif
@else
    <div class="ai-panel" style="padding:1.4rem; text-align:center;">
        <p style="margin:0; font-size:0.92rem; color:#94a3b8;">Sistem sedang memproses validasi AI...</p>
    </div>
@endif
</div>
