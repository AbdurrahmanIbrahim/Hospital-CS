<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'pharmacy';
$action   = 'Add';
$drug     = [];

/* =========================
   Fetch Categories
========================= */
$categories = $db->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC");

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM drugs WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Drug Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $drug   = $run->fetch_assoc();
    $action = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Drugs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2><?= $action ?> Drug</h2>
        <a href="view.php" class="btn btn-primary">View Drugs</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- DRUG NAME -->
            <div class="form-row">
                       <div class="form-group">
                <label>Drug Name *</label>
                <input type="text" name="drug_name" required
                       value="<?= $drug['drug_name'] ?? '' ?>">
            </div>

            <!-- GENERIC NAME -->
            <div class="form-group">
                <label>Generic Name</label>
                <input type="text" name="generic_name"
                       value="<?= $drug['generic_name'] ?? '' ?>">
            </div>
            </div>
      

            <!-- CATEGORY -->
            <div class="form-row">
                           <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= (!empty($drug['category']) && $drug['category'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- DOSAGE FORM -->
            <div class="form-group">
                <label>Dosage Form *</label>
                <select name="dosage_form" required>
                    <?php
                    $forms = ['Tablet','Capsule','Syrup','Injection','Ointment','Cream','Drops','Inhaler'];
                    foreach ($forms as $f):
                    ?>
                        <option value="<?= $f ?>" <?= (($drug['dosage_form'] ?? '') == $f) ? 'selected' : '' ?>>
                            <?= $f ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>   
            </div>


          


          <div class="form-row">
              <!-- STRENGTH -->
                 <div class="form-group">
                <label>Strength</label>
                <input type="text" name="strength" placeholder="e.g. 500mg"
                       value="<?= $drug['strength'] ?? '' ?>">
            </div>

            <!-- ROUTE -->
            <div class="form-group">
                <label>Route</label>
                <select name="route">
                    <option value="">-- Select Route --</option>
                    <?php
                    $routes = ['Oral','IV','IM','SC','Topical','Inhalation'];
                    foreach ($routes as $r):
                    ?>
                        <option value="<?= $r ?>" <?= (($drug['route'] ?? '') == $r) ? 'selected' : '' ?>>
                            <?= $r ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
          </div>
          

            <!-- UNITS PER PACK -->

            <div class="form-row">
                 <div class="form-group">
                <label>Units per Pack *</label>
                <input type="number" name="units_per_pack" required
                       value="<?= $drug['units_per_pack'] ?? '' ?>">
            </div>

            <!-- MANUFACTURER -->
            <div class="form-group">
                <label>Manufacturer</label>
                <input type="text" name="manufacturer"
                       value="<?= $drug['manufacturer'] ?? '' ?>">
            </div>
            </div>
           

            <!-- COST & SELLING PRICE -->

        <div class="form-row">
            <div class="form-group">
                <label>Cost Price *</label>
                <input type="number" step="0.01" name="cost_price" required
                       value="<?= $drug['cost_price'] ?? '' ?>">
            </div>

            <div class="form-group">
                <label>Selling Price *</label>
                <input type="number" step="0.01" name="selling_price" required
                       value="<?= $drug['selling_price'] ?? '' ?>">
            </div>

            <!-- MINIMUM STOCK -->
            <div class="form-group">
                <label>Minimum Stock Level</label>
                <input type="number" name="minimum_stock_level"
                       value="<?= $drug['minimum_stock_level'] ?? 10 ?>">
            </div>
        </div>

            <!-- STATUS -->
          
            <!-- Hidden ID -->
            <?php if (!empty($drug['id'])): ?>
                <input type="hidden" name="id" value="<?= $drug['id'] ?>">
            <?php endif; ?>

            <!-- SUBMIT -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Drug
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
