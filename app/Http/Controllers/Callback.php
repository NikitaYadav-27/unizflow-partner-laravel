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
                $utr = $data->rrn;
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Success",
                  "payee_name" => $data->payeeDetails->name,
                  "payee_vpa" => $data->payeeDetails->vpa,
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                  "success_at" => date("Y-m-d H:i:s"),
                  "rrn" => $utr
                ]);

$this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Failed"){
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
                $utr = $data->rrn;
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Success",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                  "success_at" => date("Y-m-d H:i:s"),
                  "rrn" => $utr
                ]);

                $this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Failed"){
                $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Failed",
                  "txn_complete_time" => date("Y-m-d H:i:s"),
                ]);
                $this->sendWebhook($transaction['id']);
            }elseif($data->txnStatus == "Expired"){
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

        $transactionResponseModel = new \App\Models\Transactionres();
        $transactionResponseModel->create([
            "txnid" => $data->txnID ?? 'UNKNOWN',
            "type" => "webhook",
            "data" => $raw,
            "webhook_ip" => request()->ip()
        ]);

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

            if ($data->status == 200 && round($transaction['amount']) == round($data->payerAmount)) {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Success",
                    "payee_name" => $data->payerName ?? '',
                    "payee_vpa" => $data->payerVA ?? '',
                    "txn_complete_time" => $data->TxnCompletionDate ?: date("Y-m-d H:i:s"),
                    "success_at" => date("Y-m-d H:i:s"),
                    "rrn" => $data->BankRRN ?? ''
                ]);

$this->sendWebhook($transaction['id']);
            } else {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Failed",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                ]);

                $this->sendWebhook($transaction['id']);
            }
        }

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

        $transactionResponseModel = new \App\Models\Transactionres();
        $transactionResponseModel->create([
            "txnid" => $data->txnid ?? 'UNKNOWN',
            "type" => "webhook",
            "data" => $raw,
            "webhook_ip" => request()->ip()
        ]);

        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByMerchantId($data->txnid);

        if (isset($transaction['status']) && in_array($transaction['status'], ["Pending", "Processing"])) {

            if (strtolower($data->status) === "success") {
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
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Failed",
                    "txn_complete_time" => date("Y-m-d H:i:s"),
                    "rrn" => $data->rrn ?? $data->BankRRN ?? ''
                ]);

                $this->sendWebhook($transaction['id']);

            } elseif (strtolower($data->status) === "expired") {
                $transactionModel->where('id', $transaction['id'])->update([
                    "status" => "Expired",
                    "txn_complete_time" => date("Y-m-d H:i:s")
                ]);
                $this->sendWebhook($transaction['id']);
            }
        }

        return response()->json([
            "isSuccess" => true,
            "message" => "Successfully Insert",
            "result" => "Success"
        ], 200);
    }

   
