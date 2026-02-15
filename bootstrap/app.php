<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.pegawai.role' => \App\Http\Middleware\CheckPegawaiRole::class,
            'check.atasan.role' => \App\Http\Middleware\CheckAtasanRole::class,
            'check.finance.role' => \App\Http\Middleware\CheckFinanceRole::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'optimize.cache' => \App\Http\Middleware\OptimizeAssetCaching::class,
        ]);

        // Global middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\OptimizeAssetCaching::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
