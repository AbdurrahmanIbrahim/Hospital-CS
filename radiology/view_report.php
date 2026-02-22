<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3 AND $_SESSION['type'] != 9)) {
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
   FETCH SCAN LIST + SCAN + PATIENT
========================= */
$sql = "
    SELECT
        sl.*,
        s.name           AS scan_name,
        s.modality,
        s.body_part,

        ps.patient_id,
        ps.clinical_info AS ps_clinical_info,
        ps.is_walkin,
        ps.walkin_name,
        ps.walkin_phone,
        ps.date_request  AS ps_date_request,

        u.name           AS patient_name,
        u.hospital_num,
        u.phone          AS patient_phone,
        u.gender,
        u.age,

        asker.name       AS asker_name,
        radiologist.name AS radiologist_name,
        verifier.name    AS verifier_name

    FROM scan_lists sl
    JOIN patient_scan ps ON sl.patient_scan_id = ps.id
    JOIN scans s ON sl.scan_id = s.id
    LEFT JOIN users u ON ps.patient_id = u.id
    LEFT JOIN users asker ON sl.asker_id = asker.id
    LEFT JOIN users radiologist ON sl.radiologist_id = radiologist.id
    LEFT JOIN users verifier ON sl.verifier_id = verifier.id
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
   FETCH SCAN RESULTS
========================= */
$result = null;
$sql = "SELECT * FROM scan_results WHERE scan_list_id = '$scanlist_id' LIMIT 1";
$run = $db->query($sql);
if ($run && $run->num_rows > 0) {
    $result = $run->fetch_assoc();
}

/* =========================
   FETCH HOSPITAL DETAILS
========================= */
$sql = "SELECT * FROM hospital_details";
$run = $db->query($sql);
if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Hospital details not found';
    echo "<script>window.history.back()</script>";
    exit;
}
$hospital = $run->fetch_assoc();

/* Display values */
$display_name = ($scan['is_walkin'] == 1) ? $scan['walkin_name'] : $scan['patient_name'];
$display_id   = ($scan['is_walkin'] == 1) ? $scan['walkin_phone'] : $scan['hospital_num'];

$date_request  = $scan['date_request'] ? date('d M Y', strtotime($scan['date_request'])) : '-';
$date_performed = $scan['date_performed'] ? date('d M Y', strtotime($scan['date_performed'])) : '-';
$date_reported  = $scan['date_reported'] ? date('d M Y', strtotime($scan['date_reported'])) : '-';
$date_verified  = $scan['date_verified'] ? date('d M Y', strtotime($scan['date_verified'])) : '-';
$date_released  = $scan['date_released'] ? date('d M Y', strtotime($scan['date_released'])) : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Radiology Report</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>

:root {
    --hospital-blue: #0066cc;
    --hospital-light-blue: #e6f2ff;
    --hospital-dark-blue: #004080;
    --medical-green: #00a86b;
    --medical-red: #d32f2f;
    --border-gray: #e0e0e0;
    --text-dark: #2c3e50;
    --lab-gray: #f5f5f5;
}

/* Report Card */
.rad-report-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 102, 204, 0.15);
    overflow: hidden;
    border: 1px solid var(--border-gray);
    max-width: 1200px;
    margin: 30px auto;
}

/* Header */
.rad-header {
    background: linear-gradient(135deg, var(--hospital-blue) 0%, var(--hospital-dark-blue) 100%);
    color: white;
    padding: 25px 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.rad-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
    opacity: 0.1;
}

.rad-logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}

.rad-logo {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid white;
    padding: 5px;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.rad-title-container {
    margin-left: 25px;
    text-align: left;
}

.rad-title-main {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
}

.rad-subtitle {
    font-size: 14px;
    opacity: 0.95;
    margin-bottom: 3px;
    font-weight: 300;
}

.rad-report-title {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 2;
}

.rad-report-title h4 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    letter-spacing: 1px;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Patient Info */
.patient-info-section {
    padding: 30px;
    background: var(--hospital-light-blue);
    border-bottom: 3px solid var(--hospital-blue);
}

.medical-form-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.1);
}

