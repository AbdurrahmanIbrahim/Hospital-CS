<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3 AND $_SESSION['type'] != 4 AND $_SESSION['type'] != 7)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admission';

if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = intval($_GET['id']);

/* =========================
   FETCH ADMISSION + PATIENT
========================= */
$sql = "SELECT
    a.*,
    u.name AS patient_name,
    u.hospital_num,
    u.phone,
    r.room_name,
    r.room_price,
    w.ward_name,
    d.name AS doctor_name
FROM admissions a
INNER JOIN users u ON u.id = a.patient_id
INNER JOIN rooms r ON r.id = a.room_id
LEFT JOIN wards w ON w.id = r.ward
LEFT JOIN users d ON d.id = a.doctor_id
WHERE a.id = '$admission_id'
LIMIT 1";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Admission not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission = $run->fetch_assoc();

// Process room billing to ensure charges are up to date
if ($admission['status'] == 0) {
    processRoomBilling($admission_id);
}

/* =========================
   FETCH BILLING ITEMS
========================= */
$billing_items = [];
$room_total = 0;
$drug_total = 0;
$other_total = 0;
$paid_total = 0;
$unpaid_total = 0;

$sql = "SELECT * FROM admission_billing WHERE admission_id = '$admission_id' ORDER BY created_at ASC";
$run = $db->query($sql);
if ($run) {
    while ($row = $run->fetch_assoc()) {
        $billing_items[] = $row;
        if ($row['billing_type'] == 1) $room_total += $row['amount'];
        elseif ($row['billing_type'] == 2) $drug_total += $row['amount'];
        else $other_total += $row['amount'];

        if ($row['paid'] == 1) $paid_total += $row['amount'];
        else $unpaid_total += $row['amount'];
    }
}

$grand_total = $room_total + $drug_total + $other_total;

// Calculate days admitted
$admitted_date = new DateTime($admission['admission_date']);
$now = $admission['status'] == 1 ? new DateTime($admission['discharge_date']) : new DateTime();
$days = $admitted_date->diff($now)->days + 1;

