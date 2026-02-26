<?php

namespace App\Http\Controllers\Finance\Workflows;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\RejectByFinanceRequest;
use App\Http\Requests\Finance\SendToAccurateRequest;
use App\Models\COA;
use App\Models\Departemen;
use App\Models\KasBank;
use App\Models\Pengajuan;
use App\Services\FinanceApprovalQueryService;
use App\Services\FinanceApprovalWorkflowService;
use App\Services\ReportExportService;
use App\Traits\FiltersPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    use FiltersPengajuan;

    public function __construct(
        protected FinanceApprovalQueryService $approvalQueryService,
        protected FinanceApprovalWorkflowService $workflowService,
        protected ReportExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $query = $this->approvalQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with([
                'user:id,name',
                'departemen:departemen_id,nama_departemen',
                'kategori:kategori_id,nama_kategori',
                'validasiAi',
            ])
        );

        $pengajuans = $query->paginate(config('app.pagination.approval'));

        // Optimized: Get totals only for the filtered view or global if needed
        // Usually index shows global pending stats for the badge/summary
        $stats = Pengajuan::selectRaw('
            COUNT(CASE WHEN status = ? THEN 1 END) as total_pending,
            SUM(CASE WHEN status = ? THEN nominal ELSE 0 END) as total_nominal_pending
        ', [PengajuanStatus::MENUNGGU_FINANCE->value, PengajuanStatus::MENUNGGU_FINANCE->value])->first();

        $totalPending = $stats->total_pending ?? 0;
        $totalNominalPending = $stats->total_nominal_pending ?? 0;

        $departemens = Cache::remember('departemens_list', 3600, function () {
            return Departemen::orderBy('nama_departemen')->get(['departemen_id', 'nama_departemen']);
        });

        return view('dashboard.finance.approval.index', compact('pengajuans', 'totalPending', 'totalNominalPending', 'departemens'));
    }

    public function getCount()
    {
        $cacheKey = 'finance_approval_pending_count';
        $count = Cache::flexible($cacheKey, [5, 15], function () use ($cacheKey) {
            return Cache::lock($cacheKey.'_lock', 5)->block(2, function () {
                return Pengajuan::where('status', PengajuanStatus::MENUNGGU_FINANCE->value)->count();
            });
        });

        return response()->json(['pending_count' => $count]);
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('reviewByFinance', $pengajuan);

        if (! in_array($pengajuan->status, [
            PengajuanStatus::MENUNGGU_FINANCE,
            PengajuanStatus::TERKIRIM_ACCURATE,
            PengajuanStatus::DICAIRKAN,
            PengajuanStatus::DITOLAK_FINANCE,
            PengajuanStatus::SELESAI,
        ])) {
            abort(403, 'Pengajuan tidak tersedia untuk Finance');
        }

        // Trigger auto-mapping if coa_id is still null
        if (! $pengajuan->coa_id && $pengajuan->status === PengajuanStatus::MENUNGGU_FINANCE) {
            $pengajuan->save(); // This will trigger the booted saving hook
        }

        $coas = Cache::remember('coas_list', 3600, function () {
            return COA::where('is_active', true)
                ->orderBy('kode_coa')
                ->get(['coa_id', 'kode_coa', 'nama_coa']);
        });

        $kasBanks = Cache::remember('kasbanks_list', 3600, function () {
            return KasBank::where('is_active', true)
                ->orderBy('nama_kas_bank')
                ->get(['kas_bank_id', 'nama_kas_bank', 'kode_kas_bank', 'accurate_id']);
        });

        $pengajuan->load([
            'user:id,name,email,atasan_id',
            'departemen:departemen_id,nama_departemen',
            'approvedByAtasan:id,name',
            'kategori:kategori_id,nama_kategori',
            'coa:coa_id,kode_coa,nama_coa',
            'kasBank:kas_bank_id,nama_kas_bank',
            'validasiAi',
        ]);

        $budgetStatus = Pengajuan::getBudgetStatus(
            $pengajuan->departemen_id,
            $pengajuan->nominal,
            $pengajuan->tanggal_transaksi->month,
            $pengajuan->tanggal_transaksi->year,
            $pengajuan->pengajuan_id
        );

        // COA Prediction based on category + history
        $coaPrediction = null;
        if ($pengajuan->kategori) {
            $prediction = $this->predictCOA($pengajuan);
            if ($prediction) {
                $coaPrediction = $prediction;
            }
        }

        return view('dashboard.finance.approval.show', compact('pengajuan', 'coas', 'kasBanks', 'budgetStatus', 'coaPrediction'));
    }

    /**
     * Predict COA based on kategori default and historical usage patterns
     *
     * @return array|null ['coa_id', 'coa_code', 'coa_name', 'confidence', 'reason']
     */
    private function predictCOA(Pengajuan $pengajuan): ?array
    {
        try {
            // 1. Primary: Use kategori's default COA (already set during creation)
            if ($pengajuan->kategori && $pengajuan->kategori->default_coa_id) {
                $defaultCoa = COA::find($pengajuan->kategori->default_coa_id);
                if ($defaultCoa) {
                    return [
                        'coa_id' => $defaultCoa->coa_id,
                        'coa_code' => $defaultCoa->kode_coa,
                        'coa_name' => $defaultCoa->nama_coa,
                        'confidence' => 'high',
                        'reason' => 'Default COA dari kategori '.$pengajuan->kategori->nama_kategori,
                    ];
                }
            }

            // 2. Secondary: Find most frequently used COA for same kategori
            $historicalCOA = Pengajuan::where('kategori_id', $pengajuan->kategori_id)
                ->whereNotNull('coa_id')
                ->whereIn('status', ['terkirim_accurate', 'dicairkan'])
                ->selectRaw('coa_id, COUNT(*) as usage_count')
                ->groupBy('coa_id')
                ->orderByRaw('COUNT(*) DESC')
                ->first();

            if ($historicalCOA) {
                $coa = COA::find($historicalCOA->coa_id);
                if ($coa) {
                    return [
                        'coa_id' => $coa->coa_id,
                        'coa_code' => $coa->kode_coa,
                        'coa_name' => $coa->nama_coa,
                        'confidence' => 'medium',
                        'reason' => 'COA yang paling sering digunakan untuk kategori ini ('.$historicalCOA->usage_count.' kali)',
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::debug('COA Prediction error: '.$e->getMessage());

            return null;
        }
    }

    public function send(SendToAccurateRequest $request, Pengajuan $pengajuan)
    {
        $this->authorize('sendToAccurate', $pengajuan);
        $result = $this->workflowService->sendToAccurate($pengajuan, $request->validated(), $request->user());

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function retry(Request $request, Pengajuan $pengajuan)
    {
        $this->authorize('sendToAccurate', $pengajuan);
        $result = $this->workflowService->retrySendToAccurate($pengajuan, $request->user());

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function reject(RejectByFinanceRequest $request, Pengajuan $pengajuan)
    {
        $this->authorize('rejectByFinance', $pengajuan);
        $result = $this->workflowService->rejectByFinance($pengajuan, $request->validated('catatan_finance'), $request->user());

        return redirect()->route('finance.approval.index')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function exportCsv(Request $request)
    {
        $query = $this->approvalQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'verifikasi_pengajuan',
            mode: 'finance'
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->approvalQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'verifikasi_pengajuan',
            sheetName: 'Verifikasi Finance',
            mode: 'finance'
        );
    }

    public function exportPdf(Request $request)
    {
        $query = $this->approvalQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        $pengajuans = $query->get();
        $totalNominal = $pengajuans->sum('nominal');

        return $this->exportService->exportToPDF(
            'verifikasi_pengajuan_'.date('Y-m-d').'.pdf',
            'dashboard.finance.approval.pdf.approval-report',
            compact('pengajuans', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }

    public function history(Request $request)
    {
        $query = $this->approvalQueryService->history(
            request: $request,
            query: Pengajuan::query()->with([
                'user:id,name',
                'departemen:departemen_id,nama_departemen',
                'kategori:kategori_id,nama_kategori',
                'validasiAi',
            ])
        );

        $pengajuans = $query->paginate(config('app.pagination.approval'));

        $approvedStatuses = [
            PengajuanStatus::TERKIRIM_ACCURATE->value,
            PengajuanStatus::DICAIRKAN->value,
            PengajuanStatus::SELESAI->value,
        ];

        $totalNominal = $query->clone()->sum('nominal');
        $totalProcessed = $query->clone()->count();
        $totalApproved = $query->clone()->whereIn('pengajuan.status', $approvedStatuses)->count();
        $totalRejected = $query->clone()->where('pengajuan.status', PengajuanStatus::DITOLAK_FINANCE->value)->count();

        $departemens = Departemen::orderBy('nama_departemen')->get(['departemen_id', 'nama_departemen']);

        return view('dashboard.finance.approval.history', compact(
            'pengajuans',
            'totalNominal',
            'totalProcessed',
            'totalApproved',
            'totalRejected',
            'departemens'
        ));
    }

    public function historyExportCsv(Request $request)
    {
        $query = $this->approvalQueryService->history(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'riwayat_approval_finance',
            mode: 'finance'
        );
    }

    public function historyExportXlsx(Request $request)
    {
        $query = $this->approvalQueryService->history(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'riwayat_approval_finance',
            sheetName: 'Riwayat Approval',
            mode: 'finance'
        );
    }

    public function historyExportPdf(Request $request)
    {
        $query = $this->approvalQueryService->history(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        $pengajuans = $query->get();
        $totalNominal = $pengajuans->sum('nominal');
        $range = $this->resolveExportDateRange(
            request: $request,
            dates: $pengajuans->map(fn ($item) => $item->tanggal_disetujui_finance ?? $item->created_at)
        );
        $startDate = $range['startDate'];
        $endDate = $range['endDate'];

        return $this->exportService->exportToPDF(
            'riwayat_approval_finance_'.date('Y-m-d').'.pdf',
            'dashboard.finance.approval.pdf.approval-history',
            compact('pengajuans', 'startDate', 'endDate', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }
}
