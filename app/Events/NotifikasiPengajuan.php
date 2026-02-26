<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifikasiPengajuan implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public $userId;

    public $message;

    public $type; // 'success', 'error', 'info'

    public $title;

    public $notifikasiId;

    public $pengajuanId;

    public $pengajuanOwnerId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $title, $message, $type = 'info', $notifikasiId = null, $pengajuanId = null, $pengajuanOwnerId = null)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->notifikasiId = $notifikasiId;
        $this->pengajuanId = $pengajuanId;
        $this->pengajuanOwnerId = $pengajuanOwnerId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->userId),
        ];
    }

    public function broadcastAs()
    {
        return 'notifikasi.pengajuan';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'notifikasi_id' => $this->notifikasiId,
            'pengajuan_id' => $this->pengajuanId,
            'pengajuan_owner_id' => $this->pengajuanOwnerId,
        ];
    }
}