// Check if all items are paid
$all_paid = ($unpaid_total == 0 && count($billing_items) > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Admission Billing</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            body { display: block; }
            .main-content { width: 100%; }
            .content-scroll { padding: 0; }
        }
        .pay-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .pay-btn:hover { background: #1d4ed8; }
        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-unpaid {
            background: #fef2f2;
            color: #dc2626;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-box h3 { margin: 0 0 20px; font-size: 18px; }
        .modal-box .form-group { margin-bottom: 16px; }
        .modal-box label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
        .modal-box select, .modal-box input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .modal-actions { display: flex; gap: 12px; margin-top: 20px; }
        .modal-actions button { flex: 1; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; }
        .btn-cancel { background: #f1f5f9; color: #475569; }
        .btn-confirm { background: #2563eb; color: white; }
        .btn-confirm:hover { background: #1d4ed8; }
    </style>
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header no-print">
        <div>
            <h1>Admission Billing</h1>
            <p>Bill details for <?= htmlspecialchars($admission['patient_name']) ?></p>
        </div>
        <div class="header-actions">
            <a href="view.php" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back
            </a>
            <a href="reports.php?id=<?= $admission_id ?>" class="btn-secondary">Reports</a>
            <button onclick="window.print()" class="btn-primary">Print Bill</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrap" style="background:#dbeafe;">
                    <span style="color:#2563eb;">&#8358;</span>
                </div>
            </div>
            <h3>&#8358;<?= number_format($grand_total, 2) ?></h3>
            <p>Total Bill</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrap" style="background:#dcfce7;">
                    <span style="color:#15803d;">&#10003;</span>
                </div>
            </div>
            <h3>&#8358;<?= number_format($paid_total, 2) ?></h3>
            <p>Paid</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrap" style="background:#fef2f2;">
                    <span style="color:#dc2626;">&#8358;</span>
                </div>
            </div>
            <h3>&#8358;<?= number_format($unpaid_total, 2) ?></h3>
            <p>Unpaid</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrap" style="background:#ede9fe;">
                    <span style="color:#7c3aed;">&#128197;</span>
                </div>
            </div>
            <h3><?= $days ?></h3>
            <p>Day(s) <?= $admission['status'] == 0 ? 'Admitted' : 'Total Stay' ?></p>
        </div>
    </div>

    <!-- Patient & Admission Info -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Admission Details</h2>
            <?php if($admission['status'] == 0): ?>
                <span style="background:#dcfce7;color:#15803d;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">Active</span>
            <?php else: ?>
                <span style="background:#f1f5f9;color:#64748b;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">Discharged <?= formatDateReadable($admission['discharge_date']) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:24px;">
            <div class="form-row">
                <div class="form-group">
                    <label>Patient</label>
                    <p style="font-weight:600;"><?= htmlspecialchars($admission['patient_name']) ?> (<?= $admission['hospital_num'] ?>)</p>
                </div>
                <div class="form-group">
                    <label>Room / Bed</label>
                    <p><?= htmlspecialchars($admission['room_name']) ?> - Bed <?= $admission['bed_number'] ?> (<?= htmlspecialchars($admission['ward_name'] ?? '') ?>)</p>
                </div>
                <div class="form-group">
                    <label>Doctor</label>
                    <p><?= htmlspecialchars($admission['doctor_name'] ?? 'N/A') ?></p>
                </div>
                <div class="form-group">
                    <label>Admission Date</label>
                    <p><?= formatDateReadableWithTime($admission['admission_date']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Billing Item Form (only for active admissions, admin/doctor) -->
    <?php if($admission['status'] == 0 && ($_SESSION['type'] == 0 || $_SESSION['type'] == 3)): ?>
    <div class="card no-print" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Add Billing Item</h2>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form method="POST" action="add_billing.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
                <input type="hidden" name="admission_id" value="<?= $admission_id ?>">
                <div class="form-group" style="margin-bottom:0;flex:2;min-width:200px;">
                    <label>Description *</label>
                    <input type="text" name="description" placeholder="e.g. Dressing, Oxygen, etc." required>
                </div>
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:120px;">
                    <label>Amount (&#8358;) *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:150px;">
                    <label>Type *</label>
                    <select name="billing_type" required>
                        <option value="3">Other</option>
                        <option value="2">Drug</option>
                        <option value="1">Room</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="height:44px;white-space:nowrap;">Add Item</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Billing Items Table -->
    <div class="card">
        <div class="card-header">
            <h2>Billing Breakdown</h2>
            <span style="color:var(--text-muted);font-size:14px;"><?= count($billing_items) ?> item(s)</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <?php if(($_SESSION['type'] == 0 || $_SESSION['type'] == 7) && $admission['status'] == 0): ?>
                    <th class="no-print">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(count($billing_items) > 0): ?>
                    <?php $i = 1; foreach($billing_items as $item): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td>
                            <?php if($item['billing_type'] == 1): ?>
                                <span style="background:#dbeafe;color:#2563eb;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">Room</span>
                            <?php elseif($item['billing_type'] == 2): ?>
                                <span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">Drug</span>
                            <?php else: ?>
                                <span style="background:#f1f5f9;color:#64748b;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">Other</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:600;">&#8358;<?= number_format($item['amount'], 2) ?></td>
                        <td><?= formatDateReadable($item['created_at']) ?></td>
                        <td>
                            <?php if($item['paid'] == 1): ?>
                                <span class="badge-paid">Paid</span>
                            <?php else: ?>
                                <span class="badge-unpaid">Not Paid</span>
                            <?php endif; ?>
                        </td>
                        <?php if(($_SESSION['type'] == 0 || $_SESSION['type'] == 7) && $admission['status'] == 0): ?>
                        <td class="no-print">
                            <?php if($item['paid'] == 0): ?>
                                <?php if($item['billing_type'] == 2 && $item['reference_id'] > 0): ?>
                                    <a href="../payments/index.php" class="pay-btn" style="text-decoration:none;">Pay via Payments</a>
                                <?php else: ?>
                                    <button class="pay-btn" onclick="openPayModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['description']) ?>', <?= $item['amount'] ?>)">Pay</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#15803d;font-size:12px;">&#10003;</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc;font-weight:700;">
                        <td colspan="3" style="text-align:right;font-size:16px;">Grand Total:</td>
                        <td style="font-size:16px;color:var(--primary);">&#8358;<?= number_format($grand_total, 2) ?></td>
                        <td></td>
                        <td></td>
                        <?php if(($_SESSION['type'] == 0 || $_SESSION['type'] == 7) && $admission['status'] == 0): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No billing items yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Discharge Section -->
    <?php if($admission['status'] == 0 && ($_SESSION['type'] == 0 || $_SESSION['type'] == 3)): ?>
    <div class="card no-print" style="margin-top: 24px;">
        <div class="card-body" style="padding: 24px; text-align: center;">
            <?php if($all_paid): ?>
                <p style="color:#15803d;font-weight:600;margin-bottom:16px;">All billing items have been paid. Patient can be discharged.</p>
                <a href="discharge.php?id=<?= $admission_id ?>" class="btn-primary" style="padding:12px 32px;font-size:15px;text-decoration:none;" onclick="return confirm('Are you sure you want to discharge this patient?')">Discharge Patient</a>
            <?php else: ?>
                <p style="color:#dc2626;font-weight:600;margin-bottom:8px;">Cannot discharge - <?= count(array_filter($billing_items, function($i){ return $i['paid'] == 0; })) ?> unpaid item(s) remaining.</p>
                <p style="color:var(--text-muted);font-size:14px;">Outstanding: &#8358;<?= number_format($unpaid_total, 2) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payModal">
    <div class="modal-box">
        <h3>Process Payment</h3>
        <div class="form-group">
            <label>Item</label>
            <input type="text" id="modal_desc" readonly style="background:#f8fafc;">
        </div>
        <div class="form-group">
            <label>Amount</label>
            <input type="text" id="modal_amount" readonly style="background:#f8fafc;font-weight:700;">
        </div>
        <div class="form-group">
            <label>Payment Method *</label>
            <select id="modal_method">
                <option value="">-- Select --</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Transfer">Transfer</option>
                <option value="POS">POS</option>
            </select>
        </div>
        <input type="hidden" id="modal_billing_id">
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closePayModal()">Cancel</button>
            <button class="btn-confirm" id="confirmPayBtn" onclick="confirmPay()">Confirm Payment</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
function openPayModal(billingId, description, amount) {
    document.getElementById('modal_billing_id').value = billingId;
    document.getElementById('modal_desc').value = description;
    document.getElementById('modal_amount').value = '\u20A6' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('modal_method').value = '';
    document.getElementById('payModal').classList.add('active');
}

function closePayModal() {
    document.getElementById('payModal').classList.remove('active');
}

function confirmPay() {
    var billingId = document.getElementById('modal_billing_id').value;
    var method = document.getElementById('modal_method').value;
    var btn = document.getElementById('confirmPayBtn');

    if (!method) {
        swal('Error', 'Please select a payment method', 'error');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Processing...';

    fetch('pay_item.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({billing_id: parseInt(billingId), payment_method: method})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            swal('Success', data.message, 'success').then(() => {
                window.location.reload();
            });
        } else {
            swal('Error', data.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Confirm Payment';
        }
    })
    .catch(() => {
        swal('Error', 'Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = 'Confirm Payment';
    });
}
</script>

</body>
</html>
