<?php
namespace App\Http\Controllers;
use App\Libraries\Validators;
use App\Libraries\Recaptcha;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class Home extends Controller
{

    public function index()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            if($user['active'] == 0){
                $session->flush();
                return redirect()->route('login');
            }
            return redirect()->route('dashboard');
        }
    }

    public function dashboard()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $transactionModel = new \App\Models\Transaction();
            $unsettledAmount = $transactionModel
                ->where('user_id', $user['id'])
                ->where('service', 'payin')
                ->where('status', 'Success')
                ->where('settled', 0)
                ->sum('net_amount');
            $data['unsettled_amount'] = number_format($unsettledAmount, 2, '.', '');
            return view('dashboard', $data);
        }
    }

    public function upi_collection()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('upi_collection', $data);
        }
    }

public function download() {
    $session = session();
    if (!$session->get('user_id')) {
        return response()->json([
            "status" => "error",
            "message" => "Unauthorized"
        ], 401);
    }

    $startDate = request()->query("startDate");
    $endDate = request()->query("endDate");
    $reportType = request()->query("reportType");
    $status = request()->query("status");
    $txnType = request()->query("txnType");

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid Start Date"
        ], 400);
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid End Date"
        ], 400);
    }

    if (!in_array($reportType, ["transactions", "settlements", "wallet", "wallet_ledger"])) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid Report Type"
        ], 400);
    }

    if (!in_array($status, ["", "Success", "Pending", "Failed", "Expired"])) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid Status"
        ], 400);
    }

    if (!in_array($txnType, ["", "payin", "payout"])) {
        return response()->json([
            "status" => "error",
            "message" => "Invalid Transaction Type"
        ], 400);
    }

    if ($reportType === 'wallet' || $reportType === 'wallet_ledger') {
        $walletBuilder = \App\Models\WalletTransaction::where("user_id", $session->get('user_id'))
            ->whereBetween('created_at', ["$startDate 00:00:00", "$endDate 23:59:59"]);

        $walletHeaders = [
            'ID', 'Type', 'Txn Type', 'Txn ID', 'Amount', 'Opening', 'Closing', 'Remarks', 'Created At'
        ];
        $walletFilename = 'wallet_ledger_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function() use ($walletBuilder, $walletHeaders) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $walletHeaders);
            $rowCounter = 1;
            foreach ($walletBuilder->orderBy('id', 'DESC')->cursor() as $wt) {
                fputcsv($output, [
                    $rowCounter++,
                    strtoupper($wt->type ?? ''),
                    $wt->txn_type ?? '',
                    $wt->txnid ?? '',
                    $wt->net_amount ?? $wt->amount ?? '0',
                    $wt->opening ?? '0',
                    $wt->closing ?? '0',
                    $wt->remarks ?? '',
                    $wt->created_at ?? ''
                ]);
            }
            fclose($output);
        }, $walletFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$walletFilename\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    $transactionModel = new \App\Models\Transaction();
    $builder = $transactionModel->newQuery();
    $builder->where("user_id", $session->get('user_id'));
    $builder->whereRaw("created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'");
    
    if ($status != "") {
        $builder->where("status", $status);
    }
    
    if ($txnType != "") {
        $builder->where("service", $txnType);
    }

    $filename = 'report_' . date('YmdHis') . '.csv';

    $headers = [
        'ID', 'Service', 'Name', 'Phone', 'Email', 'Transaction ID', 'UTR', 
        'Reference ID', 'Status', 'Amount', 'Charges', 'GST', 'Payee Name', 
        'Payee VPA', 'Account Number', 'IFSC Code', 'Remarks', 
        'Transaction Complete Time', 'Transaction Time'
    ];

    if ($reportType === 'settlements') {
        $settlementBuilder = \App\Models\Settlement::where("user_id", $session->get('user_id'))
            ->whereBetween('created_at', ["$startDate 00:00:00", "$endDate 23:59:59"]);
        if ($status != "") {
            $settlementBuilder->where("status", $status);
        }

        $settlementHeaders = [
            'ID', 'Txn ID', 'Amount', 'Charges', 'GST', 'Net Amount', 'Status', 'Settle To', 'UTR', 'Remarks', 'Created At'
        ];
        $settlementFilename = 'settlements_report_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function() use ($settlementBuilder, $settlementHeaders) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $settlementHeaders);
            $rowCounter = 1;
            foreach ($settlementBuilder->orderBy('id', 'DESC')->cursor() as $settlement) {
                fputcsv($output, [
                    $rowCounter++,
                    $settlement->txnid ?? '',
                    $settlement->amount ?? '',
                    $settlement->charges ?? '',
                    $settlement->gst ?? '',
                    $settlement->net_amount ?? '',
                    $settlement->status ?? '',
                    $settlement->settle_to ?? '',
                    $settlement->rrn ?? $settlement->utr ?? '',
                    $settlement->remarks ?? '',
                    $settlement->created_at ?? ''
                ]);
            }
            fclose($output);
        }, $settlementFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$settlementFilename\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    return response()->streamDownload(function() use ($builder, $headers) {
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        $rowCounter = 1;
        foreach ($builder->orderBy('id', 'DESC')->cursor() as $transaction) {
            fputcsv($output, [
                $rowCounter++,
                strtoupper($transaction->service ?? ''),
                $transaction->name ?? '',
                $transaction->phone ?? '',
                $transaction->email ?? '',
                $transaction->txnid ?? '',
                $transaction->rrn ?? '',
                $transaction->reference_id ?? '',
                $transaction->status ?? '',
                $transaction->amount ?? '',
                $transaction->charges ?? '',
                $transaction->gst ?? '',
                $transaction->payee_name ?? '',
                $transaction->payee_vpa ?? '',
                $transaction->account_number ?? '',
                $transaction->ifsc_code ?? '',
                $transaction->remarks ?? '',
                $transaction->txn_complete_time ?? '',
                $transaction->created_at ?? ''
            ]);
        }
        fclose($output);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Cache-Control' => 'no-store, no-cache',
    ]);
}
    
    public function reports_download() {
         $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('reports_download', $data);
        }
    }

