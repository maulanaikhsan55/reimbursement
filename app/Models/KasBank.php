<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBank extends Model
{
    use HasFactory;

    protected $table = 'kas_bank';

    protected $primaryKey = 'kas_bank_id';

    public $timestamps = true;

    protected $fillable = [
        'kode_kas_bank',
        'nama_kas_bank',
        'is_active',
        'is_default',
        'coa_id',
        'parent_kas_bank_id',
        'accurate_id',
        'currency_code',
        'tipe_akun',
        'saldo',
        'as_of_date',
        'deskripsi',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'as_of_date' => 'date',
            'last_sync_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::updating(function ($kasBank) {
            if ($kasBank->accurate_id && ! app()->runningInConsole() && ! $kasBank->isDirty(['accurate_id', 'last_sync_at'])) {
                throw new \Exception('Data Kas/Bank yang disinkronisasi dari Accurate tidak boleh diubah secara manual.');
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('kasbanks_list');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('kasbanks_list');
        });
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'kas_bank_id', 'kas_bank_id');
    }

    public function coa()
    {
        return $this->belongsTo(COA::class, 'coa_id', 'coa_id');
    }

    public function parent()
    {
        return $this->belongsTo(KasBank::class, 'parent_kas_bank_id', 'kas_bank_id');
    }

    public function children()
    {
        return $this->hasMany(KasBank::class, 'parent_kas_bank_id', 'kas_bank_id');
    }

    public function getRouteKeyName()
    {
        return 'kas_bank_id';
    }
}
