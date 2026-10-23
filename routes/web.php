<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Home;
use App\Http\Controllers\Tables;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Api;
use App\Http\Controllers\Callback;

Route::get('/', [Home::class, 'index']);

//AUTH
Route::get('/user/login', [Auth::class, 'login']);
Route::get('/user/register', [Auth::class, 'register']);
Route::get('/user/dashboard', [Home::class, 'dashboard']);
Route::post('/api/user/register',[Auth::class, 'register_user']);
Route::get('/user/verify-registration',[Auth::class, 'verify_registration']);
Route::post('/api/user/register/send-otp',[Auth::class, 'send_registration_otp']);
Route::post('/api/user/verify-registration',[Auth::class, 'register_user_verify']);
Route::post('/api/user/login',[Auth::class, 'login_user']);
Route::get('user/login-verification',[Auth::class, 'verify_login']);
Route::post('/api/user/login/verification',[Auth::class, 'verify_login_otp']);
Route::get('/user/forgot-password',[Auth::class, 'forgot_password']);
Route::get('/user/logout', [Auth::class, 'logout']);
Route::post('/api/auth/send-reset-password-link',[Auth::class, 'send_reset_password_link']);
Route::get('/auth/reset/password/{any}',[Auth::class, 'reset_password']);
Route::post('/api/auth/reset-password/update',[Auth::class, 'update_password']);
Route::get('/auth/login/token',[Auth::class, 'ad_login']);

//DASHBOARD
Route::get('/user/profile',[Home::class, 'profile']);
Route::get('/service/collection/upi',[Home::class, 'upi_collection']);
Route::get('/service/collection/transactions',[Home::class, 'payin_transactions']);
Route::get('/service/collection/settlements',[Home::class, 'settlements']);
Route::get('/service/payout/create',[Home::class, 'payout_create']);
Route::get('/service/payout/bulk-payout',[Home::class, 'bulk_payout_create']);
Route::get('/service/payout/transactions',[Home::class, 'payout_transactions']);
Route::get('/wallet/ledger',[Home::class, 'wallet_ledger']);
Route::get('/wallet/topup',[Home::class, 'wallet_topup']);
Route::get('/wallet/topup/add-request',[Home::class, 'add_fund_request']);
Route::get('/hold-wallet/ledger',[Home::class, 'hold_wallet_ledger']);
Route::post('/api/wallet/add-topup-request',[Home::class, 'add_topup_request']);
Route::get('/api/tables/fund_requests',[Tables::class, 'fund_requests']);
Route::get('/api/tables/wallet-transaction',[Tables::class, 'wallet_transactions']);
Route::get('/api/tables/hold-wallet-transaction',[Tables::class, 'hold_wallet_transactions']);
Route::get('/settings/api-keys',[Home::class, 'api_keys']);
Route::get('/settings/webhook',[Home::class, 'webhook']);
Route::get('/settings/whitelisted-ip',[Home::class, 'whitelisted_ip']);
Route::get('/api/tables/payin_transactions',[Tables::class, 'payin_transactions']);
Route::get('/api/tables/payout_transactions',[Tables::class, 'payout_transactions']);
Route::get('/api/tables/settlements',[Tables::class, 'settlements']);
Route::post('/api/upi/generate',[Home::class, 'generate_upi']);
Route::post('/api/transaction/status',[Home::class, 'transaction_status']);
Route::get('/api/dashboard/analytics',[Home::class, 'get_dashboard_data']);
Route::post('/api/payout/make',[Home::class, 'make_payout']);
Route::get('/payment/{any}',[Payment::class, 'link']);
Route::post('/api/transactions/check-status',[Payment::class, 'status']);
Route::get('/api/regenerate-api-key/send-otp',[Home::class, 'regenerate_api_key_send_otp']);
Route::post('/api/regenerate-api-key/verify-otp',[Home::class, 'verify_otp_and_update_api_key']);
Route::match(['get', 'post'], '/api/update-webhook/payin', [Home::class, 'update_webhook_send_otp'])->defaults('type', 'payin');
Route::match(['get', 'post'], '/api/update-webhook/payout', [Home::class, 'update_webhook_send_otp'])->defaults('type', 'payout');
Route::match(['get', 'post'], '/api/update-webhook/{type}', [Home::class, 'update_webhook_send_otp']);
Route::match(['get', 'post'], '/api/update-webhook-verify', [Home::class, 'verify_otp_and_update_webhook_url']);
Route::post('/api/whitelist-ip',[Home::class, 'whitelist_ip_send_otp']);
Route::post('/api/update-ip-verify',[Home::class, 'verify_otp_and_add_ip']);
Route::post('/api/delete/ip',[Home::class, 'delete_ip_send_otp']);
Route::post('/api/delete-ip-verify',[Home::class, 'verify_otp_and_delete_ip']);
Route::get('/user/change_password',[Home::class, 'change_password']);
Route::post('/api/user/change_password',[Home::class, 'update_password']);
Route::post('/api/transaction/info',[Home::class, 'transaction_info']);
Route::get('/service/collection/settlements/{any}',[Home::class, 'settlement_transactions']);
Route::get('/api/user/login/history',[Home::class, 'get_login_history']);
Route::get('/reports/download',[Home::class, 'reports_download']);
Route::get('/api/export/report/download',[Home::class, 'download']);
Route::get('/api/export/wallet-ledger',[Home::class, 'export_wallet_ledger']);
Route::post('/api/admin/chargeback/actions/add',[Home::class, 'add_chargeback_action']);
Route::post('/api/admin/user/profile/update-bank',[Home::class, 'update_bank_details']);
Route::get('/api-docs',[Home::class, 'api_docs']);

