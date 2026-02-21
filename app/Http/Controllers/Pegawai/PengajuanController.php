<?php

namespace App\Http\Controllers\Pegawai;

use App\Actions\Pegawai\CreatePengajuanAction;
use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanRequest;
use App\Models\KategoriBiaya;
use App\Models\Pengajuan;
use App\Services\LocalReceiptParser;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Services\ValidasiAIService;
use App\Traits\FiltersPengajuan;
use App\Traits\HandlesImageUpload;
use App\Traits\HandlesPengajuanStore;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pegawai Pengajuan Controller
 *
 * Handles reimbursement request operations for employees:
 * - Create new reimbursement requests
 * - View personal request history
 * - Upload proof documents with AI validation
 * - Export request data to PDF/CSV
 * - Submit requests for approval
 */
class PengajuanController extends Controller
{
    use FiltersPengajuan, HandlesImageUpload, HandlesPengajuanStore;

    protected $validasiAIService;

    protected $notifikasiService;

    protected $exportService;

    protected $localParser;

    protected $createAction;

    /**
     * Constructor - Inject services
     */
    public function __construct(
        ValidasiAIService $validasiAIService,
        NotifikasiService $notifikasiService,
        ReportExportService $exportService,
        LocalReceiptParser $localParser,
        CreatePengajuanAction $createAction
    ) {
        $this->validasiAIService = $validasiAIService;
        $this->notifikasiService = $notifikasiService;
        $this->exportService = $exportService;
        $this->localParser = $localParser;
        $this->createAction = $createAction;
    }

