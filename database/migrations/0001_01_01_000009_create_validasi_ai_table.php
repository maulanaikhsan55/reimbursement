<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validasi_ai', function (Blueprint $table) {
            $table->bigIncrements('validasi_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->enum('jenis_validasi', ['ocr', 'duplikasi', 'vendor', 'nominal', 'tanggal', 'anomali', 'pajak', 'sekuensial']);
            $table->enum('status', ['pending', 'valid', 'invalid'])->default('pending');
            $table->integer('confidence_score')->default(0);
            $table->json('hasil_ocr')->nullable()->comment('{"vendor": "PT Gojek", "nominal": 25000}');
            $table->text('pesan_validasi')->nullable();
            $table->boolean('is_blocking')->default(false)->comment('Apakah block submit atau tidak');
            $table->dateTime('validated_at')->useCurrent();
            $table->timestamps();

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->onDelete('cascade');

            $table->index('pengajuan_id');
            $table->index('jenis_validasi');
            $table->index('status');
            $table->index(['pengajuan_id', 'jenis_validasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validasi_ai');
    }
};
