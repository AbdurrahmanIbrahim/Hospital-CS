<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

/* =========================
   FILTER & PAGINATION
========================= */
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date   = isset($_GET['end_date'])   ? sanitize($_GET['end_date'])   : '';
$search     = isset($_GET['search'])     ? sanitize($_GET['search'])     : '';

$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 20;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE
========================= */
$where = "WHERE p.purpose = 3 AND p.appointment_id = 0";

if (empty($start_date) && empty($end_date)) {
    $today = date('Y-m-d');
    $where .= " AND DATE(p.payment_date) = '$today'";
} elseif (!empty($start_date) && empty($end_date)) {
    $where .= " AND DATE(p.payment_date) = '$start_date'";
} elseif (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(p.payment_date) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($search)) {
    $where .= " AND (
        p.reciept_num LIKE '%$search%'
        OR p.note LIKE '%$search%'
        OR u.name LIKE '%$search%'
        OR u.hospital_num LIKE '%$search%'
    )";
}

/* =========================
   FILTER DESCRIPTION
========================= */
$filterText = "Lab POS Sales History";

if (empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>today</strong>";
} elseif (!empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>" . formatDateReadable($start_date) . "</strong>";
} elseif (!empty($start_date) && !empty($end_date)) {
    $filterText .= " from <strong>" . formatDateReadable($start_date) . "</strong> to <strong>" . formatDateReadable($end_date) . "</strong>";
}

/* =========================
   COUNT FOR PAGINATION
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM payments p
    LEFT JOIN users u ON u.id = p.patient_id
    $where
";

$countResult = $db->query($countSql);
$totalRows   = $countResult ? $countResult->fetch_assoc()['total'] : 0;
$totalPages  = ceil($totalRows / $limit);

/* =========================
   FETCH RECORDS
========================= */
$sql = "
    SELECT
        p.id,
        p.patient_id,
        p.amount,
        p.discount,
        p.net_amount,
        p.`payment-method` AS payment_method,
        p.payment_date,
        p.reciept_num,
        p.note,
        p.status,
        IFNULL(u.name, '') AS patient_name,
        IFNULL(u.hospital_num, '') AS hospital_num
    FROM payments p
    LEFT JOIN users u ON u.id = p.patient_id
    $where
    ORDER BY p.id DESC
    LIMIT $limit OFFSET $offset
";

$records = $db->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Lab POS History</title>
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
    min-width: 900px;
    border-collapse: collapse;
}

.badge-walkin {
    display: inline-block;
    padding: 3px 10px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-registered {
    display: inline-block;
    padding: 3px 10px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 16px;
}

.back-link:hover {
    text-decoration: underline;
}

.action-btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: var(--primary);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
}

/* Summary stats */
.stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.stat-box {
    flex: 1;
    min-width: 140px;
    padding: 14px 18px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    text-align: center;
}

.stat-box .stat-val {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-dark);
}

.stat-box .stat-label {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 500;
}

/* Responsive */
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
    .stats-row {
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

<a href="index.php" class="back-link">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Back to Lab POS
</a>

<div class="card">
    <div class="card-header">
        <h2><?= $filterText ?></h2>
    </div>

    <div class="card-body" style="padding:24px;">

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
                            Search (Receipt, Name, Hospital No)
                        </label>
                        <div class="search-input-wrapper" style="display: flex;">
                            <input type="text"
                                   name="search"
                                   id="searchQuery"
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                                   placeholder="Enter receipt number, patient name..."
                                   style="flex-grow: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px 0 0 6px; font-size: 14px; border-right: none;">
                            <button type="submit"
                                    class="search-btn"
                                    style="padding: 10px 20px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;">
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Reset -->
                    <div class="filter-group" style="margin-left: 16px; align-self: flex-end;">
                        <a href="history.php"
                           class="reset-btn"
                           style="display: inline-block; padding: 10px 16px; background-color: #6c757d; color: white; border: 1px solid #6c757d; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>

        <?php
        /* Quick stats */
        $totalSales = 0;
        $totalDiscount = 0;
        $totalNet = 0;
        $orderCount = $totalRows;

        $statsQ = $db->query("
            SELECT
                COALESCE(SUM(p.amount), 0)     AS total_sales,
                COALESCE(SUM(p.discount), 0)   AS total_discount,
                COALESCE(SUM(p.net_amount), 0) AS total_net
            FROM payments p
            LEFT JOIN users u ON u.id = p.patient_id
            $where
        ");
        if ($statsQ && $statsQ->num_rows > 0) {
            $stats = $statsQ->fetch_assoc();
            $totalSales    = $stats['total_sales'];
            $totalDiscount = $stats['total_discount'];
            $totalNet      = $stats['total_net'];
        }
        ?>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-val"><?= $orderCount ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-box">
                <div class="stat-val">N<?= number_format($totalSales, 2) ?></div>
                <div class="stat-label">Gross Amount</div>
            </div>
            <div class="stat-box">
                <div class="stat-val">N<?= number_format($totalDiscount, 2) ?></div>
                <div class="stat-label">Total Discount</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color: var(--success);">N<?= number_format($totalNet, 2) ?></div>
                <div class="stat-label">Net Revenue</div>
            </div>
        </div>

        <?php if (!$records || $records->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">
                No POS lab sales found for this period.
            </p>
        <?php else: ?>

        <div class="table-responsive">
            <table class="table" style="overflow:hidden;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt #</th>
                        <th>Patient / Walk-In</th>
                        <th>Date</th>
                        <th>Tests</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Net</th>
                        <th>Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; while ($row = $records->fetch_assoc()):
                        // Determine display name
                        $displayName = '';
                        $badgeClass  = '';
                        if ($row['patient_id'] > 0 && $row['patient_name'] !== '') {
                            $displayName = htmlspecialchars($row['patient_name']);
                            if ($row['hospital_num']) {
                                $displayName .= ' <small style="color:var(--text-muted);">(' . htmlspecialchars($row['hospital_num']) . ')</small>';
                            }
                            $badgeClass = 'badge-registered';
                            $badgeText  = 'Registered';
                        } else {
                            // Extract walk-in name from note
                            $displayName = htmlspecialchars($row['note']);
                            $badgeClass = 'badge-walkin';
                            $badgeText  = 'Walk-In';
                        }

                        // Count tests for this payment
                        $testCountQ = $db->query("
                            SELECT COUNT(*) AS cnt
                            FROM patient_test pt
                            JOIN test_lists tl ON tl.patient_test_id = pt.id
                            WHERE pt.payment_id = '" . intval($row['id']) . "'
                        ");
                        $testCount = ($testCountQ && $testCountQ->num_rows > 0) ? $testCountQ->fetch_assoc()['cnt'] : 0;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($row['reciept_num']) ?></strong></td>
                        <td>
                            <?= $displayName ?><br>
                            <span class="<?= $badgeClass ?>"><?= $badgeText ?></span>
                        </td>
                        <td><?= date('d M Y, g:i A', strtotime($row['payment_date'])) ?></td>
                        <td style="text-align:center;"><?= $testCount ?></td>
                        <td>N<?= number_format($row['amount'], 2) ?></td>
                        <td>N<?= number_format($row['discount'], 2) ?></td>
                        <td><strong>N<?= number_format($row['net_amount'], 2) ?></strong></td>
                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                        <td>
                            <a href="../reciept/index.php?id=<?= $row['id'] ?>" class="action-btn">View Receipt</a>
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
                        <a href="?page=<?= $p ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>"
                           style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:var(--text-main);
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

</div><!-- /content-scroll -->

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
