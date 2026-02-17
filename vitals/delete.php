<?php 
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 4)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

// Check if ID is provided
if (empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}

$vital_id = sanitize($_GET['id']);

// Soft delete: set status = -1
$sql = "UPDATE vitals SET status = -1 WHERE id = '$vital_id' AND status = 1";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Could not delete vital. Please try again.";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Vital deleted successfully!";
header('Location: view.php'); // redirect to the vitals list page
exit;
?>
