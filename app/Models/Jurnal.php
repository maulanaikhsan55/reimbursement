<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'jurnal';

    protected $primaryKey = 'jurnal_id';

    public $timestamps = true;

    protected $fillable = [
        'pengajuan_id',
        'coa_id',
        'nominal',
        'tipe_posting',
        'tanggal_posting',
        'nomor_ref',
        'deskripsi',
        'posted_at',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal_posting' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function coa()
    {
        return $this->belongsTo(COA::class, 'coa_id', 'coa_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by', 'id');
    }
}
