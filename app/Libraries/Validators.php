<?php
namespace App\Libraries;

class Validators
{
    public static function phone($number)
    {
        $number = preg_replace('/\D/', '', $number);
        $number = trim($number);

        if (strlen($number) !== 10) {
            return false;
        }
        if (!preg_match('/^[6789]/', $number)) {
            return false;
        }
        if (preg_match('/^(\d)\1{9}$/', $number)) {
            return false;
        }
        return true;
    }

    public static function name($name)
    {
        return preg_match("/^[a-zA-Z-' ]{2,50}$/", $name);
    }

    public static function password($password)
    {
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/", $password);
    }

    public static function email($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        list($username, $domain) = explode('@', $email);

        if (strlen($username) <= 4) {
            return false;
        }

        $forbiddenKeywords = ['demo', 'unknown', 'test', 'user', 'admin', 'example', 'sample', 'placeholder', 'anonymous', 'temp', 'fake', 'fictitious', 'trial'];

        foreach ($forbiddenKeywords as $keyword) {
            if (stripos($username, $keyword) !== false) {
                return false;
            }
        }

        return true;
    }

    public static function amount($amount)
    {
        //check for valid amount and amount should be greater than 0
        if ($amount <= 0) {
            return false;
        }
        return preg_match("/^\d+(\.\d{1,2})?$/", $amount);
    }

    public static function accountNumber($accountNumber)
    {
        $accountNumber = preg_replace('/\D/', '', $accountNumber);
        return preg_match('/^\d{6,18}$/', $accountNumber);
    }

    public static function ifsc($ifsc)
    {
        return preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/i', $ifsc);
    }

    public static function utr($utr)
    {
        return preg_match('/^\d{6,}$/', $utr);
    }

    public static function remarks($remarks)
    {
        // Remove HTML/JS tags and encode special characters
        $cleaned = htmlspecialchars(strip_tags($remarks), ENT_QUOTES, 'UTF-8');
        // If cleaned content is not the same, it's unsafe
        if ($cleaned !== $remarks) {
            return false;
        }
        // Length check
        return is_string($remarks) && strlen($remarks) <= 100;
    }

    public static function transactionMode($mode)
    {
        $allowed = ['IMPS', 'NEFT', 'RTGS', 'UPI'];
        return in_array(strtoupper($mode), $allowed);
    }

    public static function url($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme), ['https'])) {
            return false;
        }

        return true;
    }

    public static function ip($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public function convertNumberToWord($num = false)
    {
        $num = (int) $num;
        if (!$num) {
            return 'Zero';
        }

        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
        ];

        $result = '';

        $crore = floor($num / 10000000);
        $num %= 10000000;

        $lakh = floor($num / 100000);
        $num %= 100000;

        $thousand = floor($num / 1000);
        $num %= 1000;

        $hundred = floor($num / 100);
        $num %= 100;

        if ($crore) {
            $result .= $this->convertNumberToWord($crore) . ' Crore ';
        }
        if ($lakh) {
            $result .= $this->convertNumberToWord($lakh) . ' Lakh ';
        }
        if ($thousand) {
            $result .= $this->convertNumberToWord($thousand) . ' Thousand ';
        }
        if ($hundred) {
            $result .= $this->convertNumberToWord($hundred) . ' Hundred ';
        }

        if ($num) {
            $result .= $result != '' ? 'and ' : '';
            if ($num < 21) {
                $result .= $words[$num];
            } else {
                $ten = floor($num / 10) * 10;
                $unit = $num % 10;
                $result .= $words[$ten];
                if ($unit) {
                    $result .= ' ' . $words[$unit];
                }
            }
        }

        return trim($result);
    }
}