public function payin_transactions()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('payin_transactions', $data);
        }
    }

    public function settlements() {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('settlements', $data);
        }
    }
    
    public function hold_wallet_ledger(){
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('hold_wallet_ledger', $data);
        }
    }
    
    public function transaction_status(){
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized"
            ], 401);
        }
        
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->findTxnByTxnid(request()->post("txnid"));
        if(isset($transaction['status'])){
            return response()->json([
                "status" => $transaction['status']
            ], 200);
        }
        
    }

    public function payout_create()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('payout_create', $data);
        }
    }
    
    public function profile(){
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $keyModel = new \App\Models\Key();
            $key = $keyModel->findUser($user['id']);
            if(!isset($key['id'])){
                $aesKey = bin2hex(random_bytes(32));
                $iv = base64_encode(random_bytes(12));
                
                $keyModel->create([
                  "user_id"	=> $user['id'],
                  "aes_key" => $aesKey,
                  "aes_iv" => $iv	
                ]);
                $key = $keyModel->findUser($user['id']);
            }
            $data['key'] = $key;
            return view('profile', $data);
        }
    }

    public function payout_transactions()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('payout_transactions', $data);
        }
    }

    public function wallet_ledger()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('wallet_ledger', $data);
        }
    }
    
    public function add_fund_request(){
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $data['banks'] = \Illuminate\Support\Facades\DB::table('fund_request_banks')->where('users', 'LIKE', '%\"'.$session->get('user_id').'\"%')->get();
            return view('add_fund_request', $data);
        }
    }

    public function wallet_topup()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $data['banks'] = \Illuminate\Support\Facades\DB::table('fund_request_banks')->where('users', 'LIKE', '%\"'.$session->get('user_id').'\"%')->get();
            return view('wallet_topup', $data);
        }
    }
    
    public function add_topup_request() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $bankId = request()->input('bank');
        $mode = request()->input('mode');
        $amount = request()->input('amount');
        $remarks = request()->input('remarks');
        $receipt = request()->input('receipt');
        $utr = request()->input('utr');
        
        if (!Validators::amount($amount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid amount'
            ], 400);
        }
        
        if (!Validators::remarks($remarks)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Remark Should be less than 100 characters'
            ], 400);
        }
        
        if (!Validators::remarks($remarks)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Remark Should be less than 100 characters'
            ], 400);
        }
        
       if (strlen($utr) < 5 || strlen($utr) > 50 || !preg_match('/^[a-zA-Z0-9]+$/', $utr)) {
           return response()->json([
               'status' => 'error',
               'message' => 'Invalid UTR Number'
           ], 400);
        }
        
       $builder = \Illuminate\Support\Facades\DB::table('fund_request_banks');
       $builder->where('id', $bankId);
       $count = $builder->count();
       
       if($count == 0){
           return response()->json([
                'status' => 'error',
                'message' => 'Invalid Bank ID'
            ], 400);
       }
       
       if (!preg_match('/^data:(.*);base64,(.*)$/', $receipt, $matches)) {
             return response()->json([
                'status' => 'error',
                'message' => 'Invalid File Type'
             ], 400);
        }

        $mimeType = $matches[1];
        $base64Data = $matches[2];
        $ext = '';

        switch ($mimeType) {
            case 'image/png':
                $ext = 'png';
                break;
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'application/pdf':
                $ext = 'pdf';
                break;
            default:
                 return response()->json([
                   'status' => 'error',
                   'message' => 'Invalid File'
                 ], 400);
        }

        $fileName = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $filePath = './uploads/' . $fileName;

        if (!file_put_contents($filePath, base64_decode($base64Data))) {
             return response()->json([
                'status' => 'error',
                'message' => 'Unable to Save file.'
             ], 400);
        }

        $fundRequestModel = new \App\Models\FundRequest();
        
        if($fundRequestModel->where('rrn', $utr)->where('status', '!=', 'Failed')->first()) {
            return response()->json([
                'status' => 'error',
                'message' => 'UTR is already Exists.'
            ], 400);
        }
        
        $fundRequestModel->create([
           "user_id" => $session->get('user_id'),
           "bank" => $bankId,
           "mode" => $mode,
           "rrn" => $utr,
           "amount" => $amount,
           "remarks" => $remarks,
           "receipt" => $fileName,
           "txnid" => $this->generateTxnId(),
           "status" => "Pending",
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Request Submitted Successfully !'
        ], 200);

}

public function change_password(){
         $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('change_password', $data);
        }
    }

public function settlement_transactions($encId){
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $settlementId = openssl_decrypt(hex2bin($encId),'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16));
            
            if(!($settlementId > 0)) {
                return redirect()->route('service.collection.settlements');
            }
            $settlementModel = new \App\Models\Settlement();
            $settlement = $settlementModel->find($settlementId);
            if(!isset($settlement['id'])){
                return redirect()->route('service.collection.settlements');
            }
            if($settlement['user_id'] != $session->get('user_id')) {
                 return redirect()->route('service.collection.settlements');
            }
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $data['settlement'] = $settlement;
            return view('settlement_transactions', $data);
        }
    }

    public function api_keys()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            $keyModel = new \App\Models\Key();
            $key = $keyModel->findUser($user['id']);
            if(!isset($key['id'])){
                $aesKey = bin2hex(random_bytes(32));
                $iv = base64_encode(random_bytes(12));
                
                $keyModel->create([
                  "user_id"	=> $user['id'],
                  "aes_key" => $aesKey,
                  "aes_iv" => $iv	
                ]);
                $key = $keyModel->findUser($user['id']);
            }
            $data['key'] = $key;
            return view('api_keys', $data);
        }
    }