    /**
     * Display employee's reimbursement requests with pagination
     *
     * Lists all pengajuan for authenticated user with filtering, sorting, and pagination.
     * Optimized with eager loading to prevent N+1 queries.
     * Shows summary stats: total, pending, approved, rejected.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

        // Cache stats for 1 minute to improve performance
        $stats = \Cache::remember("pengajuan_stats_{$userId}", 60, function () use ($userId) {
            return Pengajuan::getPersonalStats($userId);
        });

        // Eager load and paginate with specific columns and ordering
        $pengajuanList = $query->with([
            'kategori:kategori_id,nama_kategori',
            'departemen:departemen_id,nama_departemen',
            'validasiAi',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.pagination.pengajuan'))
            ->withQueryString();

        // Handle AJAX request for live filter
        if ($request->ajax()) {
            return response()->json([
                'table' => view('dashboard.pegawai.pengajuan.partials._table', compact('pengajuanList'))->render(),
                'stats' => view('dashboard.pegawai.pengajuan.partials._stats', compact('stats'))->render(),
            ]);
        }

        return view('dashboard.pegawai.pengajuan.index', compact('pengajuanList', 'stats'));
    }

    /**
     * Show create reimbursement form with budget tracking
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $kategoriBiaya = KategoriBiaya::with('defaultCoa:coa_id,kode_coa,nama_coa')
            ->where('is_active', true)
            ->orderBy('nama_kategori')
            ->get(['kategori_id', 'nama_kategori', 'deskripsi', 'default_coa_id']);

        $user = Auth::user()->load(['departemen:departemen_id,nama_departemen,budget_limit', 'atasan:id,name']);

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

        return view('dashboard.pegawai.pengajuan.create', compact('kategoriBiaya', 'budgetStatus', 'duplicateFrom'));
    }

    /**
     * Store new reimbursement request with AI validation
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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

        return redirect()->route('pegawai.pengajuan.show', $result['pengajuan']->pengajuan_id)
            ->with($result['type'] ?? 'success', $result['message']);
    }

    /**
     * Show pengajuan detail with full relationships
     *
     * @return \Illuminate\View\View
     */
    public function show(Pengajuan $pengajuan)
    {
        $this->authorize('view', $pengajuan);

        // Eager load user data for layout
        Auth::user()->load(['departemen', 'atasan']);

        // Eager load relationships with selective columns to optimize queries
        $pengajuan->load([
            'validasiAi:validasi_id,pengajuan_id,jenis_validasi,status,confidence_score,hasil_ocr,pesan_validasi,is_blocking',
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

        return view('dashboard.pegawai.pengajuan.show', compact('pengajuan', 'budgetStatus'));
    }

    /**
     * Delete pengajuan only if status is 'menunggu_atasan'
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Pengajuan $pengajuan)
    {
        $this->authorize('delete', $pengajuan);

        $allowedStatuses = [
            PengajuanStatus::VALIDASI_AI,
            PengajuanStatus::MENUNGGU_ATASAN,
            PengajuanStatus::DITOLAK_ATASAN,
            PengajuanStatus::DITOLAK_FINANCE,
        ];
        if (! in_array($pengajuan->status, $allowedStatuses)) {
            return redirect()->route('pegawai.pengajuan.index')
                ->with('error', 'Hanya pengajuan yang belum disetujui atau sudah ditolak yang dapat dibatalkan/dihapus.');
        }

        try {
            $pengajuan->delete();

            return redirect()->route('pegawai.pengajuan.index')
                ->with('success', 'Pengajuan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('pegawai.pengajuan.index')
                ->with('error', 'Gagal menghapus pengajuan: '.$e->getMessage());
        }
    }

    /**
     * Export reimbursement requests to PDF format
     * Authorization: User can only export their own data
     * Note: Route changed to POST to require CSRF token and prevent cache/prefetch vulnerabilities
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AuthorizationException If user tries to export other users' data
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);
            $pengajuanList = $query->limit(500)->with([
                'kategori:kategori_id,nama_kategori',
                'departemen:departemen_id,nama_departemen',
            ])->get();

            // Security: Ensure user can only export their own data
            foreach ($pengajuanList as $pengajuan) {
                if ($pengajuan->user_id !== Auth::id()) {
                    \Log::warning('Unauthorized PDF export attempt by user '.Auth::id().' for pengajuan '.$pengajuan->pengajuan_id);
                    abort(403, 'Anda tidak memiliki akses untuk mengekspor data ini.');
                }
            }

            \Log::info('User '.Auth::id().' exported '.count($pengajuanList).' pengajuan records to PDF');

            return $this->exportService->exportToPDF(
                'pengajuan_reimbursement_'.date('Y-m-d_His').'.pdf',
                'dashboard.pegawai.pengajuan.pdf.laporan-pengajuan',
                [
                    'pengajuanList' => $pengajuanList,
                    'user' => Auth::user()->load('departemen'),
                ],
                ['orientation' => 'landscape']
            );
        } catch (\Exception $e) {
            \Log::error('PDF export error for user '.Auth::id().': '.$e->getMessage());
            abort(500, 'Gagal mengekspor data PDF. Silakan coba lagi.');
        }
    }

    /**
     * Export reimbursement requests to CSV format
     * Authorization: User can only export their own data
     * Note: Route changed to POST to require CSRF token and prevent cache/prefetch vulnerabilities
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws AuthorizationException If user tries to export other users' data
     */
    public function exportCsv(Request $request)
    {
        try {
            $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

            $fileName = 'pengajuan_reimbursement_'.date('Y-m-d_His').'.csv';
            $headers = $this->getPengajuanCsvHeaders('personal');

            $pengajuanList = $query->with(['user', 'kategori'])->get();
            $data = $this->mapPengajuanForCsv($pengajuanList, 'personal');

            \Log::info('User '.Auth::id().' exporting CSV with '.count($pengajuanList).' records');

            return $this->exportService->exportToCSV($fileName, $headers, $data);
        } catch (\Exception $e) {
            \Log::error('CSV export error for user '.Auth::id().': '.$e->getMessage());
            abort(500, 'Gagal mengekspor data CSV. Silakan coba lagi.');
        }
    }

    /**
     * Export reimbursement requests to XLSX format.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportXlsx(Request $request)
    {
        try {
            $query = $this->applyPersonalPengajuanFilters(Pengajuan::query(), $request);

            $fileName = 'pengajuan_reimbursement_'.date('Y-m-d_His').'.xlsx';
            $headers = $this->getPengajuanCsvHeaders('personal');

            $pengajuanList = $query->with(['user', 'kategori'])->get();
            $data = $this->mapPengajuanForCsv($pengajuanList, 'personal');

            \Log::info('User '.Auth::id().' exporting XLSX with '.count($pengajuanList).' records');

            return $this->exportService->exportToXlsx($fileName, $headers, $data, [
                'sheet_name' => 'Pengajuan Pribadi',
            ]);
        } catch (\Exception $e) {
            \Log::error('XLSX export error for user '.Auth::id().': '.$e->getMessage());
            abort(500, 'Gagal mengekspor data XLSX. Silakan coba lagi.');
        }
    }
}
