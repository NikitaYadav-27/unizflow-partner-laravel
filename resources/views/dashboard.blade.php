@if (!request()->query->has('turbo'))
    @include('header', ['page' => 'dashboard'])
@endif

<div class="d-md-flex d-block align-items-center justify-content-between py-3 page-header-breadcrumb">
    <div>
        <p class="fw-semibold fs-18 mb-0">Dashboard</p>

    </div>
    <div class="btn-list mt-md-0 mt-2">

    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title">Today's</h6>
            </div>
            <div class="card-body p-3 pb-0">
                <div class="row">
            <div class="col-lg-4">
                <div class="card custom-card bg-success-transparent border-success border">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Collection</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="todaysCollection">
                                        
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-success-transparent">
                                    <i class="ti ti-arrow-down-left fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card bg-danger-transparent border-danger border">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Payout</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="todaysPayout">
                                        
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-danger-transparent">
                                    <i class="ti ti-arrow-up-right fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card bg-purple-transparent border-purple border" style="border-color: rgb(137,32,173) !important;">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Charges</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="todaysCharges">
                                       
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-purple-transparent">
                                    <i class="ti ti-recharging fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>

<div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title">Total History</h6>
            </div>
            <div class="card-body p-3 pb-0">

<div class="row">
            <div class="col-lg-4">
                <div class="card custom-card bg-success-transparent border-success border">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Collection</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="totalCollection">
                                        
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-success-transparent">
                                    <i class="ti ti-arrow-down-left fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card custom-card bg-danger-transparent border-danger border">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Payout</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="totalPayout">
                                        
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-danger-transparent">
                                    <i class="ti ti-arrow-up-right fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card bg-purple-transparent border-purple border" style="border-color: rgb(137,32,173) !important;">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-xxl-9 col-xl-10 col-lg-9 col-md-9 col-sm-8 col-8 px-3">
                                <div class="mb-2">Charges</div>
                                <div class="text-muted mb-1 fs-12">
                                    <span class="text-dark fw-semibold fs-20 lh-1 vertical-bottom" id="totalCharges">
                                       
                                    </span>
                                </div>
                            </div>

                            <div
                                class="col-xxl-3 col-xl-2 col-lg-3 col-md-3 col-sm-4 col-4 d-flex align-items-center justify-content-center ecommerce-icon px-0">
                                <span class="rounded p-3 bg-purple-transparent">
                                    <i class="ti ti-recharging fs-20 fw-bold"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        </div>
        </div>

<div class="card custom-card">
            <div class="card-body">
                <div class="content-wrapper">
                    <div id="crm-revenue-analytics"></div>
                </div>
            </div>
        </div>

</div>
    <div class="col-lg-4">

<div class="card custom-card overflow-hidden">
                            <div class="card-body p-0">
                                <div  class="bg-primary">
                                <div class="d-sm-flex align-items-top p-4 border-bottom-0 " >
                                    
                                    <div>
                                        <span class="avatar avatar-xxl avatar-rounded online me-3 bg-white text-primary fw-bold">
                                            {{ collect(explode(' ', $user['name'] ?? ''))->filter()->map(fn($n) => strtoupper($n[0] ?? ''))->implode('') }}
                                        </span>
                                    </div>
                                    <div class="flex-fill main-profile-info">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fw-semibold mb-1 text-fixed-white">{!! $user['name']; !!}</h6>
                                            
                                        </div>
                                        <p class="mb-1 text-muted text-fixed-white op-7">{!! $user['email']; !!}</p>
                                        <p class="fs-12 text-fixed-white mb-4 op-5">
                                            <span class="me-3"><i class="ri-profile-line me-1 align-middle"></i>{!! $user['merchant_id']; !!}</span>
                                            <span><i class="ri-smartphone-line me-1 align-middle"></i>+91{!! $user['phone']; !!}</span>
                                        </p>
                                        
                                    </div>

</div>
                                
                                <div class="px-4 pb-4">
                                    <h6 class="text-white">Wallet Balance</h6>
                                    <div class="d-flex justify-content-start gap-3 align-items-center mb-2">
                    <p class="fs-22 fw-semibold mb-0 text-white" id="walletBalance" balance="₹ {!! number_format($user['wallet'], 2, '.', '') !!}">₹ *******
                    </p> <i class="fs-22 fw-semibold ti ti-eye cursor-pointer text-white"
                        onclick="hideShowBalance(this,'walletBalance');"></i>
                </div>
               
                <div class="d-flex text-white justify-content-between text-muted">

