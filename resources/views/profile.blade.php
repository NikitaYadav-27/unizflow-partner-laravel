<?php if (!isset($_GET['turbo'])) { $page = "profile"; echo view("header", get_defined_vars()); } 

$db = \Illuminate\Support\Facades\DB::connection();


?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
   
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-user me-1 fs-15"></i>Profile</li>
    </ol>
</nav>

<!-- Profile Header -->

<div class="row">
    <div class="col-lg-4">
        <div class="card custom-card overflow-hidden">
                            <div class="card-body p-0">
                                <div class="d-sm-flex align-items-top p-4 border-bottom-0 main-profile-cover">
                                    <div>
                                        <span class="avatar avatar-xxl avatar-rounded online me-3 bg-white text-primary fw-bold">
                                            <?php 
                                  //initials of name
                                    $name = $user['name'];
                                    $name = explode(" ", $name);
                                    $initials = "";
                                    foreach ($name as $n) {
                                        $initials .= strtoupper($n[0]);
                                    }
                                    echo $initials;
                                ?>
                                        </span>
                                    </div>
                                    <div class="flex-fill main-profile-info">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fw-semibold mb-1 text-fixed-white">{!! $user['name']; !!}</h6>
                                            
                                        </div>
                                        <p class="mb-1 text-muted text-fixed-white op-7">{!! $user['email']; !!}</p>
                                        <p class="fs-12 text-fixed-white mb-4 op-5">
                                            <span class="me-3"><i class="ri-profile-line me-1 align-middle"></i>{!! $user['merchant_id']; !!}</span>
                                            <span><i class="ri-smartphone-line me-1 align-middle"></i>+91 {!! $user['phone']; !!}</span>
                                        </p>
                                        <div class="d-flex mb-0">
                                            <div class="me-4">
                                                <p class="fw-bold fs-20 text-fixed-white text-shadow mb-0">₹ {!! $user['wallet'] !!}</p>
                                                <p class="mb-0 fs-11 op-5 text-fixed-white"><i class="ri-wallet-2-line"></i> Wallet Balance</p>
                                            </div>
                                          
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 border-bottom border-block-end-dashed">
                                    <p class="fs-15 mb-2 me-4 fw-semibold">Contact Information :</p>
                                    <div class="text-muted">
                                        
                                        <p class="mb-2">
                                            <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-profile-line align-middle fs-14"></i>
                                            </span>
                                           {!! $user['merchant_id'] !!}
                                        </p>
                                        
                                        <p class="mb-2">
                                            <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-mail-line align-middle fs-14"></i>
                                            </span>
                                           {!! $user['email']; !!}
                                        </p>
                                        <p class="mb-0">
                                            <span class="avatar avatar-sm avatar-rounded me-2 bg-primary-transparent ">
                                                <i class="ri-phone-line align-middle fs-14"></i>
                                            </span>
                                            +91  {!! $user['phone']; !!}
                                        </p>
                                        
                                    </div>
                                </div>
                                
                                
                                
                              
                                
                                
                               
                            </div>
                        </div>
    </div>
    <div class="col-lg-8">
        <div class="card custom-card" >
                         
                            <div class="card-body">
                                <ul class="nav nav-pills justify-content-start nav-style-2 mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#overview" aria-selected="true"><i class="ti ti-report"></i>&nbsp;Overview</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#commercials" aria-selected="false"><i class="ti ti-webhook"></i>&nbsp;Commercials</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#loginHistory" aria-selected="false"><i class="ti ti-history"></i>&nbsp;Login History</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#apiKeys" aria-selected="false"><i class="ti ti-key"></i>&nbsp;API Keys</a>
                                    </li>
                                    
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#ipWhiteListing" aria-selected="false"><i class="ti ti-server"></i>&nbsp;IP</a>
                                    </li>
                                    
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page"
                                        href="#Webhook" aria-selected="false"><i class="ti ti-webhook"></i>&nbsp;Webhook</a>
                                    </li>
                                </ul>
                                <div class="tab-content p-0">
                                    <div class="tab-pane show active text-muted" id="overview" role="tabpanel">
                                        <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-semibold text-muted">Name</div>
          <div class="fs-14">{!! $user['name'] !!}</div>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted">Merchant ID</div>
          <div class="fs-14">{!! $user['merchant_id'] !!}</div>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted">Email</div>
          <div class="fs-14">{!! $user['email'] !!}</div>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted">Phone</div>
          <div class="fs-14">{!! $user['phone'] !!}</div>
        </div>
      </div>
      
      <!--<h6 class="my-3 ">Settlement Bank Account</h6>-->
      
      <?php 
      
      $bankAccount = (array) \Illuminate\Support\Facades\DB::connection()
            ->table('users_bank')
            ->where('user_id', $user['id'])
            ->orderBy('id', 'DESC')
            ->first();
      
      ?>
      <hr>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-title">Bank Account Holder</div>
          <div class="fs-14 account-detail">{!! $bankAccount['account_name'] ?? "NA" !!}</div>
          <input class="form-control" style="display: none;" name="account_name" value="{!! $bankAccount['account_name'] ?? "" !!}" placeholder="Enter Account name" required>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-title">Bank Account Number</div>
          <div class="fs-14 account-detail">{!! $bankAccount['account_number'] ?? "NA" !!}</div>
          <input class="form-control" style="display: none;" name="account_number" value="{!! $bankAccount['account_number'] ?? "" !!}" placeholder="Enter Account Number" required>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-title">Branch IFSC</div>
          <div class="fs-14 account-detail">{!! $bankAccount['account_ifsc'] ?? "NA" !!}</div>
          <input class="form-control" style="display: none;" oninput="checkIFSC(this.value);" name="account_ifsc" value="{!! $bankAccount['account_ifsc'] ?? "" !!}" placeholder="Enter IFSC Code" required>
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-title">Bank Name</div>
          <div class="fs-14 account-detail">{!! $bankAccount['bank_name'] ?? "NA" !!}</div>
          <input class="form-control" style="display: none;" disabled name="bank_name" value="{!! $bankAccount['bank_name'] ?? "" !!}" placeholder="Enter Bank Name">
        </div>
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-title">Branch Name</div>
          <div class="fs-14 account-detail">{!! $bankAccount['branch'] ?? "NA" !!}</div>
          <input class="form-control" disabled style="display: none;" name="branch" value="{!! $bankAccount['branch'] ?? "" !!}" placeholder="Enter Branch Name">
        </div> 
        <div class="col-md-6">
          <div class="fw-semibold text-muted account-status-title">Account Status</div>
          <div class="fs-14 account-detail"><?php 
             $status = $bankAccount['status'] ?? "NA";
             if($status  == "Pending"){
                echo '<badge class="badge bg-warning-transparent">Waiting for Approval</badge>';  
             }elseif($status  == "Approved"){
                echo '<badge class="badge bg-success-transparent">Approved</badge>';    
             }else{
                 echo "NA";
             }
          ?></div>
        </div>
        
        <div class="col-md-12 text-end">
          <button type="button"  class="btn btn-outline-primary btn-sm mt-4 edit-account-btn" onclick="editBank()">Edit Bank</button>
          <button type="button" style="display:none" class="btn btn-outline-primary btn-sm mt-4 update-account-btn" onclick="updateBank(this)">Update Bank</button>
        </div>
      </div>
                                    </div>
                                    <div class="tab-pane text-muted p-0 border-0 pb-2" id="commercials" role="tabpanel">
                                       <div class="card custom-card shadow-0 border mb-3">
                                           <div class="card-header">
                                               <h6 class="card-title">Payin Charges</h6>
                                           </div>
                                           <div class="card-body p-0">
                                               <table class="table">
                                                   <thead class="table-primary">
                                                       <tr>
                                                           <th>From Amount</th>
                                                           <th>To Amount</th>
                                                           <th>Charges</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody>
                                                       <?php foreach($db->table("package_slabs")->where("package_id",$user['package'])->where('service','payin')->get() as $slab) :  ?>
                                                         <tr>
                                                             <td>₹ {!! $slab->min_amount; !!}</td>
                                                             <td>₹ {!! $slab->max_amount; !!}</td>
                                                             <td>
                                                                 <?php if($slab->percent == 1) : ?>
                                                                 {!! $slab->amount; !!} %  <?php $slab->gst == 1?"+ GST":""?>
                                                                 <?php else : ?>
                                                                  ₹ {!! $slab->amount; !!}  <?php $slab->gst == 1?"+ GST":""?>
                                                                 <?php endif; ?>
                                                             </td>
                                                         </tr>
                                                       <?php endforeach; ?>
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                       
                                       <div class="card custom-card shadow-0 border">
                                           <div class="card-header">
                                               <h6 class="card-title">Payout Charges</h6>
                                           </div>
                                           <div class="card-body p-0">
                                               <table class="table">
                                                   <thead class="table-primary">
                                                       <tr>
                                                           <th>From Amount</th>
                                                           <th>To Amount</th>
                                                           <th>Charges</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody>
                                                       <?php foreach($db->table("package_slabs")->where("package_id",$user['package'])->where('service','payout')->get() as $slab) :  ?>
                                                         <tr>
                                                             <td>₹ {!! $slab->min_amount; !!}</td>
                                                             <td>₹ {!! $slab->max_amount; !!}</td>
                                                             <td>
                                                                 <?php if($slab->percent == 1) : ?>
                                                                 {!! $slab->amount; !!} %  <?php $slab->gst == 1?"+ GST":""?>
                                                                 <?php else : ?>
                                                                  ₹ {!! $slab->amount; !!}  <?php $slab->gst == 1?"+ GST":""?>
                                                                 <?php endif; ?>
                                                             </td>
                                                         </tr>
                                                       <?php endforeach; ?>
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                    </div>
                                    <div class="tab-pane text-muted p-0 border-0" id="loginHistory" role="tabpanel">
                                       
                                       <div class="card custom-card shadow-0 border mb-3">
                                           <div class="card-header d-flex align-items-center justify-content-between">
                                               <h6 class="card-title">Login History</h6>
                                               <div class="badge bg-primary">Last 5</div>
                                           </div>
                                           <div class="card-body p-0">
                                               <table class="table">
                                                   <thead class="table-primary">
                                                       <tr>
                                                           <th>Browser</th>
                                                           <th>IP</th>
                                                           <th>Device</th>
                                                           <th>Time</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody id="recentLogins">
                                                      
                                                      
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                       
                                       
                                    </div>
                                    <div class="tab-pane text-muted p-0 border-0" id="apiKeys" role="tabpanel">
                                       <div class="card custom-card shadow-0 border mb-3">
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
                                    
                                     <div class="tab-pane text-muted p-0 border-0" id="ipWhiteListing" role="tabpanel">
                                     <div class="card custom-card border shadow-0">
            <div class="card-header d-flex justify-content-between">
                <h6 class="card-title"><i class="ti ti-server"></i> Whitelisted IP's</h6>
                <button class="btn btn-sm btn-primary" onclick="navigate('/settings/whitelisted-ip');"><i class="ti ti-circle-plus"></i>&nbsp;Whitelist IP</button>
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
                                    
                                    <div class="tab-pane text-muted p-0 border-0" id="Webhook" role="tabpanel">
                                        <div class="card custom-card mb-2 border shadow-0 mt-2">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-arrow-down-left"></i> Payin Webhook URL</h6>
            </div>
            <div class="card-body">
                <form action="/api/update-webhook/payin" method="POST" novalidate>
                    <label class="mb-1">Webhook URL <span class="text-primary">*</span></label>
                    <input type="text" name="url" value="{!! $user['payin_webhook'] !!}" validation="url" class="form-control" placeholder="Enter URL" required>
                    <div class="invalid-feedback"></div>
                    
                    <button type="submit" class="btn btn-primary  float-end mt-3">Update <i class="ti ti-arrow-right"></i></button>
                </form>
            </div>
        </div>
        
        
        <div class="card custom-card border shadow-0">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-arrow-up-right"></i> Payout Webhook URL</h6>
            </div>
            <div class="card-body">
                <form action="/api/update-webhook/payout" method="POST" novalidate>
                    <label class="mb-1">Webhook URL <span class="text-primary">*</span></label>
                    <input type="text" name="url" value="{!! $user['payout_webhook'] !!}" validation="url" class="form-control" placeholder="Enter URL" required>
                    <div class="invalid-feedback"></div>
                    
                    <button type="submit" class="btn btn-primary  float-end mt-3">Update <i class="ti ti-arrow-right"></i></button>
                </form>
            </div>
        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    </div>
