<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\FundRequest;
use App\Models\Settlement;
use App\Models\HoldWallet;
use App\Models\Credits;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

class RunOperations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operations:work 
                            {--daemon : Run continuously as a background daemon loop} 
                            {--sleep=1 : Sleep interval in seconds between daemon loop iterations} 
                            {--once : Run once and exit (default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process core operations (payouts, topups, refunds, settlements, holds, credits, usages)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daemon = $this->option('daemon');
        $sleep = max(1, (int) $this->option('sleep'));

        if ($daemon) {
            $this->info("Starting Operations Worker daemon (sleep: {$sleep}s)... Press Ctrl+C to exit.");
            while (true) {
                $this->runOperations();
                sleep($sleep);
            }
        } else {
            $this->runOperations();
            $this->info("Operations completed successfully.");
        }

        return Command::SUCCESS;
    }

    /**
     * Run all operational tasks.
     */
    public function runOperations()
    {
        try { $this->clearPayouts(); } catch (\Throwable $e) { $this->error("clearPayouts error: " . $e->getMessage()); }
        try { $this->refundFailedPayouts(); } catch (\Throwable $e) { $this->error("refundFailedPayouts error: " . $e->getMessage()); }
        try { $this->creditSuccessSettlements(); } catch (\Throwable $e) { $this->error("creditSuccessSettlements error: " . $e->getMessage()); }
        try { $this->creditSuccessFundRequests(); } catch (\Throwable $e) { $this->error("creditSuccessFundRequests error: " . $e->getMessage()); }
        try { $this->processHoldTransactions(); } catch (\Throwable $e) { $this->error("processHoldTransactions error: " . $e->getMessage()); }
        try { $this->processCreditsTransactions(); } catch (\Throwable $e) { $this->error("processCreditsTransactions error: " . $e->getMessage()); }
        try { $this->processPendingWalletCreditsDebits(); } catch (\Throwable $e) { $this->error("processPendingWalletCreditsDebits error: " . $e->getMessage()); }
        try { $this->updatePayinPayoutUsers(); } catch (\Throwable $e) { $this->error("updatePayinPayoutUsers error: " . $e->getMessage()); }
        try { $this->updatePayinPayoutProviders(); } catch (\Throwable $e) { $this->error("updatePayinPayoutProviders error: " . $e->getMessage()); }
    }

    private function clearPayouts()
    {
        $transactionModel = new Transaction();
        $userModel = new User();
        $walletModel = new WalletTransaction();
        $transactions = $transactionModel->getPendingDebitPayouts(5);

        foreach ($transactions as $transaction) {
            $user = $userModel->find($transaction['user_id']);
            if (!$user) {
                continue;
            }

            if ($user['wallet'] < $transaction['net_amount']) {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Failed",
                    "reason" => "Insufficient Funds"
                ]);
            } else {
                $newBalance = number_format(($user['wallet'] - $transaction['net_amount']), 2, '.', '');
                $userModel->where('id', $user['id'])->update(["wallet" => $newBalance]);

                $walletModel->create([
                    "user_id" => $user['id'],
                    "type" => "DEBIT",
                    "txn_type" => "PAYOUT",
                    "txnid" => $transaction['txnid'],
                    "opening" => $user['wallet'],
                    "amount" => $transaction['amount'],
                    "charges" => $transaction['charges'],
                    "gst" => $transaction['gst'],
                    "net_amount" => $transaction['net_amount'],
                    "closing" => $newBalance,
                    "remarks" => !empty($transaction['remarks']) ? $transaction['remarks'] : "Payout Debit: " . $transaction['txnid']
                ]);

                $transactionModel->where('id', $transaction['id'])->update(["debited" => 1]);

                $artisanPath = base_path('artisan');
                if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                    pclose(popen("start /B php \"{$artisanPath}\" txn:process {$transaction['txnid']}", "r"));
                } else {
                    shell_exec("php \"{$artisanPath}\" txn:process '{$transaction['txnid']}' > /dev/null 2>&1 &");
                }
            }
        }
    }

    private function refundFailedPayouts()
    {
        $transactionModel = new Transaction();
        $userModel = new User();
        $walletModel = new WalletTransaction();
        $transactions = $transactionModel->getFailedNonRefundedPayouts();

        foreach ($transactions as $transaction) {
            $user = $userModel->find($transaction['user_id']);
            if (!$user) {
                continue;
            }

            $newBalance = number_format($user['wallet'] + $transaction['net_amount'], 2, '.', '');
            $userModel->where('id', $user['id'])->update([
                "wallet" => $newBalance
            ]);

            $walletModel->create([
                "user_id" => $user['id'],
                "type" => "CREDIT",
                "txn_type" => "REFUND",
                "txnid" => $transaction['txnid'],
                "opening" => $user['wallet'],
                "amount" => $transaction['amount'],
                "charges" => $transaction['charges'],
                "gst" => $transaction['gst'],
                "net_amount" => $transaction['net_amount'],
                "closing" => $newBalance,
                "remarks" => "Refund for Failed Payout: " . (!empty($transaction['reason']) ? $transaction['reason'] : $transaction['txnid'])
            ]);

            $transactionModel->where('id', $transaction['id'])->update([
                "refunded" => 1
            ]);
        }
    }

    private function creditSuccessFundRequests()
    {
        $fundRequestModel = new FundRequest();
        $userModel = new User();
        $walletModel = new WalletTransaction();
        $fundRequests = $fundRequestModel->getSuccessPendingRequests();

        foreach ($fundRequests as $fundRequest) {
            $user = $userModel->find($fundRequest['user_id']);
            if (!$user) {
                continue;
            }

            $newBalance = number_format($user['wallet'] + $fundRequest['amount'], 2, '.', '');
            $userModel->where('id', $user['id'])->update([
                "wallet" => $newBalance
            ]);

            $walletModel->create([
                "user_id" => $user['id'],
                "type" => "CREDIT",
                "txn_type" => "TOPUP",
                "txnid" => $fundRequest['txnid'],
                "opening" => $user['wallet'],
                "amount" => number_format($fundRequest['amount'], 2, '.', ''),
                "charges" => 0,
                "gst" => 0,
                "net_amount" => number_format($fundRequest['amount'], 2, '.', ''),
                "closing" => $newBalance,
                "remarks" => !empty($fundRequest['remarks']) ? $fundRequest['remarks'] : "Fund Request Topup"
            ]);

            $fundRequestModel->where('id', $fundRequest['id'])->update(['credited' => 1]);
        }
    }

    private function creditSuccessSettlements()
    {
        $settlementModel = new Settlement();
        $userModel = new User();
        $walletModel = new WalletTransaction();
        $settlements = $settlementModel->getSuccessPendingCredit();

        foreach ($settlements as $settlement) {
            $user = $userModel->find($settlement['user_id']);
            if (!$user) {
                continue;
            }

            $newBalance = number_format($user['wallet'] + $settlement['net_amount'], 2, '.', '');
            $userModel->where('id', $user['id'])->update([
                "wallet" => $newBalance
            ]);

            $walletModel->create([
                "user_id" => $user['id'],
                "type" => "CREDIT",
                "txn_type" => "SETTLEMENT",
                "txnid" => $settlement['txnid'],
                "opening" => $user['wallet'],
                "amount" => $settlement['amount'],
                "charges" => $settlement['charges'],
                "gst" => $settlement['gst'],
                "net_amount" => $settlement['net_amount'],
                "closing" => $newBalance,
                "remarks" => "Auto Settlement Batch " . ($settlement['batch_id'] ?? '')
            ]);

            $settlementModel->where('id', $settlement['id'])->update(['credited' => 1]);
        }
    }

    private function processHoldTransactions()
    {
        $holdWalletModel = new HoldWallet();
        $userModel = new User();
        $walletModel = new WalletTransaction();

        foreach ($holdWalletModel->where("processed", 0)->get() as $txn) {
            $user = $userModel->find($txn['user_id']);
            if (!$user) {
                continue;
            }

            if ($txn['type'] == "hold") {
                $newBalance = number_format($user['wallet'] - $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                    "wallet" => $newBalance
                ]);

                $walletModel->create([
                    "user_id" => $user['id'],
                    "type" => "DEBIT",
                    "txn_type" => "HOLD",
                    "txnid" => $txn['txnid'],
                    "opening" => $user['wallet'],
                    "amount" => $txn['amount'],
                    "charges" => 0,
                    "gst" => 0,
                    "remarks" => $txn['reason'],
                    "net_amount" => $txn['amount'],
                    "closing" => $newBalance
                ]);

                $newHoldBalance = number_format($user['hold_wallet'] + $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                    "hold_wallet" => $newHoldBalance
                ]);

                $holdWalletModel->where('id', $txn['id'])->update([
                    "opening" => $user['hold_wallet'],
                    "processed" => 1,
                    "closing" => $newHoldBalance
                ]);
            } elseif ($txn['type'] == "released") {
                $newBalance = number_format($user['wallet'] + $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                    "wallet" => $newBalance
                ]);

                $walletModel->create([
                    "user_id" => $user['id'],
                    "type" => "CREDIT",
                    "txn_type" => "HOLD-RELEASE",
                    "txnid" => $txn['txnid'],
                    "opening" => $user['wallet'],
                    "amount" => $txn['amount'],
                    "charges" => 0,
                    "remarks" => $txn['reason'],
                    "gst" => 0,
                    "net_amount" => $txn['amount'],
                    "closing" => $newBalance
                ]);

                $newHoldBalance = number_format($user['hold_wallet'] - $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                    "hold_wallet" => $newHoldBalance
                ]);

                $holdWalletModel->where('id', $txn['id'])->update([
                    "opening" => $user['hold_wallet'],
                    "processed" => 1,
                    "closing" => $newHoldBalance
                ]);
            }
        }
    }

    private function processCreditsTransactions()
    {
        $creditsModel = new Credits();
        $userModel = new User();
        $walletModel = new WalletTransaction();

        foreach ($creditsModel->where('debited', 0)->where('debit_granted', 1)->get() as $txn) {
            $user = $userModel->find($txn['user_id']);
            if (!$user) {
                continue;
            }

            $newBalance = number_format($user['wallet'] - $txn['amount'], 2, '.', '');
            $userModel->where('id', $txn['user_id'])->update([
                "wallet" => $newBalance
            ]);

            $walletModel->create([
                "user_id" => $user['id'],
                "type" => "DEBIT",
                "txn_type" => "ADVANCE-DEBIT",
                "txnid" => $txn['txnid'],
                "opening" => $user['wallet'],
                "amount" => $txn['amount'],
                "charges" => 0,
                "gst" => 0,
                "net_amount" => $txn['amount'],
                "closing" => $newBalance,
                "remarks" => $txn['debit_remarks']
            ]);

            $creditsModel->where('id', $txn['id'])->update([
                "debited" => 1,
                "debited_at" => date("Y-m-d H:i:s")
            ]);
        }

        foreach ($creditsModel->where('credited', 0)->get() as $txn) {
            $user = $userModel->find($txn['user_id']);
            if (!$user) {
                continue;
            }

            $newBalance = number_format($user['wallet'] + $txn['amount'], 2, '.', '');
            $userModel->where('id', $txn['user_id'])->update([
                "wallet" => $newBalance
            ]);

            $walletModel->create([
                "user_id" => $user['id'],
                "type" => "CREDIT",
                "txn_type" => "ADVANCE-CREDIT",
                "txnid" => $txn['txnid'],
                "opening" => $user['wallet'],
                "amount" => $txn['amount'],
                "charges" => 0,
                "gst" => 0,
                "net_amount" => $txn['amount'],
                "closing" => $newBalance,
                "remarks" => $txn['credit_remarks']
            ]);

            $creditsModel->where('id', $txn['id'])->update([
                "credited" => 1,
                "credited_at" => date("Y-m-d H:i:s")
            ]);
        }
    }

    private function processPendingWalletCreditsDebits()
    {
        $userModel = new User();
        $walletModel = new WalletTransaction();

        foreach ($walletModel->where("processed", 2)->get() as $transaction) {
            $user = $userModel->find($transaction['user_id']);
            if (!$user) {
                continue;
            }

            if ($transaction['type'] == "DEBIT") {
                $newBalance = number_format($user['wallet'] - $transaction['net_amount'], 2, '.', '');
                $userModel->where('id', $user['id'])->update([
                    "wallet" => $newBalance
                ]);
                $walletModel->where('id', $transaction['id'])->update([
                    "processed" => 1,
                    "opening" => number_format($user['wallet'], 2, '.', ''),
                    "closing" => $newBalance
                ]);
            } elseif ($transaction['type'] == "CREDIT") {
                $newBalance = number_format($user['wallet'] + $transaction['net_amount'], 2, '.', '');
                $userModel->where('id', $user['id'])->update([
                    "wallet" => $newBalance
                ]);
                $walletModel->where('id', $transaction['id'])->update([
                    "processed" => 1,
                    "opening" => number_format($user['wallet'], 2, '.', ''),
                    "closing" => $newBalance
                ]);
            }
        }
    }

    private function updatePayinPayoutUsers()
    {
        $transactionModel = new Transaction();
        $userModel = new User();
        $usersList = [];

        foreach ($userModel->get() as $user) {
            $usersList[$user['id']] = $user['id'];
        }

        $payinUsersList = $usersList;
        $payoutUsersList = $usersList;

        foreach ($transactionModel->getTodaysPayinUser() as $usage) {
            $userModel->where('id', $usage['user_id'])->update(["todays_payin" => $usage['amount']]);
            unset($payinUsersList[$usage['user_id']]);
        }
        if (!empty($payinUsersList)) {
            $userModel->whereIn("id", array_values($payinUsersList))->update(["todays_payin" => 0]);
        }

        foreach ($transactionModel->getTodaysPayoutUser() as $usage) {
            $userModel->where('id', $usage['user_id'])->update(["todays_payout" => $usage['amount']]);
            unset($payoutUsersList[$usage['user_id']]);
        }
        if (!empty($payoutUsersList)) {
            $userModel->whereIn("id", array_values($payoutUsersList))->update(["todays_payout" => 0]);
        }
    }

    private function updatePayinPayoutProviders()
    {
        $transactionModel = new Transaction();
        $providerModel = new Provider();
        $providersList = [];

        foreach ($providerModel->get() as $provider) {
            $providersList[$provider['id']] = $provider['id'];
        }

        $providerUsage = $transactionModel->getTodaysProviderUsage();
        foreach ($providerUsage as $usage) {
            if (!empty($usage['provider_id'])) {
                $providerModel->where('id', $usage['provider_id'])->update([
                    "used_limit" => $usage['total_amount']
                ]);
                unset($providersList[$usage['provider_id']]);
            }
        }

        if (!empty($providersList)) {
            $providerModel->whereIn("id", array_values($providersList))->update(["used_limit" => 0]);
        }
    }
}
