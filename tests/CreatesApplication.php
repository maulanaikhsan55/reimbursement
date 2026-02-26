<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    public function createApplication()
    {
        $storageDirs = [
            __DIR__.'/../storage/framework/cache',
            __DIR__.'/../storage/framework/sessions',
            __DIR__.'/../storage/framework/views',
            __DIR__.'/../storage/framework/testing',
        ];

        foreach ($storageDirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
