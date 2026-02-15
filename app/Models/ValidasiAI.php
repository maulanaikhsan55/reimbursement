<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidasiAI extends Model
{
    protected $table = 'validasi_ai';

    protected $primaryKey = 'validasi_id';

    public $timestamps = true;

    protected $fillable = [
        'pengajuan_id',
        'jenis_validasi',
        'status',
        'confidence_score',
        'hasil_ocr',
        'pesan_validasi',
        'is_blocking',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'hasil_ocr' => 'json',
            'is_blocking' => 'boolean',
            'validated_at' => 'datetime',
            'status' => \App\Enums\ValidationStatus::class,
        ];
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }
}
