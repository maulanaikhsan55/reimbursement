<?php

namespace App\Jobs;

use App\Models\Notifikasi;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateUserNotificationJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 20;

    public int $uniqueFor = 60;

    public function __construct(
        public int $userId,
        public ?int $pengajuanId,
        public string $tipe,
        public string $judul,
        public string $pesan,
    ) {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return sha1(implode('|', [
            $this->userId,
            $this->pengajuanId ?? 'null',
            $this->tipe,
            $this->judul,
            $this->pesan,
        ]));
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('notif-create-user-'.$this->userId))->expireAfter(20),
            (new ThrottlesExceptions(10, 60))->backoff(3),
        ];
    }

    public function handle(): void
    {
        Notifikasi::create([
            'user_id' => $this->userId,
            'pengajuan_id' => $this->pengajuanId,
            'tipe' => $this->tipe,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'is_read' => false,
        ]);
    }
}
