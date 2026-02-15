<?php

namespace App\Http\Controllers\Atasan;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('departemen');
        $subordinateIds = User::where('atasan_id', $user->id)->pluck('id')->toArray();

        // 1. Personal Stats & Trends
        $personalStats = Pengajuan::getPersonalStats($user->id);

        $disbursedThisMonth = Pengajuan::where('user_id', $user->id)
            ->where('status', PengajuanStatus::DICAIRKAN)
            ->whereMonth('tanggal_disetujui_finance', now()->month)
            ->whereYear('tanggal_disetujui_finance', now()->year)
            ->sum('nominal');

        $disbursedLastMonth = Pengajuan::where('user_id', $user->id)
            ->where('status', PengajuanStatus::DICAIRKAN)
            ->whereMonth('tanggal_disetujui_finance', now()->subMonth()->month)
            ->whereYear('tanggal_disetujui_finance', now()->subMonth()->year)
            ->sum('nominal');

        $disbursedGrowth = 0;
        if ($disbursedLastMonth > 0) {
            $disbursedGrowth = (($disbursedThisMonth - $disbursedLastMonth) / $disbursedLastMonth) * 100;
        }

        $monthlyTrend = Pengajuan::where('user_id', $user->id)
            ->where('status', 'dicairkan')
            ->select(
                DB::raw('DATE(tanggal_pengajuan) as tanggal'),
                DB::raw('SUM(nominal) as total')
            )
            ->whereBetween('tanggal_pengajuan', [now()->subDays(30), now()])
            ->groupBy(DB::raw('DATE(tanggal_pengajuan)'))
            ->orderBy('tanggal')
            ->get();

        $avgDailySpending = $monthlyTrend->count() > 0 ? $monthlyTrend->avg('total') : 0;

        $statusData = [
            'menunggu_atasan' => Pengajuan::where('user_id', $user->id)->where('status', PengajuanStatus::MENUNGGU_ATASAN)->count(),
            'menunggu_finance' => Pengajuan::where('user_id', $user->id)->where('status', PengajuanStatus::MENUNGGU_FINANCE)->count(),
            'dicairkan' => Pengajuan::where('user_id', $user->id)->where('status', PengajuanStatus::DICAIRKAN)->count(),
            'ditolak_atasan' => Pengajuan::where('user_id', $user->id)->where('status', PengajuanStatus::DITOLAK_ATASAN)->count(),
            'ditolak_finance' => Pengajuan::where('user_id', $user->id)->where('status', PengajuanStatus::DITOLAK_FINANCE)->count(),
        ];

        $categoryDist = Pengajuan::where('user_id', $user->id)
            ->where('status', 'dicairkan')
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->select('kategori_biaya.nama_kategori', DB::raw('SUM(pengajuan.nominal) as total'))
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderBy('total', 'desc')
            ->limit(7)
            ->get();

        $activeRequest = Pengajuan::where('user_id', $user->id)
            ->whereIn('status', [PengajuanStatus::VALIDASI_AI, PengajuanStatus::MENUNGGU_ATASAN, PengajuanStatus::MENUNGGU_FINANCE])
            ->orderBy('created_at', 'desc')
            ->first();

        // 2. Team Stats (Optimized)
        $teamStats = Pengajuan::getTeamStats($user->id, $subordinateIds);

        $recentRequests = Pengajuan::with(['user:id,name', 'kategori:kategori_id,nama_kategori'])
            ->where('status', PengajuanStatus::MENUNGGU_ATASAN)
            ->whereIn('user_id', $subordinateIds)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->limit(5)
            ->get();

        // 3. Team Member performance (Optimized query instead of loop)
        $teamMembers = User::where('atasan_id', $user->id)
            ->with(['departemen'])
            ->select('id', 'name', 'departemen_id')
            ->withCount(['pengajuan as total_count' => function ($q) {
                $q->whereMonth('tanggal_pengajuan', now()->month)
                    ->whereYear('tanggal_pengajuan', now()->year);
            }])
            ->withSum(['pengajuan as total_nominal' => function ($q) {
                $q->whereMonth('tanggal_pengajuan', now()->month)
                    ->whereYear('tanggal_pengajuan', now()->year);
            }], 'nominal')
            ->withSum(['pengajuan as pending_nominal' => function ($q) {
                $q->whereIn('status', ['validasi_ai', 'menunggu_atasan', 'menunggu_finance']);
            }], 'nominal')
            ->withSum(['pengajuan as approved_nominal' => function ($q) {
                $q->where('status', 'dicairkan');
            }], 'nominal')
            ->orderBy('name')
            ->get();

        // 4. Budget Stats
        $budgetLimit = 0;
        $monthlySpending = 0;
        if ($user->departemen_id) {
            $departemen = Departemen::find($user->departemen_id);
            $budgetLimit = $departemen->budget_limit ?? 0;
            $monthlySpending = Pengajuan::getBudgetUsage($user->departemen_id, date('m'), date('Y'));
        }

        $teamBudgetStats = [
            'team_total' => $monthlySpending,
            'team_limit' => $budgetLimit,
            'usage_percent' => $budgetLimit > 0 ? round(($monthlySpending / $budgetLimit) * 100, 1) : 0,
            'remaining' => max(0, $budgetLimit - $monthlySpending),
        ];

        // 5. Queue Stats
        $approvalQueueStats = (object) [
            'total_count' => $teamStats->pending_approvals,
            'total_nominal' => $teamStats->pending_nominal,
            'new_count' => Pengajuan::whereIn('user_id', $subordinateIds)
                ->where('status', PengajuanStatus::MENUNGGU_ATASAN)
                ->where('tanggal_pengajuan', '>', now()->subDays(3))
                ->count(),
            'overdue_count' => $teamStats->oversla_count,
        ];

        return view('dashboard.atasan.dashboard', [
            'user' => $user,
            'personalStats' => $personalStats,
            'monthlyTrend' => $monthlyTrend,
            'statusData' => $statusData,
            'categoryDist' => $categoryDist,
            'activeRequest' => $activeRequest,
            'recentRequests' => $recentRequests,
            'totalRequests' => $personalStats->total ?? 0,
            'nominalPending' => $personalStats->nominal_pending ?? 0,
            'nominalRejected' => Pengajuan::where('user_id', $user->id)
                ->whereIn('status', [PengajuanStatus::DITOLAK_ATASAN, PengajuanStatus::DITOLAK_FINANCE])
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('nominal'),
            'budgetLimit' => $budgetLimit,
            'monthlySpending' => $monthlySpending,
            'nominalDisbursedMonth' => $disbursedThisMonth,
            'disbursedGrowth' => $disbursedGrowth,
            'avgDailySpending' => $avgDailySpending,
            'topCategory' => $categoryDist->first()->nama_kategori ?? 'Belum ada',
            'teamMembers' => $teamMembers,
            'teamBudgetStats' => $teamBudgetStats,
            'approvalQueueStats' => $approvalQueueStats,
        ]);
    }
}
