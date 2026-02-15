<?php

namespace App\Livewire;

use App\Models\Notifikasi;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public function getListeners()
    {
        if (! auth()->check()) {
            return [];
        }

        return [
            'echo-private:App.Models.User.'.auth()->id().',.notifikasi.pengajuan' => 'refreshNotifications',
            'notifikasi-baru' => 'refreshNotifications',
        ];
    }

    public function refreshNotifications()
    {
        // This will trigger a re-render
    }

    public function markAsRead($notificationId)
    {
        $notifikasi = Notifikasi::find($notificationId);
        if ($notifikasi && $notifikasi->user_id === auth()->id()) {
            $notifikasi->markAsRead();
            $this->dispatch('notifikasi-baru'); // Refresh the bell as well

            // Redirect logic based on role
            if ($notifikasi->pengajuan_id) {
                $role = auth()->user()->role;

                if ($role === 'pegawai') {
                    return redirect()->route('pegawai.pengajuan.show', $notifikasi->pengajuan_id);
                } elseif ($role === 'atasan') {
                    return redirect()->route('atasan.approval.show', $notifikasi->pengajuan_id);
                } elseif ($role === 'finance') {
                    return redirect()->route('finance.approval.show', $notifikasi->pengajuan_id);
                }
            }
        }
    }

    public function markAllAsRead()
    {
        Notifikasi::markAllAsReadForUser(auth()->id());
        $this->dispatch('notifikasi-baru'); // Refresh the bell as well
        $this->dispatch('refresh-notif-badges'); // Update sidebar
    }

    public function render()
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.notifikasi', 20));

        return view('livewire.notification-list', [
            'notifikasi' => $notifikasi,
        ]);
    }
}
