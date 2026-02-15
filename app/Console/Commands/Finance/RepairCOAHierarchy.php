<?php

namespace App\Console\Commands\Finance;

use App\Models\COA;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RepairCOAHierarchy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:repair-coa-hierarchy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically detect and repair orphan COA accounts using code inference';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting COA Hierarchy Repair...');

        $orphans = COA::whereNotNull('accurate_id')
            ->whereNull('parent_coa_id')
            ->get();

        $repairedCount = 0;

        foreach ($orphans as $coa) {
            $number = $coa->kode_coa;

            // Logic: if code is 110101, parent might be 1101
            if (strlen($number) > 4) {
                $parentCode = substr($number, 0, -2);
                $parent = COA::where('kode_coa', $parentCode)->first();

                if ($parent) {
                    $coa->parent_coa_id = $parent->coa_id;
                    $coa->saveQuietly();
                    $repairedCount++;
                    $this->line("Repaired: {$number} -> linked to {$parentCode}");
                }
            }
        }

        $this->info("Repair completed. Total repaired: {$repairedCount}");
        Log::info("COA Hierarchy Repair completed. Total repaired: {$repairedCount}");

        return Command::SUCCESS;
    }
}
