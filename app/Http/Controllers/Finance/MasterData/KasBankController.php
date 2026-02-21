<?php

namespace App\Http\Controllers\Finance\MasterData;

use App\Http\Controllers\Controller;
use App\Models\COA;
use App\Models\KasBank;
use App\Models\Notifikasi;
use App\Services\AccurateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasBankController extends Controller
{
    protected $accurateService;

    public function __construct(AccurateService $accurateService)
    {
        $this->accurateService = $accurateService;
    }

    public function index(Request $request)
    {
        $query = KasBank::with('coa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kas_bank', 'like', "%{$search}%")
                    ->orWhere('kode_kas_bank', 'like', "%{$search}%")
                    ->orWhere('nomor_rekening', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $kasBanks = $query->orderBy('created_at', 'desc')->paginate(config('app.pagination.master_data'));

        $stats = [
            'total' => KasBank::count(),
            'aktif' => KasBank::where('is_active', true)->count(),
            'nonaktif' => KasBank::where('is_active', false)->count(),
        ];

        return view('dashboard.finance.masterdata.kas_bank.index', compact('kasBanks', 'stats'));
    }

    public function syncFromAccurate(Request $request)
    {
        try {
            $forceFullSync = $request->has('force_full_sync');
            $lastSync = KasBank::max('last_sync_at');
            $lastModified = null;

            if ($lastSync && ! $forceFullSync) {
                // Beri margin 1 menit untuk memastikan tidak ada data terlewat karena perbedaan clock
                $lastModified = \Carbon\Carbon::parse($lastSync)->subMinute()->format('d/m/Y H:i:s');
            }

            $response = $this->accurateService->fetchKasBankFromAccurate($lastModified);

            if (! $response['success']) {
                $errorMsg = 'Gagal mengambil data Kas/Bank dari Accurate: '.$response['message'];

                Notifikasi::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'error',
                    'judul' => 'Sinkronisasi Kas/Bank Gagal',
                    'pesan' => $errorMsg,
                    'is_read' => false,
                ]);

                return redirect()->route('finance.masterdata.kas_bank.index')
                    ->with('error', $errorMsg);
            }

            $kasBanks = $response['data'] ?? [];

            if (empty($kasBanks)) {
                $infoMsg = 'Tidak ada data Kas/Bank yang ditemukan di Accurate untuk disinkronkan.';

                Notifikasi::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'info',
                    'judul' => 'Sinkronisasi Kas/Bank Selesai',
                    'pesan' => $infoMsg,
                    'is_read' => false,
                ]);

                return redirect()->route('finance.masterdata.kas_bank.index')
                    ->with('info', $infoMsg);
            }

            $result = DB::transaction(function () use ($kasBanks, $lastModified) {
                $created = 0;
                $updated = 0;
                $failed = 0;

                // Ambil semua accurate_id dari response untuk validasi deaktivasi
                $accurateIdsFromResponse = collect($kasBanks)->pluck('accurate_id')->filter()->toArray();

                foreach ($kasBanks as $data) {
                    try {
                        $kodeKasBank = trim($data['kode_kas_bank'] ?? '');
                        $namaKasBank = trim($data['nama_kas_bank'] ?? '');

                        if (empty($kodeKasBank) || empty($namaKasBank)) {
                            continue;
                        }

                        // Cari COA pendamping (Kas/Bank di Accurate adalah GL Account)
                        $coa = COA::where('accurate_id', $data['accurate_id'])->first();

                        // Jika COA belum ada, buat COA-nya dulu karena Kas/Bank di Accurate ADALAH COA
                        $syncTime = now();
                        $coaData = [
                            'kode_coa' => $kodeKasBank,
                            'nama_coa' => $namaKasBank,
                            'tipe_akun' => 'asset', // Kas/Bank selalu masuk tipe asset (CASH_BANK)
                            'is_active' => $data['is_active'] ?? true,
                            'currency_code' => $data['currency_code'] ?? 'IDR',
                            'saldo' => $data['saldo'] ?? 0,
                            'as_of_date' => $syncTime->toDateString(),
                            'deskripsi' => $data['deskripsi'] ?? null,
                            'accurate_id' => $data['accurate_id'],
                            'synced_from_accurate' => true,
                            'last_sync_at' => $syncTime,
                        ];

                        if ($coa) {
                            $coa->fill($coaData);
                            $coa->saveQuietly();
                        } else {
                            $coa = COA::create($coaData);
                        }

                        $kb = KasBank::where('accurate_id', $data['accurate_id'])->first();

                        // Jika tidak ketemu by accurate_id, coba cari by kode_kas_bank
                        if (! $kb) {
                            $kb = KasBank::where('kode_kas_bank', $kodeKasBank)->first();
                        }

                        $kbData = [
                            'kode_kas_bank' => $kodeKasBank,
                            'nama_kas_bank' => $namaKasBank,
                            'is_active' => $data['is_active'] ?? true,
                            'currency_code' => $data['currency_code'] ?? 'IDR',
                            'tipe_akun' => 'CASH_BANK',
                            'saldo' => $data['saldo'] ?? 0,
                            'as_of_date' => $coaData['as_of_date'],
                            'deskripsi' => $data['deskripsi'] ?? null,
                            'accurate_id' => $data['accurate_id'],
                            'coa_id' => $coa->coa_id,
                            'last_sync_at' => $syncTime,
                        ];

                        if ($kb) {
                            $updated++;
                            $kb->fill($kbData);
                            $kb->saveQuietly();
                        } else {
                            $created++;
                            $kb = KasBank::create($kbData);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to sync Kas/Bank: '.$e->getMessage());
                        $failed++;
                    }
                }

                // SMART SYNC: Hanya nonaktifkan yang sebelumnya tersinkron tapi sekarang tidak ada di response Accurate
                // PENTING: Hanya lakukan jika ini adalah FULL SYNC
                $deactivated = 0;
                if (! $lastModified) {
                    $deactivated = KasBank::whereNotNull('accurate_id')
                        ->whereNotIn('accurate_id', $accurateIdsFromResponse)
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'last_sync_at' => now(),
                        ]);
                }

                return [
                    'created' => $created,
                    'updated' => $updated,
                    'deactivated' => $deactivated,
                    'failed' => $failed,
                    'is_delta' => ! empty($lastModified),
                ];
            });

            $syncType = $result['is_delta'] ? 'Incremental' : 'Full';
            $successMsg = "Sinkronisasi Kas/Bank ({$syncType}) berhasil. ";
            $successMsg .= "{$result['created']} baru, ";
            $successMsg .= "{$result['updated']} diperbarui";

            if (! $result['is_delta']) {
                $successMsg .= ", {$result['deactivated']} dinonaktifkan.";
            } else {
                $successMsg .= '.';
            }

            if ($result['failed'] > 0) {
                $successMsg .= " ({$result['failed']} data gagal diproses).";
            }

            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'success',
                'judul' => 'Sinkronisasi Kas/Bank Berhasil',
                'pesan' => $successMsg,
                'is_read' => false,
            ]);

            return redirect()->route('finance.masterdata.kas_bank.index')
                ->with('success', $successMsg);
        } catch (\Exception $e) {
            $errorMsg = 'Terjadi kesalahan sistem saat sinkronisasi Kas/Bank: '.$e->getMessage();

            Notifikasi::create([
                'user_id' => auth()->id(),
                'tipe' => 'error',
                'judul' => 'Kesalahan Sinkronisasi Kas/Bank',
                'pesan' => $errorMsg,
                'is_read' => false,
            ]);

            return redirect()->route('finance.masterdata.kas_bank.index')
                ->with('error', $errorMsg);
        }
    }

    public function checkBalance(KasBank $kasBank)
    {
        if (! $kasBank->accurate_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum terhubung dengan Accurate',
            ], 400);
        }

        $result = $this->accurateService->getAccountBalance($kasBank->accurate_id);

        if ($result['success']) {
            $syncTime = now();
            // Update KasBank
            $kasBank->saldo = $result['balance'];
            $kasBank->as_of_date = $syncTime->toDateString();
            $kasBank->last_sync_at = $syncTime;
            $kasBank->saveQuietly();

            // SINKRONISASI: Update linked COA agar data di laporan (Buku Besar/Recon) konsisten
            // Gunakan accurate_id untuk robustness
            COA::where('accurate_id', $kasBank->accurate_id)->update([
                'saldo' => $result['balance'],
                'as_of_date' => $syncTime->toDateString(),
                'last_sync_at' => $syncTime,
            ]);
        }

        return response()->json($result);
    }
}
