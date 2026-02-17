<?php
include '../functions.php';

/* =========================
   AUTHORIZATION CHECK
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login to continue';
    header('Location: ../login/index.php');
    exit;
}
/* =========================
   VALIDATE REQUEST METHOD
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    header("Location: index.php");
    exit();
}

/* =========================
   SANITIZE & VALIDATE INPUTS
========================= */
$test_id   = filter_var(sanitize($_POST['test_id']), FILTER_VALIDATE_INT);
$result_id = filter_var(sanitize($_POST['result']), FILTER_VALIDATE_INT);
$parameter = trim(sanitize($_POST['parameter']));
$unit      = trim(sanitize($_POST['unit']));
$range     = trim(sanitize($_POST['range']));
$normal    = trim(sanitize($_POST['normal']));

/* =========================
   BASIC VALIDATIONS
========================= */
if (!$test_id || !$result_id) {
    $_SESSION['error'] = 'Invalid test or result reference';
    header("Location: index.php");
    exit();
}

if (empty($parameter)) {
    $_SESSION['error'] = 'Parameter name is required';
    header("Location: index.php?id=" . ($test_id + 1200));
    exit();
}

/* =========================
   CONFIRM TEST EXISTS
========================= */
$checkTest = $db->query("SELECT id FROM tests WHERE id = '$test_id'");
if ($checkTest->num_rows === 0) {
    $_SESSION['error'] = 'Test not found';
    header("Location: index.php");
    exit();
}

/* =========================
   PREVENT DUPLICATE PARAMETERS
========================= */
$checkParam = $db->query("
    SELECT id 
    FROM result_parameters 
    WHERE name = '$parameter' 
      AND test_id = '$test_id' 
      AND id != '$result_id'
");

if ($checkParam->num_rows > 0) {
    $_SESSION['error'] = 'This parameter already exists for this test';
    header("Location: index.php?id=" . ($test_id + 1200));
    exit();
}

/* =========================
   UPDATE RESULT PARAMETER
========================= */
$sql = "
    UPDATE result_parameters 
    SET 
        name = '$parameter',
        unit = '$unit',
        para_range = '$range',
        normal_range = '$normal'
    WHERE id = '$result_id'
      AND test_id = '$test_id'
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Result parameter updated successfully';
    header("Location: index.php?id=" . ($test_id + 1200));
    exit();
} else {
    $_SESSION['error'] = 'Failed to update result parameter';
    header("Location: index.php?id=" . ($test_id + 1200));
    exit();
}
?>
