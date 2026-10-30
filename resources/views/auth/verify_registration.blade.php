<!DOCTYPE html>
<html style="--primary-rgb: {!! e(env('colors.primary.rgb')) !!};" lang="en" dir="ltr" data-nav-layout="vertical"
    data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light"
    data-toggled="close">

<head>

<meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Register - {!! env('app.title') !!}</title>
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
                                        <div class="mb-5">
                                            <img src="/assets/login-feature.png" class="w-75 h-auto" alt="">
                                        </div>
                                        <h6 class="fw-semibold text-fixed-white">Your Trusted Fintech Provider</h6>
                                        <p class="fw-normal fs-14 op-7"> Register your secure {!! env('app.title') !!} account to manage transactions, monitor real-time payments, and access insights for smarter financial decisions. Our payment gateway ensures smooth, reliable, and safe transactions tailored to your business needs. </p>
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
                        <div class="card col-xxl-9 col-lg-9 col-md-9 col-sm-12">
                            <div class="card-body p-5">
                            <div class="d-flex  justify-content-between align-items-center mb-4">
                                    <img src="{!! e(env('logo')) !!}" alt="logo"
                                        style="width: auto; height: 70px;" class="desktop-logo">
                                    <div class="d-flex flex-column">
                                       <h6 class="text-primary mb-0">Verification</h6>
                                       <p class="text-muted mb-0 fs-10">Verify Email.</p>
                                    </div>
                                </div>

<form action="{{ route('api.user.register.verify') }}" method="POST" autocomplete="off" captcha="true">
                                    <div class="row">

<div class="col-xl-12 mt-2">
                                            <label for="signin-username" class="form-label text-default mb-1">Email</label>
                                            <div class="input-group email">

<input disabled value="{{ session()->get('user-registration-data')['email'] ?? '' }}" autocomplete="new-email" type="email" name="email"
                                                class="form-control form-control-lg" id="signin-email"
                                                placeholder="Enter you email" required>
                                                <div class="d-flex gap-2 align-items-center justify-content-center px-2" style="font-size: 15px;">
                                                    <button type="button" id="emailOtpSendBtn" onclick="sendOTP(this,'email');" class="badge bg-primary border-0">send OTP</button>
                                                </div>
                                            </div>
                                        </div>

<div class="col-xl-12 mt-2">
                                            <label for="signin-username" class="form-label text-default mb-1">Email OTP</label>
                                            <input disabled oninput="checkOTP();" autocomplete="new-email" type="number" name="email_otp" class="form-control form-control-lg" 
                                                  placeholder="Enter otp received on your email" required>
                                              
                                        </div>

<div class="col-xl-12 d-grid mt-4">
                                            <button disabled type="submit" id="submitBtn" class="btn btn-lg btn-primary">Continue</button>
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
    
     <script type="plain/text" class="afterLoadScript">
            sendOTP(document.querySelector("#emailOtpSendBtn"),'email',true);
    </script>

<script>
        var recaptchaWidget;

        function onRequestResponse(response) {
            navigate('{{ route('login') }}',true,() => {
                notify("success",response.message);
            });
        }

        function onRequestError(error) {
           notify("error",error.responseJSON.message);
           let btn = document.querySelector("button[type=submit]");
           if (btn) { btn.disabled = false; btn.innerHTML = 'Continue'; }
        }

function checkOTP()
        {
            let emailOTP = document.querySelector("input[name=email_otp]").value;
            if(emailOTP.length == 6)
            {
                document.querySelector("#submitBtn").disabled = false;
            }else{
                document.querySelector("#submitBtn").disabled = true;
            }
        }
        
        function sendOTP(btn,source,skipAlert = false)
        {   
            btn.disabled = true;
            btn.innerHTML = 'Sending..';
            let time = 100;
            $.post(@json(route('api.user.register.send-otp')),{ source : source },(data) => {

                    document.querySelector("input[name="+source+"_otp]").disabled = false;
                    let i = setInterval(() => {
                        time--;
                        if(time == 0)
                        {
                            btn.disabled = false;
                            btn.innerHTML = 'Resend OTP';
                            
                            clearInterval(i);
                           
                        }else{
                             btn.innerHTML = formatTime(time);
                        }
                       
                    },1000);
                    
                    if(!skipAlert)
                    {
                         notify("success", data.msg);
                    }
                 
            }).fail((error) => {
                notify("error",error.responseJSON.message);
            });;
        }
        
        function formatTime(seconds) {
           const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
           const secs = String(seconds % 60).padStart(2, '0');
           return `${mins}:${secs}`;
        }

</script>

    <style>

.input-group button{

        }
        
        .input-group:not(.phone) input{
            border-right: 0px;
        }
        
        .input-group.phone input{
            border-left: 0px;
            border-right: 0px !important;
        }
        
        .input-group.phone div:first-child{
             border-radius: 13px 0px 0px 13px !important;
             background-color: transparent;
             border-right: 0px;
             border-width: 1px;
             border-style: solid;
             border-color: var(--input-border) !important;
             background-color: rgb(var(--light-rgb));
             color: var(--default-text-color);
        }
        
        .input-group.phone div:last-child{
             border-radius: 0px 13px 13px 0px !important;
             background-color: transparent;
             border-left: 0px !important;
             border-width: 1px;
             border-style: solid;
             border-color: var(--input-border) !important;
             background-color: rgb(var(--light-rgb));
             color: var(--default-text-color);
        }

.input-group:not(.email) input{
            border-left: 0px;
        }
        
        .input-group.email input{
            border-right: 0px;
        }
        
        .input-group.email div{
             border-radius: 0px 13px 13px 0px !important;
             background-color: transparent;
             border-left: 0px;
             border-width: 1px;
             border-style: solid;
             border-color: var(--input-border) !important;
             background-color: rgb(var(--light-rgb));
             color: var(--default-text-color);
        }
        
        .line{
                border-top: 1px solid var(--input-border);
                width: 100%;
        }

</style>

@include('includes.js')

</body>

</html>