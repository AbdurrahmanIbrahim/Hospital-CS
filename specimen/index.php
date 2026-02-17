<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';
$action   = 'Add';
$specimen = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM specimen WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Specimen Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $specimen = $run->fetch_assoc();
    $action   = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Specimen</title>
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
        <h2><?= $action ?> Specimen</h2>
        <a href="view.php" class="btn btn-primary">View Specimens</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- SPECIMEN NAME -->
            <div class="form-group">
                <label>Specimen Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. Blood, Urine, Stool, Sputum"
                       value="<?= htmlspecialchars($specimen['name'] ?? '') ?>">
            </div>


            <!-- Hidden ID -->
            <?php if (!empty($specimen['id'])): ?>
                <input type="hidden" name="id" value="<?= $specimen['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Specimen
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
