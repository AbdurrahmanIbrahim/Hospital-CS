<?php
include '../functions.php';

if (
    !isLoggedIn() ||
    ($_SESSION['type'] != '0' &&
     $_SESSION['type'] != '2' &&
     $_SESSION['type'] != '-2')
) {
    $_SESSION['error'] = 'Login to continue';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   VALIDATE INPUT
========================= */
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$testlist_id = isset($_GET['patient_test_id']) ? intval($_GET['patient_test_id']) : 0;

$testlist_id = $testlist_id - 1200;
$today = date('Y-m-d');

if ($testlist_id <= 0 || $status !== '3') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   FETCH TEST RECORD
========================= */
$sql = "
    SELECT 
        ptl.id,
        ptl.status,
        pt.id AS patient_test_id
    FROM test_lists ptl
    JOIN patient_test pt ON pt.id = ptl.patient_test_id
    WHERE ptl.id = '$testlist_id'
";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Test record not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$test = $run->fetch_assoc();

/* =========================
   STATUS VALIDATION
========================= */
if (!in_array($test['status'], ['2'])) {
    $_SESSION['error'] = 'Specimen is not eligible for acknowledgement';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE ACKNOWLEDGEMENT
========================= */
$update = "
    UPDATE test_lists 
    SET 
        status = '3',
        acknowleger_id = '$user_id',
        date_acknowledge = '$today'
    WHERE id = '$testlist_id'
";

if ($db->query($update)) {

    $_SESSION['success'] = "Specimen acknowledged successfully";
    header("Location: index.php?status=3");
    exit;

} else {
    $_SESSION['error'] = 'Failed to acknowledge specimen';
    echo "<script>window.history.back()</script>";
    exit;
}
