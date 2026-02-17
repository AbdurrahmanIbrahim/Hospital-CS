<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'pharmacy';

/* =========================
   Fetch Categories
========================= */
$categories = $db->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Add Drug Stock</title>
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
        <h2>Add Drug Stock</h2>
        <a href="view.php" class="btn btn-primary">View Stock</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- CATEGORY -->
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- DRUG -->
            <div class="form-group">
                <label>Drug *</label>
                <select name="drug_id" id="drug_id" required disabled>
                    <option value="">-- Select Drug --</option>
                </select>
            </div>

            <!-- QUANTITY -->
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number"
                       name="quantity"
                       min="1"
                       required
                       placeholder="Enter quantity">
            </div>

            <!-- SUBMIT -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    Add Stock
                </button>
            </div>

        </form>
    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
document.getElementById('category_id').addEventListener('change', function () {
    const categoryId = this.value;
    const drugSelect = document.getElementById('drug_id');

    drugSelect.innerHTML = '<option value="">Loading...</option>';
    drugSelect.disabled = true;

    if (!categoryId) {
        drugSelect.innerHTML = '<option value="">-- Select Drug --</option>';
        return;
    }

    fetch('get_drugs.php?category_id=' + categoryId)
        .then(res => res.json())
        .then(data => {
            drugSelect.innerHTML = '<option value="">-- Select Drug --</option>';

            if (data.length === 0) {
                drugSelect.innerHTML += '<option value="">No drugs found</option>';
            } else {
                data.forEach(drug => {
                    drugSelect.innerHTML += `
                        <option value="${drug.id}">
                            ${drug.drug_name}
                        </option>`;
                });
            }

            drugSelect.disabled = false;
        })
        .catch(() => {
            drugSelect.innerHTML = '<option value="">Error loading drugs</option>';
        });
});
</script>

</body>
</html>
