<?php if (!isset($_GET['turbo'])): ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>

<style>
    .daterangepicker .ranges li.active,
    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: rgb({!! env("colors.primary.rgb") !!}) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Show Password JS -->
<!-- <script src="/assets/js/show-password.js?v=2"></script> -->

<script src="/assets/libs/@popperjs/core/umd/popper.min.js"></script>



<script src="/assets/js/sticky.js"></script>

<script src="/assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="/assets/libs/chart.js/chart.min.js"></script>


<script src="/assets/js/custom.js?v=1994"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Datatables Cdn -->
<script src="https://cdn.datatables.net/2.2.0/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.0/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/responsive/3.0.4/css/responsive.bootstrap.css" />

<?php if(env("recaptcha.key")): ?>
<script src="https://www.google.com/recaptcha/api.js?render={!! e(env('recaptcha.key')) !!}"></script>
<?php endif; ?>

<script src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


<script src="/assets/libs/apexcharts/apexcharts.min.js"></script>




<style>
    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
        margin-right: 0.5em;
        display: inline-block;
        box-sizing: border-box;
        content: "+";
        text-align: left;
        padding: 1px 5px;
        border-radius: 50%;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input:-webkit-autofill {
        box-shadow: 0 0 0 1000px white inset !important;
        -webkit-box-shadow: 0 0 0 1000px white inset !important;
        -webkit-text-fill-color: #212529 !important;
        /* default text color */
    }

    /* Firefox */
    input[type="number"] {
        -moz-appearance: textfield;
    }


    table.table th,table.table td{
        padding: 7px 20px !important;
    }
    
    @media (max-width: 575.98px) {
      .sm-w-100 {
        width: 100% !important;
      }
    }
    
 .g-recaptcha {
    transform: scale(1); /* Adjust size if needed */
}


</style>



