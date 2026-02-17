<?php
include '../functions.php';

/* =========================
   AUTH CHECK
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login to continue';
    header('Location: ../login/index.php');
    exit;
}

/* =========================
   REQUEST CHECK
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$test_id = isset($_POST['test_id']) ? intval($_POST['test_id']) : 0;
$count   = isset($_POST['parameter_count']) ? intval($_POST['parameter_count']) : 0;

/* =========================
   VALIDATION
========================= */
if ($test_id <= 0 || $count <= 0) {
    $_SESSION['error'] = 'Invalid data supplied';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM TEST EXISTS
========================= */
$check = $db->query("SELECT id FROM tests WHERE id = '$test_id' LIMIT 1");
if ($check->num_rows === 0) {
    $_SESSION['error'] = 'Test not found';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   BUILD INSERT QUERY
========================= */
$values = [];
$added  = false;

for ($i = 1; $i <= $count; $i++) {

    $parameter = !empty($_POST['parameter_'.$i]) ? sanitize($_POST['parameter_' . $i]) : '';
    $unit      = !empty($_POST['unit_' . $i]) ? sanitize($_POST['unit_' . $i]) : '';
    $range     = !empty($_POST['range_' . $i]) ? sanitize($_POST['range_' . $i]) : '';

    // Skip empty rows
    if ($parameter === '') {
        continue;
    }

    $added = true;
    $values[] = "('$parameter','$unit','$range','$test_id','1')";
}

/* =========================
   INSERT PARAMETERS
========================= */
if ($added && count($values) > 0) {

    $sql = "
        INSERT INTO result_parameters
            (name, unit, para_range, test_id, status)
        VALUES " . implode(',', $values);

    if ($db->query($sql)) {
        unset($_SESSION['parameter_count']);
        $_SESSION['success'] = 'Result parameters added successfully';
        header('Location: index.php?id=' . ($test_id + 1200));
        exit;
    }

    $_SESSION['error'] = 'Failed to add parameters';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   NOTHING TO INSERT
========================= */
$_SESSION['error'] = 'No valid parameters provided';
echo "<script>window.history.back()</script>";
exit;
