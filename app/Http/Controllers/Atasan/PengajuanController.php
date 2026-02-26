<?php

namespace App\Http\Controllers\Atasan;

use App\Actions\Pegawai\CreatePengajuanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanRequest;
use App\Models\KategoriBiaya;
use App\Models\Pengajuan;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Services\ValidasiAIService;
use App\Traits\FiltersPengajuan;
use App\Traits\HandlesImageUpload;
use App\Traits\HandlesPengajuanStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    use FiltersPengajuan, HandlesImageUpload, HandlesPengajuanStore;

    protected $validasiAIService;

    protected $notifikasiService;

    protected $exportService;

    protected $createAction;

    public function __construct(
        ValidasiAIService $validasiAIService,
        NotifikasiService $notifikasiService,
        ReportExportService $exportService,
        CreatePengajuanAction $createAction
    ) {
        $this->validasiAIService = $validasiAIService;
        $this->notifikasiService = $notifikasiService;
        $this->exportService = $exportService;
        $this->createAction = $createAction;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

        // Eager load relationships to prevent N+1 queries
        $pengajuanList = $query->with([
            'kategori:kategori_id,nama_kategori',
            'departemen:departemen_id,nama_departemen',
            'user:id,name',
            'validasiAi' => function ($validasiQuery) {
                $validasiQuery
                    ->select('validasi_id', 'pengajuan_id', 'jenis_validasi', 'status')
                    ->where('jenis_validasi', 'ocr');
            },
        ])->paginate(config('app.pagination.pengajuan'))
            ->withQueryString();

        // Cache stats for 1 minute to improve performance
        $stats = \Cache::remember("pengajuan_stats_{$userId}", 60, function () use ($userId) {
            return Pengajuan::getPersonalStats($userId);
        });

        if ($request->ajax()) {
            return response()->json([
                'table' => view('dashboard.atasan.pengajuan.partials._table', compact('pengajuanList'))->render(),
                'stats' => view('dashboard.atasan.pengajuan.partials._stats', compact('stats'))->render(),
            ]);
        }

        return view('dashboard.atasan.pengajuan.index', compact('pengajuanList', 'stats'));
    }

    public function create(Request $request)
    {
        $user = Auth::user()->load('departemen');
        $kategoriBiaya = KategoriBiaya::with('defaultCoa:coa_id,kode_coa,nama_coa')
            ->where('is_active', true)
            ->orderBy('nama_kategori')
            ->get();

        $duplicateFrom = null;
        if ($request->has('duplicate_id')) {
            $duplicateFrom = Pengajuan::where('user_id', Auth::id())
                ->find($request->duplicate_id);
        }

        $budgetStatus = null;
        if ($user->departemen_id) {
            $initialNominal = $duplicateFrom ? $duplicateFrom->nominal : 0;
            $budgetStatus = Pengajuan::getBudgetStatus($user->departemen_id, $initialNominal);
        }

        return view('dashboard.shared.pengajuan.create', [
            'kategoriBiaya' => $kategoriBiaya,
            'budgetStatus' => $budgetStatus,
            'duplicateFrom' => $duplicateFrom,
            'routePrefix' => 'atasan',
        ]);
    }

    public function store(StorePengajuanRequest $request)
    {
        $result = $this->createAction->execute(
            Auth::user(),
            $request->validated(),
            $request->file('file_bukti'),
            $request->input('ocr_data_json')
        );

        if (! $result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        return redirect()->route('atasan.pengajuan.show', $result['pengajuan']->pengajuan_id)
            ->with($result['type'] ?? 'success', $result['message']);
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);

        $pengajuan->load([
            'validasiAi' => function ($validasiQuery) {
                $validasiQuery
                    ->select('validasi_id', 'pengajuan_id', 'jenis_validasi', 'status', 'confidence_score', 'hasil_ocr', 'pesan_validasi', 'is_blocking')
                    ->orderBy('validasi_id');
            },
            'kategori:kategori_id,nama_kategori',
            'departemen:departemen_id,nama_departemen',
            'user:id,name,email,atasan_id',
        ]);

        $budgetStatus = null;
        if ($pengajuan->departemen_id) {
            $budgetStatus = Pengajuan::getBudgetStatus(
                $pengajuan->departemen_id,
                $pengajuan->nominal,
                $pengajuan->tanggal_transaksi->month,
                $pengajuan->tanggal_transaksi->year,
                $pengajuan->pengajuan_id
            );
        }

        return view('dashboard.atasan.pengajuan.show', compact('pengajuan', 'budgetStatus'));
    }

    public function destroy(Pengajuan $pengajuan)
    {
        $this->authorize('delete', $pengajuan);

        // Status yang boleh dihapus oleh atasan untuk pengajuan miliknya sendiri
        $allowedStatuses = [
            \App\Enums\PengajuanStatus::VALIDASI_AI, 
            \App\Enums\PengajuanStatus::MENUNGGU_FINANCE,
            \App\Enums\PengajuanStatus::DITOLAK_FINANCE,
        ];
        if (! in_array($pengajuan->status, $allowedStatuses)) {
            return redirect()->route('atasan.pengajuan.index')
                ->with('error', 'Hanya pengajuan yang belum diproses Finance atau sudah ditolak yang dapat dibatalkan.');
        }

        try {
            $pengajuan->delete();

            return redirect()->route('atasan.pengajuan.index')
                ->with('success', 'Pengajuan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('atasan.pengajuan.index')
                ->with('error', 'Gagal menghapus pengajuan: '.$e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);
        $pengajuanList = $query->with(['kategori', 'user', 'departemen'])->limit(500)->get();

        return $this->exportService->exportToPDF(
            'pengajuan_reimbursement_'.date('Y-m-d_His').'.pdf',
            'dashboard.atasan.pengajuan.pdf.laporan-pengajuan',
            [
                'pengajuanList' => $pengajuanList,
                'user' => Auth::user()->load('departemen'),
            ],
            ['orientation' => 'landscape']
        );
    }

    public function exportCsv(Request $request)
    {
        $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

        return $this->exportPengajuanCsvFromQuery(
            exportService: $this->exportService,
            query: $query->with(['user', 'kategori']),
            filenameBase: 'pengajuan_reimbursement',
            mode: 'personal'
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

        return $this->exportPengajuanXlsxFromQuery(
            exportService: $this->exportService,
            query: $query->with(['user', 'kategori']),
            filenameBase: 'pengajuan_reimbursement',
            sheetName: 'Pengajuan Pribadi',
            mode: 'personal'
        );
    }
}
