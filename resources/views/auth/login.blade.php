<!DOCTYPE html>
<html style="--primary-rgb: {!! e(env('colors.primary.rgb')) !!};" lang="en" dir="ltr" data-nav-layout="vertical"
    data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light"
    data-toggled="close">

<head>

    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - {!! env('app.title') !!}</title>
    <meta name="Description" content="">
    <meta name="Author" content="">
    <meta name="keywords" content="">
    
    {!! view("includes/css"); !!}

</head>

<body class="bg-white">

 <div id="loader" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 999999;" class="bg-primary d-flex justify-content-center align-items-center flex-column gap-3">
         <img src="{!! e(env('logo')) !!}" class="h-auto" style="filter: brightness(100); width: 20%;" alt="">
         <div class="spinner-border text-light" role="status"></div>
    </div>

    <div class="">
        <div class="d-flex justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-7 col-xl-6 col-lg-6 col-md-0 d-none d-lg-flex col-12 position-relative bg-primary px-0 vh-100"  >
               <div class="authentication-cover" style="background: none;">
                <div class="aunthentication-cover-content rounded d-flex justify-content-center align-items-center" style="background-color: transparent; backdrop-filter: none;">
                    <div class="swiper keyboard-control">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="text-fixed-white text-center p-5 d-flex align-items-center justify-content-start">
    <div>
        <div class="mb-2">
            <img src="/assets/auth-feature.png" class="w-100 h-auto" alt="{!! env('app.title') !!} Login">
        </div>
        <h4 class="fw-semibold text-fixed-white">Welcome to {!! env('app.title') !!}</h4>
        <p class="fw-normal fs-14 op-7">
            Sign in to your secure <strong>{!! env('app.title') !!}</strong> account and take control of your payments. 
            Track transactions in real time, manage settlements with ease, and access powerful insights to grow your business. 
            With UNIZFLOW, every payment is seamless, secure, and built for your success.
        </p>
    </div>
</div>

                            </div>
                       
                        </div>
                       
                    </div>
                </div>
            </div>
            </div>

            <div class="col-xxl-5 col-xl-6 col-lg-6 col-md-12 col-sm-8 col-12">
                <div class="container">
                    <div class="d-flex justify-content-center">
                        <div class="card col-xxl-9 col-lg-9 col-md-9 col-sm-12 ">
                            <div class="card-body p-5">

<div class="d-flex  justify-content-between align-items-center mb-4">
                                    <img src="{!! e(env('logo')) !!}" alt="logo"
                                        style="width: auto; height: 70px;" class="desktop-logo">
                                    <div class="d-flex flex-column">
                                       <h6 class="text-primary mb-0">Login</h6>
                                       <p class="text-muted mb-0 fs-10">login to your account.</p>
                                    </div>
                                </div>

<form class="no-ajax">
                                    <div class="row gy-3">
                                        
                                        <div class="col-xl-12">
                                            <label for="email" class="form-label text-default">Email</label>
                                            <input autocomplete="new-email" type="email" name="email"
                                                class="form-control form-control-lg" id="email"
                                                placeholder="Enter you email" required>
                                        </div>
                                        <div class="col-xl-12 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                              <label for="password" class="form-label text-default d-block mb-0">Password</label>
                                                <a onclick="navigate('{{ route('forgot-password') }}',true)" class="text-primary cursor-pointer">Forgot Password?</a>
                                            </div>
                                          
                                            <div class="input-group">
                                                <input autocomplete="new-password" type="password" name="password"
                                                    class="form-control form-control-lg" id="password"
                                                    placeholder="Enter you password" required>
                                                <button class="px-3 btn btn-light" type="button"
                                                    onclick="createpassword('password',this)"
                                                    id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                            </div>
                                            <div class="mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" name="remember_me" type="checkbox"
                                                        value="" id="defaultCheck1">
                                                    <label class="form-check-label text-muted fw-normal"
                                                        for="defaultCheck1">
                                                        Remember password ?
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

<div class="col-xl-12 d-grid mt-2">
                                            <button type="button" id="loginBtn" class="btn btn-lg btn-danger rounded-pill">Login</button>
                                        </div>
                                        
                                         <div class="col-xl-12 d-grid mt-3">
                                            <p class="text-center">Don't have account ? <a class="text-primary cursor-pointer" onclick="navigate('{{ route('register') }}',true)">Register</a></p>
                                        </div>

</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .authentication .authentication-cover:before {
            background-color: transparent;
        }

    </style>
<p class="d-none">login-page-identifier</p>

<script>

function createpassword (type, ele)  {
    document.getElementById(type).type = document.getElementById(type).type == "password" ? "text" : "password"
    let icon = ele.childNodes[0].classList
    let stringIcon = icon.toString()
    if (stringIcon.includes("ri-eye-line")) {
        ele.childNodes[0].classList.remove("ri-eye-line")
        ele.childNodes[0].classList.add("ri-eye-off-line")
    }
    else {
        ele.childNodes[0].classList.add("ri-eye-line")
        ele.childNodes[0].classList.remove("ri-eye-off-line")
    }
}

