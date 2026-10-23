<?php
namespace App\Http\Controllers;
use App\Libraries\Validators;
class Callback extends Controller
{

    private function sendWebhook($id) {
        $artisanPath = base_path('artisan');
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            pclose(popen("start /B php \"{$artisanPath}\" webhook:send {$id}", "r"));
        } else {
            shell_exec("php \"{$artisanPath}\" webhook:send '{$id}' > /dev/null 2>&1 &");
        }
    }
    
    public function solitpay_payin() {
        if(request()->ip() !== "43.224.1.241"){
            return response()->json([
                'responseMessage' => 'You are not Authorized',
                'returnCode' => -1,
            ], 401);
        } 
        $data = json_decode(request()->getContent());
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnReference);
        if(isset($transaction['status']) && in_array($transaction['status'],["Pending"])) {
            if($data->txnStatus == "Success" && round($transaction['amount']) == round($data->paid_amount)) {
                //transaction successfull
                $utr = $data->rrn;
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Success",
                  "payee_name" => $data->payeeDetails->name,
                  "payee_vpa" => $data->payeeDetails->vpa,
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                  "success_at" => date("Y-m-d H:i:s"),
                  "rrn" => $utr
                ]);

                // Payin funds remain unsettled until Admin/AutoSettlement settles them to Wallet or Bank.
                // Wallet is NOT immediately credited here to prevent premature usage and double payout.
                $this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Failed"){
                //transaction failed
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Failed",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                ]);
                $this->sendWebhook($transaction['id']);
            }
        }
        if(isset($transaction['id'])){
            $transactionResponseModel = new \App\Models\Transactionres();
            $transactionResponseModel->create([
              "txnid" => $transaction['txnid'],
              "type" => "webhook",
              "data" => json_encode($data),
              "webhook_ip" => request()->ip()
            ]);
        }
        return response()->json([
           'responseMessage' => 'Successful',
           'returnCode' => 0,
        ], 200);
    }
    
    public function solitpay_payout(){
        if(request()->ip() !== "43.224.1.241"){
            return response()->json([
                'responseMessage' => 'You are not Authorized',
                'returnCode' => -1,
            ], 401);
        } 
        $data = json_decode(request()->getContent());
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnReference);
        if(isset($transaction['status']) && in_array($transaction['status'],["Pending", "Processing"])) {
            if($data->txnStatus == "Success") {
                //transaction successfull
                $utr = $data->rrn;
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Success",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                  "success_at" => date("Y-m-d H:i:s"),
                  "rrn" => $utr
                ]);

                $this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Failed"){
                //transaction failed
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Failed",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                ]);
                $this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Expired"){
                //transaction expired
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Expired",
                ]);
                $this->sendWebhook($transaction['id']);
            }
        }
        if(isset($transaction['id'])){
            $transactionResponseModel = new \App\Models\Transactionres();
            $transactionResponseModel->create([
              "txnid" => $transaction['txnid'],
              "type" => "webhook",
              "data" => json_encode($data),
              "webhook_ip" => request()->ip()
            ]);
        }
        return response()->json([
           'responseMessage' => 'Successful',
           'returnCode' => 0,
        ], 200);
    }

    public function zenova_payin()
    {
        $raw = request()->getContent();
        $data = json_decode($raw);

        // Log every callback (for debugging / audit)
        $transactionResponseModel = new \App\Models\Transactionres();
        $transactionResponseModel->create([
            "txnid" => $data->txnID ?? 'UNKNOWN',
            "type" => "webhook",
            "data" => $raw,
            "webhook_ip" => request()->ip()
        ]);

        // Validate required fields
        if (empty($data->txnID) || empty($data->status)) {
            return response()->json([
                "isSuccess" => false,
                "message" => "Missing required parameters",
                "result" => "Failed"
            ], 400);
        }

        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnID);

        if ($transaction && isset($transaction['status']) && $transaction['status'] == "Pending") {

            // When status = 200, it means Success in Zenova
            if ($data->status == 200 && round($transaction['amount']) == round($data->payerAmount)) {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Success",
                    "payee_name" => $data->payerName ?? '',
                    "payee_vpa" => $data->payerVA ?? '',
                    "txn_complete_time" => $data->TxnCompletionDate ?: date("Y-m-d H:i:s"),
                    "success_at" => date("Y-m-d H:i:s"),
                    "rrn" => $data->BankRRN ?? ''
                ]);

                // Payin funds remain unsettled until Admin/AutoSettlement settles them to Wallet or Bank.
                // Wallet is NOT immediately credited here to prevent premature usage and double payout.
                $this->sendWebhook($transaction['id']);
            } else {
                // Transaction failed or mismatch
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Failed",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                ]);

                $this->sendWebhook($transaction['id']);
            }
        }

        // Send Zenova expected response
        return response()->json([
            "isSuccess" => true,
            "message" => "Successfully Insert",
            "result" => "Success"
        ], 200);
    }

    
    public function zenova_payout()
    {
        $raw = request()->getContent();
        $data = json_decode($raw);

        // Log raw callback
        $transactionResponseModel = new \App\Models\Transactionres();
        $transactionResponseModel->create([
            "txnid" => $data->txnid ?? 'UNKNOWN',
            "type" => "webhook",
            "data" => $raw,
            "webhook_ip" => request()->ip()
        ]);

        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnid); // use txnid from callback

        if (isset($transaction['status']) && in_array($transaction['status'], ["Pending", "Processing"])) {

            if (strtolower($data->status) === "success") {
                // Transaction successful
                $utr = $data->rrn ?? $data->BankRRN ?? '';
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Success",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                    "success_at" => date("Y-m-d H:i:s"),
                    "rrn" => $utr,
                    "optxid" => $data->optxid ?? '',
                    "amount" => $data->amount ?? $transaction['amount']
                ]);

                $this->sendWebhook($transaction['id']);

            } elseif (strtolower($data->status) === "failed") {
                // Transaction failed
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Failed",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                    "rrn" => $data->rrn ?? $data->BankRRN ?? ''
                ]);

                $this->sendWebhook($transaction['id']);

            } elseif (strtolower($data->status) === "expired") {
                // Transaction expired
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Expired",
                    "txn_complete_time" => date("Y-m-d H:i:s")
                ]);
                $this->sendWebhook($transaction['id']);
            }
        }

        // Respond to Zenova
        return response()->json([
            "isSuccess" => true,
            "message" => "Successfully Insert",
            "result" => "Success"
        ], 200);
    }

}