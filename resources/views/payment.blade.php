<?php

 $first_date = new DateTime($txn['created_at']);
 $second_date = new DateTime(date("Y-m-d H:i:s"));
 
 $difference = $first_date->diff($second_date);

 $timeRemain = $difference->i * 60;

 $timeRemain = 900 - $timeRemain;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RidePay - Secure Payment Page</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="/assets/ridepay-favicon.png" type="image/x-icon">
    <style>
      :root {
        --primary: #185765;
        --primary-dark: #185765;
        --secondary: #f43f5e;
        --dark: #1e293b;
        --light: #f8fafc;
      }

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        color: var(--dark);
      }

      .logo {
        max-height: 50px;
      }

      .payment-container {
        max-width: 850px;
        margin: 2rem auto;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      }

      .header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        padding: 1.5rem;
        color: white;
      }

      .main-content {
        background-color: white;
        padding: 0;
      }

      .qr-section {
        padding: 2rem;
        text-align: center;
        border-right: 1px solid #e5e7eb;
      }

      .qr-code-container {
        position: relative;
        width: 220px;
        height: 220px;
        margin: 1rem auto;
      }

      .qr-code {
        width: 100%;
        height: 100%;
        border: 8px solid white;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
      }

      .timer-container {
        margin-top: 1.5rem;
      }

      .timer {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--secondary);
      }

      .info-section {
        padding: 2rem;
      }

      .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
      }

      .detail-row:last-child {
        border-bottom: none;
      }

      .detail-label {
        color: #64748b;
        font-weight: 500;
      }

      .detail-value {
        font-weight: 600;
      }

      .amount {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-dark);
      }

      .upi-apps {
        margin-top: 2rem;
      }

      .upi-app {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin: 0 0.75rem;
        width: 80px !important;
        cursor: pointer;
        transition: transform 0.2s;
      }

      .upi-app:hover {
        transform: scale(1.05);
      }

      .upi-app img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: contain;
        margin-bottom: 0.5rem;
      }

      .upi-app span {
        font-size: 0.8rem;
        color: #64748b;
        text-align: center;
      }

      .help-text {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 2rem;
      }

      .footer {
        background-color: #f8fafc;
        padding: 1rem;
        text-align: center;
        font-size: 0.85rem;
        color: #64748b;
        border-top: 1px solid #e5e7eb;
      }

      .highlighted {
        color: var(--primary-dark);
        font-weight: 600;
      }

      .pulse-animation {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        height: 80%;
        border-radius: 50%;
        background-color: rgba(79, 70, 229, 0.1);
        animation: pulse 2s infinite;
      }

      @keyframes pulse {
        0% {
          transform: translate(-50%, -50%) scale(0.8);
          opacity: 0.8;
        }

        70% {
          transform: translate(-50%, -50%) scale(1.1);
          opacity: 0;
        }

        100% {
          transform: translate(-50%, -50%) scale(0.8);
          opacity: 0;
        }
      }

      @media (max-width: 768px) {
        .main-content {
          flex-direction: column;
        }

        .qr-section {
          border-right: none;
          border-bottom: 1px solid #e5e7eb;
        }
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="payment-container">
        <div class="header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <!-- Company Logo Placeholder -->
            <img src="/assets/ridepay-logo-white.png" alt="PayQuick" class="logo me-3">
          </div>
          <div>
            <span class="badge bg-light text-dark">
              <i class="fas fa-lock me-1"></i> SSL Secured </span>
          </div>
        </div>
        <div class="main-content d-flex">
          <div class="qr-section col-md-6 d-flex align-items-center justify-content-center">
            <div id="qrBox" class="">
            <h5>Scan & Pay with any UPI App</h5>
            <div class="qr-code-container">
              <div class="pulse-animation"></div>
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=
														{!! urlencode($txn['intent']) !!}&color=185765" alt="UPI QR Code" class="qr-code">
            </div>
            <div class="timer-container">
              <p class="mb-1">Time remaining to complete payment</p>
              <div class="timer" id="countdown">15:00</div>
            </div>
            <div class="upi-apps mt-4">
              <h6>Pay using popular UPI apps</h6>
              <div class="d-flex justify-content-center mt-3">
                <!-- Add data attributes to each UPI app div -->
                <div class="upi-app" data-android-pkg="com.google.android.apps.nbu.paisa.user" data-ios-scheme="googlepay://" data-upi-url="
																{!! htmlspecialchars($txn['intent']) !!}">
                  <img src="/assets/payment/google-pay-icon.png" alt="Google Pay">
                  <span>Google Pay</span>
                </div>
                <div class="upi-app" data-android-pkg="com.phonepe.app" data-ios-scheme="phonepe://" data-upi-url="
																	{!! htmlspecialchars($txn['intent']) !!}">
                  <img src="/assets/payment/phonepe-icon.png" alt="PhonePe">
                  <span>PhonePe</span>
                </div>
                <div class="upi-app" data-android-pkg="net.one97.paytm" data-ios-scheme="paytmmp://" data-upi-url="
																		{!! htmlspecialchars($txn['intent']) !!}">
                  <img src="/assets/payment/paytm-icon.png" alt="Paytm">
                  <span>Paytm</span>
                </div>
                <div class="upi-app" data-android-pkg="in.org.npci.upiapp" data-ios-scheme="bhim://" data-upi-url="
																			{!! htmlspecialchars($txn['intent']) !!}">
                  <img src="/assets/payment/bhim-app-icon.png" alt="BHIM">
                  <span>BHIM</span>
                </div>
                <script>
                  document.querySelectorAll('.upi-app').forEach(div => {
                    div.addEventListener('click', function(e) {
                      e.preventDefault();
                      const androidPkg = this.dataset.androidPkg;
                      const iosScheme = this.dataset.iosScheme;
                      const upiUrl = this.dataset.upiUrl;
                      let appUrl;
                      // Detect platform
                      const isAndroid = /android/i.test(navigator.userAgent);
                      const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                      if (isAndroid) {
                        // Android Intent URL with package targeting
                        appUrl = `intent://${upiUrl.split('://')[1]}#Intent;package=${androidPkg};scheme=upi;end;`;
                      } else if (isIOS) {
                        // iOS Deep Link
                        appUrl = upiUrl.replace('upi://', iosScheme);
                      } else {
                        // Fallback for other platforms
                        appUrl = upiUrl;
                      }
                      // Try to open app
                      window.location.href = appUrl;
                      // Fallback to generic UPI if app not installed
                      setTimeout(() => {
                        if (!document.hidden) {
                          window.location.href = upiUrl;
                        }
                      }, 500);
                    });
                  });
                </script>
              </div>
            </div>
            <div class="help-text">
              <p>
                <i class="fas fa-info-circle me-1"></i> Open any UPI app, scan the QR code and complete your payment
              </p>
            </div>
            </div>
            
            <div id="expireBox" class="d-none">
                <i class="bi bi-x-circle-fill text-danger" style="font-size: 80px;"></i>
                <h6 class="text-danger fs-4">Transaction Expired</h6>
            </div>
            
            <div id="failedBox" class="d-none">
                <i class="bi bi-x-circle-fill text-danger" style="font-size: 80px;"></i>
                <h6 class="text-danger fs-4">Transaction Failed</h6>
            </div>
            
             <div id="successBox" class="d-none">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                <h6 class="text-success fs-4">Transaction Successful !</h6>
            </div>
          </div>
          <div class="info-section col-md-6">
            <h5 class="mb-4">Payment Details</h5>
            <div class="mb-4">
              <label class="detail-label">Amount to Pay</label>
              <div class="amount">₹ {!! $txn['amount']; !!} </div>
            </div>
            <div class="transaction-details">
              <div class="detail-row">
                <span class="detail-label">Transaction ID</span>
                <span class="detail-value"> {!! $txn['txnid']; !!} </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"> {!! $txn['name'] !!} </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">xxxx <?php 
                                    $email = $txn['email'];
                                    echo substr(explode("@", $email)[0],-4);
                                    echo "@".explode("@", $email)[1];
                                   
                                 
                                 ?> </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value">xxxxxx {!! substr($txn['phone'],-4) !!} </span>
              </div>
            </div>
            <div class="mt-4">
              <div class="alert alert-info" role="alert">
                <i class="fas fa-shield-alt me-2"></i> Your payment information is secure and encrypted
              </div>
            </div>
          </div>
        </div>
        <div class="footer">
          <p class="mb-0">
            <i class="fas fa-shield-alt me-1"></i> Secured by <span class="highlighted">RidePay</span> | All transactions are 100% secure and encrypted
          </p>
        </div>
      </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
      // Countdown Timer

      function startCountdown() {
        let duration = {!! $timeRemain !!}; // 15 minutes in seconds
        const countdownElement = document.getElementById('countdown');
        const timer = setInterval(function() {
          const minutes = Math.floor(duration / 60);
          let seconds = duration % 60;
          seconds = seconds < 10 ? "0" + seconds : seconds;
          countdownElement.textContent = minutes + ":" + seconds;
          if (--duration < 0) {
            clearInterval(timer);
            expired();
          }
        }, 1000);
      }
      // Display current date and time
      function updateDateTime() {
        const now = new Date();
        const options = {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        };
        document.getElementById('current-time').textContent = now.toLocaleDateString('en-IN', options);
      }
      // Initialize when document is ready
      document.addEventListener('DOMContentLoaded', function() {
          <?php if($txn['status'] == "Pending") :  ?>
          
          let interval = setInterval(() => {
            $.post("/api/transactions/check-status",{
                txnid: "{!! $encTxnId !!}"
            },(data) => {
                if(data.status == "Success"){
                    success();
                    clearInterval(interval);
                }
                if(data.status == "Expired"){
                    expired();
                    clearInterval(interval);
                }
                if(data.status == "Failed"){
                    failed();
                    clearInterval(interval);
                }
                
            });
          },3000);
          
         startCountdown();
         updateDateTime();
         
         
         
          
          
         
         <?php else : ?>
            <?php if($txn['status'] == "Success") : ?>
              success();
            <?php elseif($txn['status'] == "Failed"): ?>
              failed();
            <?php elseif($txn['status'] == "Expired"): ?>
              expired();
            <?php endif; ?>
         <?php endif; ?>
      });
      
      function expired() {
          $("#expireBox").removeClass("d-none");
          $("#qrBox").addClass("d-none");
      }
      
       function failed() {
          $("#failedBox").removeClass("d-none");
          $("#qrBox").addClass("d-none");
       }
       
       function success() {
          $("#successBox").removeClass("d-none");
          $("#qrBox").addClass("d-none");
       }
    </script>
  </body>
</html>