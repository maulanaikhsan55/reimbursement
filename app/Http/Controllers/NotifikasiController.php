<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Notifikasi Controller
 *
 * Manages user notifications:
 * - Fetch unread notifications (up to 10)
 * - Fetch all notifications for display
 * - Mark individual notifications as read
 * - Mark all notifications as read
 * - Get unread count for badge display
 */
class NotifikasiController extends Controller
{
    /**
     * Get unread notifications for current user
     *
     * Returns:
     * - List of up to 10 unread notifications with relationships
     * - Total count of unread notifications
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnread()
    {
        $user = Auth::user();
        $unread = Notifikasi::where('user_id', $user->id)
            ->where('is_read', false)
            ->with(['pengajuan:pengajuan_id,nomor_pengajuan,status', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = Notifikasi::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread' => $unread,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Display all notifications view
     * Route to appropriate role-specific notification page
     *
     * @return \Illuminate\View\View
     */
    public function getAll()
    {
        $user = Auth::user();
        $viewPath = match ($user->role) {
            'finance' => 'dashboard.finance.notifikasi',
            'pegawai' => 'dashboard.pegawai.notifikasi',
            'atasan' => 'dashboard.atasan.notifikasi',
            default => 'dashboard.finance.notifikasi'
        };

        return view($viewPath);
    }

    /**
     * Mark individual notification as read
     *
     * Security: Verifies notification belongs to current user
     *
     * @param  string  $notifikasi_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($notifikasi_id)
    {
        $notifikasi = Notifikasi::where('notifikasi_id', $notifikasi_id)->firstOrFail();

        if ($notifikasi->user_id !== Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return back()->with('error', 'Unauthorized');
        }

        $notifikasi->markAsRead();
        Cache::forget('notif_unread_count_user_'.Auth::id());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi telah ditandai sebagai dibaca',
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read for current user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        Notifikasi::markAllAsReadForUser(Auth::id());
        Cache::forget('notif_unread_count_user_'.Auth::id());

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Get count of unread notifications (for badge display)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCount()
    {
        $userId = Auth::id();
        $count = Cache::remember('notif_unread_count_user_'.$userId, 10, function () use ($userId) {
            return Notifikasi::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
        });

        return response()->json(['unread_count' => $count]);
    }
}
