<?php

namespace App\Http\Controllers\Finance\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'finance_dashboard_data_v2';

        $viewData = Cache::remember($cacheKey, 300, function () {
            return $this->getDashboardData();
        });

        return view('dashboard.finance.dashboard', $viewData);
    }

    private function getDashboardData()
    {
        $now = Carbon::now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $slaDays = 3;
        $today = today();
        $slaDate = $now->copy()->subDays($slaDays);

        // 1. CONSOLIDATED CORE METRICS (Extreme Optimization)
        $coreStats = Pengajuan::selectRaw('
            COUNT(*) as total_requests,
            SUM(CASE WHEN status IN ("menunggu_finance", "menunggu_atasan") THEN 1 ELSE 0 END) as total_pending_count,
            SUM(CASE WHEN status = "menunggu_finance" THEN 1 ELSE 0 END) as waiting_finance_count,
            SUM(CASE WHEN status = "menunggu_finance" THEN nominal ELSE 0 END) as waiting_finance_amount,
            SUM(CASE WHEN status = "menunggu_atasan" THEN nominal ELSE 0 END) as waiting_atasan_amount,
            SUM(CASE WHEN status = "dicairkan" THEN nominal ELSE 0 END) as lifetime_disbursed,
            SUM(CASE WHEN tanggal_pengajuan = ? THEN 1 ELSE 0 END) as requests_today,
            SUM(CASE WHEN status = "terkirim_accurate" THEN nominal ELSE 0 END) as approved_not_disbursed,
            SUM(CASE WHEN status IN ("ditolak_finance", "ditolak_atasan") THEN 1 ELSE 0 END) as rejected_total_count,
            
            SUM(CASE WHEN status = "dicairkan" AND updated_at >= ? AND updated_at <= ? THEN nominal ELSE 0 END) as this_month_disbursed,
            SUM(CASE WHEN status = "dicairkan" AND updated_at >= ? AND updated_at <= ? THEN nominal ELSE 0 END) as last_month_disbursed,
            
            COUNT(CASE WHEN tanggal_pengajuan >= ? AND tanggal_pengajuan <= ? THEN 1 END) as this_month_count,
            SUM(CASE WHEN tanggal_pengajuan >= ? AND tanggal_pengajuan <= ? THEN nominal ELSE 0 END) as this_month_amount,
            
            SUM(CASE WHEN status IN ("dicairkan", "terkirim_accurate") AND updated_at >= ? AND updated_at <= ? THEN nominal ELSE 0 END) as this_month_processed_amount,
            
            COUNT(CASE WHEN status IN ("menunggu_finance", "menunggu_atasan") AND created_at < ? THEN 1 END) as oversla_count,
            SUM(CASE WHEN status IN ("menunggu_finance", "menunggu_atasan") AND created_at < ? THEN nominal ELSE 0 END) as oversla_nominal
        ', [
            $today->toDateString(),
            $startOfThisMonth, $endOfThisMonth,
            $startOfLastMonth, $endOfLastMonth,
            $startOfThisMonth->toDateString(), $endOfThisMonth->toDateString(),
            $startOfThisMonth->toDateString(), $endOfThisMonth->toDateString(),
            $startOfThisMonth, $endOfThisMonth,
            $slaDate,
            $slaDate,
        ])->first();

        // 2. GROWTH & DERIVED METRICS
        $lastMonthDisbursed = $coreStats->last_month_disbursed ?? 0;
        $thisMonthDisbursed = $coreStats->this_month_disbursed ?? 0;
        $growthPercentage = $lastMonthDisbursed > 0
            ? (($thisMonthDisbursed - $lastMonthDisbursed) / $lastMonthDisbursed) * 100
            : ($thisMonthDisbursed > 0 ? 100 : 0);

        $thisMonthRequestsCount = $coreStats->this_month_count ?? 0;
        $thisMonthRequestsAmount = $coreStats->this_month_amount ?? 0;
        $avgRequestAmount = $thisMonthRequestsCount > 0 ? $thisMonthRequestsAmount / $thisMonthRequestsCount : 0;

        // 3. BUDGET ANALYTICS
        $monthlyBudget = (float) Departemen::sum('budget_limit');
        $thisMonthProcessedAmount = (float) ($coreStats->this_month_processed_amount ?? 0);
        $budgetUsagePercentage = $monthlyBudget > 0 ? ($thisMonthProcessedAmount / $monthlyBudget) * 100 : ($thisMonthProcessedAmount > 0 ? 100 : 0);

        // 4. CHART DATA: ACTIVITY TREND (Ensure no empty gaps for 7 days)
        $trendRaw = Pengajuan::select(
            DB::raw('DATE(tanggal_pengajuan) as tanggal'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(nominal) as total')
        )
            ->where('tanggal_pengajuan', '>=', $now->copy()->subDays(6)->startOfDay()->toDateString())
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $dailyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            if (isset($trendRaw[$date])) {
                $dailyTrend[] = [
                    'tanggal' => $date,
                    'count' => (int) $trendRaw[$date]->count,
                    'total' => (float) ($trendRaw[$date]->total ?? 0),
                ];
            } else {
                $dailyTrend[] = [
                    'tanggal' => $date,
                    'count' => 0,
                    'total' => 0,
                ];
            }
        }

        // 5. CHART DATA: DEPARTMENT DISTRIBUTION (Top 5)
        $departementDistribution = Pengajuan::select(
            'departemen.nama_departemen',
            DB::raw('COUNT(*) as count')
        )
            ->join('departemen', 'pengajuan.departemen_id', '=', 'departemen.departemen_id')
            ->whereBetween('pengajuan.created_at', [$startOfThisMonth, $endOfThisMonth])
            ->groupBy('departemen.nama_departemen')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 6. NEW: PENDING PER CATEGORY (Important Info for Finance)
        $pendingByCategory = Pengajuan::select(
            'kategori_biaya.nama_kategori',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(nominal) as total_nominal')
        )
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->whereIn('status', ['menunggu_finance', 'menunggu_atasan'])
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderByDesc('total_nominal')
            ->get();

        // 7. REJECTION RATE (Enhanced with counts)
        $rejectionRateByCategory = Pengajuan::select(
            'kategori_biaya.nama_kategori',
            DB::raw('ROUND((SUM(CASE WHEN status IN ("ditolak_finance", "ditolak_atasan", "ditolak_ai") THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100, 1) as rejection_rate'),
            DB::raw('SUM(CASE WHEN status IN ("dicairkan", "terkirim_accurate") THEN 1 ELSE 0 END) as approved_count'),
            DB::raw('SUM(CASE WHEN status IN ("ditolak_finance", "ditolak_atasan", "ditolak_ai") THEN 1 ELSE 0 END) as rejected_count')
        )
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->whereBetween('pengajuan.created_at', [$startOfThisMonth, $endOfThisMonth])
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderByDesc('rejection_rate')
            ->limit(5)
            ->get();

        // 8. STATUS DISTRIBUTION (For monthly overview chart)
        $statusDistribution = [
            'menunggu_atasan' => Pengajuan::where('status', 'menunggu_atasan')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'menunggu_finance' => Pengajuan::where('status', 'menunggu_finance')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'dicairkan' => Pengajuan::where('status', 'dicairkan')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'ditolak_atasan' => Pengajuan::where('status', 'ditolak_atasan')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'ditolak_finance' => Pengajuan::where('status', 'ditolak_finance')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
            'ditolak_ai' => Pengajuan::where('status', 'ditolak_ai')
                ->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])
                ->count(),
        ];

        // 9. CATEGORY DISTRIBUTION (Enhanced)
        $categoryDistribution = Pengajuan::select(
            'kategori_biaya.nama_kategori',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(nominal) as total')
        )
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->whereBetween('pengajuan.created_at', [$startOfThisMonth, $endOfThisMonth])
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        // 10. AVERAGE OVERDUE DAYS (SLA Analysis)
        $overdueRequests = Pengajuan::select('created_at', 'status')
            ->whereIn('status', ['menunggu_finance', 'menunggu_atasan'])
            ->where('created_at', '<', $slaDate)
            ->get();

        $overslaAvgDays = $overdueRequests->count() > 0
            ? $overdueRequests->avg(function ($p) use ($now) {
                return $now->diffInDays($p->created_at);
            })
            : 0;

        // 11. MONTHLY TREND (Last 30 days)
        $monthlyTrend = Pengajuan::select(
            DB::raw('DATE(tanggal_pengajuan) as tanggal'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(nominal) as total')
        )
            ->where('tanggal_pengajuan', '>=', $now->copy()->subDays(29)->startOfDay()->toDateString())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total,
                ];
            });

        // 12. TOP REQUESTER (Bulan Ini)
        $topRequesters = Pengajuan::select('users.name', DB::raw('SUM(pengajuan.nominal) as total_nominal'), DB::raw('COUNT(*) as total_requests'))
            ->join('users', 'pengajuan.user_id', '=', 'users.id')
            ->whereBetween('pengajuan.tanggal_pengajuan', [$startOfThisMonth->toDateString(), $endOfThisMonth->toDateString()])
            ->whereNotIn('pengajuan.status', ['ditolak_atasan', 'ditolak_finance'])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_nominal')
            ->limit(4)
            ->get();

        // 9. RECENT ACTIVITY
        $recentRequests = Pengajuan::with(['user', 'departemen', 'kategori'])
            ->whereNotIn('status', ['menunggu_atasan', 'ditolak_atasan'])
            ->latest()
            ->limit(12)
            ->get();

        // 10. MANAGEMENT COUNTS
        $totalUsers = User::count();
        $totalDepartemen = Departemen::count();
        $totalCategories = \App\Models\KategoriBiaya::count();


        return [
            'total_pending_count' => $coreStats->total_pending_count ?? 0,
            'waiting_finance_count' => $coreStats->waiting_finance_count ?? 0,
            'waiting_finance_amount' => $coreStats->waiting_finance_amount ?? 0,
            'waiting_atasan_amount' => $coreStats->waiting_atasan_amount ?? 0,
            'requests_today' => $coreStats->requests_today ?? 0,
            'this_month_disbursed' => $thisMonthDisbursed,
            'lifetime_disbursed' => $coreStats->lifetime_disbursed ?? 0,
            'this_month_amount' => $thisMonthRequestsAmount,
            'this_month_count' => $thisMonthRequestsCount,
            'this_month_processed_amount' => $thisMonthProcessedAmount,
            'monthly_budget' => $monthlyBudget,
            'budget_usage_percent' => $budgetUsagePercentage,
            'growth_percentage' => $growthPercentage,
            'avg_request_amount' => $avgRequestAmount,
            'oversla_count' => $coreStats->oversla_count ?? 0,
            'oversla_nominal' => $coreStats->oversla_nominal ?? 0,
            'oversla_avg_days' => $overslaAvgDays,
            'dailyTrend' => $dailyTrend,
            'departementDistribution' => $departementDistribution,
            'statusDistribution' => $statusDistribution,
            'categoryDistribution' => $categoryDistribution,
            'monthlyTrend' => $monthlyTrend,
            'pendingByCategory' => $pendingByCategory,
            'rejectionRateByCategory' => $rejectionRateByCategory,
            'topRequesters' => $topRequesters,
            'recentRequests' => $recentRequests,
            'slaDays' => $slaDays,
            'totalUsers' => $totalUsers,
            'totalDepartemen' => $totalDepartemen,
            'totalCategories' => $totalCategories,
        ];
    }
}
