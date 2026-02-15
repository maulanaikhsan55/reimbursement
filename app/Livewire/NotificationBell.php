<?php

namespace App\Livewire;

use App\Models\Notifikasi;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;

    public $notifications = [];

    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    #[On('notifikasi-baru')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (! auth()->check()) {
            return;
        }

        $userId = auth()->id();
        $this->unreadCount = Notifikasi::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        // Load last 5 notifications for the dropdown
        $this->notifications = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function markAsRead($notificationId)
    {
        $notifikasi = Notifikasi::find($notificationId);
        if ($notifikasi && $notifikasi->user_id === auth()->id()) {
            $notifikasi->markAsRead();
            $this->loadNotifications();

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
        $this->loadNotifications();

        // Dispatch event for JS to update sidebar badges
        $this->dispatch('refresh-notif-badges');
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
