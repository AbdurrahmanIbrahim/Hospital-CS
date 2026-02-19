<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 4)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

if (empty($_POST['admission_id']) || empty($_POST['report'])) {
    $_SESSION['error'] = 'All fields are required';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = intval($_POST['admission_id']);
$report = sanitize($_POST['report']);
$nurse_id = getId();

// Validate admission exists and is active
$admission = $db->query("SELECT * FROM admissions WHERE id = '$admission_id' AND status = 0");
if ($admission->num_rows == 0) {
    $_SESSION['error'] = 'Admission not found or already discharged';
    echo "<script>window.history.back()</script>";
    exit;
}

$sql = "INSERT INTO admission_reports (admission_id, nurse_id, report, created_at)
        VALUES ('$admission_id', '$nurse_id', '$report', NOW())";
$run = $db->query($sql);

if ($run) {
    $_SESSION['success'] = 'Report added successfully';
} else {
    $_SESSION['error'] = 'Failed to add report. Please try again.';
}

header("Location: reports.php?id=$admission_id");
exit;
