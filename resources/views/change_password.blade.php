<?php if (!isset($_GET['turbo'])) { $page = "profile"; echo view("header", get_defined_vars()); } ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-user me-1 fs-15"></i>Profile</a>
        </li>
        <li class="breadcrumb-item">
            <a href="javascript:void(0);"><i class="ti ti-shield me-1 fs-15"></i>Security</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-lock me-1 fs-15"></i>Change Password</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-4 mb-2">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-lock"></i> Change Password</h6>
            </div>
            <div class="card-body">
                <form action="/api/user/change_password" method="POST" novalidate>
                    <div class="form-group mb-2">
                      <label class="mb-1">Current Password <span class="text-primary">*</span></label>
                      <input type="password" name="password"  validation="password" class="form-control" placeholder="Enter Current Password" required>
                      <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-2">
                      <label class="mb-1">New Password <span class="text-primary">*</span></label>
                      <input type="password" name="npassword"  validation="password" class="form-control" placeholder="Enter New Password" required>
                      <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-2">
                      <label class="mb-1">Confirm Password <span class="text-primary">*</span></label>
                      <input type="password" name="cpassword"  validation="password" class="form-control" placeholder="Re-enter New Password" required>
                      <div class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary  float-end mt-3">Update <i class="ti ti-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
   
</div>


<script>

    function onRequestResponse(response){
                notify("success",response.message);
    }
    
    function onRequestError(xhr){
        let error = xhr.responseJSON.message;
        notify("error",error);
    }
</script>

<?php if (!isset($_GET['turbo'])) { echo view("footer", get_defined_vars()); } ?>