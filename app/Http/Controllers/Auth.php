<?php
namespace App\Http\Controllers;
use App\Libraries\Recaptcha;
use App\Libraries\Validators;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Auth extends Controller
{
    private const OTP_EXPIRY_SECONDS = 300;
    private const OTP_RESEND_DELAY = 120;
    public function login()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function register()
    {
        $session = session();
        if ($session->get('user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function verify_registration()
    {
        $session = session();
        if (!$session->get('user-registration-data')) {
            return redirect()->route('home');
        }
        return view('auth.verify_registration');
    }

    public function register_user()
    {
        $name = trim(request()->input('name', ''));
        $email = trim(request()->input('email', ''));
        $password = trim(request()->input('password', ''));
        $phone = ltrim(preg_replace("/\D/", "", trim(request()->input('phone', ''))), '0');

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
            "password" => Hash::make($password),
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
        $emailOtp = request()->input('email_otp');

        if (
            !ctype_digit((string) $emailOtp) || strlen((string) $emailOtp) != 6
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

        if ($emailOtp != $session->get("email_otp")) {
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
        $email = trim(request()->input('email', ''));
        $password = trim(request()->input('password', ''));
        if (app()->environment('local')) {
            $lat = request()->input('location.lat', 26.9124);
            $lon = request()->input('location.lon', 75.7873);
        } else {
            $lat = request()->input('location.lat');
            $lon = request()->input('location.lon');
        }
        $browser = request()->input('browser');
        $device = request()->input('device');
        $os = request()->input('os');

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

        $userData = User::where('email', strtolower($email))->first();
        if ($userData) {

            $loginAttemptCount = \App\Models\LoginAttempt::where('user_id', $userData['id'])
                ->where('created_at', '>=', now()->subMinutes(15))
                ->where('verified', -1)
                ->count();

            if ($loginAttemptCount >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many login attempts. Please try again after 15 Minutes.',
                ], 429);
            }

            $createdAttempt = \App\Models\LoginAttempt::create([
                "ip" => request()->ip(),
                "browser" => $browser,
                "device" => $device,
                "lat" => $lat,
                "log" => $lon,
                "verified" => 0,
                "user_id" => $userData['id'],
                "session_id" => session()->getId()
            ]);

            $loginAttemptId = $createdAttempt->id;
        }

        if (!$userData || !Hash::check($password, $userData['password'])) {
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

        \App\Models\LoginAttempt::where("id", $loginAttemptId)->update([
            "verified" => 1,
            "user_id" => $userData['id']
        ]);

        \App\Models\LoginAttempt::where('user_id', $userData['id'])
            ->where('verified', -1)
            ->update(["verified" => 2]);

        if ($userData['active'] == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $login = new \App\Models\Login();
        $session_id = session()->getId();
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

        if(app()->environment('local')) {
            $emailOtp = 654321;
        } else {
            $emailOtp = random_int(111111, 999999);
        }
        $session->put('email_otp', $emailOtp);
      
        $session->put('otp_expires', time() + self::OTP_EXPIRY_SECONDS);

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
        $session = session();
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session()->getId())->orderBy('id', 'DESC')->where('verified', 0)
        ->where('created_at', '>=', date("Y-m-d H:i:s", strtotime("-15 minutes")))
        ->first();
        if (!$loginLog) {
            return redirect()->route('login');
        }
        $user = new User();
        $userData = $user->where('id', $loginLog['user_id'])->first();
        return view('auth.verify_login', [ "email" => $userData['email'], "phone" => $userData['phone'] ]);
    }

    public function verify_login_otp(){
        $otp = request()->input('otp');
        $session = session();
        $login = new \App\Models\Login();
        $loginLog = $login->where('session_id', session()->getId())->orderBy('id', 'DESC')->where('verified', 0)
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

if (
            !ctype_digit((string) $otp) || strlen((string) $otp) != 6
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
        if ($otp != $session->get("email_otp")) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please Enter valid OTP',
            ], 400);
        }

        $emailVerified = $otp == $session->get("email_otp") ? 1 : 0;
        $phoneVerified = 0;

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
            return redirect()->route('dashboard');
        }
        return view('auth.forgot_password');
    }
    
    public function send_reset_password_link() {
        $email = request()->input('email');
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
            return redirect()->route('login');
        }
        $userModel = new \App\Models\User();
        $user = $userModel->where("reset_password_token", $token)->first();
        if(!isset($user['id'])) {
            return redirect()->route('login');
        }
        $generateTime = $user['reset_password_token_time'];
        $currentTime = time();
        $hoursToExpire = 1;
        if (($currentTime - $generateTime) > ($hoursToExpire * 3600)) {
            return redirect()->route('login');
        }
        $data = [];
        $data['user'] = $user;
        return view('auth.reset_password',$data);
    }
    
    public function update_password() {
        $token = request()->input('token');
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
        
        $npassword = request()->input('npassword');
        $cpassword = request()->input('cpassword');
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
        
        $userModel->where('id', $user['id'])->update([
          "password" => Hash::make($npassword)
        ]);
        
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
        return redirect()->route('login');
    }

    public function ad_login() {
        $adToken = request()->query('ad_token');
        if ($adToken && strlen($adToken) > 10) {
            $userModel = new \App\Models\User();
            $user = $userModel->where("ad_token", $adToken)->first();
            if ($user && isset($user["id"])) {
                session()->put("user_id", $user["id"]);
                $userModel->where('id', $user["id"])->update(["ad_token" => ""]);
                return redirect()->route('dashboard');
            }
        }
        return redirect()->route('login')->with('error', 'Invalid or expired login token.');
    }

}