<?php

namespace App\Http\Controllers\Finance\MasterData;

use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\KasBank;
use App\Models\Notifikasi;
use App\Services\AccurateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class COAController extends Controller
{
    protected $accurateService;

    public function __construct(AccurateService $accurateService)
    {
        $this->accurateService = $accurateService;
    }

    public function index(Request $request)
    {
        $useCache = ! $request->filled('search') && ! $request->filled('tipe_akun') && ! $request->filled('status') && $request->get('page', 1) == 1;
        $cacheKey = 'coa_tree_view_page_1';

        if ($useCache && Cache::has($cacheKey)) {
            return view('dashboard.finance.masterdata.coa.index', ['coas' => Cache::get($cacheKey), 'is_cached' => true]);
        }

        $query = COA::where('parent_coa_id', null)
            ->orderBy('kode_coa')
            ->with(['children' => function ($q) {
                $q->orderBy('kode_coa');
            }, 'children.children']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_coa', 'like', "%{$search}%")
                    ->orWhere('nama_coa', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('saldo', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($q2) use ($search) {
                        $q2->where('kode_coa', 'like', "%{$search}%")
                            ->orWhere('nama_coa', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tipe_akun')) {
            $query->where('tipe_akun', $request->tipe_akun);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $coas = $query->paginate(config('app.pagination.master_data'));

        if ($useCache) {
            Cache::put($cacheKey, $coas, now()->addHours(12));
        }

        return view('dashboard.finance.masterdata.coa.index', compact('coas'));
    }

    public function syncFromAccurate(Request $request)
    {
        try {
            $forceFullSync = $request->has('force_full_sync');
            $lastSync = COA::max('last_sync_at');
            $lastModified = null;

            if ($lastSync && ! $forceFullSync) {
                // Beri margin 1 menit untuk memastikan tidak ada data terlewat karena perbedaan clock
                $lastModified = \Carbon\Carbon::parse($lastSync)->subMinute()->format('d/m/Y H:i:s');
            }

            $response = $this->accurateService->fetchCOAsFromAccurate($lastModified);

            if (! $response['success']) {
                $errorMsg = 'Gagal mengambil data COA dari Accurate: '.$response['message'];

                Notifikasi::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'error',
                    'judul' => 'Sinkronisasi COA Gagal',
                    'pesan' => $errorMsg,
                    'is_read' => false,
                ]);

                return redirect()->route('finance.masterdata.coa.index')
                    ->with('error', $errorMsg);
            }

            $coas = $response['data'] ?? [];

            if (empty($coas)) {
                $infoMsg = 'Tidak ada data COA yang ditemukan di Accurate untuk disinkronkan.';

                Notifikasi::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'info',
                    'judul' => 'Sinkronisasi COA Selesai',
                    'pesan' => $infoMsg,
                    'is_read' => false,
                ]);

                return redirect()->route('finance.masterdata.coa.index')
                    ->with('info', $infoMsg);
            }

            $result = DB::transaction(function () use ($coas, $lastModified) {
                $created = 0;
                $updated = 0;
                $failed = 0;

                // Ambil semua accurate_id dari response untuk validasi deaktivasi
                $accurateIdsFromResponse = collect($coas)->pluck('accurate_id')->filter()->toArray();

                foreach ($coas as $coaData) {
                    try {
                        $kodeCoA = trim($coaData['kode_coa'] ?? '');
                        $namaCoA = trim($coaData['nama_coa'] ?? '');

                        if (empty($kodeCoA) || empty($namaCoA)) {
                            continue;
                        }

                        $coa = COA::where('accurate_id', $coaData['accurate_id'])->first();

                        // Jika tidak ketemu by accurate_id, coba cari by kode_coa (untuk mapping data lokal lama)
                        if (! $coa) {
                            $coa = COA::where('kode_coa', $kodeCoA)->first();
                        }

                        $syncTime = now();
                        $updateData = [
                            'kode_coa' => $kodeCoA,
                            'nama_coa' => $namaCoA,
                            'tipe_akun' => $coaData['tipe_akun'] ?? 'expense',
                            'is_active' => $coaData['is_active'] ?? true,
                            'currency_code' => $coaData['currency_code'] ?? 'IDR',
                            'saldo' => $coaData['saldo'] ?? 0,
                            'as_of_date' => $syncTime->toDateString(), // Gunakan waktu sekarang sebagai referensi saldo terbaru
                            'deskripsi' => $coaData['deskripsi'] ?? null,
                            'accurate_id' => $coaData['accurate_id'],
                            'synced_from_accurate' => true,
                            'last_sync_at' => $syncTime,
                        ];

                        if ($coa) {
                            $updated++;
                            $coa->fill($updateData);
                            $coa->saveQuietly();
                        } else {
                            $created++;
                            $coa = COA::create($updateData);
                        }

                        // SINKRONISASI: Update/Create KasBank pendamping jika ini adalah akun Kas & Bank
                        $kb = KasBank::where('accurate_id', $coa->accurate_id)->first();
                        $kbData = [
                            'coa_id' => $coa->coa_id,
                            'kode_kas_bank' => $coa->kode_coa,
                            'nama_kas_bank' => $coa->nama_coa,
                            'saldo' => $updateData['saldo'],
                            'as_of_date' => $updateData['as_of_date'],
                            'last_sync_at' => $updateData['last_sync_at'],
                            'is_active' => $updateData['is_active'],
                            'currency_code' => $updateData['currency_code'],
                            'tipe_akun' => 'CASH_BANK',
                            'accurate_id' => $coa->accurate_id,
                        ];

                        if ($kb) {
                            $kb->fill($kbData);
                            $kb->saveQuietly();
                        } elseif (($coaData['raw_account_type'] ?? '') === 'CASH_BANK') {
                            // Buat baru jika ini adalah akun Kas/Bank tapi belum ada di tabel kas_bank
                            KasBank::create($kbData);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to sync COA: '.$e->getMessage());
                        $failed++;
                    }
                }

                // SECOND PASS: Link Parent-Child Hierarchy (Ultra Smart Logic)
                foreach ($coas as $coaData) {
                    $parent = null;

                    // Priority 1: Link by accurate_id (if available)
                    if (! empty($coaData['parent_accurate_id'])) {
                        $parent = COA::where('accurate_id', $coaData['parent_accurate_id'])->first();
                    }

                    // Priority 2: Link by code inference (if accurate_id linking failed/unavailable)
                    if (! $parent && ! empty($coaData['parent_code'])) {
                        $parent = COA::where('kode_coa', $coaData['parent_code'])->first();
                    }

                    if ($parent) {
                        COA::where('accurate_id', $coaData['accurate_id'])
                            ->update(['parent_coa_id' => $parent->coa_id]);
                    } else {
                        COA::where('accurate_id', $coaData['accurate_id'])
                            ->update(['parent_coa_id' => null]);
                    }
                }

                // SMART SYNC: Hanya nonaktifkan yang sebelumnya tersinkron tapi sekarang tidak ada di response Accurate
                // PENTING: Hanya lakukan jika ini adalah FULL SYNC
                $deactivated = 0;
                if (! $lastModified) {
                    $deactivated = COA::whereNotNull('accurate_id')
                        ->whereNotIn('accurate_id', $accurateIdsFromResponse)
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'last_sync_at' => now(),
                        ]);
                }

                // Clear Cache
                Cache::forget('coa_tree_view_page_1');

                return [
                    'created' => $created,
                    'updated' => $updated,
                    'deactivated' => $deactivated,
                    'failed' => $failed,
                    'is_delta' => ! empty($lastModified),
                ];
            });

            $syncType = $result['is_delta'] ? 'Incremental' : 'Full';
            $successMsg = "Sinkronisasi COA ({$syncType}) berhasil. ";
            $successMsg .= "{$result['created']} baru, ";
            $successMsg .= "{$result['updated']} diperbarui";

            if (! $result['is_delta']) {
                $successMsg .= ", {$result['deactivated']} dinonaktifkan.";
            } else {
                $successMsg .= '.';
            }

            if ($result['failed'] > 0) {
                $successMsg .= " ({$result['failed']} gagal).";
            }

            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'success',
                'judul' => 'Sinkronisasi COA Berhasil',
                'pesan' => $successMsg,
                'is_read' => false,
            ]);

            return redirect()->route('finance.masterdata.coa.index')
                ->with('success', $successMsg);
        } catch (\Exception $e) {
            $errorMsg = 'Terjadi kesalahan sistem saat sinkronisasi COA: '.$e->getMessage();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'error',
                'judul' => 'Kesalahan Sinkronisasi COA',
                'pesan' => $errorMsg,
                'is_read' => false,
            ]);

            return redirect()->route('finance.masterdata.coa.index')
                ->with('error', $errorMsg);
        }
    }
}
