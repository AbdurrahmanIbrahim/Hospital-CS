<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'radiology';

/* =========================
   VALIDATE REQUEST
========================= */
if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$scanlist_id = intval($_GET['id']);

if ($scanlist_id <= 0) {
    $_SESSION['error'] = 'Invalid scan reference';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   FETCH SCAN RECORD + PATIENT INFO
========================= */
$sql = "
    SELECT
        sl.*,
        s.name AS scan_name,
        s.modality,
        s.body_part,
        ps.patient_id,
        ps.clinical_info,
        ps.is_walkin,
        ps.walkin_name,
        ps.walkin_phone,
        ps.date_request AS ps_date_request,
        u.name AS patient_name,
        u.hospital_num,
        u.phone AS patient_phone
    FROM scan_lists sl
    JOIN patient_scan ps ON sl.patient_scan_id = ps.id
    JOIN scans s ON sl.scan_id = s.id
    LEFT JOIN users u ON ps.patient_id = u.id
    WHERE sl.id = '$scanlist_id'
";

$run = $db->query($sql);

if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Scan record not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$scan = $run->fetch_assoc();

/* =========================
   CHECK IF RESULT EXISTS (EDIT MODE)
========================= */
$existing_result = null;
$sql = "SELECT * FROM scan_results WHERE scan_list_id = '$scanlist_id' LIMIT 1";
$run = $db->query($sql);
if ($run && $run->num_rows > 0) {
    $existing_result = $run->fetch_assoc();
}

/* Determine display values */
$display_name = ($scan['is_walkin'] == 1) ? $scan['walkin_name'] : $scan['patient_name'];
$display_id   = ($scan['is_walkin'] == 1) ? $scan['walkin_phone'] : $scan['hospital_num'];
$display_date = $scan['date_request'] ? date('d M Y', strtotime($scan['date_request'])) : '-';

/* Pre-fill values */
$clinical_info  = $existing_result ? $existing_result['clinical_info'] : $scan['clinical_info'];
$findings       = $existing_result ? $existing_result['findings'] : '';
$impression     = $existing_result ? $existing_result['impression'] : '';
$recommendation = $existing_result ? $existing_result['recommendation'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Radiology Report</title>
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

.btn-primary:hover {
    opacity: 0.9;
}

.report-form .form-group {
    margin-bottom: 20px;
}

.report-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.report-form textarea,
.report-form input[type="text"] {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    box-sizing: border-box;
}

.report-form textarea:focus,
.report-form input[type="text"]:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
}

.patient-info-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 24px;
}

.patient-info-box .info-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 10px;
}

.patient-info-box .info-item {
    flex: 1;
    min-width: 180px;
}

.patient-info-box .info-item strong {
    display: block;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.patient-info-box .info-item span {
    font-size: 15px;
    color: #212529;
    font-weight: 500;
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
        <h2><?= $existing_result ? 'Edit' : 'Upload' ?> Radiology Report</h2>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- PATIENT INFO -->
        <div class="patient-info-box">
            <div class="info-row">
                <div class="info-item">
                    <strong>Patient Name</strong>
                    <span><?= htmlspecialchars($display_name) ?></span>
                </div>
                <div class="info-item">
                    <strong><?= ($scan['is_walkin'] == 1) ? 'Phone' : 'Hospital No' ?></strong>
                    <span><?= htmlspecialchars($display_id) ?></span>
                </div>
                <div class="info-item">
                    <strong>Scan</strong>
                    <span><?= htmlspecialchars($scan['scan_name']) ?></span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <strong>Modality</strong>
                    <span><?= htmlspecialchars($scan['modality']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Body Part</strong>
                    <span><?= htmlspecialchars($scan['body_part']) ?></span>
                </div>
                <div class="info-item">
                    <strong>Date Requested</strong>
                    <span><?= $display_date ?></span>
                </div>
            </div>
        </div>

        <!-- REPORT FORM -->
        <form method="POST" action="submit_report.php" class="report-form" enctype="multipart/form-data">
            <input type="hidden" name="scan_list_id" value="<?= $scanlist_id ?>">

            <div class="form-group">
                <label for="clinical_info">Clinical Information</label>
                <textarea name="clinical_info" id="clinical_info" rows="4"
                          placeholder="Enter clinical information..."><?= htmlspecialchars($clinical_info) ?></textarea>
            </div>

            <div class="form-group">
                <label for="findings">Findings</label>
                <textarea name="findings" id="findings" rows="10"
                          placeholder="Enter detailed findings..."><?= htmlspecialchars($findings) ?></textarea>
            </div>

            <div class="form-group">
                <label for="impression">Impression</label>
                <textarea name="impression" id="impression" rows="5"
                          placeholder="Enter impression..."><?= htmlspecialchars($impression) ?></textarea>
            </div>

            <div class="form-group">
                <label for="recommendation">Recommendation</label>
                <textarea name="recommendation" id="recommendation" rows="4"
                          placeholder="Enter recommendation..."><?= htmlspecialchars($recommendation) ?></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Upload Scan Image / File</label>
                <?php if ($existing_result && !empty($existing_result['attachment'])): ?>
                    <div style="margin-bottom:10px;padding:10px;background:#e8f5e9;border-radius:6px;border:1px solid #c8e6c9;">
                        <span style="color:#2e7d32;font-weight:600;">Current file:</span>
                        <a href="../images/scans/<?= htmlspecialchars($existing_result['attachment']) ?>" target="_blank" style="color:#1565c0;text-decoration:underline;">
                            <?= htmlspecialchars($existing_result['attachment']) ?>
                        </a>
                        <br><small style="color:#666;">Upload a new file to replace it, or leave empty to keep current file.</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="attachment" id="attachment" class="form-control"
                       accept="image/*,.pdf,.dcm"
                       style="padding:10px;border:2px dashed #ddd;border-radius:8px;background:#fafafa;cursor:pointer;">
                <small style="color:#6c757d;display:block;margin-top:6px;">Accepted: Images (JPG, PNG, etc.), PDF, DICOM (.dcm). Max size: 10MB</small>
            </div>

            <div style="text-align:center;margin-top:20px;">
                <button type="submit" class="btn-primary">Submit Report</button>
                <a href="index.php?status=2" style="margin-left:15px;color:#6c757d;text-decoration:none;font-weight:600;">Cancel</a>
            </div>
        </form>

    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
