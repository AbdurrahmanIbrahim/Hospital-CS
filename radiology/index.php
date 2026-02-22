<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'radiology';

$status     = isset($_GET['status']) ? sanitize($_GET['status']) : '';

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
   WHERE CLAUSE
========================= */
$where = " WHERE 1=1 ";

if ($status !== '') {
    $where .= " AND sl.status = '$status'";
}

/* =========================
   STATUS LABEL
========================= */
$statusLabels = [
    ''  => 'All Scans',
    '1' => 'Pending Scans',
    '2' => 'Performed - Awaiting Report',
    '3' => 'Reports - Awaiting Verification',
    '4' => 'Verified - Awaiting Release',
    '5' => 'Released'
];
$filterText = "Radiology - " . ($statusLabels[$status] ?? 'All Scans');

/* =========================
   DATE FILTER
========================= */
if (!empty($start_date) && empty($end_date)) {
    $where .= " AND DATE(sl.date_request) = '$start_date'";
    $filterText .= " for <strong>" . formatDateReadable($start_date) . "</strong>";
} elseif (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(sl.date_request) BETWEEN '$start_date' AND '$end_date'";
    $filterText .= " from <strong>" . formatDateReadable($start_date) . "</strong>
                     to <strong>" . formatDateReadable($end_date) . "</strong>";
}

/* =========================
   SEARCH FILTER
========================= */
if (!empty($search)) {
    $where .= " AND (
        s.name LIKE '%$search%'
        OR u.name LIKE '%$search%'
        OR u.hospital_num LIKE '%$search%'
        OR u.phone LIKE '%$search%'
        OR ps.walkin_name LIKE '%$search%'
        OR ps.walkin_phone LIKE '%$search%'
    )";
}

/* =========================
   COUNT FOR PAGINATION
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM scan_lists sl
    JOIN patient_scan ps ON sl.patient_scan_id = ps.id
    JOIN scans s ON sl.scan_id = s.id
    LEFT JOIN users u ON ps.patient_id = u.id
    LEFT JOIN users asker ON sl.asker_id = asker.id
    $where
";

$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   FETCH SCAN RECORDS
========================= */
$sql = "
    SELECT
        sl.id            AS scanlist_id,
        sl.scan_id,
        sl.asker_id,
        sl.status        AS scanlist_status,
        sl.radiologist_id,
        sl.verifier_id,
        sl.date_request,
        sl.date_performed,
        sl.date_reported,
        sl.date_verified,
        sl.date_released,
        sl.paid,
        sl.amount,
        sl.notes,

        s.name           AS scan_name,
        s.modality,
        s.body_part,

        ps.patient_id,
        ps.priority,
        ps.clinical_info,
        ps.is_walkin,
        ps.walkin_name,
        ps.walkin_phone,
        ps.date_request  AS ps_date_request,

        u.name           AS patient_name,
        u.hospital_num,
        u.phone          AS patient_phone,

        asker.name       AS asker_name

    FROM scan_lists sl
    JOIN patient_scan ps ON sl.patient_scan_id = ps.id
    JOIN scans s ON sl.scan_id = s.id
    LEFT JOIN users u ON ps.patient_id = u.id
    LEFT JOIN users asker ON sl.asker_id = asker.id

    $where
    ORDER BY sl.date_request DESC
    LIMIT $limit OFFSET $offset
";

$scans = $db->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Radiology</title>
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

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-responsive table {
    min-width: 1000px;
    border-collapse: collapse;
}

