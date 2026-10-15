<?php if (!isset($_GET['turbo'])) { $page = "settlements"; echo view("header", get_defined_vars()); } ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-inbox me-1 fs-15"></i>Collection</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</li>
    </ol>
</nav>

 


              



<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="me-2 sm-w-100">
               
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="d-flex flex-wrap gap-2">

                <div class="me-2 sm-w-100">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="txnid">Transaction ID</option>
                        <option value="batch_id">Batch ID</option>
                    </select>
                </div>
                <div class="me-2 sm-w-100">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>


               <div id="reportrange" class="form-control sm-w-100" style="width: min-content; white-space: nowrap;">
                 <i class="ti ti-calendar"></i>&nbsp;
                 <span></span> <i class="fa fa-caret-down"></i>
               </div>

               <button onclick="downloadSettlementsReport(this)" class="btn btn-outline-primary sm-w-100">
                 <i class="ti ti-download me-1"></i> Export CSV
               </button>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Batch ID</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Charges</th>
                        <th scope="col">GST</th>
                        <th scope="col">Net Amount</th>
                        <th scope="col">Settle To</th>
                        <th scope="col">TXNID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Create Time</th>
                        <th scope="col">Settled Time</th>
                        <!--<th scope="col">To Time</th>-->
                        <th scope="col">Transactions</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>


    </div>
</div>




<script>

    window.dataTable = null;
    window.searchBy = "";
    window.searchValue = "";
    window.startDate = "";
    window.endDate = "";


    function getTransactions() {
        
        
    var start = moment().subtract(6, 'days'); // 6 days ago
    var end = moment(); // today

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
       // renderTransactionsVolume(start, end);
       window.startDate = start.format("YYYY-MM-DD");
       window.endDate = end.format("YYYY-MM-DD");
       window.dataTable.DataTable().ajax.reload();
       
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

     $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
     window.startDate = start.format("YYYY-MM-DD");
     window.endDate = end.format("YYYY-MM-DD");
    // cb(start, end); 
    
    
    
        window.dataTable = $("#dataTable").dataTable(
            {
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "ajax": {
                    "url": "/api/tables/settlements",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                        d.startDate = window.startDate;
                        d.endDate = window.endDate;
                    }
                }
            }
        );

        document.querySelector(".card-body").append(document.querySelectorAll("#dataTable_wrapper .row")[2])
    }

    function changeLength(value) {
        window.dataTable.DataTable().page.len(value).draw();
    }

    function changeSearchBy(value) {
        window.searchBy = value;
        //refresh data
        if (window.searchValue != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

    function changeSearchValue(value) {
        window.searchValue = value;
        //refresh data
        if (window.searchBy != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

    function downloadSettlementsReport(btn) {
        if (btn) {
            btn.disabled = true;
            setTimeout(() => { btn.disabled = false; }, 3000);
        }
        const params = new URLSearchParams({
            reportType: 'settlements',
            startDate: window.startDate || '',
            endDate: window.endDate || '',
            status: '',
            txnType: ''
        });
        location.href = '/api/export/report/download?' + params.toString();
    }

</script>
<script class="afterLoadScript" type="text/plain">
    getTransactions();
</script>

<style>
    #dataTable_paginate {
        display: flex;
        justify-content: end;
    }

    .table-responsive+.row {
        margin-top: 20px;
        position: sticky;
    }

    .table-responsive {
        max-height: 60vh;
        overflow-y: scroll;
    }
</style>




<?php if (!isset($_GET['turbo'])) { echo view("footer", get_defined_vars()); } ?>