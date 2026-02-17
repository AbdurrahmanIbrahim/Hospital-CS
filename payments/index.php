<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 7)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'payments';

$status = !empty($_GET['status']) ? sanitize($_GET['status']) : 0;

/* =========================
   FILTER & PAGINATION
========================= */
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date   = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$search     = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 20;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE (DATE LOGIC)
========================= */
$where = "WHERE p.status = '$status'";

if (empty($start_date) && empty($end_date)) {
    $today = date('Y-m-d');
    $where .= " AND DATE(p.record_date) = '$today'";
} elseif (!empty($start_date) && empty($end_date)) {
    $where .= " AND DATE(p.record_date) = '$start_date'";
} elseif (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(p.record_date) BETWEEN '$start_date' AND '$end_date'";
}

/* =========================
   TEXT SEARCH FILTER
========================= */
if (!empty($search)) {
    $where .= " AND (u.name LIKE '%$search%' OR u.phone LIKE '%$search%' OR p.reciept_num LIKE '%$search%')";
}

$filterText = "Payments";

if (empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>today</strong>";
} elseif (!empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>" . formatDateReadable($start_date) . "</strong>";
} elseif (!empty($start_date) && !empty($end_date)) {
    $filterText .= " from <strong>" . formatDateReadable($start_date) . "</strong> 
                     to <strong>" . formatDateReadable($end_date) . "</strong>";
}

/* =========================
   COUNT FOR PAGINATION
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM payments p
    JOIN users u ON u.id = p.patient_id
    $where
";

$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   FETCH PAYMENTS
========================= */
 $sql = "
    SELECT 
        p.id,
        p.amount,
        p.discount,
        p.net_amount,
        p.`payment-method` AS payment_method,
        p.record_date,
        p.payment_date,
        p.purpose,
        p.reciept_num,
        p.status,
        u.name AS patient_name,
        u.phone,
        u.hospital_num
    FROM payments p
    JOIN users u ON u.id = p.patient_id
    $where
    ORDER BY p.id DESC
    LIMIT $limit OFFSET $offset
";



