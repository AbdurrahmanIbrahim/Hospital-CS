<?php 
include '../functions.php';

// Check login & permissions
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 5)) {
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

$family_id = sanitize($_GET['id']);

/* =========================
   Soft Delete Family
========================= */
$sql = "UPDATE families SET status = -1 WHERE id = '$family_id'";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Unable to deactivate family";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Family deactivated successfully!";
header('Location: view.php');
exit;
