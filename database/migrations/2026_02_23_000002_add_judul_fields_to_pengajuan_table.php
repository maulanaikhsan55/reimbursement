<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->string('judul', 120)->nullable()->after('tanggal_transaksi');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengajuan', 'deskripsi_singkat')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->dropColumn('deskripsi_singkat');
            });
        }

        if (Schema::hasColumn('pengajuan', 'judul')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->dropColumn('judul');
            });
        }
    }
};
