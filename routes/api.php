<?php

use App\Http\Controllers\Api\OCRController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ocr/process-bukti', [OCRController::class, 'processBukti'])->name('ocr.process-bukti');
    Route::post('/ocr/validate-data', [OCRController::class, 'validateData'])->name('ocr.validate-data');
});
