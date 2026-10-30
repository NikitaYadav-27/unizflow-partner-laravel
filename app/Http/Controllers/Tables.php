<?php
namespace App\Http\Controllers;

class Tables extends Controller
{  
    private $session;
    function __construct() {
        $this->middleware(function ($request, $next) {
            if (!session()->get("user_id")) {
                return response()->json(["status" => "error", "message" => "Unauthorized access"], 401);
            }
            return $next($request);
        });
    }

    public function payin_transactions()
   {
      $availableSearchKeys = ["rrn","txnid","merchant_txnid","name","phone","status"];
      $request = request();
      $session = session();
      $offset = $request->input('start');
      $limit = $request->input('length');
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      $draw = (int) $request->input('draw');
      $transactionModel = new \App\Models\Transaction();
      $settlementId = $request->input('settlementId');
      if($settlementId != ""){
          $settlementId = openssl_decrypt(hex2bin($settlementId),'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16));
          $totalTransactions = $transactionModel->where('user_id', $session->get('user_id'))->where("settlement_id",$settlementId)->where('service', 'payin')->count();
      }else{
          $totalTransactions = $transactionModel->where('user_id', $session->get('user_id'))->where('service', 'payin')->count();
      }
      
      $builder = $transactionModel::query();
      $builder->where('user_id', $session->get('user_id'));
      $builder->where('service', 'payin');
      
      if($settlementId != "") {
          $builder->where('settlement_id', $settlementId);
      }
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('transactions.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      $filteredBuilder = clone $builder;
      $filteredTransactions = $filteredBuilder->count();
      $builder->orderBy('transactions.id', "DESC")->offset($offset)->limit($limit);
      $transactions = $builder->get();
      $data = [];
      $index = 1;

      foreach ($transactions as $transaction) {
          $data[] = [
              $index,
              $transaction->name,
              $transaction->phone,
              $transaction->txnid,
              $transaction->merchant_txnid,
              $transaction->rrn,
              "₹ ".$transaction->amount,
              "₹ ".$transaction->charges,
              "₹ ".$transaction->gst,
              "₹ ".$transaction->net_amount,
              $this->getStatusSpan($transaction->status),
              $transaction->payee_name,
              $transaction->payee_vpa,
              $transaction->created_at,
              $transaction->txn_complete_time,
              $transaction->remarks
          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalTransactions,
          'recordsFiltered' => $filteredTransactions,
          'data' => $data
      ]);
    }
    
    public function payout_transactions(){
      
      $availableSearchKeys = ["rrn","txnid","merchant_txnid","name","amount","account_number","status"];
      $request = request();
      $session = session();
      $offset = $request->input('start');
      $limit = $request->input('length');
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      $draw = (int) $request->input('draw');
      $transactionModel = new \App\Models\Transaction();
      $totalTransactions = $transactionModel->where('user_id', $session->get('user_id'))->where('service', 'payin')->count();
      $builder = $transactionModel::query();
      $builder->where('user_id', $session->get('user_id'));
      $builder->where('service', 'payout');
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('transactions.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      $filteredBuilder = clone $builder;
      $filteredTransactions = $filteredBuilder->count();
      $builder->orderBy('transactions.id', "DESC")->offset($offset)->limit($limit);
      $transactions = $builder->get();
      $data = [];
      $index = 1;

      foreach ($transactions as $transaction) {
          $data[] = [
              $index,
              $transaction->name,
              $transaction->phone,
              $transaction->txnid,
              $transaction->merchant_txnid,
              $transaction->rrn,
              "₹ ".$transaction->amount,
              "₹ ".$transaction->charges,
              "₹ ".$transaction->gst,
              "₹ ".$transaction->net_amount,
              $this->getStatusSpan($transaction->status),
              $transaction->account_number,
              $transaction->ifsc_code,
              $transaction->bank_name,
              $transaction->created_at,
              $transaction->txn_complete_time,
              $transaction->remarks,
              str_contains(strtolower($transaction->reason), 'insufficient') ? "" : $transaction->reason,
              $transaction->status == "Success"?'<button class="btn btn-success-transparent btn-sm" onclick="downloadReceipt(this,\''.$transaction->txnid.'\')"><i class="ti ti-receipt"></i></button>':""
          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalTransactions,
          'recordsFiltered' => $filteredTransactions,
          'data' => $data
      ]);
    }

private function getStatusSpan($status) {
       if($status == "Success") { return '<span class="badge bg-success-transparent">Success</span>'; } 
       if($status == "Pending") { return '<span class="badge bg-warning-transparent">Pending</span>'; } 
       if($status == "Failed") { return '<span class="badge bg-danger-transparent">Failed</span>'; } 
       if($status == "Hold") { return '<span class="badge bg-danger-transparent">Hold</span>'; } 
       if($status == "Expired") { return '<span class="badge bg-danger-transparent">Expired</span>'; } 
       if($status == "Processing") { return '<span class="badge bg-info-transparent">Processing</span>'; } 
       if($status == "Uploaded") { return '<span class="badge bg-purple-transparent">Uploaded</span>'; } 
    }

    public function settlements()
    {
      $availableSearchKeys = ["txnid","batch_id"];
      $request = request();
      $session = session();
      $offset = (int) $request->input('start', 0);
      $limit = (int) $request->input('length', 10);
      if ($limit <= 0) {
          $limit = 10;
      }
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      
      $startDate = $request->input('startDate')." 00:00:00";
      $endDate = $request->input('endDate')." 23:59:59";
      
      $draw = (int) $request->input('draw');
      $settlementModel = new \App\Models\Settlement();
      $totalSettlement = $settlementModel->where('user_id', $session->get('user_id'))->whereBetween("created_at", [$startDate, $endDate])->count();
      $builder = $settlementModel::query();
      $builder->where('user_id', $session->get('user_id'));
      $builder->whereBetween("created_at", [$startDate, $endDate]);
      
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('settlements.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      
      $filteredBuilder = clone $builder;
      $filteredSettlements = $filteredBuilder->count();
      $builder->orderBy('settlements.id', "DESC")->offset($offset)->limit($limit);
      $settlements = $builder->get();
      $data = [];
      $index = 1;
      
      foreach ($settlements as $settlement) {
          $totalTransactions = (new \App\Models\Transaction())->where("settlement_id", $settlement->id)->count();
          $data[] = [
              $index,
              $settlement->batch_id ?? 'NA',
              "₹ " . number_format((float) ($settlement->amount ?? 0), 2),
              "₹ " . number_format((float) ($settlement->charges ?? 0), 2),
              "₹ " . number_format((float) ($settlement->gst ?? 0), 2),
              "₹ " . number_format((float) ($settlement->net_amount ?? 0), 2),
              ($settlement->status == "Success")
                  ? ($settlement->settle_to == "wallet"
                      ? '<span class="badge bg-primary-transparent"><i class="ti ti-wallet"></i>&nbsp;&nbsp; Wallet</span>'
                      : '<span class="badge bg-success-transparent"><i class="ti ti-building-bank"></i>&nbsp;&nbsp; Bank</span>')
                  : '<span class="badge bg-warning-transparent">Pending</span>',
              $settlement->txnid ?? 'NA',
              $this->getStatusSpan($settlement->status ?? 'Pending'),
              $settlement->created_at ? date('Y-m-d H:i:s', strtotime($settlement->created_at)) : 'NA',
              ($settlement->status == "Success" && $settlement->updated_at) ? date('Y-m-d H:i:s', strtotime($settlement->updated_at)) : 'NA',
              '<button onclick="navigate(\''.route('service.collection.settlement.transactions', ['any' => $this->encrypt($settlement->id)]).'\')" class="btn btn-sm btn-primary-transparent"><i class="ti ti-list"></i>&nbsp;&nbsp;'.$totalTransactions.'</button>'
          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalSettlement,
          'recordsFiltered' => $filteredSettlements,
          'data' => $data
      ]);
    }
    
    public function fund_requests(){
      $availableSearchKeys = [];
      $request = request();
      $session = session();
      $offset = $request->input('start');
      $limit = $request->input('length');
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      $draw = (int) $request->input('draw');
      $fundrequestModel = new \App\Models\FundRequest();
      $totalFundRequest = $fundrequestModel->where('user_id', $session->get('user_id'))->count();
      $builder = $fundrequestModel::query();
      $builder->selectRaw("fund_requests.*, fund_request_banks.name as bank_name");
      $builder->where('user_id', $session->get('user_id'));
      $builder->join("fund_request_banks", "fund_request_banks.id", "=", "fund_requests.bank");
      
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('fund_requests.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      $filteredBuilder = clone $builder;
      $filteredFundRequests = $filteredBuilder->count();
      $builder->orderBy('fund_requests.id', "DESC")->offset($offset)->limit($limit);
      $fundrequests = $builder->get();
      $data = [];
      $index = 1;

      foreach ($fundrequests as $fundrequest) {
          $remarksText = !empty($fundrequest->remarks) ? $fundrequest->remarks : (!empty($fundrequest->reason) ? $fundrequest->reason : "-");
          $data[] = [
              $fundrequest->txnid,
              $fundrequest->bank_name,
              $fundrequest->rrn,
              $fundrequest->mode,
              "₹ ".$fundrequest->amount,
              $this->getStatusSpan($fundrequest->status),
              $remarksText,
              $fundrequest->created_at,
          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalFundRequest,
          'recordsFiltered' => $filteredFundRequests,
          'data' => $data
      ]);
      
    }

public function wallet_transactions() {
      $availableSearchKeys = ["type","txn_type","txnid","amount","opening","closing","remarks"];
      $request = request();
      $session = session();
      $offset = $request->input('start');
      $limit = $request->input('length');
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      $draw = (int) $request->input('draw');
      $wallettransactionModel = new \App\Models\WalletTransaction();
      $totalWalletTransaction = $wallettransactionModel->where('user_id', $session->get('user_id'))->count();
      $builder = $wallettransactionModel::query();
      $builder->where('user_id', $session->get('user_id'));
      
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('wallet_transactions.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      $filteredBuilder = clone $builder;
      $filteredWalletTransactions = $filteredBuilder->count();
      $builder->orderBy('wallet_transactions.id', "DESC")->offset($offset)->limit($limit);
      $wallettransactions = $builder->get();
      $data = [];
      $index = 1;

      foreach ($wallettransactions as $wallettransaction) {
          $data[] = [
              $index,
              $wallettransaction->type,
              $wallettransaction->txn_type,
              $wallettransaction->txnid,
              "₹ ".$wallettransaction->net_amount,
              "₹ ".$wallettransaction->opening,
              "₹ ".$wallettransaction->closing,
              $wallettransaction->remarks,
              $wallettransaction->created_at,

          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalWalletTransaction,
          'recordsFiltered' => $filteredWalletTransactions,
          'data' => $data
      ]);        
    }

    public function hold_wallet_transactions() {
      $availableSearchKeys = [];
      $request = request();
      $session = session();
      $offset = $request->input('start');
      $limit = $request->input('length');
      $searchBy = $request->input('searchBy');
      $searchValue = $request->input('searchValue');
      $draw = (int) $request->input('draw');
      $wallettransactionModel = new \App\Models\HoldWallet();
      $totalWalletTransaction = $wallettransactionModel->where('user_id', $session->get('user_id'))->count();
      $builder = $wallettransactionModel::query();
      $builder->where('user_id', $session->get('user_id'));
      
      if (!empty($searchBy) && !empty($searchValue)) {
          if(in_array($searchBy,$availableSearchKeys))
          {
             $builder->where('hold_wallet_transactions.' . $searchBy, "LIKE", "%" . trim($searchValue, "\"'") . "%");
          }
      }
      $filteredBuilder = clone $builder;
      $filteredWalletTransactions = $filteredBuilder->count();
      $builder->orderBy('hold_wallet_transactions.id', "DESC")->offset($offset)->limit($limit);
      $wallettransactions = $builder->get();
      $data = [];
      $index = 1;

      foreach ($wallettransactions as $wallettransaction) {
          $data[] = [
              $index,
              strtoupper($wallettransaction->type),
              $wallettransaction->reason,
              $wallettransaction->txnid,
              "₹ ".$wallettransaction->amount,
              "₹ ".$wallettransaction->opening,
              "₹ ".$wallettransaction->closing,
              $wallettransaction->created_at,

          ];
          $index++;
      }

      return response()->json([
          'draw' => $draw,
          'recordsTotal' => $totalWalletTransaction,
          'recordsFiltered' => $filteredWalletTransactions,
          'data' => $data
      ]);        
    }
    
    private function encrypt($text){
        return bin2hex(openssl_encrypt($text, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)));
    }

}
