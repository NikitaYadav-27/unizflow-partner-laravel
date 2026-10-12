<?php
namespace App\Libraries;

class Recaptcha{
    public static function verifyV3Captcha($captchaResponse)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => env("recaptcha.secret"),
            'response' => $captchaResponse
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);
        return $result;
    }
    
    public static function verifyV2Captcha($captchaResponse)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => env("recaptchav2.secret"),
            'response' => $captchaResponse
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);
        return $result;
    }
}