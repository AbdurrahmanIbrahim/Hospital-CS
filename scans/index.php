<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'radiology';
$action   = 'Add';
$scan     = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM scans WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Scan Type Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $scan = $run->fetch_assoc();
    $action = 'Update';
}

/* Modality options */
$modalities = ['X-Ray', 'CT', 'MRI', 'Ultrasound', 'Mammography', 'Fluoroscopy', 'Nuclear', 'PET'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Scan Type</title>
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
        <h2><?= $action ?> Scan Type</h2>
        <a href="view.php" class="btn btn-primary">View All Scan Types</a>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <div class="form-row">
                <!-- SCAN NAME -->
                <div class="form-group">
                    <label>Scan Name *</label>
                    <input type="text"
                           name="name"
                           required
                           placeholder="e.g. Chest X-Ray"
                           value="<?= htmlspecialchars($scan['name'] ?? '') ?>">
                </div>

                <!-- MODALITY -->
                <div class="form-group">
                    <label>Modality *</label>
                    <select name="modality" required>
                        <option value="">-- Select Modality --</option>
                        <?php foreach ($modalities as $mod): ?>
                            <option value="<?= $mod ?>"
                                <?= (isset($scan['modality']) && $scan['modality'] == $mod) ? 'selected' : '' ?>>
                                <?= $mod ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <!-- BODY PART -->
                <div class="form-group">
                    <label>Body Part</label>
                    <input type="text"
                           name="body_part"
                           placeholder="e.g. Chest, Abdomen, Brain, Pelvis"
                           value="<?= htmlspecialchars($scan['body_part'] ?? '') ?>">
                </div>

                <!-- AMOUNT -->
                <div class="form-group">
                    <label>Amount *</label>
                    <input type="number"
                           step="0.01"
                           name="amount"
                           required
                           placeholder="e.g. 15000"
                           value="<?= htmlspecialchars($scan['amount'] ?? '') ?>">
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"
                          rows="4"
                          placeholder="Optional description of the scan type"><?= htmlspecialchars($scan['description'] ?? '') ?></textarea>
            </div>

            <!-- Hidden ID -->
            <?php if (!empty($scan['id'])): ?>
                <input type="hidden" name="id" value="<?= $scan['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    Save Scan Type
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
