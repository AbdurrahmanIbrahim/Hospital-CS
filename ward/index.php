<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'ward';
$action   = 'Add';
$ward     = null;

/* =========================
   EDIT MODE
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM wards WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Ward Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $ward   = $run->fetch_assoc();
    $action = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Ward Management</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="card">
        <div class="card-header">
            <h2><?= $action ?> Ward</h2>
        </div>

        <div class="card-body" style="padding:32px;">
            <form method="POST" action="process.php">

                <!-- WARD NAME -->
                <div class="form-group">
                    <label>Ward Name *</label>
                    <input type="text"
                           name="ward_name"
                           required
                           placeholder="e.g. Male Ward, Female Ward, ICU"
                           value="<?= $ward['ward_name'] ?? '' ?>">
                </div>

                <!-- HIDDEN ID -->
                <?php if (!empty($ward['id'])): ?>
                    <input type="hidden" name="id" value="<?= $ward['id'] ?>">
                <?php endif; ?>

                <!-- SUBMIT -->
                <div class="form-group" style="text-align:right;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">
                        <?= $action ?> Ward
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
