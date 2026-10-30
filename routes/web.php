<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Home;
use App\Http\Controllers\Tables;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Api;
use App\Http\Controllers\Callback;

Route::get('/', [Home::class, 'index'])->name('home');

//AUTH
Route::get('/user/login', [Auth::class, 'login'])->name('login');
Route::get('/user/register', [Auth::class, 'register'])->name('register');
Route::get('/user/dashboard', [Home::class, 'dashboard'])->name('dashboard');
Route::post('/api/user/register',[Auth::class, 'register_user'])->name('api.user.register');
Route::get('/user/verify-registration',[Auth::class, 'verify_registration'])->name('verify-registration');
Route::post('/api/user/register/send-otp',[Auth::class, 'send_registration_otp'])->name('api.user.register.send-otp');
Route::post('/api/user/verify-registration',[Auth::class, 'register_user_verify'])->name('api.user.register.verify');
Route::post('/api/user/login',[Auth::class, 'login_user'])->name('api.user.login');
Route::get('user/login-verification',[Auth::class, 'verify_login'])->name('verify-login');
Route::post('/api/user/login/verification',[Auth::class, 'verify_login_otp'])->name('api.user.login.verification');
Route::get('/user/forgot-password',[Auth::class, 'forgot_password'])->name('forgot-password');
Route::get('/user/logout', [Auth::class, 'logout'])->name('logout');
Route::post('/api/auth/send-reset-password-link',[Auth::class, 'send_reset_password_link'])->name('api.auth.send-reset-password-link');
Route::get('/auth/reset/password/{any}',[Auth::class, 'reset_password'])->name('auth.reset-password');
Route::post('/api/auth/reset-password/update',[Auth::class, 'update_password'])->name('api.auth.reset-password.update');
Route::get('/auth/login/token',[Auth::class, 'ad_login'])->name('auth.login.token');

//DASHBOARD
Route::get('/user/profile',[Home::class, 'profile'])->name('profile');
Route::get('/service/collection/upi',[Home::class, 'upi_collection'])->name('service.collection.upi');
Route::get('/service/collection/transactions',[Home::class, 'payin_transactions'])->name('service.collection.transactions');
Route::get('/service/collection/settlements',[Home::class, 'settlements'])->name('service.collection.settlements');
Route::get('/service/payout/create',[Home::class, 'payout_create'])->name('service.payout.create');
Route::get('/service/payout/bulk-payout',[Home::class, 'bulk_payout_create'])->name('service.payout.bulk-payout');
Route::get('/service/payout/transactions',[Home::class, 'payout_transactions'])->name('service.payout.transactions');
Route::get('/wallet/ledger',[Home::class, 'wallet_ledger'])->name('wallet.ledger');
Route::get('/wallet/topup',[Home::class, 'wallet_topup'])->name('wallet.topup');
Route::get('/wallet/topup/add-request',[Home::class, 'add_fund_request'])->name('wallet.topup.add-request');
Route::get('/hold-wallet/ledger',[Home::class, 'hold_wallet_ledger'])->name('hold-wallet.ledger');
Route::post('/api/wallet/add-topup-request',[Home::class, 'add_topup_request'])->name('api.wallet.add-topup-request');
Route::get('/api/tables/fund_requests',[Tables::class, 'fund_requests'])->name('api.tables.fund-requests');
Route::get('/api/tables/wallet-transaction',[Tables::class, 'wallet_transactions'])->name('api.tables.wallet-transaction');
Route::get('/api/tables/hold-wallet-transaction',[Tables::class, 'hold_wallet_transactions'])->name('api.tables.hold-wallet-transaction');
Route::get('/settings/api-keys',[Home::class, 'api_keys'])->name('settings.api-keys');
Route::get('/settings/webhook',[Home::class, 'webhook'])->name('settings.webhook');
Route::get('/settings/whitelisted-ip',[Home::class, 'whitelisted_ip'])->name('settings.whitelisted-ip');
Route::get('/api/tables/payin_transactions',[Tables::class, 'payin_transactions'])->name('api.tables.payin-transactions');
Route::get('/api/tables/payout_transactions',[Tables::class, 'payout_transactions'])->name('api.tables.payout-transactions');
Route::get('/api/tables/settlements',[Tables::class, 'settlements'])->name('api.tables.settlements');
Route::post('/api/upi/generate',[Home::class, 'generate_upi'])->name('api.upi.generate');
Route::post('/api/transaction/status',[Home::class, 'transaction_status'])->name('api.transaction.status');
Route::get('/api/dashboard/analytics',[Home::class, 'get_dashboard_data'])->name('api.dashboard.analytics');
Route::post('/api/payout/make',[Home::class, 'make_payout'])->name('api.payout.make');
Route::get('/payment/{any}',[Payment::class, 'link'])->name('payment.link');
Route::post('/api/transactions/check-status',[Payment::class, 'status'])->name('api.transactions.check-status');
Route::get('/api/regenerate-api-key/send-otp',[Home::class, 'regenerate_api_key_send_otp'])->name('api.regenerate-api-key.send-otp');
Route::post('/api/regenerate-api-key/verify-otp',[Home::class, 'verify_otp_and_update_api_key'])->name('api.regenerate-api-key.verify-otp');
Route::match(['get', 'post'], '/api/update-webhook/payin', [Home::class, 'update_webhook_send_otp'])->defaults('type', 'payin')->name('api.update-webhook.payin');
Route::match(['get', 'post'], '/api/update-webhook/payout', [Home::class, 'update_webhook_send_otp'])->defaults('type', 'payout')->name('api.update-webhook.payout');
Route::match(['get', 'post'], '/api/update-webhook/{type}', [Home::class, 'update_webhook_send_otp'])->name('api.update-webhook');
Route::match(['get', 'post'], '/api/update-webhook-verify', [Home::class, 'verify_otp_and_update_webhook_url'])->name('api.update-webhook.verify');
Route::post('/api/whitelist-ip',[Home::class, 'whitelist_ip_send_otp'])->name('api.whitelist-ip');
Route::post('/api/update-ip-verify',[Home::class, 'verify_otp_and_add_ip'])->name('api.update-ip-verify');
Route::post('/api/delete/ip',[Home::class, 'delete_ip_send_otp'])->name('api.delete.ip');
Route::post('/api/delete-ip-verify',[Home::class, 'verify_otp_and_delete_ip'])->name('api.delete-ip-verify');
Route::get('/user/change_password',[Home::class, 'change_password'])->name('user.change-password');
Route::post('/api/user/change_password',[Home::class, 'update_password'])->name('api.user.change-password');
Route::post('/api/transaction/info',[Home::class, 'transaction_info'])->name('api.transaction.info');
Route::get('/service/collection/settlements/{any}',[Home::class, 'settlement_transactions'])->name('service.collection.settlement.transactions');
Route::get('/api/user/login/history',[Home::class, 'get_login_history'])->name('api.user.login.history');
Route::get('/reports/download',[Home::class, 'reports_download'])->name('reports.download');
Route::get('/api/export/report/download',[Home::class, 'download'])->name('api.export.report.download');
Route::get('/api/export/wallet-ledger',[Home::class, 'export_wallet_ledger'])->name('api.export.wallet-ledger');
Route::post('/api/admin/chargeback/actions/add',[Home::class, 'add_chargeback_action'])->name('api.admin.chargeback.actions.add');
Route::post('/api/admin/user/profile/update-bank',[Home::class, 'update_bank_details'])->name('api.admin.user.profile.update-bank');
Route::get('/api-docs',[Home::class, 'api_docs'])->name('api-docs');

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




