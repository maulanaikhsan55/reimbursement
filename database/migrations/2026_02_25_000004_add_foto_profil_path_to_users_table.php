<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'foto_profil_path')) {
                $table->string('foto_profil_path')->nullable()->after('nomor_rekening');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'foto_profil_path')) {
                $table->dropColumn('foto_profil_path');
            }
        });
    }
};
