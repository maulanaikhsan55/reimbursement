<?php

namespace App\Http\Controllers\Finance\Reports;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\Departemen;
use App\Models\Jurnal;
use App\Models\KasBank;
use App\Models\KategoriBiaya;
use App\Models\Pengajuan;
use App\Services\AccurateService;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $exportService;

    protected $accurateService;

    public function __construct(ReportExportService $exportService, AccurateService $accurateService)
    {
        $this->exportService = $exportService;
        $this->accurateService = $accurateService;
    }

    private function parseRequestDate(?string $value, bool $endOfDay = false): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    private function resolveDateRangeFromQuery($query, string $column): array
    {
        $startDate = $this->parseRequestDate(request('start_date'));
        $endDate = $this->parseRequestDate(request('end_date'), true);

        if (! $startDate) {
            $minDate = (clone $query)->min($column);
            if ($minDate) {
                $startDate = Carbon::parse($minDate)->startOfDay();
            }
        }

        if (! $endDate) {
            $maxDate = (clone $query)->max($column);
            if ($maxDate) {
                $endDate = Carbon::parse($maxDate)->endOfDay();
            }
        }

        $startDate ??= Carbon::now()->subMonths(1)->startOfDay();
        $endDate ??= Carbon::now()->endOfDay();

        return [$startDate, $endDate];
    }

    public function budgetAudit(Request $request)
    {
        $year = (int) $request->get('year', date('Y'));
        $departemenId = $request->get('departemen_id');
        $basis = $request->get('basis', 'komitmen');

        if (! in_array($basis, ['komitmen', 'realisasi'], true)) {
            $basis = 'komitmen';
        }

        $departemenQuery = Departemen::query();
        if ($departemenId) {
            $departemenQuery->where('departemen_id', $departemenId);
        }
        $departemens = $departemenQuery->orderBy('nama_departemen')->get();

        if ($basis === 'realisasi') {
            $monthlyUsageRows = Pengajuan::query()
                ->selectRaw('departemen_id, MONTH(COALESCE(tanggal_pencairan, DATE(updated_at))) as month_num, SUM(nominal) as total_nominal')
                ->where('status', PengajuanStatus::DICAIRKAN->value)
                ->where(function ($query) use ($year) {
                    $query->whereYear('tanggal_pencairan', $year)
                        ->orWhere(function ($fallbackQuery) use ($year) {
                            $fallbackQuery->whereNull('tanggal_pencairan')
                                ->whereYear('updated_at', $year);
                        });
                })
                ->when($departemenId, fn ($query) => $query->where('departemen_id', $departemenId))
                ->groupBy('departemen_id', 'month_num')
                ->get();
        } else {
            $monthlyUsageRows = Pengajuan::query()
                ->selectRaw('departemen_id, MONTH(tanggal_transaksi) as month_num, SUM(nominal) as total_nominal')
                ->whereYear('tanggal_transaksi', $year)
                ->whereNotIn('status', [
                    PengajuanStatus::DITOLAK_ATASAN->value,
                    PengajuanStatus::DITOLAK_FINANCE->value,
                ])
                ->when($departemenId, fn ($query) => $query->where('departemen_id', $departemenId))
                ->groupBy('departemen_id', 'month_num')
                ->get();
        }

        $usageByDeptMonth = [];
        foreach ($monthlyUsageRows as $row) {
            $usageByDeptMonth[$row->departemen_id][(int) $row->month_num] = (float) $row->total_nominal;
        }

        $auditData = [];
        $totalAnnualBudget = 0.0;
        $totalYearUsage = 0.0;
        $totalOverrun = 0.0;
        $departemenOverLimit = 0;

        foreach ($departemens as $dept) {
            $limit = (float) $dept->budget_limit;
            $annualBudget = $limit * 12;

            $monthlyUsage = [];
            $overBudgetMonths = 0;
            $overrunTotal = 0.0;

            for ($m = 1; $m <= 12; $m++) {
                $usage = (float) ($usageByDeptMonth[$dept->departemen_id][$m] ?? 0);
                $monthlyUsage[$m] = $usage;

                if ($limit > 0 && $usage > $limit) {
                    $overBudgetMonths++;
                    $overrunTotal += ($usage - $limit);
                } elseif ($limit <= 0 && $usage > 0) {
                    $overBudgetMonths++;
                    $overrunTotal += $usage;
                }
            }

            $totalYear = array_sum($monthlyUsage);
            $avgMonthly = $totalYear / 12;
            $utilizationPercent = $annualBudget > 0 ? ($totalYear / $annualBudget) * 100 : 0;

            $statusLabel = $overBudgetMonths > 0
                ? 'Melebihi'
                : (($utilizationPercent >= 85) ? 'Waspada' : 'Aman');
            $statusColor = $overBudgetMonths > 0
                ? '#dc2626'
                : (($utilizationPercent >= 85) ? '#b45309' : '#059669');

            $auditData[] = [
                'departemen' => $dept,
                'monthly_usage' => $monthlyUsage,
                'total_year' => $totalYear,
                'avg_monthly' => $avgMonthly,
                'budget_limit' => $limit,
                'annual_budget' => $annualBudget,
                'utilization_percent' => $utilizationPercent,
                'over_budget_months' => $overBudgetMonths,
                'overrun_total' => $overrunTotal,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
            ];

            $totalAnnualBudget += $annualBudget;
            $totalYearUsage += $totalYear;
            $totalOverrun += $overrunTotal;
            if ($overBudgetMonths > 0) {
                $departemenOverLimit++;
            }
        }

        $summary = [
            'total_departemen' => $departemens->count(),
            'total_annual_budget' => $totalAnnualBudget,
            'total_year_usage' => $totalYearUsage,
            'overall_utilization' => $totalAnnualBudget > 0 ? (($totalYearUsage / $totalAnnualBudget) * 100) : 0,
            'total_overrun' => $totalOverrun,
            'departemen_over_limit' => $departemenOverLimit,
        ];

        $allDepartemens = Departemen::orderBy('nama_departemen')->get();

        return view('dashboard.finance.reports.budget_audit', compact('auditData', 'year', 'departemenId', 'allDepartemens', 'basis', 'summary'));
    }

    /**
     * Laporan Operasional: Index Pengajuan yang Dicairkan
     */
    public function index()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Pengajuan::query()->where('status', 'dicairkan'),
            'tanggal_pencairan'
        );

        $pengajuanQuery = Pengajuan::where('status', 'dicairkan')
            ->whereBetween('tanggal_pencairan', [$startDate, $endDate]);

        if (request('departemen_id')) {
            $pengajuanQuery->where('departemen_id', request('departemen_id'));
        }

        $pengajuan = $pengajuanQuery->with(['user', 'departemen', 'kategori'])
            ->orderBy('tanggal_pencairan', 'desc')
            ->paginate(config('app.pagination.reports'));

        $totalNominal = $pengajuanQuery->sum('nominal');
        $totalCount = $pengajuanQuery->count();

        $departemen = Departemen::orderBy('nama_departemen')->get();
        $kategori = KategoriBiaya::where('is_active', true)->orderBy('nama_kategori')->get();

        $departemenBreakdown = Pengajuan::whereIn('pengajuan_id', $pengajuanQuery->pluck('pengajuan_id'))
            ->selectRaw('departemen_id, COUNT(*) as count, SUM(nominal) as total')
            ->groupBy('departemen_id')
            ->get()
            ->keyBy('departemen_id');

        return view('dashboard.finance.reports.index', compact(
            'pengajuan',
            'startDate',
            'endDate',
            'totalNominal',
            'totalCount',
            'departemen',
            'kategori',
            'departemenBreakdown'
        ));
    }

    /**
     * Ringkasan Statistik Laporan
     */
    public function summary()
    {
        $totalCairkan = Pengajuan::where('status', 'dicairkan')->count();
        $totalNominal = Pengajuan::where('status', 'dicairkan')->sum('nominal');

        $cairkanByMonth = Pengajuan::where('status', 'dicairkan')
            ->selectRaw('DATE_FORMAT(tanggal_pencairan, "%Y-%m") as month, COUNT(*) as count, SUM(nominal) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $cairkanByDept = Pengajuan::where('status', 'dicairkan')
            ->with('departemen')
            ->selectRaw('departemen_id, COUNT(*) as count, SUM(nominal) as total')
            ->groupBy('departemen_id')
            ->orderBy('total', 'desc')
            ->get();

        return view('dashboard.finance.reports.summary', compact(
            'totalCairkan',
            'totalNominal',
            'cairkanByMonth',
            'cairkanByDept'
        ));
    }

    /**
     * Export CSV Laporan Operasional
     */
    public function export()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Pengajuan::query()->where('status', 'dicairkan'),
            'tanggal_pencairan'
        );

        $pengajuanQuery = Pengajuan::where('status', 'dicairkan')
            ->whereBetween('tanggal_pencairan', [$startDate, $endDate]);

        if (request('departemen_id')) {
            $pengajuanQuery->where('departemen_id', request('departemen_id'));
        }

        $pengajuan = $pengajuanQuery->with(['user', 'departemen', 'kategori'])->orderBy('tanggal_pencairan', 'desc')->get();

        $headers = ['No', 'Nomor Pengajuan', 'Pegawai', 'Email', 'Departemen', 'Kategori Biaya', 'Deskripsi', 'Nominal (IDR)', 'Tanggal Pencairan'];

        $data = $pengajuan->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nomor_pengajuan,
                $item->user->name,
                $item->user->email,
                $item->departemen->nama_departemen,
                $item->kategori ? $item->kategori->nama_kategori : '',
                $item->deskripsi,
                'Rp '.number_format((float) $item->nominal, 0, ',', '.'),
                $item->tanggal_pencairan->format('d/m/Y'),
            ];
        });

        return $this->exportService->exportToCSV(
            'laporan_pencairan_'.date('Y-m-d_His').'.csv',
            $headers,
            $data
        );
    }

    /* -------------------------------------------------------------------------- */
    /*                         LAPORAN AKUNTANSI (FINANCIAL) */
    /* -------------------------------------------------------------------------- */

    private function getJurnalUmumData()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );
        $coaId = request('coa_id');
        $search = request('search');

        $query = Jurnal::query();

        // Jika filter COA aktif, kita harus cari semua nomor_ref yang mengandung COA tersebut
        // agar jurnal tetap tampil balance (debit & kredit)
        if ($coaId) {
            $matchingRefs = Jurnal::where('coa_id', $coaId)
                ->whereBetween('tanggal_posting', [$startDate, $endDate])
                ->pluck('nomor_ref')
                ->unique();

            $query->whereIn('nomor_ref', $matchingRefs);
        } else {
            $query->whereBetween('tanggal_posting', [$startDate, $endDate]);
        }

        if ($search) {
            $query->where('nomor_ref', 'LIKE', "%{$search}%");
        }

        return $query->with('coa', 'pengajuan', 'postedBy')
            ->orderBy('tanggal_posting')
            ->orderBy('nomor_ref')
            ->orderBy('jurnal_id')
            ->get();
    }

    public function jurnalUmum()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );
        $coaId = request('coa_id');

        $allJournal = $this->getJurnalUmumData();

        $groupedJournal = $allJournal->groupBy('nomor_ref')->map(function ($entries) {
            $totalGroupDebit = $entries->where('tipe_posting', 'debit')->sum('nominal');
            $totalGroupCredit = $entries->where('tipe_posting', 'credit')->sum('nominal');
            $isBalanced = abs($totalGroupDebit - $totalGroupCredit) < 0.01;

            return [
                'entries' => $entries->sortBy(function ($e) {
                    return $e->tipe_posting === 'debit' ? 0 : 1;
                }),
                'nomor_ref' => $entries->first()->nomor_ref,
                'tanggal' => $entries->first()->tanggal_posting,
                'pengajuan' => $entries->first()->pengajuan,
                'total_debit' => $totalGroupDebit,
                'total_credit' => $totalGroupCredit,
                'is_balanced' => $isBalanced,
            ];
        })->values();

        $currentPage = request('page', 1);
        $perPage = config('app.pagination.reports', 15);
        $paginatedJournal = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedJournal->forPage($currentPage, $perPage),
            $groupedJournal->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $coas = COA::where('is_active', true)->orderBy('kode_coa')->get();

        $totalDebit = $allJournal->where('tipe_posting', 'debit')->sum('nominal');
        $totalCredit = $allJournal->where('tipe_posting', 'credit')->sum('nominal');

        return view('dashboard.finance.reports.jurnal_umum', compact(
            'paginatedJournal',
            'startDate',
            'endDate',
            'coas',
            'coaId',
            'totalDebit',
            'totalCredit'
        ));
    }

    public function jurnalUmumExportCsv()
    {
        $allJournal = $this->getJurnalUmumData();

        $headers = ['No', 'Tanggal', 'No. Ref', 'Pengajuan', 'Kode COA', 'Nama Akun', 'Deskripsi', 'Debit (IDR)', 'Kredit (IDR)'];

        $data = $allJournal->values()->map(function ($journal, $index) {
            return [
                $index + 1,
                $journal->tanggal_posting->format('d/m/Y'),
                $journal->nomor_ref,
                $journal->pengajuan ? $journal->pengajuan->nomor_pengajuan : '-',
                $journal->coa->kode_coa,
                $journal->coa->nama_coa,
                $journal->deskripsi,
                $journal->tipe_posting == 'debit' ? number_format((float) $journal->nominal, 0, ',', '.') : '-',
                $journal->tipe_posting == 'credit' ? number_format((float) $journal->nominal, 0, ',', '.') : '-',
            ];
        });

        return $this->exportService->exportToCSV(
            'jurnal_umum_'.date('Y-m-d_His').'.csv',
            $headers,
            $data
        );
    }

    public function jurnalUmumExportXlsx()
    {
        $allJournal = $this->getJurnalUmumData();

        $headers = ['No', 'Tanggal', 'No. Ref', 'Pengajuan', 'Kode COA', 'Nama Akun', 'Deskripsi', 'Debit (IDR)', 'Kredit (IDR)'];

        $data = $allJournal->values()->map(function ($journal, $index) {
            return [
                $index + 1,
                $journal->tanggal_posting->format('d/m/Y'),
                $journal->nomor_ref,
                $journal->pengajuan ? $journal->pengajuan->nomor_pengajuan : '-',
                $journal->coa->kode_coa,
                $journal->coa->nama_coa,
                $journal->deskripsi,
                $journal->tipe_posting == 'debit' ? number_format((float) $journal->nominal, 0, ',', '.') : '-',
                $journal->tipe_posting == 'credit' ? number_format((float) $journal->nominal, 0, ',', '.') : '-',
            ];
        });

        return $this->exportService->exportToXlsx(
            'jurnal_umum_'.date('Y-m-d_His').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Jurnal Umum']
        );
    }

    public function jurnalUmumExportPdf()
    {
        $allJournal = $this->getJurnalUmumData();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );

        $groupedJournal = $allJournal->groupBy('nomor_ref')->map(function ($entries) {
            return [
                'entries' => $entries->sortBy(function ($e) {
                    return $e->tipe_posting === 'debit' ? 0 : 1;
                }),
                'nomor_ref' => $entries->first()->nomor_ref,
                'tanggal' => $entries->first()->tanggal_posting,
                'pengajuan' => $entries->first()->pengajuan,
            ];
        })->values();

        $totalDebit = $allJournal->where('tipe_posting', 'debit')->sum('nominal');
        $totalCredit = $allJournal->where('tipe_posting', 'credit')->sum('nominal');

        return $this->exportService->exportToPDF(
            'jurnal_umum_'.date('Y-m-d_His').'.pdf',
            'dashboard.finance.reports.pdf.jurnal_umum',
            compact('groupedJournal', 'startDate', 'endDate', 'totalDebit', 'totalCredit'),
            ['orientation' => 'landscape']
        );
    }

    private function getBukuBesarData()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );

        return Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])
            ->with('coa')
            ->get();
    }

    public function bukuBesar()
    {
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );
        $coaId = request('coa_id');

        $coaQuery = COA::where('is_active', true);
        if ($coaId) {
            $coaQuery->where('coa_id', $coaId);
        }
        $allCoas = $coaQuery->orderBy('kode_coa')->get();

        // OPTIMASI ULTRA: Ambil semua mutasi dalam satu query untuk menghindari N+1
        $coaIds = $allCoas->pluck('coa_id')->toArray();
        $allMutations = Jurnal::whereIn('coa_id', $coaIds)
            ->where('tanggal_posting', '<=', $endDate)
            ->with(['coa', 'pengajuan'])
            ->orderBy('tanggal_posting')
            ->orderBy('jurnal_id')
            ->get()
            ->groupBy('coa_id');

        $ledger = collect();

        foreach ($allCoas as $coa) {
            $initialBalance = $coa->saldo ?? 0;
            $asOfDate = $coa->as_of_date ? Carbon::parse($coa->as_of_date) : Carbon::parse('2000-01-01');
            $isDebitNormal = in_array($coa->tipe_akun, ['asset', 'expense']);
            $lastSync = $coa->last_sync_at;

            // Ambil mutasi khusus akun ini dari collection (In-Memory)
            $coaMutations = $allMutations->get($coa->coa_id, collect());

            // 1. Calculate Saldo Awal (Balance before start_date) menggunakan data yang sudah ditarik
            // LOGIKA SYNC-AWARE: Transaksi dianggap "Historical" (sudah masuk saldo sync) jika:
            // posted_at <= last_sync_at. Sisanya adalah "Mutasi".
            if ($asOfDate->lt($startDate)) {
                // MAJU: Dari Sync Point (asOfDate) ke Start Date
                $preMutations = $coaMutations->filter(function ($m) use ($asOfDate, $startDate, $lastSync) {
                    $isAfterSync = $m->tanggal_posting->gt($asOfDate) || ($lastSync && $m->tanggal_posting->equalTo($asOfDate) && $m->posted_at && $m->posted_at->gt($lastSync));

                    return $isAfterSync && $m->tanggal_posting->lt($startDate);
                });
            } else {
                // MUNDUR: Dari Sync Point (asOfDate) ke Start Date
                $preMutations = $coaMutations->filter(function ($m) use ($asOfDate, $startDate, $lastSync) {
                    $isInSync = $m->tanggal_posting->lt($asOfDate) || ($lastSync && $m->tanggal_posting->equalTo($asOfDate) && (! $m->posted_at || $m->posted_at->lte($lastSync)));

                    return $m->tanggal_posting->gte($startDate) && $isInSync;
                });
            }

            $preDebit = $preMutations->where('tipe_posting', 'debit')->sum('nominal');
            $preCredit = $preMutations->where('tipe_posting', 'credit')->sum('nominal');

            if ($asOfDate->lt($startDate)) {
                $saldoAwal = $initialBalance + ($isDebitNormal ? ($preDebit - $preCredit) : ($preCredit - $preDebit));
            } else {
                $saldoAwal = $initialBalance - ($isDebitNormal ? ($preDebit - $preCredit) : ($preCredit - $preDebit));
            }

            // 2. Get mutations within range (In-Memory)
            $entries = $coaMutations->filter(function ($m) use ($startDate, $endDate) {
                return $m->tanggal_posting >= $startDate && $m->tanggal_posting <= $endDate;
            });

            if ($entries->isEmpty() && $saldoAwal == 0) {
                continue;
            }

            $currentSaldo = $saldoAwal;
            $processedEntries = $entries->map(function ($entry) use (&$currentSaldo, $isDebitNormal) {
                if ($entry->tipe_posting === 'debit') {
                    $currentSaldo += $isDebitNormal ? $entry->nominal : -$entry->nominal;
                } else {
                    $currentSaldo += $isDebitNormal ? -$entry->nominal : $entry->nominal;
                }
                $entry->running_balance = $currentSaldo;

                return $entry;
            });

            $totalDebit = $entries->where('tipe_posting', 'debit')->sum('nominal');
            $totalCredit = $entries->where('tipe_posting', 'credit')->sum('nominal');

            $ledger->put($coa->coa_id, [
                'coa' => $coa,
                'saldo_awal' => $saldoAwal,
                'entries' => $processedEntries,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'saldo_akhir' => $currentSaldo,
                'is_debit_normal' => $isDebitNormal,
            ]);
        }

        $totalDebitAll = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])->where('tipe_posting', 'debit')->sum('nominal');
        $totalCreditAll = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])->where('tipe_posting', 'credit')->sum('nominal');

        return view('dashboard.finance.reports.buku_besar', [
            'ledger' => $ledger,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'coaId' => $coaId,
            'coas' => COA::where('is_active', true)->orderBy('kode_coa')->get(),
            'totalDebit' => $totalDebitAll,
            'totalCredit' => $totalCreditAll,
        ]);
    }

    public function reconciliationDashboard()
    {
        $kasBank = KasBank::with('coa')->where('is_active', true)->get();

        $reconData = $kasBank->map(function ($kb) {
            $coa = $kb->coa;
            if (! $coa) {
                return null;
            }

            $initialBalance = $coa->saldo ?? 0;
            $asOfDate = $coa->as_of_date ? Carbon::parse($coa->as_of_date) : Carbon::parse('2000-01-01');

            // Hitung mutasi (Sync-Aware):
            // Hanya ambil transaksi yang diposting SETELAH waktu sinkronisasi terakhir,
            // atau jika tidak ada timestamp sync, gunakan tanggal posting > as_of_date.
            $query = Jurnal::where('coa_id', $coa->coa_id);

            if ($coa->last_sync_at) {
                // Jika ada timestamp sync, kita bisa sangat presisi:
                // Ambil semua transaksi yang diposting setelah sync (kapanpun tanggal postingnya)
                // ATAU yang tanggal postingnya > as_of_date (untuk backup jika posted_at tidak akurat)
                $query->where(function ($q) use ($coa, $asOfDate) {
                    $q->where('posted_at', '>', $coa->last_sync_at)
                        ->orWhere('tanggal_posting', '>', $asOfDate);
                });
            } else {
                $query->where('tanggal_posting', '>', $asOfDate);
            }

            $debit = (clone $query)->where('tipe_posting', 'debit')->sum('nominal');
            $credit = (clone $query)->where('tipe_posting', 'credit')->sum('nominal');

            $localBalance = $initialBalance + ($debit - $credit);

            return [
                'kas_bank_id' => $kb->kas_bank_id,
                'coa_id' => $coa->coa_id,
                'kode' => $kb->kode_kas_bank,
                'nama' => $kb->nama_kas_bank,
                'accurate_id' => $kb->accurate_id,
                'local_balance' => (float) $localBalance,
                'last_sync' => $kb->last_sync_at,
            ];
        })->filter();

        return view('dashboard.finance.reports.reconciliation', compact('reconData'));
    }

    public function reconcileLedger(Request $request)
    {
        try {
            $coaId = $request->coa_id;
            $localBalance = $request->local_balance;

            $coa = COA::findOrFail($coaId);

            if (! $coa->accurate_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun COA ini belum terhubung ke Accurate',
                ]);
            }

            $result = $this->accurateService->getAccountBalance($coa->accurate_id);

            if ($result['success']) {
                $accurateBalance = $result['balance'];
                $syncTime = now();

                // AUTO-UPDATE: Selalu perbarui saldo lokal saat pengecekan rekonsiliasi
                // agar as_of_date menjadi 'now()' dan menghindari double counting mutasi
                // Gunakan fill dan saveQuietly untuk menghindari trigger exception di model booted()
                $coa->fill([
                    'saldo' => $accurateBalance,
                    'as_of_date' => $syncTime->toDateString(),
                    'last_sync_at' => $syncTime,
                ]);
                $coa->saveQuietly();

                // Update KasBank pendamping jika ada
                KasBank::where('coa_id', $coa->coa_id)->update([
                    'saldo' => $accurateBalance,
                    'as_of_date' => $syncTime->toDateString(),
                    'last_sync_at' => $syncTime,
                ]);

                // Re-calculate local balance after sync update
                // Karena as_of_date sudah 'now()', maka mutasi seharusnya 0 (kecuali ada transaksi yang diposting tepat setelah sync)
                $newLocalBalance = $accurateBalance;

                // Cek jika ada transaksi yang diposting SETELAH microsecond sync ini (sangat jarang tapi mungkin)
                $extraDebit = Jurnal::where('coa_id', $coa->coa_id)
                    ->where('posted_at', '>', $syncTime)
                    ->where('tipe_posting', 'debit')
                    ->sum('nominal');
                $extraCredit = Jurnal::where('coa_id', $coa->coa_id)
                    ->where('posted_at', '>', $syncTime)
                    ->where('tipe_posting', 'credit')
                    ->sum('nominal');

                $finalLocalBalance = $newLocalBalance + ($extraDebit - $extraCredit);

                $diff = abs($accurateBalance - $finalLocalBalance);
                $isMatch = $diff < 0.01;

                $discrepancies = [];
                if (! $isMatch) {
                    // Try to find transactions in Accurate that are NOT in our Jurnal
                    // Increase lookback to 90 days to catch older manual adjustments/opening balances
                    $lookbackDays = 90;
                    $startDate = Carbon::now()->subDays($lookbackDays)->toDateString();
                    $endDate = Carbon::now()->toDateString();

                    $history = $this->accurateService->getAccountTransactions(
                        $coa->accurate_id,
                        $startDate,
                        $endDate
                    );

                    if ($history['success']) {
                        // Cek di seluruh jurnal lokal apakah nomor_ref ini sudah pernah di-sync
                        $localRefs = Jurnal::where('tanggal_posting', '>=', Carbon::now()->subDays($lookbackDays + 7))
                            ->pluck('nomor_ref')
                            ->map(fn ($ref) => trim($ref))
                            ->toArray();

                        foreach ($history['data'] as $accTrans) {
                            $cleanRef = trim($accTrans['number']);
                            if (! in_array($cleanRef, $localRefs)) {
                                $discrepancies[] = [
                                    'accurate_id' => $accTrans['id'], // ID for detail fetch
                                    'number' => $accTrans['number'],
                                    'date' => $accTrans['transDate'],
                                    'amount' => $accTrans['totalAmount'],
                                    'description' => $accTrans['description'],
                                    'trans_type' => $accTrans['transType'] ?? 'JOURNAL_VOUCHER',
                                ];
                            }
                            if (count($discrepancies) >= 5) {
                                break;
                            }
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'is_match' => $isMatch,
                    'accurate_balance' => $accurateBalance,
                    'local_balance' => (float) $finalLocalBalance,
                    'diff' => $diff,
                    'discrepancies' => $discrepancies,
                ]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reconciliation error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync a specific missing transaction from Accurate to Web database
     */
    public function syncMissingTransaction(Request $request)
    {
        $accurateId = $request->accurate_id;
        $coaId = $request->coa_id; // Optional: Current COA we are reconciling
        $transType = $request->trans_type ?? 'JOURNAL_VOUCHER';

        if (! $accurateId) {
            return response()->json(['success' => false, 'message' => 'ID Transaksi Accurate diperlukan']);
        }

        // Fetch detail from Accurate using polymorphic type
        $result = $this->accurateService->getTransactionDetail($accurateId, $transType);

        if (! $result['success']) {
            return response()->json($result);
        }

        $jv = $result['data'];

        // Find the line that belongs to our COA
        $coa = COA::find($coaId);
        $targetLine = null;

        // Accurate details can be in many possible keys based on transaction type
        $details = $jv['journalVoucherDetails'] ??
                   $jv['details'] ??
                   $jv['detailJournalVoucher'] ??
                   $jv['journalVoucherLines'] ??
                   $jv['lines'] ??
                   $jv['cashOutDetails'] ??
                   $jv['cashInDetails'] ??
                   $jv['otherDepositDetails'] ??
                   $jv['otherPaymentDetails'] ??
                   $jv['bankTransferDetails'] ??
                   null;

        if (! $details) {
            $availableKeys = array_keys($jv);
            \Illuminate\Support\Facades\Log::error('Accurate Transaction Detail missing details key', [
                'id' => $accurateId,
                'type' => $transType,
                'available_keys' => $availableKeys,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Struktur data Accurate ($transType) tidak sesuai (Missing details). Keys found: ".implode(', ', $availableKeys),
                'data_received' => $jv,
            ]);
        }

        // Create Jurnal Entries for all lines to maintain balance
        try {
            DB::beginTransaction();

            // Delete existing entries for this reference to allow re-syncing/fixing
            $existingJurnal = Jurnal::where('nomor_ref', $jv['number'])->first();
            $linkedPengajuanId = $existingJurnal ? $existingJurnal->pengajuan_id : null;

            // Jika tidak ada di jurnal, coba cari di tabel pengajuan berdasarkan nomor_ref atau deskripsi
            if (! $linkedPengajuanId) {
                $description = $jv['description'] ?? '';
                // Cari pola PJ + 10 digit angka (misal PJ2025xxxxxx atau PJ2026xxxxxx)
                if (preg_match('/PJ\d{10}/', $description, $matches)) {
                    $pengajuan = Pengajuan::where('nomor_pengajuan', $matches[0])->first();
                    $linkedPengajuanId = $pengajuan ? $pengajuan->pengajuan_id : null;
                }
            }

            Jurnal::where('nomor_ref', $jv['number'])->delete();

            // Safe date parsing with multiple fallback keys
            $rawDate = $jv['transDate'] ?? $jv['date'] ?? null;
            if (! $rawDate) {
                // If no date found at top level, check if it's in the first detail line (rare but possible)
                $rawDate = $details[0]['transDate'] ?? $details[0]['date'] ?? null;
            }

            try {
                if ($rawDate) {
                    if (str_contains($rawDate, '/')) {
                        $tanggalPosting = Carbon::createFromFormat('d/m/Y', $rawDate);
                    } else {
                        $tanggalPosting = Carbon::parse($rawDate);
                    }
                } else {
                    $tanggalPosting = now(); // Fallback if still null
                    \Illuminate\Support\Facades\Log::warning("Accurate transaction {$jv['number']} missing date key");
                }
            } catch (\Exception $e) {
                $tanggalPosting = Carbon::parse($rawDate ?? now());
            }

            $syncedCount = 0;
            $newCoas = [];

            // 1. Process Main Details Lines
            foreach ($details as $line) {
                $lineAccountNo = $line['glAccount']['no'] ?? $line['accountNo'] ?? $line['account']['no'] ?? null;
                if (! $lineAccountNo) {
                    continue;
                }

                $localCoa = COA::where('kode_coa', $lineAccountNo)->first();

                if (! $localCoa) {
                    $coaResult = $this->accurateService->getAccountByNumber($lineAccountNo);
                    if ($coaResult['success']) {
                        $localCoa = COA::create($coaResult['data']);
                        $newCoas[] = $lineAccountNo;
                    }
                }

                if ($localCoa) {
                    // Determine Posting Type
                    $rawAmount = $line['amount'] ?? 0;
                    $tipePosting = 'debit';

                    if (isset($line['amountType'])) {
                        // For Journal Vouchers
                        $tipePosting = strtolower($line['amountType']);
                    } else {
                        // Fallback/Default for other types
                        if ($transType === 'CASH_OUT' || $transType === 'OTHER_PAYMENT') {
                            $tipePosting = 'debit'; // Expense lines are debit
                        } elseif ($transType === 'CASH_IN' || $transType === 'OTHER_DEPOSIT') {
                            $tipePosting = 'credit'; // Income lines are credit
                        } else {
                            $tipePosting = $rawAmount >= 0 ? 'debit' : 'credit';
                        }
                    }

                    // Handle negative amounts by flipping the posting type
                    if ($rawAmount < 0) {
                        $tipePosting = ($tipePosting === 'debit') ? 'credit' : 'debit';
                    }

                    Jurnal::create([
                        'pengajuan_id' => $linkedPengajuanId,
                        'coa_id' => $localCoa->coa_id,
                        'nominal' => abs($rawAmount),
                        'tipe_posting' => $tipePosting,
                        'tanggal_posting' => $tanggalPosting,
                        'nomor_ref' => $jv['number'],
                        'deskripsi' => $line['memo'] ?? $jv['description'] ?? 'Imported from Accurate',
                        'posted_at' => now(),
                        'posted_by' => auth()->id() ?? 1,
                    ]);
                    $syncedCount++;
                }
            }

            // 2. Process Header Account (For CASH_IN / CASH_OUT)
            // Other Payment/Deposit has a 'bank' field which is the main account
            $headerAccountNo = $jv['bank']['no'] ?? $jv['cashAccount']['no'] ?? null;
            if ($headerAccountNo) {
                $headerCoa = COA::where('kode_coa', $headerAccountNo)->first();
                if (! $headerCoa) {
                    $coaResult = $this->accurateService->getAccountByNumber($headerAccountNo);
                    if ($coaResult['success']) {
                        $headerCoa = COA::create($coaResult['data']);
                        $newCoas[] = $headerAccountNo;
                    }
                }

                if ($headerCoa) {
                    $rawHeaderAmount = $jv['totalAmount'] ?? $jv['amount'] ?? 0;
                    $headerType = 'credit'; // Default for CASH_OUT

                    if ($transType === 'CASH_IN' || $transType === 'OTHER_DEPOSIT') {
                        $headerType = 'debit';
                    }

                    // Handle negative header amount (rare but possible in reversals)
                    if ($rawHeaderAmount < 0) {
                        $headerType = ($headerType === 'debit') ? 'credit' : 'debit';
                    }

                    Jurnal::create([
                        'pengajuan_id' => $linkedPengajuanId,
                        'coa_id' => $headerCoa->coa_id,
                        'nominal' => abs($rawHeaderAmount),
                        'tipe_posting' => $headerType,
                        'tanggal_posting' => $tanggalPosting,
                        'nomor_ref' => $jv['number'],
                        'deskripsi' => $jv['description'] ?? 'Imported from Accurate (Header)',
                        'posted_at' => now(),
                        'posted_by' => auth()->id() ?? 1,
                    ]);
                    $syncedCount++;
                }
            }

            if ($syncedCount === 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sinkronisasi: Tidak ada akun COA yang cocok atau dapat ditemukan.',
                ]);
            }

            DB::commit();

            $msg = "Transaksi {$jv['number']} berhasil disinkronkan ($syncedCount baris).";
            if (! empty($newCoas)) {
                $msg .= ' Menambahkan akun baru: '.implode(', ', array_unique($newCoas)).'.';
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: '.$e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ],
            ]);
        }
    }

    public function syncByRef(Request $request)
    {
        $ref = $request->nomor_ref;
        if (! $ref) {
            return response()->json(['success' => false, 'message' => 'No. Ref tidak boleh kosong']);
        }

        // Check various transaction types
        $types = ['JOURNAL_VOUCHER', 'CASH_OUT', 'CASH_IN'];
        $accurateId = null;
        $foundType = 'JOURNAL_VOUCHER';

        foreach ($types as $type) {
            $check = $this->accurateService->checkTransactionExists($ref, $type);
            if ($check['success'] && $check['exists']) {
                $accurateId = $check['data']['id'];
                $foundType = $type;
                break;
            }
        }

        if (! $accurateId) {
            return response()->json(['success' => false, 'message' => "Transaksi $ref tidak ditemukan di Accurate (Checked: JV, Cash In/Out)"]);
        }

        $syncRequest = new Request([
            'accurate_id' => $accurateId,
            'trans_type' => $foundType,
        ]);

        return $this->syncMissingTransaction($syncRequest);
    }

    public function bukuBesarExportCsv()
    {
        $entries = $this->getBukuBesarData();

        $ledger = $entries->groupBy('coa_id')->map(function ($items) {
            $coa = $items->first()->coa;
            $debit = $items->where('tipe_posting', 'debit')->sum('nominal');
            $credit = $items->where('tipe_posting', 'credit')->sum('nominal');

            $isDebitNormal = in_array($coa->tipe_akun, ['asset', 'expense']);
            $saldoAkhir = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

            return [
                'coa' => $coa,
                'debit' => $debit,
                'credit' => $credit,
                'saldo' => $saldoAkhir,
                'count' => $items->count(),
            ];
        })->sortBy('coa.kode_coa');

        $headers = ['No', 'Kode Akun', 'Nama Akun', 'Debit (IDR)', 'Kredit (IDR)', 'Saldo (IDR)', 'Jumlah Transaksi'];

        $data = $ledger->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item['coa']->kode_coa,
                $item['coa']->nama_coa,
                number_format((float) $item['debit'], 0, ',', '.'),
                number_format((float) $item['credit'], 0, ',', '.'),
                number_format((float) $item['saldo'], 0, ',', '.'),
                $item['count'],
            ];
        });

        return $this->exportService->exportToCSV(
            'buku_besar_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function bukuBesarExportXlsx()
    {
        $entries = $this->getBukuBesarData();

        $ledger = $entries->groupBy('coa_id')->map(function ($items) {
            $coa = $items->first()->coa;
            $debit = $items->where('tipe_posting', 'debit')->sum('nominal');
            $credit = $items->where('tipe_posting', 'credit')->sum('nominal');

            $isDebitNormal = in_array($coa->tipe_akun, ['asset', 'expense']);
            $saldoAkhir = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

            return [
                'coa' => $coa,
                'debit' => $debit,
                'credit' => $credit,
                'saldo' => $saldoAkhir,
                'count' => $items->count(),
            ];
        })->sortBy('coa.kode_coa');

        $headers = ['No', 'Kode Akun', 'Nama Akun', 'Debit (IDR)', 'Kredit (IDR)', 'Saldo (IDR)', 'Jumlah Transaksi'];

        $data = $ledger->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item['coa']->kode_coa,
                $item['coa']->nama_coa,
                number_format((float) $item['debit'], 0, ',', '.'),
                number_format((float) $item['credit'], 0, ',', '.'),
                number_format((float) $item['saldo'], 0, ',', '.'),
                $item['count'],
            ];
        });

        return $this->exportService->exportToXlsx(
            'buku_besar_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Buku Besar']
        );
    }

    public function bukuBesarExportPdf()
    {
        $entries = $this->getBukuBesarData();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query(),
            'tanggal_posting'
        );

        $ledger = $entries->groupBy('coa_id')->map(function ($items) {
            $coa = $items->first()->coa;
            $debit = $items->where('tipe_posting', 'debit')->sum('nominal');
            $credit = $items->where('tipe_posting', 'credit')->sum('nominal');

            $isDebitNormal = in_array($coa->tipe_akun, ['asset', 'expense']);
            $saldoAkhir = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

            return [
                'coa' => $coa,
                'debit' => $debit,
                'credit' => $credit,
                'saldo' => $saldoAkhir,
                'is_debit_normal' => $isDebitNormal,
                'count' => $items->count(),
            ];
        })->sortBy('coa.kode_coa');

        $totalDebit = $entries->where('tipe_posting', 'debit')->sum('nominal');
        $totalCredit = $entries->where('tipe_posting', 'credit')->sum('nominal');

        return $this->exportService->exportToPDF(
            'buku_besar_'.date('Y-m-d').'.pdf',
            'dashboard.finance.reports.pdf.buku_besar',
            compact('ledger', 'startDate', 'endDate', 'totalDebit', 'totalCredit'),
            ['orientation' => 'landscape']
        );
    }

    public function laporanArusKas()
    {
        $cashBankCoaIds = KasBank::pluck('coa_id')->toArray();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query()->whereIn('coa_id', $cashBankCoaIds),
            'tanggal_posting'
        );

        $query = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])
            ->whereIn('coa_id', $cashBankCoaIds)
            ->with(['coa', 'pengajuan.user', 'pengajuan.departemen', 'pengajuan.kategori']);

        if (request('kategori_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('kategori_id', request('kategori_id'));
            });
        }

        if (request('departemen_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('departemen_id', request('departemen_id'));
            });
        }

        $allEntries = $query->orderBy('tanggal_posting', 'desc')->get();

        // Optimized: Batch fetch all counterparts for all entries in one query
        $allRefNumbers = $allEntries->pluck('nomor_ref')->unique()->toArray();
        $allCounterparts = Jurnal::whereIn('nomor_ref', $allRefNumbers)
            ->with('coa')
            ->get()
            ->groupBy('nomor_ref');

        $activities = [
            'operasi' => ['entries' => collect(), 'total' => 0, 'label' => 'Arus Kas dari Aktivitas Operasi'],
            'investasi' => ['entries' => collect(), 'total' => 0, 'label' => 'Arus Kas dari Aktivitas Investasi'],
            'pendanaan' => ['entries' => collect(), 'total' => 0, 'label' => 'Arus Kas dari Aktivitas Pendanaan'],
            'lainnya' => ['entries' => collect(), 'total' => 0, 'label' => 'Arus Kas dari Aktivitas Lain-lain'],
        ];

        foreach ($allEntries as $entry) {
            // Optimized: Find counterpart from pre-fetched collection instead of querying database
            $relatedLines = $allCounterparts->get($entry->nomor_ref) ?? collect();

            // Find all OTHER lines in this transaction that are NOT cash/bank
            $counterparts = $relatedLines->filter(function ($line) use ($entry, $cashBankCoaIds) {
                return $line->jurnal_id != $entry->jurnal_id && ! in_array($line->coa_id, $cashBankCoaIds);
            });

            // If no non-cash counterpart (e.g. bank transfer between accounts),
            // just take any other line in the transaction
            if ($counterparts->isEmpty()) {
                $counterpart = $relatedLines->filter(function ($line) use ($entry) {
                    return $line->jurnal_id != $entry->jurnal_id;
                })->first();
            } else {
                // Take the one with the largest amount as the primary purpose of the transaction
                $counterpart = $counterparts->sortByDesc('nominal')->first();
            }

            $category = 'lainnya'; // Default
            if ($counterpart && $counterpart->coa) {
                $tipe = $counterpart->coa->tipe_akun; // Sudah mapped di AccurateService (asset, expense, revenue, liability, equity)
                $namaCoa = strtolower($counterpart->coa->nama_coa);
                $kodeCoa = $counterpart->coa->kode_coa;

                // 1. Aktivitas Operasi (Beban, Pendapatan, dan Aset/Kewajiban Lancar)
                if ($tipe === 'expense' || $tipe === 'revenue' || str_contains($namaCoa, 'operasional')) {
                    $category = 'operasi';
                }
                // 2. Aktivitas Investasi (Perolehan/Pelepasan Aset Tetap/Non-lancar)
                elseif ($tipe === 'asset') {
                    // Check if it's a long-term asset based on name or code
                    if (str_contains($namaCoa, 'tetap') || str_contains($namaCoa, 'kendaraan') ||
                        str_contains($namaCoa, 'bangunan') || str_contains($namaCoa, 'tanah') ||
                        str_contains($namaCoa, 'peralatan') || str_contains($namaCoa, 'mesin') ||
                        str_contains($namaCoa, 'inventaris') || str_contains($namaCoa, 'investasi') ||
                        str_starts_with($kodeCoa, '13') || str_starts_with($kodeCoa, '14')) {
                        $category = 'investasi';
                    } else {
                        $category = 'operasi'; // Current assets like receivables/prepaid are usually Operating
                    }
                }
                // 3. Aktivitas Pendanaan (Hutang Jangka Panjang, Modal, Dividen)
                elseif ($tipe === 'liability' || $tipe === 'equity') {
                    if (str_contains($namaCoa, 'pinjaman') || str_contains($namaCoa, 'modal') ||
                        str_contains($namaCoa, 'saham') || str_contains($namaCoa, 'deviden') ||
                        str_contains($namaCoa, 'prive') || str_contains($namaCoa, 'owner') ||
                        str_contains($namaCoa, 'hutang bank') || str_contains($namaCoa, 'loan') ||
                        str_starts_with($kodeCoa, '22') || str_starts_with($kodeCoa, '3')) {
                        $category = 'pendanaan';
                    } else {
                        $category = 'operasi'; // Current liabilities like accounts payable are Operating
                    }
                }
            }

            $amount = ($entry->tipe_posting === 'debit' ? 1 : -1) * $entry->nominal;

            $activities[$category]['entries']->push([
                'tanggal' => $entry->tanggal_posting,
                'nomor_ref' => $entry->nomor_ref,
                'deskripsi' => $entry->deskripsi,
                'coa_name' => $entry->coa->nama_coa,
                'counterpart_name' => $counterpart ? $counterpart->coa->nama_coa : '-',
                'departemen' => $entry->pengajuan && $entry->pengajuan->departemen ? $entry->pengajuan->departemen->nama_departemen : '-',
                'kategori' => $entry->pengajuan && $entry->pengajuan->kategori ? $entry->pengajuan->kategori->nama_kategori : '-',
                'file_bukti' => $entry->pengajuan ? $entry->pengajuan->file_bukti : null,
                'pengajuan_id' => $entry->pengajuan ? $entry->pengajuan->pengajuan_id : null,
                'accurate_id' => $entry->pengajuan ? $entry->pengajuan->accurate_transaction_id : null,
                'nominal' => $entry->nominal,
                'tipe' => $entry->tipe_posting,
                'flow' => $amount,
            ]);

            $activities[$category]['total'] += $amount;
        }

        $totalInflow = $allEntries->where('tipe_posting', 'debit')->sum('nominal');
        $totalOutflow = $allEntries->where('tipe_posting', 'credit')->sum('nominal');
        $netFlow = $totalInflow - $totalOutflow;
        $totalEntries = $allEntries->count();

        // 3. Calculate Saldo Awal & Akhir Kas (Aggregate for all Cash/Bank)
        $saldoAwalKas = 0;
        foreach ($cashBankCoaIds as $cbCoaId) {
            $coa = COA::find($cbCoaId);
            if (! $coa) {
                continue;
            }

            $initialBalance = $coa->saldo ?? 0;
            $asOfDate = $coa->as_of_date ? Carbon::parse($coa->as_of_date) : Carbon::parse('2000-01-01');

            if ($asOfDate->lt($startDate)) {
                $preMutations = Jurnal::where('coa_id', $coa->coa_id)
                    ->where(function ($q) use ($coa, $asOfDate) {
                        $q->where('tanggal_posting', '>', $asOfDate);
                        if ($coa->last_sync_at) {
                            $q->orWhere(function ($sub) use ($coa, $asOfDate) {
                                $sub->where('tanggal_posting', $asOfDate)
                                    ->where('posted_at', '>', $coa->last_sync_at);
                            });
                        }
                    })
                    ->where('tanggal_posting', '<', $startDate)
                    ->get();
                $preDebit = $preMutations->where('tipe_posting', 'debit')->sum('nominal');
                $preCredit = $preMutations->where('tipe_posting', 'credit')->sum('nominal');
                $saldoAwalKas += ($initialBalance + ($preDebit - $preCredit));
            } else {
                // Hitung mundur (Inklusif asOfDate)
                $preMutations = Jurnal::where('coa_id', $coa->coa_id)
                    ->where('tanggal_posting', '>=', $startDate)
                    ->where(function ($q) use ($coa, $asOfDate) {
                        $q->where('tanggal_posting', '<', $asOfDate);
                        if ($coa->last_sync_at) {
                            $q->orWhere(function ($sub) use ($coa, $asOfDate) {
                                $sub->where('tanggal_posting', $asOfDate)
                                    ->where('posted_at', '<=', $coa->last_sync_at);
                            });
                        } else {
                            $q->orWhere('tanggal_posting', $asOfDate);
                        }
                    })
                    ->get();
                $preDebit = $preMutations->where('tipe_posting', 'debit')->sum('nominal');
                $preCredit = $preMutations->where('tipe_posting', 'credit')->sum('nominal');
                $saldoAwalKas += ($initialBalance - ($preDebit - $preCredit));
            }
        }
        $saldoAkhirKas = $saldoAwalKas + $netFlow;

        $kategori = KategoriBiaya::where('is_active', true)->orderBy('nama_kategori')->get();
        $departemen = Departemen::orderBy('nama_departemen')->get();

        return view('dashboard.finance.reports.laporan_arus_kas', compact(
            'activities',
            'startDate',
            'endDate',
            'totalInflow',
            'totalOutflow',
            'netFlow',
            'saldoAwalKas',
            'saldoAkhirKas',
            'totalEntries',
            'kategori',
            'departemen'
        ));
    }

    public function laporanArusKasExportCsv()
    {
        $cashBankCoaIds = KasBank::pluck('coa_id')->toArray();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query()->whereIn('coa_id', $cashBankCoaIds),
            'tanggal_posting'
        );

        $query = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])
            ->whereIn('coa_id', $cashBankCoaIds)
            ->with(['coa', 'pengajuan.user', 'pengajuan.departemen', 'pengajuan.kategori']);

        if (request('kategori_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('kategori_id', request('kategori_id'));
            });
        }

        if (request('departemen_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('departemen_id', request('departemen_id'));
            });
        }

        $entries = $query->orderBy('tanggal_posting', 'desc')->get();

        $headers = ['No', 'Tanggal', 'No. Referensi', 'Pengajuan', 'Departemen', 'Kategori', 'Kode Akun', 'Keterangan', 'Penerimaan (IDR)', 'Pengeluaran (IDR)'];

        $data = $entries->values()->map(function ($entry, $index) {
            return [
                $index + 1,
                $entry->tanggal_posting->format('d/m/Y'),
                $entry->nomor_ref,
                $entry->pengajuan ? $entry->pengajuan->nomor_pengajuan : '-',
                $entry->pengajuan && $entry->pengajuan->departemen ? $entry->pengajuan->departemen->nama_departemen : '-',
                $entry->pengajuan && $entry->pengajuan->kategori ? $entry->pengajuan->kategori->nama_kategori : '-',
                $entry->coa->kode_coa,
                $entry->deskripsi,
                $entry->tipe_posting == 'debit' ? number_format((float) $entry->nominal, 0, ',', '.') : '-',
                $entry->tipe_posting == 'credit' ? number_format((float) $entry->nominal, 0, ',', '.') : '-',
            ];
        });

        return $this->exportService->exportToCSV(
            'laporan_arus_kas_'.date('Y-m-d').'.csv',
            $headers,
            $data
        );
    }

    public function laporanArusKasExportXlsx()
    {
        $cashBankCoaIds = KasBank::pluck('coa_id')->toArray();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query()->whereIn('coa_id', $cashBankCoaIds),
            'tanggal_posting'
        );

        $query = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])
            ->whereIn('coa_id', $cashBankCoaIds)
            ->with(['coa', 'pengajuan.user', 'pengajuan.departemen', 'pengajuan.kategori']);

        if (request('kategori_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('kategori_id', request('kategori_id'));
            });
        }

        if (request('departemen_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('departemen_id', request('departemen_id'));
            });
        }

        $entries = $query->orderBy('tanggal_posting', 'desc')->get();

        $headers = ['No', 'Tanggal', 'No. Referensi', 'Pengajuan', 'Departemen', 'Kategori', 'Kode Akun', 'Keterangan', 'Penerimaan (IDR)', 'Pengeluaran (IDR)'];

        $data = $entries->values()->map(function ($entry, $index) {
            return [
                $index + 1,
                $entry->tanggal_posting->format('d/m/Y'),
                $entry->nomor_ref,
                $entry->pengajuan ? $entry->pengajuan->nomor_pengajuan : '-',
                $entry->pengajuan && $entry->pengajuan->departemen ? $entry->pengajuan->departemen->nama_departemen : '-',
                $entry->pengajuan && $entry->pengajuan->kategori ? $entry->pengajuan->kategori->nama_kategori : '-',
                $entry->coa->kode_coa,
                $entry->deskripsi,
                $entry->tipe_posting == 'debit' ? number_format((float) $entry->nominal, 0, ',', '.') : '-',
                $entry->tipe_posting == 'credit' ? number_format((float) $entry->nominal, 0, ',', '.') : '-',
            ];
        });

        return $this->exportService->exportToXlsx(
            'laporan_arus_kas_'.date('Y-m-d').'.xlsx',
            $headers,
            $data,
            ['sheet_name' => 'Arus Kas']
        );
    }

    public function laporanArusKasExportPdf()
    {
        $cashBankCoaIds = KasBank::pluck('coa_id')->toArray();
        [$startDate, $endDate] = $this->resolveDateRangeFromQuery(
            Jurnal::query()->whereIn('coa_id', $cashBankCoaIds),
            'tanggal_posting'
        );

        $query = Jurnal::whereBetween('tanggal_posting', [$startDate, $endDate])
            ->whereIn('coa_id', $cashBankCoaIds)
            ->with(['coa', 'pengajuan.user', 'pengajuan.departemen', 'pengajuan.kategori']);

        if (request('kategori_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('kategori_id', request('kategori_id'));
            });
        }

        if (request('departemen_id')) {
            $query->whereHas('pengajuan', function ($q) {
                $q->where('departemen_id', request('departemen_id'));
            });
        }

        $entries = $query->orderBy('tanggal_posting', 'desc')->get();

        $totalInflow = $entries->where('tipe_posting', 'debit')->sum('nominal');
        $totalOutflow = $entries->where('tipe_posting', 'credit')->sum('nominal');
        $netFlow = $totalInflow - $totalOutflow;

        return $this->exportService->exportToPDF(
            'laporan_arus_kas_'.date('Y-m-d').'.pdf',
            'dashboard.finance.reports.pdf.arus_kas',
            compact('entries', 'startDate', 'endDate', 'totalInflow', 'totalOutflow', 'netFlow'),
            ['orientation' => 'landscape']
        );
    }
}
