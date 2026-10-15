<?php if (!isset($_GET['turbo'])) { $page = "ledger"; echo view("header", get_defined_vars()); } ?>


<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-wallet me-1 fs-15"></i>Wallet</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-file-text me-1 fs-15"></i>Ledger</li>
    </ol>
</nav>



<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">

            <div class="me-2">
                <select onchange="changeLength(this.value)" class="form-select" id="pageSize"
                    onchange="window.pageSize = this.value; getTransactions(window.pageNo,window.pageSize,window.searchBy,window.searchValue)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="d-flex">

                <div class="me-2">
                    <select class="form-select" id="searchBy" onchange="changeSearchBy(this.value)">
                        <option value="">Select</option>
                        <option value="type">Type</option>
                        <option value="txn_type">Txn Type</option>
                        <option value="txnid">TxnId</option>
                        <option value="amount">Amount</option>
                        <option value="opening">Opening</option>
                        <option value="closing">Closing</option>
                        <option value="remarks">Remarks</option>
                    </select>
                </div>
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>
                <div class="me-2">
                    <button onclick="downloadWalletLedger(this)" class="btn btn-outline-primary">
                        <i class="ti ti-download me-1"></i> Export CSV
                    </button>
                </div>


            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Type</th>
                        <th scope="col">Txn Type</th>
                        <th scope="col">TxnId</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Opening</th>
                        <th scope="col">Closing</th>
                        <th scope="col">Remarks</th>
                        <th scope="col">Date Time</th>
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


    function getTransactions() {
        window.dataTable = $("#dataTable").dataTable(
            {
                "bFilter": false,
                "bLengthChange": false,
                "serverSide": true,
                "processing": true,
                "paging": true,
                "ordering" : false,
                "ajax": {
                    "url": "/api/tables/wallet-transaction",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
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

    function downloadWalletLedger(btn) {
        if (btn) {
            btn.disabled = true;
            setTimeout(() => { btn.disabled = false; }, 3000);
        }
        location.href = '/api/export/wallet-ledger?searchBy=' + encodeURIComponent(window.searchBy || '') + '&searchValue=' + encodeURIComponent(window.searchValue || '');
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