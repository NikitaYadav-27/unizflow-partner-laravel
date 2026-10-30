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
                        <div class="card col-xxl-9 col-lg-9 col-md-9 col-sm-12">
                            <div class="card-body p-5">
                            <div class="d-flex  justify-content-between align-items-center mb-4">
                                    <img src="{!! e(env('logo')) !!}" alt="logo"
                                        style="width: auto; height: 70px;" class="desktop-logo">
                                    <div class="d-flex flex-column">
                                       <h6 class="text-primary mb-0">Register</h6>
                                       <p class="text-muted mb-0 fs-10">Register your account.</p>
                                    </div>
                                </div>

<form action="{{ route('api.user.register') }}" method="POST" autocomplete="off" captcha="true">
                                    <div class="row">
                                        
                                        <div class="col-xl-12 mb-2">
                                            <label for="signin-username" class="form-label text-default mb-1">Name</label>
                                            <input validation="first-last-name"  autocomplete="new-email" type="text" name="name"
                                                class="form-control form-control-lg" id="signin-name"
                                                placeholder="Enter your Name" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-xl-12 mb-2">
                                            <label for="signin-username" class="form-label text-default mb-1">Phone</label>
                                            <input validation="phone" autocomplete="new-email" type="number" name="phone"
                                                  class="form-control form-control-lg" id="signin-phone"
                                                  placeholder="Enter your Phone" required>
                                            <div class="invalid-feedback"></div>
                                        </div>

<div class="col-xl-12 mb-2">
                                            <label for="signin-username" class="form-label text-default">Email</label>
                                            <input validation="email" autocomplete="new-email" type="email" name="email"
                                                class="form-control form-control-lg" id="signin-email"
                                                placeholder="Enter you email" required>
                                            <div class="invalid-feedback"></div>
                                        </div>

<div class="col-xl-12 mb-2">
                                            <label for="signin-password"
                                                class="form-label text-default d-block">Password</label>
                                            
                                                <input validation="password" autocomplete="new-password" type="password" name="password"
                                                    class="form-control form-control-lg" id="signin-password"
                                                    placeholder="Enter you password" required>
                                        
                                                <div class="invalid-feedback"></div>
                                            <div class="mt-3">
                                                <div class="form-check">
                                                    <input required class="form-check-input" name="agree" type="checkbox"
                                                        value="1" id="defaultCheck1">
                                                    <label class="form-check-label text-muted fw-normal"
                                                        for="defaultCheck1">
                                                        I Agree with Terms & Conditions 
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

<div class="col-xl-12 d-grid mt-2">
                                            <button type="submit" id="submitBtn" class="btn btn-lg btn-danger rounded-pill">Create new Account</button>
                                        </div>
                                        
                                         <div class="col-xl-12 d-grid mt-3">
                                            <p class="text-center">Already have account ? <a class="text-primary cursor-pointer" onclick="navigate('{{ route('login') }}',true)">Login</a></p>
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

    <script>
        var recaptchaWidget;

        function onRequestResponse(response) {
           navigate('{{ route('verify-registration') }}',true,() => {
               notify("success","Please Verify your Email to continue");
           });
        }

        function onRequestError(error) {
           notify("error",error.responseJSON.message);
           let btn = document.querySelector("button[type=submit]");
           if (btn) { btn.disabled = false; btn.innerHTML = 'Create new Account'; }
        }

function onCaptchaSuccess(token) {
        
            $("#submitBtn").removeAttr("disabled");
        }

        function onCaptchaExpired() {
            $("#submitBtn").attr("disabled", "true");
        }

</script>

    <style>

.card.custom-card {
            width: auto;
        }
        
        input:-webkit-autofill {
    background-clip: padding-box;
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