<div class="d-flex flex-column align-items-start gap-1">
                         <p class="mb-0 fs-14 text-white">Unsettled Amount</p>
                      <span class="text-warning fw-semibold">₹ {!! $unsettled_amount ?? '0.00' !!}</span>
                      
                    </div>
                    
                     <div class="d-flex flex-column align-items-start gap-1">
                         <p class="mb-0 fs-14 text-white">Funds on Hold</p>
                        <div class="d-flex gap-2 align-items-center">
                             <span id="holdWallet" balance="₹ {!! number_format($user['hold_wallet'], 2, '.', '') !!}">₹ *******</span>
                              <i class="fs-14 fw-semibold ti ti-eye cursor-pointer text-white"
                        onclick="hideShowBalance(this,'holdWallet');"></i>
                        
                             <i class="ti ti-info-circle cursor-pointer" onclick="navigate('{{ route('hold-wallet.ledger') }}');"></i>
                        </div>
                     
                    </div>
                   
                </div>
                                </div>
                                </div>

<div class="p-4 border-bottom border-block-end-dashed">
                                    <p class="fs-15 mb-2 me-4 fw-semibold">Merchant Information :</p>
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

        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="fw-bold mb-0" id="totalTransactionCount"></h4>
                    <div class="ms-2">

                        <span class="text-muted ms-1">Total Transactions</span>
                    </div>
                </div>
                <div class="progress-stacked progress-animate progress-xs mb-4">
                    <div class="progress-bar bg-success" role="progressbar" id="successTransactionProgress" style="width: 21%" aria-valuenow="21"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-warning" role="progressbar" id="pendingTransactionProgress" style="width: 26%" aria-valuenow="26"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-danger" role="progressbar" id="failedTransactionProgress" style="width: 35%" aria-valuenow="35"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-info" role="progressbar" id="processingTransactionProgress" style="width: 18%" aria-valuenow="18"
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <ul class="list-unstyled mb-0 pt-2 crm-deals-status">
                    <li class="success">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>Successful Transactions</div>
                            <div class="fs-12 text-muted" id="successTransactionCount"></div>
                        </div>
                    </li>
                    <li class="warning">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>Pending Transactions</div>
                            <div class="fs-12 text-muted" id="pendingTransactionCount"></div>
                        </div>
                    </li>
                    <li class="danger">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>Failed Transactions</div>
                            <div class="fs-12 text-muted" id="failedTransactionCount"></div>
                        </div>
                    </li>
                    <li class="info">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>Processing Transactions</div>
                            <div class="fs-12 text-muted" id="processingTransactionCount"></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

</div>
</div>
<style>
    .cursor-pointer {
        cursor: pointer;
    }

    .crm-deals-status li.danger:before {
        background-color: rgb(var(--danger-rgb));
    }