public function webhook()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('webhook', $data);
        }
    }

    public function whitelisted_ip()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('whitelisted_ip', $data);
        }
    }
    
    public function transaction_info() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $txnid = request()->post("txnid");
        if(strlen($txnid) < 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
            ], 400);
        }
        $transactionModel = new \App\Models\Transaction();
        $transaction = $transactionModel->where("txnid", $txnid)->first();
        if($transaction['service'] == "payout" && $transaction['user_id'] == $session->get('user_id') && $transaction['status'] == "Success") {
            $date = new \DateTime($transaction['txn_complete_time']);
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction Found !',
                'data' => [
                   "date" => $date->format('F d, Y'),
                   "time" => $date->format('h:i A'),
                   "amount" => $transaction['amount'],
                   "inwords" => (new Validators())->convertNumberToWord($transaction['amount']),
                   "name" => $transaction['name'],
                   "account" => $transaction['account_number'],
                   "ifsc" => $transaction['ifsc_code'],
                   'remarks' => $transaction['remarks'],
                   'txnid' => $transaction['txnid'],
                   'utr' => $transaction['rrn'],
                   "reference_id" => $transaction['reference_id'],
                ]
            ], 200); 
        }else{
           return response()->json([
                'status' => 'error',
                'message' => 'Invalid Txnid'
           ], 400); 
        }
    }

    public function generate_upi()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $name = request()->input('customer_name');
        $phone = request()->input('customer_phone');
        $email = request()->input('customer_email');
        $amount = request()->input('amount');
        $remarks = request()->input('remarks');
        if (!Validators::name($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid name'
            ], 400);
        }

        if (!Validators::phone($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid phone number'
            ], 400);
        }

        if (!Validators::email($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email'
            ], 400);
        }

        if (!Validators::amount($amount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid amount'
            ], 400);
        }
        
        if ($amount < 100) {
            return response()->json([
                'status' => 'error',
                'message' => 'Min amount for transaction is ₹ 100'
            ], 400);
        }

        if (strlen($remarks) > 100) {
            return response()->json([
                'status' => 'error',
                'message' => 'Remarks should not exceed 100 characters'
            ], 400);
        }

$user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        
        $transaction = new \App\Models\Transaction();
        $start = date('Y-m-d') . ' 00:00:00';
        $end   = date('Y-m-d') . ' 23:59:59';

        $totalPayin = $transaction
            ->selectRaw('SUM(amount) as total')
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->where("user_id",$loggedInUser['id'])
            ->first();
        
        if($totalPayin['total'] > $loggedInUser['payin_limit']){
            return response()->json([
                'status' => 'error',
                'message' => 'Your Today\'s payin Limit is exceed.'
            ], 400);
        }
        
        $providerRow = (new \App\Models\Provider())->where("id",$loggedInUser['payin_provider'])->first();
        if($providerRow && $providerRow['used_limit'] > $providerRow['daily_limit']){
            return response()->json([
                'status' => 'error',
                'message' => 'Pipe Limit is exceed. Please contact to Administrator'
            ], 400);
        }

$settingsModel = new \App\Models\Settings();
        if(!$settingsModel->isPayinEnabled()){
             return response()->json([
                'status' => 'error',
                'message' => 'Payin Service is disabled. Please contact to Administrator'
            ], 400);
        }

        if($loggedInUser['payin'] != 1){
            return response()->json([
                'status' => 'error',
                'message' => 'Payin Service is disabled for your account. Please contact to Administrator'
            ], 400);
        }
        $charge = new \App\Models\Charge();
        $charge = $charge->payin($amount, $loggedInUser['package'], $loggedInUser['payin_provider']);

        if (!$charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Slabs are not defined for this amount'
            ], 400);
        }
    
        $tresult = false;
        $intent = "";
        $referenceId = "";
        $orderId = "";
        $txnId = $this->generateTxnId();
        $isBypass = env('PAYMENT_BYPASS', true);

        if ($isBypass) {
            $tresult = true;
            $intent = "upi://pay?pa=mockpay@upi&pn=" . urlencode($name) . "&am=" . $amount . "&tr=" . $txnId . "&tn=" . urlencode($remarks ?: 'Payin') . "&cu=INR";
            $referenceId = "REF" . time() . rand(100, 999);
            $orderId = "ORD" . time() . rand(100, 999);
            $mockRrn = "BYPASS" . date('YmdHis') . rand(100, 999);
        } elseif ($loggedInUser['payin_provider'] == 1) {
            $provider = new \App\Libraries\Solitpay();
            $result = $provider->generateQr($txnId, $name, $phone, $email, $amount);
            
            if($result->status == "success") { 
                $tresult = true;
                $intent = $result->data->paymentData->intent;
                $referenceId = $result->data->transaction->txnid;
                $orderId = $result->data->transaction->txnid;
            }
        } elseif ($loggedInUser['payin_provider'] == 3) {
            $provider = new \App\Libraries\Zenova();
            $result = $provider->generateQr($txnId, $loggedInUser['merchant_id'], '12355', $name, $amount);
            
            if (isset($result->isSuccess) && $result->isSuccess == true && strtolower($result->status) == "success") {
                $tresult = true;
                $intent = $result->qr ?? '';
                $referenceId = $result->txnID ?? '';
                $orderId = $result->txnID ?? '';
            } 
        }

