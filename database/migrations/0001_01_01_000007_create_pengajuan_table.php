<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->bigIncrements('pengajuan_id');
            $table->string('nomor_pengajuan', 50)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('departemen_id');
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('coa_id')->nullable();
            $table->unsignedBigInteger('kas_bank_id')->nullable();

            $table->date('tanggal_pengajuan');
            $table->date('tanggal_transaksi');
            $table->string('nama_vendor', 100);
            $table->enum('jenis_transaksi', [
                'marketplace',
                'transfer_direct',
                'transport',
                'other',
            ])->nullable();
            $table->text('deskripsi');
            $table->decimal('nominal', 15, 2);
            $table->string('file_bukti', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_hash', 255)->nullable();

            $table->enum('status_validasi', ['pending', 'valid', 'invalid'])->default('pending');

            $table->text('catatan_pegawai')->nullable();
            $table->text('catatan_atasan')->nullable();
            $table->text('catatan_finance')->nullable();

            $table->enum('status', [
                'validasi_ai',
                'menunggu_atasan',
                'ditolak_atasan',
                'menunggu_finance',
                'ditolak_finance',
                'terkirim_accurate',
                'dicairkan',
                'selesai',
            ])->default('validasi_ai');

            $table->string('accurate_transaction_id', 100)->nullable();

            $table->unsignedBigInteger('disetujui_atasan_oleh')->nullable();
            $table->dateTime('tanggal_disetujui_atasan')->nullable();

            $table->unsignedBigInteger('disetujui_finance_oleh')->nullable();
            $table->dateTime('tanggal_disetujui_finance')->nullable();

            $table->date('tanggal_pencairan')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->foreign('departemen_id')
                ->references('departemen_id')
                ->on('departemen')
                ->onDelete('restrict');

            $table->foreign('kategori_id')
                ->references('kategori_id')
                ->on('kategori_biaya')
                ->onDelete('restrict');

            $table->foreign('coa_id')
                ->references('coa_id')
                ->on('coa')
                ->onDelete('set null');

            $table->foreign('kas_bank_id')
                ->references('kas_bank_id')
                ->on('kas_bank')
                ->onDelete('set null');

            $table->foreign('disetujui_atasan_oleh')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('disetujui_finance_oleh')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('user_id');
            $table->index('tanggal_pengajuan');
            $table->index('nama_vendor');
            $table->index('status_validasi');
            $table->index('kategori_id');
            $table->index('disetujui_atasan_oleh');
            $table->index('disetujui_finance_oleh');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('tanggal_disetujui_atasan');
            $table->index('tanggal_disetujui_finance');
            $table->index('tanggal_pencairan');
            $table->index('tanggal_transaksi');

            // Composite Indexes for better filtering performance
            $table->index(['user_id', 'status']);
            $table->index(['departemen_id', 'status']);
            $table->index(['status', 'tanggal_pengajuan']);
            $table->index(['coa_id', 'status']);
            $table->index(['departemen_id', 'tanggal_transaksi']);
            $table->index(['nama_vendor', 'tanggal_transaksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};