</div>

                    
                    
                    
<script>


    function editBank() {
       $(".account-detail").hide();
       $(".account-title").addClass("mb-1");
       $(".account-status-title").hide();
       $(".edit-account-btn").hide();
       $(".update-account-btn").show();
       $("#overview input").show();
    }

function updateBank(btn) {
    btn.disabled = true;

    const name = $("input[name=account_name]").val().trim();
    const accountNumber = $("input[name=account_number]").val().trim();
    const ifsc = $("input[name=account_ifsc]").val().trim();

    const showError = (msg) => {
        notify("error", msg);
        btn.disabled = false;
    };

    // Validations
    if (!name) return showError("Account holder name is required");
    if (!accountNumber) return showError("Account number is required");
    if (!/^\d{9,18}$/.test(accountNumber)) return showError("Account number must be 9-18 digits");
    if (!ifsc) return showError("IFSC code is required");
    if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifsc)) return showError("Invalid IFSC code format");

    // Ajax request in JSON format
    $.ajax({
        url: "/api/admin/user/profile/update-bank",
        type: "POST",
        data: JSON.stringify({
            name: name,
            account: accountNumber,
            ifsc: ifsc
        }),
        contentType: "application/json",
        dataType: "json"
    })
    .done((data) => {
        notify("success", "Bank details updated successfully!", () => {
            navigate(location.pathname);
        });
    })
    .fail((xhr) => {
        showError(xhr.responseJSON?.message || "Something went wrong!");
    });
}


    
    function checkIFSC(value){
       if(value.length == 11) {
           $.get('https://ifsc.razorpay.com/'+value,{},(data) => {
               $("input[name=bank_name]").val(data.BANK);
               $("input[name=branch]").val(data.BRANCH);
           });
       } 
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
                        $.post("/api/update-webhook-verify",{ otp : otp },(data) => {
                          
                                notify("success",data.message,() => {
                                    //navigate('/settings/api-keys');
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
</script>



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
            $.get("/api/regenerate-api-key/send-otp",{},(data) => {
                
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
                        $.post("/api/regenerate-api-key/verify-otp",{ otp : otp },(data) => {
                            
                                notify("success",data.message,() => {
                                    navigate('/settings/api-keys');
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

function load() {
    $.get("/api/user/login/history",{},(data) => {
        data.data.loginHistory.forEach((login) => {
        $("#recentLogins").append(`
        
        <tr>
          <td class="d-flex align-items-center">
          
                             <div class="me-2 lh-1 "> 
                                            <span
                                                class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                <img
                                                    src="${getBrowserIcon(login.browser)}">
                                            </span>
                                        </div>
                                        <div class="flex-fill">
                                            <p class="mb-0 fw-semibold">${login.browser}</p>
                                            
                                        </div>
                                        
                                        </td>
          <td>${login.ip}</td>
          <td>${login.device}</td>
          <td>${login.created_at}</td>
          
        </tr>
        `);
    });
    });
}


function getBrowserIcon(browser)
          {
              if(browser == "Chrome")
              {
                  return "https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Google_Chrome_icon_%28February_2022%29.svg/1200px-Google_Chrome_icon_%28February_2022%29.svg.png";
              }
              
              if(browser == "Firefox")
              {
                  return "https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Firefox_logo%2C_2019.svg/512px-Firefox_logo%2C_2019.svg.png";
              }
              
              if(browser == "Safari")
              {
                  return "https://upload.wikimedia.org/wikipedia/commons/thumb/5/52/Safari_browser_logo.svg/2057px-Safari_browser_logo.svg.png";
              }
              
              if(browser == "MSIE")
              {
                  return "https://upload.wikimedia.org/wikipedia/commons/7/7e/Microsoft_Edge_logo_%282019%29.png";
              }
              
              if(browser == "Unknown Browser")
              {
                  return "https://cdn-icons-png.flaticon.com/512/5045/5045810.png";
              }
          }

</script>

<script class="afterLoadScript" type="plain/text">
load();

    
</script>

<style>
    .border-primary{
        border-color: rgb(var(--primary-rgb)) !important;
    }
</style>


<?php if (!isset($_GET['turbo'])) { echo view("footer", get_defined_vars()); } ?>