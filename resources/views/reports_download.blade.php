<?php if (!isset($_GET['turbo'])) {
    $page = "download";
    echo view("header", get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-report me-1 fs-15"></i>Reports</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-download me-1 fs-15"></i>Download</li>
    </ol>
</nav>


<div class="card custom-card">
    <div class="card-header">
        <h6 class="card-title">Download Reports</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3">
             <div class="form-group mb-2">
              <label class="mb-1">Date Range <span class="text-primary">*</span></label>
              <div id="reportrange" class="form-control w-100" style="width: min-content; white-space: nowrap;">
                 <i class="ti ti-calendar"></i>&nbsp;
                 <span></span> <i class="fa fa-caret-down"></i>
               </div>  
            </div>
          </div>
          
            <div class="col-lg-3">
               <div class="form-group mb-2">
                <label class="mb-1">Report Type <span class="text-primary">*</span></label>
                <select class="form-control" id="reportType">
                    <option value="transactions">Transactions</option>
                    <!--<option value="settlements">Settlements</option>-->
                </select>
              </div>
            </div>
          
            <div class="col-lg-3">
               <div class="form-group mb-2">
                <label class="mb-1">Transaction Type <span class="text-primary">*</span></label>
                <select class="form-control" id="txnType">
                    <option value="">All</option>
                    <option value="payin">Payin</option>
                    <option value="payout">Payout</option>
                </select>
              </div>
            </div>
            
            <div class="col-lg-3">
               <div class="form-group mb-2">
                <label class="mb-1">Transaction Status <span class="text-primary">*</span></label>
                <select class="form-control" id="status">
                    <option value="">All</option>
                    <option value="Success">Success</option>
                    <option value="Pending">Pending</option>
                    <option value="Failed">Failed</option>
                    <option value="Expired">Expired</option>
                </select>
              </div>
            </div>
            
            
        </div>
        
        <button onclick="exportData(this);" class="btn btn-primary float-end mt-3"><i class="ti ti-download"></i>&nbsp;Download Report</button>
    </div>
</div>
<script>

    window.startDate = "";
    window.endDate = "";
    
    function exportData(btn) {
    btn.disabled = true;
    setTimeout(() => {
        btn.disabled = false;
    }, 5000);

    const reportType = $("#reportType").val();
    const status = $("#status").val();
    const txnType = $("#txnType").val();

    const params = new URLSearchParams({
        startDate: window.startDate,
        endDate: window.endDate,
        reportType: reportType,
        status: status,
        txnType: txnType
    });

    location.href = '/api/export/report/download?' + params.toString();
}

    
    
    function load(){
        
        var start = moment().subtract(6, 'days'); // 6 days ago
        var end = moment(); // today

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
            window.startDate = start.format("YYYY-MM-DD");
            window.endDate = end.format("YYYY-MM-DD");
        }
    
    
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
            }, cb);

            $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
            window.startDate = start.format("YYYY-MM-DD");
            window.endDate = end.format("YYYY-MM-DD");
        }
</script>

<script class="afterLoadScript" type="text/plain">
    load();
</script>

<?php if (!isset($_GET['turbo'])) {
    echo view("footer", get_defined_vars());
} ?>