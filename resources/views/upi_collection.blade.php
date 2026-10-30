@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'upi'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-inbox me-1 fs-15"></i>Collection</a>
        </li>
        <li class="breadcrumb-item" aria-current="page"><i class="ti ti-link me-1 fs-15"></i>UPI Collection</li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-qrcode me-1 fs-15"></i>Generate QR</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-qrcode me-1 fs-15"></i> Generate QR</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('api.upi.generate') }}" method="POST" novalidate>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Customer Name <span class="text-primary">*</span></label>

                                <input validation="first-last-name" type="text" required
                                    class="form-control rounded-end" id="customer_name" name="customer_name"
                                    placeholder="Customer Name">
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Customer Phone <span class="text-primary">*</span></label>
                                <input validation="phone" type="text" class="form-control rounded-end"
                                    id="customer_phone" name="customer_phone" placeholder="Customer Phone" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Customer Email <span class="text-primary">*</span></label>

                                <input validation="email" type="text" class="form-control rounded-end"
                                    id="customer_email" name="customer_email" placeholder="Customer Email" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Expiry Time <span class="text-primary">*</span></label>

                                <input type="text" disabled class="form-control" value="15 minutes" required>

</div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Amount <span class="text-primary">*</span></label>

                                <input validation="amount" type="text" class="form-control rounded-end"
                                    id="amount" name="amount" placeholder="Amount" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Remarks <span class="text-primary"></span></label>

                                <textarea validation="remarks" type="text" class="form-control rounded-end"
                                    id="remarks" name="remarks" placeholder="Remarks"></textarea>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>
                    </div>

                    <button class="btn btn-danger float-end mt-3" type="submit"><i class="ti ti-qrcode"></i>&nbsp;Generate QR</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-qrcode me-1 fs-15"></i> QR Code Preview</h5>
            </div>
            <div class="card-body">
                
                <p class="text-muted text-center my-4 d-block" id="initTitle">The generated QR code will appear here.</p>
                
                <div id="qrBox" class="d-none flex-column justify-content-center align-items-center w-100">
                     <div class="d-flex justify-content-center w-75">
                        <img class="w-50 mb-3" src="/assets/bhim.png">
                        
                        <img class="w-50 mb-3" src="/assets/upi-n.png">
                     </div>
                     
                     <img id="qrCode" src="" class="w-50">
                     
                     <p class="text-primary mt-4" style="margin-block-end: 0em;">Time Remaining</p>
                     <h5 class="mt-0 text-primary" id="countDown" style="font-weight: bold;"></h5>
                     
                      <div class="d-flex justify-content-center w-100 gap-2 mt-3">
                       <button id="downloadQrBtn" class="btn btn-outline-primary w-50"><i class="ti ti-download"></i>&nbsp;&nbsp;Download QR</button>
                       <button id="copyLinkBtn" class="btn btn-primary w-50"><i class="ti ti-link"></i>&nbsp;&nbsp;Copy Link</button>
                      </div>
                </div>
                
            </div>
        </div>
    </div>

</div>

<script>
        window.paymentLink = "";
        function startMoniter(intent,txnid)
        {
             $("#qrBox").removeClass("d-none").addClass("d-flex");
             $("#initTitle").addClass("d-none").addClass("d-block");
             
             document.querySelector("#downloadQrBtn").onclick = () => {
                 downloadImage("https://api.qrserver.com/v1/create-qr-code/?size=150x150&data="+encodeURIComponent(intent)+"&color=185765", 'QR.png');
             }
             
             document.querySelector("#copyLinkBtn").onclick = () => {
                 let btn = document.querySelector("#copyLinkBtn");
                 navigator.clipboard.writeText(window.paymentLink)
                 .then(() => {
                    btn.innerHTML = 'Copied'
                    setTimeout(() => {
                        btn.innerHTML = '<i class="ti ti-link"></i>&nbsp;&nbsp;Copy Link';
                    },1000);
                 })
                 .catch(err => {
                     console.error('Failed to copy:', err);
                 });
             }

document.querySelector("#qrCode").src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data="+encodeURIComponent(intent)+"&color=185765";
             let remainingTime = 900;
             let interval = setInterval(() => {
                 remainingTime--;
                 let min = parseInt(remainingTime/60);
                 let sec = parseInt(remainingTime%60);
                 if(min < 10) { min = "0"+min; }
                 if(sec < 10) { sec = "0"+sec; }
                 $("#countDown").html(min+":"+sec);
                 if(remainingTime <= 0) { 
                     clearInterval(interval); 
                     notify("error", "QR Code Expired");  
                     $("#qrBox").addClass("d-none").removeClass("d-flex");
                     $("#initTitle").addClass("d-block").removeClass("d-none");
                 }
             },1000);
             
             let interval2 = setInterval(() => {
                 $.post(@json(route('api.transaction.status')),{
                     txnid : txnid
                 },(data) => {
                     if(data.status == "Success")
                     {
                          notify("success", "Transaction Successful !");  
                           $("#qrBox").addClass("d-none").removeClass("d-flex");
                     $("#initTitle").addClass("d-block").removeClass("d-none");
                     clearInterval(interval2);
                     clearInterval(interval); 
                     }else if(data.status == "Expired")
                     {
                          notify("error", "QR Code Expired !"); 
                           $("#qrBox").addClass("d-none").removeClass("d-flex");
                     $("#initTitle").addClass("d-block").removeClass("d-none");
                     clearInterval(interval2);
                     clearInterval(interval); 
                     }else if(data.status == "Failed")
                     {
                          notify("error", "Transaction Failed !");  
                           $("#qrBox").addClass("d-none").removeClass("d-flex");
                     $("#initTitle").addClass("d-block").removeClass("d-none");
                     clearInterval(interval2);
                     clearInterval(interval); 
                     }

});
             },5000);
             
             window.intervals.push(interval);
             window.intervals.push(interval2);
        }
        
        function downloadImage(url, filename = 'downloaded-image.jpg') {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                })
                .catch(console.error);
        }

function onRequestResponse(response) {
            notify("success",response.message);
            startMoniter(response.data.intent,response.data.txnid)
            window.paymentLink = response.data.payment_link;
            try{document.querySelector("form").reset();}catch(e){}
            
        }

        function onRequestError(error) {
           notify("error",error.responseJSON.message);
        }
</script>
@if (!request()->query->has('turbo'))
    @include('footer')
@endif