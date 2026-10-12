<!DOCTYPE html>
<html style="--primary-rgb: {!! e(env('colors.primary.rgb')) !!};" lang="en" dir="ltr" data-nav-layout="vertical"
    data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light"
    data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Password - {!! env('app.title') !!}</title>
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
                                       <h6 class="text-primary mb-0">Reset Password</h6>
                                       <p class="text-muted mb-0 fs-10">Reset Your Password.</p>
                                    </div>
                                </div>
                                
                               

                                
                                
                                <form action="/api/auth/reset-password/update" novalidate method="POST" captcha="true">
                                    <div class="row">
                                        
                                        <div class="form-group mb-2">
                                                <label for="signin-password" class="form-label text-default d-block">New Password</label>
                                            
                                                <input validation="password" autocomplete="new-password" type="password" name="npassword"
                                                    class="form-control form-control-lg" id="nsignin-password"
                                                    placeholder="Enter New Password" required>
                                        
                                                <div class="invalid-feedback"></div>
                                            
                                        </div>
                                        <input type="hidden" name="token" value="{!! $user['reset_password_token'] !!}">
                                        <div class="form-group mb-2">
                                                <label for="signin-password" class="form-label text-default d-block">Confirm Password</label>
                                            
                                                <input validation="password" autocomplete="confirm-password" type="password" name="cpassword"
                                                    class="form-control form-control-lg" id="csignin-password"
                                                    placeholder="Enter Confirm Password" required>
                                        
                                                <div class="invalid-feedback"></div>
                                            
                                        </div>
                                        
                                        
                                       


                                        <div class="col-xl-12 d-grid mt-2">
                                            <button type="submit" id="loginBtn" class="btn btn-lg btn-primary rounded-pill">Update Password</button>
                                        </div>
                                        
                                         <div class="col-xl-12 d-grid mt-3">
                                            <p class="text-center">Password Remembered ? <a class="text-primary cursor-pointer" onclick="navigate('/user/login',true)">Login</a></p>
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


        function onRequestResponse(response) {
          navigate("/user/login",true,() => {
              notify("success",response.message);
          });
        }

        function onRequestError(error) {
           notify("error",error.responseJSON.message);
           let btn = document.querySelector("button[type=submit]");
           if (btn) { btn.disabled = false; btn.innerHTML = 'Update Password'; }
        }


</script>


    <style>
    

        .card.custom-card {
            width: auto;
        }

        /* css for devices width less than 768px */
        @media screen and (max-width: 768px) {
            .col-sm-0 {
                display: none !important;
            }

        }
        
        .cursor-pointer{
            cursor: pointer;
        }
        
        .form-control{
            /*padding: 12px 20px;*/
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


    <?php echo view('includes/js', get_defined_vars()); ?>


</body>

</html>