@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'key'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-settings me-1 fs-15"></i>Settings</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-key me-1 fs-15"></i>API Keys</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-5">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-key"></i> API Keys</h6>
            </div>
            <div class="card-body">
                
            <div class="form-group col-lg-12 col-sm-12 col-md-12 mb-2">
                <label class="mb-1">Merchant ID</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{!! $user['merchant_id']; !!}">
                    <button onclick="copyKey('{!! $user['merchant_id']; !!}',this);" class="btn text-primary border-primary" type="button"><i class="ti ti-copy"></i></button>
                </div>
            </div>

<div class="form-group col-lg-12 col-sm-12 col-md-12 mb-2">
                <label class="mb-1">Client Secret</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{!! $user['api_access_key']; !!}">
                    <button onclick="copyKey('{!! $user['api_access_key']; !!}',this);" class="btn text-primary border-primary" type="button"><i class="ti ti-copy"></i></button>
                </div>
            </div>

<div class="form-group col-lg-12 col-sm-12 col-md-12 mb-2">
                <label class="mb-1">Encryption Key</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{!! $key['aes_key']; !!}">
                    <button onclick="copyKey('{!! $key['aes_key']; !!}',this);" class="btn text-primary border-primary" type="button"><i class="ti ti-copy"></i></button>
                </div>
            </div>
            
            <div class="form-group col-lg-12 col-sm-12 col-md-12 mb-2">
                <label class="mb-1">Encryption IV</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{!! $key['aes_iv']; !!}">
                    <button onclick="copyKey('{!! $key['aes_iv']; !!}',this);" class="btn text-primary border-primary" type="button"><i class="ti ti-copy"></i></button>
                </div>
            </div>
            
              <div class="mt-5 d-flex justify-content-between">
                 <button onclick="regenerateKey();" class="btn text-primary border-primary"><i class="ti ti-history"></i> &nbsp;&nbsp;Regenerate Secret</button>
                  <button onclick="window.open('{!! env("api.docs") !!}');" class="btn btn-primary"><i class="ti ti-file-code"></i> &nbsp;&nbsp;API Documents</button>
               </div>
            </div>
        </div>
    </div>

</div>

<script>
    function regenerateKey()
    {
       Swal.fire({
         title: 'Are you sure?',
         text: "Your previous API key will stop working!",
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#d33',
         cancelButtonColor: '#3085d6',
         confirmButtonText: 'Yes, regenerate it!',
         cancelButtonText: 'Cancel'
       }).then((result) => {
         if (result.isConfirmed) {
            $.get(@json(route('api.regenerate-api-key.send-otp')),{},(data) => {
                
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
                        $.post(@json(route('api.regenerate-api-key.verify-otp')),{ otp : otp },(data) => {
                            
                                notify("success",data.message,() => {
                                    navigate(@json(route('settings.api-keys')));
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
       });    
    }
    
    function copyKey(text,btn)
    {
        btn.innerHTML = '<i class="ti ti-check"></i>';
        navigator.clipboard.writeText(text)
        .then(() => {
          console.log('Text copied to clipboard:', text);
        })
        .catch(err => {
          console.error('Failed to copy:', err);
        });
        setTimeout(() => {
             btn.innerHTML = '<i class="ti ti-copy"></i>';
        },1000);
    }
</script>

<style>
    .border-primary{
        border-color: rgb(var(--primary-rgb)) !important;
    }
</style>
@if (!request()->query->has('turbo'))
    @include('footer')
@endif
