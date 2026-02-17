<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'pharmacy';
$action   = 'Add';
$category = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM categories WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Category Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $category = $run->fetch_assoc();
    $action   = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Drug Categories</title>
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
        <h2><?= $action ?> Drug Category</h2>
           <a href="view.php" class="btn btn-primary"> View Categories</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- CATEGORY NAME -->
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. Antibiotics, Analgesics, Syrups"
                       value="<?= $category['name'] ?? '' ?>">
            </div>

        

            <!-- Hidden ID -->
            <?php if (!empty($category['id'])): ?>
                <input type="hidden" name="id" value="<?= $category['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Category
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
