<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'pharmacy';

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
$where = "WHERE p.purpose = 2 AND p.appointment_id = 0";

if (empty($start_date) && empty($end_date)) {
    $today = date('Y-m-d');
    $where .= " AND DATE(p.payment_date) = '$today'";
} elseif (!empty($start_date) && empty($end_date)) {
    $where .= " AND DATE(p.payment_date) = '$start_date'";
} elseif (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(p.payment_date) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($search)) {
    $where .= " AND (u.name LIKE '%$search%' OR p.reciept_num LIKE '%$search%' OR p.note LIKE '%$search%')";
}

/* =========================
   FILTER TEXT
========================= */
$filterText = "POS Sales History";

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
    LEFT JOIN users u ON u.id = p.patient_id AND u.type = 1
    $where
";
$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   FETCH POS SALES
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
        u.name AS patient_name,
        (SELECT COUNT(*) FROM drug_list dl
         INNER JOIN patient_drugs pd ON pd.id = dl.patient_drugs_id
         WHERE pd.payment_id = p.id) AS items_count
    FROM payments p
    LEFT JOIN users u ON u.id = p.patient_id AND u.type = 1
    $where
    ORDER BY p.id DESC
    LIMIT $limit OFFSET $offset
";

$sales = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | POS Sales History</title>
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

/* No records message styling */
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

/* Action button styling */
.table-actions a[href*="index.php"], .modal_btn {
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

.modal_btn {
    background-color: blue;
    border-color: blue;
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

.walkin-badge {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.registered-badge {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
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

#staffType:focus,
#searchQuery:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

#searchQuery::placeholder {
    color: #6c757d;
    opacity: 0.8;
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

</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header">
        <div>
            <h1>POS Sales History</h1>
            <p>View all point-of-sale drug transactions</p>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn-secondary" style="padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back to POS
            </a>
        </div>
    </div>

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
                            Search (Name, Receipt No)
                        </label>
                        <div class="search-input-wrapper" style="display: flex;">
                            <input type="text"
                                   name="search"
                                   id="searchQuery"
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                                   placeholder="Enter patient name or receipt number..."
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

                </div>
            </form>
        </div>

        <?php if ($sales->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">
                No POS sales found for the selected period.
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table" style="overflow:hidden;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt No</th>
                        <th>Patient / Walk-In</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Payment Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $offset + 1; while ($row = $sales->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($row['reciept_num']) ?></td>
                        <td>
                            <?php if ($row['patient_id'] == 0): ?>
                                <?php
                                    // Extract walk-in name from note
                                    $walkin_display = $row['note'];
                                    if (strpos($row['note'], 'POS Walk-In: ') === 0) {
                                        $walkin_display = str_replace('POS Walk-In: ', '', $row['note']);
                                    }
                                ?>
                                <?= htmlspecialchars($walkin_display) ?>
                                <br><span class="walkin-badge">Walk-In</span>
                            <?php else: ?>
                                <?= htmlspecialchars($row['patient_name'] ?? 'Unknown') ?>
                                <br><span class="registered-badge">Registered</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($row['payment_date'])) ?></td>
                        <td style="text-align:center;"><?= $row['items_count'] ?></td>
                        <td>&#8358;<?= number_format($row['amount'], 2) ?></td>
                        <td>
                            <?php if ($row['discount'] > 0): ?>
                                <?= $row['discount'] ?>%
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:600;">&#8358;<?= number_format($row['net_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                        <td class="table-actions">
                            <a href="../reciept/index.php?id=<?= $row['id'] ?>" class="modal_btn">View Receipt</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:24px;display:flex;justify-content:center;">
            <ul style="list-style:none;display:flex;gap:8px;padding:0;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li>
                    <a href="?page=<?= $p ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&search=<?= urlencode($search) ?>"
                       style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;
                       <?= $p == $page ? 'background:#007bff;color:#fff;' : '' ?>">
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

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
