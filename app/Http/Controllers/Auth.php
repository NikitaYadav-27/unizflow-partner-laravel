<?php
namespace App\Http\Controllers;
use App\Libraries\Recaptcha;
use App\Libraries\Validators;
use App\Models\User;

class Auth extends Controller
{
    private const OTP_EXPIRY_SECONDS = 300;
    private const OTP_RESEND_DELAY = 120;
    public function login()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect('/user/dashboard');
        }
        return view('auth.login');
    }

    public function register()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect('/user/dashboard');
        }
        return view('auth.register');
    }

    public function verify_registration()
    {
        $session = session();
        if (!$session->get('user-registration-data')) {
            return redirect('/');
        }
        return view('auth.verify_registration');
    }

    public function register_user()
    {
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        $name = trim($input->name);
        $email = trim($input->email);
        $password = trim($input->password);
        $phone = ltrim(preg_replace("/\D/", "", trim($input->phone)), '0');

        if (!Validators::name($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Name. please enter valid name',
            ], 400);
        }

        if (!Validators::email($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email. please enter valid email',
            ], 400);
        }

        if (!Validators::phone($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Phone. please enter valid phone',
            ], 400);
        }

        if (!Validators::password($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character',
            ], 400);
        }

        $user = new User();
        if ($user->checkEmailExists($email) || $user->checkPhoneExists($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials are already in use.',
            ], 409);
        }

        $session = session();
        $session->put("user-registration-data", [
            "name" => strtoupper($name),
            "email" => strtolower($email),
            "phone" => $phone,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "merchant_id" => $this->generateMerchantId(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Details Validated Successfully. Proceed for verification.',
        ], 200);

    }

    public function send_registration_otp()
    {
        $session = session();

        if (!$session->get('user-registration-data')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration Session not found',
            ], 401);
        }

        $source = request()->post("source");
        if (!in_array($source, ['email'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Source Param',
            ], 400);
        }

        $lastOtpSendTimeKey = 'last_' . $source . '_otp_send_time';
        $otpSendFunction = 'sendRegisterOTP';

        if ($session->get($lastOtpSendTimeKey) && time() - $session->get($lastOtpSendTimeKey) <= self::OTP_RESEND_DELAY) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wait for 2 minutes before Resend OTP',
            ], 429);
        }

        $session->put($source . '_otp_expires', time() + self::OTP_EXPIRY_SECONDS);
        $this->$otpSendFunction($source);
        $session->put($lastOtpSendTimeKey, time());

        return response()->json([
            'status' => 'success',
            'message' => $source . ' OTP sent successfully',
        ], 200);
    }

    private function sendRegisterOTP($source)
    {
        $session = session();
        $otp = random_int(111111, 999999); 
        if ($source == "email") {
            $session->put('email_otp', $otp);
            $email = $session->get('user-registration-data')['email'];
            $message = "Your OTP code for registration is: $otp";
            
        \Illuminate\Support\Facades\Mail::raw($message, function ($message) use ($email) {
            $message->to($email)
                    ->subject('Your OTP for Registration');
        });
        }
    }

    public function register_user_verify()
    {
        $session = session();
        if (!$session->get('user-registration-data')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration Session not found',
            ], 401);
        }
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }

        if (
            !ctype_digit($input->email_otp) || strlen($input->email_otp) != 6
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        if ($session->get("email_otp_expires") < time()) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP Expired. Please resend OTP.',
            ], 400);
        }

        if ($input->email_otp != $session->get("email_otp")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        $userData = $session->get("user-registration-data");
        $name = $userData['name'];
        $email = $userData['email'];
        $phone = $userData['phone'];
        $password = $userData['password'];
        $merchantId = $userData['merchant_id'];
        $session->forget("user-registration-data");
        $session->forget("phone_otp");
        $session->forget("email_otp");
        $session->forget("last_email_otp_send_time");
        $session->forget("last_phone_otp_send_time");
        $session->forget("email_otp_expires");
        $session->forget("phone_otp_expires");

        $user = new User();
        if ($user->checkEmailExists($email) || $user->checkPhoneExists($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials are already in use.',
            ], 409);
        }

        //register user
        \App\Models\User::create([
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "password" => $password,
            "merchant_id" => $merchantId,
            "api_access_key" => bin2hex(random_bytes(30))
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User Registered Successfully !',
        ], 201);

    }

    private function generateMerchantId()
    {
        $user = new User();
        $merchantId = "RD" . strtoupper(bin2hex(random_bytes(4)));
        if ($user->checkMerchantIdExists($merchantId)) {
            return $this->generateMerchantId();
        } else {
            return $merchantId;
        }
    }

    public function login_user()
    {
        $session = session();
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        $email = trim($input->email);
        $password = trim($input->password);
        $lat = $input->location->lat ?? null;
        $lon = $input->location->lon ?? null;
        $browser = $input->browser;
        $device = $input->device;
        $os = $input->os;

        if (!Validators::email($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email',
            ], 400);
        }

        if (!Validators::password($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Password',
            ], 400);
        }

        if (!in_array($browser, ["Unknown Browser", "Chrome", "Firefox", "Safari", "Internet Explorer"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Browser',
            ], 400);
        }

        if (!in_array($os, ["Unknown OS", "Windows", "Linux", "macOS", "Android", "iOS"])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Operating System',
            ], 400);
        }

        $user = new User();
        $userData = $user->where('email', strtolower($email))->first();
        if ($userData) {
            //check for login attempts exceeded more than 3 in 15 minutes

            $loginAttempt = new \App\Models\LoginAttempt();
            $loginAttempt->where('user_id', $userData['id'])
                ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
                ->where('verified', -1);

            $loginAttemptCount = $loginAttempt->count();
            if ($loginAttemptCount >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many login attempts. Please try again after 15 Minutes.',
                ], 429);
            }

            //add login Attempt 
            $loginAttempt = new \App\Models\LoginAttempt();
            $createdAttempt = \App\Models\LoginAttempt::create([
                "ip" => request()->ip(),
                "browser" => $browser,
                "device" => $device,
                "lat" => $lat,
                "log" => $lon,
                "verified" => 0,
                "user_id" => $userData['id'],
                "session_id" => session_id()
            ]);

            $loginAttemptId = $createdAttempt->id;
        }

        if (!$userData || !password_verify($password, $userData['password'])) {
            //login attempt failed
            if($userData){
              \App\Models\LoginAttempt::where("id", $loginAttemptId)->update([
                  "verified" => -1,
                  "user_id" => $userData['id']
              ]);
           }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials',
            ], 401);

        }

        //login attempt verified
        \App\Models\LoginAttempt::where("id", $loginAttemptId)->update([
            "verified" => 1,
            "user_id" => $userData['id']
        ]);

        if ($userData['active'] == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        //insert logs to login_logs table
        $login = new \App\Models\Login();
        $session_id =  session_id();
        \App\Models\Login::create([
            "user_id" => $userData['id'],
            "ip" => request()->ip(),
            "browser" => $browser,
            "device" => $device,
            "os" => $os,
            "lat" => $lat,
            "lon" => $lon,
            "verified" => 0,
            "email_verified" => 0,
            "phone_verified" => 0,
            "session_id" => $session_id
        ]);
        
        
        $browserToken = request()->cookie('browser_token');
        if(strlen($browserToken) > 5){
            $loginBrowserModel = new \App\Models\LoginBrowser();
            $browser = $loginBrowserModel->findBrowserToken($browserToken);
            if(isset($browser['user_id']) && $browser['user_id'] == $userData['id']){
                //update login log
                $login->where("session_id",$session_id)->update(["phone_verified" => $browser['phone_verified'],
                   "email_verified" => $browser['email_verified']]);
                
                session()->put('user_id', $userData['id']);
                
                return response()->json([
                   'status' => 'success',
                   'otpRequired' => false,
                   'message' => 'Login Successful !',
                ], 200);
                
            }
        }

        //send otp to email & phone
        $emailOtp = random_int(111111, 999999);
        $session->put('email_otp', $emailOtp);
      
        $session->put('otp_expires', time() + self::OTP_EXPIRY_SECONDS);

        //send email
        \Illuminate\Support\Facades\Mail::raw("Your OTP for Login is ".$emailOtp, function($message) use ($userData) {
            $message->to($userData['email'])->subject('Your OTP for Login - '.env("APP_NAME").' Account');
        });

      
        return response()->json([
            'status' => 'success',
            'otpRequired' => true,
            'message' => 'Login OTP sent successfully',
        ], 200);

    }

    public function verify_login()
    {
        //check if there is login log is present in login_logs with current session id within 15 minutes
        $session = session();
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session_id())->orderBy('id', 'DESC')->where('verified', 0)
        ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
        ->first();
        if (!$loginLog) {
            return redirect('/user/login');
        }
        $user = new User();
        $userData = $user->where('id', $loginLog['user_id'])->first();
        return view('auth.verify_login', [ "email" => $userData['email'], "phone" => $userData['phone'] ]);
    }

    public function verify_login_otp(){
        //captcha verification
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        $session = session();
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session_id())->orderBy('id', 'DESC')->where('verified', 0)
            ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
            ->first();
        if (!$loginLog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login Session not found',
            ], 401);
        }

        if (!$session->get('email_otp')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login Session not found',
            ], 401);
        }

        //check if otp matching with phone otp or email otp .

        if (
            !ctype_digit($input->otp) || strlen($input->otp) != 6
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }
        if ($session->get("otp_expires") < time()) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP Expired. Please resend OTP.',
            ], 400);
        }
        if ($input->otp != $session->get("email_otp")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        $emailVerified = $input->otp == $session->get("email_otp") ? 1 : 0;
        $phoneVerified = 0;


        //login attempt verified
        \App\Models\Login::where("id", $loginLog['id'])->update([
            "verified" => 1,
            "email_verified" => $emailVerified,
            "phone_verified" => $phoneVerified
        ]);
        
        
        $loginBrowserModel = new \App\Models\LoginBrowser();
        $browserToken = substr(bin2hex(random_bytes(30)), 0, 30);
        \App\Models\LoginBrowser::create([
           "user_id" => $loginLog['user_id'],
           "browser_token" => $browserToken,
           "phone_verified" => $phoneVerified,
           "email_verified" => $emailVerified
        ]);
        
        
        session()->put('user_id', $loginLog['user_id']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Login Successful',
        ], 200)->cookie("browser_token", $browserToken, 86400 / 60, "/", null, true, true);
    }


    public function forgot_password()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect('/user/dashboard');
        }
        return view('auth.forgot_password');
    }
    
    public function send_reset_password_link() {
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.7)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        
        $email = $input->email;
        if(!Validators::email($email)){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email Address',
            ], 400);
        }
        
        $userModel = new \App\Models\User();
        if($userModel->where("email",$email)->count() == 0){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email Address / Email is not Registered',
            ], 400);
        }
        
        $user = $userModel->where("email",$email)->first();
        $token = strtolower(substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 50));
        $userModel->update($user['id'], [
           "reset_password_token" => $token,
           "reset_password_token_time" => time()
        ]);
        
        //send email
        $link = env("app_baseURL")."auth/reset/password/".$token;
        
        \Illuminate\Support\Facades\Mail::raw("For Reset password please follow link ".$link, function ($message) use ($email) {
            $message->to($email)
                    ->subject("Password Reset - ".env("app.title")." Account");
        });
        return response()->json([
               'status' => 'success',
               'message' => 'Email Sent Successfully !',
        ], 200);
        
        
    }
    
    public function reset_password($token) {
        if(strlen($token) < 10) {
            return redirect('/user/login');
        }
        $userModel = new \App\Models\User();
        $user = $userModel->where("reset_password_token", $token)->first();
        if(!isset($user['id'])) {
            return redirect('/user/login');
        }
        $generateTime = $user['reset_password_token_time'];
        $currentTime = time();
        $hoursToExpire = 1;
        if (($currentTime - $generateTime) > ($hoursToExpire * 3600)) {
            return redirect('/user/login');
        }
        $data = [];
        $data['user'] = $user;
        return view('auth.reset_password',$data);
    }
    
    public function update_password() {
        $input = (object) json_decode(request()->getContent());
        // $result = Recaptcha::verifyV3Captcha($input->captcha);
        // if (!($result->success && $result->score > 0.5)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Unable to resolve captcha.',
        //     ], 401);
        // }
        $token = $input->token;
        $userModel = new \App\Models\User();
        $user = $userModel->where("reset_password_token", $token)->first();
        if(!isset($user['id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Reset password Token or Expired',
            ], 401);
        }
        $generateTime = $user['reset_password_token_time'];
        $currentTime = time();
        $hoursToExpire = 1;
        if (($currentTime - $generateTime) > ($hoursToExpire * 3600)) {
           return response()->json([
                'status' => 'error',
                'message' => 'Reset password Token is Expired',
            ], 401);
        }
        
        $npassword = $input->npassword;
        $cpassword = $input->cpassword;
        if(!Validators::password($npassword) || !Validators::password($cpassword)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character',
            ], 400);
        }
        
        if($npassword != $cpassword){
            return response()->json([
                'status' => 'error',
                'message' => 'New Password & Confirm Password not Matched',
            ], 400);
        }
        
        //update user Password
        $userModel->where('id', $user['id'])->update([
          "password" => password_hash($npassword, PASSWORD_DEFAULT)
        ]);
        
        //make expire Token
        $token = strtolower(substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 50));
        $userModel->where('id', $user['id'])->update([
           "reset_password_token" => $token,
           "reset_password_token_time" => time()
        ]);
        
        return response()->json([
           'status' => 'success',
           'message' => 'Your new password is updated successfully !',
        ], 200);
        
    }

    public function logout()
    {
        $session = session();
        $session->flush();
        return redirect('/user/login');
    }

    public function ad_login() {
        $userModel = new \App\Models\User();
        if(strlen($_GET['ad_token']) > 10){
            $user = $userModel->where("ad_token",$_GET['ad_token'])->first();;
            if(isset($user["id"])){
                session()->put("user_id",$user["id"]);
                $userModel->where('id', $user["id"])->update(["ad_token" => ""]);
                return redirect('/user/dashboard');
            }
        }
        
    }

}