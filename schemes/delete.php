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

$scheme_id = sanitize($_GET['id']);

/* =========================
   Soft Delete Scheme
========================= */
$sql = "UPDATE schemes SET status = -1 WHERE id = '$scheme_id'";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Unable to delete scheme";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Scheme deleted successfully!";
header('Location: view.php');
exit;
