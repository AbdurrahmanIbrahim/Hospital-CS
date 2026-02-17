<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';
$action   = 'Add';
$test     = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM tests WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Test Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $test = $run->fetch_assoc();
    $action = 'Update';
}

/* =========================
   Fetch Specimens for dropdown
========================= */
$specimens = $db->query("SELECT id, name FROM specimen WHERE status=1 ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>
<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header">
        <h2><?= $action ?> Test</h2>
        <a href="view.php" class="btn btn-primary">View Tests</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- TEST NAME -->
            <div class="form-group">
                <label>Test Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. Complete Blood Count"
                       value="<?= htmlspecialchars($test['name'] ?? '') ?>">
            </div>

            <!-- AMOUNT -->
            <div class="form-group">
                <label>Amount *</label>
                <input type="number"
                       step="0.01"
                       name="amount"
                       required
                       placeholder="e.g. 5000"
                       value="<?= htmlspecialchars($test['amount'] ?? '') ?>">
            </div>

            <!-- SPECIMEN -->
            <div class="form-group">
                <label>Specimen *</label>
                <select name="specimen" required>
                    <option value="">-- Select Specimen --</option>
                    <?php while ($row = $specimens->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"
                            <?= (isset($test['specimen']) && $test['specimen'] == $row['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- TEST TYPE -->
            <div class="form-group">
                <label>Test Type *</label>
                <select name="type" required>
                    <option value="">-- Select Test Type --</option>
                    <option value="1" <?= (isset($test['type']) && $test['type']==1)?'selected':'' ?>>Chempath</option>
                    <option value="2" <?= (isset($test['type']) && $test['type']==2)?'selected':'' ?>>Microbiology</option>
                    <option value="3" <?= (isset($test['type']) && $test['type']==3)?'selected':'' ?>>Widal</option>
                </select>
            </div>

            <!-- STATUS -->
  <!--           <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="1" <?= (isset($test['status']) && $test['status']==1)?'selected':'' ?>>Active</option>
                    <option value="0" <?= (isset($test['status']) && $test['status']==0)?'selected':'' ?>>Inactive</option>
                </select>
            </div> -->

            <!-- Hidden ID -->
            <?php if (!empty($test['id'])): ?>
                <input type="hidden" name="id" value="<?= $test['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Test
                </button>
            </div>

        </form>
    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>
</body>
</html>
