<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Serve private reimbursement proof files with authorization
     */
    public function showProof(Pengajuan $pengajuan): StreamedResponse
    {
        $user = Auth::user();

        // 1. Owner can always see their own pengajuan
        if ($pengajuan->user_id === $user->id) {
            $canAccess = true;
        }
        // 2. Finance can see all pengajuan
        elseif ($user->role === 'finance') {
            $canAccess = true;
        }
        // 3. Atasan can see their subordinates' pengajuan
        elseif ($user->role === 'atasan') {
            if ($pengajuan->user->atasan_id === $user->id || $pengajuan->disetujui_atasan_oleh === $user->id) {
                $canAccess = true;
            } else {
                $canAccess = false;
            }
        } else {
            $canAccess = false;
        }

        if (! $canAccess) {
            abort(403, 'Anda tidak memiliki akses ke berkas ini.');
        }

        if (! Storage::disk('local')->exists($pengajuan->file_bukti)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk('local')->download(
            $pengajuan->file_bukti,
            basename($pengajuan->file_bukti),
            [
                'Content-Type' => Storage::disk('local')->mimeType($pengajuan->file_bukti),
                'Content-Disposition' => 'inline; filename="'.basename($pengajuan->file_bukti).'"',
            ]
        );
    }
}
