<?php

namespace App\Services;

use App\Enums\PengajuanStatus;
use App\Models\Pengajuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceApprovalQueryService
{
    public function pending(Request $request, ?Builder $query = null): Builder
    {
        $query ??= Pengajuan::query();
        $query->where('pengajuan.status', PengajuanStatus::MENUNGGU_FINANCE->value);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->leftJoin('users', 'pengajuan.user_id', '=', 'users.id')
                ->select('pengajuan.*')
                ->where(function ($q) use ($search) {
                    $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                        ->orWhere('pengajuan.nama_vendor', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
        }

        if ($request->filled('departemen_id')) {
            $query->where('pengajuan.departemen_id', $request->input('departemen_id'));
        }

        $startDateParam = $request->filled('start_date') ? 'start_date' : 'tanggal_from';
        $endDateParam = $request->filled('end_date') ? 'end_date' : 'tanggal_to';

        if ($request->filled($startDateParam)) {
            $query->whereDate('pengajuan.tanggal_pengajuan', '>=', $request->input($startDateParam));
        }

        if ($request->filled($endDateParam)) {
            $query->whereDate('pengajuan.tanggal_pengajuan', '<=', $request->input($endDateParam));
        }

        $query->orderBy('pengajuan.tanggal_pengajuan', 'desc');

        return $query;
    }

    public function history(Request $request, ?Builder $query = null): Builder
    {
        $query ??= Pengajuan::query();
        $query->whereIn('pengajuan.status', [
            PengajuanStatus::TERKIRIM_ACCURATE->value,
            PengajuanStatus::DICAIRKAN->value,
            PengajuanStatus::SELESAI->value,
            PengajuanStatus::DITOLAK_FINANCE->value,
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->join('users', 'pengajuan.user_id', '=', 'users.id')
                ->select('pengajuan.*')
                ->where(function ($q) use ($search) {
                    $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                        ->orWhere('pengajuan.nama_vendor', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
        }

        if ($request->filled('departemen_id')) {
            $query->where('pengajuan.departemen_id', $request->input('departemen_id'));
        }

        $startDateParam = $request->filled('start_date') ? 'start_date' : 'tanggal_from';
        $endDateParam = $request->filled('end_date') ? 'end_date' : 'tanggal_to';

        if ($request->filled($startDateParam)) {
            $query->whereDate(DB::raw('COALESCE(pengajuan.tanggal_disetujui_finance, pengajuan.created_at)'), '>=', $request->input($startDateParam));
        }

        if ($request->filled($endDateParam)) {
            $query->whereDate(DB::raw('COALESCE(pengajuan.tanggal_disetujui_finance, pengajuan.created_at)'), '<=', $request->input($endDateParam));
        }

        $query->orderByRaw('COALESCE(pengajuan.tanggal_disetujui_finance, pengajuan.created_at) DESC');

        return $query;
    }
}