<script>

    window.navHistory = [];

    function validate(type, data) {
        switch (type) {
            case "first-last-name":
                if (/^[A-Za-z]+ [A-Za-z]+$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Name should contain first and last name" };
                }

            case "email":
                if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Invalid email format." };
                }

            case "phone":
                if (/^\d{10}$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Phone number should be exactly 10 digits." };
                }

            case "password":
                if (/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?#&])[A-Za-z\d@$!%*?#&]{8,}$/.test(data)) {
                    return { status: true };
                } else {
                    return {
                        status: false,
                        message: "Password must be at least 8 characters long and include 1 uppercase, 1 lowercase, 1 digit, and 1 special character."
                    };
                }
            case "ifsc" : 
                if (/^[A-Z]{4}0[A-Z0-9]{6}$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Invalid IFSC code format." };
                }

            case "account":
                if (/^\d{9,18}$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Account number should be between 5 to 18 digits." };
                }

            case "utr":
                if (/^\d{9,18}$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "UTR should be between 5 to 18 digits." };
                }

            case "ip":
                if (/^(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)){3}$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Invalid IP address format." };
                }
                
            case "url":
                if (/^(https?:\/\/)?([\w-]+(\.[\w-]+)+)([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Invalid URL format." };
                }
    

            case "amount":
                if (/^\d+(\.\d{1,2})?$/.test(data)) {
                    return { status: true };
                } else {
                    return { status: false, message: "Amount should be a valid number with up to 2 decimal places." };
                }

            case "re-account":
                let accountNumber = document.querySelector("#account_number").value;
                if (data === accountNumber) {
                    return { status: true };
                } else {
                    return { status: false, message: "Account numbers do not match." };
                }

            case "remarks":
                if (data.length <= 100) {
                    return { status: true };
                } else {
                    return { status: false, message: "Remarks should be less than 100 characters." };
                }
  
            default:
                return { status: false, message: "Invalid validation type provided." };
        }
    }


    function convertAllFormToAjax() {

        document.querySelectorAll("form:not(.no-ajax)").forEach((form) => {

            //validation part starts
            form.querySelectorAll('input[validation], select[validation], textarea[validation]').forEach((input) => {
                input.addEventListener('focus', () => {
                    input.dataset.touched = "true";
                });
                input.addEventListener('blur', () => {
                    if (input.dataset.touched === "true") {
                        let validation = input.getAttribute("validation");
                        if(validation == "ifsc"){
                           try{ ifsc_check(input) }catch(e){}
                        }
                        let result = validate(validation, input.value);
                        if (result.status) {
                            $(input).removeClass("is-invalid");
                            $(input).siblings(".invalid-feedback").html("");
                        } else {
                            $(input).addClass("is-invalid");
                            $(input).siblings(".invalid-feedback").html(result.message);
                        }
                    }

                    input.oninput = () => {
                        if (input.className.includes("is-invalid")) {
                            let validation = input.getAttribute("validation");
                            let result = validate(validation, input.value);
                            if (result.status) {
                                $(input).removeClass("is-invalid");
                                $(input).siblings(".invalid-feedback").html("");
                          
                            }
                        }

                    }
                });
            });
            //validation part ends

            form.onsubmit = (e) => {
                e.preventDefault();
                
                //check for validation
                let isValid = true;
                form.querySelectorAll('input[validation], select[validation], textarea[validation]').forEach((input) => {
                    if (input.dataset.touched === "true") {
                        let validation = input.getAttribute("validation");
                        let result = validate(validation, input.value);
                        if (!result.status) {
                            isValid = false;
                            $(input).addClass("is-invalid");
                            $(input).siblings(".invalid-feedback").html(result.message);
                        } else {
                            $(input).removeClass("is-invalid");
                            $(input).siblings(".invalid-feedback").html("");
                        }
                    }
                });

                if (!isValid) {
                    return false;
                }
                //check for validation ends

                //check for empty fields
                form.querySelectorAll('input[required], select[required], textarea[required]').forEach((input) => {
                    if (input.value == "") {
                        isValid = false;
                        $(input).addClass("is-invalid");
                        $(input).siblings(".invalid-feedback").html("This field is required.");
                    } else {
                        $(input).removeClass("is-invalid");
                        $(input).siblings(".invalid-feedback").html("");
                    }
                });
                //check for empty fields ends
                if (!isValid) {
                    return false;
                }


                form.querySelector("button[type=submit]").disabled = true;
                let buttonText = form.querySelector("button[type=submit]").innerHTML;
                form.querySelector("button[type=submit]").innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                if (form.getAttribute("captcha") && "{!! e(env('recaptcha.key')) !!}" !== "") {
                    grecaptcha.ready(function () {
                        grecaptcha.execute('{!! e(env('recaptcha.key')) !!}', { action: 'submit' }).then(function (token) {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'captcha';
                            input.value = token;
                            form.appendChild(input);
                            submitForm(form, buttonText);
                        });
                    });

                } else {
                    submitForm(form, buttonText);
                }



            }
        });

    }

    function submitForm(form, buttonText) {

        const formData = $(form).serializeArray();
        const jsonData = {};
        formData.forEach(field => {
            jsonData[field.name] = field.value;
        });
        $.ajax({
            url: form.getAttribute('action'),
            method: form.method,
            contentType: 'application/json',
            data: JSON.stringify(jsonData),
            success: function (response) {
                try {
                    onRequestResponse(response);
                } catch (e) { console.log("error", e); }
                form.querySelector("button[type=submit]").disabled = false;
                form.querySelector("button[type=submit]").innerHTML = buttonText;
            },
            error: function (xhr, status, error) {
                try {
                    onRequestError(xhr);
                } catch (e) { console.log("error", e); }
                form.querySelector("button[type=submit]").disabled = false;
                form.querySelector("button[type=submit]").innerHTML = buttonText;
            }
        });

    }


    function notify(status, msg, callback = null) {


        Swal.fire({
            title: msg,
            icon: status
        }).then((result) => {
            if (result.isConfirmed) {
                if (callback != null) {
                    callback();
                }
            }
        });

    }

    window.first = <?php if (session()->get("user_id")) {
        echo "false";
    } else {
        echo "true";
    } ?>;

    function navigate(path, complete = false, callback = null) {
        let postData = {
            "turbo": "1"
        };

        if (window.first) {
            postData.first = 1;
            window.first = false;
        }
        try{
         window.intervals.forEach((interval) => {
            clearInterval(interval);
         });
        }catch(e){}




        $.get(path, postData, (data) => {
            if (complete) {
                document.documentElement.innerHTML = data;

                setTimeout(() => {
                    try {
                        document.querySelectorAll("script").forEach((old_script) => {
                            let script = document.createElement("script");

                            if (old_script.className != "afterLoadScript") {
                                if (old_script.src) {
                                    script.src = old_script.src;
                                    old_script.remove();
                                    document.body.append(script);

                                } else {

                                    script.innerHTML = old_script.innerHTML;
                                    document.body.append(script);
                                    old_script.remove();
                                }

                            }


                        });
                    } catch (e) { }



                    try {
                        document.querySelectorAll("link").forEach((old_link) => {
                            let link = document.createElement("link");
                            link.href = old_link.href;
                            link.rel = "stylesheet";
                            old_link.remove();
                            document.head.append(link);
                        });

                    } catch (e) { }

                    executeComponentScript();
                    convertAllFormToAjax();

                    setTimeout(() => {
                        try {
                            if (callback != null) {
                                callback();
                            }
                        } catch (e) {
                            console.log("Error", e);
                        }
                    }, 50);

                    setTimeout(() => {
                        let loader = document.querySelector("#loader");
                        if (loader) {
                            loader.remove();
                        }
                    }, 500);




                }, 50);


            } else {

                if(data.includes('login-page-identifier'))
                {
                    location.href='/';
                    return false;
                }

                window.navHistory.push(path);
                document.querySelector("#innerBody").innerHTML = data;


                document.querySelectorAll("#innerBody script").forEach((old_script) => {
                    let script = document.createElement("script");
                    if (old_script.className != "afterLoadScript") {
                        script.innerHTML = old_script.innerHTML;
                        document.querySelector("#innerBody").append(script);
                        old_script.remove();
                    }


                });

                executeComponentScript();
                convertAllFormToAjax();
                try {
                    document.querySelector("#loader").style = "display: none !important;";
                } catch (e) { }

                setTimeout(() => {
                    try {
                        if (callback != null) {
                            callback();
                        }
                    } catch (e) {
                        console.log("Error", e);
                    }
                }, 50);


            }

            setTimeout(() => {
                let loader = document.querySelector("#loader");
                if (loader) {
                    loader.remove();
                }
            }, 500);
        });

        try {
            document.querySelectorAll(".side-menu__item").forEach((element) => {
                if (element.getAttribute("onclick")?.includes(path)) {
                    activeNavigation(element);
                }
            });

        } catch (e) {
            //console.log("Error",e);
        }

        history.pushState({}, "", path);

    }


    function closeDropdown(ele) {
        ele.parentElement.parentElement.className = ele.parentElement.parentElement.className.replace("show", "");
    }

    function activeNavigation(ele) {
        $(".side-menu__item").removeClass("active");
        ele.className = 'side-menu__item active';
        if (document.documentElement.clientWidth <= 992) {
            //hide sidebar
            document.querySelector("html").setAttribute("data-toggled", "close");
            document.querySelector("#responsive-overlay").className = '';
        }


    }


    window.onload = function () {
        executeComponentScript();
        convertAllFormToAjax();
        setTimeout(() => {
            let loader = document.querySelector("#loader");
            if (loader) {
                loader.remove();
            }
        }, 500);
        window.onloads.forEach((func) => {
            try { func(); } catch (e) { }
        });


    }


    function executeComponentScript() {
        try { $('select:not(.no-select2)').select2(); } catch (e) { }
        let scriptComponent = document.querySelector('.afterLoadScript');
        if (scriptComponent) {
            try {
                eval(scriptComponent.innerHTML);
            } catch (e) { console.log("Error", e); }

        }

    }

    window.addEventListener('popstate', function (event) {
        navigate(document.location.pathname);
    });

</script>