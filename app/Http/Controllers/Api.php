<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\IP;
use App\Models\Key;
use App\Models\Transaction;
use App\Libraries\Validators;
use App\Models\ApiRequest;
use Exception;

class Api extends Controller
{
    private $user;
    private $keys;
    private $apiRequestModel;
    private $apiRequestId;
    private $startTime;

    public function testToken()
    {
        $provider   = strtolower($this->request->getHeaderLine('Provider'));
        if ($provider === "solitpay") {
            $solitpay = new \App\Libraries\Solitpay();
            $token = $solitpay->generateToken();
        }
        if ($provider === "zenova") {
            $solitpay = new \App\Libraries\Zenova();
            $token = $solitpay->generateToken();
        }
        return $this->respond([
            "status" => "success",
            'error' => false,
            'message' => 'Token generated successfully.',
            'token' => $token
        ],200);
    }
    
    private function validateHeaders()
    {
        $this->startTime = microtime(true);

        $merchantId = $this->request->getHeaderLine('Merchant-id');
        $token      = $this->request->getHeaderLine('Token');

        if (strlen($merchantId) !== 10) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Merchant ID must be exactly 10 characters.",
            ], 401);
        }

        if (strlen($token) < 10) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication token is invalid or too short.",
            ], 401);
        }

        $userModel = new User();
        $user = $userModel->findMerchantId($merchantId);

        if (!isset($user['id'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Merchant ID not found.",
            ], 401);
        }

        $ipModel = new IP();
        $ipAddress = request()->ip();
        $ipAddress='::1';
        if (!$ipModel->isWhiteListed($user['id'], $ipAddress)) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: IP address is not whitelisted.",
            ], 401);
        }

        $this->user = $user;
        
        if($user['api_access'] == 0) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: You don't have access to API.",
            ], 401);
        }
        
        if($user['active'] == 0) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: User is inactive please contact to Administrator",
            ], 401);
        }

        /**************************************
         * PROVIDER WISE TOKEN HANDLING
         **************************************/
        if ($user['payin_provider'] == 1 || $user['payout_provider'] == 2) {
            $keyModel = new Key();
            $this->keys = $keyModel->findUser($user['id']);

            $decryptedData = $this->decrypt($token, $this->keys['aes_key'], $this->keys['aes_iv']);
            if (!$decryptedData) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication failed: Token decryption failed.",
                ], 401);
            }

            $data = json_decode($decryptedData, true);

            if (!isset($data['timestamp'], $data['secret'], $data['reqId'])) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication failed: Token is malformed.",
                ], 401);
            }

            if ($data['secret'] !== $user['api_access_key']) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication failed: Secret key mismatch.",
                ], 401);
            }

            // Expiry
            if (time() - intval($data['timestamp']) > 300) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication token has expired.",
                ], 401);
            }
        }

        if ($user['payin_provider'] == 3 || $user['payout_provider'] == 4) {
            $payloadJson = base64_decode($token);
            $data = json_decode($payloadJson, true);

            if (!isset($data['timestamp'], $data['reqId'])) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication failed: Token is malformed.",
                ], 401);
            }

            if (time() - intval($data['timestamp']) > 300) {
                return response()->json([
                    "status" => "error",
                    "error" => true,
                    "message" => "Authentication token has expired.",
                ], 401);
            }
        } 

        /**************************************
         * COMMON VALIDATIONS
         **************************************/
        
        if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $data['reqId'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Invalid ReqId: Must be alphanumeric and 5-20 characters long.",
            ], 401);
        }

        $this->apiRequestModel = new ApiRequest();
        if ($this->apiRequestModel->isReqIdExists($data['reqId'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Duplicate ReqId: This request has already been processed.",
            ], 409);
        }

        /**************************************
         * REQUEST LOGGING
         **************************************/
        $headers = $this->request->getHeaders();
        $formattedHeaders = [];

        foreach ($headers as $name => $values) {
            $formattedHeaders[$name] = $values->getValueLine();
        }

        $jsonHeaders = json_encode($formattedHeaders, JSON_UNESCAPED_UNICODE);

        $rawInput = $this->request->getBody();
        $datau = json_decode($rawInput);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Invalid JSON Body",
            ])->setStatusCode(400);
        }

        $this->apiRequestId = $this->apiRequestModel->create(
            [
                "reqid" => $data['reqId'],
                "user_id" => $user['id'],
                "path" => $_SERVER['REQUEST_URI'],
                "method" => $_SERVER['REQUEST_METHOD'],
                "ip" => $ipAddress,
                "headers" => $jsonHeaders,
                "payload" => json_encode((object) json_decode(request()->getContent())),
            ],
            true
        );

        return null;
    }


    private function validateHeaders_old()
    {
        $this->startTime = microtime(true);
        $merchantId = $this->request->getHeaderLine('Merchant-id');
        $token = $this->request->getHeaderLine('Token');

        if (strlen($merchantId) !== 10) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Merchant ID must be exactly 10 characters.",
            ], 401);
        }

        if (strlen($token) < 10) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication token is invalid or too short.",
            ], 401);
        }

        $userModel = new User();
        $user = $userModel->findMerchantId($merchantId);

        if (!isset($user['id'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Merchant ID not found.",
            ], 401);
        }

        $ipModel = new IP();
        $ipAddress = request()->ip();
        
        if (!$ipModel->isWhiteListed($user['id'], $ipAddress)) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: IP address is not whitelisted.",
            ], 401);
        }

        $this->user = $user;
        
        if($user['api_access'] == 0) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: You don't have access to API.",
            ], 401);
        }
        
        if($user['active'] == 0) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Access denied: User is inactive please contact to Administrator",
            ], 401);
        }

        $keyModel = new Key();
        $this->keys = $keyModel->findUser($user['id']);

        $decryptedData = $this->decrypt($token, $this->keys['aes_key'], $this->keys['aes_iv']);
        if (!$decryptedData) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication failed: Token decryption failed.",
            ], 401);
        }

        $data = json_decode($decryptedData, true);
        if (!isset($data['timestamp'], $data['secret'], $data['reqId'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication failed: Token is malformed.",
            ], 401);
        }

        if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $data['reqId'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Invalid ReqId: Must be alphanumeric and 5-20 characters long.",
            ], 401);
        }

        if ($data['secret'] !== $user['api_access_key']) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication failed: Secret key mismatch.",
            ], 401);
        }

        // Check if token is expired (more than 5 minutes)
        $currentTime = time();
        if ($currentTime - intval($data['timestamp']) > 300) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Authentication token has expired.",
            ], 401);
        }

        //check if req id already used
        $this->apiRequestModel = new ApiRequest();
        if ($this->apiRequestModel->isReqIdExists($data['reqId'])) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Duplicate ReqId: This request has already been processed.",
            ], 409);
        }

        $headers = $this->request->getHeaders();

        $formattedHeaders = [];
        foreach ($headers as $name => $values) {
            // getValueLine() gives the header value as a string
            $formattedHeaders[$name] = $values->getValueLine();
        }

        $jsonHeaders = json_encode($formattedHeaders, JSON_UNESCAPED_UNICODE);
        
        $rawInput = $this->request->getBody();
        $datau = json_decode($rawInput);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                "status" => "error",
                "error" => true,
                "message" => "Invalid JSON Body",
            ])->setStatusCode(400);
        }

        //Insert Request
        $this->apiRequestId = $this->apiRequestModel->create(
            [
                "reqid" => $data['reqId'],
                "user_id" => $user['id'],
                "path" => $_SERVER['REQUEST_URI'],
                "method" => $_SERVER['REQUEST_METHOD'],
                "ip" => $ipAddress,
                "headers" => $jsonHeaders,
                "payload" => json_encode((object) json_decode(request()->getContent())),
            ],
            true
        );

        return null;
    }

    public function check_balance()
    {
        if ($validation = $this->validateHeaders()) {
            return $validation;
        }
        return $this->respond([
            "status" => "success",
            'error' => false,
            'message' => 'Wallet balance retrieved successfully.',
            'data' => [
                'wallet_balance' => $this->user['wallet'],
            ],
        ],200);
    }

    public function generate_qr_old()
    {
        if ($validation = $this->validateHeaders()) {
            return $validation;
        }
        
        $data = (object) json_decode(request()->getContent());
        
        
        $settingsModel = new \App\Models\Settings();
        if(!$settingsModel->isPayinEnabled()){
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payin Services are disabled. please contact to administrator',
            ],401);
        }
        
        if($this->user['payin'] != 1) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payin Services are disabled for your account. please contact to administrator',
            ],401);
        }

        // Name validation
        if (!Validators::name($data->name ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Full name is required (first and last).',
            ],400);
        }

        // Email validation
        if (!Validators::email($data->email ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid email format.',
            ], 400);
        }

        // Phone validation
        if (!Validators::phone($data->phone ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid phone number.',
            ],400);
        }

        // Amount validation
        if (!Validators::amount($data->amount ?? 0)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid transaction amount.',
            ],400);
        }
        
        if ($data->amount < 100) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Min amount for transaction is ₹ 100.',
            ],400);
        }

        // Merchant Txn ID validation
        if (strlen($data->merchantTxnId ?? '') < 5) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Merchant Transaction ID is too short.',
            ],400);
        }

        // Notes length validation
        if (strlen($data->notes ?? '') > 100) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Notes exceed maximum length of 100 characters.',
            ],400);
        }

        // Duplicate transaction check
        $transactionModel = new \App\Models\Transaction();
        if ($transactionModel->isMerchantTxnIdExists($data->merchantTxnId)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Duplicate error: Merchant Transaction ID already exists.',
            ],409);
        }
        
        
        if($this->user['todays_payin'] > $this->user['payin_limit']) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Your Today\'s payin Limit is exceed.',
            ],429);
        }

        
        $provider = (new \App\Models\Provider())->where("id",$this->user['payin_provider'])->first();
        if($provider['used_limit'] > $provider['daily_limit']){
             return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Pipe Limit is exceed. Please contact to Administrator',
            ],400);
        }

        // Calculate charge based on slab
        $chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payin($data->amount, $this->user['package'], $this->user['payin_provider']);

        if (!$charge) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Slabs are not defined for this amount.',
            ],400);
        }
        
        $tresult = false;
        $intent = "";
        $referenceId = "";
        $orderId = "";
        $txnId = $this->generateTxnId();
        $result = null;
        if($this->user['payin_provider'] == 1){
            //Solitpay
            $provider = new \App\Libraries\Solitpay();
            $result = $provider->generateQr($txnId, $data->name,$data->phone,$data->email, $data->amount);
            if(isset($result->status) && $result->status == "success") { 
                $tresult = true;
                $intent = $result->data->paymentData->intent;
                $referenceId = $result->data->transaction->txnid;
                $orderId = $result->data->transaction->txnid;
            }
        }
        
        
        $transaction = new \App\Models\Transaction();
        $transaction->create([
            'user_id' => $this->user['id'],
            'service' => 'payin',
            'txnid' => $txnId,
            'reference_id' => $referenceId,
            'status' => 'Pending',
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'amount' => number_format($data->amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($data->amount - $charge['charge'] - $charge['charge_gst'], 2, '.', ''),
            'intent' => $intent,
            'order_id' => $orderId,
            'remarks' => $data->notes ?? '',
            'payin_provider' => $this->user['payin_provider'],
            'source' => "api",
            'merchant_txnid' => $data->merchantTxnId
        ]);
        
        $tr_response = new \App\Models\Transactionres();
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "request",
            "data" => json_encode($provider->getRequest())
        ]);
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "creation",
            "data" => json_encode($result)
        ]);
        
        
        return $this->respond([
            'status' => 'success',
            'error' => false,
            'message' => 'QR Code Generated Successfully !',
            'data' => [
                "transaction" => [
                   "txnStatus" => "Pending",
                   "txnid" => $txnId,
                   "txnReference" => $data->merchantTxnId,
                ],
                "commercials" => [
                   "amount" => $data->amount,
                   "charges" => number_format($charge['charge'], 2, '.', ''),
                   "gst" => number_format($charge['charge_gst'], 2, '.', ''),
                ],
                "paymentData" => [
                    "intent" => $intent,
                    "paymentLink" => env("app_baseURL")."payment/".bin2hex(openssl_encrypt($txnId, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)))
                ],
                "datetime" => date("Y-m-d H:i:s")
            ]
        ],200);
        
        
    }

    public function generate_qr()
    {
        if ($validation = $this->validateHeaders()) {
            return $validation;
        }
        
        $data = (object) json_decode(request()->getContent());
        
        
        $settingsModel = new \App\Models\Settings();
        if(!$settingsModel->isPayinEnabled()){
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payin Services are disabled. please contact to administrator',
            ],401);
        }
        
        if($this->user['payin'] != 1) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payin Services are disabled for your account. please contact to administrator',
            ],401);
        }

        // Name validation
        if (!Validators::name($data->name ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Full name is required (first and last).',
            ],400);
        }

        // Email validation
        if (!Validators::email($data->email ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid email format.',
            ], 400);
        }

        // Phone validation
        if (!Validators::phone($data->phone ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid phone number.',
            ],400);
        }

        // Amount validation
        if (!Validators::amount($data->amount ?? 0)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid transaction amount.',
            ],400);
        }
        
        if ($data->amount < 100) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Min amount for transaction is ₹ 100.',
            ],400);
        }

        // Merchant Txn ID validation
        if (strlen($data->merchantTxnId ?? '') < 5) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Merchant Transaction ID is too short.',
            ],400);
        }

        // Notes length validation
        if (strlen($data->notes ?? '') > 100) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Notes exceed maximum length of 100 characters.',
            ],400);
        }

        // Duplicate transaction check
        $transactionModel = new \App\Models\Transaction();
        if ($transactionModel->isMerchantTxnIdExists($data->merchantTxnId)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Duplicate error: Merchant Transaction ID already exists.',
            ],409);
        }
        
        
        if($this->user['todays_payin'] > $this->user['payin_limit']) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Your Today\'s payin Limit is exceed.',
            ],429);
        }

        
        $provider = (new \App\Models\Provider())->where("id",$this->user['payin_provider'])->first();
        if($provider['used_limit'] > $provider['daily_limit']){
             return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Pipe Limit is exceed. Please contact to Administrator',
            ],400);
        }

        // Calculate charge based on slab
        $chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payin($data->amount, $this->user['package'], $this->user['payin_provider']);

        if (!$charge) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Slabs are not defined for this amount.',
            ],400);
        }
        
        $tresult = false;
        $intent = "";
        $referenceId = "";
        $orderId = "";
        $txnId = $this->generateTxnId();
        $result = null;
        if($this->user['payin_provider'] == 1){
            //Solitpay
            $provider = new \App\Libraries\Solitpay();
            $result = $provider->generateQr($txnId, $data->name,$data->phone,$data->email, $data->amount);
            if(isset($result->status) && $result->status == "success") { 
                $tresult = true;
                $intent = $result->data->paymentData->intent;
                $referenceId = $result->data->transaction->txnid;
                $orderId = $result->data->transaction->txnid;
            }
        }
        elseif ($this->user['payin_provider'] == 3) {
            // Zenova Integration
            $provider = new \App\Libraries\Zenova();
            $result = $provider->generateQr($txnId, $data->name, $data->amount);
            
            if (isset($result->isSuccess) && $result->isSuccess == true && strtolower($result->status) == "success") {
                $tresult = true;
                $intent = $result->qr ?? '';
                $referenceId = $result->txnID ?? '';
                $orderId = $result->txnID ?? '';
            } else {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Zenova Error: ' . ($result->message ?? 'Unknown error'),
                    'response' => $result
                ], 400);
            }
        }
        
        $transaction = new \App\Models\Transaction();
        $transaction->create([
            'user_id' => $this->user['id'],
            'service' => 'payin',
            'txnid' => $txnId,
            'reference_id' => $referenceId,
            'status' => 'Pending',
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'amount' => number_format($data->amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($data->amount - $charge['charge'] - $charge['charge_gst'], 2, '.', ''),
            'intent' => $intent,
            'order_id' => $orderId,
            'remarks' => $data->notes ?? '',
            'payin_provider' => $this->user['payin_provider'],
            'source' => "api",
            'merchant_txnid' => $data->merchantTxnId
        ]);
        
        $tr_response = new \App\Models\Transactionres();
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "request",
            "data" => json_encode($provider->getRequest())
        ]);
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "creation",
            "data" => json_encode($result)
        ]);
        
        
        return $this->respond([
            'status' => 'success',
            'error' => false,
            'message' => 'QR Code Generated Successfully !',
            'data' => [
                "transaction" => [
                   "txnStatus" => "Pending",
                   "txnid" => $txnId,
                   "txnReference" => $data->merchantTxnId,
                ],
                "commercials" => [
                   "amount" => $data->amount,
                   "charges" => number_format($charge['charge'], 2, '.', ''),
                   "gst" => number_format($charge['charge_gst'], 2, '.', ''),
                ],
                "paymentData" => [
                    "intent" => $intent,
                    "paymentLink" => env("app_baseURL")."payment/".bin2hex(openssl_encrypt($txnId, 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)))
                ],
                "datetime" => date("Y-m-d H:i:s")
            ]
        ],200);
        
        
    }

    public function generate_qr_new_old()
    {
        $data = (object) json_decode(request()->getContent());

        // Zenova (provider = 3) ke liye validateHeaders skip hoga
        if (($data->memberid ?? null) && ($data->txnpwd ?? null)) {
            // Zenova ke liye memberid se user nikal lo
            $userModel = new \App\Models\User();
            $this->user = $userModel->where('merchant_id', $data->memberid)->where('active', 1)->first();
            
            if (!$this->user) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Unauthorized: Invalid Zenova credentials (memberid or txnpwd).',
                ], 401);
            }
            
        } 
        else {
            // Solitpay (or other providers) -> validateHeaders() call
            if ($validation = $this->validateHeaders()) {
                return $validation;
            }
        }
        
        $settingsModel = new \App\Models\Settings();
        if (!$settingsModel->isPayinEnabled()) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Payin Services are disabled. please contact to administrator',
            ], 401);
        }

        if ($this->user['payin'] != 1) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Payin Services are disabled for your account. please contact to administrator',
            ], 401);
        }

        $merchantTxnId = "";
        
        // validation alag-alag provider ke hisab se
        if ($this->user['payin_provider'] == 1) {
            // Solitpay validations
            if (!Validators::name($data->name ?? '')) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Full name is required (first and last).',
                ], 400);
            }

            if (!Validators::email($data->email ?? '')) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid email format.',
                ], 400);
            }

            if (!Validators::phone($data->phone ?? '')) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid phone number.',
                ], 400);
            }

            if (!Validators::amount($data->amount ?? 0)) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid transaction amount.',
                ], 400);
            }

            if ($data->amount < 100) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Min amount for transaction is ₹ 100.',
                ], 400);
            }

            if (strlen($data->merchantTxnId ?? '') < 5) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Merchant Transaction ID is too short.',
                ], 400);
            }

            if (strlen($data->notes ?? '') > 100) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Notes exceed maximum length of 100 characters.',
                ], 400);
            }

            $merchantTxnId = $data->merchantTxnId ?? '';
        } 
        elseif ($this->user['payin_provider'] == 3) {
            // Zenova validations
            if (empty($data->memberid) || empty($data->txnpwd) || empty($data->name) || empty($data->amount) || empty($data->txnid)) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Missing required Zenova parameters (memberid, txnpwd, name, amount, txnid).',
                ], 400);
            }

            if (!Validators::amount($data->amount ?? 0)) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid transaction amount.',
                ], 400);
            }

            if ($data->amount < 100) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Min amount for transaction is ₹ 100.',
                ], 400);
            }

            // Zenova me txnid hi merchantTxnId ki tarah use hoga
            $merchantTxnId = $data->txnid ?? '';
        }
        
        // Duplicate transaction check
        $transactionModel = new \App\Models\Transaction();
        if ($transactionModel->isMerchantTxnIdExists($merchantTxnId)) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Duplicate error: Merchant Transaction ID already exists.',
            ], 409);
        }
        
        if ($this->user['todays_payin'] > $this->user['payin_limit']) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Your Today\'s payin Limit is exceed.',
            ], 429);
        }

        $provider = (new \App\Models\Provider())->where("id", $this->user['payin_provider'])->first();
        if ($provider['used_limit'] > $provider['daily_limit']) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Pipe Limit is exceed. Please contact to Administrator',
            ], 400);
        }

        // Calculate charge based on slab
        $chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payin($data->amount, $this->user['package'], $this->user['payin_provider']);

        if (!$charge) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Amount error: Slabs are not defined for this amount.',
            ], 400);
        }

        $txnId = $this->generateTxnId();
        $tresult = false;
        $intent = "";
        $referenceId = "";
        $orderId = "";
        $result = null;

        if ($this->user['payin_provider'] == 1) {
            // Solitpay
            $provider = new \App\Libraries\Solitpay();
            $result = $provider->generateQr($txnId, $data->name, $data->phone, $data->email, $data->amount);
            if (isset($result->status) && $result->status == "success") { 
                $tresult = true;
                $intent = $result->data->paymentData->intent;
                $referenceId = $result->data->transaction->txnid;
                $orderId = $result->data->transaction->txnid;
            }
        } 
        elseif ($this->user['payin_provider'] == 3) {
            // Zenova Integration
            $provider = new \App\Libraries\Zenova();
            $result = $provider->generateQr($data->txnid, $data->memberid, $data->txnpwd, $data->name, $data->amount);
            
            if (isset($result->isSuccess) && $result->isSuccess == true && strtolower($result->status) == "success") {
                $tresult = true;
                $intent = $result->qr ?? '';
                $referenceId = $result->txnID ?? $data->txnid;
                $orderId = $result->txnID ?? $data->txnid;
            } else {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Zenova Error: ' . ($result->message ?? 'Unknown error'),
                    'response' => $result
                ], 400);
            }
        }
        
        // Transaction record
        $transaction = new \App\Models\Transaction();
        $transaction->create([
            'user_id' => $this->user['id'],
            'service' => 'payin',
            'txnid' => $txnId,
            'reference_id' => $referenceId,
            'status' => 'Pending',
            'name' => $data->name,
            'email' => $data->email ?? '',
            'phone' => $data->phone ?? '',
            'amount' => number_format($data->amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($data->amount - $charge['charge'] - $charge['charge_gst'], 2, '.', ''),
            'intent' => $intent,
            'order_id' => $orderId,
            'remarks' => $data->notes ?? '',
            'payin_provider' => $this->user['payin_provider'],
            'source' => "api",
            'merchant_txnid' => $merchantTxnId
        ]);

        $tr_response = new \App\Models\Transactionres();
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "request",
            "data" => json_encode($provider->getRequest())
        ]);
        $tr_response->create([
            "txnid" => $txnId, 
            "type" => "creation",
            "data" => json_encode($result)
        ]);

        return $this->respond([
            'status' => 'success',
            'error' => false,
            'message' => 'QR Code Generated Successfully !',
            'data' => [
                "transaction" => [
                "txnStatus" => "Pending",
                "txnid" => $txnId,
                "txnReference" => $merchantTxnId,
                ],
                "commercials" => [
                "amount" => $data->amount,
                "charges" => number_format($charge['charge'], 2, '.', ''),
                "gst" => number_format($charge['charge_gst'], 2, '.', ''),
                ],
                "paymentData" => [
                    "intent" => $intent,
                    "paymentLink" => env("app_baseURL") . "payment/" . bin2hex(openssl_encrypt(
                        $txnId, 
                        'AES-256-CBC', 
                        env("encryption.key"), 
                        OPENSSL_RAW_DATA, 
                        substr(hash('sha256', env("encryption.key")), 0, 16)
                    ))
                ],
                "datetime" => date("Y-m-d H:i:s")
            ]
        ], 200);
    }
    
    public function check_status_old() {
        
        if ($validation = $this->validateHeaders()) {
            return $validation;
        }

        $data = json_decode(request()->getContent());
        if(!isset($data['txnid']) && !isset($data['merchantTxnId'])){
            return $this->respond([
              "status" => "error",
              "error" => true,
              "message" => "Validation error: Please provide either 'txnid' or 'merchantTxnId' to check status."
            ],400);
        }
        
        $transactionModel = new Transaction();
        
        if (isset($data['txnid'])) {
            if(strlen($data['txnid']) < 5) {
                return $this->respond([
                   "status" => "error",
                   "error" => true,
                   "message" => "Invalid TxnId."
                ],400);
            }
            $transaction = $transactionModel->findTxnByTxnid($data['txnid']);
        } else {
            if(strlen($data['merchantTxnId']) < 5) {
                return $this->respond([
                   "status" => "error",
                   "error" => true,
                   "message" => "Invalid MerchantTxnId."
                ],400);
            }
            $transaction = $transactionModel->findTxnByMerchantId($data['merchantTxnId']);
        }
        
        if(!isset($transaction['id']) || $transaction['user_id'] != $this->user['id']){
            return $this->respond([
                   "status" => "error",
                   "error" => true,
                   "message" => "Transaction not Found with provided details"
            ],400);
        }
        
        return $this->respond([
           "status" => "success",
           "error" => false,
           "message" => "Transaction Found !",
           "data" => [
              "transaction" => [
                   "name" => $transaction['name'],
                   "phone" => $transaction['phone'],
                   "txnType" => $transaction['service'],
                   "txnStatus" => $transaction['status'],
                   "txnid" => $transaction['txnid'],
                   "txnReference" => $transaction['merchant_txnid'],
                   "rrn" => $transaction['status'] == "Success"?$transaction['rrn']:"NA"
                ],
                "commercials" => [
                   "amount" => $transaction['amount'],
                   "charges" => $transaction['charges'],
                   "gst" => $transaction['gst'],
                ],
                "payeeDetails" => [
                   "name" => $transaction['status'] == "Success"?$transaction['payee_name']:"NA",
                   "vpa" => $transaction['status'] == "Success"?$transaction['payee_vpa']:"NA",
                ],
                "paymentData" => [
                    "intent" => $transaction['intent'],
                    "paymentLink" => env("app_baseURL")."payment/".bin2hex(openssl_encrypt($transaction['txnid'], 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16)))
                ],
                "datetime" => date("Y-m-d H:i:s")
           ]
        ],200);
        
    }

    public function check_status()
    {
        $data = json_decode(request()->getContent());

        // Step 0: Detect Provider
        if (!empty($data['MemberId']) && !empty($data['TxnPwd'])) {
            // Zenova authentication (no header validation)
            $userModel = new \App\Models\User();
            $this->user = $userModel
                ->where('merchant_id', $data['MemberId'])
                ->where('active', 1)
                ->first();

            if (!$this->user) {
                return $this->respond([
                    "status" => "error",
                    "error" => true,
                    "message" => "Unauthorized: Invalid Zenova credentials (MemberId or TXNPWD)."
                ], 401);
            }

            // Zenova-specific transaction ID
            $txnId = $data['TxnId'] ?? null;
            if (empty($txnId)) {
                return $this->respond([
                    "status" => "error",
                    "error" => true,
                    "message" => "Validation error: Missing TxnId for Zenova check."
                ], 400);
            }

            $transactionModel = new \App\Models\Transaction();
            $transaction = $transactionModel->findTxnByMerchantId($txnId);
        } else {
            // Solitpay or others → validate headers
            if ($validation = $this->validateHeaders()) {
                return $validation;
            }

            if (!isset($data['txnid']) && !isset($data['merchantTxnId'])) {
                return $this->respond([
                    "status" => "error",
                    "error" => true,
                    "message" => "Validation error: Please provide either 'txnid' or 'merchantTxnId' to check status."
                ], 400);
            }

            $transactionModel = new \App\Models\Transaction();

            if (isset($data['txnid'])) {
                if (strlen($data['txnid']) < 5) {
                    return $this->respond([
                        "status" => "error",
                        "error" => true,
                        "message" => "Invalid TxnId."
                    ], 400);
                }
                $transaction = $transactionModel->findTxnByTxnid($data['txnid']);
            } else {
                if (strlen($data['merchantTxnId']) < 5) {
                    return $this->respond([
                        "status" => "error",
                        "error" => true,
                        "message" => "Invalid MerchantTxnId."
                    ], 400);
                }
                $transaction = $transactionModel->findTxnByMerchantId($data['merchantTxnId']);
            }
        }
        
        // Step 1: Validate transaction
        if (!isset($transaction['id']) || $transaction['user_id'] != $this->user['id']) {
            return $this->respond([
                "status" => "error",
                "error" => true,
                "message" => "Transaction not Found with provided details"
            ], 400);
        }

        $remoteStatus = null;

        // Step 2: Provider-wise status check
        if ($this->user['payin_provider'] == 1) {
            // Solitpay Provider
            $solitpay = new \App\Libraries\Solitpay();
            $solitResponse = $solitpay->getStatus([
                "txnid" => $transaction['txnid'],
                "merchantTxnId" => $transaction['merchant_txnid']
            ]);

            if (!empty($solitResponse) && isset($solitResponse->status) && strtolower($solitResponse->status) === "success") {
                $remoteStatus = $solitResponse->data->transaction->txnStatus ?? 'Pending';
            }

        } elseif ($this->user['payin_provider'] == 3) {
            // Zenova Provider
            $zenova = new \App\Libraries\Zenova();
            $zenovaResponse = $zenova->checkStatus($data['TxnId'], $data['MemberId'], $data['TxnPwd']);
            
            if (isset($zenovaResponse->isSuccess) && $zenovaResponse->isSuccess == true) {
                $remoteStatus = $zenovaResponse->message ?? 'Pending';
            } else {
                return $this->respond([
                    "status" => "error",
                    "error" => true,
                    "message" => "Zenova Error: " . ($zenovaResponse->message ?? 'Unknown error'),
                    "response" => $zenovaResponse
                ], 400);
            }
        }
        
        // Step 3: Update if changed
        if ($remoteStatus && $transaction['status'] !== $remoteStatus) {
            
            $transactionModel->where('id', $transaction['id'])->update([
                'status' => $remoteStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $transaction['status'] = $remoteStatus;
        }

        // Step 4: Unified response
        return $this->respond([
            "status" => "success",
            "error" => false,
            "message" => "Transaction Found!",
            "data" => [
                "transaction" => [
                    "name" => $transaction['name'],
                    "phone" => $transaction['phone'],
                    "txnType" => $transaction['service'],
                    "txnStatus" => $transaction['status'],
                    "txnid" => $transaction['txnid'],
                    "txnReference" => $transaction['merchant_txnid'],
                    "rrn" => $transaction['status'] === "Success" ? $transaction['rrn'] : "NA"
                ],
                "commercials" => [
                    "amount" => $transaction['amount'],
                    "charges" => $transaction['charges'],
                    "gst" => $transaction['gst'],
                ],
                "payeeDetails" => [
                    "name" => $transaction['status'] === "Success" ? $transaction['payee_name'] : "NA",
                    "vpa" => $transaction['status'] === "Success" ? $transaction['payee_vpa'] : "NA",
                ],
                "paymentData" => [
                    "intent" => $transaction['intent'],
                    "paymentLink" => env("app_baseURL") . "payment/" . bin2hex(openssl_encrypt(
                        $transaction['txnid'],
                        'AES-256-CBC',
                        env("encryption.key"),
                        OPENSSL_RAW_DATA,
                        substr(hash('sha256', env("encryption.key")), 0, 16)
                    ))
                ],
                "datetime" => date("Y-m-d H:i:s")
            ]
        ], 200);
    }
    
    public function make_payout_old(){
        if ($validation = $this->validateHeaders()) {
            return $validation;
        }

        $data = (object) json_decode(request()->getContent());
        
        $settingsModel = new \App\Models\Settings();
        if(!$settingsModel->isPayoutEnabled()){
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payout Services are disabled. please contact to administrator',
            ],401);
        }
        
        if($this->user['payout'] != 1) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Payout Services are disabled for your account. please contact to administrator',
            ],401);
        }

        // Name validation
        if (!Validators::name($data->name ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Full name is required (first and last).',
            ],400);
        }


        // Phone validation
        if (!Validators::phone($data->phone ?? '')) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid phone number.',
            ],400);
        }

        // Amount validation
        if (!Validators::amount($data->amount ?? 0)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid transaction amount.',
            ],400);
        }

        // Merchant Txn ID validation
        if (strlen($data->merchantTxnId ?? '') < 5) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Merchant Transaction ID is too short.',
            ],400);
        }

        // Notes length validation
        if (strlen($data->notes ?? '') > 100) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Notes exceed maximum length of 100 characters.',
            ],400);
        }
        
        
        //validate account number
        if (!Validators::accountNumber($data->account_number ?? 0)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid Account Number.',
            ],400);
        }
        
        if (!Validators::ifsc($data->ifsc_code ?? 0)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Invalid IFSC Code.',
            ],400);
        }

         $bankName = "";

        try {
            $resp = @json_decode(file_get_contents("https://ifsc.razorpay.com/" . $data->ifsc_code), true);
            if (!$resp || !isset($resp['BANK'])) {
                throw new Exception("Invalid IFSC");
            }
            $bankName = $resp['BANK'];
        } catch (Exception $e) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'IFSC error: Invalid IFSC code provided',
            ], 400);
        }

        // Duplicate transaction check
        $transactionModel = new \App\Models\Transaction();
        if ($transactionModel->isMerchantTxnIdExists($data->merchantTxnId)) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Duplicate error: Merchant Transaction ID already exists.',
            ],409);
        }
        
        if($data->amount > $this->user['payout_per_limit']){
             return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Transaction failed. Reason: Exceeds per-transaction limit of ₹'.$this->user['payout_per_limit'],
            ],400);
        }
        
        
        if($this->user['todays_payout'] > $this->user['payout_limit']) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Your Today\'s payout Limit is exceed.',
            ],429);
        }

        // Calculate charge based on slab
        $chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payout($data->amount, $this->user['package'], $this->user['payout_provider']);

        if (!$charge) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Amount error: Slabs are not defined for this amount.',
            ],400);
        }
        
        $netAmount = $data->amount + $charge['charge'] + $charge['charge_gst'];
        
        if($this->user['wallet'] < $netAmount) {
            return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Insufficient Funds in your wallet',
            ],400);
        }
        
        //add transaction
        $txnId = $this->generateTxnId();
        $transaction = new \App\Models\Transaction();
        $transaction->create([
            'user_id' => $this->user['id'],
            'service' => 'payout',
            'txnid' => $txnId,
            'reference_id' => '',
            'status' => 'Pending',
            'name' => $data->name,
            'phone' => $data->phone,
            'account_number' => $data->account_number,
            'ifsc_code' => $data->ifsc_code,
            'bank_name' => $bankName,
            'amount' => number_format($data->amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($netAmount, 2, '.', ''),
            'remarks' => $data->notes ?? '',
            'payout_provider' => $this->user['payout_provider'],
            'source' => 'api',
            'merchant_txnid' => $data->merchantTxnId
        ]);
        
        
        return $this->respond([
            'status' => 'success',
            'error' => false,
            "message" => "Transaction initialized successfully!",
            'data' => [
                "transaction" => [
                  "txnStatus" => "Pending",
                  "txnid" => $txnId,
                  "txnReference" => $data->merchantTxnId,
                ],
                "commercials" => [
                   "amount" => $data->amount,
                   "charges" => number_format($charge['charge'], 2, '.', ''),
                   "gst" => number_format($charge['charge_gst'], 2, '.', ''),
                ]
                
            ]
        ],200);
        
        
    }

    public function make_payout()
    { 
        $data = (object) json_decode(request()->getContent());
        
        // Zenova (provider = 3) validation via memberid and txnpwd
        if (($data->memberid ?? null) && ($data->txnpwd ?? null)) {
            
            $userModel = new \App\Models\User();
            $this->user = $userModel
                ->where('merchant_id', $data->memberid)
                ->where('active', 1)
                ->first();

            if (!$this->user) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Unauthorized: Invalid Zenova credentials (memberid or txnpwd).',
                ], 401);
            }
        } else {
            // Solitpay header validation
            if ($validation = $this->validateHeaders()) {
                return $validation;
            }
        }

        $settingsModel = new \App\Models\Settings();
        if (!$settingsModel->isPayoutEnabled()) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Payout Services are disabled. please contact administrator',
            ], 401);
        }

        if ($this->user['payout'] != 1) {
            return $this->respond([
                'status' => 'error',
                'error' => true,
                'message' => 'Payout Services are disabled for your account. please contact administrator',
            ], 401);
        }
       
        // Zenova Provider (ID = 3)
        if ($this->user['payout_provider'] == 4) {
            if (empty($data->memberid) || empty($data->txnpwd) || empty($data->mobile) || empty($data->account_holder_name) || empty($data->account_no) || empty($data->ifsc) || empty($data->amount) || empty($data->txnID)) {
                return $this->respond([
                    'status' => 'error',
                    'error' => true,
                    'message' => 'Validation error: Missing required Zenova parameters (memberid, txnpwd, mobile, account_holder_name, account_no, ifsc, amount, txnID).',
                ], 400);
            }
        } 
        else {
            // Solitpay Provider (ID = 2)
            if (!Validators::name($data->name ?? '')) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Full name is required.'],400);
            }

            if (!Validators::phone($data->phone ?? '')) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Invalid phone number.'],400);
            }

            if (!Validators::amount($data->amount ?? 0)) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Invalid transaction amount.'],400);
            }

            if (strlen($data->merchantTxnId ?? '') < 5) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Merchant Transaction ID too short.'],400);
            }

            if (!Validators::accountNumber($data->account_number ?? '')) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Invalid Account Number.'],400);
            }

            if (!Validators::ifsc($data->ifsc_code ?? '')) {
                return $this->respond(['status' => 'error','error' => true,'message' => 'Validation error: Invalid IFSC Code.'],400);
            }
        }

        // Fetch Bank Name (same for both providers)
        try {
            $ifscCode = $this->user['payout_provider'] == 4 ? $data->ifsc : $data->ifsc_code;
            $resp = @json_decode(file_get_contents("https://ifsc.razorpay.com/" . $ifscCode), true);
            if (!$resp || !isset($resp['BANK'])) {
                throw new \Exception("Invalid IFSC");
            }
            $bankName = $resp['BANK'];
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'IFSC error: Invalid IFSC code.'],400);
        }

        // Check duplicate Txn ID
        $transactionModel = new \App\Models\Transaction();
        $merchantTxnId = $this->user['payout_provider'] == 4 ? $data->txnID : $data->merchantTxnId;
        if ($transactionModel->isMerchantTxnIdExists($merchantTxnId)) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'Duplicate error: Merchant Transaction ID already exists.'],409);
        }

        // Balance and Limit checks
        if ($data->amount > $this->user['payout_per_limit']) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'Exceeds per-transaction limit ₹'.$this->user['payout_per_limit']],400);
        }

        if ($this->user['todays_payout'] > $this->user['payout_limit']) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'Today\'s payout limit exceeded.'],429);
        }

        // Calculate Charge
        $chargeModel = new \App\Models\Charge();
        $charge = $chargeModel->payout($data->amount, $this->user['package'], $this->user['payout_provider']);
        if (!$charge) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'Amount error: Slabs not defined.'],400);
        }

        $netAmount = $data->amount + $charge['charge'] + $charge['charge_gst'];
        if ($this->user['wallet'] < $netAmount) {
            return $this->respond(['status' => 'error','error' => true,'message' => 'Insufficient wallet balance.'],400);
        }

        $txnId = $this->generateTxnId();
        $status = "Pending";
        $utr = "";
        $referenceId = "";


        
        $transaction = new \App\Models\Transaction();
        $transaction->create([
            'user_id' => $this->user['id'],
            'service' => 'payout',
            'txnid' => $txnId,
            'reference_id' => '',
            'status' => 'Pending',
            'name' => $this->user['payout_provider'] == 4 ? ($data->account_holder_name ?? $data->name ?? '') : ($data->name ?? ''),
            'phone' => $this->user['payout_provider'] == 4 ? ($data->mobile ?? $data->phone ?? '') : ($data->phone ?? ''),
            'account_number' => $this->user['payout_provider'] == 4 ? ($data->account_no ?? $data->account_number ?? '') : ($data->account_number ?? ''),
            'ifsc_code' => $this->user['payout_provider'] == 4 ? ($data->ifsc ?? $data->ifsc_code ?? '') : ($data->ifsc_code ?? ''),
            'bank_name' => $bankName,
            'amount' => number_format($data->amount, 2, '.', ''),
            'charges' => number_format($charge['charge'], 2, '.', ''),
            'gst' => number_format($charge['charge_gst'], 2, '.', ''),
            'net_amount' => number_format($netAmount, 2, '.', ''),
            'remarks' => $data->notes ?? '',
            'payout_provider' => $this->user['payout_provider'],
            'source' => 'api',
            'merchant_txnid' => $merchantTxnId
        ]);

        return $this->respond([
            'status' => 'success',
            'error' => false,
            'message' => 'Payout request initialized successfully!',
            'data' => [
                "transaction" => [
                    "txnStatus" => "Pending",
                    "txnid" => $txnId,
                    "txnReference" => $merchantTxnId,
                    "utr" => "NA"
                ],
                "commercials" => [
                    "amount" => $data->amount,
                    "charges" => number_format($charge['charge'], 2, '.', ''),
                    "gst" => number_format($charge['charge_gst'], 2, '.', '')
                ]
            ]
        ], 200);
    }


    private function encrypt($data, $key, $iv)
    {
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }

    private function decrypt($data, $key, $iv)
    {
        $decoded = base64_decode($data);
        return $decoded !== false ? openssl_decrypt($decoded, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv) : false;
    }

    private function respond_old($response,$statusCode = 200) {
        $timeTaken = round(microtime(true) - $this->startTime, 4);
        $udata = [ "response" => json_encode($response), "time_taken" => $timeTaken ];
        if(isset($response['data']['transaction']['txnid'])) {
            $udata['txnid'] = $response['data']['transaction']['txnid'];
        }
        $this->apiRequestModel->update($this->apiRequestId, $udata);
        return $this->response->setStatusCode($statusCode)->setJSON($response);
    }

    private function respond($response, $statusCode = 200) {
        $timeTaken = round(microtime(true) - $this->startTime, 4);
        $udata = [ "response" => json_encode($response), "time_taken" => $timeTaken ];

        if (isset($response['data']['transaction']['txnid'])) {
            $udata['txnid'] = $response['data']['transaction']['txnid'];
        }

        // Check before calling update
        if (isset($this->apiRequestModel) && isset($this->apiRequestId)) {
            $this->apiRequestModel->update($this->apiRequestId, $udata);
        }

        return $this->response->setStatusCode($statusCode)->setJSON($response);
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
}
