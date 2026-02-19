<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3 AND $_SESSION['type'] != 4)) {
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
    u.gender,
    u.dob,
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

/* =========================
   FETCH REPORTS
========================= */
$reports = [];
$sql = "SELECT ar.*, u.name AS nurse_name FROM admission_reports ar
        INNER JOIN users u ON u.id = ar.nurse_id
        WHERE ar.admission_id = '$admission_id'
        ORDER BY ar.created_at DESC";
$run = $db->query($sql);
if ($run) {
    while ($row = $run->fetch_assoc()) $reports[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Admission Reports</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header">
        <div>
            <h1>Admission Reports</h1>
            <p>Nurse reports for <?= htmlspecialchars($admission['patient_name']) ?></p>
        </div>
        <div class="header-actions">
            <a href="view.php" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back to Admissions
            </a>
            <a href="billing.php?id=<?= $admission_id ?>" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;">
                View Billing
            </a>
        </div>
    </div>

    <!-- Patient Info -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Patient Details</h2>
            <?php if($admission['status'] == 0): ?>
                <span style="background:#dcfce7;color:#15803d;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">Active Admission</span>
            <?php else: ?>
                <span style="background:#f1f5f9;color:#64748b;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">Discharged</span>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding: 24px;">
            <div class="form-row">
                <div class="form-group">
                    <label>Patient</label>
                    <p style="font-weight:600;"><?= htmlspecialchars($admission['patient_name']) ?> (<?= htmlspecialchars($admission['hospital_num']) ?>)</p>
                </div>
                <div class="form-group">
                    <label>Room / Ward</label>
                    <p style="font-weight:600;"><?= htmlspecialchars($admission['room_name']) ?> - Bed <?= $admission['bed_number'] ?> (<?= htmlspecialchars($admission['ward_name'] ?? 'N/A') ?>)</p>
                </div>
                <div class="form-group">
                    <label>Attending Doctor</label>
                    <p><?= htmlspecialchars($admission['doctor_name'] ?? 'N/A') ?></p>
                </div>
                <div class="form-group">
                    <label>Admitted Since</label>
                    <p><?= formatDateReadableWithTime($admission['admission_date']) ?></p>
                </div>
            </div>
            <?php if(!empty($admission['notes'])): ?>
            <div class="form-group" style="margin-top:8px;">
                <label>Admission Notes</label>
                <p><?= htmlspecialchars($admission['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Report Form (Nurses and Admin only) -->
    <?php if($admission['status'] == 0 && ($_SESSION['type'] == 0 || $_SESSION['type'] == 4)): ?>
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Add New Report</h2>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form method="POST" action="add_report.php">
                <input type="hidden" name="admission_id" value="<?= $admission_id ?>">
                <div class="form-group">
                    <label>Report *</label>
                    <textarea name="report" rows="5" required placeholder="Enter patient observation, vitals, condition updates, medication notes, etc."></textarea>
                </div>
                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn-primary" style="padding: 10px 24px;">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reports List -->
    <div class="card">
        <div class="card-header">
            <h2>Report History</h2>
            <span style="color:var(--text-muted);font-size:14px;"><?= count($reports) ?> report(s)</span>
        </div>
        <div style="padding: 24px;">
            <?php if(count($reports) > 0): ?>
                <?php foreach($reports as $report): ?>
                <div style="border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <div>
                            <strong style="font-size:15px;"><?= htmlspecialchars($report['nurse_name']) ?></strong>
                            <span style="background:#eff6ff;color:#2563eb;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600;margin-left:8px;">Nurse</span>
                        </div>
                        <span style="color:var(--text-muted);font-size:13px;"><?= formatDateReadableWithTime($report['created_at']) ?></span>
                    </div>
                    <p style="line-height:1.7;color:var(--text-main);white-space:pre-wrap;"><?= htmlspecialchars($report['report']) ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center;padding:40px;color:var(--text-muted);">
                    <p>No reports have been submitted yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
