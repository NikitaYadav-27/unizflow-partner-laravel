<?php

namespace App\Libraries;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Pikelpoint
{
    private $base_url;
    private $token;
    private $webhook_secret;
    private $payload;
    private $headers;
    private $requestUrl;

    public function __construct()
    {
        $this->base_url       = rtrim(config('providers.pikelpoint.base_url', 'https://api.pikelpoint.com'), '/');
        $this->token          = config('providers.pikelpoint.token', '');
        $this->webhook_secret = config('providers.pikelpoint.webhook_secret', '');
    }

    public function getRequest()
    {
        return [
            "url" => $this->requestUrl,
            "payload" => $this->payload,
            "headers" => $this->headers,
        ];
    }

    public function generateQr($txnid, $name, $phone, $email, $amount, $redirectUrl = null)
    {
        $payload = [
            "amount" => (float) $amount,
            "currency" => "inr",
            "merchant_order_id" => (string) $txnid,
            "redirect_url" => $redirectUrl ?: "http://157.20.51.180:8078/service/collection/upi",
            "name" => $name,
            "mobile_number" => $phone,
            "email" => $email,
            "address" => 'jigrotech solutions',
        ];

        $res = $this->request("POST", "/payin/v3/DynamicQR", $payload,true);

        $isSuccess = ($res->status ?? false) === true;
        $resData = $res->data ?? (object)[];
        $qrString = $resData->qr_string ?? '';
        $gatewayTxnId = (string) ($resData->id ?? ($resData->gateway_txn_id ?? ''));

        return (object)[
            'status' => $isSuccess ? 'success' : 'error',
            'isSuccess' => $isSuccess,
            'message' => $res->message ?? ($isSuccess ? 'Success' : 'Pikelpoint Error'),
            'qr' => $qrString,
            'txnID' => $gatewayTxnId,
            'data' => (object)[
                'paymentData' => (object)[
                    'intent' => $qrString,
                    'qr_string' => $qrString,
                    'paymentLink' => $resData->paymentLink ?? '',
                ],
                'transaction' => (object)[
                    'txnid' => $gatewayTxnId,
                ]
            ],
            'raw' => $res,
        ];
    }

    public function makePayout($txnid, $name, $phone, $amount, $account, $ifsc, $email = '', $bankName = '')
    {
        $payload = [
            "name" => $name,
            "mobile_number" => $phone,
            "email" => $email ?: "user@example.com",
            "address" => "India",
            "merchant_order_id" => (string) $txnid,
            "currency" => "inr",
            "amount" => (string) $amount,
            "payment_method" => "IMPS",
            "bank_name" => $bankName ?: "Bank",
            "account_number" => (string) $account,
            "ifsc" => strtoupper($ifsc),
        ];

        $res = $this->request("POST", "/payout/transfer", $payload);
        $isSuccess = ($res->status ?? false) === true;
        $resData = $res->data ?? (object)[];
        $gatewayId = (string) ($resData->gateway_id ?? ($resData->id ?? ''));
        $utr = (string) ($resData->utr ?? '');
        $txnStatus = ucfirst(strtolower($resData->status ?? ($isSuccess ? 'Pending' : 'Failed')));

        return (object)[
            'status' => $isSuccess ? 'success' : 'error',
            'isSuccess' => $isSuccess,
            'message' => $res->message ?? ($isSuccess ? 'Payout Initiated' : 'Payout Failed'),
            'gateway_id' => $gatewayId,
            'utr' => $utr,
            'txnStatus' => $txnStatus,
            'data' => (object)[
                'transaction' => (object)[
                    'txnStatus' => $txnStatus,
                    'txnid' => $gatewayId,
                    'utr' => $utr,
                ]
            ],
            'raw' => $res,
        ];
    }

    public function getStatus($merchantOrderId, $type = 'payin')
    {
        $endpoint = ($type === 'payout') ? "/payout/status/{$merchantOrderId}" : "/payin/status/{$merchantOrderId}";
        $res = $this->request("GET", $endpoint);
        $isSuccess = ($res->status ?? false) === true;
        $resData = $res->data ?? (object)[];

        return (object)[
            'status' => $isSuccess ? 'success' : 'error',
            'isSuccess' => $isSuccess,
            'message' => $res->message ?? '',
            'txnStatus' => ucfirst(strtolower($resData->status ?? ($isSuccess ? 'Success' : 'Pending'))),
            'utr' => $resData->utr ?? '',
            'amount' => $resData->amount ?? 0,
            'gateway_id' => (string) ($resData->gateway_id ?? ($resData->id ?? '')),
            'raw' => $res,
        ];
    }

    public function verifyWebhookSignature(string $rawBody, string $signature, ?string $secret = null): bool
    {
        $secretKey = $secret ?: $this->webhook_secret;
        if (empty($secretKey)) {
            return true;
        }

        if (empty($signature)) {
            return true;
        }

        $cleanSignature = trim($signature);

        $rawCalculated = hash_hmac('sha256', $rawBody, $secretKey);
        if (hash_equals($rawCalculated, $cleanSignature)) {
            return true;
        }

        $json = json_decode($rawBody, true);
        if (is_array($json)) {
            $data = $json['data'] ?? [];
            $timestamp = (string) ($json['timestamp'] ?? '');

            $concatenated = ($data['id'] ?? '')
                . ($data['amount'] ?? '')
                . ($data['status'] ?? '')
                . ($data['utr'] ?? '')
                . ($data['merchant_order_id'] ?? '')
                . $timestamp;

            $concatCalculated = hash_hmac('sha256', $concatenated, $secretKey);
            if (hash_equals($concatCalculated, $cleanSignature)) {
                return true;
            }
        }

        \Log::warning('Pikelpoint Webhook Signature Mismatch', [
            'received_signature' => $cleanSignature,
            'calculated_raw'     => $rawCalculated,
            'raw_body'           => $rawBody,
        ]);

        return false;
    }



private function request($method, $endpoint, $data = [], $isPayin = false)
{
    $baseUrl = $isPayin
        ? config('providers.pikelpoint.payin_url')
        : $this->base_url;

    $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

    $this->payload = $data;
    $this->requestUrl = $url;

    $this->headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    try {
        $request = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->timeout(30)
            ->connectTimeout(10);

        $response = strtoupper($method) === 'GET'
            ? $request->get($url, $data)
            : $request->send(strtoupper($method), $url, [
                'json' => $data,
            ]);

        Log::info('Pikelpoint API Response', [
            'url' => $url,
            'http_status' => $response->status(),
            'successful' => $response->successful(),
            'response' => $response->body(),
        ]);

        return $response->object();

    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        Log::error('Pikelpoint Connection Error', [
            'url' => $url,
            'error' => $e->getMessage(),
        ]);

        return null;

    } catch (\Throwable $e) {
        Log::error('Pikelpoint API Error', [
            'url' => $url,
            'error' => $e->getMessage(),
        ]);

        return null;
    }
}
}

