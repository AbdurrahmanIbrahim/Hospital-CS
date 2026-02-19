<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3 AND $_SESSION['type'] != 4)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admission';

/* =========================
   FILTER & PAGINATION
========================= */
$status_filter = isset($_GET['status']) ? $_GET['status'] : '0';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE
========================= */
$where = "WHERE 1";

if ($status_filter === '0') {
    $where .= " AND a.status = 0";
} elseif ($status_filter === '1') {
    $where .= " AND a.status = 1";
} elseif ($status_filter !== 'all') {
    $where .= " AND a.status = 0";
}

if (!empty($search)) {
    $where .= " AND (u.name LIKE '%$search%' OR u.hospital_num LIKE '%$search%' OR u.phone LIKE '%$search%')";
}

if (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(a.admission_date) BETWEEN '$start_date' AND '$end_date'";
} elseif (!empty($start_date)) {
    $where .= " AND DATE(a.admission_date) = '$start_date'";
}

/* =========================
   PROCESS ROOM BILLING FOR ACTIVE ADMISSIONS
========================= */
if ($status_filter === '0' || $status_filter === 'all') {
    $active = $db->query("SELECT id FROM admissions WHERE status = 0");
    if ($active) {
        while ($row = $active->fetch_assoc()) {
            processRoomBilling($row['id']);
        }
    }
}

/* =========================
   FETCH ADMISSIONS
========================= */
$count_sql = "SELECT COUNT(*) AS total FROM admissions a INNER JOIN users u ON u.id = a.patient_id $where";
$total = $db->query($count_sql)->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

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
$where
ORDER BY a.admission_date DESC
LIMIT $limit OFFSET $offset";
$run = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Admissions</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header">
        <div>
            <h1>
                <?php if($status_filter === '1'): ?>Discharged Patients
                <?php elseif($status_filter === 'all'): ?>All Admissions
                <?php else: ?>Active Admissions<?php endif; ?>
            </h1>
            <p><?= $total ?> record(s) found</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 20px;">
            <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <div class="form-group" style="margin-bottom:0;flex:1;min-width:180px;">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Name, Hospital No, Phone..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>From</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>To</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <button type="submit" class="btn-primary" style="height:44px;">Filter</button>
            </form>
        </div>
    </div>

    <!-- Admissions Table -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Hospital No</th>
                    <th>Room</th>
                    <th>Bed</th>
                    <th>Doctor</th>
                    <th>Admitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($run && $run->num_rows > 0): ?>
                    <?php while($row = $run->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['patient_name']) ?></strong><br>
                            <small style="color:var(--text-muted);"><?= htmlspecialchars($row['phone']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['hospital_num']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['room_name']) ?><br>
                            <small style="color:var(--text-muted);"><?= htmlspecialchars($row['ward_name'] ?? '') ?></small>
                        </td>
                        <td>Bed <?= $row['bed_number'] ?></td>
                        <td><?= htmlspecialchars($row['doctor_name'] ?? 'N/A') ?></td>
                        <td>
                            <?= formatDateReadable($row['admission_date']) ?><br>
                            <small style="color:var(--text-muted);"><?= date('g:i A', strtotime($row['admission_date'])) ?></small>
                        </td>
                        <td>
                            <?php if($row['status'] == 0): ?>
                                <span style="background:#dcfce7;color:#15803d;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">Active</span>
                            <?php elseif($row['status'] == 1): ?>
                                <span style="background:#f1f5f9;color:#64748b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">Discharged</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="reports.php?id=<?= $row['id'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Reports</a>
                                <a href="billing.php?id=<?= $row['id'] ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px;">Billing</a>
                                <?php if($row['status'] == 0 && ($_SESSION['type'] == 0 || $_SESSION['type'] == 3)): ?>
                                    <a href="discharge.php?id=<?= $row['id'] ?>" class="btn-danger" style="padding:6px 12px;font-size:12px;width:auto;" onclick="return confirm('Are you sure you want to discharge this patient?')">Discharge</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">No admissions found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:24px;">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>"
               style="padding:8px 14px;border-radius:8px;border:1px solid var(--border);text-decoration:none;color:var(--text-main);font-size:14px;<?= $page == $i ? 'background:var(--primary);color:white;border-color:var(--primary);' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
