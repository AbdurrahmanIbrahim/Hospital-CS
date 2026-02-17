<?php 
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}

$ward_id = intval($_GET['id']); // changed variable name

/* =========================
   SOFT DELETE WARD
========================= */
$sql = "UPDATE wards SET status = -1 WHERE id = '$ward_id'"; // changed table name
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Unable to delete ward";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Ward deleted successfully!";
header('Location: view.php');
exit;
