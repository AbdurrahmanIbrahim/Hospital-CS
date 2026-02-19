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

$sql = "SELECT * FROM admission_billing WHERE admission_id = '$admission_id' ORDER BY created_at ASC";
$run = $db->query($sql);
if ($run) {
    while ($row = $run->fetch_assoc()) {
        $billing_items[] = $row;
        if ($row['billing_type'] == 1) $room_total += $row['amount'];
        elseif ($row['billing_type'] == 2) $drug_total += $row['amount'];
        else $other_total += $row['amount'];
    }
}

$grand_total = $room_total + $drug_total + $other_total;

// Calculate days admitted
$admitted_date = new DateTime($admission['admission_date']);
$now = $admission['status'] == 1 ? new DateTime($admission['discharge_date']) : new DateTime();
$days = $admitted_date->diff($now)->days + 1;
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
                <div class="stat-icon-wrap" style="background:#fef3c7;">
                    <span style="color:#d97706;">&#127968;</span>
                </div>
            </div>
            <h3>&#8358;<?= number_format($room_total, 2) ?></h3>
            <p>Room Charges</p>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon-wrap" style="background:#dcfce7;">
                    <span style="color:#15803d;">&#128138;</span>
                </div>
            </div>
            <h3>&#8358;<?= number_format($drug_total, 2) ?></h3>
            <p>Drug Charges</p>
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
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc;font-weight:700;">
                        <td colspan="3" style="text-align:right;font-size:16px;">Grand Total:</td>
                        <td style="font-size:16px;color:var(--primary);">&#8358;<?= number_format($grand_total, 2) ?></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">No billing items yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
