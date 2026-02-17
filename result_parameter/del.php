<?php
include '../functions.php';
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login to continue';
    header('Location: ../login/index.php');
    exit;
}

/* =========================
   VALIDATE INPUT
========================= */
if (!isset($_GET['test_id'], $_GET['result'])) {
    echo "<script>window.history.back()</script>";
    exit;
}

$test_id = filter_var(sanitize($_GET['test_id']), FILTER_VALIDATE_INT);
$result  = filter_var(sanitize($_GET['result']), FILTER_VALIDATE_INT);

if (empty($test_id) || empty($result)) {
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM TEST EXISTS
========================= */
$sql = "SELECT id FROM tests WHERE id = '$test_id'";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DELETE PARAMETER
========================= */
$sql = "DELETE FROM result_parameters 
        WHERE id = '$result' 
        AND test_id = '$test_id'";

$run = $db->query($sql);

if ($run) {
    $_SESSION['success'] = 'Successfully deleted result parameter';
    header('location:index.php?id=' . ($test_id + 1200));
    exit;
}

/* =========================
   ERROR FALLBACK
========================= */
echo "<script>window.history.back()</script>";
exit;
