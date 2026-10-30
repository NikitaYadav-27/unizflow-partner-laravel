@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'payin-transactions'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-inbox me-1 fs-15"></i>Collection</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-arrows-left-right me-1 fs-15"></i>Transactions</li>
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
                        <option value="rrn">UTR</option>
                        <option value="txnid">Transaction ID</option>
                        <option value="merchant_txnid">Merchant TxnId</option>
                        <option value="name">Name</option>
                        <option value="phone">Phone</option>
                        <option value="status">Status</option>
                        
                    </select>
                </div>
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>

</div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap table-bordered" id="dataTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        
                        <th scope="col">Txnid</th>
                        <th scope="col">Merchant TxnId</th>
                        <th scope="col">RRN</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Charges</th>
                        <th scope="col">GST</th>
                        <th scope="col">Net Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Payee Name</th>
                        <th scope="col">Payee VPA</th>
                        <th scope="col">Date Time</th>
                        <th scope="col">Txn Complete Time</th>
                        <th scope="col">Remarks</th>
                        
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
                    "url": @json(route('api.tables.payin-transactions')),
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
        
        if (window.searchValue != "") {
            window.dataTable.DataTable().ajax.reload();
        }

    }

    function changeSearchValue(value) {
        window.searchValue = value;
        
        if (window.searchBy != "") {
            window.dataTable.DataTable().ajax.reload();
        }

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

@if (!request()->query->has('turbo'))
    @include('footer')
@endif