<?php

use App\Http\Controllers\Atasan\ApprovalController;
use App\Http\Controllers\Atasan\DashboardController;
use App\Http\Controllers\Atasan\PengajuanController;
use App\Http\Controllers\Atasan\ProfileController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Pegawai\ValidasiAIController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
});

Route::group([], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifikasi', [NotifikasiController::class, 'getAll'])->name('notifikasi');
    Route::get('/notifikasi/unread', [NotifikasiController::class, 'getUnread'])->name('notifikasi.unread');
    Route::get('/notifikasi/count', [NotifikasiController::class, 'getCount'])->name('notifikasi.count');
    Route::post('/notifikasi/{notifikasi_id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.mark-read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.mark-all-read');

    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::get('/approval/count', [ApprovalController::class, 'getCount'])->name('approval.count');
    Route::post('/approval/export/pdf', [ApprovalController::class, 'exportPdf'])->name('approval.export-pdf');
    Route::post('/approval/export/csv', [ApprovalController::class, 'exportCsv'])->name('approval.export-csv');
    Route::get('/approval/{pengajuan}', [ApprovalController::class, 'show'])->name('approval.show');
    Route::post('/approval/{pengajuan}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/approval/{pengajuan}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');

    Route::resource('pengajuan', PengajuanController::class, ['except' => ['edit', 'update']]);
    Route::post('/pengajuan/export/pdf', [PengajuanController::class, 'exportPdf'])->name('pengajuan.export-pdf');
    Route::post('/pengajuan/export/csv', [PengajuanController::class, 'exportCsv'])->name('pengajuan.export-csv');

    Route::post('/validasi-ai/process-file', [ValidasiAIController::class, 'processFile'])
        ->name('validasi-ai.process-file')
        ->middleware('throttle:10,1'); // Max 10 requests per minute
    Route::post('/validasi-ai/validate-input', [ValidasiAIController::class, 'validateInput'])
        ->name('validasi-ai.validate-input')
        ->middleware('throttle:10,1'); // Max 10 requests per minute
});
