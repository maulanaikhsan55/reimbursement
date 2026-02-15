<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal', function (Blueprint $table) {
            $table->bigIncrements('jurnal_id');
            $table->unsignedBigInteger('pengajuan_id')->nullable();
            $table->unsignedBigInteger('coa_id');
            $table->decimal('nominal', 15, 2);
            $table->enum('tipe_posting', ['debit', 'credit']);
            $table->date('tanggal_posting');
            $table->string('nomor_ref', 50);
            $table->text('deskripsi')->nullable();
            $table->dateTime('posted_at')->useCurrent();
            $table->unsignedBigInteger('posted_by');
            $table->timestamps();

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->onDelete('cascade');

            $table->foreign('coa_id')
                ->references('coa_id')
                ->on('coa')
                ->onDelete('restrict');

            $table->foreign('posted_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->index('pengajuan_id');
            $table->index('coa_id');
            $table->index('tanggal_posting');
            $table->index('nomor_ref');
            $table->index('tipe_posting');
            $table->index(['coa_id', 'tanggal_posting']);
            $table->index(['nomor_ref', 'coa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
