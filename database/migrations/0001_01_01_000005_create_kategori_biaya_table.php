<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_biaya', function (Blueprint $table) {
            $table->bigIncrements('kategori_id');
            $table->string('kode_kategori', 20)->unique();
            $table->string('nama_kategori', 100);
            $table->unsignedBigInteger('default_coa_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('default_coa_id')
                ->references('coa_id')
                ->on('coa')
                ->onDelete('set null');

            $table->index('kode_kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_biaya');
    }
};
