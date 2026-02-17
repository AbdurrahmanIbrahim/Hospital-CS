<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'patient'; // ❗ NOT CHANGED
$action   = 'Add';
$scheme   = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM schemes WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Scheme Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $scheme = $run->fetch_assoc();
    $action = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Schemes</title>
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
        <h2><?= $action ?> Scheme</h2>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- SCHEME NAME -->
            <div class="form-group">
                <label>Scheme Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. NHIS, Private, Corporate"
                       value="<?= $scheme['name'] ?? '' ?>">
            </div>

            <!-- DISCOUNT FEE (%) -->
            <div class="form-group">
                <label>Discount Fee (%) *</label>
                <input type="number"
                       name="discount_fee"
                       required
                       min="0"
                       max="100"
                       step="0.01"
                       placeholder="e.g. 10"
                       value="<?= $scheme['discount_fee'] ?? '' ?>">
            </div>

            <!-- Hidden ID -->
            <?php if (!empty($scheme['id'])): ?>
                <input type="hidden" name="id" value="<?= $scheme['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Scheme
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
