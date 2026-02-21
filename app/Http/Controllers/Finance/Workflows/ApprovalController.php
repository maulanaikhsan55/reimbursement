<?php

namespace App\Http\Controllers\Finance\Workflows;

use App\Enums\PengajuanStatus;
use App\Events\NotifikasiPengajuan;
use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\Departemen;
use App\Models\KasBank;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Services\AccurateService;
use App\Services\JurnalService;
use App\Services\NotifikasiService;
use App\Services\ReportExportService;
use App\Traits\FiltersPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    use FiltersPengajuan;

    public function __construct(
        protected AccurateService $accurateService,
        protected NotifikasiService $notifikasiService,
        protected JurnalService $jurnalService,
        protected ReportExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $query = $this->applyPendingApprovalFilters(
            Pengajuan::query()->with([
                'user:id,name',
                'departemen:departemen_id,nama_departemen',
                'kategori:kategori_id,nama_kategori',
                'validasiAi',
            ]),
            $request
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
        $count = Cache::remember('finance_approval_pending_count', 10, function () {
            return Pengajuan::where('status', PengajuanStatus::MENUNGGU_FINANCE->value)->count();
        });

        return response()->json(['pending_count' => $count]);
    }

    public function show(Pengajuan $pengajuan)
    {
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

    public function send(Request $request, Pengajuan $pengajuan)
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return back()->with('error', 'Pengajuan tidak dalam status menunggu finance');
        }

        $validated = $request->validate([
            'coa_id' => 'required|exists:coa,coa_id',
            'kas_bank_id' => 'required|exists:kas_bank,kas_bank_id',
            'catatan_finance' => 'nullable|string|max:500',
        ]);

        $coa = COA::findOrFail($validated['coa_id']);
        $kasBank = KasBank::findOrFail($validated['kas_bank_id']);

        // Final server-side balance check
        if ($kasBank->accurate_id) {
            $balanceResult = $this->accurateService->getAccountBalance($kasBank->accurate_id);
            if ($balanceResult['success'] && $balanceResult['balance'] < $pengajuan->nominal) {
                return back()->with('error', 'Gagal: Saldo '.$kasBank->nama_kas_bank.' di Accurate tidak mencukupi (Sisa: Rp '.number_format($balanceResult['balance'], 0, ',', '.').')');
            }
        }

        // Check if this pengajuan can be auto-approved
        $canAutoApprove = $this->isEligibleForAutoApproval($pengajuan);

        $pengajuan->update([
            'coa_id' => $validated['coa_id'],
            'kas_bank_id' => $validated['kas_bank_id'],
            'catatan_finance' => $validated['catatan_finance'] ?? null,
        ]);

        $response = $this->accurateService->sendTransaction(
            $pengajuan,
            $coa->kode_coa,
            $kasBank->kode_kas_bank
        );

        if ($response['success']) {
            DB::transaction(function () use ($pengajuan, $response) {
                $pengajuan->update([
                    'status' => PengajuanStatus::TERKIRIM_ACCURATE,
                    'accurate_transaction_id' => $response['transaction_id'],
                    'disetujui_finance_oleh' => auth()->user()->id,
                    'tanggal_disetujui_finance' => now(),
                ]);

                // Create local journal immediately after successful Accurate sync
                $jurnalResult = $this->jurnalService->createJurnalFromPengajuan($pengajuan, $response['transaction_id']);
                if (! $jurnalResult['success']) {
                    throw new \Exception('Berhasil ke Accurate tapi gagal buat jurnal lokal: '.$jurnalResult['error']);
                }

                $this->notifikasiService->notifySentToAccurate($pengajuan);
            });

            $successMsg = "Pengajuan #{$pengajuan->nomor_pengajuan} berhasil dikirim ke Accurate dengan ID: {$response['transaction_id']}";

            // Persistent notification for current Finance user
            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'success',
                'judul' => 'Kirim Accurate Berhasil',
                'pesan' => $successMsg,
                'is_read' => false,
            ]);

            // Add real-time notification for current Finance user
            try {
                event(new NotifikasiPengajuan(
                    auth()->id(),
                    'Kirim Accurate Berhasil',
                    $successMsg,
                    'success'
                ));
            } catch (\Exception $e) {
                \Log::warning('Gagal mengirim broadcast approval berhasil: '.$e->getMessage());
            }

            return back()->with('success', "Pengajuan berhasil dikirim ke Accurate Online dengan nomor jurnal #{$response['transaction_id']} dan jurnal lokal telah dibuat");
        } else {
            $this->notifikasiService->notifyFailedToAccurate($pengajuan, $response['error_message'] ?? $response['message']);

            return back()->with('error', $response['message']);
        }
    }

    public function retry(Pengajuan $pengajuan)
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return back()->with('error', 'Pengajuan tidak dapat di-retry');
        }

        if (! $pengajuan->coa_id || ! $pengajuan->kas_bank_id) {
            return back()->with('error', 'COA dan Kas/Bank harus dipilih sebelum retry');
        }

        $coa = COA::find($pengajuan->coa_id);
        $kasBank = KasBank::find($pengajuan->kas_bank_id);

        // 1. EXTRA RESILIENCY: Check if already exists in Accurate BEFORE sending
        // This is a safety net in case AccurateService.sendTransaction's check isn't enough
        $check = $this->accurateService->checkTransactionExists($pengajuan->nomor_pengajuan);
        if ($check['success'] && $check['exists']) {
            $transactionId = $check['data']['number'] ?? $check['data']['id'];

            DB::transaction(function () use ($pengajuan, $transactionId) {
                $pengajuan->update([
                    'status' => PengajuanStatus::TERKIRIM_ACCURATE,
                    'accurate_transaction_id' => $transactionId,
                    'disetujui_finance_oleh' => auth()->user()->id,
                    'tanggal_disetujui_finance' => now(),
                ]);

                $this->jurnalService->createJurnalFromPengajuan($pengajuan, $transactionId);
                $this->notifikasiService->notifySentToAccurate($pengajuan);
            });

            return back()->with('success', "Transaksi ternyata sudah ada di Accurate (#{$transactionId}). Status telah disinkronkan.");
        }

        // Final server-side balance check
        if ($kasBank->accurate_id) {
            $balanceResult = $this->accurateService->getAccountBalance($kasBank->accurate_id);
            if ($balanceResult['success'] && $balanceResult['balance'] < $pengajuan->nominal) {
                return back()->with('error', 'Gagal: Saldo '.$kasBank->nama_kas_bank.' di Accurate tidak mencukupi (Sisa: Rp '.number_format($balanceResult['balance'], 0, ',', '.').')');
            }
        }

        $response = $this->accurateService->sendTransaction(
            $pengajuan,
            $coa->kode_coa,
            $kasBank->kode_kas_bank
        );

        if ($response['success']) {
            DB::transaction(function () use ($pengajuan, $response) {
                $pengajuan->update([
                    'status' => PengajuanStatus::TERKIRIM_ACCURATE,
                    'accurate_transaction_id' => $response['transaction_id'],
                    'disetujui_finance_oleh' => auth()->user()->id,
                    'tanggal_disetujui_finance' => now(),
                ]);

                // Create local journal immediately after successful Accurate sync
                $jurnalResult = $this->jurnalService->createJurnalFromPengajuan($pengajuan, $response['transaction_id']);
                if (! $jurnalResult['success']) {
                    throw new \Exception('Berhasil ke Accurate tapi gagal buat jurnal lokal: '.$jurnalResult['error']);
                }

                $this->notifikasiService->notifySentToAccurate($pengajuan);
            });

            $successMsg = "Pengajuan #{$pengajuan->nomor_pengajuan} berhasil dikirim ke Accurate dengan ID: {$response['transaction_id']} (Retry)";

            // Persistent notification for current Finance user
            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'success',
                'judul' => 'Kirim Accurate Berhasil',
                'pesan' => $successMsg,
                'is_read' => false,
            ]);

            // Add real-time notification for current Finance user
            try {
                event(new NotifikasiPengajuan(
                    auth()->id(),
                    'Kirim Accurate Berhasil',
                    $successMsg,
                    'success'
                ));
            } catch (\Exception $e) {
                \Log::warning('Gagal mengirim broadcast approval berhasil: '.$e->getMessage());
            }

            return back()->with('success', "Retry berhasil. Pengajuan terkirim ke Accurate Online dengan nomor jurnal #{$response['transaction_id']} dan jurnal lokal telah dibuat");
        } else {
            $this->notifikasiService->notifyFailedToAccurate($pengajuan, $response['message']);

            return back()->with('error', $response['message']);
        }
    }

    public function reject(Request $request, Pengajuan $pengajuan)
    {
        Cache::forget('finance_approval_pending_count');

        if ($pengajuan->status !== PengajuanStatus::MENUNGGU_FINANCE) {
            return back()->with('error', 'Pengajuan tidak dalam status menunggu finance');
        }

        $validated = $request->validate([
            'catatan_finance' => 'required|string|max:500',
        ]);

        $pengajuan->update([
            'status' => PengajuanStatus::DITOLAK_FINANCE,
            'disetujui_finance_oleh' => auth()->user()->id,
            'tanggal_disetujui_finance' => now(),
            'catatan_finance' => $validated['catatan_finance'],
        ]);

        $this->notifikasiService->notifyRejectedByFinance($pengajuan);

        return redirect()->route('finance.approval.index')->with('success', 'Pengajuan berhasil ditolak');
    }

    public function exportCsv(Request $request)
    {
        $query = $this->applyPendingApprovalFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
        );

        $pengajuans = $query->get();

        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuans, 'finance');

        return $this->exportService->exportToCSV(
            'verifikasi_pengajuan_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function exportXlsx(Request $request)
    {
        $query = $this->applyPendingApprovalFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
        );

        $pengajuans = $query->get();
        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuans, 'finance');

        return $this->exportService->exportToXlsx(
            'verifikasi_pengajuan_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Verifikasi Finance']
        );
    }

    public function exportPdf(Request $request)
    {
        $query = $this->applyPendingApprovalFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
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
        $query = $this->applyApprovalHistoryFilters(
            Pengajuan::query()->with([
                'user:id,name',
                'departemen:departemen_id,nama_departemen',
                'kategori:kategori_id,nama_kategori',
                'validasiAi',
            ]),
            $request
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
        $query = $this->applyApprovalHistoryFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
        );

        $pengajuans = $query->get();

        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuans, 'finance');

        return $this->exportService->exportToCSV(
            'riwayat_approval_finance_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function historyExportXlsx(Request $request)
    {
        $query = $this->applyApprovalHistoryFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
        );

        $pengajuans = $query->get();
        $headers = $this->getPengajuanCsvHeaders('finance');
        $data = $this->mapPengajuanForCsv($pengajuans, 'finance');

        return $this->exportService->exportToXlsx(
            'riwayat_approval_finance_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Riwayat Approval']
        );
    }

    public function historyExportPdf(Request $request)
    {
        $query = $this->applyApprovalHistoryFilters(
            Pengajuan::query()->with('user', 'departemen', 'kategori'),
            $request
        );

        $pengajuans = $query->get();
        $totalNominal = $pengajuans->sum('nominal');
        $processedDates = $pengajuans->map(fn ($item) => $item->tanggal_disetujui_finance ?? $item->created_at)->filter();

        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
            : ($processedDates->min() ? \Carbon\Carbon::parse($processedDates->min())->startOfDay() : \Carbon\Carbon::now()->subMonths(1)->startOfDay());

        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
            : ($processedDates->max() ? \Carbon\Carbon::parse($processedDates->max())->endOfDay() : \Carbon\Carbon::now()->endOfDay());

        return $this->exportService->exportToPDF(
            'riwayat_approval_finance_'.date('Y-m-d').'.pdf',
            'dashboard.finance.approval.pdf.approval-history',
            compact('pengajuans', 'startDate', 'endDate', 'totalNominal'),
            ['orientation' => 'landscape']
        );
    }

    private function applyPendingApprovalFilters($query, Request $request)
    {
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

        return $query->orderBy('pengajuan.tanggal_pengajuan', 'desc');
    }

    private function applyApprovalHistoryFilters($query, Request $request)
    {
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

        return $query->orderByRaw('COALESCE(pengajuan.tanggal_disetujui_finance, pengajuan.created_at) DESC');
    }

    /**
     * Check if pengajuan is eligible for auto-approval
     *
     * Criteria:
     * - AI validation passed (not warning/fail)
     * - Nominal & date & vendor matches 100% with OCR (no manual adjustment)
     * - No budget exceeded issue
     * - COA is properly mapped from category
     * - Kas/Bank is properly selected
     */
    private function isEligibleForAutoApproval(Pengajuan $pengajuan): bool
    {
        try {
            // 1. Check AI validation status
            if ($pengajuan->status_validasi !== \App\Enums\ValidationStatus::VALID) {
                return false; // Only auto-approve if AI validation is 'valid'
            }

            // 2. Check if nominal and date matches are perfect (100%)
            // We look at confidence_score for nominal and tanggal validation records
            $nominalValidasi = $pengajuan->validasiAi->where('jenis_validasi', 'nominal')->first();
            $tanggalValidasi = $pengajuan->validasiAi->where('jenis_validasi', 'tanggal')->first();

            $nominalScore = $nominalValidasi ? $nominalValidasi->confidence_score : 0;
            $tanggalScore = $tanggalValidasi ? $tanggalValidasi->confidence_score : 0;

            if ($nominalScore < 100 || $tanggalScore < 100) {
                return false; // Require 100% match for auto-approval
            }

            // 3. Check budget status
            if ($pengajuan->departemen) {
                $budgetLimit = (float) ($pengajuan->departemen->budget_limit ?? 0);
                if ($budgetLimit > 0) {
                    $startOfMonth = $pengajuan->tanggal_transaksi->copy()->startOfMonth();
                    $endOfMonth = $pengajuan->tanggal_transaksi->copy()->endOfMonth();

                    $currentMonthUsage = Pengajuan::where('departemen_id', $pengajuan->departemen_id)
                        ->whereBetween('tanggal_transaksi', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                        ->whereNotIn('status', [PengajuanStatus::DITOLAK_ATASAN->value, PengajuanStatus::DITOLAK_FINANCE->value])
                        ->sum('nominal');

                    if ($currentMonthUsage > $budgetLimit) {
                        return false; // Budget exceeded
                    }
                }
            }

            // 4. Check if COA is properly set (from category default or history)
            if (! $pengajuan->coa_id) {
                return false; // COA must be set
            }

            // All criteria met - eligible for auto-approval
            return true;

        } catch (\Exception $e) {
            Log::warning('Auto-approval eligibility check failed: '.$e->getMessage());

            return false; // Fail safely - don't auto-approve on error
        }
    }
}