document.querySelector("#loginBtn").onclick = async () => {
    const email = document.querySelector("#email").value.trim();
    const password = document.querySelector("#password").value.trim();
    $("#loginBtn").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $("#loginBtn").attr("disabled",true);
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        notify("error", "Invalid email address");
        $("#loginBtn").html('Sign In');
        $("#loginBtn").removeAttr("disabled");
        return;
    }

    if (password.length < 6) {
        notify("error", "Password must be at least 6 characters");
        $("#loginBtn").html('Sign In');
        $("#loginBtn").removeAttr("disabled");
        return;
    }

    try {

        let captchaToken = '';
        try {
            if (typeof grecaptcha !== 'undefined' && window.recaptchaSiteKey) {
                captchaToken = await new Promise((resolve) => {
                    grecaptcha.ready(async () => {
                        try {
                            const token = await grecaptcha.execute(window.recaptchaSiteKey, { action: 'login' });
                            resolve(token);
                        } catch (e) {
                            console.warn("reCAPTCHA execute failed:", e);
                            resolve('');
                        }
                    });
                });
            }
        } catch (e) {
            console.warn("reCAPTCHA unavailable:", e);
        }

        let coords = { lat: null, lon: null };
        try {
            coords = await new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(
                    position => resolve({
                        lat: position.coords.latitude,
                        lon: position.coords.longitude
                    }),
                    error => {
                        console.warn("Geolocation error:", error.message);
                        resolve({ lat: null, lon: null });
                    }
                );
            });
        } catch (e) {
            console.error("Location fetch failed", e);
        }

       if (
    (coords.lat == null || coords.lon == null) &&
    @json(app()->environment('production'))
) {
    notify("error", "Please Allow Location to continue");
    $("#loginBtn").html("Sign In");
    $("#loginBtn").prop("disabled", false);
    return;
}

        const getBrowserInfo = () => {
            const ua = navigator.userAgent;
            let browser = "Unknown Browser";
            if (ua.includes("Chrome")) browser = "Chrome";
            else if (ua.includes("Firefox")) browser = "Firefox";
            else if (ua.includes("Safari")) browser = "Safari";
            else if (ua.includes("MSIE") || ua.includes("Trident")) browser = "Internet Explorer";

            let os = "Unknown OS";
            if (ua.includes("Win")) os = "Windows";
            else if (ua.includes("Mac")) os = "macOS";
            else if (ua.includes("Linux")) os = "Linux";
            else if (ua.includes("Android")) os = "Android";
            else if (ua.includes("like Mac")) os = "iOS";

            return { browser, os };
        };

        const deviceData = getBrowserInfo();
        const deviceName = navigator.platform || "Unknown Platform";

        const payload = {
            email,
            password,
            location: coords,
            browser: deviceData.browser,
            os: deviceData.os,
            device: deviceName,
            captcha: captchaToken
        };

        const response = await fetch(@json(route('api.user.login')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (response.status !== 200) {
            notify("error", result.message);
            $("#loginBtn").html('Sign In');
            $("#loginBtn").removeAttr("disabled");
            return;
        }

        if (result.status == "success" && result.otpRequired) {
            navigate(@json(route('verify-login')), true, () => {
                notify("success", "OTP Sent to your registered email & phone");
            });
        } else if (result.status == "success" && !result.otpRequired) {
            notify("success", result.message, () => {
                location.href = @json(route('dashboard'));
            });
        }

    } catch (error) {
        notify("error", "An error occurred while logging in");
        console.error("Login error:", error);
        $("#loginBtn").html('Sign In');
        $("#loginBtn").removeAttr("disabled");
    }
};
</script>

<style>

.card.custom-card {
            width: auto;
        }

        @media screen and (max-width: 768px) {
            .col-sm-0 {
                display: none !important;
            }

        }
        
        .cursor-pointer{
            cursor: pointer;
        }
        
        .form-control{
            border-radius: 13px;
            font-size: 15px;
        }
        
        .input-group button{
             border-radius: 0px 13px 13px 0px;
             background-color: transparent;
             border-left: 0px;
             border-color: var(--input-border);
        }
        
        .input-group:not(.phone) input{
            border-right: 0px;
        }
        
        .input-group.phone input{
            border-left: 0px;
        }
        
        .input-group.phone div{
             border-radius: 13px 0px 0px 13px !important;
             background-color: transparent;
             border-right: 0px;
             border-width: 1px;
             border-style: solid;
             border-color: var(--input-border) !important;
        }
        
        .line{
                border-top: 1px solid var(--input-border);
                width: 100%;
        }
    </style>

@include('includes.js')

</body>

</html>