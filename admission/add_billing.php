<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Unauthorized';
    echo "<script>window.history.back()</script>";
    exit;
}

if (empty($_POST['admission_id']) || empty($_POST['description']) || empty($_POST['amount']) || empty($_POST['billing_type'])) {
    $_SESSION['error'] = 'All fields are required';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = intval($_POST['admission_id']);
$description = sanitize($_POST['description']);
$amount = floatval($_POST['amount']);
$billing_type = intval($_POST['billing_type']);

if ($amount <= 0) {
    $_SESSION['error'] = 'Amount must be greater than zero';
    echo "<script>window.history.back()</script>";
    exit;
}

if (!in_array($billing_type, [1, 2, 3])) {
    $_SESSION['error'] = 'Invalid billing type';
    echo "<script>window.history.back()</script>";
    exit;
}

// Verify admission exists and is active
$admission = $db->query("SELECT * FROM admissions WHERE id = '$admission_id' AND status = 0");
if ($admission->num_rows == 0) {
    $_SESSION['error'] = 'Admission not found or already discharged';
    echo "<script>window.history.back()</script>";
    exit;
}

$db->query("INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
            VALUES ('$admission_id', '$description', '$amount', '$billing_type', 0, NOW())");

$_SESSION['success'] = 'Billing item added successfully';
header("Location: billing.php?id=$admission_id");
exit;
