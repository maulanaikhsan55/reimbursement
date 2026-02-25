<?php

use App\Services\TesseractService;
use App\Services\ValidasiAIService;

beforeEach(function () {
    $this->service = new ValidasiAIService(\Mockery::mock(TesseractService::class));
});

afterEach(function () {
    \Mockery::close();
});

it('prioritizes total transaction instead of admin fee from detected totals', function () {
    $result = $this->service->matchNominal(
        1000,
        275998,
        null,
        [
            ['label' => 'Biaya Admin', 'amount' => 1000],
            ['label' => 'Total Transaksi', 'amount' => 275998],
        ]
    );

    expect($result['status'])->toBe('pass');
});

it('keeps nominal strict when ocr nominal already exists', function () {
    $result = $this->service->matchNominal(
        1000,
        274998,
        'Nominal Rp274.998 dan Biaya Admin Rp1.000'
    );

    expect($result['status'])->toBe('fail');
});

it('matches vendor with trailing numeric id and does not fail', function () {
    $result = $this->service->matchVendor(
        'TRAVELOKA 1329194623',
        'traveloka',
        'Tujuan TRAVELOKA 1329194623'
    );

    expect($result['status'])->not->toBe('fail')
        ->and($result['match_percentage'])->toBeGreaterThanOrEqual(75);
});

it('keeps llm reject as warning and still allows submit for manual review', function () {
    $today = now()->toDateString();

    $ocrData = [
        'vendor' => 'TRAVELOKA',
        'nominal' => 274998,
        'tanggal' => $today,
        'raw_text' => 'Transaksi Berhasil 11 Februari 2026, 15:10 WIB',
        'llm_anomaly_analysis' => [
            'risk_score' => 82,
            'risk_level' => 'high',
            'approval_recommendation' => 'reject',
            'summary' => 'Banyak red flag pada dokumen.',
            'decision_reason' => 'Butuh review manual.',
        ],
    ];

    $inputData = [
        'nama_vendor' => 'traveloka',
        'nominal' => 274998,
        'tanggal_transaksi' => $today,
        'jenis_transaksi' => 'other',
    ];

    $result = $this->service->validateManualInput($ocrData, $inputData, null);

    expect($result['can_submit'])->toBeTrue()
        ->and(collect($result['issues'])->contains(fn (array $issue) => $issue['type'] === 'warning' && $issue['title'] === 'Rekomendasi AI: Risiko Tinggi'))->toBeTrue();
});

it('keeps duplicate check non-blocking when db is unavailable', function () {
    $today = now()->toDateString();

    $ocrData = [
        'vendor' => 'TRAVELOKA 1329194623',
        'nominal' => 274998,
        'tanggal' => $today,
        'raw_text' => 'Tujuan TRAVELOKA 1329194623',
        'llm_anomaly_analysis' => [
            'risk_score' => 20,
            'risk_level' => 'low',
            'approval_recommendation' => 'approve',
            'summary' => 'Aman',
            'decision_reason' => 'Tidak ada red flag mayor.',
        ],
    ];

    $inputData = [
        'nama_vendor' => 'traveloka',
        'nominal' => 274998,
        'tanggal_transaksi' => $today,
        'jenis_transaksi' => 'other',
    ];

    $result = $this->service->validateManualInput($ocrData, $inputData, '99999');

    expect($result['matches']['duplicate']['is_duplicate'])->toBeFalse();
});
