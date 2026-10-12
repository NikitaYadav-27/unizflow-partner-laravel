<?php
namespace App\Http\Controllers;

class Cron extends Controller {
    
    public function index() {
        //echo "<pre>";
        $ipAddress = request()->ip();
        if($ipAddress != env("server.ip")) {
            return response()->json([
               "status" => "error",
               "message" => "Unauthorised"
            ], 401);
        }
        $this->clear_payouts();
        $this->refund_failed_payouts();
        $this->credit_success_settlements();
        $this->credit_success_fund_requests();
        $this->process_hold_transactions();
        $this->process_credits_transactions();
        $this->process_pending_wallet_credits_debits();
        $this->update_payin_payout_users();
        $this->update_payin_payout_providers();
        
  
    }
    
    private function clear_payouts() {
        $transactionModel = new \App\Models\Transaction();
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        $transactions = $transactionModel->getPendingDebitPayouts(5);
        foreach($transactions as $transaction) {
           $user = $userModel->find($transaction['user_id']);
           //check for funds
           if($user['wallet'] < $transaction['net_amount']) {
               //failed the transaction with reason insufficent funds
               $transactionModel->where('id', $transaction['id'])->update([
                  "status" => "Failed",
                  "reason" => "Insufficient Funds"
               ]);
           }else {
               //has sufficient balance to transaction
               //debit the Fund
               $newBalance = number_format(($user['wallet'] - $transaction['net_amount']), 2, '.', '');
               $userModel->where('id', $user['id'])->update(["wallet" => $newBalance]);
               //add wallet transaction for debt
               $walletModel->create([
                  "user_id" => $user['id'],
                  "type" =>	"DEBIT",
                  "txn_type" =>	"PAYOUT",
                  "txnid" => $transaction['txnid'],	
                  "opening" => $user['wallet'],
                  "amount" => $transaction['amount'],
                  "charges" => $transaction['charges'],
                  "gst" => $transaction['gst'],
                  "net_amount" => $transaction['net_amount'],
                  "closing" => $newBalance
               ]);
               $transactionModel->where('id', $transaction['id'])->update(["debited" => 1]);
               //process transaction with provider
               shell_exec("php ".env("app.basePath")."spark txn:process '".$transaction['txnid']."' > /dev/null 2>&1 &");
           }
           
        }
    }
    
    private function refund_failed_payouts() {
        $transactionModel = new \App\Models\Transaction();
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        $transactions = $transactionModel->getFailedNonRefundedPayouts();
        foreach($transactions as $transaction) {
           $user = $userModel->find($transaction['user_id']);
           //refund amount
           $newBalance = number_format($user['wallet'] + $transaction['net_amount'], 2, '.', '');
           $userModel->where('id', $user['id'])->update([
             "wallet" => $newBalance
           ]);
           //add wallet Transaction
           
           $walletModel->create([
              "user_id" => $user['id'],
              "type" =>	"CREDIT",
              "txn_type" =>	"REFUND",
              "txnid" => $transaction['txnid'],	
              "opening" => $user['wallet'],
              "amount" => $transaction['amount'],
              "charges" => $transaction['charges'],
              "gst" => $transaction['gst'],
              "net_amount" => $transaction['net_amount'],
              "closing" => $newBalance
            ]);
    
           //update refunded
           $transactionModel->where('id', $transaction['id'])->update([
              "refunded" => 1
           ]);
        }
    }
    
    private function credit_success_fund_requests() {
       $fundRequestModel = new \App\Models\FundRequest();
       $userModel = new \App\Models\User();
       $walletModel = new \App\Models\WalletTransaction();
       $fundRequests = $fundRequestModel->getSuccessPendingRequests();
       foreach($fundRequests as $fundRequest) {
           $user = $userModel->find($fundRequest['user_id']);
           //add money to wallet
           $newBalance = number_format($user['wallet'] + $fundRequest['amount'], 2, '.', '');
    
           $userModel->where('id', $user['id'])->update([
             "wallet" => $newBalance
           ]);
           
           $walletModel->create([
              "user_id" => $user['id'],
              "type" =>	"CREDIT",
              "txn_type" =>	"TOPUP",
              "txnid" => $fundRequest['txnid'],	
              "opening" => $user['wallet'],
              "amount" => number_format($fundRequest['amount'], 2, '.', ''),
              "charges" => 0,
              "gst" => 0,
              "net_amount" => number_format($fundRequest['amount'], 2, '.', ''),
              "closing" => $newBalance
            ]);
            
           $fundRequestModel->where('id', $fundRequest['id'])->update(['credited' => 1]);
       }
    }
    
    private function credit_success_settlements() {
       
       $settlementModel = new \App\Models\Settlement();
       $userModel = new \App\Models\User();
       $walletModel = new \App\Models\WalletTransaction();
       $settlements = $settlementModel->getSuccessPendingCredit();
       foreach($settlements as $settlement) {
           $user = $userModel->find($settlement['user_id']);
           //add money to wallet
           $newBalance = number_format($user['wallet'] + $settlement['net_amount'], 2, '.', '');
           
           $userModel->where('id', $user['id'])->update([
             "wallet" => $newBalance
           ]);
           
           $walletModel->create([
              "user_id" => $user['id'],
              "type" =>	"CREDIT",
              "txn_type" =>	"SETTLEMENT",
              "txnid" => $settlement['txnid'],	
              "opening" => $user['wallet'],
              "amount" => $settlement['amount'],
              "charges" => $settlement['charges'],
              "gst" => $settlement['gst'],
              "net_amount" => $settlement['net_amount'],
              "closing" => $newBalance
            ]);
            
           $settlementModel->where('id', $settlement['id'])->update(['credited' => 1]);
       }
    }
    
