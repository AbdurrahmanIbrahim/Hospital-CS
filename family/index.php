 <?php
include '../functions.php';

// Only allow certain users
if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'patient'; // Changed from patient
$action   = 'Add';
$family   = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM families WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Family Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $family = $run->fetch_assoc();
    $action = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Families</title>
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
        <h2><?= $action ?> Family</h2>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- FAMILY NAME -->
            <div class="form-group">
                <label>Family Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="Enter family name"
                       value="<?= $family['name'] ?? '' ?>">
            </div>

            <!-- PHONE -->
            <div class="form-group">
                <label>Phone</label>
                <input type="text"
                       name="phone"
                       placeholder="e.g. 08012345678"
                       value="<?= $family['phone'] ?? '' ?>">
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label>Email</label>
                <input type="email"
                       name="email"
                       placeholder="e.g. family@example.com"
                       value="<?= $family['email'] ?? '' ?>">
            </div>

            <!-- ADDRESS -->
            <div class="form-group">
                <label>Address</label>
                <input type="text"
                       name="address"
                       placeholder="Enter address"
                       value="<?= $family['address'] ?? '' ?>">
            </div>

            <!-- STATUS -->
         <!--    <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= (isset($family['status']) && $family['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (isset($family['status']) && $family['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div> -->

            <!-- Hidden ID -->
            <?php if (!empty($family['id'])): ?>
                <input type="hidden" name="id" value="<?= $family['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Family
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
