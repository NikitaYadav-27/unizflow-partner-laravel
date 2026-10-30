<?php

namespace App\Libraries;

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
            "redirect_url" => $redirectUrl ?: "",
            "name" => $name,
            "mobile_number" => $phone,
        ];

        $res = $this->request("POST", "/payin/DynamicQR", $payload);
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
        if (empty($secretKey) || empty($signature)) {
            return false;
        }

        $json = json_decode($rawBody, true);
        if (!is_array($json)) {
            return false;
        }

        $data = $json['data'] ?? [];
        $timestamp = (string) ($json['timestamp'] ?? '');

        $concatenated = ($data['id'] ?? '')
            . ($data['amount'] ?? '')
            . ($data['status'] ?? '')
            . ($data['utr'] ?? '')
            . ($data['merchant_order_id'] ?? '')
            . $timestamp;

        $calculated = hash_hmac('sha256', $concatenated, $secretKey);
        return hash_equals($calculated, trim($signature));
    }

    private function request($method, $endpoint, $data = [])
    {
        $curl = curl_init();
        $this->payload = $data;
        $url = $this->base_url . $endpoint;
        $this->requestUrl = $url;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ];
        $this->headers = $headers;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
        ];

        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } elseif (strtoupper($method) === 'GET' && !empty($data)) {
            $options[CURLOPT_URL] = $url . '?' . http_build_query($data);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }
}