.medical-form-table th {
    background: var(--hospital-blue);
    color: white;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-right: 1px solid rgba(255,255,255,0.2);
    min-width: 140px;
}

.medical-form-table th:last-child {
    border-right: none;
}

.medical-form-table td {
    padding: 16px 12px;
    background: white;
    border-bottom: 1px solid var(--border-gray);
    font-weight: 500;
    color: var(--text-dark);
    border-right: 1px solid var(--border-gray);
}

.medical-form-table tr:last-child td {
    border-bottom: none;
}

.medical-form-table td:last-child {
    border-right: none;
}

.medical-form-table tr:nth-child(even) td {
    background: #fafafa;
}

/* Report Sections */
.report-body {
    padding: 30px;
    background: white;
}

.report-section {
    margin-bottom: 25px;
    padding: 20px;
    background: var(--lab-gray);
    border-radius: 8px;
    border-left: 4px solid var(--hospital-blue);
}

.report-section-title {
    font-weight: 700;
    color: var(--hospital-dark-blue);
    margin-bottom: 12px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.report-section-content {
    background: white;
    padding: 18px;
    border-radius: 6px;
    border: 1px solid var(--border-gray);
    line-height: 1.8;
    white-space: pre-wrap;
    word-wrap: break-word;
    font-size: 15px;
    color: #333;
}

/* Signatures */
.signature-section {
    padding: 25px 30px;
    background: #fafafa;
    border-top: 2px solid var(--border-gray);
}

.signature-row {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    justify-content: space-between;
}

.signature-item {
    flex: 1;
    min-width: 200px;
    text-align: center;
    padding: 15px;
}

.signature-item .sig-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.signature-item .sig-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--hospital-dark-blue);
    border-top: 2px solid var(--hospital-blue);
    padding-top: 8px;
}

.signature-item .sig-date {
    font-size: 13px;
    color: #888;
    margin-top: 4px;
}

/* Disclaimer */
.disclaimer {
    text-align: center;
    padding: 20px;
    margin: 25px 30px;
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    border-radius: 8px;
    border: 2px solid var(--medical-red);
}

.disclaimer p {
    font-weight: 800;
    color: #b71c1c;
    font-size: 16px;
    letter-spacing: 0.5px;
    margin: 0;
}

/* Print Controls */
.print-controls {
    text-align: center;
    padding: 25px;
    background: var(--lab-gray);
    border-top: 1px solid var(--border-gray);
}

.print-btn {
    background: linear-gradient(135deg, var(--hospital-blue) 0%, var(--hospital-dark-blue) 100%);
    color: white;
    border: none;
    padding: 16px 45px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.print-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 102, 204, 0.4);
    background: linear-gradient(135deg, var(--hospital-dark-blue) 0%, var(--hospital-blue) 100%);
}

.back-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 16px 35px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    margin-left: 15px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: #5a6268;
    transform: translateY(-3px);
}

/* Print Styles */
@media print {
    .no-print {
        display: none !important;
        visibility: hidden !important;
    }

    .rad-report-card {
        box-shadow: none;
        border: 1px solid #000;
        margin: 0;
        max-width: 100%;
    }

    .print-btn, .back-btn {
        display: none !important;
    }

    .rad-header {
        background: white !important;
        color: black !important;
        border-bottom: 3px solid black;
        -webkit-print-color-adjust: exact;
    }

    .rad-header::before {
        display: none;
    }

    .rad-logo {
        border: 2px solid black;
    }

    .rad-report-title {
        background: white !important;
        border: 2px solid black;
    }

    .rad-report-title h4 {
        color: black !important;
        text-shadow: none !important;
    }

    .medical-form-table th {
        background: #f0f0f0 !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
    }

    .report-section {
        border-left-color: #333;
    }

    .disclaimer {
        border: 2px solid black !important;
        background: white !important;
    }

    .disclaimer p {
        color: black !important;
    }

    .signature-item .sig-name {
        border-top-color: #333;
        color: #333;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .rad-header {
        padding: 20px;
    }

    .rad-logo-container {
        flex-direction: column;
        text-align: center;
    }

    .rad-title-container {
        margin-left: 0;
        margin-top: 15px;
        text-align: center;
    }

    .patient-info-section {
        padding: 15px;
    }

    .medical-form-table {
        display: block;
        overflow-x: auto;
    }

    .report-body {
        padding: 15px;
    }

    .signature-row {
        flex-direction: column;
    }
}

