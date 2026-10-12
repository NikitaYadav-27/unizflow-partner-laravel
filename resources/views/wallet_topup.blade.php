<?php if (!isset($_GET['turbo'])) { $page = "topup"; echo view("header", get_defined_vars()); } 

//print_r($banks);

?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-wallet me-1 fs-15"></i>Wallet</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-plus me-1 fs-15"></i>Topup Wallet</li>
    
    </ol>
</nav>


    <div class="row mt-2">
        <div class="col-lg-4 mb-3">
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="card-title">Bank Details</h6>
                </div>
                <div class="card-body px-0 py-0">
                    
 <div class="accordion accordion-primary rounded-0" id="accordionPrimaryExample">
     
     
    <?php $index = 1; foreach($banks as $bank) :  ?>
    <div class="accordion-item rounded-0">
        <h2 class="accordion-header rounded-0" id="headingPrimaryOne<?php echo $index; ?>">
            <button class="accordion-button rounded-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapsePrimaryOne<?php echo $index; ?>" aria-expanded="<?php echo $index == 1?"true":"false"?>"
                aria-controls="collapsePrimaryOne<?php echo $index; ?>">
                <?php echo $bank->name; ?>
            </button>
        </h2>
        <div id="collapsePrimaryOne<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index == 1?"show":""?>"
            aria-labelledby="headingPrimaryOne<?php echo $index; ?>"
            data-bs-parent="#accordionPrimaryExample">
            <div class="accordion-body">
                <table>
                    <tr>
                        <td style="white-space: nowrap;">Account Name</td>
                        <td class="px-2">:</td>
                        <td><?php echo $bank->account_holder_name; ?></td>
                    </tr>
                    
                    <tr>
                        <td style="white-space: nowrap;">Account No</td>
                        <td class="px-2">:</td>
                        <td><?php echo $bank->account_number; ?></td>
                    </tr>
                    
                    <tr>
                        <td style="white-space: nowrap;">IFSC Code</td>
                        <td class="px-2">:</td>
                        <td><?php echo $bank->ifsc_code; ?></td>
                    </tr>
                    
                     <tr>
                        <td style="white-space: nowrap;">Bank</td>
                        <td class="px-2">:</td>
                        <td><?php echo $bank->bank; ?></td>
                    </tr>
                    
                    <tr>
                        <td style="white-space: nowrap;">Branch</td>
                        <td class="px-2">:</td>
                        <td><?php echo $bank->branch; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php $index++; endforeach; ?>
    
    <?php if($index == 1) : ?>
    <center>
        <p class="my-5">You don't have assigned Bank Account</p>
    </center>
    <?php endif; ?>

</div>    
                        
                     
                </div>
            </div>
        </div>
         <div class="col-lg-8 mb-3">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between">
                   <div class="card-title">
                       Topup History
                   </div>
                   
                   <button class="btn btn-sm btn-primary py-1" onclick="navigate('/wallet/topup/add-request');"><i class="ti ti-circle-plus"></i>&nbsp;&nbsp;Add Request</button>
                </div>
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
              
                    </select>
                </div>
                <div class="me-2">
                    <input type="text" onkeyup="changeSearchValue(this.value)" class="form-control" id="searchValue"
                        placeholder="Search">
                </div>


            </div>
        </div>
        
                    <div class="table-responsive">
                        <table class="table text-nowrap w-100" id="dataTable">
                            <thead class="table-primary">
                                <tr>
                                   
                                    <th>TxnId</th>
                                    <th>Bank</th>
                                    <th>RRN</th>
                                    <th>Mode</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
                    "url": "/api/tables/fund_requests",
                    "type": "GET",
                    "data": function (d) {
                        d.searchBy = window.searchBy;
                        d.searchValue = window.searchValue;
                    }
                }
            }
        );

        document.querySelectorAll(".card-body")[1].append(document.querySelectorAll("#dataTable_wrapper .row")[2])
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

</script>
<script class="afterLoadScript" type="text/plain">
    getTransactions();
</script>


<?php if (!isset($_GET['turbo'])) { echo view("footer", get_defined_vars()); } ?>