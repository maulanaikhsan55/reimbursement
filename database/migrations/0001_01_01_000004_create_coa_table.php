<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->bigIncrements('coa_id');
            $table->string('kode_coa', 50)->unique();
            $table->string('nama_coa', 200);
            $table->string('tipe_akun', 50)->default('expense');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->string('currency_code', 10)->nullable();
            $table->unsignedBigInteger('parent_coa_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('synced_from_accurate')->default(false);
            $table->string('accurate_id', 100)->nullable()->unique();
            $table->date('as_of_date')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->foreign('parent_coa_id')
                ->references('coa_id')
                ->on('coa')
                ->onDelete('set null');

            $table->index('kode_coa');
            $table->index('tipe_akun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
