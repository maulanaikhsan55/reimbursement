<?php

namespace App\Http\Controllers\Atasan;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atasan\ApprovePengajuanRequest;
use App\Http\Requests\Atasan\RejectPengajuanRequest;
use App\Models\Pengajuan;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Traits\FiltersPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    use FiltersPengajuan;

    public function __construct(
        protected NotifikasiService $notifikasiService,
        protected AuditTrailService $auditTrailService,
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
            'validasiAi' => function ($validasiQuery) {
                $validasiQuery
                    ->select('validasi_id', 'pengajuan_id', 'jenis_validasi', 'status')
                    ->where('jenis_validasi', 'ocr');
            },
        ])->paginate(config('app.pagination.approval'))
            ->withQueryString();

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
        $userId = auth()->id();
        $cacheKey = 'atasan_approval_pending_count_user_'.$userId;

        $count = Cache::flexible($cacheKey, [5, 15], function () use ($userId, $cacheKey) {
            return Cache::lock($cacheKey.'_lock', 5)->block(2, function () use ($userId) {
                $subordinateIds = User::where('atasan_id', $userId)->pluck('id');

                if ($subordinateIds->isEmpty()) {
                    return 0;
                }

                return Pengajuan::where('status', PengajuanStatus::MENUNGGU_ATASAN)
                    ->whereIn('user_id', $subordinateIds)
                    ->count();
            });
        });

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
            ],
            ['orientation' => 'landscape']
        );
    }

    public function exportCsv(Request $request)
    {
        $query = $this->applyApprovalFilters(Pengajuan::query(), $request);
        
        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $query->with(['user', 'kategori', 'departemen']),
            filenameBase: 'laporan_persetujuan',
            mode: 'approval'
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->applyApprovalFilters(Pengajuan::query(), $request);
        
        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $query->with(['user', 'kategori', 'departemen']),
            filenameBase: 'laporan_persetujuan',
            sheetName: 'Persetujuan Atasan',
            mode: 'approval'
        );
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);
        $pengajuan->loadMissing('user:id,name,email,role,atasan_id');

        $isSubordinatePengajuan = (int) ($pengajuan->user->atasan_id ?? 0) === (int) Auth::id();
        $wasApprovedByCurrentAtasan = (int) ($pengajuan->disetujui_atasan_oleh ?? 0) === (int) Auth::id();

        if (! Auth::user()->isAtasan() || (! $isSubordinatePengajuan && ! $wasApprovedByCurrentAtasan)) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $pengajuan->load([
            'validasiAi' => function ($validasiQuery) {
                $validasiQuery
                    ->select('validasi_id', 'pengajuan_id', 'jenis_validasi', 'status', 'confidence_score', 'hasil_ocr', 'pesan_validasi', 'is_blocking')
                    ->orderBy('validasi_id');
            },
            'departemen:departemen_id,nama_departemen,budget_limit',
        ]);

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

    public function approve(ApprovePengajuanRequest $request, Pengajuan $pengajuan)
    {
        $this->authorize('approveByAtasan', $pengajuan);

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Pengajuan ini tidak sedang menunggu persetujuan Anda.');
        }

        $user = Auth::user();

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($pengajuan, $user, $validated) {
                $lockedPengajuan = Pengajuan::query()
                    ->whereKey($pengajuan->pengajuan_id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedPengajuan || $lockedPengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
                    throw new \RuntimeException('PENGAJUAN_STATUS_CHANGED');
                }

                $statusFrom = $lockedPengajuan->status->value;

                $lockedPengajuan->update([
                    'status' => PengajuanStatus::MENUNGGU_FINANCE,
                    'disetujui_atasan_oleh' => $user->id,
                    'tanggal_disetujui_atasan' => now(),
                    'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                ]);

                $this->notifikasiService->notifyApprovedByAtasan($lockedPengajuan);
                $this->notifikasiService->notifyNewPengajuanToFinance($lockedPengajuan);

                $this->auditTrailService->logPengajuan(
                    event: 'pengajuan.approved_by_atasan',
                    pengajuan: $lockedPengajuan,
                    actor: $user,
                    description: 'Atasan menyetujui pengajuan dan meneruskan ke Finance.',
                    context: [
                        'status_from' => $statusFrom,
                        'status_to' => $lockedPengajuan->status->value,
                        'catatan_atasan' => $validated['catatan_atasan'] ?? null,
                    ]
                );
            });

            Cache::forget('atasan_approval_pending_count_user_'.$user->id);
            Cache::forget('atasan_dashboard_'.$user->id);

            return redirect()->route('atasan.approval.index')
                ->with('success', 'Pengajuan berhasil disetujui dan dikirim ke Finance.');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'PENGAJUAN_STATUS_CHANGED') {
                return redirect()->route('atasan.approval.index')
                    ->with('warning', 'Status pengajuan sudah berubah oleh proses lain. Silakan refresh data.');
            }

            throw $e;
        } catch (\Exception $e) {
            \Log::error('Approval failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses persetujuan. Silakan coba lagi.');
        }
    }

    public function reject(RejectPengajuanRequest $request, Pengajuan $pengajuan)
    {
        $this->authorize('rejectByAtasan', $pengajuan);

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
            return redirect()->route('atasan.approval.index')
                ->with('error', 'Pengajuan ini tidak sedang menunggu persetujuan Anda.');
        }

        $user = Auth::user();

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($pengajuan, $validated, $user) {
                $lockedPengajuan = Pengajuan::query()
                    ->whereKey($pengajuan->pengajuan_id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedPengajuan || $lockedPengajuan->status !== PengajuanStatus::MENUNGGU_ATASAN) {
                    throw new \RuntimeException('PENGAJUAN_STATUS_CHANGED');
                }

                $statusFrom = $lockedPengajuan->status->value;

                $lockedPengajuan->update([
                    'status' => PengajuanStatus::DITOLAK_ATASAN,
                    'catatan_atasan' => $validated['catatan_atasan'],
                ]);

                $this->notifikasiService->notifyRejectedByAtasan($lockedPengajuan);

                $this->auditTrailService->logPengajuan(
                    event: 'pengajuan.rejected_by_atasan',
                    pengajuan: $lockedPengajuan,
                    actor: $user,
                    description: 'Atasan menolak pengajuan.',
                    context: [
                        'status_from' => $statusFrom,
                        'status_to' => $lockedPengajuan->status->value,
                        'catatan_atasan' => $validated['catatan_atasan'],
                    ]
                );
            });

            Cache::forget('atasan_approval_pending_count_user_'.$user->id);
            Cache::forget('atasan_dashboard_'.$user->id);

            return redirect()->route('atasan.approval.index')
                ->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'PENGAJUAN_STATUS_CHANGED') {
                return redirect()->route('atasan.approval.index')
                    ->with('warning', 'Status pengajuan sudah berubah oleh proses lain. Silakan refresh data.');
            }

            throw $e;
        } catch (\Exception $e) {
            \Log::error('Rejection failed: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menolak pengajuan. Silakan coba lagi.');
        }
    }
}
