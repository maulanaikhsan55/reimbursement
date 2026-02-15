<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_transaksi_accurate', function (Blueprint $table) {
            $table->bigIncrements('log_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->string('accurate_transaction_id', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->useCurrent();
            $table->timestamps();

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->onDelete('cascade');

            $table->index('pengajuan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_transaksi_accurate');
    }
};