.action-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.action-links a {
    display: inline-block;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border-radius: 6px;
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.action-links a:hover {
    background: #e2e8f0;
    color: #020617;
}

.action-links a.perform {
    background: #e0f2fe;
    color: #0369a1;
    border-color: #bae6fd;
}

.action-links a.report {
    background: #ecfeff;
    color: #0f766e;
    border-color: #99f6e4;
}

.action-links a.verify {
    background: #fef9c3;
    color: #854d0e;
    border-color: #fde68a;
}

.action-links a.release {
    background: #fce7f3;
    color: #9d174d;
    border-color: #fbcfe8;
}

.action-links a.print {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

/* Status tabs */
.status-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.status-tabs a {
    display: inline-block;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.status-tabs a:hover {
    background: #e2e8f0;
}

.status-tabs a.active {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
}

@media (max-width: 768px) {
    .search-filters {
        flex-direction: column;
        gap: 16px;
    }

    .filter-group {
        margin-left: 0 !important;
        width: 100%;
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

#searchQuery:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

#searchQuery::placeholder {
    color: #6c757d;
    opacity: 0.8;
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

        <!-- STATUS TABS -->
        <div class="status-tabs">
            <a href="index.php" class="<?= $status === '' ? 'active' : '' ?>">All</a>
            <a href="index.php?status=1" class="<?= $status === '1' ? 'active' : '' ?>">Pending</a>
            <a href="index.php?status=2" class="<?= $status === '2' ? 'active' : '' ?>">Perform</a>
            <a href="index.php?status=3" class="<?= $status === '3' ? 'active' : '' ?>">Report</a>
            <a href="index.php?status=4" class="<?= $status === '4' ? 'active' : '' ?>">Verify</a>
            <a href="index.php?status=5" class="<?= $status === '5' ? 'active' : '' ?>">Release</a>
        </div>

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
                        <a href="?<?= $status !== '' ? 'status='.$status : '' ?>"
                           class="reset-btn"
                           style="display: inline-block; padding: 10px 16px; background-color: #6c757d; color: white; border: 1px solid #6c757d; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                            Reset
                        </a>
                    </div>
                    <input type="hidden" name="status" value="<?=$status?>">
                </div>
            </form>
        </div>

<?php if ($scans->num_rows == 0): ?>
    <p style="text-align:center;padding:30px;color:#777;">
        No scan requests found.
    </p>
<?php else: ?>

<div class="table-responsive">
    <table class="table" style="overflow:hidden;">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Hospital No</th>
                <th>Scan</th>
                <th>Modality</th>
                <th>Priority</th>
                <th>Requested</th>
                <th>Paid</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = $offset + 1; while ($row = $scans->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>

                <td>
                    <?php if ($row['is_walkin'] == 1): ?>
                        <?= htmlspecialchars($row['walkin_name']) ?>
                        <br><small style="color:#888;">(Walk-in)</small>
                    <?php else: ?>
                        <?= htmlspecialchars($row['patient_name']) ?>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($row['is_walkin'] == 1): ?>
                        <?= htmlspecialchars($row['walkin_phone']) ?>
                    <?php else: ?>
                        <?= htmlspecialchars($row['hospital_num']) ?>
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($row['scan_name']) ?></td>

                <td><?= htmlspecialchars($row['modality']) ?></td>

                <td>
                    <?= htmlspecialchars($row['priority']) ?>
                </td>

                <td><?= $row['date_request'] ? date('d M Y', strtotime($row['date_request'])) : '-' ?></td>

                <td>
                    <?= $row['paid'] == 1
                        ? '<span class="status-paid" style="color:#0a7a0a;font-weight:600;">Paid</span>'
                        : '<span class="status-pending" style="color:#c0392b;font-weight:600;">Unpaid</span>' ?>
                </td>

                <td>
                    <?= get_scan_status_badge($row['scanlist_status']) ?>
                </td>

                <td>
                    <div class="action-links">
                    <?php if ($row['scanlist_status'] == 1): ?>
                        <a href="perform.php?id=<?=$row['scanlist_id']?>" class="perform">Perform Scan</a>
                    <?php endif; ?>

                    <?php if ($row['scanlist_status'] == 2): ?>
                        <a href="report.php?id=<?=$row['scanlist_id']?>" class="report">Upload Report</a>
                    <?php endif; ?>

                    <?php if ($row['scanlist_status'] == 3): ?>
                        <a href="verify.php?id=<?=$row['scanlist_id']?>" class="verify">Verify</a>
                    <?php endif; ?>

                    <?php if ($row['scanlist_status'] == 4): ?>
                        <a href="confirm.php?id=<?=$row['scanlist_id']?>" class="release">Release</a>
                        <a href="view_report.php?id=<?=$row['scanlist_id']?>" class="print">View Report</a>
                    <?php endif; ?>

                    <?php if ($row['scanlist_status'] == 5): ?>
                        <a href="view_report.php?id=<?=$row['scanlist_id']?>" class="print">View Report</a>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div style="margin-top:24px;display:flex;justify-content:center;">
        <ul style="list-style:none;display:flex;gap:8px;padding:0;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li>
                <a href="?page=<?= $p ?>
                    &status=<?= urlencode($status) ?>
                    &start_date=<?= urlencode($start_date) ?>
                    &end_date=<?= urlencode($end_date) ?>
                    &search=<?= urlencode($search) ?>"
                   style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;
                   <?= $p == $page ? 'background:#007bff;color:#fff;' : '' ?>">
                   <?= $p ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
