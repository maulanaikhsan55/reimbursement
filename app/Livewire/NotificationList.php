<?php

namespace App\Livewire;

use App\Models\Notifikasi;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Route;

class NotificationList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $readStatus = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'readStatus' => ['except' => 'all'],
    ];

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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedReadStatus(): void
    {
        $this->resetPage();
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
        return $notifikasi->resolveTargetUrlForViewer(auth()->user());
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

        $baseQuery = Notifikasi::where('user_id', auth()->id());
        $totalCount = (clone $baseQuery)->count();
        $unreadCount = (clone $baseQuery)->where('is_read', false)->count();

        $notifikasi = $baseQuery
            ->when($this->readStatus === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($this->readStatus === 'read', fn ($query) => $query->where('is_read', true))
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('judul', 'like', '%'.$search.'%')
                        ->orWhere('pesan', 'like', '%'.$search.'%')
                        ->orWhere('tipe', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.pagination.notifikasi', 10))
            ->withPath($paginationPath)
            ->withQueryString();

        return view('livewire.notification-list', [
            'notifikasi' => $notifikasi,
            'totalCount' => $totalCount,
            'unreadCount' => $unreadCount,
        ]);
    }
}
