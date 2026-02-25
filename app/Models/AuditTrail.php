<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'audit_trails';

    protected $fillable = [
        'actor_id',
        'actor_role',
        'event',
        'auditable_type',
        'auditable_id',
        'pengajuan_id',
        'description',
        'context',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id', 'pengajuan_id');
    }
}
