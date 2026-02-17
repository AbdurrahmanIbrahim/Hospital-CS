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

$category_id = intval($_GET['id']);

/* =========================
   SOFT DELETE CATEGORY
========================= */
$sql = "UPDATE categories SET status = -1 WHERE id = '$category_id'";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Unable to delete category";
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = "Category deleted successfully!";
header('Location: view.php');
exit;
