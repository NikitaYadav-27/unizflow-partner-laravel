<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ApiWebhook;

class SendWebhook extends Command
{
    
    protected $signature = 'webhook:send {id}';

     
    protected $description = 'Send Webhook by transaction id';

    
    public function handle()
    {
        $id = $this->argument('id');
        if (empty($id)) {
            $this->error("Please provide a transaction id.");
            return 1;
        }

        $transactionModel = new Transaction();
        $transaction = $transactionModel->where('id', $id)->first();
        if ($transaction) {
            $this->sendWebhook($transaction);
        }

        return 0;
    }

    private function sendWebhook($transaction)
    {
        $startTime = microtime(true);
        $payload = [];
        if (!isset($transaction['id'])) {
            return false;
        }

        if ($transaction['service'] == "payin") {
            $payload = [
                "txnid" => $transaction['txnid'],
                "txnReference" => $transaction['merchant_txnid'],
                "txnStatus" => $transaction['status'],
                "rrn" => $transaction['rrn'],
                "amount" => $transaction['amount'],
                "paid_amount" => $transaction['amount'],
                "payeeDetails" => [
                    "name" => $transaction['payee_name'],
                    "vpa" => $transaction['payee_vpa']
                ],
                "datetime" => date("Y-m-d H:i:s")
            ];
        } elseif ($transaction['service'] == "payout") {
            $payload = [
                "txnid" => $transaction['txnid'],
                "txnReference" => $transaction['merchant_txnid'],
                "txnStatus" => $transaction['status'],
                "rrn" => $transaction['rrn'],
                "amount" => $transaction['amount'],
                "datetime" => date("Y-m-d H:i:s")
            ];
        }

        $userModel = new User();
        $user = $userModel->find($transaction['user_id']);
        if (!isset($user['id'])) {
            return false;
        }
        if ($transaction['status'] == "Expired" && ($user['expired_webhook'] ?? 0) == 0) {
            return false;
        }

        $url = "";
        if ($transaction['service'] == "payin") {
            $url = $user['payin_webhook'] ?? "";
        } elseif ($transaction['service'] == "payout") {
            $url = $user['payout_webhook'] ?? "";
        }

        if (empty($url)) {
            return false;
        }

        $jsonData = json_encode($payload);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $timeTaken = round(microtime(true) - $startTime, 4);

        $webhookModel = new ApiWebhook();
        $webhookModel->create([
            'path' => $url,
            'user_id' => $transaction['user_id'],
            'txnid' => $transaction['txnid'],
            'payload' => $jsonData,
            'response' => $response,
            'response_code' => $httpCode,
            'time_taken' => $timeTaken
        ]);

        return true;
    }
}
