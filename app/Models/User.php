<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $jabatan
 * @property int|null $departemen_id
 * @property int|null $atasan_id
 * @property string|null $nomor_telepon
 * @property string|null $nama_bank
 * @property string|null $nomor_rekening
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $password_reset_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Departemen|null $departemen
 * @property-read \App\Models\User|null $atasan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $bawahan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pengajuan[] $pengajuan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pengajuan[] $pengajuanDisetujui
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pengajuan[] $pengajuanDisetujuiFinance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PengajuanHistory[] $history
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants for type safety
     */
    public const ROLE_PEGAWAI = 'pegawai';

    public const ROLE_ATASAN = 'atasan';

    public const ROLE_FINANCE = 'finance';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'departemen_id',
        'atasan_id',
        'nomor_telepon',
        'nama_bank',
        'nomor_rekening',
        'is_active',
        'password_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'password_reset_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->wasChanged('name')) {
                $oldName = $user->getOriginal('name');
                $newName = $user->name;

                \App\Models\Notifikasi::where('pesan', 'like', "%{$oldName}%")
                    ->each(function ($n) use ($oldName, $newName) {
                        $n->update(['pesan' => str_replace($oldName, $newName, $n->pesan)]);
                    });

                $pIds = $user->pengajuan()->pluck('pengajuan_id');
                if ($pIds->isNotEmpty()) {
                    \App\Models\Jurnal::whereIn('pengajuan_id', $pIds)
                        ->where('deskripsi', 'like', "%{$oldName}%")
                        ->each(function ($j) use ($oldName, $newName) {
                            $j->update(['deskripsi' => str_replace($oldName, $newName, $j->deskripsi)]);
                        });
                }
            }
        });
    }

    /**
     * Get the departemen that the user belongs to.
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'departemen_id');
    }

    /**
     * Get the user's supervisor.
     */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    /**
     * Get the user's subordinates.
     */
    public function bawahan()
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    /**
     * Get the pengajuan made by the user.
     */
    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'user_id', 'id');
    }

    /**
     * Get the pengajuan approved by this user as supervisor.
     */
    public function pengajuanDisetujui()
    {
        return $this->hasMany(Pengajuan::class, 'disetujui_atasan_oleh', 'id');
    }

    /**
     * Get the pengajuan approved by this user as finance.
     */
    public function pengajuanDisetujuiFinance()
    {
        return $this->hasMany(Pengajuan::class, 'disetujui_finance_oleh', 'id');
    }

    /**
     * Get the history of actions performed by this user.
     */
    public function history()
    {
        return $this->hasMany(PengajuanHistory::class, 'user_id', 'id');
    }

    /**
     * Role checks - Using constants for type safety
     */
    public function isPegawai(): bool
    {
        return $this->role === self::ROLE_PEGAWAI;
    }

    public function isAtasan(): bool
    {
        return $this->role === self::ROLE_ATASAN;
    }

    public function isFinance(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get role label in Indonesian
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_PEGAWAI => 'Pegawai',
            self::ROLE_ATASAN => 'Atasan',
            self::ROLE_FINANCE => 'Finance',
            default => ucfirst($this->role),
        };
    }

    /**
     * Masked phone number (e.g., 0812****5678)
     */
    public function getMaskedNomorTeleponAttribute(): ?string
    {
        if (! $this->nomor_telepon) {
            return null;
        }
        $len = strlen($this->nomor_telepon);
        if ($len <= 8) {
            return $this->nomor_telepon;
        }

        return substr($this->nomor_telepon, 0, 4).str_repeat('*', $len - 8).substr($this->nomor_telepon, -4);
    }

    /**
     * Masked bank account (e.g., 123****890)
     */
    public function getMaskedNomorRekeningAttribute(): ?string
    {
        if (! $this->nomor_rekening) {
            return null;
        }
        $len = strlen($this->nomor_rekening);
        if ($len <= 6) {
            return $this->nomor_rekening;
        }

        return substr($this->nomor_rekening, 0, 3).str_repeat('*', $len - 6).substr($this->nomor_rekening, -3);
    }

    /**
     * Check if the user's email domain is allowed
     */
    public function hasOfficialEmail(): bool
    {
        if (! config('reimbursement.security.force_official_email', true)) {
            return true;
        }

        $allowedDomains = config('reimbursement.security.allowed_domains', []);
        $emailParts = explode('@', $this->email);
        $domain = end($emailParts);

        return in_array($domain, $allowedDomains);
    }
}
