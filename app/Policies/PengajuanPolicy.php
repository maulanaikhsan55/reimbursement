<?php

namespace App\Policies;

use App\Models\Pengajuan;
use App\Models\User;

class PengajuanPolicy
{
    public function view(User $user, Pengajuan $pengajuan): bool
    {
        // 1. Pemilik pengajuan bisa melihat
        if ($user->id === $pengajuan->user_id) {
            return true;
        }

        // 2. Atasan bisa melihat pengajuan bawahannya
        if ($user->role === 'atasan') {
            return $pengajuan->user->atasan_id === $user->id;
        }

        // 3. Finance bisa melihat semua pengajuan
        if ($user->role === 'finance') {
            return true;
        }

        return false;
    }

    public function update(User $user, Pengajuan $pengajuan): bool
    {
        return $user->id === $pengajuan->user_id && in_array($pengajuan->status->value, [\App\Enums\PengajuanStatus::VALIDASI_AI->value]);
    }

    public function delete(User $user, Pengajuan $pengajuan): bool
    {
        if ($user->id !== $pengajuan->user_id) {
            return false;
        }

        if ($user->role === 'pegawai') {
            // Pegawai bisa hapus selama belum disetujui atasan atau jika ditolak
            return in_array($pengajuan->status->value, [
                \App\Enums\PengajuanStatus::VALIDASI_AI->value,
                \App\Enums\PengajuanStatus::MENUNGGU_ATASAN->value,
                \App\Enums\PengajuanStatus::DITOLAK_ATASAN->value,
                \App\Enums\PengajuanStatus::DITOLAK_FINANCE->value,
            ]);
        } elseif ($user->role === 'atasan') {
            // Atasan bisa hapus pengajuan miliknya sendiri selama belum diproses Finance atau jika ditolak
            return in_array($pengajuan->status->value, [
                \App\Enums\PengajuanStatus::VALIDASI_AI->value,
                \App\Enums\PengajuanStatus::MENUNGGU_FINANCE->value,
                \App\Enums\PengajuanStatus::DITOLAK_FINANCE->value,
            ]);
        }

        return false;
    }
}
