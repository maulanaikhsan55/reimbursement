<?php

namespace App\Traits;

use App\Enums\PengajuanStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait FiltersPengajuan
{
    protected function applyBasePengajuanFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('pengajuan.nama_vendor', 'like', "%{$search}%")
                    ->orWhere('pengajuan.deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tanggal_from')) {
            $query->where('pengajuan.tanggal_pengajuan', '>=', $request->input('tanggal_from'));
        }

        if ($request->filled('tanggal_to')) {
            $query->where('pengajuan.tanggal_pengajuan', '<=', $request->input('tanggal_to'));
        }

        return $query;
    }

    protected function applyPersonalPengajuanFilters($query, Request $request)
    {
        $userId = Auth::id();
        $query->where('pengajuan.user_id', $userId);

        $this->applyBasePengajuanFilters($query, $request);

        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('pengajuan.status', $request->input('status'));
        }

        return $query->orderBy('pengajuan.tanggal_pengajuan', 'desc');
    }

    protected function applyApprovalFilters($query, Request $request)
    {
        $user = Auth::user();

        // Optimize: Use whereIn instead of whereHas
        $subordinateIds = User::where('atasan_id', $user->id)->pluck('id')->toArray();
        $query->whereIn('pengajuan.user_id', $subordinateIds);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('pengajuan.status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');

            // Join users table for faster search
            $query->leftJoin('users', 'pengajuan.user_id', '=', 'users.id')
                ->select('pengajuan.*') // Avoid column name collisions
                ->where(function ($q) use ($search) {
                    $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                        ->orWhere('pengajuan.nama_vendor', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
        }

        if ($request->filled('tanggal_from')) {
            $query->where('pengajuan.tanggal_pengajuan', '>=', $request->input('tanggal_from'));
        }

        if ($request->filled('tanggal_to')) {
            $query->where('pengajuan.tanggal_pengajuan', '<=', $request->input('tanggal_to'));
        }

        return $query->orderByRaw('CASE WHEN pengajuan.status = ? THEN 0 ELSE 1 END', [PengajuanStatus::MENUNGGU_ATASAN->value])
            ->orderBy('pengajuan.tanggal_pengajuan', 'desc');
    }

    protected function applyFinanceFilters($query, Request $request, $defaultStatus = null)
    {
        $defaultStatus ??= PengajuanStatus::MENUNGGU_FINANCE->value;
        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('pengajuan.status', $request->input('status'));
        } elseif (! $request->filled('status')) {
            $query->where('pengajuan.status', $defaultStatus);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');

            // Join users table for faster search - using leftJoin to be safe
            $query->leftJoin('users', 'pengajuan.user_id', '=', 'users.id')
                ->select('pengajuan.*')
                ->where(function ($q) use ($search) {
                    $q->where('pengajuan.nomor_pengajuan', 'like', "%{$search}%")
                        ->orWhere('pengajuan.nama_vendor', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
        }

        // Department Filter
        if ($request->filled('departemen_id')) {
            $query->where('pengajuan.departemen_id', $request->input('departemen_id'));
        }

        // Date Filter - Support both parameter names
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

    protected function applyHistoryFilters($query, Request $request)
    {
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

    protected function getPengajuanCsvHeaders($mode = 'personal')
    {
        $headers = ['Nomor Pengajuan'];

        if ($mode === 'finance' || $mode === 'approval') {
            $headers[] = 'Staff';
            if ($mode === 'finance') {
                $headers[] = 'Email';
                $headers[] = 'Departemen';
            }
        }

        $headers = array_merge($headers, ['Vendor', 'Tanggal', 'Kategori Biaya', 'Deskripsi', 'Nominal', 'Status']);

        if ($mode === 'history') {
            $headers[] = 'Tanggal Pencairan';
        }

        return $headers;
    }

    protected function mapPengajuanForCsv($pengajuanList, $mode = 'personal')
    {
        return $pengajuanList->map(function ($item) use ($mode) {
            $row = [$item->nomor_pengajuan];

            if ($mode === 'finance' || $mode === 'approval') {
                $row[] = $item->user->name;
                if ($mode === 'finance') {
                    $row[] = $item->user->email;
                    $row[] = $item->departemen->nama_departemen ?? '';
                }
            }

            $row[] = $item->nama_vendor;
            $row[] = $item->tanggal_pengajuan->format('d/m/Y');
            $row[] = $item->kategori ? $item->kategori->nama_kategori : '';
            $row[] = $item->deskripsi;
            $row[] = $item->nominal;
            $row[] = $item->status->label();

            if ($mode === 'history') {
                $row[] = $item->tanggal_pencairan ? $item->tanggal_pencairan->format('d/m/Y') : '-';
            }

            return $row;
        });
    }
}
