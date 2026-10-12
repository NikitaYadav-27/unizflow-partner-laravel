<?php
namespace App\Http\Controllers;
use App\Libraries\Validators;
class Callback extends Controller
{

    private function sendWebhook($id) {
       shell_exec("php ".env("app.basePath")."spark webhook:send '".$id."' > /dev/null 2>&1 &");
    }
    
    public function solitpay_payin() {
        if(trim($_SERVER['REMOTE_ADDR']) != trim("43.224.1.241")){
            return response()->json([
                'responseMessage' => 'You are not Authorized',
                'returnCode' => -1,
            ], 401);
        } 
        $data = file_get_contents("php://input");
        $data = json_decode($data);
        $transactionModel = new \App\Models\Transaction();
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
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
                  "rrn" => $utr
                ]);

                //add wallet Transaction
                $user = $userModel->find($transaction['user_id']);
                $newBalance = number_format($user['wallet'] + $transaction['net_amount'], 2, '.', '');
                $userModel->where('id', $user['id'])->update([
                    "wallet" => $newBalance
                ]);
                
                $walletModel->create([
                    "user_id" => $user['id'],
                    "type" =>	"CREDIT",
                    "txn_type" =>	"PAYIN",
                    "txnid" => $transaction['txnid'],	
                    "opening" => $user['wallet'],
                    "amount" => $transaction['amount'],
                    "charges" => $transaction['charges'],
                    "gst" => $transaction['gst'],
                    "net_amount" => $transaction['net_amount'],
                    "closing" => $newBalance
                    ]);
            
                //update credited
                $transactionModel->where('id', $transaction['id'])->update([
                    "credited" => 1
                ]);

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
        if(trim($_SERVER['REMOTE_ADDR']) != trim("43.224.1.241")){
            return response()->json([
                'responseMessage' => 'You are not Authorized',
                'returnCode' => -1,
            ], 401);
        } 
        $data = file_get_contents("php://input");
        $data = json_decode($data);
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnReference);
        if(isset($transaction['status']) && in_array($transaction['status'],["Pending"])) {
            if($data->txnStatus == "Success") {
                //transaction successfull
                $utr = $data->rrn;
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Success",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
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
        $raw = file_get_contents("php://input");
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
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnID);

        if ($transaction && isset($transaction['status']) && $transaction['status'] == "Pending") {

            // When status = 200, it means Success in Zenova
            if ($data->status == 200 && round($transaction['amount']) == round($data->payerAmount)) {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Success",
                    "payee_name" => $data->payerName ?? '',
                    "payee_vpa" => $data->payerVA ?? '',
                    "txn_complete_time" => $data->TxnCompletionDate ?: date("Y-m-d H:i:s"),
                    "rrn" => $data->BankRRN ?? ''
                ]);

                //add wallet Transaction
                $user = $userModel->find($transaction['user_id']);
                $newBalance = number_format($user['wallet'] + $transaction['net_amount'], 2, '.', '');
                $userModel->where('id', $user['id'])->update([
                    "wallet" => $newBalance
                ]);

                $walletModel->create([
                    "user_id" => $user['id'],
                    "type" =>	"CREDIT",
                    "txn_type" =>	"PAYIN",
                    "txnid" => $transaction['txnid'],	
                    "opening" => $user['wallet'],
                    "amount" => $transaction['amount'],
                    "charges" => $transaction['charges'],
                    "gst" => $transaction['gst'],
                    "net_amount" => $transaction['net_amount'],
                    "closing" => $newBalance
                    ]);
            
                //update credited
                $transactionModel->where('id', $transaction['id'])->update([
                    "credited" => 1
                ]);
                
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
        $raw = file_get_contents("php://input");
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

        if (isset($transaction['status']) && in_array($transaction['status'], ["Pending"])) {

            if (strtolower($data->status) === "success") {
                // Transaction successful
                $utr = $data->rrn ?? $data->BankRRN ?? '';
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Success",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
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