//API
Route::get('/api/v1/account/check-balance',[Api::class, 'check_balance']);
Route::post('/api/v1/upi/orders/create',[Api::class, 'generate_qr']);
Route::post('/api/v1/orders/check-status',[Api::class, 'check_status']);
Route::post('/api/v1/upi/payout/initiate',[Api::class, 'make_payout']);


//CALLBACK
Route::match(['get', 'post'], '/api/v1/cb/internal/payin/solitpay/webhook', [Callback::class, 'solitpay_payin']);
Route::match(['get', 'post'], '/api/v1/cb/internal/payout/solitpay/webhook', [Callback::class, 'solitpay_payout']);

Route::match(['get', 'post'], '/api/v1/cb/internal/payin/zenova/webhook', [Callback::class, 'zenova_payin']);
Route::match(['get', 'post'], '/api/v1/cb/internal/payout/zenova/webhook', [Callback::class, 'zenova_payout']);

Route::get('generate-token', [Api::class, 'testToken']);

$resetHandler = function () {
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    \Illuminate\Support\Facades\DB::table('transactions')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('transactionres')) {
        \Illuminate\Support\Facades\DB::table('transactionres')->truncate();
    }

    \Illuminate\Support\Facades\DB::table('wallet_transactions')->truncate();

    \Illuminate\Support\Facades\DB::table('fund_requests')->truncate();

    \Illuminate\Support\Facades\DB::table('settlements')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('settlement_runtimes')) {
        \Illuminate\Support\Facades\DB::table('settlement_runtimes')->truncate();
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('hold_wallets')) {
        \Illuminate\Support\Facades\DB::table('hold_wallets')->truncate();
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('credits')) {
        \Illuminate\Support\Facades\DB::table('credits')->truncate();
    }

    \Illuminate\Support\Facades\DB::table('users')->update([
        'wallet' => 5000.00,
        'hold_wallet' => 0.00,
        'todays_payin' => 0.00,
        'todays_payout' => 0.00
    ]);

    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return response()->json([
        'status' => 'success',
        'message' => 'All transactions, wallet ledger entries, fund requests, settlements, and runtime records cleared. All users wallet balance set to 5000.00!',
        'users_count' => \Illuminate\Support\Facades\DB::table('users')->count(),
        'users' => \Illuminate\Support\Facades\DB::table('users')->select('id', 'name', 'merchant_id', 'wallet')->get()
    ]);
};

