<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->bigIncrements('notifikasi_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pengajuan_id')->nullable();
            $table->string('tipe');
            $table->string('judul', 255);
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->onDelete('cascade');

            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
            $table->index(['user_id', 'is_read', 'created_at'], 'notifikasi_user_read_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
