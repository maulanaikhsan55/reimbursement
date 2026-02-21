<?php

namespace App\Livewire;

use App\Models\Notifikasi;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Route;

class NotificationList extends Component
{
    use WithPagination;

    public function mount()
    {
        if (! auth()->check()) {
            return;
        }

        // Notification page acts as history center.
        // Once opened, existing unread items are marked as read.
        Notifikasi::markAllAsReadForUser(auth()->id());
        $this->dispatch('notifikasi-baru');
        $this->dispatch('refresh-notif-badges');
    }

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
            return redirect()->to($this->resolveNotificationTarget($notifikasi));
        }
    }

    private function resolveNotificationTarget(Notifikasi $notifikasi): string
    {
        $role = auth()->user()->role ?? '';

        if ($notifikasi->pengajuan_id) {
            if ($role === 'pegawai') {
                return route('pegawai.pengajuan.show', $notifikasi->pengajuan_id);
            }

            if ($role === 'atasan') {
                return route('atasan.approval.show', $notifikasi->pengajuan_id);
            }

            if ($role === 'finance') {
                return route('finance.approval.show', $notifikasi->pengajuan_id);
            }
        }

        $fallbackRoute = $role.'.notifikasi';
        if (\Illuminate\Support\Facades\Route::has($fallbackRoute)) {
            return route($fallbackRoute);
        }

        $dashboardRoute = $role.'.dashboard';
        if (\Illuminate\Support\Facades\Route::has($dashboardRoute)) {
            return route($dashboardRoute);
        }

        return url('/');
    }

    public function markAllAsRead()
    {
        Notifikasi::markAllAsReadForUser(auth()->id());
        $this->dispatch('notifikasi-baru'); // Refresh the bell as well
        $this->dispatch('refresh-notif-badges'); // Update sidebar
    }

    public function render()
    {
        $role = auth()->user()->role ?? '';
        $routeName = $role ? $role.'.notifikasi' : '';
        $paginationPath = Route::has($routeName)
            ? route($routeName, [], false)
            : '/'.ltrim(request()->path(), '/');

        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.notifikasi', 20))
            ->withPath($paginationPath)
            ->withQueryString();

        return view('livewire.notification-list', [
            'notifikasi' => $notifikasi,
        ]);
    }
}
