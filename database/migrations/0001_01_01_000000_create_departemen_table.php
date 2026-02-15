<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departemen', function (Blueprint $table) {
            $table->bigIncrements('departemen_id');
            $table->string('kode_departemen', 10)->nullable()->unique();
            $table->string('nama_departemen', 100);
            $table->decimal('budget_limit', 15, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('accurate_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index('kode_departemen');
            $table->index('accurate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departemen');
    }
};
