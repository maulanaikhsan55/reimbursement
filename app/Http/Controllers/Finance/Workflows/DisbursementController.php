<?php

namespace App\Http\Controllers\Finance\Workflows;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Services\JurnalService;
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
        protected NotifikasiService $notifikasiService,
        protected JurnalService $jurnalService,
        protected ReportExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $query = $this->applyFinanceFilters(
            Pengajuan::query()->with('user', 'departemen'),
            $request,
            PengajuanStatus::TERKIRIM_ACCURATE->value
        );

        $pengajuans = $query->paginate(config('app.pagination.approval'));

        $totalWaitingDisbursement = Pengajuan::where('status', PengajuanStatus::TERKIRIM_ACCURATE->value)->count();
        $totalNominalWaitingDisbursement = Pengajuan::where('status', PengajuanStatus::TERKIRIM_ACCURATE->value)->sum('nominal');
        $totalAlreadyDisbursed = Pengajuan::where('status', PengajuanStatus::DICAIRKAN->value)->count();

        $departemens = Departemen::orderBy('nama_departemen')->get();

        return view('dashboard.finance.pencairan.index', compact('pengajuans', 'totalWaitingDisbursement', 'totalNominalWaitingDisbursement', 'totalAlreadyDisbursed', 'departemens'));
    }

    public function getCount()
    {
        $count = Cache::remember('finance_disbursement_pending_count', 10, function () {
            return Pengajuan::where('status', PengajuanStatus::TERKIRIM_ACCURATE->value)->count();
        });

        return response()->json(['pending_count' => $count]);
    }

    public function show(Pengajuan $pengajuan)
    {
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
        $query = $this->applyFinanceFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request,
            PengajuanStatus::TERKIRIM_ACCURATE->value
        );

        $pengajuan = $query->get();

        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuan, 'finance');

        return $this->exportService->exportToCSV(
            'daftar_pencairan_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->applyFinanceFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request,
            PengajuanStatus::TERKIRIM_ACCURATE->value
        );

        $pengajuan = $query->get();
        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuan, 'finance');

        return $this->exportService->exportToXlsx(
            'daftar_pencairan_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Pencairan']
        );
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyFinanceFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request,
            PengajuanStatus::TERKIRIM_ACCURATE->value
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

    public function mark(Request $request, Pengajuan $pengajuan)
    {
        Cache::forget('finance_disbursement_pending_count');

        if ($pengajuan->status !== PengajuanStatus::TERKIRIM_ACCURATE) {
            return back()->with('error', 'Pengajuan tidak dalam status terkirim accurate');
        }

        $validated = $request->validate([
            'tanggal_pencairan' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($pengajuan, $validated) {
                $pengajuan->update([
                    'status' => PengajuanStatus::DICAIRKAN->value,
                    'tanggal_pencairan' => $validated['tanggal_pencairan'],
                ]);

                $this->notifikasiService->notifyDisbursed($pengajuan);
            });

            return back()->with('success', 'Pengajuan berhasil ditandai sebagai dicairkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $pengajuanQuery = $this->applyHistoryFilters(
            Pengajuan::query()->with(['user', 'departemen', 'kategori']),
            $request
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
        $pengajuanQuery = $this->applyHistoryFilters(
            Pengajuan::query()->with(['user', 'departemen', 'kategori']),
            $request
        );

        $pengajuan = $pengajuanQuery->get();

        $headers = $this->getPengajuanCsvHeaders('history');
        $data = $this->mapPengajuanForCsv($pengajuan, 'history');

        return $this->exportService->exportToCSV(
            'riwayat_pencairan_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function historyExportXlsx(Request $request)
    {
        $pengajuanQuery = $this->applyHistoryFilters(
            Pengajuan::query()->with(['user', 'departemen', 'kategori']),
            $request
        );

        $pengajuan = $pengajuanQuery->get();
        $headers = $this->getPengajuanCsvHeaders('history');
        $data = $this->mapPengajuanForCsv($pengajuan, 'history');

        return $this->exportService->exportToXlsx(
            'riwayat_pencairan_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Riwayat Pencairan']
        );
    }

    public function historyExportPdf(Request $request)
    {
        $pengajuanQuery = $this->applyHistoryFilters(
            Pengajuan::query()->with(['user', 'departemen', 'kategori']),
            $request
        );

        $pengajuan = $pengajuanQuery->get();
        $totalNominal = $pengajuan->sum('nominal');
        $disbursedDates = $pengajuan->pluck('tanggal_pencairan')->filter();

        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
            : ($disbursedDates->min() ? \Carbon\Carbon::parse($disbursedDates->min())->startOfDay() : \Carbon\Carbon::now()->subMonths(1)->startOfDay());

        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
            : ($disbursedDates->max() ? \Carbon\Carbon::parse($disbursedDates->max())->endOfDay() : \Carbon\Carbon::now()->endOfDay());

        return $this->exportService->exportToPDF(
            'riwayat_pencairan_'.date('Y-m-d').'.pdf',
            'dashboard.finance.pencairan.pdf.pencairan-history',
            compact('pengajuan', 'startDate', 'endDate', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }
}
