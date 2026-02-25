<?php

namespace App\Services;

use App\Jobs\CreateUserNotificationJob;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class NotifikasiService
{
    /**
     * Decide whether notification writes should go through queue.
     * Default is sync to keep UX reliable in local/dev without queue workers.
     */
    protected function shouldQueueNotifications(): bool
    {
        $delivery = strtolower((string) config('reimbursement.notifications.delivery', 'sync'));
        $queueDefault = strtolower((string) config('queue.default', 'sync'));

        return $delivery === 'queue' && $queueDefault !== 'sync';
    }

    /**
     * Clear sidebar notification and approval caches
     */
    protected function clearSidebarCache(int $userId): void
    {
        Notifikasi::flushUnreadCaches($userId);
        Cache::forget($this->pendingApprovalsCacheKey($userId));
    }

    protected function pendingApprovalsCacheKey(int $userId): string
    {
        return 'pending_approvals_count_'.$userId;
    }

    /**
     * Queue notification creation so write spikes do not block request lifecycle.
     */
    protected function queueNotification(int $userId, ?int $pengajuanId, string $tipe, string $judul, string $pesan): void
    {
        CreateUserNotificationJob::dispatch($userId, $pengajuanId, $tipe, $judul, $pesan);
    }

    public function notifyUserImmediate(int $userId, ?int $pengajuanId, string $tipe, string $judul, string $pesan): void
    {
        Notifikasi::create([
            'user_id' => $userId,
            'pengajuan_id' => $pengajuanId,
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($userId);
    }

    protected function notifyUser(int $userId, ?int $pengajuanId, string $tipe, string $judul, string $pesan): void
    {
        if (! $this->shouldQueueNotifications()) {
            $this->notifyUserImmediate($userId, $pengajuanId, $tipe, $judul, $pesan);

            return;
        }

        try {
            $this->queueNotification($userId, $pengajuanId, $tipe, $judul, $pesan);
            $this->clearSidebarCache($userId);
        } catch (\Throwable $e) {
            \Log::warning('Notifikasi queue gagal, fallback ke sync', [
                'user_id' => $userId,
                'pengajuan_id' => $pengajuanId,
                'tipe' => $tipe,
                'error' => $e->getMessage(),
            ]);

            $this->notifyUserImmediate($userId, $pengajuanId, $tipe, $judul, $pesan);
        }
    }

    protected function getActiveFinanceUsers(): Collection
    {
        return User::where('role', 'finance')
            ->where('is_active', true)
            ->get();
    }

    public function notifyApprovedByAtasan(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pengajuan->load('approvedByAtasan');
        $atasan = $pengajuan->approvedByAtasan;

        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah disetujui oleh ".($atasan->name ?? 'Atasan');
        $judul = 'Pengajuan Disetujui';

        $this->notifyUser($pegawai->id, $pengajuan->pengajuan_id, 'disetujui_atasan', $judul, $pesan);
        if ($atasan) {
            $this->clearSidebarCache($atasan->id);
        }
    }

    public function notifyRejectedByAtasan(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah ditolak. Alasan: {$pengajuan->catatan_atasan}";
        $judul = 'Pengajuan Ditolak';

        $this->notifyUser($pegawai->id, $pengajuan->pengajuan_id, 'ditolak_atasan', $judul, $pesan);
        if ($pengajuan->user->atasan_id) {
            $this->clearSidebarCache($pengajuan->user->atasan_id);
        }
    }

    public function notifyRejectedByFinance(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah ditolak oleh Finance. Alasan: {$pengajuan->catatan_finance}";
        $judul = 'Pengajuan Ditolak Finance';

        $this->notifyUser($pegawai->id, $pengajuan->pengajuan_id, 'ditolak_finance', $judul, $pesan);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = "Pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah ditolak oleh Finance. Alasan: {$pengajuan->catatan_finance}";
                $this->notifyUser($atasan->id, $pengajuan->pengajuan_id, 'ditolak_finance', $judul, $pesanAtasan);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to notify atasan about finance rejection: '.$e->getMessage());
        }
    }

    public function notifyNewPengajuanToAtasan(Pengajuan $pengajuan): void
    {
        try {
            // Load user and atasan relationship
            $pengajuan->load('user.atasan', 'departemen');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesan = "Ada pengajuan baru dari {$pengajuan->user->name} ({$pengajuan->departemen->nama_departemen}) sebesar Rp ".number_format((float) $pengajuan->nominal, 0, ',', '.');
                $judul = 'Pengajuan Baru untuk Persetujuan';

                $this->notifyUser($atasan->id, $pengajuan->pengajuan_id, 'pengajuan_baru', $judul, $pesan);
            } else {
                \Log::warning('NotifikasiService: Atasan not found or inactive', [
                    'user_id' => $pengajuan->user_id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('NotifikasiService: Failed to notify atasan', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyNewPengajuanToFinance(Pengajuan $pengajuan): void
    {
        try {
            $pengajuan->load('user', 'departemen');

            $financeUsers = $this->getActiveFinanceUsers();

            if ($financeUsers->isEmpty()) {
                \Log::warning('NotifikasiService: No active finance users found', [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                ]);

                return;
            }

            $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} ({$pengajuan->departemen->nama_departemen}) sebesar Rp ".number_format((float) $pengajuan->nominal, 0, ',', '.').' menunggu review';
            $judul = 'Pengajuan Baru dari Atasan';

            foreach ($financeUsers as $finance) {
                \Log::info('Notifying finance user: '.$finance->id.' for pengajuan: '.$pengajuan->pengajuan_id);
                $this->notifyUser($finance->id, $pengajuan->pengajuan_id, 'pengajuan_baru', $judul, $pesan);
            }
        } catch (\Exception $e) {
            \Log::error('NotifikasiService: Failed to notify finance users', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifySentToAccurate(Pengajuan $pengajuan): void
    {
        $jvNumber = $pengajuan->accurate_transaction_id ? " ({$pengajuan->accurate_transaction_id})" : '';
        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah disetujui oleh Finance and diproses ke Accurate Online dengan nomor jurnal {$jvNumber}.";
        $judul = 'Disetujui Finance';

        $this->notifyUser($pengajuan->user_id, $pengajuan->pengajuan_id, 'terkirim_accurate', $judul, $pesan);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = "Pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah disetujui oleh Finance dan diproses ke sistem akuntansi.";
                $this->notifyUser($atasan->id, $pengajuan->pengajuan_id, 'terkirim_accurate', $judul, $pesanAtasan);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to notify atasan about accurate approval: '.$e->getMessage());
        }
    }

    public function notifyFailedToAccurate(Pengajuan $pengajuan, string $errorMessage): void
    {
        try {
            $financeUsers = $this->getActiveFinanceUsers();

            $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} gagal dikirim ke Accurate. Error: {$errorMessage}. Silakan coba lagi.";
            $judul = 'Gagal Kirim ke Accurate';

            foreach ($financeUsers as $finance) {
                $this->notifyUser($finance->id, $pengajuan->pengajuan_id, 'ditolak_finance', $judul, $pesan);
            }
        } catch (\Exception $e) {
            \Log::error('NotifikasiService: Failed to notify finance about Accurate error', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyDisbursed(Pengajuan $pengajuan): void
    {
        $jvNumber = $pengajuan->accurate_transaction_id ? " (Ref: {$pengajuan->accurate_transaction_id})" : '';
        $pesan = 'Reimbursement Rp '.number_format((float) $pengajuan->nominal, 0, ',', '.')." untuk pengajuan #{$pengajuan->nomor_pengajuan}{$jvNumber} telah ditransfer ke rekening Anda pada ".($pengajuan->tanggal_pencairan ? \Carbon\Carbon::parse($pengajuan->tanggal_pencairan)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y'));
        $judul = 'Dana Dicairkan';

        $this->notifyUser($pengajuan->user_id, $pengajuan->pengajuan_id, 'dicairkan', $judul, $pesan);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = 'Reimbursement Rp '.number_format((float) $pengajuan->nominal, 0, ',', '.')." untuk pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah dicairkan pada ".($pengajuan->tanggal_pencairan ? \Carbon\Carbon::parse($pengajuan->tanggal_pencairan)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y'));
                $this->notifyUser($atasan->id, $pengajuan->pengajuan_id, 'dicairkan', $judul, $pesanAtasan);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to notify atasan about disbursement: '.$e->getMessage());
        }
    }
}
