<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pengajuan_history')) {
            return;
        }
        Schema::create('pengajuan_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('pengajuan_id');
            $table->unsignedBigInteger('user_id')->comment('User yang melakukan aksi');
            $table->string('status_from')->nullable();
            $table->string('status_to');
            $table->string('action');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->index('pengajuan_id');
            $table->index('user_id');
            $table->index(['pengajuan_id', 'created_at'], 'pengajuan_history_pengajuan_created_idx');
            $table->index(['user_id', 'action', 'created_at'], 'pengajuan_history_user_action_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_history');
    }
};
