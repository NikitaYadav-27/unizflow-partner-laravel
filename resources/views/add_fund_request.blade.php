@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'topup'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-wallet me-1 fs-15"></i>Wallet</a>
        </li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-circle-plus me-1 fs-15"></i>Topup Wallet</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-upload me-1 fs-15"></i>Add Request</li>
    
    </ol>
</nav>

<div class="card custom-card">
    <div class="card-header">
        <h6 class="card-title">Request Fund</h6>
    </div>
    <div class="card-body">
         <form action="{{ route('api.wallet.add-topup-request') }}"  method="POST" novalidate>
          <div class="row">
           
           <div class="col-lg-4">
          <div class="form-group mb-2">
              <label>Bank Account * </label>
              <select class="form-control noselect2" name="bank"  required>
                 <option value="">Select Account</option>
                 @foreach($banks as $bank)
                  <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                 @endforeach
              </select>
              <div class="invalid-feedback"></div>
          </div>
          
          </div>
          <div class="col-lg-4">
        
          <div class="form-group mb-2">
              <label>Transaction mode *</label>
              <select class="form-control noselect2" name="mode" required>
                 <option value="">Select mode</option>
                 <option value="IMPS">IMPS</option>
                 <option value="NEFT">NEFT</option>
                 <option value="RTGS">RTGS</option>
                 <option value="UPI">UPI BANK TRANSFER</option>
                 <option value="CASH">CASH DEPOSIT</option>
              </select>
              <div class="invalid-feedback"></div>
          </div>
          
            </div>
          <div class="col-lg-4">
          
          <div class="form-group mb-2">
              <label>Reference Number *</label>
              <input class="form-control" placeholder="Reference Number" name="utr" required>
              <div class="invalid-feedback"></div>
          </div>
          
            </div>
          <div class="col-lg-4">
          
          <div class="form-group mb-2">
              <label>Amount *</label>
              <input class="form-control" validation="amount" type="number" min="100" placeholder="Amount" name="amount" required>
               <div class="invalid-feedback"></div>
          </div>
          
           </div>
          <div class="col-lg-4">
          <div class="form-group mb-2">
              <label>Remark</label>
              <input class="form-control" validation="remarks" placeholder="Remarks" name="remarks">
               <div class="invalid-feedback"></div>
          </div>
          
            </div>
          <div class="col-lg-4">
              
           <div class="form-group mb-2">
              <label>Receipt * </label>
              <input class="form-control" onchange="fileUploaded(this)" type="file" name="receipt" required accept=".png,.jpg,.jpeg,.pdf">
           </div>
            </div>
            
            <input type="hidden" required name="receipt" id="file">

</div>

<button class="btn btn-primary float-end mt-2" type="submit" disabled id="ctbtn">Continue</button>
        </form>
    </div>
</div>

<script>

function onRequestResponse(response)
      {
         notify("success",response.message,() => {
             navigate(@json(route('wallet.topup')));
         });
      }

function onRequestError(error) {
          notify("error",error.responseJSON.message);
       }

function fileUploaded(input) {
    document.querySelector("#ctbtn").disabled = true;
    const file = input.files[0];
    if (!file) return;

const allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
    const fileExtension = file.name.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alertt("error", "Invalid file type. Please upload PNG, JPG, JPEG, or PDF files only.");
        input.value = ""; 
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {
        document.querySelector("#ctbtn").disabled = false;
        const base64String = e.target.result; 
        $("#file").val(base64String);
    };

reader.readAsDataURL(file);
}

</script>

@if (!request()->query->has('turbo'))
    @include('footer')
@endif