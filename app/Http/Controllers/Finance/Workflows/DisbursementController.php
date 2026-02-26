<?php

namespace App\Http\Controllers\Finance\Workflows;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\MarkDisbursementRequest;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Services\AuditTrailService;
use App\Services\FinanceDisbursementQueryService;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Traits\FiltersPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DisbursementController extends Controller
{
    use FiltersPengajuan;

    public function __construct(
        protected FinanceDisbursementQueryService $disbursementQueryService,
        protected NotifikasiService $notifikasiService,
        protected AuditTrailService $auditTrailService,
        protected ReportExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $query = $this->disbursementQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen')
        );

        $pengajuans = $query->paginate(config('app.pagination.approval'));

        $summaryCacheKey = 'finance_disbursement_summary';
        $summary = Cache::flexible($summaryCacheKey, [20, 60], function () use ($summaryCacheKey) {
            return Cache::lock($summaryCacheKey.'_lock', 5)->block(2, function () {
                return Pengajuan::selectRaw('
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_waiting_disbursement,
                    SUM(CASE WHEN status = ? THEN nominal ELSE 0 END) as total_nominal_waiting_disbursement,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_already_disbursed
                ', [
                    PengajuanStatus::TERKIRIM_ACCURATE->value,
                    PengajuanStatus::TERKIRIM_ACCURATE->value,
                    PengajuanStatus::DICAIRKAN->value,
                ])->first();
            });
        });

        $totalWaitingDisbursement = (int) ($summary->total_waiting_disbursement ?? 0);
        $totalNominalWaitingDisbursement = (float) ($summary->total_nominal_waiting_disbursement ?? 0);
        $totalAlreadyDisbursed = (int) ($summary->total_already_disbursed ?? 0);

        $departemens = Cache::remember('departemens_list', 3600, function () {
            return Departemen::orderBy('nama_departemen')->get();
        });

        return view('dashboard.finance.pencairan.index', compact('pengajuans', 'totalWaitingDisbursement', 'totalNominalWaitingDisbursement', 'totalAlreadyDisbursed', 'departemens'));
    }

    public function getCount()
    {
        $cacheKey = 'finance_disbursement_pending_count';
        $count = Cache::flexible($cacheKey, [5, 15], function () use ($cacheKey) {
            return Cache::lock($cacheKey.'_lock', 5)->block(2, function () {
                return Pengajuan::where('status', PengajuanStatus::TERKIRIM_ACCURATE->value)->count();
            });
        });

        return response()->json(['pending_count' => $count]);
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('reviewByFinance', $pengajuan);

        if (! \in_array($pengajuan->status, [
            PengajuanStatus::TERKIRIM_ACCURATE,
            PengajuanStatus::DICAIRKAN,
            PengajuanStatus::SELESAI,
        ])) {
            return abort(403, 'Pengajuan tidak dalam status pencairan atau riwayat');
        }

        $pengajuan->load(['user', 'departemen', 'kategori', 'coa', 'kasBank', 'validasiAi']);

        return view('dashboard.finance.pencairan.show', compact('pengajuan'));
    }

    public function exportCsv(Request $request)
    {
        $query = $this->disbursementQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'daftar_pencairan',
            mode: 'finance'
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->disbursementQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $query,
            filenameBase: 'daftar_pencairan',
            sheetName: 'Pencairan',
            mode: 'finance'
        );
    }

    public function exportPdf(Request $request)
    {
        $query = $this->disbursementQueryService->pending(
            request: $request,
            query: Pengajuan::query()->with('user', 'departemen', 'kategori')
        );

        $pengajuan = $query->get();
        $totalNominal = $pengajuan->sum('nominal');

        return $this->exportService->exportToPDF(
            'daftar_pencairan_'.date('Y-m-d').'.pdf',
            'dashboard.finance.pencairan.pdf.pencairan-index',
            compact('pengajuan', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }

    public function mark(MarkDisbursementRequest $request, Pengajuan $pengajuan)
    {
        $this->authorize('markDisbursed', $pengajuan);

        Cache::forget('finance_disbursement_pending_count');
        Cache::forget('finance_disbursement_summary');

        if ($pengajuan->status !== PengajuanStatus::TERKIRIM_ACCURATE) {
            return back()->with('error', 'Pengajuan tidak dalam status terkirim accurate');
        }

        $validated = $request->validated();

        try {
            $statusFrom = $pengajuan->status->value;
            DB::transaction(function () use ($pengajuan, $validated, $statusFrom) {
                $pengajuan->update([
                    'status' => PengajuanStatus::DICAIRKAN->value,
                    'tanggal_pencairan' => $validated['tanggal_pencairan'],
                ]);

                $this->notifikasiService->notifyDisbursed($pengajuan);
                $this->auditTrailService->logPengajuan(
                    event: 'pengajuan.marked_disbursed',
                    pengajuan: $pengajuan,
                    actor: auth()->user(),
                    description: 'Finance menandai pengajuan sebagai dicairkan.',
                    context: [
                        'status_from' => $statusFrom,
                        'status_to' => $pengajuan->status->value,
                        'tanggal_pencairan' => $validated['tanggal_pencairan'],
                    ]
                );
            });

            return back()->with('success', 'Pengajuan berhasil ditandai sebagai dicairkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $pengajuanQuery = $this->disbursementQueryService->history(
            request: $request,
            query: Pengajuan::query()->with(['user', 'departemen', 'kategori'])
        );

        $pengajuan = $pengajuanQuery->paginate(config('app.pagination.approval'));

        $totalNominal = $pengajuanQuery->clone()->sum('nominal');
        $totalCount = $pengajuanQuery->clone()->count();

        $departemen = \App\Models\Departemen::orderBy('nama_departemen')->get();

        $departemenBreakdown = $pengajuanQuery->clone()
            ->selectRaw('pengajuan.departemen_id, COUNT(*) as count, SUM(nominal) as total')
            ->groupBy('pengajuan.departemen_id')
            ->with('departemen')
            ->get()
            ->keyBy('departemen_id');

        return view('dashboard.finance.pencairan.pencairan-history', compact(
            'pengajuan',
            'totalNominal',
            'totalCount',
            'departemen',
            'departemenBreakdown'
        ));
    }

    public function historyExportCsv(Request $request)
    {
        $pengajuanQuery = $this->disbursementQueryService->history(
            request: $request,
            query: Pengajuan::query()->with(['user', 'departemen', 'kategori'])
        );

        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $pengajuanQuery,
            filenameBase: 'riwayat_pencairan',
            mode: 'history'
        );
    }

    public function historyExportXlsx(Request $request)
    {
        $pengajuanQuery = $this->disbursementQueryService->history(
            request: $request,
            query: Pengajuan::query()->with(['user', 'departemen', 'kategori'])
        );

        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $pengajuanQuery,
            filenameBase: 'riwayat_pencairan',
            sheetName: 'Riwayat Pencairan',
            mode: 'history'
        );
    }

    public function historyExportPdf(Request $request)
    {
        $pengajuanQuery = $this->disbursementQueryService->history(
            request: $request,
            query: Pengajuan::query()->with(['user', 'departemen', 'kategori'])
        );

        $pengajuan = $pengajuanQuery->get();
        $totalNominal = $pengajuan->sum('nominal');
        $range = $this->resolveExportDateRange(
            request: $request,
            dates: $pengajuan->pluck('tanggal_pencairan')
        );
        $startDate = $range['startDate'];
        $endDate = $range['endDate'];

        return $this->exportService->exportToPDF(
            'riwayat_pencairan_'.date('Y-m-d').'.pdf',
            'dashboard.finance.pencairan.pdf.pencairan-history',
            compact('pengajuan', 'startDate', 'endDate', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }
}
