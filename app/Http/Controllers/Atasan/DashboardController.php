<?php

namespace App\Http\Controllers\Atasan;

use App\Enums\PengajuanStatus;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('departemen');
        $data = $this->resolveDashboardData($user, request()->boolean('refresh'));

        return view('dashboard.atasan.dashboard', array_merge($data, ['user' => $user]));
    }

    public function widgets(): JsonResponse
    {
        $user = Auth::user()->load('departemen');
        $payload = $this->resolveWidgetPayload($user, request()->boolean('refresh'));

        return response()->json($payload);
    }

    private function resolveWidgetPayload(User $user, bool $forceRefresh = false): array
    {
        $userId = (int) $user->id;
        $cacheKey = 'atasan_dashboard_sections_'.$userId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 30, function () use ($user, $forceRefresh) {
            $data = $this->resolveDashboardData($user, $forceRefresh);
            $view = view('dashboard.atasan.dashboard', array_merge($data, ['user' => $user]));

            $content = method_exists($view, 'renderSections')
                ? ($view->renderSections()['content'] ?? '')
                : $view->render();

            return [
                'sections' => $this->extractSectionsFromHtml($content, [
                    'welcome-card',
                    'team-overview-card',
                    'approval-queue-card',
                    'recent-section',
                    'self-requests-card',
                ]),
                'generated_at' => $data['generatedAt'] ?? now()->toIso8601String(),
            ];
        });
    }

    private function resolveDashboardData(User $user, bool $forceRefresh = false): array
    {
        $userId = (int) $user->id;
        $cacheKey = 'atasan_dashboard_'.$userId;
        $cacheLockKey = $cacheKey.'_lock';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::flexible($cacheKey, [60, 300], function () use ($user, $cacheLockKey) {
            return Cache::lock($cacheLockKey, 15)->block(5, function () use ($user) {
                return $this->getDashboardData($user);
            });
        });
    }

    private function extractSectionsFromHtml(string $html, array $classNames): array
    {
        if ($html === '') {
            return [];
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $xpath = new \DOMXPath($dom);
        $sections = [];

        foreach ($classNames as $className) {
            $nodes = $xpath->query(
                "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$className} ')]"
            );

            if ($nodes && $nodes->length > 0) {
                $sections['.'.$className] = trim($dom->saveHTML($nodes->item(0)));
            }
        }

        return $sections;
    }

    private function getDashboardData(User $user): array
    {
        $userId = (int) $user->id;
        $subordinateIds = User::where('atasan_id', $userId)->pluck('id')->toArray();

        $personalStats = Pengajuan::getPersonalStats($userId);
        $now = now();
        $lastMonth = $now->copy()->subMonth();

        $personalAggregate = Pengajuan::where('user_id', $userId)
            ->selectRaw(
                '
                SUM(CASE WHEN status = ? AND MONTH(tanggal_disetujui_finance) = ? AND YEAR(tanggal_disetujui_finance) = ? THEN nominal ELSE 0 END) as nominal_disbursed_this_month,
                SUM(CASE WHEN status = ? AND MONTH(tanggal_disetujui_finance) = ? AND YEAR(tanggal_disetujui_finance) = ? THEN nominal ELSE 0 END) as nominal_disbursed_last_month,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_menunggu_atasan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_menunggu_finance,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_dicairkan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_ditolak_atasan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_ditolak_finance,
                SUM(CASE WHEN status IN (?, ?) AND MONTH(updated_at) = ? AND YEAR(updated_at) = ? THEN nominal ELSE 0 END) as nominal_rejected
                ',
                [
                    PengajuanStatus::DICAIRKAN->value, $now->month, $now->year,
                    PengajuanStatus::DICAIRKAN->value, $lastMonth->month, $lastMonth->year,
                    PengajuanStatus::MENUNGGU_ATASAN->value,
                    PengajuanStatus::MENUNGGU_FINANCE->value,
                    PengajuanStatus::DICAIRKAN->value,
                    PengajuanStatus::DITOLAK_ATASAN->value,
                    PengajuanStatus::DITOLAK_FINANCE->value,
                    PengajuanStatus::DITOLAK_ATASAN->value,
                    PengajuanStatus::DITOLAK_FINANCE->value,
                    $now->month, $now->year,
                ]
            )
            ->first();

        $disbursedThisMonth = (float) ($personalAggregate->nominal_disbursed_this_month ?? 0);
        $disbursedLastMonth = (float) ($personalAggregate->nominal_disbursed_last_month ?? 0);
        $disbursedGrowth = 0;
        if ($disbursedLastMonth > 0) {
            $disbursedGrowth = (($disbursedThisMonth - $disbursedLastMonth) / $disbursedLastMonth) * 100;
        }

        $monthlyTrend = Pengajuan::where('user_id', $userId)
            ->where('status', PengajuanStatus::DICAIRKAN->value)
            ->select(
                DB::raw('DATE(tanggal_pengajuan) as tanggal'),
                DB::raw('SUM(nominal) as total')
            )
            ->whereBetween('tanggal_pengajuan', [$now->copy()->subDays(30), $now])
            ->groupBy(DB::raw('DATE(tanggal_pengajuan)'))
            ->orderBy('tanggal')
            ->get();

        $avgDailySpending = $monthlyTrend->count() > 0 ? $monthlyTrend->avg('total') : 0;

        $statusData = [
            'menunggu_atasan' => (int) ($personalAggregate->count_menunggu_atasan ?? 0),
            'menunggu_finance' => (int) ($personalAggregate->count_menunggu_finance ?? 0),
            'dicairkan' => (int) ($personalAggregate->count_dicairkan ?? 0),
            'ditolak_atasan' => (int) ($personalAggregate->count_ditolak_atasan ?? 0),
            'ditolak_finance' => (int) ($personalAggregate->count_ditolak_finance ?? 0),
        ];

        $categoryDist = Pengajuan::where('user_id', $userId)
            ->where('status', PengajuanStatus::DICAIRKAN->value)
            ->join('kategori_biaya', 'pengajuan.kategori_id', '=', 'kategori_biaya.kategori_id')
            ->select('kategori_biaya.nama_kategori', DB::raw('SUM(pengajuan.nominal) as total'))
            ->groupBy('kategori_biaya.nama_kategori')
            ->orderBy('total', 'desc')
            ->limit(7)
            ->get();

        $activeRequest = Pengajuan::where('user_id', $userId)
            ->whereIn('status', [
                PengajuanStatus::VALIDASI_AI->value,
                PengajuanStatus::MENUNGGU_ATASAN->value,
                PengajuanStatus::MENUNGGU_FINANCE->value,
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        $teamStats = Pengajuan::getTeamStats($userId, $subordinateIds);

        $recentRequests = Pengajuan::with(['user:id,name', 'kategori:kategori_id,nama_kategori'])
            ->where('status', PengajuanStatus::MENUNGGU_ATASAN->value)
            ->whereIn('user_id', $subordinateIds)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->limit(5)
            ->get();

        $myRecentRequests = Pengajuan::with(['kategori:kategori_id,nama_kategori'])
            ->where('user_id', $userId)
            ->latest('tanggal_pengajuan')
            ->limit(5)
            ->get();

        $teamMembers = User::where('atasan_id', $userId)
            ->with(['departemen'])
            ->select('id', 'name', 'departemen_id')
            ->withCount(['pengajuan as total_count' => function ($q) use ($now) {
                $q->whereMonth('tanggal_pengajuan', $now->month)
                    ->whereYear('tanggal_pengajuan', $now->year);
            }])
            ->withSum(['pengajuan as total_nominal' => function ($q) use ($now) {
                $q->whereMonth('tanggal_pengajuan', $now->month)
                    ->whereYear('tanggal_pengajuan', $now->year);
            }], 'nominal')
            ->withSum(['pengajuan as pending_nominal' => function ($q) {
                $q->whereIn('status', [
                    PengajuanStatus::VALIDASI_AI->value,
                    PengajuanStatus::MENUNGGU_ATASAN->value,
                    PengajuanStatus::MENUNGGU_FINANCE->value,
                ]);
            }], 'nominal')
            ->withSum(['pengajuan as approved_nominal' => function ($q) {
                $q->where('status', PengajuanStatus::DICAIRKAN->value);
            }], 'nominal')
            ->orderBy('name')
            ->get();

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

        $approvalQueueStats = (object) [
            'total_count' => $teamStats->pending_approvals,
            'total_nominal' => $teamStats->pending_nominal,
            'new_count' => Pengajuan::whereIn('user_id', $subordinateIds)
                ->where('status', PengajuanStatus::MENUNGGU_ATASAN->value)
                ->where('tanggal_pengajuan', '>', now()->subDays(3))
                ->count(),
            'overdue_count' => $teamStats->oversla_count,
        ];

        return [
            'personalStats' => $personalStats,
            'monthlyTrend' => $monthlyTrend,
            'statusData' => $statusData,
            'categoryDist' => $categoryDist,
            'activeRequest' => $activeRequest,
            'recentRequests' => $recentRequests,
            'myRecentRequests' => $myRecentRequests,
            'totalRequests' => $personalStats->total ?? 0,
            'nominalPending' => $personalStats->nominal_pending ?? 0,
            'nominalRejected' => (float) ($personalAggregate->nominal_rejected ?? 0),
            'budgetLimit' => $budgetLimit,
            'monthlySpending' => $monthlySpending,
            'nominalDisbursedMonth' => $disbursedThisMonth,
            'disbursedGrowth' => $disbursedGrowth,
            'avgDailySpending' => $avgDailySpending,
            'topCategory' => $categoryDist->first()->nama_kategori ?? 'Belum ada',
            'teamMembers' => $teamMembers,
            'teamBudgetStats' => $teamBudgetStats,
            'approvalQueueStats' => $approvalQueueStats,
            'generatedAt' => now()->toIso8601String(),
        ];
    }
}
