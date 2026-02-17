<?php include 'backend.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Receipt - Order #<?= $order['reciept_num'] ?></title>
   <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
  <div class="receipt-container">
    <div class="receipt">
      <div class="receipt-header">
        <div class="logo-container">
          <img src="<?=ROOT_URL?>images/logos/<?=$hospital['logo']?>" class="logo" alt="<?=$hospital['name']?>">
        </div>
        <div class="store-name"><?=$hospital['name']?></div>
        <div class="store-tagline"><?=$hospital['address']?></div>
      </div>
      
      <div class="receipt-body">
        <div class="order-info">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Receipt No</span>
              <span class="info-value">#<?= $payment['reciept_num'] ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Payment Date</span>
              <span class="info-value"><?= !empty($payment['payment_date']) ? formatDateReadableWithTime($payment['payment_date']) : 'NILL'?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Print Date</span>
              <span class="info-value"><?= date('Y-m-d h:i A') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Customer</span>
              <span class="info-value"><?= get('name','users',$payment['patient_id']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Payment Method</span>
              <span class="info-value"><?= !empty($payment['payment-method']) ? $payment['payment-method']  : 'NILL' ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Purpose</span>
              <span class="info-value">
                <?=get_purpose($payment['purpose'])?>
              </span>
            </div>
              <div class="info-item">
              <span class="info-label">Payment status</span>
              <span class="info-value">
                <?=get_payment_status_badge($payment['status'])?>
              </span>
            </div>
          </div>
        </div>
        
        <div class="items-section">
          <div class="section-title">Items Items</div>
          <table class="items-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Qty</th>
                <th style="text-align: right;">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): 
                $total = $item['price'];
                $subtotal += $total;
              ?>
              <tr>
                <td class="item-name"><?= htmlspecialchars($item['name']) ?></td>
                <td class="item-quantity"><?= $item['quantity'] ?? 'NILL' ?></td>
                <td class="item-price">₦<?= number_format($item['price'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <div class="totals-section">
          <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span class="total-value">₦<?= number_format($subtotal, 2) ?></span>
          </div>
          
    
          <div class="total-row">
            <span class="total-label">Discount</span>
            <span class="total-value" style="color: var(--accent);"><?= $payment['discount']?>%</span>
          </div>
        
          
          <div class="grand-total">
            <div class="total-row">
              <span class="total-label">TOTAL AMOUNT</span>
              <span class="total-value">₦<?= number_format($payment['net_amount'], 2) ?></span>
            </div>
          </div>
          
          <?php if (!empty($order['credit'])): ?>
          <div class="credit-badge" style="margin-top: 15px;">
            Credit Receipt
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="receipt-footer">
        <div class="served-by">
          Served by: <?= htmlspecialchars($user_name) ?>
        </div>
        <div class="thank-you">
          Thank you for your patronage!
        </div>
        <div class="powered-by">
          Powered by <?=$hospital['name']?>
        </div>
      </div>
    </div>
    
    <button class="print-btn" onclick="window.print()">Print Receipt</button>
  </div>

 <!--  <script type="text/javascript">
    window.onload = function () {
      localStorage.removeItem('cart_items');
      window.onafterprint = function () {
        setTimeout(function() {
          window.location.href = 'index.php';
        }, 1000);
      };
      
      // Add a slight delay before auto-printing for better UX
      setTimeout(function() {
        window.print();
      }, 1000);
    };
  </script> -->
</body>
</html>