<?php
include '../functions.php';

/* =========================
   AUTHORIZATION
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login to continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

/* =========================
   VALIDATE REQUEST
========================= */
if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$scanlist_id = intval($_GET['id']);

if ($scanlist_id <= 0) {
    $_SESSION['error'] = 'Invalid scan reference';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   FETCH SCAN RECORD
========================= */
$sql = "SELECT id, status FROM scan_lists WHERE id = '$scanlist_id'";
$run = $db->query($sql);

if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Scan record not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$scan_info = $run->fetch_assoc();

/* =========================
   STATUS VALIDATION
========================= */
if ($scan_info['status'] != 1) {
    $_SESSION['error'] = 'This scan is not eligible to be marked as performed';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE SCAN STATUS
========================= */
$sql = "UPDATE scan_lists
        SET status = 2,
            date_performed = NOW(),
            radiologist_id = '" . getId() . "'
        WHERE id = '$scanlist_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Scan marked as performed';
    header("Location: index.php?status=2");
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'Unable to update scan status';
echo "<script>window.history.back()</script>";
exit;
