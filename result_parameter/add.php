<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login to continue';
    header('Location: ../login/index.php');
    exit;
}

/* =========================
   VALIDATE REQUEST
========================= */
if (!isset($_POST['test_id'], $_POST['parameter'])) {
    echo "<script>window.history.back()</script>";
    exit;
}

$test_id   = filter_var(sanitize($_POST['test_id']), FILTER_VALIDATE_INT);
$parameter = sanitize($_POST['parameter']);
$unit      = sanitize($_POST['unit']);
$range     = sanitize($_POST['range']);

if (empty($test_id) || empty($parameter)) {
    $_SESSION['error'] = 'An Error Occured';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM TEST EXISTS
========================= */
$sql = "SELECT id FROM tests WHERE id = '$test_id'";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Test Not Found';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK DUPLICATE PARAMETER
========================= */
$sql = "SELECT id 
        FROM result_parameters 
        WHERE name = '$parameter' 
        AND test_id = '$test_id'";
$run = $db->query($sql);

if ($run->num_rows > 0) {
    $_SESSION['error'] = 'This parameter already exists';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   INSERT PARAMETER
========================= */
$sql = "INSERT INTO result_parameters 
        (name, unit, para_range, test_id, status)
        VALUES 
        ('$parameter', '$unit', '$range', '$test_id', '1')";

$run = $db->query($sql);

if ($run) {
    $_SESSION['success'] = 'Successfully added result parameter';
    header('location:index.php?id=' . ($test_id + 1200));
    exit;
}

/* =========================
   ERROR FALLBACK
========================= */
echo "<script>window.history.back()</script>";
exit;
