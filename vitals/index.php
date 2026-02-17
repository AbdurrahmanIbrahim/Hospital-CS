<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=4)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'nurses';
$action   = 'Add';
$vital    = null;

/* =========================
   EDIT MODE
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM vitals WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Vital Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $vital  = $run->fetch_assoc();
    // print_r($vital);
    // exit;
    $action = 'Update';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Vitals Management</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="card">
        <div class="card-header">
            <h2><?= $action ?> Vital</h2>
        </div>

        <div class="card-body" style="padding:32px;">
            <form method="POST" action="process.php">

                <!-- VITAL NAME -->
                <div class="form-group">
                    <label>Vital Name *</label>
                    <input type="text" name="vital_name" required
                           value="<?= $vital['name'] ?? '' ?>">
                </div>

                  <div class="form-group">
                    <label>Vital Unit *</label>
                    <input type="text" name="vital_unit" required
                           value="<?= $vital['unit'] ?? '' ?>">
                </div>

               

                <!-- HIDDEN ID -->
                <?php if (!empty($vital['id'])): ?>
                    <input type="hidden" name="id" value="<?= $vital['id'] ?>">
                <?php endif; ?>

                <!-- SUBMIT -->
                <div class="form-group" style="text-align:right;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">
                        <?= $action ?> Vital
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
