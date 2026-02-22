<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid Request';
    header("Location: view.php");
    exit;
}

$id = intval($_GET['id']);

/* =========================
   Soft Delete
========================= */
$sql = "UPDATE scans SET status = -1 WHERE id = '$id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Scan type deleted';
} else {
    $_SESSION['error'] = 'Failed to delete scan type';
}

header("Location: view.php");
exit;
