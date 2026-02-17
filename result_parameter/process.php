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
   VALIDATE REQUEST
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$count   = isset($_POST['count']) ? intval($_POST['count']) : 0;
$test_id = isset($_POST['test_id']) ? intval($_POST['test_id']) : 0;

/* =========================
   VALIDATION
========================= */
if ($count <= 0) {
    $_SESSION['error'] = 'Please enter a valid number of parameters';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($test_id <= 0) {
    $_SESSION['error'] = 'Invalid test selected';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   STORE PARAMETER COUNT
========================= */
$_SESSION['parameter_count'] = $count;

/* =========================
   REDIRECT (OBFUSCATED ID)
========================= */
header('Location: index.php?id=' . ($test_id + 1200));
exit;
