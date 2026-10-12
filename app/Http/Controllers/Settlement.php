<?php
namespace App\Http\Controllers;

class Settlement extends Controller
{
    public function t00_02() {
       echo "<pre>"; 
      $ipAddress = request()->ip();
      if($ipAddress != env("server.ip")) {
          return response()->json([
              "status" => "error",
              "message" => "Unauthorised"
          ], 401);
      }
       //check for settlement is runned for today for 00:00:00
       $yesterday = date('Y-m-d', strtotime('-1 day'));
       $settlementRuntimeModel = new \App\Models\SettlementRuntime();
       $runned = $settlementRuntimeModel->where("time",$yesterday." 00:00:00")->count() > 0;
      if($runned) {
          return response()->json([
              "status" => "error",
              "message" => "Already Runned Today"
          ], 429);
      }
       $settlementRuntimeModel->create(["time" => $yesterday." 00:00:00"]);
       $transactionModel = new \App\Models\Transaction();
       $userModel = new \App\Models\User();
       $settlementModel = new \App\Models\Settlement();
       $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
       foreach($user_ids as $user_id){
           $user = $userModel->where("id",$user_id['user_id'])->first();
           if($user['settlement_package'] == 1) {
               $startDate = $yesterday." 00:00:00";
               $endDate = $yesterday." 23:59:59";
               $this->process_settlement($startDate,$endDate,$user_id);
           }elseif($user['settlement_package'] == 2) {
               $startDate = $yesterday." 19:00:00";
               $endDate = $yesterday." 23:59:59";
               $this->process_settlement($startDate,$endDate,$user_id);
           }
       }
       
    }
    
    public function t07_02() {
       echo "<pre>"; 
       $ipAddress = request()->ip();
       if($ipAddress != env("server.ip")) {
           return response()->json([
              "status" => "error",
              "message" => "Unauthorised"
           ], 401);
       }
       //check for settlement is runned for today for 07:02:00
       $today = date('Y-m-d');
       $settlementRuntimeModel = new \App\Models\SettlementRuntime();
       $runned = $settlementRuntimeModel->where("time",$today." 07:02:00")->count() > 0;
       if($runned) {
           return response()->json([
              "status" => "error",
              "message" => "Already Runned Today"
           ], 429);
       }
       $settlementRuntimeModel->create(["time" => $today." 07:02:00"]);
       $transactionModel = new \App\Models\Transaction();
       $userModel = new \App\Models\User();
       $settlementModel = new \App\Models\Settlement();
       $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
       foreach($user_ids as $user_id) {
           $user = $userModel->where("id",$user_id['user_id'])->first();
           if($user['settlement_package'] == 2) {
              $startDate = $today." 00:00:00";
              $endDate = $today." 06:59:59";
              $this->process_settlement($startDate,$endDate,$user_id);
           }
       }
       
    }
    
    public function t13_02() {
       echo "<pre>"; 
       $ipAddress = request()->ip();
       if($ipAddress != env("server.ip")) {
           return response()->json([
              "status" => "error",
              "message" => "Unauthorised"
           ], 401);
       }
       //check for settlement is runned for today for 13:02:00
       $today = date('Y-m-d');
       $settlementRuntimeModel = new \App\Models\SettlementRuntime();
       $runned = $settlementRuntimeModel->where("time",$today." 13:02:00")->count() > 0;
       if($runned) {
           return response()->json([
              "status" => "error",
              "message" => "Already Runned Today"
           ], 429);
       }
       $settlementRuntimeModel->create(["time" => $today." 13:02:00"]);
       $transactionModel = new \App\Models\Transaction();
       $userModel = new \App\Models\User();
       $settlementModel = new \App\Models\Settlement();
       $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
       foreach($user_ids as $user_id) {
           $user = $userModel->where("id",$user_id['user_id'])->first();
           if($user['settlement_package'] == 2) {
              $startDate = $today." 07:00:00";
              $endDate = $today." 12:59:59";
              $this->process_settlement($startDate,$endDate,$user_id);
           }
       }
       
    }
    
    public function t19_02() {
       echo "<pre>"; 
       $ipAddress = request()->ip();
       if($ipAddress != env("server.ip")) {
           return response()->json([
              "status" => "error",
              "message" => "Unauthorised"
           ], 401);
       }
       //check for settlement is runned for today for 19:02:00
       $today = date('Y-m-d');
       $settlementRuntimeModel = new \App\Models\SettlementRuntime();
       $runned = $settlementRuntimeModel->where("time",$today." 19:02:00")->count() > 0;
       if($runned) {
           return response()->json([
              "status" => "error",
              "message" => "Already Runned Today"
           ], 429);
       }
       $settlementRuntimeModel->create(["time" => $today." 19:02:00"]);
       $transactionModel = new \App\Models\Transaction();
       $userModel = new \App\Models\User();
       $settlementModel = new \App\Models\Settlement();
       $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
       foreach($user_ids as $user_id) {
           $user = $userModel->where("id",$user_id['user_id'])->first();
           if($user['settlement_package'] == 2) {
              $startDate = $today." 13:00:00";
              $endDate = $today." 18:59:59";
              $this->process_settlement($startDate,$endDate,$user_id);
           }
       }
       
    }
    
    private function process_settlement($startDate,$endDate,$user_id) {
       $transactionModel = new \App\Models\Transaction();
       $settlementModel = new \App\Models\Settlement();
       $result = $transactionModel
               ->selectRaw('sum(amount) as total_amount, sum(charges) as total_charges, sum(gst) as total_gst')
               ->where('service', 'payin')
               ->where('status', 'Success')
               ->where('settled', 0)
               ->where('txn_complete_time', '>=', $startDate)
               ->where('txn_complete_time', '<=', $endDate)
               ->where('user_id', $user_id)
               ->first();
       $totalAmount = number_format($result['total_amount'], 2, '.', '');
       if($totalAmount > 0) {
           
          $totalCharges = number_format($result['total_charges'], 2, '.', '');
          $totalGST = number_format($result['total_gst'], 2, '.', '');
          $netAmount = $result['total_amount'] - $result['total_charges'] - $result['total_gst'];
          $netAmount = number_format($netAmount, 2, '.', '');
         
       
          //create settlement batch
          $settlementModel->create([
             "user_id" => $user_id,
             "batch_id" => "BTC".strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20)),
             "amount" => $totalAmount,
             "charges" => $totalCharges,
             "gst" => $totalGST,
             "net_amount" => $netAmount,
             "settle_to" => "",
             "txnid" => "SETL".strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20)),
             "status" => "Pending",
             "from_time" => $startDate,
             "to_time" => $endDate
          ]);  
          
          $settlementId = $settlementModel->getConnection()->getPdo()->lastInsertId();
          //update transaction to settled
          $transactionModel
                  ->where('service', 'payin')
                  ->where('status', 'Success')
                  ->where('settled', 0)
                  ->where('txn_complete_time', '>=', $startDate)
                  ->where('txn_complete_time', '<=', $endDate)
                  ->where('user_id', $user_id)
                  ->set(['settled' => 1, 'settlement_id' => $settlementId])
                  ->update();
          
       } 
    }
    
}