<?php

namespace App\Models;

use App\Events\NotifikasiPengajuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Notifikasi extends Model
{
    use HasFactory;

    private static function forgetUnreadCountCache(int $userId): void
    {
        Cache::forget('notif_unread_count_user_'.$userId);
        Cache::forget('unread_notif_count_'.$userId);
    }

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
            self::forgetUnreadCountCache((int) $notifikasi->user_id);

            // Automatically broadcast notification when created
            // Use afterCommit to ensure the transaction is finished so listeners get updated data
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($notifikasi) {
                try {
                    event(new NotifikasiPengajuan(
                        $notifikasi->user_id,
                        $notifikasi->judul,
                        $notifikasi->pesan,
                        self::mapTypeToEventStyle($notifikasi->tipe),
                        $notifikasi->notifikasi_id,
                        $notifikasi->pengajuan_id
                    ));
                } catch (\Exception $e) {
                    // Ignore broadcast failures if Reverb is down
                    \Log::warning('Auto-broadcast failed for notification: '.$e->getMessage());
                }
            });
        });

        static::deleted(function ($notifikasi) {
            self::forgetUnreadCountCache((int) $notifikasi->user_id);
        });

        static::updated(function ($notifikasi) {
            if ($notifikasi->wasChanged('is_read')) {
                self::forgetUnreadCountCache((int) $notifikasi->user_id);
            }
        });
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

        self::forgetUnreadCountCache((int) $userId);
    }
}
