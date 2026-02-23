<?php
include '../functions.php';

if (!isLoggedIn() || $_SESSION['type'] != 0) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admin';

// Fetch current consultation fee
$sql = "SELECT id, consultation_fee, review_window_days FROM hospital_details LIMIT 1";
$run = $db->query($sql);
$hospital = $run->fetch_assoc();
$current_fee = $hospital ? floatval($hospital['consultation_fee']) : 0;
$review_window = $hospital ? intval($hospital['review_window_days']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= APP_NAME ?> | Consultation Fee</title>
<link rel="stylesheet" href="../styles/styles.css">
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header">
        <h2>Consultation Fee Settings</h2>
    </div>
    <div class="card-body" style="padding: 32px;">

        <p style="color:#555;margin-bottom:24px;">
            Set the consultation fee amount that patients must pay before their appointment becomes active.
            Setting the fee to <strong>0</strong> will disable the consultation fee requirement.
        </p>

        <form method="POST" action="process.php">
            <input type="hidden" name="id" value="<?= $hospital['id'] ?? '' ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="consultation_fee">Consultation Fee Amount</label>
                    <input type="number"
                           id="consultation_fee"
                           name="consultation_fee"
                           step="0.01"
                           min="0"
                           value="<?= $current_fee ?>"
                           placeholder="Enter consultation fee amount"
                           required
                           style="max-width:300px;">
                </div>
            </div>

            <div class="form-row" style="margin-top:20px;">
                <div class="form-group">
                    <label for="review_window_days">Review Window (Days)</label>
                    <input type="number"
                           id="review_window_days"
                           name="review_window_days"
                           step="1"
                           min="0"
                           value="<?= $review_window ?>"
                           placeholder="Number of days for free review"
                           required
                           style="max-width:300px;">
                    <p style="color:#888;font-size:13px;margin-top:6px;">
                        Number of days after consultation ends during which the patient can return for review without paying again.
                        Setting to <strong>0</strong> means unlimited review access (no re-payment required).
                    </p>
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>

    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
