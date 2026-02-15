<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $departemen_id
 * @property string|null $kode_departemen
 * @property string $nama_departemen
 * @property float $budget_limit
 * @property string|null $deskripsi
 * @property string|null $accurate_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_sync_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pengajuan[] $pengajuan
 */
class Departemen extends Model
{
    use HasFactory;

    protected $table = 'departemen';

    protected $primaryKey = 'departemen_id';

    public $timestamps = true;

    protected $fillable = [
        'kode_departemen',
        'nama_departemen',
        'budget_limit',
        'deskripsi',
        'accurate_id',
        'is_active',
        'last_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'budget_limit' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::updated(function ($departemen) {
            if ($departemen->wasChanged('nama_departemen')) {
                $oldName = $departemen->getOriginal('nama_departemen');
                $newName = $departemen->nama_departemen;

                // Update Notifikasi
                \App\Models\Notifikasi::where('pesan', 'like', "%({$oldName})%")
                    ->each(function ($n) use ($oldName, $newName) {
                        $n->update(['pesan' => str_replace("({$oldName})", "({$newName})", $n->pesan)]);
                    });

                // Update Jurnal entries for this department
                $pIds = $departemen->pengajuan()->pluck('pengajuan_id');
                if ($pIds->isNotEmpty()) {
                    \App\Models\Jurnal::whereIn('pengajuan_id', $pIds)
                        ->where('deskripsi', 'like', "%({$oldName})%")
                        ->each(function ($j) use ($oldName, $newName) {
                            $j->update(['deskripsi' => str_replace("({$oldName})", "({$newName})", $j->deskripsi)]);
                        });
                }
            }
        });

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('departemens_list');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('departemens_list');
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'departemen_id', 'departemen_id');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'departemen_id', 'departemen_id');
    }

    public function getRouteKeyName()
    {
        return 'departemen_id';
    }
}
