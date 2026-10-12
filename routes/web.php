<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Home;
use App\Http\Controllers\Tables;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Api;
use App\Http\Controllers\Settlement;
use App\Http\Controllers\Cron;
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
Route::post('/api/update-webhook/{any}',[Home::class, 'update_webhook_send_otp']);
Route::post('/api/update-webhook-verify',[Home::class, 'verify_otp_and_update_webhook_url']);
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
Route::post('/api/admin/chargeback/actions/add',[Home::class, 'add_chargeback_action']);
Route::post('/api/admin/user/profile/update-bank',[Home::class, 'update_bank_details']);
Route::get('/api-docs',[Home::class, 'api_docs']);

//API
Route::get('/api/v1/account/check-balance',[Api::class, 'check_balance']);
Route::post('/api/v1/upi/orders/create',[Api::class, 'generate_qr']);
Route::post('/api/v1/orders/check-status',[Api::class, 'check_status']);
Route::post('/api/v1/upi/payout/initiate',[Api::class, 'make_payout']);

//INTERNAL 
Route::get('/api/v1/settlements/internal/settlements-t-00-02',[Settlement::class, 't00_02']);
Route::get('/api/v1/settlements/internal/settlements-t-07-02',[Settlement::class, 't07_02']);
Route::get('/api/v1/settlements/internal/settlements-t-13-02',[Settlement::class, 't13_02']);
Route::get('/api/v1/settlements/internal/settlements-t-19-02',[Settlement::class, 't19_02']);
Route::get('/api/v1/operations/internal/cron',[Cron::class, 'index']);

//CALLBACK
Route::get('/api/v1/cb/internal/payin/solitpay/webhook',[Callback::class, 'solitpay_payin']);
Route::get('/api/v1/cb/internal/payout/solitpay/webhook',[Callback::class, 'solitpay_payout']);

Route::get('/api/v1/cb/internal/payin/zenova/webhook',[Callback::class, 'zenova_payin']);
Route::get('/api/v1/cb/internal/payout/zenova/webhook',[Callback::class, 'zenova_payout']);

Route::get('generate-token', [Api::class, 'testToken']);
