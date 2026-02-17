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
if (empty($_GET['pid'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DECODE PATIENT TEST ID
========================= */
$encodedId = sanitize($_GET['pid']);
$encodedId = filter_var($encodedId, FILTER_VALIDATE_INT);

if (!$encodedId) {
    $_SESSION['error'] = 'Invalid test ID';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient_test_id = $encodedId - 1200;
$today = date('Y-m-d');

/* =========================
   FETCH PATIENT TEST
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
 Status meaning (based on your flow):
 3 = Result uploaded
 4 = Verified
 5 = Released
*/
if (!in_array($test_status, [4, 5, 6])) {
    $_SESSION['error'] = 'Result has not been uploaded yet';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   VERIFY RESULT
========================= */
$sql = "UPDATE test_lists 
        SET status = 5,
            verifier = '$user_id',
            verified_date = '$today'
        WHERE id = '$patient_test_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Result verified successfully';
    echo "<script>
        window.location.href='index.php?id=" . ($patient_test_id + 1200) . "&task=verify'
    </script>";
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'Unable to verify result';
echo "<script>window.history.back()</script>";
exit;
