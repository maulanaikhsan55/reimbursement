<?php

namespace App\Models;

use App\Enums\PengajuanStatus;
use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $pengajuan_id
 * @property string $nomor_pengajuan
 * @property int $user_id
 * @property int $departemen_id
 * @property int $kategori_id
 * @property int|null $coa_id
 * @property int|null $kas_bank_id
 * @property \Illuminate\Support\Carbon $tanggal_pengajuan
 * @property \Illuminate\Support\Carbon $tanggal_transaksi
 * @property string $nama_vendor
 * @property string|null $jenis_transaksi
 * @property string $deskripsi
 * @property float $nominal
 * @property string|null $file_bukti
 * @property string|null $file_name
 * @property string|null $file_hash
 * @property \App\Enums\ValidationStatus $status_validasi
 * @property string|null $catatan_pegawai
 * @property string|null $catatan_atasan
 * @property string|null $catatan_finance
 * @property \App\Enums\PengajuanStatus $status
 * @property string|null $accurate_transaction_id
 * @property int|null $disetujui_atasan_oleh
 * @property \Illuminate\Support\Carbon|null $tanggal_disetujui_atasan
 * @property int|null $disetujui_finance_oleh
 * @property \Illuminate\Support\Carbon|null $tanggal_disetujui_finance
 * @property \Illuminate\Support\Carbon|null $tanggal_pencairan
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Departemen $departemen
 * @property-read \App\Models\KategoriBiaya $kategori
 * @property-read \App\Models\COA|null $coa
 * @property-read \App\Models\KasBank|null $kasBank
 * @property-read \App\Models\User|null $approvedByAtasan
 * @property-read \App\Models\User|null $approvedByFinance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PengajuanHistory[] $history
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ValidasiAi[] $validasiAi
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Notifikasi[] $notifikasi
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LogTransaksiAccurate[] $logTransaksiAccurate
 */
