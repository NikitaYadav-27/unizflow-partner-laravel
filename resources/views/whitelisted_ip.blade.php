<?php if (!isset($_GET['turbo'])) { $page = "ip"; echo view("header", get_defined_vars()); } ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-settings me-1 fs-15"></i>Settings</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-server me-1 fs-15"></i>Whitelisted IP</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-4 mb-2">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-server"></i> Whitelist IP</h6>
            </div>
            <div class="card-body">
                <form action="/api/whitelist-ip" method="POST" novalidate>
                    <div class="form-group">
                        <label class="mb-1">Server IP <span class="text-primary">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Server IP" required name="ip" validation="ip">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm mt-3 float-end"><i class="ti ti-circle-plus"></i>&nbsp;&nbsp;Whitelist</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-2">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-server"></i> Whitelisted IP's</h6>
            </div>
            <div class="card-body px-0 py-1">
                <div class="table-responsive">
                    <table class="table" id="dataTable">
                        <thead class="table-primary">
                            <tr>
                                <th>Sr</th>
                                <th>IP</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index=1; foreach((new \App\Models\IP())->where("user_id",$user['id'])->where("deleted",0)->get() as $ip) :  ?>
                              <tr>
                                  <td>{!! $index !!}</td>
                                  <td>{!! $ip['ip']; !!}</td>
                                  <td>{!! $ip['created_at']; !!}</td>
                                  <td>
                                      <button onclick="deleteIp('{!! bin2hex(openssl_encrypt($ip['id'], 'AES-256-CBC', env("encryption.key"), OPENSSL_RAW_DATA, substr(hash('sha256', env("encryption.key")), 0, 16))) !!}');" class="btn btn-sm btn-danger-transparent"><i class="ti ti-trash"></i></button>
                                  </td>
                              </tr>
                            <?php $index++; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

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
    // Proceed with deletion logic here
    confirmDelete(id);
  }
});
       }


function confirmDelete(id){
     $.post('/api/delete/ip',{
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
                        $.post("/api/delete-ip-verify",{ otp : otp },(data) => {
                          
                                notify("success",data.message,() => {
                                    navigate('/settings/whitelisted-ip');
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
                        $.post("/api/update-ip-verify",{ otp : otp },(data) => {
                          
                                notify("success",data.message,() => {
                                    navigate('/settings/whitelisted-ip');
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
    
    
      function load(){  
          $("#dataTable").dataTable(); 
      }
</script>

<script class="afterLoadScript" type="plain/text">
load();
</script>
<?php if (!isset($_GET['turbo'])) { echo view("footer", get_defined_vars()); } ?>