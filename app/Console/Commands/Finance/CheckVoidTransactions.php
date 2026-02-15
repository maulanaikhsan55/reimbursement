<?php

namespace App\Console\Commands\Finance;

use App\Models\Jurnal;
use App\Models\Pengajuan;
use App\Services\AccurateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckVoidTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:check-void';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa apakah transaksi yang ada di Web masih tersedia di Accurate Online';

    public function __construct(protected AccurateService $accurateService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan transaksi void di Accurate...');

        $pengajuans = Pengajuan::whereIn('status', ['terkirim_accurate', 'dicairkan'])
            ->whereNotNull('accurate_transaction_id')
            ->get();

        if ($pengajuans->isEmpty()) {
            $this->info('Tidak ada transaksi aktif yang perlu diperiksa.');

            return 0;
        }

        $voidCount = 0;

        foreach ($pengajuans as $pengajuan) {
            $this->line("Memeriksa: {$pengajuan->nomor_pengajuan} ({$pengajuan->accurate_transaction_id})");

            $result = $this->accurateService->checkTransactionExists($pengajuan->accurate_transaction_id);

            if ($result['success'] && $result['exists'] === false) {
                $this->warn("Transaksi {$pengajuan->accurate_transaction_id} TIDAK DITEMUKAN di Accurate. Menandai sebagai VOID.");

                // Mark as Void
                $pengajuan->update(['status' => 'void_accurate']);

                // Optional: Delete or mark local journal as void too
                Jurnal::where('pengajuan_id', $pengajuan->pengajuan_id)->delete();

                $voidCount++;

                Log::warning('Transaction marked as VOID because it was deleted in Accurate', [
                    'pengajuan_id' => $pengajuan->pengajuan_id,
                    'accurate_id' => $pengajuan->accurate_transaction_id,
                ]);
            }
        }

        $this->info('=========================================');
        $this->info('Pengecekan Selesai!');
        $this->info("Total Transaksi Void ditemukan: $voidCount");
        $this->info('=========================================');

        return 0;
    }
}
