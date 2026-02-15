<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanHistory extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_history';

    protected $primaryKey = 'history_id';

    public $timestamps = true;

    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'status_from',
        'status_to',
        'action',
        'catatan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
