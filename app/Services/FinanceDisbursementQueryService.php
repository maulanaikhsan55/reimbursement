<?php

namespace App\Services;

use App\Enums\PengajuanStatus;
use App\Models\Pengajuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FinanceDisbursementQueryService
{
    public function pending(Request $request, ?Builder $query = null): Builder
    {
        $query ??= Pengajuan::query();
        $defaultStatus = PengajuanStatus::TERKIRIM_ACCURATE->value;

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('pengajuan.status', $request->input('status'));
        } elseif (! $request->filled('status')) {
            $query->where('pengajuan.status', $defaultStatus);
        }

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
            $query->where('pengajuan.tanggal_pengajuan', '>=', $request->input($startDateParam));
        }

        if ($request->filled($endDateParam)) {
            $query->where('pengajuan.tanggal_pengajuan', '<=', $request->input($endDateParam));
        }

        return $query->orderByRaw('CASE WHEN pengajuan.status = ? THEN 0 ELSE 1 END', [$defaultStatus])
            ->orderBy('pengajuan.tanggal_pengajuan', 'desc');
    }

    public function history(Request $request, ?Builder $query = null): Builder
    {
        $query ??= Pengajuan::query();
        $query->whereIn('pengajuan.status', [PengajuanStatus::DICAIRKAN->value, PengajuanStatus::SELESAI->value]);

        if ($request->filled('start_date') || $request->filled('tanggal_from')) {
            $date = $request->input('start_date') ?? $request->input('tanggal_from');
            $query->whereDate('pengajuan.tanggal_pencairan', '>=', $date);
        }

        if ($request->filled('end_date') || $request->filled('tanggal_to')) {
            $date = $request->input('end_date') ?? $request->input('tanggal_to');
            $query->whereDate('pengajuan.tanggal_pencairan', '<=', $date);
        }

        if ($request->filled('departemen_id')) {
            $query->where('pengajuan.departemen_id', $request->input('departemen_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->join('users', 'pengajuan.user_id', '=', 'users.id')
                ->select('pengajuan.*')
                ->where(function ($q) use ($search) {
                    $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
        }

        return $query->orderBy('pengajuan.tanggal_pencairan', 'desc');
    }
}

