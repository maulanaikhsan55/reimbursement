<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_bank', function (Blueprint $table) {
            $table->bigIncrements('kas_bank_id');
            $table->string('kode_kas_bank', 50)->unique();
            $table->string('nama_kas_bank', 255);
            $table->string('tipe_akun', 50)->default('Kas & Bank');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->string('currency_code', 10)->nullable();
            $table->unsignedBigInteger('coa_id')->nullable();
            $table->unsignedBigInteger('parent_kas_bank_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('accurate_id', 100)->nullable()->unique();
            $table->date('as_of_date')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->foreign('coa_id')->references('coa_id')->on('coa')->onDelete('set null');
            $table->foreign('parent_kas_bank_id')->references('kas_bank_id')->on('kas_bank')->onDelete('set null');
            $table->index('kode_kas_bank');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_bank');
    }
};
