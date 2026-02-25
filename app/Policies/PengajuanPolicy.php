<?php

namespace App\Policies;

use App\Enums\PengajuanStatus;
use App\Models\Pengajuan;
use App\Models\User;

class PengajuanPolicy
{
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        if ($user->isFinance()) {
            return true;
        }

        if ($user->id === (int) $pengajuan->user_id) {
            return true;
        }

        if ($user->isAtasan()) {
            return $this->isSubordinatePengajuan($user, $pengajuan)
                || (int) $pengajuan->disetujui_atasan_oleh === (int) $user->id;
        }

        return false;
    }

    public function viewProof(User $user, Pengajuan $pengajuan): bool
    {
        return $this->view($user, $pengajuan);
    }

    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return $user->id === (int) $pengajuan->user_id
            && $pengajuan->status?->value === PengajuanStatus::VALIDASI_AI->value;
    }

    public function delete(User $user, Pengajuan $pengajuan): bool
    {
        if ($user->id !== (int) $pengajuan->user_id) {
            return false;
        }

        if ($user->isPegawai()) {
            return in_array($pengajuan->status?->value, [
                PengajuanStatus::VALIDASI_AI->value,
                PengajuanStatus::MENUNGGU_ATASAN->value,
                PengajuanStatus::DITOLAK_ATASAN->value,
                PengajuanStatus::DITOLAK_FINANCE->value,
            ]);
        }

        if ($user->isAtasan()) {
            return in_array($pengajuan->status?->value, [
                PengajuanStatus::VALIDASI_AI->value,
                PengajuanStatus::MENUNGGU_FINANCE->value,
                PengajuanStatus::DITOLAK_FINANCE->value,
            ]);
        }

        return false;
    }

    public function approveByAtasan(User $user, Pengajuan $pengajuan): bool
    {
        return $user->isAtasan()
            && $this->isSubordinatePengajuan($user, $pengajuan)
            && $pengajuan->status?->value === PengajuanStatus::MENUNGGU_ATASAN->value;
    }

    public function rejectByAtasan(User $user, Pengajuan $pengajuan): bool
    {
        return $this->approveByAtasan($user, $pengajuan);
    }

    public function reviewByFinance(User $user, Pengajuan $pengajuan): bool
    {
        if (! $user->isFinance()) {
            return false;
        }

        return in_array($pengajuan->status?->value, [
            PengajuanStatus::MENUNGGU_FINANCE->value,
            PengajuanStatus::TERKIRIM_ACCURATE->value,
            PengajuanStatus::DICAIRKAN->value,
            PengajuanStatus::SELESAI->value,
            PengajuanStatus::DITOLAK_FINANCE->value,
        ], true);
    }

    public function sendToAccurate(User $user, Pengajuan $pengajuan): bool
    {
        return $user->isFinance()
            && $pengajuan->status?->value === PengajuanStatus::MENUNGGU_FINANCE->value;
    }

    public function rejectByFinance(User $user, Pengajuan $pengajuan): bool
    {
        return $this->sendToAccurate($user, $pengajuan);
    }

    public function markDisbursed(User $user, Pengajuan $pengajuan): bool
    {
        return $user->isFinance()
            && $pengajuan->status?->value === PengajuanStatus::TERKIRIM_ACCURATE->value;
    }

    private function isSubordinatePengajuan(User $user, Pengajuan $pengajuan): bool
    {
        $atasanId = $pengajuan->relationLoaded('user')
            ? $pengajuan->user?->atasan_id
            : $pengajuan->user()->value('atasan_id');

        return (int) $atasanId === (int) $user->id;
    }
}
