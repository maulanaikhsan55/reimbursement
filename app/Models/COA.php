<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COA extends Model
{
    use HasFactory;

    protected $table = 'coa';

    protected $primaryKey = 'coa_id';

    public $timestamps = true;

    protected $fillable = [
        'kode_coa',
        'nama_coa',
        'tipe_akun',
        'parent_coa_id',
        'is_active',
        'synced_from_accurate',
        'accurate_id',
        'currency_code',
        'saldo',
        'as_of_date',
        'deskripsi',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_from_accurate' => 'boolean',
            'as_of_date' => 'date',
            'last_sync_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::updating(function ($coa) {
            // Izinkan perubahan jika:
            // 1. Sedang running di Console (Command Sync)
            // 2. accurate_id sedang di-assign (mapping awal)
            // 3. last_sync_at sedang diperbarui (berarti ini proses sinkronisasi/rekonsiliasi resmi)
            if ($coa->accurate_id && ! app()->runningInConsole() && ! $coa->isDirty(['accurate_id', 'last_sync_at'])) {
                throw new \Exception('Data COA yang disinkronisasi dari Accurate tidak boleh diubah secara manual.');
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('coas_list');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('coas_list');
        });
    }

    public function parent()
    {
        return $this->belongsTo(COA::class, 'parent_coa_id', 'coa_id');
    }

    public function children()
    {
        return $this->hasMany(COA::class, 'parent_coa_id', 'coa_id');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'coa_id', 'coa_id');
    }

    public function kasBank()
    {
        return $this->hasMany(KasBank::class, 'coa_id', 'coa_id');
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class, 'coa_id', 'coa_id');
    }

    public function getRouteKeyName()
    {
        return 'coa_id';
    }
}
