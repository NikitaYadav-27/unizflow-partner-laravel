<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\AutoSettlement;
use App\Models\SettlementPackageSlot;

class ProcessSettlements extends Command
{
    
    protected $signature = 'settlements:process 
                            {time? : Execution time slot (e.g. 10:00, 14:00, 18:00, "all", or "auto")}
                            {--time= : Execution time slot option}
                            {--date= : Target settlement date (default: today Y-m-d)}
                            {--force : Bypass runtime already-run check}';

    
    protected $description = 'Process automatic dynamic time-slot settlements according to settlement packages and slots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeInput = $this->argument('time') ?: $this->option('time');
        $date = $this->option('date') ?: date('Y-m-d');
        $force = (bool) $this->option('force');

        if (empty($timeInput) || $timeInput === 'auto') {
            $timeInput = date('H:i');
        }

        $this->info("=================================================");
        $this->info(" Running Dynamic Auto-Settlement Engine");
        $this->info(" Date:       {$date}");
        $this->info(" Target:     " . ($timeInput === 'all' ? 'All active slots' : "Slot {$timeInput}"));
        $this->info(" Force Mode: " . ($force ? 'YES' : 'NO'));
        $this->info("=================================================");

        if (strtolower($timeInput) === 'all') {
            $slots = SettlementPackageSlot::where('status', 1)
                ->select('settlement_time')
                ->distinct()
                ->orderBy('settlement_time', 'asc')
                ->pluck('settlement_time');

            if ($slots->isEmpty()) {
                $this->warn("No active settlement package slots found in database.");
                $res = AutoSettlement::process($date, null, $force);
                $this->reportResult($res);
                return Command::SUCCESS;
            }

            foreach ($slots as $slotTime) {
                $this->line("\n--- Processing Slot: [{$slotTime}] ---");
                $res = AutoSettlement::process($date, $slotTime, $force);
                $this->reportResult($res);
            }

            return Command::SUCCESS;
        }

        $hasMatchingSlot = SettlementPackageSlot::where('status', 1)->where('settlement_time', $timeInput)->exists();
        if (!$hasMatchingSlot && in_array($timeInput, [date('H:i'), 'auto'])) {
            $this->line("No active package slots scheduled for current time [{$timeInput}].");
            return Command::SUCCESS;
        }

        $res = AutoSettlement::process($date, $timeInput, $force);
        $this->reportResult($res);

        return Command::SUCCESS;
    }

    
    private function reportResult(array $res)
    {
        if (($res['status'] ?? '') === 'already_run') {
            $this->warn("Already processed: " . ($res['message'] ?? 'Skipped.'));
            return;
        }

        $this->info("Eligible Automatic Merchants: " . ($res['eligible_auto_users_count'] ?? 0));
        $this->info("Skipped Manual Merchants:    " . ($res['skipped_manual_users_count'] ?? 0));
        $this->info("Settlement Batches Created:   " . ($res['settlements_created'] ?? 0));
        $this->info("Total Amount Settled:         ₹" . number_format($res['total_amount_settled'] ?? 0, 2));

        if (!empty($res['details'])) {
            $rows = [];
            foreach ($res['details'] as $item) {
                $rows[] = [
                    $item['merchant_id'] ?? $item['user_id'],
                    $item['batch_id'] ?? '-',
                    $item['settlement_time'] ?? '-',
                    "₹" . number_format((float)($item['amount'] ?? 0), 2),
                    ($item['from_time'] ?? '') . ' to ' . ($item['to_time'] ?? '')
                ];
            }
            $this->table(['Merchant ID', 'Batch ID', 'Slot Time', 'Net Amount', 'Window'], $rows);
        }
    }
}
