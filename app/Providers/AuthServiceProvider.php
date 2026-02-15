<?php

namespace App\Providers;

use App\Models\Pengajuan;
use App\Policies\PengajuanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Pengajuan::class => PengajuanPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
