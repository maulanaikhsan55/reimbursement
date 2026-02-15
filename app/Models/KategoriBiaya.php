<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBiaya extends Model
{
    use HasFactory;

    protected $table = 'kategori_biaya';

    protected $primaryKey = 'kategori_id';

    public $timestamps = true;

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'default_coa_id',
        'deskripsi',
        'is_active',
    ];

    public function defaultCoa()
    {
        return $this->belongsTo(COA::class, 'default_coa_id', 'coa_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'kategori_id', 'kategori_id');
    }

    public function getRouteKeyName()
    {
        return 'kategori_id';
    }
}
