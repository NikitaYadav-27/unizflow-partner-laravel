@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'webhook'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-settings me-1 fs-15"></i>Settings</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-webhook me-1 fs-15"></i>Webhook</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-6 mb-2">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-arrow-down-left"></i> Payin Webhook URL</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('api.update-webhook.payin') }}" method="POST" novalidate>
                    <label class="mb-1">Webhook URL <span class="text-primary">*</span></label>
                    <input type="text" name="url" value="{!! $user['payin_webhook'] !!}" validation="url" class="form-control" placeholder="Enter URL" required>
                    <div class="invalid-feedback"></div>
                    
                    <button type="submit" class="btn btn-primary  float-end mt-3">Update <i class="ti ti-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-2">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-arrow-up-right"></i> Payout Webhook URL</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('api.update-webhook.payout') }}" method="POST" novalidate>
                    <label class="mb-1">Webhook URL <span class="text-primary">*</span></label>
                    <input type="text" name="url" value="{!! $user['payout_webhook'] !!}" validation="url" class="form-control" placeholder="Enter URL" required>
                    <div class="invalid-feedback"></div>
                    
                    <button type="submit" class="btn btn-primary  float-end mt-3">Update <i class="ti ti-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

    function onRequestResponse(response){
                  Swal.fire({
                     title: 'Enter OTP',
                     input: 'text',
                     inputLabel: 'We sent an OTP to your registered number',
                     inputPlaceholder: 'Enter 6-digit OTP',
                     inputAttributes: {
                       maxlength: 6,
                       autocapitalize: 'off',
                       autocorrect: 'off'
                     },
                     showCancelButton: true,
                     confirmButtonText: 'Verify',
                     cancelButtonText: 'Cancel',
                     inputValidator: (value) => {
                       if (!value) {
                         return 'You need to enter the OTP!';
                       }
                       if (!/^\d{6}$/.test(value)) {
                         return 'OTP must be 6 digits';
                       }
                     }
                   }).then((result) => {
                     if (result.isConfirmed) {
                        const otp = result.value;
                        $.post(@json(route('api.update-webhook.verify')),{ otp : otp },(data) => {
                          
                                notify("success",data.message,() => {
                                    
                                });
                            
                        }).fail((xhr) => {
                            notify("error",xhr.responseJSON.message);
                        });
                     }
                   });
    }
    
    function onRequestError(xhr){
        let error = xhr.responseJSON.message;
        notify("error",error);
    }

function deleteIp(id){
          Swal.fire({
  title: 'Are you sure?',
  text: "Do you want to delete this IP?",
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#d33',
  cancelButtonColor: '#3085d6',
  confirmButtonText: 'Yes, delete it!',
  cancelButtonText: 'Cancel'
}).then((result) => {
  if (result.isConfirmed) {
    
    confirmDelete(id);
  }
});
       }

function confirmDelete(id){
     $.post(@json(route('api.delete.ip')),{
         id : id
     },(data) => {
         
         Swal.fire({
                     title: 'Enter OTP',
                     input: 'text',
                     inputLabel: 'We sent an OTP to your registered number',
                     inputPlaceholder: 'Enter 6-digit OTP',
                     inputAttributes: {
                       maxlength: 6,
                       autocapitalize: 'off',
                       autocorrect: 'off'
                     },
                     showCancelButton: true,
                     confirmButtonText: 'Verify',
                     cancelButtonText: 'Cancel',
                     inputValidator: (value) => {
                       if (!value) {
                         return 'You need to enter the OTP!';
                       }
                       if (!/^\d{6}$/.test(value)) {
                         return 'OTP must be 6 digits';
                       }
                     }
                   }).then((result) => {
                     if (result.isConfirmed) {
                        const otp = result.value;
                        $.post(@json(route('api.delete-ip-verify')),{ otp : otp },(data) => {
                          
                                notify("success",data.message,() => {
                                    navigate(@json(route('settings.whitelisted-ip')));
                                });
                            
                        }).fail((xhr) => {
                            notify("error",xhr.responseJSON.message);
                        });
                     }
                   });
         
     }).fail((xhr) => {
        notify("error",xhr.responseJSON.message);
     });
}

</script>

@if (!request()->query->has('turbo'))
    @include('footer')
@endif