if(!$tresult) {
            \Illuminate\Support\Facades\DB::table('error_logs')->insert([
                'type' => 'PAYIN',
                'txnid' => $txnId,
                'log' => json_encode($result ?? []),
                'provider' => $loggedInUser['payin_provider'],
                'request' => isset($provider) ? json_encode($provider->getRequest()) : '[]'
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to generate UPI QR code'
            ], 500);
        }
        
        $createdTxn = $transaction->create([
            'user_id' => $session->get('user_id'),
            'service' => 'payin',
            'txnid' => $txnId,
            'reference_id' => $referenceId,
            'rrn' => $isBypass ? $mockRrn : '',
            'status' => $isBypass ? 'Success' : 'Pending',
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'amount' => number_format($amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($amount - $charge['charge'] - $charge['charge_gst'], 2, '.', ''),
            'intent' => $intent,
            'order_id' => $orderId,
            'remarks' => $remarks,
            'payin_provider' => $loggedInUser['payin_provider'],
            'merchant_txnid' => $txnId,
            'payee_name' => $isBypass ? 'Bypass Test' : null,
            'payee_vpa' => $isBypass ? 'mockpay@upi' : null,
            'txn_complete_time' => $isBypass ? date('Y-m-d H:i:s') : null,
            'success_at' => $isBypass ? date('Y-m-d H:i:s') : null
        ]);
        
        if (!$isBypass && isset($provider) && is_object($provider) && method_exists($provider, 'getRequest')) {
            $tr_response = new \App\Models\Transactionres();
            $tr_response->create([
                "txnid" => $txnId, 
                "type" => "request",
                "data" => json_encode($provider->getRequest())
            ]);
            $tr_response->create([
                "txnid" => $txnId, 
                "type" => "creation",
                "data" => json_encode($result ?? [])
            ]);
        }

        if ($isBypass && isset($createdTxn->id)) {
            $this->sendWebhook($createdTxn->id);
        }
        
        $encKey = env("encryption.key", "unizflow_secure_encryption_key_2026");
        return response()->json([
           'status' => 'success',
           'message' => $isBypass ? 'Payment Succeeded (Bypass Mode) !' : 'QR Code Generated Successfully !',
           'data' => [
               'txnid' => $txnId,
               'intent' => $intent,
               'payment_link' => env("app_baseURL", "http://127.0.0.1:8000/")."payment/".bin2hex(openssl_encrypt($txnId, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, substr(hash('sha256', $encKey), 0, 16)))
           ]
       ], 200);

}
    
    public function make_payout_old() {
        $session = session();
        $name = request()->input('customer_name');
        $phone = request()->input('customer_phone');
        $amount = request()->input('amount');
        $account_number = request()->input('account_number');
        $ifsc_code = request()->input('ifsc_code');
        $bank_name = request()->input('bank_name');
        $remarks = request()->input('remark');
        
        if (!Validators::name($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid name'
            ], 400);
        }
        
        if (!Validators::accountNumber($account_number)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Account Number'
            ], 400);
        }
        
        if (!Validators::ifsc($ifsc_code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid IFSC Code'
            ], 400);
        }

        if (!Validators::phone($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid phone number'
            ], 400);
        }

        if (!Validators::amount($amount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid amount'
            ], 400);
        }

        if (strlen($remarks) > 100) {
            return response()->json([
                'status' => 'error',
                'message' => 'Remarks should not exceed 100 characters'
            ], 400);
        }

$user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        
        $settingsModel = new \App\Models\Settings();
        if(!$settingsModel->isPayoutEnabled()){
             return response()->json([
                'status' => 'error',
                'message' => 'Payout Service is disabled. Please contact to Administrator'
            ], 400);
        }
        
        if (!$loggedInUser || !isset($loggedInUser['payout']) || $loggedInUser['payout'] != 1) {
            return response()->json([
             'status' => 'error',
             'message' => 'Payout Service is disabled for your account. Please contact the Administrator.'
            ], 400);
        }
        
        $transaction = new \App\Models\Transaction();
        $start = date('Y-m-d') . ' 00:00:00';
        $end   = date('Y-m-d') . ' 23:59:59';

        $totalPayin = $transaction
            ->selectRaw('SUM(amount) as total')
            ->where('service', 'payout')
            ->where('status', 'Success')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->where("user_id",$loggedInUser['id'])
            ->first();
            
        if($amount > $loggedInUser['payout_per_limit']) { 
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed. Reason: Exceeds per-transaction limit of ₹'.$loggedInUser['payout_per_limit']
            ], 400);
        }  
        
        if($totalPayin['total'] > $loggedInUser['payout_limit']){
            return response()->json([
                'status' => 'error',
                'message' => 'Your Today\'s payout Limit is exceed.'
            ], 400);
        }

