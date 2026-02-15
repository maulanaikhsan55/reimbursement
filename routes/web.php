<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/password/reset', function () {
        return view('auth.password-request');
    })->name('password.request');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/proof/{pengajuan}', [\App\Http\Controllers\FileController::class, 'showProof'])
    ->middleware(['auth'])
    ->name('proof.show');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('landing')->with('success', 'Email berhasil diverifikasi!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Link verifikasi baru telah dikirim!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'check.pegawai.role', 'force.password.change'])->prefix('pegawai')->name('pegawai.')->group(function () {
    require __DIR__.'/pegawai.php';
});

Route::middleware(['auth', 'check.atasan.role', 'force.password.change'])->prefix('atasan')->name('atasan.')->group(function () {
    require __DIR__.'/atasan.php';
});

Route::middleware(['auth', 'check.finance.role', 'force.password.change'])->prefix('finance')->name('finance.')->group(function () {
    require __DIR__.'/finance.php';
});
