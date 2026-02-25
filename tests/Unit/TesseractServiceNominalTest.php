<?php

use App\Services\GroqAIService;
use App\Services\TesseractService;

class TestableTesseractService extends TesseractService
{
    public function publicSelectFinalNominal(array $groqData, string $rawText): float
    {
        return $this->selectFinalNominal($groqData, $rawText);
    }

    public function publicNormalizeVendorForDisplay(?string $vendor): string
    {
        return $this->normalizeVendorForDisplay($vendor);
    }
}

beforeEach(function () {
    $this->service = new TestableTesseractService(\Mockery::mock(GroqAIService::class));
});

afterEach(function () {
    \Mockery::close();
});

it('selects total transaksi instead of admin or nominal lines', function () {
    $groqData = [
        'nominal' => 274998,
        'all_detected_totals' => [
            ['label' => 'Nominal', 'amount' => 274998, 'priority' => 2],
            ['label' => 'Biaya Admin', 'amount' => 1000, 'priority' => 3],
            ['label' => 'Total Transaksi', 'amount' => 275998, 'priority' => 1],
        ],
    ];

    $rawText = implode("\n", [
        'Nominal Rp274.998',
        'Biaya Admin Rp1.000',
        'Total Transaksi Rp275.998',
    ]);

    $result = $this->service->publicSelectFinalNominal($groqData, $rawText);

    expect($result)->toBe(275998.0);
});

it('normalizes vendor for display by trimming trailing long ids', function () {
    $result = $this->service->publicNormalizeVendorForDisplay('TRAVELOKA 1329194623');

    expect($result)->toBe('TRAVELOKA');
});