</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="rad-report-card">

    <!-- HOSPITAL HEADER -->
    <div class="rad-header">
        <div class="rad-logo-container">
            <img src="<?= ROOT_URL ?>images/logos/<?=$hospital['logo']?>" class="rad-logo">
            <div class="rad-title-container">
                <h1 class="rad-title-main"><?=$hospital['name']?></h1>
                <div class="rad-subtitle"><?=$hospital['address'] ?? ''?></div>
                <div class="rad-subtitle">TEL: <?=$hospital['phone'] ?? ''?></div>
            </div>
        </div>

        <div class="rad-report-title">
            <h4>Radiology Report</h4>
        </div>
    </div>

    <!-- PATIENT INFO -->
    <div class="patient-info-section">
        <table class="medical-form-table">
            <tr>
                <th>Patient Name</th>
                <td><?= htmlspecialchars($display_name) ?></td>
                <th><?= ($scan['is_walkin'] == 1) ? 'Phone' : 'Hospital No' ?></th>
                <td><?= htmlspecialchars($display_id) ?></td>
            </tr>
            <tr>
                <th>Scan</th>
                <td><?= htmlspecialchars($scan['scan_name']) ?></td>
                <th>Modality</th>
                <td><?= htmlspecialchars($scan['modality']) ?></td>
            </tr>
            <tr>
                <th>Body Part</th>
                <td><?= htmlspecialchars($scan['body_part']) ?></td>
                <th>Requested By</th>
                <td><?= htmlspecialchars($scan['asker_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>Date Requested</th>
                <td><?= $date_request ?></td>
                <th>Date Performed</th>
                <td><?= $date_performed ?></td>
            </tr>
        </table>
    </div>

    <!-- REPORT BODY -->
    <div class="report-body">

        <?php if ($result): ?>

            <div class="report-section">
                <div class="report-section-title">Clinical Information</div>
                <div class="report-section-content"><?= nl2br(htmlspecialchars($result['clinical_info'])) ?></div>
            </div>

            <div class="report-section">
                <div class="report-section-title">Findings</div>
                <div class="report-section-content"><?= nl2br(htmlspecialchars($result['findings'])) ?></div>
            </div>

            <div class="report-section">
                <div class="report-section-title">Impression</div>
                <div class="report-section-content"><?= nl2br(htmlspecialchars($result['impression'])) ?></div>
            </div>

            <?php if (!empty($result['recommendation'])): ?>
            <div class="report-section">
                <div class="report-section-title">Recommendation</div>
                <div class="report-section-content"><?= nl2br(htmlspecialchars($result['recommendation'])) ?></div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align:center;padding:40px;color:#888;">
                <p>No report has been uploaded yet.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- SIGNATURES -->
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-item">
                <div class="sig-label">Radiologist</div>
                <div class="sig-name"><?= htmlspecialchars($scan['radiologist_name'] ?? '-') ?></div>
                <div class="sig-date"><?= $date_reported ?></div>
            </div>
            <?php if ($scan['status'] >= 4 && !empty($scan['verifier_name'])): ?>
            <div class="signature-item">
                <div class="sig-label">Verified By</div>
                <div class="sig-name"><?= htmlspecialchars($scan['verifier_name']) ?></div>
                <div class="sig-date"><?= $date_verified ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DISCLAIMER -->
    <div class="disclaimer">
        <p>Please note: Any alteration to this report renders it invalid</p>
    </div>

    <!-- PRINT CONTROLS -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="print-btn">
            Print Report
        </button>
        <a href="javascript:window.history.back()" class="back-btn">Back</a>
    </div>

</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
