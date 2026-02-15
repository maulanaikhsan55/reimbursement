<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\KategoriBiaya::all();
echo 'Total Categories: '.$all->count()."\n";
foreach ($all as $k) {
    echo '- '.$k->nama_kategori.' (COA: '.($k->default_coa_id ?: 'NULL').', Active: '.($k->is_active ? 'YES' : 'NO').")\n";
}
