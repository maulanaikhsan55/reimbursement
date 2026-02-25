<?php

namespace App\Models;

use App\Events\NotifikasiPengajuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class Notifikasi extends Model
{
    use HasFactory;

    public const UNREAD_COUNT_CACHE_PREFIX = 'notif_unread_count_user_';
    public const LEGACY_UNREAD_COUNT_CACHE_PREFIX = 'unread_notif_count_';
    public const UNREAD_PAYLOAD_CACHE_PREFIX = 'notif_unread_payload_user_';

    protected $table = 'notifikasi';

    protected $primaryKey = 'notifikasi_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'pengajuan_id',
        'tipe',
        'judul',
        'pesan',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($notifikasi) {
            self::flushUnreadCaches((int) $notifikasi->user_id);

            try {
                if (! config('reimbursement.features.realtime_notifications', true)
                    || ! config('reimbursement.features.broadcast_notifications', true)
                    || config('broadcasting.default') === 'null') {
                    return;
                }

                event(new NotifikasiPengajuan(
                    $notifikasi->user_id,
                    $notifikasi->judul,
                    $notifikasi->pesan,
                    self::mapTypeToEventStyle($notifikasi->tipe),
                    $notifikasi->notifikasi_id,
                    $notifikasi->pengajuan_id,
                    $notifikasi->pengajuan_id
                        ? (int) $notifikasi->pengajuan()->value('user_id')
                        : null
                ));
            } catch (\Exception $e) {
                // Ignore broadcast failures if Reverb/queue is down.
                \Log::warning('Auto-broadcast failed for notification: '.$e->getMessage());
            }
        });

        static::deleted(function ($notifikasi) {
            self::flushUnreadCaches((int) $notifikasi->user_id);
        });

        static::updated(function ($notifikasi) {
            if ($notifikasi->wasChanged('is_read')) {
                self::flushUnreadCaches((int) $notifikasi->user_id);
            }
        });
    }

    public static function unreadCountCacheKey(int $userId): string
    {
        return self::UNREAD_COUNT_CACHE_PREFIX.$userId;
    }

    public static function unreadPayloadCacheKey(int $userId): string
    {
        return self::UNREAD_PAYLOAD_CACHE_PREFIX.$userId;
    }

    public static function flushUnreadCaches(int $userId): void
    {
        Cache::forget(self::unreadCountCacheKey($userId));
        Cache::forget(self::LEGACY_UNREAD_COUNT_CACHE_PREFIX.$userId);
        Cache::forget(self::unreadPayloadCacheKey($userId));
    }

    private static function mapTypeToEventStyle($type)
    {
        $normalizedType = strtolower((string) $type);

        if (in_array($normalizedType, ['success', 'error', 'warning', 'info'], true)) {
            return $normalizedType;
        }

        if (str_contains($normalizedType, 'disetujui') || str_contains($normalizedType, 'dicairkan') || str_contains($normalizedType, 'terkirim')) {
            return 'success';
        }
        if (str_contains($normalizedType, 'ditolak') || str_contains($normalizedType, 'gagal')) {
            return 'error';
        }

        return 'info';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function resolveTargetUrlForViewer(?User $viewer = null): string
    {
        $viewer ??= auth()->user();
        if (! $viewer) {
            return url('/');
        }

        $role = strtolower((string) ($viewer->role ?? ''));

        if ($this->pengajuan_id) {
            if ($role === 'pegawai') {
                return route('pegawai.pengajuan.show', $this->pengajuan_id);
            }

            if ($role === 'atasan') {
                $ownerId = null;
                if ($this->relationLoaded('pengajuan')) {
                    $ownerId = (int) ($this->pengajuan?->user_id ?? 0);
                } else {
                    $ownerId = (int) $this->pengajuan()->value('user_id');
                }

                if ($ownerId === (int) $viewer->id) {
                    return route('atasan.pengajuan.show', $this->pengajuan_id);
                }

                return route('atasan.approval.show', $this->pengajuan_id);
            }

            if ($role === 'finance') {
                return route('finance.approval.show', $this->pengajuan_id);
            }
        }

        $notificationRoute = $role.'.notifikasi';
        if (Route::has($notificationRoute)) {
            return route($notificationRoute);
        }

        $dashboardRoute = $role.'.dashboard';
        if (Route::has($dashboardRoute)) {
            return route($dashboardRoute);
        }

        return url('/');
    }

    public static function getUnreadForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function markAllAsReadForUser($userId)
    {
        static::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        self::flushUnreadCaches((int) $userId);
    }
}
