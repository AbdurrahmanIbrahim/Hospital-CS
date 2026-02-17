<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

/* =========================
   Search & Pagination
========================= */
$search   = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$type     = isset($_GET['type']) ? intval($_GET['type']) : 0; // Test type filter
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit    = 20;
$offset   = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE
========================= */
$where = "WHERE t.status != -1";

if (!empty($search)) {
    $where .= " AND t.name LIKE '%$search%'";
}

if ($type > 0) {
    $where .= " AND t.type = '$type'";
}

/* =========================
   Pagination Count
========================= */
$countSql   = "SELECT COUNT(*) AS total FROM tests t $where";
$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   Fetch Tests
========================= */
$sql = "
    SELECT t.*, s.name AS specimen_name
    FROM tests t
    LEFT JOIN specimen s ON t.specimen = s.id
    $where
    ORDER BY t.id DESC
    LIMIT $limit OFFSET $offset
";

$tests = $db->query($sql);
if (!$tests) echo $db->error;

/* Test type labels */
$testTypes = [
    1 => 'Chempath',
    2 => 'Microbiology',
    3 => 'Widal'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Tests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>
.status-active { color:#0a7a0a; font-weight:600; }
.status-inactive { color:#c0392b; font-weight:600; }
.table-actions a { margin-right:8px; }
.table-actions a.edit { background:#28a745; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; }
.table-actions a.delete-btn { background:#dc3545; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; }
.table-actions a.edit:hover { background:#218838; }
.table-actions a.delete-btn:hover { background:#c82333; }
.table-responsive { width:100%; overflow-x:auto; -webkit-overflow-scrolling: touch; }
.table-responsive table { min-width:800px; border-collapse:collapse; }
.search-section { background:#f8f9fa; padding:20px; border-radius:8px; border:1px solid #e9ecef; margin-bottom:24px; }
.search-filters { display:flex; flex-wrap:wrap; gap:20px; }
.filter-group { display:flex; flex-direction:column; }
.search-input-wrapper { display:flex; }
.search-btn, .reset-btn { padding:10px 16px; border-radius:6px; font-weight:600; cursor:pointer; border:none; color:#fff; }
.search-btn { background:#007bff; }
.search-btn:hover { background:#0056b3; }
.reset-btn { background:#6c757d; text-decoration:none; display:inline-block; text-align:center; }
.reset-btn:hover { background:#5a6268; }
@media (max-width:768px){ .search-filters{ flex-direction:column; gap:16px; } .search-input-wrapper{ flex-direction:column; } }
</style>
</head>

<body>
<?php include '../includes/side_nav.php'; ?>
<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Tests</h2>
        <a href="index.php" class="btn btn-primary">+ Add Test</a>
    </div>

    <div class="card-body" style="padding:24px;">

<!-- SEARCH / FILTER -->
<div class="search-section">
    <form action="" method="GET">
        <div class="search-filters">



            <!-- Test Type Filter -->
            <div class="filter-group">
                <label for="type">Test Type</label>
                <select name="type" id="type" style="padding:10px; border:1px solid #ddd; border-radius:6px; width:180px;">
                    <option value="">All Types</option>
                    <?php foreach($testTypes as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($type==$k)?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Test Name Search -->
            <div class="filter-group" style="flex-grow:1;">
                <label for="searchQuery">Test Name</label>
                <div class="search-input-wrapper">
                    <input type="text"
                           name="search"
                           id="searchQuery"
                           placeholder="Enter test name..."
                           value="<?= htmlspecialchars($search) ?>"
                           style="flex-grow:1; padding:10px; border:1px solid #ddd; border-radius:6px 0 0 6px; border-right:none;">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </div>


            <!-- Reset -->
            <div class="filter-group" style="align-self:flex-end;">
                <a href="?" class="reset-btn">Reset</a>
            </div>

        </div>
    </form>
</div>

<!-- TABLE -->
<?php if($tests->num_rows == 0): ?>
    <p style="text-align:center;padding:30px;color:#777;">No tests found.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Test Name</th>
                <th>Amount</th>
                <th>Specimen</th>
                <th>Type</th>
                <th>Status</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=$offset+1; while($row=$tests->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= number_format($row['amount'],2) ?></td>
                <td><?= htmlspecialchars($row['specimen_name']) ?></td>
                <td><?= $testTypes[$row['type']] ?? 'Unknown' ?></td>
                <td>
                    <span class="<?= $row['status']==1?'status-active':'status-inactive' ?>">
                        <?= $row['status']==1?'Active':'Inactive' ?>
                    </span>
                </td>
                <td class="table-actions">
                    <a href="index.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                    <a href="../result_parameter/index.php?id=<?= $row['id']+1200 ?>" class="edit" style="background: blue;">Result Parameter</a>
                </td>
                <td class="table-actions">
                    <a onclick="deleteTest(<?= $row['id'] ?>)" class="delete-btn">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- PAGINATION -->
<?php if($totalPages>1): ?>
<div style="margin-top:24px; display:flex; justify-content:center;">
    <ul style="list-style:none; display:flex; gap:8px; padding:0;">
        <?php for($p=1;$p<=$totalPages;$p++): ?>
            <li>
                <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>"
                   style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;<?= $p==$page?'background:#007bff;color:#fff;':'' ?>">
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

<script>
function deleteTest(id) {
    if(confirm("Are you sure you want to delete this test?")) {
        window.location.href='delete.php?id='+id;
    }
}
</script>

</body>
</html>