$charge = new \App\Models\Charge();
        $charge = $charge->payout($amount, $loggedInUser['package'], $loggedInUser['payout_provider']);

        if (!$charge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Slabs are not defined for this amount'
            ], 400);
        }
        $netAmount = $amount + $charge['charge'] + $charge['charge_gst'];
        
        if($loggedInUser['wallet'] < $netAmount)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient Funds'
            ], 400);
        }
        
        $txnId = $this->generateTxnId();
       
        $transaction->create([
            'user_id' => $session->get('user_id'),
            'service' => 'payout',
            'txnid' => $txnId,
            'reference_id' => '',
            'status' => 'Pending',
            'name' => $name,
            'phone' => $phone,
            'account_number' => $account_number,
            'ifsc_code' => $ifsc_code,
            'bank_name' => $bank_name,
            'amount' => number_format($amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($netAmount, 2, '.', ''),
            'remarks' => $remarks,
            'payout_provider' => $loggedInUser['payout_provider']
        ]);

return response()->json([
           'status' => 'success',
           'message' => 'Transaction Initiated Successfully !'
        ], 200);

}

    public function make_payout()
    {
        $session = session();
        $name = request()->input('customer_name');
        $phone = request()->input('customer_phone');
        $amount = request()->input('amount');
        $account_number = request()->input('account_number');
        $ifsc_code = request()->input('ifsc_code');
        $bank_name = request()->input('bank_name');
        $remarks = request()->input('remark');

if (!Validators::name($name)) return $this->fail("Invalid name");
        if (!Validators::accountNumber($account_number)) return $this->fail("Invalid Account Number");
        if (!Validators::ifsc($ifsc_code)) return $this->fail("Invalid IFSC Code");
        if (!Validators::phone($phone)) return $this->fail("Invalid phone number");
        if (!Validators::amount($amount)) return $this->fail("Invalid amount");
        if (strlen($remarks) > 100) return $this->fail("Remarks should not exceed 100 characters");

$userModel = new \App\Models\User();
        $loggedInUser = $userModel->find($session->get("user_id"));

        $settingsModel = new \App\Models\Settings();
        if (!$settingsModel->isPayoutEnabled()) {
            return $this->fail("Payout Service is disabled. Please contact the Administrator");
        }

        if (!$loggedInUser || $loggedInUser['payout'] != 1) {
            return $this->fail("Payout Service is disabled for your account. Please contact Administrator");
        }

$transaction = new \App\Models\Transaction();
        $start = date('Y-m-d') . " 00:00:00";
        $end   = date('Y-m-d') . " 23:59:59";

        $totalPayin = $transaction
            ->selectRaw('SUM(amount) as total')
            ->where('service', 'payout')
            ->where('status', 'Success')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->where("user_id", $loggedInUser['id'])
            ->first();

        if ($amount > $loggedInUser['payout_per_limit']) {
            return $this->fail("Exceeds per-transaction limit ₹" . $loggedInUser['payout_per_limit']);
        }

        if ($totalPayin['total'] > $loggedInUser['payout_limit']) {
            return $this->fail("Today's payout limit exceeded.");
        }

$chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payout($amount, $loggedInUser['package'], $loggedInUser['payout_provider']);

        if (!$charge) return $this->fail("Slabs are not defined for this amount");

        $netAmount = $amount + $charge['charge'] + $charge['charge_gst'];

        if ($loggedInUser['wallet'] < $netAmount) {
            return $this->fail("Insufficient Funds");
        }

        $txnId = $this->generateTxnId();
        $isBypass = env('PAYMENT_BYPASS', true);

        if ($isBypass) {
            $newBalance = number_format(($loggedInUser['wallet'] - $netAmount), 2, '.', '');
            $userModel->where('id', $loggedInUser['id'])->update(["wallet" => $newBalance]);

            $walletModel = new \App\Models\WalletTransaction();
            $walletModel->create([
                "user_id" => $loggedInUser['id'],
                "type" => "DEBIT",
                "txn_type" => "PAYOUT",
                "txnid" => $txnId,
                "opening" => $loggedInUser['wallet'],
                "amount" => number_format($amount, 2, '.', ''),
                "charges" => number_format($charge['charge'], 2, '.', ''),
                "gst" => number_format($charge['charge_gst'], 2, '.', ''),
                "net_amount" => number_format($netAmount, 2, '.', ''),
                "closing" => $newBalance,
                "remarks" => !empty($remarks) ? $remarks : "Payout Debit: " . $txnId
            ]);

            $utr = 'UTR' . date('YmdHis') . rand(100, 999);
            $refId = 'REF' . time() . rand(100, 999);

            $created = $transaction->create([
                'user_id' => $loggedInUser['id'],
                'service' => 'payout',
                'txnid' => $txnId,
                'reference_id' => $refId,
                'rrn' => $utr,
                'status' => 'Success',
                'debited' => 1,
                'processed' => 1,
                'name' => $name,
                'phone' => $phone,
                'account_number' => $account_number,
                'ifsc_code' => $ifsc_code,
                'bank_name' => $bank_name,
                'amount' => number_format($amount, 2, '.', ''),
                'charges' => number_format($charge['charge'], 2, '.', ''),
                'gst' => number_format($charge['charge_gst'], 2, '.', ''),
                'net_amount' => number_format($netAmount, 2, '.', ''),
                'remarks' => $remarks,
                'payout_provider' => $loggedInUser['payout_provider'],
                'merchant_txnid' => $txnId,
                'txn_complete_time' => date('Y-m-d H:i:s'),
                'success_at' => date('Y-m-d H:i:s')
            ]);

            $this->sendWebhook($created->id);

            return response()->json([
               'status' => 'success',
               'message' => 'Payout Processed Successfully (Bypass Mode) !',
               'data' => [
                   'txnid' => $txnId,
                   'utr' => $utr,
                   'status' => 'Success'
               ]
            ], 200);
        }

        $transaction->create([
            'user_id' => $loggedInUser['id'],
            'service' => 'payout',
            'txnid' => $txnId,
            'reference_id' => '',
            'status' => 'Pending',
            'name' => $name,
            'phone' => $phone,
            'account_number' => $account_number,
            'ifsc_code' => $ifsc_code,
            'bank_name' => $bank_name,
            'amount' => number_format($amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($netAmount, 2, '.', ''),
            'remarks' => $remarks,
            'payout_provider' => $loggedInUser['payout_provider'],
            'merchant_txnid' => $txnId
        ]);

        return response()->json([
           'status' => 'success',
           'message' => 'Transaction Initiated Successfully !'
        ], 200);
    }

    private function sendWebhook($id)
    {
        $artisanPath = base_path('artisan');
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            pclose(popen("start /B php \"{$artisanPath}\" webhook:send {$id}", "r"));
        } else {
            shell_exec("php \"{$artisanPath}\" webhook:send '{$id}' > /dev/null 2>&1 &");
        }
    }

    private function fail($msg)
    {
        return response()->json(['status' => 'error', 'message' => $msg]);
    }

