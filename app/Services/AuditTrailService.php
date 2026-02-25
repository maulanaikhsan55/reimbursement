<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditTrailService
{
    public function log(
        string $event,
        ?Authenticatable $actor = null,
        ?Model $auditable = null,
        ?Pengajuan $pengajuan = null,
        ?string $description = null,
        array $context = []
    ): void {
        try {
            $request = app()->bound('request') ? app(Request::class) : null;
            $actorUser = $actor instanceof User ? $actor : null;

            AuditTrail::create([
                'actor_id' => $actorUser?->id,
                'actor_role' => $actorUser?->role,
                'event' => $event,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'pengajuan_id' => $pengajuan?->pengajuan_id,
                'description' => $description,
                'context' => $context,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Audit trail write failed: '.$e->getMessage(), [
                'event' => $event,
            ]);
        }
    }

    public function logPengajuan(
        string $event,
        Pengajuan $pengajuan,
        ?Authenticatable $actor = null,
        ?string $description = null,
        array $context = []
    ): void {
        $this->log(
            event: $event,
            actor: $actor,
            auditable: $pengajuan,
            pengajuan: $pengajuan,
            description: $description,
            context: $context
        );
    }
}
