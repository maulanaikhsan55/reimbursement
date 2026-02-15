<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('departemen');
        $userId = $user->id;
        $cacheKey = 'pegawai_dashboard_'.$userId;

        // Force refresh cache jika ada parameter ?refresh=1
        if (request()->has('refresh')) {
            Cache::forget($cacheKey);
        }

        // Cache dashboard data untuk 5 menit
        $data = Cache::remember($cacheKey, 300, function () use ($userId) {
            return $this->getDashboardData($userId);
        });

        return view('dashboard.pegawai.dashboard', array_merge($data, ['user' => $user]));
    }

    /**
     * Get dashboard data dengan optimized queries
     *
     * Note: All data is fetched using userId only to prevent stale data
     * issues when user changes during cache lifetime
     */
    private function getDashboardData($userId)
    {
        // 1. Unified optimized query for stats and status distribution
        $stats = Pengajuan::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("validasi_ai", "menunggu_atasan", "menunggu_finance", "terkirim_accurate") THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "dicairkan" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status IN ("ditolak_atasan", "ditolak_finance") THEN 1 ELSE 0 END) as rejected_count,
                SUM(nominal) as total_nominal,
                SUM(CASE WHEN status = "dicairkan" THEN nominal ELSE 0 END) as nominal_approved,
                SUM(CASE WHEN status IN ("validasi_ai", "menunggu_atasan", "menunggu_finance", "terkirim_accurate") THEN nominal ELSE 0 END) as nominal_pending,
                SUM(CASE WHEN status IN ("ditolak_atasan", "ditolak_finance") AND MONTH(updated_at) = ? AND YEAR(updated_at) = ? THEN nominal ELSE 0 END) as nominal_rejected,
                SUM(CASE WHEN status = "dicairkan" AND MONTH(tanggal_disetujui_finance) = ? AND YEAR(tanggal_disetujui_finance) = ? THEN nominal ELSE 0 END) as nominal_approved_this_month,
                SUM(CASE WHEN status = "dicairkan" AND MONTH(tanggal_disetujui_finance) = ? AND YEAR(tanggal_disetujui_finance) = ? THEN nominal ELSE 0 END) as nominal_approved_last_month,
                SUM(CASE WHEN status IN ("validasi_ai", "menunggu_atasan") THEN 1 ELSE 0 END) as count_menunggu_atasan,
                SUM(CASE WHEN status IN ("menunggu_finance", "terkirim_accurate") THEN 1 ELSE 0 END) as count_menunggu_finance,
                SUM(CASE WHEN status IN ("dicairkan", "selesai") THEN 1 ELSE 0 END) as count_dicairkan,
                SUM(CASE WHEN status = "ditolak_atasan" THEN 1 ELSE 0 END) as count_ditolak_atasan,
                SUM(CASE WHEN status = "ditolak_finance" THEN 1 ELSE 0 END) as count_ditolak_finance
            ', [date('m'), date('Y'), date('m'), date('Y'), date('m', strtotime('first day of last month')), date('Y', strtotime('first day of last month'))])
            ->first();

        $disbursedLastMonth = (float) ($stats->nominal_approved_last_month ?? 0);
        $disbursedThisMonth = (float) ($stats->nominal_approved_this_month ?? 0);
        $disbursedGrowth = 0;
        if ($disbursedLastMonth > 0) {
            $disbursedGrowth = (($disbursedThisMonth - $disbursedLastMonth) / $disbursedLastMonth) * 100;
        }

        // 2. Budget Information - Get user with departemen relationship
        $user = User::with('departemen')->find($userId);
        $budgetLimit = 0;
        $monthlySpending = 0;
        if ($user && $user->departemen_id) {
            $departemen = $user->departemen;
            $budgetLimit = $departemen->budget_limit ?? 0;
            $monthlySpending = Pengajuan::getBudgetUsage($user->departemen_id, date('m'), date('Y'));
        }

        // 3. Category distribution for chart - ONLY APPROVED (Accounting Correct)
        $categoryDist = Pengajuan::where('user_id', $userId)
            ->where('status', 'dicairkan')
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->select('kategori_biaya.nama_kategori', DB::raw('SUM(nominal) as total'))
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderBy('total', 'desc')
            ->get();

        $topCategory = $categoryDist->first();

        // 4. Eager load recentRequests
        $recentRequests = Pengajuan::where('user_id', $userId)
            ->with(['departemen', 'kategori'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->limit(5)
            ->get();

        // 5. Query for monthly trend (Last 30 days) - ONLY APPROVED (Accounting Correct)
        $monthlyTrend = Pengajuan::where('user_id', $userId)
            ->where('status', 'dicairkan')
            ->select(
                DB::raw('DATE(tanggal_pengajuan) as tanggal'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(nominal) as total')
            )
            ->where('tanggal_pengajuan', '>=', Carbon::now()->subDays(30))
            ->groupBy(DB::raw('DATE(tanggal_pengajuan)'))
            ->orderBy('tanggal')
            ->get();

        $avgDailySpending = $monthlyTrend->count() > 0 ? $monthlyTrend->avg('total') : 0;

        // 6. Urgent Request (Latest Rejected)
        $urgentRequest = Pengajuan::where('user_id', $userId)
            ->whereIn('status', ['ditolak_atasan', 'ditolak_finance'])
            ->orderBy('updated_at', 'desc')
            ->first();

        // 7. Active Request (Latest Pending for Tracker)
        $activeRequest = Pengajuan::where('user_id', $userId)
            ->whereIn('status', ['validasi_ai', 'menunggu_atasan', 'menunggu_finance', 'terkirim_accurate'])
            ->orderBy('updated_at', 'desc')
            ->first();

        // 8. SLA Alert (Requests older than 3 days)
        $slaDate = Carbon::now()->subDays(3);
        $overslaCount = Pengajuan::where('user_id', $userId)
            ->whereIn('status', ['menunggu_atasan', 'menunggu_finance'])
            ->where('created_at', '<', $slaDate)
            ->count();

        return [
            'totalRequests' => $stats->total ?? 0,
            'diproses' => $stats->pending_count ?? 0,
            'dicairkan' => $stats->approved_count ?? 0,
            'rejectedCount' => $stats->rejected_count ?? 0,
            'totalNominalSubmitted' => (float) ($stats->total_nominal ?? 0),
            'totalNominalDisbursed' => (float) ($stats->nominal_approved ?? 0),
            'nominalDisbursedMonth' => $disbursedThisMonth,
            'disbursedGrowth' => $disbursedGrowth,
            'nominalPending' => (float) ($stats->nominal_pending ?? 0),
            'nominalRejected' => (float) ($stats->nominal_rejected ?? 0),
            'budgetLimit' => (float) $budgetLimit,
            'monthlySpending' => (float) $monthlySpending,
            'avgDailySpending' => (float) $avgDailySpending,
            'topCategory' => $topCategory->nama_kategori ?? 'Belum ada',
            'categoryDist' => $categoryDist,
            'recentRequests' => $recentRequests,
            'urgentRequest' => $urgentRequest,
            'activeRequest' => $activeRequest,
            'overslaCount' => $overslaCount,
            'statusData' => [
                'menunggu_atasan' => $stats->count_menunggu_atasan ?? 0,
                'menunggu_finance' => $stats->count_menunggu_finance ?? 0,
                'dicairkan' => $stats->count_dicairkan ?? 0,
                'ditolak_atasan' => $stats->count_ditolak_atasan ?? 0,
                'ditolak_finance' => $stats->count_ditolak_finance ?? 0,
            ],
            'monthlyTrend' => $monthlyTrend,
        ];
    }
}
