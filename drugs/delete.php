<?php 
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}

$drug_id = intval($_GET['id']);

/* =========================
   SOFT DELETE DRUG
========================= */
$sql = "UPDATE drugs SET status = -1 WHERE id = '$drug_id'";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Unable to delete drug";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Drug deleted successfully!";
header('Location: view.php'); // Redirect back to drugs list
exit;
