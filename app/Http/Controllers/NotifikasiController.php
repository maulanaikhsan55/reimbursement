<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $userId = Auth::id();
        $payloadCacheKey = Notifikasi::unreadPayloadCacheKey($userId);
        $payloadLockKey = $payloadCacheKey.'_lock';

        $payload = Cache::flexible($payloadCacheKey, [5, 15], function () use ($userId, $payloadLockKey) {
            return Cache::lock($payloadLockKey, 5)->block(2, function () use ($userId) {
                $baseQuery = Notifikasi::where('user_id', $userId)
                    ->where('is_read', false);

                $unread = (clone $baseQuery)
                    ->with(['pengajuan:pengajuan_id,nomor_pengajuan,status', 'user:id,name'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                return [
                    'unread' => $unread,
                    'unread_count' => (clone $baseQuery)->count(),
                ];
            });
        });

        return response()->json($payload);
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function markAsRead(string $notifikasi_id): RedirectResponse|JsonResponse
    {
        $notifikasi = Notifikasi::where('notifikasi_id', $notifikasi_id)->firstOrFail();

        if ($notifikasi->user_id !== Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'redirect_url' => null,
                ], 403);
            }

            return back()->with('error', 'Unauthorized');
        }

        $notifikasi->markAsRead();
        $redirectUrl = $notifikasi->resolveTargetUrlForViewer(Auth::user());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi telah ditandai sebagai dibaca',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read for current user
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(): RedirectResponse|JsonResponse
    {
        Notifikasi::markAllAsReadForUser(Auth::id());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi telah ditandai sebagai dibaca',
            ]);
        }

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
        $countCacheKey = Notifikasi::unreadCountCacheKey($userId);
        $countLockKey = $countCacheKey.'_lock';

        $count = Cache::flexible($countCacheKey, [5, 15], function () use ($userId, $countLockKey) {
            return Cache::lock($countLockKey, 5)->block(2, function () use ($userId) {
                return Notifikasi::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();
            });
        });

        return response()->json(['unread_count' => $count]);
    }
}
