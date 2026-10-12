<?php
namespace App\Libraries;
class Solitpay
{
    private $base_url = "https://partner.solitpay.com/";
    private $merchantId = "RD7E01BCA1"; //RDD1F87896
    private $clientSecret = "6fcbdd4708c4450f6e6b7a8e18c43ffcfc2e10546e14f2430f7eeecbc66d";
    private $enciv = "4gcO6dyLtfMZwfQT";
    private $enckey = "e39574fc26cc0a88bc9ad307a59466d8ba1e082ed07f258e2ee87fc046719fb3";
    private $payload;
    private $headers;
    private $requestUrl;
    
    public function getRequest() {
        return [
           "url" => $this->requestUrl,
           "payload" => $this->payload,
           "headers" => $this->headers
        ];
    }
    
    public function generateQr($txnid, $name, $phone, $email , $amount){
        return $this->request("api/v1/upi/orders/create",[
         "name" => $name,
         "phone" => $phone,
         "amount" => $amount,
         "email" => $email,
         "merchantTxnId" => $txnid,
         "notes" => "ParkPay #".$txnid
        ]);
    }
    
    public function makePayout($txnid,$name,$phone,$amount,$account,$ifsc) {
        return $this->request("api/v1/upi/payout/initiate",[
          "name" => $name,
          "phone" => $phone,
          "amount" => $amount,
          "account_number" => $account,
          "ifsc_code" => $ifsc,
          "merchantTxnId" => $txnid,
          "notes" => "payout #".$txnid
        ]);
    }
    
    public function getStatus($txnid) {
        return $this->request("api/v1/orders/check-status",[
          "merchantTxnId" => $txnid
        ]);
    }
    
    
    public function generateToken() {
        $timestamp = time(); 
        $reqId = "TXN" . rand(1000000000, 9999999999); 
        $data = json_encode([
            "timestamp" => $timestamp,
            "secret" => $this->clientSecret,
            "reqId" => $reqId
        ]);
        $token = base64_encode(openssl_encrypt($data, 'AES-256-CBC', $this->enckey, OPENSSL_RAW_DATA, $this->enciv));
        return $token;
    }


    private function request($endpoint, $data) {
        $curl = curl_init();
        $this->payload = $data;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->requestUrl = $this->base_url . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->headers = array(
                'Merchant-id: ' . $this->merchantId,
                'Token: '. $this->generateToken(),
                'accept: application/json',
                'content-type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}