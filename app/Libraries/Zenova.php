<?php
namespace App\Libraries;

class Zenova
{
    private $base_url;
    private $memberId;
    private $txnPwd;

    private $payload;
    private $headers;
    private $requestUrl;

    public function __construct()
    {
        $this->base_url = rtrim(config('providers.zenova.base_url', 'https://liveapi.zenovaservices.com/api/'), '/') . '/';
        $this->memberId = config('providers.zenova.member_id', 'PTN16539017');
        $this->txnPwd   = config('providers.zenova.txn_pwd', '12355');
    }

    public function getRequest()
    {
        return [
            "url" => $this->requestUrl,
            "payload" => $this->payload,
            "headers" => $this->headers
        ];
    }

  
    public function generateQr($txnid, $name, $amount)
    {
        $this->payload = [
            "memberid" => $this->memberId,
            "txnpwd"   => $this->txnPwd,
            "name"     => $name,
            "amount"   => $amount,
            "txnid"    => $txnid
        ];

        $response = $this->request(
            "GET",
            $this->base_url . "GenerateIntent",
            $this->payload
        );

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => (bool)($response->isSuccess ?? false),
            'status'    => ucfirst(strtolower($response->status ?? 'FAIL')),
            'message'   => $response->message ?? 'No message from Zenova',
            'qr'        => $response->qr ?? '',
            'txnID'     => $response->txnID ?? '',
            'qrimage'   => $response->qrimage ?? '',
            'raw'       => $response
        ];
    }


  
    public function checkStatus($txnId, $memberId, $txnPwd)
    {
        $this->payload = [
            "MemberId" => $memberId,
            "TxnPwd"   => $txnPwd,
            "TxnId"    => $txnId
        ];

        $response = $this->request(
            "POST",
            $this->base_url . "Common/CollectionStatusCheck",
            $this->payload,
            [],
            "checkstatus"
        );

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => (bool)($response->isSuccess ?? false),
            'status'    => $response->result ?? 'FAIL',
            'message'   => $response->message ?? 'No message from Zenova',
            'data'      => $response->data ?? $response,
            'raw'       => $response
        ];
    }


    
    public function makePayout($memberId, $txnPwd, $mobile, $account_holder_name, $account_no, $ifsc, $amount, $txnID)
    {
        $this->payload = [
            "mobile"              => $mobile,
            "account_holder_name" => $account_holder_name,
            "account_no"          => $account_no,
            "ifsc"                => $ifsc,
            "amount"              => $amount,
            "txnID"               => $txnID
        ];

        $headers = [
            "MemberId: {$memberId}",
            "TXNPWD: {$txnPwd}"
        ];

        $response = $this->request(
            "POST",
            $this->base_url . "TransferPayOut",
            $this->payload,
            $headers,
            "payout"
        );

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => (bool)($response->isSuccess ?? false),
            'status'    => $response->status ?? 'FAIL',
            'message'   => $response->message ?? ($response->result ?? 'No message from Zenova'),
            'data'      => $response->data ?? $response,
            'raw'       => $response
        ];
    }

    public function generateToken()
    {
        $timestamp = time();
        $reqId = "TXN" . rand(100000, 999999);

        $payload = json_encode([
            "timestamp" => $timestamp,
            "reqId" => $reqId
        ]);

        return base64_encode($payload);
    }


     
    private function request($method, $url, $params = [], $headers = [], $service = "")
    {
        $curl = curl_init();

        $this->headers = array_merge(["Accept: application/json"], $headers);

        if (strtoupper($method) === "GET") {

            $query = http_build_query($params);
            $this->requestUrl = $url . '?' . $query;

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->requestUrl,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => $this->headers,
            ]);

        } else { 
            $queryString = ($service === 'checkstatus') ? '?' . http_build_query($params) : '';
            $body        = ($service === 'payout') ? http_build_query($params) : '';
            $this->requestUrl = $url . $queryString;

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->requestUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $this->headers,
            ]);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return (object)[
                "isSuccess" => false,
                "status" => "FAIL",
                "message" => "CURL Error: " . $error
            ];
        }

        $decoded = json_decode($response);

        if (!$decoded) {
            return (object)[
                "isSuccess" => false,
                "status" => "FAIL",
                "message" => "Invalid JSON response",
                "raw" => $response
            ];
        }

        return (object)$decoded;
    }

}
