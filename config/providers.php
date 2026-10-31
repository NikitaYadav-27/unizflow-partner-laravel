<?php

return [

    

    'solitpay' => [
        'base_url' => env('SOLITPAY_BASE_URL', 'https://partner.solitpay.com/'),
        'merchant_id' => env('SOLITPAY_MERCHANT_ID', 'RD7E01BCA1'),
        'client_secret' => env('SOLITPAY_CLIENT_SECRET', '6fcbdd4708c4450f6e6b7a8e18c43ffcfc2e10546e14f2430f7eeecbc66d'),
        'enc_iv' => env('SOLITPAY_ENC_IV', '4gcO6dyLtfMZwfQT'),
        'enc_key' => env('SOLITPAY_ENC_KEY', 'e39574fc26cc0a88bc9ad307a59466d8ba1e082ed07f258e2ee87fc046719fb3'),
    ],

    'zenova' => [
        'base_url' => env('ZENOVA_BASE_URL', 'https://liveapi.zenovaservices.com/api/'),
        'member_id' => env('ZENOVA_MEMBER_ID', 'PTN16539017'),
        'txn_pwd' => env('ZENOVA_TXN_PWD', '12355'),
    ],

    'pikelpoint' => [
        'base_url' => env('PIKELPOINT_BASE_URL', 'https://api.pikelpoint.com'),
        'payin_url' => env('PIKELPOINT_PAYIN_URL', 'https://api.pikelpoint.com'),
        'payout_url' => env('PIKELPOINT_PAYOUT_URL', 'https://api.pikelpoint.com'),
        'token' => env('PIKELPOINT_TOKEN', ''),
        'webhook_secret' => env('PIKELPOINT_WEBHOOK_SECRET', ''),
        'payin_webhook_secret' => env('PIKELPOINT_PAYIN_WEBHOOK_SECRET', env('PIKELPOINT_WEBHOOK_SECRET', '')),
        'payout_webhook_secret' => env('PIKELPOINT_PAYOUT_WEBHOOK_SECRET', env('PIKELPOINT_WEBHOOK_SECRET', '')),
    ],

];
