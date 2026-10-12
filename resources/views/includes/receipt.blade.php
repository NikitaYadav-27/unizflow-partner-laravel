

<div style="position: absolute;
    top: -9999px;  
    left: -9999px;
    opacity: 1;   
    pointer-events: none; width: 500px !important;">
        <div class="receipt-card text-center w-100 p-3" id="receipt">
            <div class="receipt-container">
            <div class="receipt-header">
                <div class="success-icon">
                    <i class="ti ti-check"></i>
                </div>
                <h3 class="mb-1 text-white">Payment Successful</h3>
                <p class="mb-0">Your IMPS transaction has been completed successfully</p>
            </div>
            
            <div class="receipt-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Transaction Receipt</h5>
                        <small class="text-muted text-start" style="float: left !important;">
                            <i class="ti ti-calendar"></i> 
                            <span class="date"></span> | 
                            <span class="time"></span>
                        </small>
                    </div>
                    <span class="badge-success">
                        <i class="ti ti-check"></i> Successful
                    </span>
                </div>
                
                <div class="amount-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 text-muted text-start">Amount Paid</p>
                            <p class="amount-value mb-0 text-start amount"></p>
                            <p class="amount-words mb-0 text-start inwords"></p>
                        </div>
                        <i class="ti ti-coin-rupee text-muted" style="font-size: 32px;"></i>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-user me-1"></i> Beneficiary Name
                    </div>
                    <div class="info-value name"></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-credit-card me-1"></i> Account Number
                    </div>
                    <div class="info-value account"></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-building-bank me-1"></i> IFSC Code
                    </div>
                    <div class="info-value ifsc"></div>
                </div>
                
                <div class="divider"></div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-message me-1"></i> Remarks
                    </div>
                    <div class="info-value remarks"></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-id me-1"></i> Transaction ID
                    </div>
                    <div class="info-value txnid"></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-fingerprint me-1"></i> UTR Number
                    </div>
                    <div class="info-value utr"></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="ti ti-hash me-1"></i> Reference ID
                    </div>
                    <div class="info-value refId"></div>
                </div>
            </div>
            
            <div class="receipt-footer">
                <div class="verification-section">
                    <div class="verification-badge">
                        <i class="ti ti-shield-check"></i> Verified Secure Payment
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">
                    <small>This is a computer generated receipt.<br>No signature required.</small>
                </p>
            </div>
        </div>
        </div>
    </div>

<script>

    function downloadReceipt(btn,txnid) {
        let btnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'; 
        $.post('/api/transaction/info',{
            txnid : txnid
        },(data) => {
            data = data.data;
            $("#receipt .date").html(data.date);
            $("#receipt .time").html(data.time);
            $("#receipt .name").html(data.name);
            $("#receipt .account").html(data.account);
            $("#receipt .ifsc").html(data.ifsc);
            $("#receipt .utr").html(data.utr);
            $("#receipt .refId").html(data.reference_id);
            $("#receipt .txnid").html(data.txnid);
            $("#receipt .remarks").html(data.remarks);
            $("#receipt .amount").html("₹ " + data.amount);
            $("#receipt .inwords").html(data.inwords + " Rupees Only");
            
            html2canvas(document.querySelector("#receipt")).then(canvas => {
               const link = document.createElement('a');
               link.download = txnid + '.png';
               link.href = canvas.toDataURL();
               link.click();
               btn.disabled = false;
               btn.innerHTML = btnText;
            });
                
        }).fail((xhr) =>{
            notify("error",xhr.responseJSON.message);
        });
    }
    
    // function downloadReceipt()
    // {
    //     html2canvas(document.querySelector("#receipt")).then(canvas => {
    //       const link = document.createElement('a');
    //       link.download = 'transaction_receipt.png';
    //       link.href = canvas.toDataURL();
    //       link.click();
    //   });
    // }
</script>
<style>
    .receipt-container {
            width: 100% !important;
            margin: 0px !important;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            background: linear-gradient(135deg, rgb(24, 87, 101) 0%, #208f84 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .success-icon {
            background-color: white;
            color: #10B981;
            width: 70px;
            height: 70px;
            font-size: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .receipt-body {
            padding: 30px;
            background-color: white;
            border-left: 1px solid lightgray !important;
            border-right: 1px solid lightgray !important;
        }
        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 20px 0;
        }
        .receipt-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px dashed #dee2e6;
            border: 1px solid lightgray !important;
            border-top: 0px !important;
            border-radius: 0px 0px 16px 16px !important;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        .info-value {
            font-weight: 600;
            text-align: right;
            color: #212529;
        }
        .amount-section {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid rgb(24, 87, 101);
        }
        .amount-value {
            font-size: 28px;
            font-weight: 700;
            color: rgb(24, 87, 101);
        }
        .amount-words {
            color: #4B5563;
            font-size: 14px;
            font-style: italic;
        }
        .badge-success {
            background-color: #D1FAE5;
            color: #059669;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 600;
        }
        .verification-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
        }
        .verification-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            background-color: #EFF6FF;
            color: rgb(24, 87, 101);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        @media print {
            .receipt-container {
                box-shadow: none;
            }
        }
</style>