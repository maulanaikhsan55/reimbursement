<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogTransaksiAccurate extends Model
{
    use HasFactory;

    protected $table = 'log_transaksi_accurate';

    protected $primaryKey = 'log_id';

    public $timestamps = true;

    protected $fillable = [
        'pengajuan_id',
        'request_payload',
        'response_payload',
        'status',
        'accurate_transaction_id',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'json',
            'response_payload' => 'json',
            'sent_at' => 'datetime',
        ];
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }
}