public function get_dashboard_data()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user_id = $session->get('user_id');
        $transaction = new \App\Models\Transaction();
        $todayData = $transaction->getTodayData($user_id);
        $totalData = $transaction->getTotalData($user_id);
        $unsettledAmount = $transaction
            ->where('user_id', $user_id)
            ->where('service', 'payin')
            ->where('status', 'Success')
            ->where('settled', 0)
            ->sum('net_amount');
        $totalData['unsettled'] = number_format($unsettledAmount, 2, '.', '');
        $statusData = $transaction->getStatusData($user_id);
        $recentTransactions = $transaction->getRecentTransactions($user_id,5);
        $weeklySummary = $transaction->getWeeklySummary($user_id);
        $login = new \App\Models\Login();
        $loginHistory = $login->getHistory($user_id,4);
        $settlement = new \App\Models\Settlement();
        $settlementHistory = $settlement->getRecent($user_id,4);
        return response()->json([
           'status' => 'success',
           'message' => 'Dashboard data Fetched Successfully !',
           'data' => [
               "today" => $todayData,
               "status" => $statusData,
               "weeklySummary" => $weeklySummary,
               "total" => $totalData,
              
            ]
        ], 200);
        
    }
    
    public function get_login_history() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user_id = $session->get('user_id');
        $login = new \App\Models\Login();
        $loginHistory = $login->getHistory($user_id,5);
        return response()->json([
           'status' => 'success',
           'message' => 'Login History Fetched Successfully !',
           'data' => [
               "loginHistory" => $loginHistory,
            ]
        ], 200);
    }
    
    private function generateTxnId()
    {
        $txnId = strtoupper(substr("UNZ" . dechex(time()) . bin2hex(random_bytes(3)), 0, 20));
        $transaction = new \App\Models\Transaction();
        if ($transaction->isTxnIdExists($txnId)) {
            return $this->generateTxnId();
        }else{
            return $txnId;
        }
    }

public function regenerate_api_key_send_otp()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $lastSentTime = $session->get('api_key_otp_time');
        
        if ($lastSentTime && (time() - $lastSentTime) < 600) {
            $remaining = 600 - (time() - $lastSentTime);
            return response()->json([
                'status' => "error",
                'message' => "OTP already sent. Try again After 10 minutes"
            ], 400);
        }

        $newApiKey = bin2hex(random_bytes(30));
        $otp = random_int(100000, 999999);

        $session->put([
            'pending_api_key' => $newApiKey,
            'api_key_otp' => $otp,
            'api_key_otp_time' => time(),
        ]);
        $email = $loggedInUser['email'];
        
        $this->sendEmail($email,"OTP for Regenerate API Keys","Your OTP for Regenerate API Keys is ".$otp);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.'
        ]);
    }

public function verify_otp_and_update_api_key()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $inputOtp = request()->post('otp'); 
    
        $storedOtp = $session->get('api_key_otp');
        $newApiKey = $session->get('pending_api_key');
        $otpTime = $session->get('api_key_otp_time');
    
        if (!$storedOtp || !$otpTime || (time() - $otpTime) > 600) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired or not found. Please try again.'
            ], 400);
        }
        
        if(strlen($inputOtp) != 6){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }
    
        if ($inputOtp != $storedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }
    
        $user->where('id', $loggedInUser['id'])->update(["api_access_key" => $newApiKey]);
     
        $session->forget(['api_key_otp', 'pending_api_key', 'api_key_otp_time']);
    
        return response()->json([
            'status' => 'success',
            'message' => 'API Key regenerated and updated successfully.'
        ], 200);
    }

public function update_webhook_send_otp($type = null) {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $url = request()->input('url');
        
        if (!$type) {
            $type = request()->input('type');
        }

        $type = strtolower((string) $type);

        if(!in_array($type, ["payin", "payout"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Url Type'
            ], 400);
        }
        
        if(!Validators::url($url)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Url'
            ], 400);
        }
        
        $lastSentTime = $session->get('wh_otp_time');
        
        if ($lastSentTime && (time() - $lastSentTime) < 600) {
            $remaining = 600 - (time() - $lastSentTime);
            return response()->json([
                'status' => "error",
                'message' => "OTP already sent. Try again After 10 minutes"
            ], 400);
        }

        $otp = random_int(100000, 999999);

        $session->put([
            'pending_url' => $url,
            'pending_url_type' => $type,
            'wh_otp' => $otp,
            'wh_otp_time' => time(),
        ]);
        $email = $loggedInUser['email'];
        
        $this->sendEmail($email,"OTP for Update Webhook URL","Your OTP for Update Webhook URL is ".$otp);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.'
        ]);

    }

