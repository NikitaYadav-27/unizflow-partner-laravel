<?php
namespace App\Http\Controllers;

use App\Models\SettlementRuntime;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Settlement as SettlementModel;
use Illuminate\Support\Facades\DB;

class Settlement extends Controller
{
    public function t00_02() {
        $ipAddress = request()->ip();
        if(env("server.ip") && $ipAddress != env("server.ip")) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorised"
            ], 401);
        }
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $settlementRuntimeModel = new SettlementRuntime();
        $runned = $settlementRuntimeModel->where("time", $yesterday . " 00:00:00")->count() > 0;
        if($runned) {
            return response()->json([
                "status" => "error",
                "message" => "Already Runned Today"
            ], 429);
        }
        $settlementRuntimeModel->create(["time" => $yesterday . " 00:00:00"]);
        
        $transactionModel = new Transaction();
        $userModel = new User();
        $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
        foreach($user_ids as $user_id){
            $user = $userModel->where("id", $user_id['user_id'])->first();
            if($user && $user['settlement_package'] == 1) {
                $startDate = $yesterday . " 00:00:00";
                $endDate = $yesterday . " 23:59:59";
                $this->process_settlement($startDate, $endDate, $user_id);
            } elseif($user && $user['settlement_package'] == 2) {
                $startDate = $yesterday . " 19:00:00";
                $endDate = $yesterday . " 23:59:59";
                $this->process_settlement($startDate, $endDate, $user_id);
            }
        }

        return response()->json([
            "status" => "success",
            "message" => "T00_02 settlement executed successfully"
        ]);
    }
    
    public function t07_02() {
        $ipAddress = request()->ip();
        if(env("server.ip") && $ipAddress != env("server.ip")) {
            return response()->json([
               "status" => "error",
               "message" => "Unauthorised"
            ], 401);
        }
        $today = date('Y-m-d');
        $settlementRuntimeModel = new SettlementRuntime();
        $runned = $settlementRuntimeModel->where("time", $today . " 07:02:00")->count() > 0;
        if($runned) {
            return response()->json([
               "status" => "error",
               "message" => "Already Runned Today"
            ], 429);
        }
        $settlementRuntimeModel->create(["time" => $today . " 07:02:00"]);
        
        $transactionModel = new Transaction();
        $userModel = new User();
        $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
        foreach($user_ids as $user_id) {
            $user = $userModel->where("id", $user_id['user_id'])->first();
            if($user && $user['settlement_package'] == 2) {
               $startDate = $today . " 00:00:00";
               $endDate = $today . " 06:59:59";
               $this->process_settlement($startDate, $endDate, $user_id);
            }
        }

        return response()->json([
            "status" => "success",
            "message" => "T07_02 settlement executed successfully"
        ]);
    }
    
    public function t13_02() {
        $ipAddress = request()->ip();
        if(env("server.ip") && $ipAddress != env("server.ip")) {
            return response()->json([
               "status" => "error",
               "message" => "Unauthorised"
            ], 401);
        }
        $today = date('Y-m-d');
        $settlementRuntimeModel = new SettlementRuntime();
        $runned = $settlementRuntimeModel->where("time", $today . " 13:02:00")->count() > 0;
        if($runned) {
            return response()->json([
               "status" => "error",
               "message" => "Already Runned Today"
            ], 429);
        }
        $settlementRuntimeModel->create(["time" => $today . " 13:02:00"]);
        
        $transactionModel = new Transaction();
        $userModel = new User();
        $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
        foreach($user_ids as $user_id) {
            $user = $userModel->where("id", $user_id['user_id'])->first();
            if($user && $user['settlement_package'] == 2) {
               $startDate = $today . " 07:00:00";
               $endDate = $today . " 12:59:59";
               $this->process_settlement($startDate, $endDate, $user_id);
            }
        }

        return response()->json([
            "status" => "success",
            "message" => "T13_02 settlement executed successfully"
        ]);
    }
    
    public function t19_02() {
        $ipAddress = request()->ip();
        if(env("server.ip") && $ipAddress != env("server.ip")) {
            return response()->json([
               "status" => "error",
               "message" => "Unauthorised"
            ], 401);
        }
        $today = date('Y-m-d');
        $settlementRuntimeModel = new SettlementRuntime();
        $runned = $settlementRuntimeModel->where("time", $today . " 19:02:00")->count() > 0;
        if($runned) {
            return response()->json([
               "status" => "error",
               "message" => "Already Runned Today"
            ], 429);
        }
        $settlementRuntimeModel->create(["time" => $today . " 19:02:00"]);
        
        $transactionModel = new Transaction();
        $userModel = new User();
        $user_ids = $transactionModel->select('user_id')->where('service', 'payin')->where('status', 'Success')->where('settled', 0)->groupBy('user_id')->get();
        foreach($user_ids as $user_id) {
            $user = $userModel->where("id", $user_id['user_id'])->first();
            if($user && $user['settlement_package'] == 2) {
               $startDate = $today . " 13:00:00";
               $endDate = $today . " 18:59:59";
               $this->process_settlement($startDate, $endDate, $user_id);
            }
        }

        return response()->json([
            "status" => "success",
            "message" => "T19_02 settlement executed successfully"
        ]);
    }
    
    private function process_settlement($startDate, $endDate, $user_id) {
        $uid = is_array($user_id) ? ($user_id['user_id'] ?? 0) : (is_object($user_id) ? ($user_id->user_id ?? 0) : $user_id);
        $transactionModel = new Transaction();
        $settlementModel = new SettlementModel();
        
        $result = $transactionModel
                ->selectRaw('sum(amount) as total_amount, sum(charges) as total_charges, sum(gst) as total_gst')
                ->where('service', 'payin')
                ->where('status', 'Success')
                ->where('settled', 0)
                ->where('txn_complete_time', '>=', $startDate)
                ->where('txn_complete_time', '<=', $endDate)
                ->where('user_id', $uid)
                ->first();
                
        $totalAmount = number_format($result['total_amount'] ?? 0, 2, '.', '');
        if($totalAmount > 0) {
            $totalCharges = number_format($result['total_charges'] ?? 0, 2, '.', '');
            $totalGST = number_format($result['total_gst'] ?? 0, 2, '.', '');
            $netAmount = ($result['total_amount'] ?? 0) - ($result['total_charges'] ?? 0) - ($result['total_gst'] ?? 0);
            $netAmount = number_format($netAmount, 2, '.', '');
          
            DB::transaction(function() use ($uid, $totalAmount, $totalCharges, $totalGST, $netAmount, $startDate, $endDate, $settlementModel, $transactionModel) {
                $settlement = $settlementModel->create([
                    "user_id" => $uid,
                    "batch_id" => "BTC" . strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20)),
                    "amount" => $totalAmount,
                    "charges" => $totalCharges,
                    "gst" => $totalGST,
                    "net_amount" => $netAmount,
                    "settle_to" => "",
                    "txnid" => "SETL" . strtoupper(substr(dechex(time()) . bin2hex(random_bytes(3)), 0, 20)),
                    "status" => "Pending",
                    "from_time" => $startDate,
                    "to_time" => $endDate
                ]);  
               
                $settlementId = $settlement->id;
                $transactionModel
                       ->where('service', 'payin')
                       ->where('status', 'Success')
                       ->where('settled', 0)
                       ->where('txn_complete_time', '>=', $startDate)
                       ->where('txn_complete_time', '<=', $endDate)
                       ->where('user_id', $uid)
                       ->update(['settled' => 1, 'settlement_id' => $settlementId]);
            });
        } 
    }
}