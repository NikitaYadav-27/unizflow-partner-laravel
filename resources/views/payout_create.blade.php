@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'payout'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-cash me-1 fs-15"></i>Payout</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-arrow-up-right me-1 fs-15"></i>New Payout
        </li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12 mb-2">
        <div class="card custom-card h-100 mb-2">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-cash me-1 fs-15"></i> Make Payout</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('api.payout.make') }}" method="POST" novalidate captcha="true">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Name <span class="text-primary">*</span></label>

                                <input validation="first-last-name" type="text" required
                                    class="form-control rounded-end" id="customer_name" name="customer_name"
                                    placeholder="Customer Name">
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Phone <span class="text-primary">*</span></label>
                                <input validation="phone" type="text" class="form-control rounded-end"
                                    id="customer_phone" name="customer_phone" placeholder="Customer Phone" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Account Number <span class="text-primary">*</span></label>

                                <input validation="account" type="text" class="form-control rounded-end"
                                    id="account_number" name="account_number" placeholder="Account Number" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Re-Enter Account Number <span class="text-primary">*</span></label>

                                <input validation="re-account" type="text" class="form-control rounded-end"
                                    id="re-account_number" name="re_account_number"
                                    placeholder="Re Enter Account Number" required>
                                <div class="invalid-feedback"></div>

                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">IFSC Code <span class="text-primary">*</span></label>

                                <input oninput="ifsc_check(this)" validation="ifsc" type="text"
                                    class="form-control rounded-end" id="ifsc_code" name="ifsc_code"
                                    placeholder="IFSC Code" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Bank <span class="text-primary">*</span></label>
                                <input type="text" readonly name="bank_name" id="bank_name"
                                    class="form-control rounded-end" placeholder="Bank Name" required>
                                <div class="invalid-feedback text-muted" id="branch"></div>
                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Amount <span class="text-primary">*</span></label>

                                <input validation="amount" type="text" class="form-control rounded-end" id="amount"
                                    name="amount" placeholder="Amount" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

<div class="col-lg-4">
                            <div class="form-group mb-2">
                                <label class="mb-1">Remark </label>

                                <input validation="remarks" type="text" class="form-control rounded-end" id="remark"
                                    name="remark" placeholder="Remark">

                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

</div>
                    <div class="d-flex justify-content-end align-items-end mt-5">

<button class="btn btn-primary float-end mt-3" type="submit"><i
                            class="ti ti-arrow-up-right"></i>&nbsp;Make Payout</button>
                    </div>
                     
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-sm-12 mb-2">
        <div class="card custom-card overflow-hidden h-100 mb-2">
            <div class="card-header justify-content-between">
                <div class="card-title">Recent Transactions </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="recentTxns">

@foreach(\App\Models\Transaction::where("service", "payout")
    ->where("user_id", $user['id'])
    ->orderBy("id", "DESC")
    ->limit(5)->get() as $txn)
            
                    <li class="list-group-item border-top-0 border-start-0 border-end-0">
                        <a href="javascript:void(0);">
                            <div class="d-flex align-items-center">
                                <div  class="me-2 lh-1"> <button
                                        class="avatar avatar-md avatar-rounded bg-primary-transparent" onclick="downloadReceipt(this,'{!! $txn['txnid']; !!}');"><i
                                            style="font-size: 20px;" class="ti ti-download"></i> </button> </div>
                                <div class="flex-fill px-2">
                                    <p class="mb-0 fw-semibold">{!! $txn['txnid'] !!}</p>
                                    <p class="fs-10 text-muted mb-0">{!! $txn['created_at'] !!}</p>
                                </div>
                                <div class="text-end ">
                                    <p class="mb-0 fs-12 fw-semibold">₹ {!! $txn['amount'] !!}</p>
                                    @php
                                        $badgeColor = match($txn['status']) {
                                            'Success' => 'success',
                                            'Pending' => 'warning',
                                            'Failed' => 'danger',
                                            'Processing' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}-transparent">{!! $txn['status'] !!}</span>
                                </div>
                            </div>
                        </a>
                    </li>

                 @endforeach

</ul>
            </div>
        </div>

    </div>

</div>

{!! view("includes/receipt") !!}

<script>
    function ifsc_check(input) {
        try {
            let ifsc = input.value;
            if (ifsc.length == 11) {
                $.ajax({
                    url: 'https://ifsc.razorpay.com/' + ifsc,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        $("#bank_name").val(response.BANK);
                        $("#branch").html(response.BRANCH + "," + response.CITY);
                        $("#branch").addClass("d-flex");

                    }
                });
            } else {
                $("#bank_name").val("");
                $("#branch").html("");
                $("#branch").removeClass("d-flex");
            }
        } catch (e) {
            console.log(e);
        }
    }

function onRequestResponse(response) {
       notify("success",response.message,() => {
           navigate(@json(route('service.payout.create')));
       });
       try{document.querySelector("form").reset();}catch(e){}
       
    }

    function onRequestError(error) {
       notify("error",error.responseJSON.message);
       
    }
    
    function load(){

}
</script>

<script class="afterLoadScript" type="plain/text">
load();
</script>

@if (!request()->query->has('turbo'))
    @include('footer')
@endif