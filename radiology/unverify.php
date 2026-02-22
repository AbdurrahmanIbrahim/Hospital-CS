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
if ($scan_info['status'] != 4) {
    $_SESSION['error'] = 'This report is not currently verified';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UNVERIFY REPORT
========================= */
$sql = "UPDATE scan_lists
        SET status = 3,
            verifier_id = 0,
            date_verified = NULL
        WHERE id = '$scanlist_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Report unverified successfully';
    header("Location: index.php?status=3");
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'Unable to unverify report';
echo "<script>window.history.back()</script>";
exit;