</style>
<script>
    function load() {
        
$("#recentTxns").html('');
$("#recentLogins").html('');
$.get(@json(route('api.dashboard.analytics')), {}, (data) => {
    const totalTransactions = data.data.status.total_transactions;
    const successTransactions = data.data.status.success_transactions;
    const pendingTransactions = data.data.status.pending_transactions;
    const failedTransactions = parseInt(data.data.status.failed_transactions) + parseInt(data.data.status.expired_transactions);
    const processingTransactions = data.data.status.processing_transactions;

$("#todaysCollection").html("₹" + data.data.today.collection);
    $("#todaysPayout").html("₹" + data.data.today.payout);
    $("#todaysCharges").html("₹" + data.data.today.charges);
    
     $("#totalCollection").html("₹" + data.data.total.collection);
    $("#totalPayout").html("₹" + data.data.total.payout);
    $("#totalCharges").html("₹" + data.data.total.charges);

$("#totalTransactionCount").html(totalTransactions);
    $("#successTransactionCount").html(successTransactions);
    $("#pendingTransactionCount").html(pendingTransactions);
    $("#failedTransactionCount").html(failedTransactions);
    $("#processingTransactionCount").html(processingTransactions);

const successPercentage = totalTransactions > 0 ? ((successTransactions / totalTransactions) * 100).toFixed(2) : 0;
    const pendingPercentage = totalTransactions > 0 ? ((pendingTransactions / totalTransactions) * 100).toFixed(2) : 0;
    const failedPercentage = totalTransactions > 0 ? ((failedTransactions / totalTransactions) * 100).toFixed(2) : 0;
    const processingPercentage = totalTransactions > 0 ? ((processingTransactions / totalTransactions) * 100).toFixed(2) : 0;

$("#successTransactionProgress").attr("aria-valuenow", successPercentage).css("width", successPercentage + "%");
    $("#pendingTransactionProgress").attr("aria-valuenow", pendingPercentage).css("width", pendingPercentage + "%");
    $("#failedTransactionProgress").attr("aria-valuenow", failedPercentage).css("width", failedPercentage + "%");
    $("#processingTransactionProgress").attr("aria-valuenow", processingPercentage).css("width", processingPercentage + "%");

let payin = [];
    let payout = [];
    data.data.weeklySummary.forEach((dat) => {
        payin.push({
            x : dat.txn_date,
            y : dat.total_payin
        });
        
        payout.push({
            x : dat.txn_date,
            y : dat.total_payout
        });
    });
    
    renderGraph(payin,payout);

});

}
    
    function renderGraph(payin,payout)
    {
        var options = {
            series: [
                {
                    type: 'line',
                    name: 'Payin',
                    data: payin
                },
                {
                    type: 'line',
                    name: 'Payout',
                    chart: {
                        dropShadow: {
                            enabled: true,
                            enabledOnSeries: undefined,
                            top: 5,
                            left: 0,
                            blur: 3,
                            color: '#000',
                            opacity: 0.1
                        }
                    },
                    data: payout
                },
            ],
            chart: {
                height: 350,
                animations: {
                    speed: 500
                },
                dropShadow: {
                    enabled: true,
                    enabledOnSeries: undefined,
                    top: 8,
                    left: 0,
                    blur: 3,
                    color: '#000',
                    opacity: 0.1
                },
            },
            colors: ["rgba(38,191,148, 0.85)","rgb(230,83,60)"],
            dataLabels: {
                enabled: false
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            },
            stroke: {
                curve: 'smooth',
                width: [2, 2, 0],
                dashArray: [0, 0],
            },
            xaxis: {
                axisTicks: {
                    show: false,
                },
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "₹ " + value;
                    }
                },
            },
            tooltip: {
                y: [{
                    formatter: function (e) {
                        return void 0 !== e ? "₹ " + e.toFixed(0) : e
                    }
                }, {
                    formatter: function (e) {
                        return void 0 !== e ? "₹ " + e.toFixed(0) : e
                    }
                }, {
                    formatter: function (e) {
                        return void 0 !== e ? e.toFixed(0) : e
                    }
                }]
            },
            legend: {
                show: true,
                customLegendItems: ['Payin', 'Payout'],
                inverseOrder: true
            },
            title: {
                text: 'Transaction Analytics',
                align: 'left',
                style: {
                    fontSize: '.8125rem',
                    fontWeight: 'semibold',
                    color: '#8c9097'
                },
            },
            markers: {
                hover: {
                    sizeOffset: 5
                }
            }
        };
        document.getElementById('crm-revenue-analytics').innerHTML = '';
        var chart = new ApexCharts(document.querySelector("#crm-revenue-analytics"), options);
        chart.render();
    }

function getBrowserIcon(browser)
          {
              if(browser == "Chrome")
              {
                  return "https://cdnjs.cloudflare.com/ajax/libs/browser-logos/74.0.0/chrome/chrome.png";
              }
              
              if(browser == "Firefox")
              {
                  return "https://cdnjs.cloudflare.com/ajax/libs/browser-logos/74.0.0/firefox/firefox.png";
              }
              
              if(browser == "Safari")
              {
                  return "https://cdnjs.cloudflare.com/ajax/libs/browser-logos/74.0.0/safari/safari.png";
              }
              
              if(browser == "MSIE" || browser == "Edge")
              {
                  return "https://cdnjs.cloudflare.com/ajax/libs/browser-logos/74.0.0/edge/edge.png";
              }
              
              return "https://cdn-icons-png.flaticon.com/512/5045/5045810.png";
          }

    function hideShowBalance(icon,walletId) {
        var walletBalance = document.getElementById(walletId);
        if (walletBalance.innerHTML.includes("*******")) {
            walletBalance.innerHTML = walletBalance.getAttribute("balance");
            icon.classList.remove("ti-eye");
            icon.classList.add("ti-eye-off");
        } else {
            walletBalance.innerHTML = "₹ *******";
            icon.classList.remove("ti-eye-off");
            icon.classList.add("ti-eye");
        }
    }
</script>

<script class="afterLoadScript" type="plain/text">
load();

</script>

@if (!request()->query->has('turbo'))
    @include('footer')
@endif