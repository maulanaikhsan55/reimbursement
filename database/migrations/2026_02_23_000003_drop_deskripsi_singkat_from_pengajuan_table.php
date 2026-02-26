<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pengajuan', 'deskripsi_singkat')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->dropColumn('deskripsi_singkat');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('pengajuan', 'deskripsi_singkat')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->string('deskripsi_singkat', 180)->nullable()->after('tanggal_transaksi');
            });
        }
    }
};
