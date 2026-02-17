<?php
include '../functions.php';

/* =========================
   AUTHORIZATION
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2 && $_SESSION['type'] != -2)) {
    $_SESSION['error'] = 'Login to continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

/* =========================
   VALIDATE REQUEST
========================= */
if (empty($_GET['id']) || empty($_GET['collector'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DECODE IDS
========================= */
$encodedPid = filter_var(sanitize($_GET['id']), FILTER_VALIDATE_INT);
$collector  = sanitize($_GET['collector']);

if (!$encodedPid) {
    $_SESSION['error'] = 'Invalid reference';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient_test_id = $encodedPid - 1200;
$today           = date('Y-m-d');

/* =========================
   FETCH TEST RECORD
========================= */
$sql = "SELECT id, status FROM test_lists WHERE id = '$patient_test_id'";
$run = $db->query($sql);

if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Test not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_info   = $run->fetch_assoc();
$test_status = $test_info['status'];

/* =========================
   STATUS VALIDATION
========================= */
/*
 Status meaning:
 4 = Verified
 6 = Result collected
*/
if ($test_status != 5) {
    $_SESSION['error'] = 'Result has not been verified';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM INVOICE
========================= */


/* =========================
   UPDATE COLLECTION STATUS
========================= */
$sql = "UPDATE test_lists
        SET 
            status = 7,
            collector = '$collector',
            collected_date = '$today'
        WHERE id = '$patient_test_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Result successfully given to patient';
    echo "<script>
        window.location.href='index.php?status=5';
    </script>";
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'Unable to complete action';
echo "<script>window.history.back()</script>";
exit;