public function pikelpoint_payin()
{
    \Log::info('--- Pikelpoint Payin Webhook Received ---', [
        'ip' => request()->ip(),
        'method' => request()->method(),
        'headers' => request()->headers->all(),
        'body' => request()->getContent()
    ]);

    if (request()->isMethod('options')) {
        return response()->json(['status' => true, 'message' => 'OK'], 200);
    }

    $raw = request()->getContent();
    $decoded = json_decode($raw, true) ?: request()->all();
    $data = $decoded['data'] ?? $decoded;

    if (!is_array($data)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid webhook payload'
        ], 400);
    }

    $merchantOrderId = $data['merchant_order_id']
        ?? $decoded['merchant_order_id']
        ?? ($data['ext_transaction_id'] ?? ($decoded['ext_transaction_id'] ?? null));

    $statusStr = strtoupper((string) (
        $data['status'] ?? $decoded['status'] ?? ''
    ));

    if (empty($merchantOrderId) || empty($statusStr)) {
        return response()->json([
            'status' => false,
            'message' => 'Missing required parameters'
        ], 400);
    }

    $pikelpoint = new \App\Libraries\Pikelpoint();

    $signature = request()->header('x-w-signature-new')
        ?: (request()->header('x-webhook-signature') ?: (request()->header('x-signature') ?: request()->header('signature')));

    $secretConfig = config('providers.pikelpoint.payin_webhook_secret');
    if (!empty($secretConfig) && !empty($signature)) {
        if (!$pikelpoint->verifyWebhookSignature($raw, $signature, $secretConfig)) {
            \Log::warning('Pikelpoint Payin Webhook signature mismatch', [
                'headers' => request()->headers->all(),
                'body' => $raw
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Invalid signature'
            ], 401);
        }
    }
    $transactionModel = new \App\Models\Transaction();

    $transaction = $transactionModel->findTxnByMerchantId($merchantOrderId);

    if (!$transaction) {
        $transaction = $transactionModel->findTxnByTxnid($merchantOrderId);
    }

    if (!$transaction || !isset($transaction['id'])) {
        return response()->json([
            'status' => false,
            'message' => 'Transaction not found'
        ], 404);
    }

    $transactionResponseModel = new \App\Models\Transactionres();

    $transactionResponseModel->create([
        "txnid" => $transaction['txnid'],
        "type" => "webhook",
        "data" => $raw,
        "webhook_ip" => request()->ip()
    ]);

    if ($transaction['status'] == "Pending") {

        if (in_array($statusStr, ["SUCCESS", "CLAIMED", "PAID", "COMPLETED", "1", "TRUE"])) {

            $amount = $data['amount']
                ?? $data['paid_amount']
                ?? $data['txn_amount']
                ?? null;

            if ($amount === null || (float) $transaction['amount'] != (float) $amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction amount mismatch'
                ], 400);
            }

            $utr = $data['utr']
                ?? $data['rrn']
                ?? $data['bank_rrn']
                ?? '';

            $updated = $transactionModel
                ->where('id', $transaction['id'])
                ->where('status', 'Pending')
                ->update([
                    "status" => "Success",
                    "payee_name" => $data['payer_name'] ?? $data['name'] ?? ($data['payer_account_name'] ?? ''),
                    "payee_vpa" => $data['payer_vpa'] ?? $data['vpa'] ?? ($data['upi_id'] ?? ''),
                    "txn_complete_time" => now(),
                    "success_at" => now(),
                    "rrn" => $utr
                ]);

            if ($updated) {
                $this->sendWebhook($transaction['id']);
            }

        } elseif (in_array($statusStr, ["FAILED", "FAILURE", "CANCELLED", "0", "FALSE"])) {

            $updated = $transactionModel
                ->where('id', $transaction['id'])
                ->where('status', 'Pending')
                ->update([
                    "status" => "Failed",
                    "txn_complete_time" => now(),
                ]);

            if ($updated) {
                $this->sendWebhook($transaction['id']);
            }

        } elseif (in_array($statusStr, ["EXPIRED"])) {

            $updated = $transactionModel
                ->where('id', $transaction['id'])
                ->where('status', 'Pending')
                ->update([
                    "status" => "Expired",
                    "txn_complete_time" => now(),
                ]);

            if ($updated) {
                $this->sendWebhook($transaction['id']);
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Payin webhook processed successfully'
    ], 200);
}


    
public function pikelpoint_payout()
{
    \Log::info('--- Pikelpoint Payout Webhook Received ---', [
        'ip' => request()->ip(),
        'method' => request()->method(),
        'headers' => request()->headers->all(),
        'body' => request()->getContent()
    ]);

    if (request()->isMethod('options')) {
        return response()->json(['status' => true, 'message' => 'OK'], 200);
    }

    $raw = request()->getContent();
    $decoded = json_decode($raw, true) ?: request()->all();
    $data = $decoded['data'] ?? $decoded;

    if (!is_array($data)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid webhook payload'
        ], 400);
    }

    $merchantOrderId = $data['merchant_order_id']
        ?? $decoded['merchant_order_id']
        ?? ($data['ext_transaction_id'] ?? ($decoded['ext_transaction_id'] ?? null));

    $statusStr = strtoupper((string) (
        $data['status'] ?? $decoded['status'] ?? ''
    ));

    if (empty($merchantOrderId) || empty($statusStr)) {
        return response()->json([
            'status' => false,
            'message' => 'Missing required parameters'
        ], 400);
    }

    $pikelpoint = new \App\Libraries\Pikelpoint();

    $signature = request()->header('x-w-signature-new')
        ?: (request()->header('x-webhook-signature') ?: (request()->header('x-signature') ?: request()->header('signature')));

    $secretConfig = config('providers.pikelpoint.payout_webhook_secret');
    if (!empty($secretConfig) && !empty($signature)) {
        if (!$pikelpoint->verifyWebhookSignature($raw, $signature)) {
            \Log::warning('Pikelpoint Payout Webhook signature mismatch', [
                'headers' => request()->headers->all(),
                'body' => $raw
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Invalid signature'
            ], 401);
        }
    }
    $transactionModel = new \App\Models\Transaction();

    $transaction = $transactionModel->findTxnByMerchantId($merchantOrderId);

    if (!$transaction) {
        $transaction = $transactionModel->findTxnByTxnid($merchantOrderId);
    }

    if (!$transaction || !isset($transaction['id'])) {
        return response()->json([
            'status' => false,
            'message' => 'Transaction not found'
        ], 404);
    }

    $transactionResponseModel = new \App\Models\Transactionres();

    $transactionResponseModel->create([
        "txnid" => $transaction['txnid'],
        "type" => "webhook",
        "data" => $raw,
        "webhook_ip" => request()->ip()
    ]);

    if (in_array($transaction['status'], ["Pending", "Processing"])) {

        $updateData = [];

        if (in_array($statusStr, ["SUCCESS", "PAID", "COMPLETED", "1", "TRUE"])) {

            $updateData = [
                "status" => "Success",
                "txn_complete_time" => now(),
                "success_at" => now(),
                "rrn" => $data['utr']
                    ?? $data['rrn']
                    ?? $data['bank_rrn']
                    ?? ''
            ];

        } elseif (in_array($statusStr, ["FAILED", "FAILURE", "CANCELLED", "0", "FALSE"])) {

            $updateData = [
                "status" => "Failed",
                "txn_complete_time" => now(),
                "rrn" => $data['utr']
                    ?? $data['rrn']
                    ?? $data['bank_rrn']
                    ?? ''
            ];

        } elseif (in_array($statusStr, ["REFUNDED", "EXPIRED"])) {

            $updateData = [
                "status" => "Expired",
                "txn_complete_time" => now(),
                "rrn" => $data['utr']
                    ?? $data['rrn']
                    ?? $data['bank_rrn']
                    ?? ''
            ];
        }

        if (!empty($updateData)) {

            $updated = $transactionModel
                ->where('id', $transaction['id'])
                ->whereIn('status', ['Pending', 'Processing'])
                ->update($updateData);

            if ($updated) {
                $this->sendWebhook($transaction['id']);
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Payout webhook processed successfully'
    ], 200);
}

}