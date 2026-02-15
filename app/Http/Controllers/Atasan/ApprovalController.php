<?php

namespace App\Http\Controllers\Atasan;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\User;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Traits\FiltersPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    use FiltersPengajuan;

    public function __construct(
        protected NotifikasiService $notifikasiService,
        protected ReportExportService $exportService
    ) {}

    public function index(Request $request)
    {
        // Default filter: Hanya tampilkan yang 'menunggu_atasan' jika tidak ada filter status
        if (! $request->has('status')) {
            $request->merge(['status' => PengajuanStatus::MENUNGGU_ATASAN->value]);
        }

        $query = $this->applyApprovalFilters(Pengajuan::query(), $request);

        $pengajuanList = $query->with([
            'user:id,name',
            'kategori:kategori_id,nama_kategori',
            'departemen:departemen_id,nama_departemen',
            'validasiAi',
        ])->paginate(config('app.pagination.approval'));

        $user = Auth::user();
        $subordinateIds = User::where('atasan_id', $user->id)->pluck('id')->toArray();
        $stats = Pengajuan::getTeamStats($user->id, $subordinateIds);
        $slaDays = 3;

        $currentStatus = $request->input('status');

        if ($request->ajax()) {
            return response()->json([
                'table' => view('dashboard.atasan.approval.partials._table', compact('pengajuanList'))->render(),
                'stats' => view('dashboard.atasan.approval.partials._stats', compact('pengajuanList', 'currentStatus', 'stats', 'slaDays'))->render(),
            ]);
        }

        return view('dashboard.atasan.approval.index', compact('pengajuanList', 'currentStatus', 'stats', 'slaDays'));
    }

    public function getCount()
    {
        $subordinateIds = User::where('atasan_id', auth()->id())->pluck('id')->toArray();
        $count = Pengajuan::where('status', PengajuanStatus::MENUNGGU_ATASAN)
            ->whereIn('user_id', $subordinateIds)
            ->count();

        return response()->json(['pending_count' => $count]);
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyApprovalFilters(Pengajuan::query(), $request);
        $pengajuanList = $query->with(['user', 'kategori', 'departemen'])->get();
        $user = Auth::user();

        return $this->exportService->exportToPDF(
            'laporan_persetujuan_'.date('Y-m-d_His').'.pdf',
            'dashboard.atasan.approval.pdf.laporan-pengajuan',
            [
                'pengajuanList' => $pengajuanList,
                'user' => $user->load('departemen'),
                'title' => 'Laporan Persetujuan Pengajuan',
            ]
        );
    }

    public function exportCsv(Request $request)
    {
        $query = $this->applyApprovalFilters(Pengajuan::query(), $request);
        $pengajuanList = $query->with(['user', 'kategori', 'departemen'])->get();

        $headers = $this->getPengajuanCsvHeaders('approval');
        $data = $this->mapPengajuanForCsv($pengajuanList, 'approval');

        return $this->exportService->exportToCSV(
            'laporan_persetujuan_'.date('Y-m-d_His').'.csv',
            $headers,
            $data
        );
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);

        $user = Auth::user();
        if ($pengajuan->user->atasan_id !== $user->id) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $pengajuan->load('validasiAi', 'departemen');

        // Calculate budget status for departemen using centralized model method
        $budgetData = Pengajuan::getBudgetStatus(
            $pengajuan->departemen_id,
            $pengajuan->nominal,
            $pengajuan->tanggal_transaksi->month,
            $pengajuan->tanggal_transaksi->year,
            $pengajuan->pengajuan_id
        );

        return view('dashboard.atasan.approval.show', compact('pengajuan', 'budgetData'));
    }

    public function approve(Request $request, Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Pengajuan ini tidak sedang menunggu persetujuan Anda.');
        }

        $user = Auth::user();
        if ($pengajuan->user->atasan_id !== $user->id) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Anda tidak memiliki akses untuk menyetujui pengajuan ini.');
        }

        $validated = $request->validate([
            'catatan_atasan' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($pengajuan, $user, $validated) {
                $pengajuan->update([
                    'status' => PengajuanStatus::MENUNGGU_FINANCE,
                    'disetujui_atasan_oleh' => $user->id,
                    'tanggal_disetujui_atasan' => now(),
                    'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                ]);

                $this->notifikasiService->notifyApprovedByAtasan($pengajuan);
                $this->notifikasiService->notifyNewPengajuanToFinance($pengajuan);
            });

            return redirect()->route('atasan.approval.index')
                ->with('success', 'Pengajuan berhasil disetujui dan dikirim ke Finance.');
        } catch (\Exception $e) {
            \Log::error('Approval failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses persetujuan. Silakan coba lagi.');
        }
    }

    public function reject(Request $request, Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Pengajuan ini tidak sedang menunggu persetujuan Anda.');
        }

        $user = Auth::user();
        if ($pengajuan->user->atasan_id !== $user->id) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Anda tidak memiliki akses untuk menolak pengajuan ini.');
        }

        $validated = $request->validate([
            'catatan_atasan' => 'required|string|min:10',
        ]);

        try {
            DB::transaction(function () use ($pengajuan, $validated) {
                $pengajuan->update([
                    'status' => PengajuanStatus::DITOLAK_ATASAN,
                    'catatan_atasan' => $validated['catatan_atasan'],
                ]);

                $this->notifikasiService->notifyRejectedByAtasan($pengajuan);
            });

            return redirect()->route('atasan.approval.index')
                ->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\Exception $e) {
            \Log::error('Rejection failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menolak pengajuan. Silakan coba lagi.');
        }
    }
}