class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    protected $primaryKey = 'pengajuan_id';

    public $timestamps = true;

    protected $fillable = [
        'nomor_pengajuan',
        'user_id',
        'departemen_id',
        'kategori_id',
        'coa_id',
        'kas_bank_id',
        'tanggal_pengajuan',
        'tanggal_transaksi',
        'nama_vendor',
        'jenis_transaksi',
        'deskripsi',
        'nominal',
        'file_bukti',
        'file_name',
        'file_hash',
        'status_validasi',
        'catatan_pegawai',
        'catatan_atasan',
        'catatan_finance',
        'status',
        'accurate_transaction_id',
        'disetujui_atasan_oleh',
        'tanggal_disetujui_atasan',
        'disetujui_finance_oleh',
        'tanggal_disetujui_finance',
        'tanggal_pencairan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_transaksi' => 'date',
            'tanggal_disetujui_atasan' => 'datetime',
            'tanggal_disetujui_finance' => 'datetime',
            'tanggal_pencairan' => 'date',
            'nominal' => 'decimal:2',
            'status' => PengajuanStatus::class,
            'status_validasi' => ValidationStatus::class,
        ];
    }

    protected static function booted()
    {
        static::created(function ($pengajuan) {
            $pengajuan->history()->create([
                'user_id' => $pengajuan->user_id,
                'status_from' => null,
                'status_to' => $pengajuan->status,
                'action' => 'create',
                'catatan' => $pengajuan->catatan_pegawai,
            ]);

            // Clear dashboard caches
            $pengajuan->clearAllRelevantCaches();
        });

        static::saving(function ($pengajuan) {
            // Auto-populate coa_id from kategori if not set
            if (! $pengajuan->coa_id && $pengajuan->kategori_id) {
                $kategori = KategoriBiaya::find($pengajuan->kategori_id);
                if ($kategori && $kategori->default_coa_id) {
                    $pengajuan->coa_id = $kategori->default_coa_id;
                }
            }
        });

        static::updating(function ($pengajuan) {
            // Cegah perubahan jika sudah disetujui finance atau sudah cair
            // Kecuali jika perubahan datang dari proses pencairan (pemberian accurate_transaction_id)
            if (\in_array($pengajuan->getOriginal('status'), [PengajuanStatus::TERKIRIM_ACCURATE->value, PengajuanStatus::DICAIRKAN->value]) &&
                ! $pengajuan->isDirty('accurate_transaction_id') &&
                ! $pengajuan->isDirty('tanggal_pencairan') &&
                ! $pengajuan->isDirty('kas_bank_id')) {
                throw new \Exception('Pengajuan yang sudah terkirim ke Accurate atau sudah cair tidak boleh diubah.');
            }

            // Log status change
            if ($pengajuan->isDirty('status')) {
                $pengajuan->history()->create([
                    'user_id' => auth()->id() ?? $pengajuan->user_id,
                    'status_from' => $pengajuan->getOriginal('status'),
                    'status_to' => $pengajuan->status,
                    'action' => 'update_status',
                    'catatan' => $pengajuan->catatan_atasan ?: ($pengajuan->catatan_finance ?: null),
                ]);

                // Clear dashboard caches on status change
                $pengajuan->clearAllRelevantCaches();
            }
        });

        static::updated(function ($pengajuan) {
            // Clear cache if important fields changed (nominal, etc)
            if ($pengajuan->isDirty(['nominal', 'departemen_id', 'kategori_id', 'tanggal_pengajuan', 'user_id', 'disetujui_atasan_oleh'])) {
                $pengajuan->clearAllRelevantCaches();
            }
        });

        static::deleting(function ($pengajuan) {
            // Cegah penghapusan jika sudah disetujui finance atau sudah cair
            if (in_array($pengajuan->status, [
                PengajuanStatus::TERKIRIM_ACCURATE,
                PengajuanStatus::DICAIRKAN,
                PengajuanStatus::SELESAI,
            ])) {
                throw new \Exception('Pengajuan yang sudah terkirim ke Accurate atau sudah cair tidak boleh dihapus.');
            }

            // Remove file if exists
            if ($pengajuan->file_bukti) {
                try {
                    \Storage::disk('local')->delete($pengajuan->file_bukti);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete file on model deletion: '.$pengajuan->file_bukti.' - '.$e->getMessage());
                }
            }

            // Delete validation records first (if cascade not configured in DB)
            $pengajuan->validasiAi()->delete();

            // Delete history records
            $pengajuan->history()->delete();

            // Delete notifications
            $pengajuan->notifikasi()->delete();

            // Clear dashboard caches
            $pengajuan->clearAllRelevantCaches();
        });
    }

    /**
     * Clear all relevant dashboard caches
     */
    public function clearAllRelevantCaches()
    {
        // 1. Clear Finance Dashboard Cache
        \Illuminate\Support\Facades\Cache::forget('finance_dashboard_data');

        // 2. Clear Pegawai Dashboard & Stats Cache
        \Illuminate\Support\Facades\Cache::forget('pegawai_dashboard_'.$this->user_id);
        \Illuminate\Support\Facades\Cache::forget('pengajuan_stats_'.$this->user_id);

        // 3. Clear Atasan Dashboard Cache
        // We need the atasan_id of the pegawai who made the request
        $atasanId = $this->user->atasan_id ?? null;
        if (! $atasanId && $this->user_id) {
            $user = User::find($this->user_id);
            $atasanId = $user->atasan_id ?? null;
        }

        if ($atasanId) {
            \Illuminate\Support\Facades\Cache::forget('atasan_dashboard_'.$atasanId);
        }

        // Also clear for the specific atasan who approved it (if different or if changed)
        if ($this->disetujui_atasan_oleh) {
            \Illuminate\Support\Facades\Cache::forget('atasan_dashboard_'.$this->disetujui_atasan_oleh);
        }

        // Handle cases where disetujui_atasan_oleh was just changed (clear old one too)
        if ($this->isDirty('disetujui_atasan_oleh')) {
            $oldAtasanId = $this->getOriginal('disetujui_atasan_oleh');
            if ($oldAtasanId) {
                \Illuminate\Support\Facades\Cache::forget('atasan_dashboard_'.$oldAtasanId);
            }
        }
    }

    public function history(): HasMany
    {
        return $this->hasMany(PengajuanHistory::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBiaya::class, 'kategori_id', 'kategori_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(COA::class, 'coa_id', 'coa_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function approvedByAtasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_atasan_oleh', 'id');
    }

    public function approvedByFinance(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_finance_oleh', 'id');
    }

    public function validasiAi(): HasMany
    {
        return $this->hasMany(ValidasiAi::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function logTransaksiAccurate(): HasMany
    {
        return $this->hasMany(LogTransaksiAccurate::class, 'pengajuan_id', 'pengajuan_id');
    }

    /**
     * Accessor for total_nominal (Modern Laravel Style)
     */
    protected function totalNominal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nominal,
        );
    }

    /**
     * Determine initial status based on user role
     */
    public static function getInitialStatus(User $user): string
    {
        // Jika user adalah Finance, langsung ke status menunggu finance
        if ($user->role === 'finance') {
            return PengajuanStatus::MENUNGGU_FINANCE->value;
        }

        // Jika user adalah Atasan, bypass approval atasan -> langsung ke Finance
        if ($user->role === 'atasan') {
            return PengajuanStatus::MENUNGGU_FINANCE->value;
        }

        // Jika user adalah Pegawai
        if ($user->role === 'pegawai') {
            // Check if pegawai has a supervisor (atasan_id is not null)
            if ($user->atasan_id) {
                // Has supervisor -> send to atasan for approval
                return PengajuanStatus::MENUNGGU_ATASAN->value;
            } else {
                // No supervisor -> skip atasan, go directly to finance
                return PengajuanStatus::MENUNGGU_FINANCE->value;
            }
        }

        // Default fallback
        return PengajuanStatus::MENUNGGU_ATASAN->value;
    }

    /**
     * Check if this request is auto-approved by system (bypass)
     */
    public function isBypassed(): bool
    {
        // Bypass jika role atasan atau finance dan belum ada approval atasan (karena memang tidak butuh)
        return in_array($this->user->role, ['atasan', 'finance']) && $this->disetujui_atasan_oleh === null;
    }

    /**
     * Get personal statistics for a user
     */
    public static function getPersonalStats(int $userId)
    {
        $pendingStatuses = [
            PengajuanStatus::VALIDASI_AI->value,
            PengajuanStatus::MENUNGGU_ATASAN->value,
            PengajuanStatus::MENUNGGU_FINANCE->value,
            PengajuanStatus::TERKIRIM_ACCURATE->value,
        ];

        $approvedStatus = PengajuanStatus::DICAIRKAN->value;

        $rejectedStatuses = [
            PengajuanStatus::DITOLAK_ATASAN->value,
            PengajuanStatus::DITOLAK_FINANCE->value,
        ];

        return self::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("'.implode('","', $pendingStatuses).'") THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "'.$approvedStatus.'" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status IN ("'.implode('","', $rejectedStatuses).'") THEN 1 ELSE 0 END) as rejected,
                SUM(nominal) as total_nominal,
                SUM(CASE WHEN status = "'.$approvedStatus.'" THEN nominal ELSE 0 END) as nominal_approved,
                SUM(CASE WHEN status IN ("'.implode('","', $pendingStatuses).'") THEN nominal ELSE 0 END) as nominal_pending
            ')
            ->first();
    }

    /**
     * Get budget usage for a department in a specific month/year
     */
    public static function getBudgetUsage(int $departemenId, int $month, int $year, ?int $excludeId = null)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = self::where('departemen_id', $departemenId)
            ->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNotIn('status', [
                PengajuanStatus::DITOLAK_ATASAN->value,
                PengajuanStatus::DITOLAK_FINANCE->value,
            ]);

        if ($excludeId) {
            $query->where('pengajuan_id', '!=', $excludeId);
        }

        return $query->sum('nominal');
    }

    /**
     * Get budget status details for a department
     */
    public static function getBudgetStatus(int $departemenId, $nominal = 0, $month = null, $year = null, $excludeId = null)
    {
        $departemen = Departemen::find($departemenId);
        if (! $departemen || $departemen->budget_limit <= 0) {
            return null;
        }

        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $currentUsage = self::getBudgetUsage($departemenId, $month, $year, $excludeId);
        $limit = (float) $departemen->budget_limit;
        $totalIfApproved = (float) $currentUsage + (float) $nominal;
        $percentage = ($totalIfApproved / $limit) * 100;

        return [
            'limit' => $limit,
            'usage' => $currentUsage,
            'current' => (float) $nominal,
            'total_if_approved' => $totalIfApproved,
            'remaining' => max(0, $limit - $totalIfApproved),
            'percentage' => round($percentage, 2),
            'status' => $percentage > 100 ? 'danger' : ($percentage > 80 ? 'warning' : 'success'),
            'is_over' => $percentage > 100,
        ];
    }

    /**
     * Get team statistics for an atasan
     */
    public static function getTeamStats(int $atasanId, array $subordinateIds)
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $slaDate = $now->copy()->subDays(3);

        $statusMenungguAtasan = PengajuanStatus::MENUNGGU_ATASAN->value;
        $statusDitolakAtasan = PengajuanStatus::DITOLAK_ATASAN->value;

        return self::whereIn('user_id', $subordinateIds)
            ->selectRaw('
                COUNT(*) as total_team_requests,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_approvals,
                SUM(CASE WHEN disetujui_atasan_oleh = ? AND tanggal_disetujui_atasan >= ? AND tanggal_disetujui_atasan <= ? THEN 1 ELSE 0 END) as approved_this_month,
                SUM(CASE WHEN status = ? AND updated_at >= ? AND updated_at <= ? THEN 1 ELSE 0 END) as rejected_this_month,
                SUM(CASE WHEN disetujui_atasan_oleh = ? AND tanggal_disetujui_atasan >= ? AND tanggal_disetujui_atasan <= ? THEN nominal ELSE 0 END) as this_month_approved_amount,
                SUM(CASE WHEN status = ? AND updated_at >= ? AND updated_at <= ? THEN nominal ELSE 0 END) as this_month_rejected_amount,
                SUM(CASE WHEN status = ? THEN nominal ELSE 0 END) as pending_nominal,
                SUM(CASE WHEN status = ? AND created_at < ? THEN 1 ELSE 0 END) as oversla_count
            ', [
                $statusMenungguAtasan,
                $atasanId, $startOfMonth, $endOfMonth,
                $statusDitolakAtasan, $startOfMonth, $endOfMonth,
                $atasanId, $startOfMonth, $endOfMonth,
                $statusDitolakAtasan, $startOfMonth, $endOfMonth,
                $statusMenungguAtasan,
                $statusMenungguAtasan, $slaDate,
            ])
            ->first();
    }
}
