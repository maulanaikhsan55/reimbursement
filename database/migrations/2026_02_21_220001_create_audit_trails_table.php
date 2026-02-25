<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_trails')) {
            return;
        }

        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_role', 32)->nullable();
            $table->string('event', 120);
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->unsignedBigInteger('pengajuan_id')->nullable();
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->foreign('actor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('pengajuan_id')
                ->references('pengajuan_id')
                ->on('pengajuan')
                ->nullOnDelete();

            $table->index('event');
            $table->index('actor_id');
            $table->index('pengajuan_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