public function verify_otp_and_update_webhook_url(){
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $inputOtp = request()->post('otp') ?? request()->input('otp'); 
    
        $storedOtp = $session->get('wh_otp');
        $newUrl = $session->get('pending_url');
        $type = $session->get('pending_url_type');
        $otpTime = $session->get('wh_otp_time');
    
        if (!$storedOtp || !$otpTime || (time() - $otpTime) > 600) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired or not found. Please try again.'
            ], 400);
        }
        
        if(strlen($inputOtp) != 6){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }
    
        if ($inputOtp != $storedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }
        
        $oldUrl = $loggedInUser[strtolower($type).'_webhook'];
    
        $user->where('id', $loggedInUser['id'])->update([strtolower($type).'_webhook' => $newUrl, 'previous_'.strtolower($type).'_webhook' => $oldUrl]);
     
        $session->forget(['wh_otp', 'pending_url','pending_url_type', 'wh_otp_time']);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook URL updated successfully.'
        ]);
    }
    
    public function whitelist_ip_send_otp() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $ip = request()->input('ip');
        
        if(!Validators::ip($ip)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid IP'
            ], 400);
        }
        
        $ipModel = new \App\Models\IP();
        $ips = $ipModel->where("user_id",$loggedInUser['id'])->where("deleted",0)->get();
        
        if(count($ips) > 3) {
            return response()->json([
                'status' => 'error',
                'message' => "Maximum 4 Server IP's Can be Whitelisted"
            ], 400);
        }
        
        foreach ($ips as $lip){
            if(trim($lip['ip']) == trim($ip)){
                return response()->json([
                   'status' => 'error',
                   'message' => "Entered IP is already Whitelisted."
               ], 429);
            }
        }
        
        $lastSentTime = $session->get('ip_otp_time');
        
        if ($lastSentTime && (time() - $lastSentTime) < 600) {
            $remaining = 600 - (time() - $lastSentTime);
            return response()->json([
                'status' => "error",
                'message' => "OTP already sent. Try again After 10 minutes"
            ], 400);
        }

        $otp = random_int(100000, 999999);

        $session->put([
            'ip' => $ip,
            'ip_otp' => $otp,
            'ip_otp_time' => time(),
        ]);
        $email = $loggedInUser['email'];
        
        $this->sendEmail($email,"OTP for Whitelist IP","Your OTP for Whitelist IP is ".$otp);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.'
        ]);
    }
    
    public function verify_otp_and_add_ip() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $inputOtp = request()->post('otp'); 
    
        $storedOtp = $session->get('ip_otp');
        $ip = $session->get('ip');
        $otpTime = $session->get('ip_otp_time');
        
        if(strlen($inputOtp) != 6){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }
        
        if(!Validators::ip($ip)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid IP'
            ], 400);
        }
        
        if (!$storedOtp || !$otpTime || (time() - $otpTime) > 600) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired or not found. Please try again.'
            ], 400);
        }
    
        if ($inputOtp != $storedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }
        
        $ipModel = new \App\Models\IP();
        $ips = $ipModel->where("user_id",$loggedInUser['id'])->where("deleted",0)->get();
        
        if(count($ips) > 3) {
            return response()->json([
                'status' => 'error',
                'message' => "Maximum 4 Server IP's Can be Whitelisted"
            ], 400);
        }
        
        foreach ($ips as $lip){
            if(trim($lip['ip']) == trim($ip)){
                return response()->json([
                   'status' => 'error',
                   'message' => "Entered IP is already Whitelisted."
               ], 429);
            }
        }
        
        $ipModel->create([
           "user_id" => $loggedInUser['id'],
           "ip" => trim($ip)
        ]);
     
        $session->forget(['ip_otp', 'ip', 'ip_otp_time']);
    
        return response()->json([
            'status' => 'success',
            'message' => 'IP Whitelisted Successfully'
        ]);
    }
    
    public function delete_ip_send_otp() {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $encId = request()->post("id");
        $id = openssl_decrypt(hex2bin($encId),'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16));
        
        if($id == null){
            return response()->json([
                   'status' => 'error',
                   'message' => "Invalid IP ID"
            ], 400);
        }
        
        $ipModel = new \App\Models\IP();
        $ip = $ipModel->find($id); 
        if(!isset($ip['id'])){
            return response()->json([
                   'status' => 'error',
                   'message' => "Invalid IP ID"
            ], 400);
        }
        
        if($ip['user_id'] != $loggedInUser['id']){
            return response()->json([
                   'status' => 'error',
                   'message' => "Unauthorized to delete IP"
            ], 401);
        }
        
        $lastSentTime = $session->get('ipdl_otp_time');

$otp = random_int(100000, 999999);

        $session->put([
            'ipid' => $id,
            'ipdl_otp' => $otp,
            'ipdl_otp_time' => time(),
        ]);
        $email = $loggedInUser['email'];
        
        $this->sendEmail($email,"OTP for Delete Whitelisted IP","Your OTP for Delete Whitelisted IP is ".$otp);
        
        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.'
        ]);
        
    }
    
    public function verify_otp_and_delete_ip(){
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $inputOtp = request()->post('otp'); 
    
        $storedOtp = $session->get('ipdl_otp');
        $id = $session->get('ipid');
        $otpTime = $session->get('ipdl_otp_time');
        
        if(strlen($inputOtp) != 6){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 400);
        }

