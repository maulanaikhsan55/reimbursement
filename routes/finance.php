<?php

use App\Http\Controllers\Finance\Dashboard\DashboardController;
use App\Http\Controllers\Finance\MasterData\COAController;
use App\Http\Controllers\Finance\MasterData\DepartemenController;
use App\Http\Controllers\Finance\MasterData\KasBankController;
use App\Http\Controllers\Finance\MasterData\KategoriBiayaController;
use App\Http\Controllers\Finance\MasterData\UserController;
use App\Http\Controllers\Finance\Profile\ProfileController;
use App\Http\Controllers\Finance\Reports\ReportController;
use App\Http\Controllers\Finance\Workflows\ApprovalController;
use App\Http\Controllers\Finance\Workflows\DisbursementController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

Route::group([], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifikasi', [NotifikasiController::class, 'getAll'])->name('notifikasi');
    Route::get('/notifikasi/unread', [NotifikasiController::class, 'getUnread'])->name('notifikasi.unread');
    Route::get('/notifikasi/count', [NotifikasiController::class, 'getCount'])->name('notifikasi.count');
    Route::post('/notifikasi/{notifikasi_id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.mark-read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.mark-all-read');

    Route::prefix('masterdata')->name('masterdata.')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/departemen', [DepartemenController::class, 'index'])->name('departemen.index');
        Route::put('/departemen/{departemen}', [DepartemenController::class, 'update'])->name('departemen.update');
        Route::post('/departemen/sync', [DepartemenController::class, 'syncFromAccurate'])->name('departemen.sync');

        Route::get('/kategori_biaya', [KategoriBiayaController::class, 'index'])->name('kategori_biaya.index');
        Route::get('/kategori_biaya/create', [KategoriBiayaController::class, 'create'])->name('kategori_biaya.create');
        Route::post('/kategori_biaya', [KategoriBiayaController::class, 'store'])->name('kategori_biaya.store');
        Route::get('/kategori_biaya/{kategori_biaya}/edit', [KategoriBiayaController::class, 'edit'])->name('kategori_biaya.edit');
        Route::put('/kategori_biaya/{kategori_biaya}', [KategoriBiayaController::class, 'update'])->name('kategori_biaya.update');
        Route::delete('/kategori_biaya/{kategori_biaya}', [KategoriBiayaController::class, 'destroy'])->name('kategori_biaya.destroy');

        Route::get('/coa', [COAController::class, 'index'])->name('coa.index');
        Route::post('/coa/sync', [COAController::class, 'syncFromAccurate'])->name('coa.sync');

        Route::get('/kas_bank', [KasBankController::class, 'index'])->name('kas_bank.index');
        Route::post('/kas_bank/sync', [KasBankController::class, 'syncFromAccurate'])->name('kas_bank.sync');
        Route::get('/kas_bank/{kasBank}/balance', [KasBankController::class, 'checkBalance'])->name('kas_bank.balance');
    });

    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::get('/approval/count', [ApprovalController::class, 'getCount'])->name('approval.count');
    Route::get('/approval/export-csv', [ApprovalController::class, 'exportCsv'])->name('approval.export-csv');
    Route::get('/approval/export-pdf', [ApprovalController::class, 'exportPdf'])->name('approval.export-pdf');
    Route::get('/approval/{pengajuan}', [ApprovalController::class, 'show'])->name('approval.show');
    Route::post('/approval/{pengajuan}/send-to-accurate', [ApprovalController::class, 'send'])->name('approval.send-to-accurate');
    Route::post('/approval/{pengajuan}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    Route::post('/approval/{pengajuan}/retry', [ApprovalController::class, 'retry'])->name('approval.retry');

    Route::get('/disbursement', [DisbursementController::class, 'index'])->name('disbursement.index');
    Route::get('/disbursement/count', [DisbursementController::class, 'getCount'])->name('disbursement.count');
    Route::get('/disbursement/export-csv', [DisbursementController::class, 'exportCsv'])->name('disbursement.export-csv');
    Route::get('/disbursement/export-pdf', [DisbursementController::class, 'exportPdf'])->name('disbursement.export-pdf');
    Route::get('/disbursement/{pengajuan}', [DisbursementController::class, 'show'])->name('disbursement.show');
    Route::post('/disbursement/{pengajuan}/mark', [DisbursementController::class, 'mark'])->name('disbursement.mark');
    Route::get('/disbursement-history', [DisbursementController::class, 'history'])->name('disbursement.history');
    Route::get('/disbursement-history/export-csv', [DisbursementController::class, 'historyExportCsv'])->name('disbursement.history-export-csv');
    Route::get('/disbursement-history/export-pdf', [DisbursementController::class, 'historyExportPdf'])->name('disbursement.history-export-pdf');

    Route::get('/reports', [ReportController::class, 'index'])->name('report.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('report.export');
    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('report.summary');
    Route::get('/reports/budget-audit', [ReportController::class, 'budgetAudit'])->name('report.budget_audit');

    Route::get('/reports/jurnal-umum', [ReportController::class, 'jurnalUmum'])->name('report.jurnal_umum');
    Route::post('/reports/jurnal-umum/sync-by-ref', [ReportController::class, 'syncByRef'])->name('report.jurnal_umum.sync_by_ref');
    Route::get('/reports/jurnal-umum/export-csv', [ReportController::class, 'jurnalUmumExportCsv'])->name('report.jurnal_umum.export_csv');
    Route::get('/reports/jurnal-umum/export-pdf', [ReportController::class, 'jurnalUmumExportPdf'])->name('report.jurnal_umum.export_pdf');

    Route::get('/reports/buku-besar', [ReportController::class, 'bukuBesar'])->name('report.buku_besar');
    Route::get('/reports/reconciliation', [ReportController::class, 'reconciliationDashboard'])->name('report.reconciliation');
    Route::get('/reports/buku-besar/reconcile', [ReportController::class, 'reconcileLedger'])->name('report.buku_besar.reconcile');
    Route::post('/reports/buku-besar/sync-missing', [ReportController::class, 'syncMissingTransaction'])->name('report.buku_besar.sync_missing');
    Route::get('/reports/buku-besar/export-csv', [ReportController::class, 'bukuBesarExportCsv'])->name('report.buku_besar.export_csv');
    Route::get('/reports/buku-besar/export-pdf', [ReportController::class, 'bukuBesarExportPdf'])->name('report.buku_besar.export_pdf');

    Route::get('/reports/arus-kas', [ReportController::class, 'laporanArusKas'])->name('report.laporan_arus_kas');
    Route::get('/reports/arus-kas/export-csv', [ReportController::class, 'laporanArusKasExportCsv'])->name('report.laporan_arus_kas.export_csv');
    Route::get('/reports/arus-kas/export-pdf', [ReportController::class, 'laporanArusKasExportPdf'])->name('report.laporan_arus_kas.export_pdf');
});
