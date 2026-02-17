<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'patient'; // ❗ NOT CHANGED
$action   = 'Add';
$fileType = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM file_types WHERE id='$id' AND status=1";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'File Type Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $fileType = $run->fetch_assoc();
    $action   = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | File Types</title>
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
        <h2><?= $action ?> File Type</h2>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- FILE TYPE NAME -->
            <div class="form-group">
                <label>File Type Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. Family, Individual"
                       value="<?= $fileType['name'] ?? '' ?>">
            </div>

            <!-- AMOUNT / FEE -->
            <div class="form-group">
                <label>Amount (₦) *</label>
                <input type="number"
                       name="amount"
                       required
                       min="0"
                       step="0.01"
                       placeholder="e.g. 5000"
                       value="<?= $fileType['amount'] ?? '' ?>">
            </div>

            <!-- Hidden ID -->
            <?php if (!empty($fileType['id'])): ?>
                <input type="hidden" name="id" value="<?= $fileType['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> File Type
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