if (!$storedOtp || !$otpTime || (time() - $otpTime) > 600) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired or not found. Please try again.'
            ], 400);
        }
    
        if ($inputOtp != $storedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }
        
        $ipModel = new \App\Models\IP();
        
        $ip = $ipModel->find($id); 
        if(!isset($ip['id'])){
            return response()->json([
                   'status' => 'error',
                   'message' => "Invalid IP ID"
            ], 400);
        }
        
        if($ip['user_id'] != $loggedInUser['id']){
            return response()->json([
                   'status' => 'error',
                   'message' => "Unauthorized to delete IP"
            ], 401);
        }
        
        $ipModel->where('id', $id)->update([
          "deleted" => 1,
          "deleted_at" => date("Y-m-d H:i:s")
        ]);
    
        return response()->json([
            'status' => 'success',
            'message' => 'IP Deleted Successfully'
        ]);
    }
    
    public function update_password(){
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = new \App\Models\User();
        $loggedInUser = $user->find($session->get('user_id'));
        $password = request()->input('password');
        $npassword = request()->input('npassword');
        $cpassword = request()->input('cpassword');
        
        if(!Hash::check($password, $loggedInUser['password'])){
            return response()->json([
                   'status' => 'error',
                   'message' => "Current password not Matched"
            ], 401);
        }
        
        if(!Validators::password($npassword)){
            return response()->json([
                   'status' => 'error',
                   'message' => "New password is invalid. please use capital letters, small letters , numbers and special characters."
            ], 400);
        }
        
        if($npassword != $cpassword){
            return response()->json([
                   'status' => 'error',
                   'message' => "New Password & Confirm Password not Matched"
            ], 400);
        }
        
        $user->where('id', $loggedInUser['id'])->update([
          "password" => Hash::make($npassword)
        ]);
        
         return response()->json([
            'status' => 'success',
            'message' => 'Password Changed Successfully !'
        ]);
        
    }
    
    public function update_bank_details(){
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $account = request()->input('account');
        $ifsc = request()->input('ifsc');
        $name = request()->input('name');
        if ($name === '' || strlen($name) < 3) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Account holder name must be at least 3 characters'
            ], 422);
        }

        if ($account === '' || !ctype_digit($account) || strlen($account) < 9 || strlen($account) > 18) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid account number (must be 9–18 digits)'
            ], 422);
        }

        if ($ifsc === '' || !preg_match("/^[A-Z]{4}0[A-Z0-9]{6}$/", $ifsc)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid IFSC code format'
            ], 422);
        }

        $user_id = $session->get('user_id');
        $ifscData = $this->fetchIfscDetails($ifsc);
        $bank = $ifscData['BANK'] ?? 'Bank';
        $branch = $ifscData['BRANCH'] ?? 'Main Branch';
        
        $isBypass = env('PAYMENT_BYPASS', true);
        
        if(!$isBypass && \Illuminate\Support\Facades\DB::table('users_bank')->where('user_id', $user_id)->where('status', 'Pending')->count() != 0){
            return response()->json([
                'status'  => 'error',
                'message' => 'Your previous bank account is already pending for approval. Please wait for update before updating a new one.'
            ], 422);
        }
        
        $bankStatus = $isBypass ? "Approved" : "Pending";

        \Illuminate\Support\Facades\DB::table("users_bank")->insert([
          "user_id" => $user_id,
          "name" => $name,
          "account_name" => $name,
          "account_number" => $account,
          "account_ifsc" => $ifsc,
          "bank_name" => $bank,
          "branch" => $branch,
          "status" => $bankStatus,
          "created_at" => now(),
          "updated_at" => now()
        ]);
        
         return response()->json([
            'status'  => 'success',
            'message' => $isBypass ? 'Bank Account Updated & Approved Successfully !' : 'Bank Account Updated Successfully !'
         ], 200);
    }

    private function fetchIfscDetails($ifsc)
    {
        $ifsc = strtoupper(trim($ifsc));
        $url = "https://ifsc.razorpay.com/" . urlencode($ifsc);
        
        $response = Http::withoutVerifying()
            ->timeout(5)
            ->connectTimeout(3)
            ->withUserAgent('Mozilla/5.0')
            ->get($url);

        if ($response->successful()) {
            $data = $response->json();
            if (is_array($data) && !empty($data['BANK'])) {
                return $data;
            }
        }

        $bankPrefix = substr($ifsc, 0, 4);
        $knownBanks = [
            'SBIN' => 'State Bank of India',
            'HDFC' => 'HDFC Bank',
            'ICIC' => 'ICICI Bank',
            'UTIB' => 'Axis Bank',
            'KKBK' => 'Kotak Mahindra Bank',
            'PUNB' => 'Punjab National Bank',
            'BARB' => 'Bank of Baroda',
            'CNRB' => 'Canara Bank',
            'UBIN' => 'Union Bank of India',
            'BKID' => 'Bank of India',
            'IOBA' => 'Indian Overseas Bank',
            'IDFB' => 'IDFC FIRST Bank',
            'YESB' => 'Yes Bank',
            'INDB' => 'IndusInd Bank',
            'AIRP' => 'Airtel Payments Bank',
            'PYTM' => 'Paytm Payments Bank',
            'JAKA' => 'Jammu and Kashmir Bank'
        ];

        return [
            'BANK' => $knownBanks[$bankPrefix] ?? ("Bank (" . $bankPrefix . ")"),
            'BRANCH' => 'Main Branch',
            'IFSC' => $ifsc
        ];
    }

private function sendEmail($email, $subject, $message){
        try {
            \Illuminate\Support\Facades\Mail::raw($message, function($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SendEmail error: ' . $e->getMessage());
        }
    }

    public function api_docs()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return redirect()->route('login');
        } else {
            $user = new \App\Models\User();
            $user = $user->find($session->get('user_id'));
            $data['user'] = $user;
            return view('api_docs', $data);
        }
    }

    public function export_wallet_ledger()
    {
        $session = session();
        if (!$session->get('user_id')) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized"
            ], 401);
        }

        $searchBy = request()->input('searchBy');
        $searchValue = request()->input('searchValue');
        $availableSearchKeys = ["type", "txn_type", "txnid", "amount", "opening", "closing", "remarks"];

        $walletModel = new \App\Models\WalletTransaction();
        $builder = $walletModel->newQuery();
        $builder->where('user_id', $session->get('user_id'));

        if (!empty($searchBy) && !empty($searchValue) && in_array($searchBy, $availableSearchKeys)) {
            $builder->where($searchBy, 'LIKE', '%' . trim($searchValue, "\"'") . '%');
        }

        $builder->orderBy('id', 'DESC');
        $filename = 'wallet_ledger_' . date('Ymd_His') . '.csv';
        $columns = ['ID', 'Type', 'Txn Type', 'Txn ID', 'Net Amount', 'Opening', 'Closing', 'Remarks', 'Created At'];

        return response()->streamDownload(function() use ($builder, $columns) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);
            $index = 1;

            foreach ($builder->cursor() as $wt) {
                fputcsv($output, [
                    $index++,
                    strtoupper($wt->type ?? ''),
                    $wt->txn_type ?? '',
                    $wt->txnid ?? '',
                    $wt->net_amount ?? $wt->amount ?? '0',
                    $wt->opening ?? '0',
                    $wt->closing ?? '0',
                    $wt->remarks ?? '',
                    $wt->created_at ?? ''
                ]);
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

}
