<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Transactionres;
use App\Models\User;

class ProcessTxn extends Command
{
    
    protected $signature = 'txn:process {txnid}';

    
    protected $description = 'Process a payout by txnid';

    
    public function handle()
    {
        $txnid = $this->argument('txnid');
        if (empty($txnid)) {
            $this->error("Please provide a txnid.");
            return 1;
        }

        $transactionModel = new Transaction();
        $transaction = $transactionModel->where('txnid', $txnid)->first();

        if ($transaction && $transaction['debited'] == 1 && $transaction['processed'] == 0 && $transaction['service'] == "payout") {
            $transactionModel->where('id', $transaction['id'])->update(["processed" => 1]);

            if (env('PAYMENT_BYPASS', false)) {
                $utr = 'UTR' . date('YmdHis') . rand(100, 999);
                $transactionModel->where('id', $transaction['id'])->update([
                    "reference_id" => "REF" . time() . rand(100, 999),
                    "rrn" => $utr,
                    "status" => "Success",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                    "success_at" => date("Y-m-d H:i:s")
                ]);
                $this->sendWebhook($transaction['id']);
                return 0;
            }

            if ($transaction['payout_provider'] == 2) {
                $this->process_with_solitpay($transaction);
            } elseif ($transaction['payout_provider'] == 4) {
                $this->process_with_zenova($transaction);
            } elseif (in_array($transaction['payout_provider'], [13])) {
                $this->process_with_pikelpoint($transaction);
            } elseif (in_array($transaction['payout_provider'], [12, 13])) {
                $this->process_with_pikelpoint($transaction);
            }
        }

        return 0;
    }

    private function process_with_solitpay($transaction)
    {
        $transactionModel = new Transaction();
        $solitpay = new \App\Libraries\Solitpay();
        $result = $solitpay->makePayout(
            $transaction['txnid'],
            $transaction['name'],
            $transaction['phone'],
            $transaction['amount'],
            $transaction['account_number'],
            $transaction['ifsc_code']
        );

        if (isset($result->status) && strtolower($result->status) == "success") {
            $transactionModel->where('id', $transaction['id'])->update([
                "reference_id" => $result->data->transaction->txnid ?? '',
                "rrn" => $result->data->transaction->utr ?? '',
                "status" => "Processing"
            ]);
        } elseif (isset($result->status) && strtolower($result->status) == "error") {
            $respMessage = $result->message ?? "";
            $transactionModel->where('id', $transaction['id'])->update([
                "status" => "Failed",
                "reason" => $respMessage,
                "txn_complete_time" => date("Y-m-d H:i:s")
            ]);
            $this->sendWebhook($transaction['id']);
        }

        $transactionResponseModel = new Transactionres();
        $request = $solitpay->getRequest();
        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "request",
            "data" => json_encode($request)
        ]);

        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "creation",
            "data" => json_encode($result)
        ]);
    }

    private function process_with_zenova($transaction)
    {
        $transactionModel = new Transaction();
        $userModel = new User();
        $user = $userModel->find($transaction['user_id']);

        $zenova = new \App\Libraries\Zenova();
        $result = $zenova->makePayout(
            $user['merchant_id'] ?? '',
            '12355',
            $transaction['phone'],
            $transaction['name'],
            $transaction['account_number'],
            $transaction['ifsc_code'],
            $transaction['amount'],
            $transaction['txnid']
        );

        if (isset($result->isSuccess) && $result->isSuccess == true) {
            $transactionModel->where('id', $transaction['id'])->update([
                "reference_id" => $transaction['txnid'],
                "status" => $result->status ?? "Processing"
            ]);
        } else {
            $respMessage = $result->message ?? "Unknown error";
            $transactionModel->where('id', $transaction['id'])->update([
                "status" => "Failed",
                "reason" => $respMessage,
                "txn_complete_time" => date("Y-m-d H:i:s")
            ]);
            $this->sendWebhook($transaction['id']);
        }

        $transactionResponseModel = new Transactionres();
        $request = $zenova->getRequest();
        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "request",
            "data" => json_encode($request)
        ]);

        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "creation",
            "data" => json_encode($result)
        ]);
    }

    private function process_with_pikelpoint($transaction)
    {
        $transactionModel = new Transaction();
        $userModel = new User();
        $user = $userModel->find($transaction['user_id']);

        $pikelpoint = new \App\Libraries\Pikelpoint();
        $result = $pikelpoint->makePayout(
            $transaction['txnid'],
            $transaction['name'],
            $transaction['phone'],
            $transaction['amount'],
            $transaction['account_number'],
            $transaction['ifsc_code'],
            $user['email'] ?? '',
            $transaction['bank_name'] ?? 'Bank'
        );

        if (isset($result->isSuccess) && $result->isSuccess == true) {
            $transactionModel->where('id', $transaction['id'])->update([
                "reference_id" => $result->gateway_id ?? $transaction['txnid'],
                "rrn" => $result->utr ?? 'NA',
                "status" => $result->txnStatus ?? "Pending"
            ]);
        } else {
            $respMessage = $result->message ?? "Payout failed";
            $transactionModel->where('id', $transaction['id'])->update([
                "status" => "Failed",
                "reason" => $respMessage,
                "txn_complete_time" => date("Y-m-d H:i:s")
            ]);
            $this->sendWebhook($transaction['id']);
        }

        $transactionResponseModel = new Transactionres();
        $request = $pikelpoint->getRequest();
        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "request",
            "data" => json_encode($request)
        ]);

        $transactionResponseModel->create([
            "txnid" => $transaction['txnid'],
            "type" => "creation",
            "data" => json_encode($result)
        ]);
    }

    private function sendWebhook($id)
    {
        $artisanPath = base_path('artisan');
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            pclose(popen("start /B php \"{$artisanPath}\" webhook:send {$id}", "r"));
        } else {
            shell_exec("php \"{$artisanPath}\" webhook:send '{$id}' > /dev/null 2>&1 &");
        }
    }
}
