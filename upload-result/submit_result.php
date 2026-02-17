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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SANITIZE INPUT
========================= */
$patient_test_id = filter_var($_POST['patient_test'] ?? null, FILTER_VALIDATE_INT);
$test_id         = filter_var($_POST['test_id'] ?? null, FILTER_VALIDATE_INT);
$notes           = sanitize($_POST['notes']);

if (!$patient_test_id || !$test_id) {
    $_SESSION['error'] = 'Invalid test information';
    echo "<script>window.history.back()</script>";
    exit;
}

if (empty($notes)) {
    $_SESSION['error'] = 'Please enter result notes';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DECODE IDS
========================= */
$patient_test_id -= 1200;
$test_id         -= 1200;
$today = date('Y-m-d');

/* =========================
   VALIDATE TEST
========================= */

    $sql = "SELECT * FROM tests 
            WHERE id = '$test_id' 
            AND status = 1";

$run = $db->query($sql);
if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Test not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_info = $run->fetch_assoc();

/* =========================
   VALIDATE PATIENT TEST
========================= */
$sql = "SELECT * FROM test_lists 
        WHERE id = '$patient_test_id'
        AND paid = 1
        AND status=3";

$run = $db->query($sql);
if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Patient test not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient_info = $run->fetch_assoc();

if ($patient_info['status'] == 5) {
    $_SESSION['error'] = 'Result has already been verified';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   FETCH PARAMETERS
========================= */
$parameters = [];
$sql = "SELECT * FROM result_parameters 
        WHERE test_id = '$test_id' AND status = 1";

$run = $db->query($sql);
while ($row = $run->fetch_assoc()) {
    $parameters[] = $row;
}

if (count($parameters) == 0) {
    $_SESSION['error'] = 'No result parameters found';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CLEAR OLD RESULTS
========================= */
$sql = "DELETE FROM results WHERE patient_test_id = '$patient_test_id'";
$db->query($sql);

/* =========================
   SAVE RESULTS
========================= */
$insertSQL = "INSERT INTO results (parameter_id, patient_test_id, answer) VALUES ";

foreach ($parameters as $parameter) {
    $pid = $parameter['id'];

    if ($test_info['type'] != 3) {
        $result = sanitize($_POST['value_' . ($pid + 1200)]);
    } else {
        $r1 = sanitize($_POST['value_1_' . ($pid + 1200)]);
        $r2 = sanitize($_POST['value_2_' . ($pid + 1200)]);
        $result = (!empty($r1) && !empty($r2)) ? "$r1,$r2" : '';
    }

    if (empty($result)) {
        $_SESSION['error'] = 'Please fill all parameter values';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $insertSQL .= "('$pid','$patient_test_id','$result'),";
}

$insertSQL = rtrim($insertSQL, ',');

if (!$db->query($insertSQL)) {
    $_SESSION['error'] = 'Failed to save results';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE PATIENT TEST
========================= */
$sql = "UPDATE test_lists
        SET notes = '$notes',
            status = 4,
            result_releaser_id = '$user_id',
            date_of_result = '$today'
        WHERE id = '$patient_test_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Results uploaded successfully';
    echo "<script>
        window.location.href='index.php?id=" . ($patient_test_id + 1200) . "&task=prepare'
    </script>";
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'An unexpected error occurred';
echo "<script>window.history.back()</script>";
exit;
