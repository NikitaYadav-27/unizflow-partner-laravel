<?php
namespace App\Libraries;

class Zenova
{
    private $base_url = "https://liveapi.zenovaservices.com/api/";
    private $memberId = "PTN16539017";
    private $txnPwd   = "12355";
    private $payload;
    private $headers;
    private $requestUrl;

    public function getRequest()
    {
        return [
            "url" => $this->requestUrl,
            "payload" => $this->payload,
            "headers" => $this->headers
        ];
    }

    public function generateQr($txnid, $memberid, $txnpwd, $name, $amount)
    {
        $this->payload = [
            "memberid" => $memberid,
            "txnpwd"   => $txnpwd,
            "name"     => $name,
            "amount"   => $amount,
            "txnid"    => $txnid
        ];

        $endpoint = "GenerateIntent";
        $this->requestUrl = $this->base_url . $endpoint;

        $response = $this->requestGet($this->requestUrl, $this->payload);

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => isset($response->isSuccess) ? (bool)$response->isSuccess : false,
            'status'    => isset($response->status) ? ucfirst(strtolower($response->status)) : 'FAIL',
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
       
        $endpoint = "Common/CollectionStatusCheck";
        $this->requestUrl = $this->base_url . $endpoint;

        $response = $this->requestPost($this->requestUrl, $this->payload);

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => isset($response->isSuccess) ? (bool)$response->isSuccess : false,
            'status'    => $response->result ?? 'FAIL',
            'message'   => $response->message ?? 'No message from Zenova',
            'data'      => $response->data ?? $response,
            'raw'       => $response
        ];
    }

    /**
     * 🔹 New Function: Make Payout Request
     */
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

        $endpoint = "TransferPayOut";
        $this->requestUrl = $this->base_url . $endpoint;

        // Required headers
        $this->headers = [
            "Accept: application/json",
            "MemberId: {$memberId}",
            "TXNPWD: {$txnPwd}"
        ];

        // Execute POST call
        $response = $this->requestRawPost($this->requestUrl, $this->payload);

        if (isset($response->scalar)) {
            $inner = json_decode($response->scalar);
            if ($inner) $response = $inner;
        }

        return (object)[
            'isSuccess' => isset($response->isSuccess) ? (bool)$response->isSuccess : false,
            'status'    => $response->status ?? 'FAIL',
            'message'   => $response->message ?? ($response->result ?? 'No message from Zenova'),
            'data'      => $response->data ?? $response,
            'raw'       => $response
        ];
    }

    private function requestGet($url, $params)
    {
        $query = http_build_query($params);
        $this->requestUrl = $url . "?" . $query;
        $this->headers = ["Accept: application/json"];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $this->headers,
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return (object)[
                "isSuccess" => false,
                "status" => "FAIL",
                "message" => "CURL Error: " . $curl_error
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

    private function requestPost($url, $params)
    {
        $queryString = http_build_query($params);
        $this->requestUrl = $url . '?' . $queryString;

        $this->headers = ["Accept: application/json"];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => $this->headers,
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);
       
        if ($curl_error) {
            return (object)[
                "isSuccess" => false,
                "status" => "FAIL",
                "message" => "CURL Error: " . $curl_error
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

    /**
     * POST with headers + body (used for payout)
     */
    private function requestRawPost($url, $body)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => $this->headers,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
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