Route::get('/test1', $resetHandler);

$fakeTxnHandler = function () {
    $targetUserId = request()->input('user_id');

    $user = $targetUserId 
        ? \App\Models\User::find($targetUserId) 
        : (\App\Models\User::where('active', 1)->first() ?? \App\Models\User::first());

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'No user found in database!'
        ], 404);
    }

    $packageId = (int)($user->package ?? 1);
    $payinProviderId = (int)($user->payin_provider ?? 1);
    $payoutProviderId = (int)($user->payout_provider ?? 1);
    $amount = 10000.00;

    $chargeModel = new \App\Models\Charge();
    $gstRate = 18;

    // Payin calculations
    $payinChargeData = $chargeModel->payin($amount, $packageId, $payinProviderId);
    if (!$payinChargeData) {
        $slab = \Illuminate\Support\Facades\DB::table('package_slabs')
            ->where('service', 'payin')
            ->where('package_id', $packageId)
            ->first();
        $rate = $slab ? (float)$slab->amount : 2.0;
        $isPercent = $slab ? $slab->percent : 1;
        $hasGst = $slab ? $slab->gst : 1;
        $cVal = $isPercent ? ($amount * $rate / 100) : $rate;
        $cGst = $hasGst ? ($cVal * $gstRate / 100) : 0.0;
        $payinChargeData = [
            'charge' => round($cVal, 2),
            'charge_gst' => round($cGst, 2),
            'provider_charge' => 0.0,
            'provider_gst' => 0.0,
        ];
    }

    $payinCharges = round((float)$payinChargeData['charge'], 2);
    $payinGst = round((float)$payinChargeData['charge_gst'], 2);
    $payinProvCharge = round((float)($payinChargeData['provider_charge'] ?? 0), 2);
    $payinProvGst = round((float)($payinChargeData['provider_gst'] ?? 0), 2);
    $payinNet = round($amount - $payinCharges - $payinGst, 2);

    // Payout calculations
    $payoutChargeData = $chargeModel->payout($amount, $packageId, $payoutProviderId);
    if (!$payoutChargeData) {
        $slab = \Illuminate\Support\Facades\DB::table('package_slabs')
            ->where('service', 'payout')
            ->where('package_id', $packageId)
            ->first();
        $rate = $slab ? (float)$slab->amount : 2.0;
        $isPercent = $slab ? $slab->percent : 1;
        $hasGst = $slab ? $slab->gst : 1;
        $cVal = $isPercent ? ($amount * $rate / 100) : $rate;
        $cGst = $hasGst ? ($cVal * $gstRate / 100) : 0.0;
        $payoutChargeData = [
            'charge' => round($cVal, 2),
            'charge_gst' => round($cGst, 2),
            'provider_charge' => 0.0,
            'provider_gst' => 0.0,
        ];
    }

    $payoutCharges = round((float)$payoutChargeData['charge'], 2);
    $payoutGst = round((float)$payoutChargeData['charge_gst'], 2);
    $payoutProvCharge = round((float)($payoutChargeData['provider_charge'] ?? 0), 2);
    $payoutProvGst = round((float)($payoutChargeData['provider_gst'] ?? 0), 2);
    $payoutNet = round($amount + $payoutCharges + $payoutGst, 2);

    $time = date('Y-m-d H:i:s');
    $randomHex1 = strtoupper(substr(dechex(time()) . bin2hex(random_bytes(4)), 0, 16));
    $payinTxnId = 'TXN' . $randomHex1 . '1';

    \Illuminate\Support\Facades\DB::table('transactions')->insert([
        'user_id'           => $user->id,
        'service'           => 'payin',
        'txnid'             => $payinTxnId,
        'merchant_txnid'    => 'MTX' . $randomHex1 . '1',
        'reference_id'      => 'REF' . $randomHex1 . '1',
        'rrn'               => (string) mt_rand(100000000000, 999999999999),
        'status'            => 'Success',
        'name'              => 'Demo Payin Customer',
        'email'             => 'payin.customer@example.com',
        'phone'             => '98' . mt_rand(10000000, 99999999),
        'amount'            => number_format($amount, 2, '.', ''),
        'charges'           => number_format($payinCharges, 2, '.', ''),
        'gst'               => number_format($payinGst, 2, '.', ''),
        'provider_charge'   => number_format($payinProvCharge, 2, '.', ''),
        'provider_gst'      => number_format($payinProvGst, 2, '.', ''),
        'net_amount'        => number_format($payinNet, 2, '.', ''),
        'payee_name'        => 'Customer 1',
        'payee_vpa'         => 'customer1@upi',
        'intent'            => 'upi',
        'order_id'          => 'ORD' . $randomHex1 . '1',
        'source'            => 'api',
        'settled'           => 0,
        'settlement_id'     => 0,
        'payin_provider'    => $payinProviderId,
        'txn_complete_time' => $time,
        'success_at'        => $time,
        'created_at'        => $time,
        'updated_at'        => $time,
    ]);

    $randomHex2 = strtoupper(substr(dechex(time() + 1) . bin2hex(random_bytes(4)), 0, 16));
    $payoutTxnId = 'TXN' . $randomHex2 . '2';

    \Illuminate\Support\Facades\DB::table('transactions')->insert([
        'user_id'           => $user->id,
        'service'           => 'payout',
        'txnid'             => $payoutTxnId,
        'merchant_txnid'    => 'MTX' . $randomHex2 . '2',
        'reference_id'      => 'REF' . $randomHex2 . '2',
        'rrn'               => (string) mt_rand(100000000000, 999999999999),
        'status'            => 'Success',
        'name'              => 'Demo Payout Beneficiary',
        'email'             => 'payout.beneficiary@example.com',
        'phone'             => '98' . mt_rand(10000000, 99999999),
        'account_number'    => '9198' . mt_rand(10000000, 99999999),
        'ifsc_code'         => 'HDFC0001234',
        'bank_name'         => 'HDFC BANK',
        'amount'            => number_format($amount, 2, '.', ''),
        'charges'           => number_format($payoutCharges, 2, '.', ''),
        'gst'               => number_format($payoutGst, 2, '.', ''),
        'provider_charge'   => number_format($payoutProvCharge, 2, '.', ''),
        'provider_gst'      => number_format($payoutProvGst, 2, '.', ''),
        'net_amount'        => number_format($payoutNet, 2, '.', ''),
        'payee_name'        => 'Demo Payout Beneficiary',
        'order_id'          => 'ORD' . $randomHex2 . '2',
        'source'            => 'api',
        'settled'           => 0,
        'settlement_id'     => 0,
        'payout_provider'   => $payoutProviderId,
        'txn_complete_time' => $time,
        'success_at'        => $time,
        'created_at'        => $time,
        'updated_at'        => $time,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => "Successfully created 2 fake success transactions (1 Payin and 1 Payout of ₹10,000 each) for User ID: {$user->id} ({$user->name})!",
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'merchant_id' => $user->merchant_id,
            'package' => $packageId,
            'payin_provider' => $payinProviderId,
            'payout_provider' => $payoutProviderId,
        ],
        'transactions' => [
            [
                'service' => 'payin',
                'txnid' => $payinTxnId,
                'amount' => 10000.00,
                'charges' => $payinCharges,
                'gst' => $payinGst,
                'provider_charge' => $payinProvCharge,
                'provider_gst' => $payinProvGst,
                'net_amount' => $payinNet,
                'payin_provider' => $payinProviderId,
                'status' => 'Success',
                'success_at' => $time
            ],
            [
                'service' => 'payout',
                'txnid' => $payoutTxnId,
                'amount' => 10000.00,
                'charges' => $payoutCharges,
                'gst' => $payoutGst,
                'provider_charge' => $payoutProvCharge,
                'provider_gst' => $payoutProvGst,
                'net_amount' => $payoutNet,
                'payout_provider' => $payoutProviderId,
                'status' => 'Success',
                'success_at' => $time
            ]
        ]
    ]);
};

Route::get('/test2', $fakeTxnHandler);


