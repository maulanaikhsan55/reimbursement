<?php

namespace App\Http\Controllers\Finance\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Services\AccurateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartemenController extends Controller
{
    protected $accurateService;

    public function __construct(AccurateService $accurateService)
    {
        $this->accurateService = $accurateService;
    }

    public function index(Request $request)
    {
        $selectedMonth = $request->filled('month') ? (int) $request->get('month') : null;
        $selectedYear = $request->filled('year') ? (int) $request->get('year') : null;

        if ($selectedMonth !== null && ($selectedMonth < 1 || $selectedMonth > 12)) {
            $selectedMonth = null;
        }

        if ($selectedYear !== null && ($selectedYear < 2000 || $selectedYear > 2100)) {
            $selectedYear = null;
        }

        $query = Departemen::withCount('users')
            ->orderBy('nama_departemen');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_departemen', 'like', "%{$search}%")
                    ->orWhere('kode_departemen', 'like', "%{$search}%");
            });
        }

        $departemen = $query->paginate(config('app.pagination.master_data'))
            ->withQueryString();

        $stats = [
            'total' => Departemen::count(),
            'with_users' => Departemen::has('users')->count(),
            'without_users' => Departemen::doesntHave('users')->count(),
        ];

        // Calculate usage for each department
        $departemen->getCollection()->transform(function ($dept) use ($selectedMonth, $selectedYear) {
            $usageQuery = Pengajuan::where('departemen_id', $dept->departemen_id)
                ->whereNotIn('status', ['ditolak_atasan', 'ditolak_finance']);

            if ($selectedMonth !== null) {
                $usageQuery->whereMonth('tanggal_transaksi', $selectedMonth);
            }

            if ($selectedYear !== null) {
                $usageQuery->whereYear('tanggal_transaksi', $selectedYear);
            }

            $dept->current_usage = $usageQuery->sum('nominal');

            return $dept;
        });

        return view('dashboard.finance.masterdata.departemen.index', compact('departemen', 'selectedMonth', 'selectedYear', 'stats'));
    }

    public function update(Request $request, Departemen $departemen)
    {
        $validated = $request->validate([
            'budget_limit' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $departemen->update($validated);

        return redirect()->back()->with('success', 'Budget departemen berhasil diperbarui');
    }

    public function syncFromAccurate(Request $request)
    {
        try {
            $forceFullSync = $request->has('force_full_sync');
            $lastSync = Departemen::max('last_sync_at');
            $lastModified = null;

            if ($lastSync && ! $forceFullSync) {
                $lastModified = \Carbon\Carbon::parse($lastSync)->subMinute()->format('d/m/Y H:i:s');
            }

            $response = $this->accurateService->fetchDepartmentsFromAccurate($lastModified);

            if (! $response['success']) {
                $errorMsg = 'Gagal mengambil data Departemen dari Accurate: '.$response['message'];

                Notifikasi::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'error',
                    'judul' => 'Sinkronisasi Departemen Gagal',
                    'pesan' => $errorMsg,
                    'is_read' => false,
                ]);

                return redirect()->route('finance.masterdata.departemen.index')
                    ->with('error', $errorMsg);
            }

            $departments = $response['data'] ?? [];

            if (empty($departments)) {
                $infoMsg = 'Tidak ada data Departemen yang ditemukan di Accurate untuk disinkronkan.';

                return redirect()->route('finance.masterdata.departemen.index')
                    ->with('info', $infoMsg);
            }

            $result = DB::transaction(function () use ($departments, $lastModified) {
                $created = 0;
                $updated = 0;
                $failed = 0;
                $deactivated = 0;

                $accurateIds = collect($departments)->pluck('accurate_id')->filter()->toArray();

                foreach ($departments as $deptData) {
                    try {
                        $namaDept = trim($deptData['nama_departemen'] ?? '');

                        if (empty($namaDept)) {
                            continue;
                        }

                        $dept = Departemen::where('accurate_id', $deptData['accurate_id'])->first();

                        // If not found by accurate_id, try by name to link existing local data
                        if (! $dept) {
                            $dept = Departemen::where('nama_departemen', $namaDept)->first();
                        }

                        $updateData = [
                            'nama_departemen' => $namaDept,
                            'deskripsi' => $deptData['deskripsi'] ?? null,
                            'accurate_id' => $deptData['accurate_id'],
                            'is_active' => true, // Reactivate if found in Accurate
                            'last_sync_at' => now(),
                        ];

                        if ($dept) {
                            $updated++;
                            $dept->update($updateData);
                        } else {
                            $created++;
                            // Auto-generate code if missing - Using more robust sequential check
                            $latestDept = Departemen::where('kode_departemen', 'like', 'D%')
                                ->orderByRaw('CAST(SUBSTRING(kode_departemen, 2) AS UNSIGNED) DESC')
                                ->first();

                            $nextNumber = 1;
                            if ($latestDept && preg_match('/D(\d+)/', $latestDept->kode_departemen, $matches)) {
                                $nextNumber = intval($matches[1]) + 1;
                            }

                            $updateData['kode_departemen'] = 'D'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                            Departemen::create($updateData);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to sync Department: '.$e->getMessage());
                        $failed++;
                    }
                }

                // SMART SYNC: Deactivate departments that exist in local but NOT in Accurate response
                // PENTING: Hanya lakukan jika ini adalah FULL SYNC
                if (! $lastModified) {
                    $deactivated = Departemen::whereNotNull('accurate_id')
                        ->whereNotIn('accurate_id', $accurateIds)
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'last_sync_at' => now(),
                        ]);
                }

                return [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'deactivated' => $deactivated,
                    'is_delta' => ! empty($lastModified),
                ];
            });

            $syncType = $result['is_delta'] ? 'Incremental' : 'Full';
            $successMsg = "Sinkronisasi Departemen ({$syncType}) berhasil. ";
            $successMsg .= "{$result['created']} baru, ";
            $successMsg .= "{$result['updated']} diperbarui.";

            if (! $result['is_delta'] && $result['deactivated'] > 0) {
                $successMsg .= " {$result['deactivated']} dinonaktifkan.";
            }

            if ($result['failed'] > 0) {
                $successMsg .= " ({$result['failed']} gagal).";
            }

            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'success',
                'judul' => 'Sinkronisasi Departemen Berhasil',
                'pesan' => $successMsg,
                'is_read' => false,
            ]);

            return redirect()->route('finance.masterdata.departemen.index')
                ->with('success', $successMsg);
        } catch (\Exception $e) {
            $errorMsg = 'Terjadi kesalahan sistem saat sinkronisasi Departemen: '.$e->getMessage();

            return redirect()->route('finance.masterdata.departemen.index')
                ->with('error', $errorMsg);
        }
    }
}