    private function process_hold_transactions() {
        $holdWalletModel = new \App\Models\HoldWallet();
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        foreach($holdWalletModel->where("processed", 0)->get() as $txn) {
            $user = $userModel->find($txn['user_id']);
            if($txn['type'] == "hold"){
                //debit from user wallet
                $newBalance = number_format($user['wallet'] - $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                  "wallet" => $newBalance
                ]);
                //add debit entry in wallet statement
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
                //add to hold wallet
                $newBalance = number_format($user['hold_wallet'] + $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                  "hold_wallet" => $newBalance
                ]);
                //mark transaction processed
                $holdWalletModel->where('id', $txn['id'])->update([
                   "opening" => $user['hold_wallet'],
                   "processed" => 1,
                   "closing" => $newBalance
                ]);
            }elseif($txn['type'] == "released"){
                //add to user wallet
                $newBalance = number_format($user['wallet'] + $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                  "wallet" => $newBalance
                ]);
                //add credit entry in wallet statement
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
                //debit from hold wallet
                $newBalance = number_format($user['hold_wallet'] - $txn['amount'], 2, '.', '');
                $userModel->where('id', $txn['user_id'])->update([
                  "hold_wallet" => $newBalance
                ]);
                //mark transaction processed
                $holdWalletModel->where('id', $txn['id'])->update([
                   "opening" => $user['hold_wallet'],
                   "processed" => 1,
                   "closing" => $newBalance
                ]);
            }
        }
    }
    
    public function process_credits_transactions() {
        $creditsModel = new \App\Models\Credits();
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        //manage debit credits
        foreach($creditsModel->where('debited',0)->where('debit_granted',1)->get() as $txn){
           //debit transaction 
           $user = $userModel->find($txn['user_id']);
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
           //update credits transaction
           $creditsModel->where('id', $txn['id'])->update([
              "debited" => 1,
              "debited_at" => date("Y-m-d H:i:s")
           ]);
        }
        //manage credit credits
        foreach($creditsModel->where('credited',0)->get() as $txn){
           //credit transaction 
           $user = $userModel->find($txn['user_id']);
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
           //update credits transaction
           $creditsModel->where('id', $txn['id'])->update([
              "credited" => 1,
              "credited_at" => date("Y-m-d H:i:s")
           ]);
        }
    }
    
    private function process_pending_wallet_credits_debits() {
        $userModel = new \App\Models\User();
        $walletModel = new \App\Models\WalletTransaction();
        foreach($walletModel->where("processed",2)->get() as $transaction) {
           $user = $userModel->find($transaction['user_id']);
           if($transaction['type'] == "DEBIT") {
               $newBalance = number_format($user['wallet'] - $transaction['net_amount'], 2, '.', '');
               $userModel->where('id', $user['id'])->update([
                 "wallet" => $newBalance
               ]);
               $walletModel->where('id', $transaction['id'])->update([
                   "processed" => 1,
                   "opening" => number_format($user['wallet'], 2, '.', ''),
                   "closing" => $newBalance
               ]);
           }elseif($transaction['type'] == "CREDIT"){
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
           
           //add wallet Transaction
           
          
    
        }
    }
    
    public function update_payin_payout_users() {
       $transactionModel = new \App\Models\Transaction();
       $userModel = new \App\Models\User();
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
           $userModel->whereIn("id", array_values($payinUsersList))
                     ->set("todays_payin", 0)
                     ->where('id', );
       }
       foreach ($transactionModel->getTodaysPayoutUser() as $usage) {
           $userModel->update($usage['user_id'])->update(["todays_payout" => $usage['amount']]);
           unset($payoutUsersList[$usage['user_id']]);
       }
       if (!empty($payoutUsersList)) {
           $userModel->whereIn("id", array_values($payoutUsersList))
                     ->set("todays_payout", 0)
                     ->where('id', );
       }
   }
   
   public function update_payin_payout_providers() {
       $transactionModel = new \App\Models\Transaction();
       $providerModel = new \App\Models\Provider();
       $providersList = [];
       foreach ($providerModel->get() as $provider) {
           $providersList[$provider['id']] = $provider['id'];
       }
       $providerUsage = $transactionModel->getTodaysProviderUsage();
       foreach ($providerUsage as $usage) {
           if (!empty($usage['provider_id'])) { 
               $providerModel->update($usage['provider_id'])->update([
                   "used_limit" => $usage['total_amount']
               ]);
               unset($providersList[$usage['provider_id']]);
           }
       }
       if (!empty($providersList)) {
           $providerModel->whereIn("id", array_values($providersList))
                         ->set("used_limit", 0)
                         ->update();
       }
   }
    
   

}