$payments = $db->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Payments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>

    .btn-primary {
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.status-active { color:#0a7a0a; font-weight:600; }
.status-inactive { color:#c0392b; font-weight:600; }
.table-actions a { margin-right:8px; }
   .status-active {
            color: #0a7a0a;
            font-weight: 600;
        }
        .status-inactive {
            color: #c0392b;
            font-weight: 600;
        }
        .table-actions a {
            margin-right: 8px;
        }

        /* No staff message styling */
.card-body > p {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    font-size: 16px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
    margin: 20px 0;
}

/* Edit button styling */
.table-actions a[href*="index.php"],.modal_btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #28a745;
}

.table-actions a[href*="index.php"]:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal_btn{
    background-color: blue;
}

/* Delete button styling */
.table-actions .delete-btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #dc3545;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #dc3545;
}

.table-actions .delete-btn:hover {
    background-color: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

    .search-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .search-form {
        width: 100%;
    }
    
    .search-filters {
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .search-input-wrapper {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .search-btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    .reset-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .search-filters {
            flex-direction: column;
            gap: 16px;
        }
        
        .filter-group {
            margin-left: 0 !important;
            width: 100%;
        }
        
        .filter-group:first-child {
            width: 100%;
        }
        
        #staffType {
            width: 100% !important;
        }
        
        .search-input-wrapper {
            flex-direction: column;
        }
        
        #searchQuery {
            border-radius: 6px !important;
            border-right: 1px solid #ddd !important;
            margin-bottom: 8px;
        }
        
        .search-btn {
            border-radius: 6px !important;
            width: 100%;
        }
        
        .reset-btn {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Focus states */
    #staffType:focus,
    #searchQuery:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
    
    /* Placeholder styling */
    #searchQuery::placeholder {
        color: #6c757d;
        opacity: 0.8;
    }

      .table-responsive {
    width: 100%;
    overflow-x: auto; /* Enables horizontal scroll on small screens */
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
}

.table-responsive table {
    min-width: 1000px; /* Optional: forces table to be wide enough */
    border-collapse: collapse;
}


</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header">
        <h2><?=$filterText?></h2>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- DATE FILTER -->
            <!-- DATE FILTER -->
        <div class="search-section" style="margin-bottom: 24px;">
      <form action="" method="GET" class="search-form"> 
    <div class="search-filters">

        <!-- Start Date -->
        <div class="filter-group">
            <label for="startDate" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                Start Date
            </label>
            <input type="date" 
                   name="start_date" 
                   id="startDate"
                   value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>"
                   style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 160px; background-color: white;">
        </div>

        <!-- End Date -->
        <div class="filter-group" style="margin-left: 16px;">
            <label for="endDate" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                End Date
            </label>
            <input type="date" 
                   name="end_date" 
                   id="endDate"
                   value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>"
                   style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 160px; background-color: white;">
        </div>

        <!-- Text Search -->
        <div class="filter-group" style="flex-grow: 1; margin-left: 16px;">
            <label for="searchQuery" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                Search (Name, Phone, Hospital No)
            </label>
            <div class="search-input-wrapper" style="display: flex;">
                <input type="text" 
                       name="search" 
                       id="searchQuery" 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       placeholder="Enter patient name, phone or hospital number..."
                       style="flex-grow: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px 0 0 6px; font-size: 14px; border-right: none;">
                <button type="submit" 
                        class="search-btn"
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;">
                    Search
                </button>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="filter-group" style="margin-left: 16px; align-self: flex-end;">
            <a href="?" 
               class="reset-btn"
               style="display: inline-block; padding: 10px 16px; background-color: #6c757d; color: white; border: 1px solid #6c757d; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                Reset
            </a>
        </div>
      <input type="hidden" name="status" value="<?=$status?>">
    </div>
</form>
</div>

        <?php if ($payments->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">
                No payments found.
            </p>
        <?php else: ?>
     <div class="table-responsive">
        <table class="table" style="overflow:hidden;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Hospital No</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th>Net Amount</th>
                    <th>Payment Method</th>
                    <th>Receipt No</th>
                    <th>Record Date</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = $offset + 1; while ($row = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    <td><?= htmlspecialchars($row['hospital_num']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= number_format($row['discount'], 2) ?></td>
                    <td><?= number_format($row['net_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td><?= htmlspecialchars($row['reciept_num']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($row['record_date'])) ?></td>
                     <td><?= getPurpose($row['purpose']) ?></td>
                    <td>
                        <?= $row['status'] == 0
                            ? '<span class="status-pending">Pending</span>'
                            : '<span class="status-paid">Paid</span>' ?>
                    </td>
                    <td class="table-actions">
                        <a onclick="open_modal(<?= $row['id'] ?>)" class="modal_btn">View Payment</a>
                    </td>
                      <td class="table-actions">
                        <a  href="../reciept/index.php?id=<?=$row['id']?>" class="modal_btn">View Reciept</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:24px;display:flex;justify-content:center;">
            <ul style="list-style:none;display:flex;gap:8px;padding:0;">
                <?php for ($p=1;$p<=$totalPages;$p++): ?>
                <li>
                    <a href="?page=<?= $p ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>"
                       style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;
                       <?= $p==$page?'background:#007bff;color:#fff;':'' ?>">
                       <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

</div>

<!-- PAYMENT MODAL -->
<div id="paymentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Patient Payment</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="patient-details">
                <!-- Loaded dynamically via JS -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn btn-success btn-primary" onclick="submitPayment()">Pay</button>
        </div>
    </div>
</div>


<style>
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        position: relative;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        margin: 2% auto;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        animation: slideUp 0.4s ease;
        overflow: hidden;
    }

    .modal-header {
        padding: 20px 30px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 30px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 20px 30px;
        background: #f8f9fa;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    /* Patient Details Styling */
    .patient-details .detail-section {
        margin-bottom: 30px;
        background: #f8fafc;
        padding: 20px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
    }

    .patient-details .detail-section:last-child {
        margin-bottom: 0;
    }

    .patient-details h3 {
        color: var(--primary-dark);
        font-weight: 600;
        padding-bottom: 10px;
        margin: 0 0 20px 0;
        border-bottom: 2px solid var(--primary);
        position: relative;
        font-size: 1.3rem;
        letter-spacing: 0.5px;
    }

    .patient-details h3:before {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 3px 3px 0 0;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        margin-bottom: 15px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 6px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-label i {
        color: var(--primary);
        width: 16px;
    }

    .detail-value {
        padding: 10px 15px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        min-height: 42px;
        display: flex;
        align-items: center;
        color: var(--text-main);
        font-size: 0.95rem;
        word-break: break-word;
    }

    .detail-value.empty {
        color: var(--text-muted);
        font-style: italic;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        gap: 6px;
    }

    .status-badge.active {
        background: rgba(10, 122, 10, 0.1);
        color: #0a7a0a;
        border: 1px solid rgba(10, 122, 10, 0.2);
    }

    .status-badge.inactive {
        background: rgba(192, 57, 43, 0.1);
        color: #c0392b;
        border: 1px solid rgba(192, 57, 43, 0.2);
    }

    .status-badge i {
        font-size: 0.8rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10px auto;
            max-height: 95vh;
        }

        .modal-header {
            padding: 15px 20px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            flex-direction: column;
        }

        .modal-footer button {
            width: 100%;
        }

        .detail-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
</style>

<script>
let currentPaymentId = null;

function open_modal(paymentId) {
    currentPaymentId = paymentId;
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    document.querySelector('.patient-details').innerHTML = `<p>Loading details...</p>`;

    fetch(`get_patient_payment_details.php?id=${paymentId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayPaymentModal(data);
            } else {
                document.querySelector('.patient-details').innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function displayPaymentModal(data) {
    const patient = data.patient;
    const appointments = data.appointments;
    const items = data.items;
    const lastPayment = data.last_payment;

    let appointmentsHtml = '';
    if (appointments.length > 0) {
        appointmentsHtml = appointments.map(a => `
            <div>
                <strong>Appointment:</strong> ${a.date_appointed} - ${a.diagnosis} 
            </div>
        `).join('');
    } else {
        appointmentsHtml = '<p>No appointments found</p>';
    }

    let itemsHtml = '';
    if (items.length > 0) {
        itemsHtml = '<table style="width:100%;border-collapse:collapse;margin-top:10px;">' +
                    '<tr><th>Item</th><th>Type</th><th>Price</th></tr>' +
                    items.map(i => `
                        <tr>
                            <td>${i.name}</td>
                            <td>${i.type}</td>
                            <td>${i.price}</td>
                        </tr>
                    `).join('') +
                    '</table>';
    } else {
        itemsHtml = '<p>No items/drugs/lab tests found</p>';
    }

    let lastPaymentHtml = '';
    if (lastPayment) {
        lastPaymentHtml = `
            <div style="margin-top:10px;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f1f1f1;">
                <strong>Last Payment:</strong> ${lastPayment.net_amount} (${lastPayment.payment_method}) on ${lastPayment.payment_date || lastPayment.record_date}
            </div>
        `;
    }

    document.querySelector('.patient-details').innerHTML = `
        <div class="detail-section">
            <h3>Patient Info</h3>
            <p><strong>Name:</strong> ${patient.name}</p>
            <p><strong>Hospital No:</strong> ${patient.hospital_num}</p>
            <p><strong>Phone:</strong> ${patient.phone}</p>
            <p><strong>Email:</strong> ${patient.email}</p>
        </div>

        <div class="detail-section">
            <h3>Appointments</h3>
            ${appointmentsHtml}
        </div>

        <div class="detail-section">
            <h3>Items / Drugs / Lab Tests</h3>
            ${itemsHtml}
        </div>

        <div class="detail-section">
            <h3>Payment</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <label>Amount</label>
                <input type="number" id="paymentAmount" value="${data.total_amount || 0}" class="form-control">

                <label>Discount</label>
                <input type="number" id="paymentDiscount" value="${data.discount || 0}" class="form-control">

                <label>Net Amount</label>
                <input type="number" id="paymentNet" value="${data.net_amount || 0}" readonly class="form-control">

                <label>Payment Method</label>
                <select id="paymentMethod" class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Insurance">Insurance</option>
                </select>
            </div>
            ${lastPaymentHtml}
        </div>
    `;

    // Update net amount on discount change
    const amountInput = document.getElementById('paymentAmount');
    const discountInput = document.getElementById('paymentDiscount');
    const netInput = document.getElementById('paymentNet');

    function updateNet() {
        const amt = parseFloat(amountInput.value) || 0;
        const disc = parseFloat(discountInput.value) || 0;
        netInput.value = Math.max(0, amt - disc);
    }

    amountInput.addEventListener('input', updateNet);
    discountInput.addEventListener('input', updateNet);
}

function submitPayment() {
    const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const discount = parseFloat(document.getElementById('paymentDiscount').value) || 0;
    const net = parseFloat(document.getElementById('paymentNet').value) || 0;
    const method = document.getElementById('paymentMethod').value;

    fetch('save_payment.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            payment_id: currentPaymentId,
            amount,
            discount,
            net,
            payment_method: method
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Payment successful!');
            closeModal();
            window.location.href = '../reciept/index.php?id'+currentPaymentId; // refresh page to show updated payment
        } else {
            alert('Payment failed: ' + data.error);
        }
    });
}

function closeModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// close on click outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target == modal) closeModal();
}
</script>


<?php include '../includes/footer.php'; ?>
</main>

<script>
function deletePayment(id) {
    swal("Are you sure?",
         "This will permanently delete the payment.",
         "warning",
    {
        buttons: ["Cancel", "Yes, delete"]
    }).then((ok) => {
        if (ok) {
            window.location.href = 'delete.php?id=' + id;
        }
    });
}
</script>

</body>
</html>
