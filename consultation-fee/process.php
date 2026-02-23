<?php
include '../functions.php';

if (!isLoggedIn() || $_SESSION['type'] != 0) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$id = intval($_POST['id'] ?? 0);
$fee = floatval($_POST['consultation_fee'] ?? 0);
$review_window_days = intval($_POST['review_window_days'] ?? 0);

if ($fee < 0) {
    $_SESSION['error'] = 'Fee cannot be negative';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($review_window_days < 0) {
    $_SESSION['error'] = 'Review window cannot be negative';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($id <= 0) {
    $_SESSION['error'] = 'Hospital details not found. Please set up hospital information first.';
    echo "<script>window.history.back()</script>";
    exit;
}

$sql = "UPDATE hospital_details SET consultation_fee = '$fee', review_window_days = '$review_window_days' WHERE id = '$id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Settings updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update settings';
}

header('Location: index.php');
exit;
