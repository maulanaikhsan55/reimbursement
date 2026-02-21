<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\User;

class NotifikasiService
{
    /**
     * Clear sidebar notification and approval caches
     */
    protected function clearSidebarCache(int $userId): void
    {
        \Illuminate\Support\Facades\Cache::forget('notif_unread_count_user_'.$userId);
        \Illuminate\Support\Facades\Cache::forget('unread_notif_count_'.$userId);
        \Illuminate\Support\Facades\Cache::forget('pending_approvals_count_'.$userId);
    }

    public function notifyApprovedByAtasan(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pengajuan->load('approvedByAtasan');
        $atasan = $pengajuan->approvedByAtasan;

        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah disetujui oleh ".($atasan->name ?? 'Atasan');
        $judul = 'Pengajuan Disetujui';

        Notifikasi::create([
            'user_id' => $pegawai->id,
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'tipe' => 'disetujui_atasan',
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($pegawai->id);
        if ($atasan) {
            $this->clearSidebarCache($atasan->id);
        }
    }

    public function notifyRejectedByAtasan(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah ditolak. Alasan: {$pengajuan->catatan_atasan}";
        $judul = 'Pengajuan Ditolak';

        Notifikasi::create([
            'user_id' => $pegawai->id,
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'tipe' => 'ditolak_atasan',
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($pegawai->id);
        if ($pengajuan->user->atasan_id) {
            $this->clearSidebarCache($pengajuan->user->atasan_id);
        }
    }

    public function notifyRejectedByFinance(Pengajuan $pengajuan): void
    {
        $pegawai = $pengajuan->user;
        $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} telah ditolak oleh Finance. Alasan: {$pengajuan->catatan_finance}";
        $judul = 'Pengajuan Ditolak Finance';

        Notifikasi::create([
            'user_id' => $pegawai->id,
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'tipe' => 'ditolak_finance',
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($pegawai->id);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = "Pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah ditolak oleh Finance. Alasan: {$pengajuan->catatan_finance}";
                Notifikasi::create([
                    'user_id' => $atasan->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'ditolak_finance',
                    'judul' => $judul,
                    'pesan' => $pesanAtasan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($atasan->id);
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

                Notifikasi::create([
                    'user_id' => $atasan->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'pengajuan_baru',
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($atasan->id);
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

            $financeUsers = User::where('role', 'finance')
                ->where('is_active', true)
                ->get();

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
                Notifikasi::create([
                    'user_id' => $finance->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'pengajuan_baru',
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($finance->id);
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

        Notifikasi::create([
            'user_id' => $pengajuan->user_id,
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'tipe' => 'terkirim_accurate',
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($pengajuan->user_id);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = "Pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah disetujui oleh Finance dan diproses ke sistem akuntansi.";
                Notifikasi::create([
                    'user_id' => $atasan->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'terkirim_accurate',
                    'judul' => $judul,
                    'pesan' => $pesanAtasan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($atasan->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to notify atasan about accurate approval: '.$e->getMessage());
        }
    }

    public function notifyFailedToAccurate(Pengajuan $pengajuan, string $errorMessage): void
    {
        try {
            $financeUsers = User::where('role', 'finance')
                ->where('is_active', true)
                ->get();

            $pesan = "Pengajuan #{$pengajuan->nomor_pengajuan} gagal dikirim ke Accurate. Error: {$errorMessage}. Silakan coba lagi.";
            $judul = 'Gagal Kirim ke Accurate';

            foreach ($financeUsers as $finance) {
                Notifikasi::create([
                    'user_id' => $finance->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'ditolak_finance',
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($finance->id);
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

        Notifikasi::create([
            'user_id' => $pengajuan->user_id,
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'tipe' => 'dicairkan',
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
        ]);

        $this->clearSidebarCache($pengajuan->user_id);

        try {
            $pengajuan->load('user.atasan');
            $atasan = $pengajuan->user->atasan;

            if ($atasan && $atasan->is_active) {
                $pesanAtasan = 'Reimbursement Rp '.number_format((float) $pengajuan->nominal, 0, ',', '.')." untuk pengajuan #{$pengajuan->nomor_pengajuan} dari {$pengajuan->user->name} telah dicairkan pada ".($pengajuan->tanggal_pencairan ? \Carbon\Carbon::parse($pengajuan->tanggal_pencairan)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y'));
                Notifikasi::create([
                    'user_id' => $atasan->id,
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'tipe' => 'dicairkan',
                    'judul' => $judul,
                    'pesan' => $pesanAtasan,
                    'is_read' => false,
                ]);

                $this->clearSidebarCache($atasan->id);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to notify atasan about disbursement: '.$e->getMessage());
        }
    }
}
