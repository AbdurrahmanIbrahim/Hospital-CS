<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$name     = sanitize($_POST['name']);
$amount   = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$specimen = isset($_POST['specimen']) ? intval($_POST['specimen']) : 0;
$type     = isset($_POST['type']) ? intval($_POST['type']) : 0;
$status   = isset($_POST['status']) ? intval($_POST['status']) : 1;

/* =========================
   VALIDATION
========================= */
if (empty($name) || $amount <= 0 || $specimen == 0 || $type == 0) {
    $_SESSION['error'] = 'Please fill all required fields correctly';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE TEST
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE tests SET
            name     = '$name',
            amount   = '$amount',
            specimen = '$specimen',
            type     = '$type',
            status   = '$status'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Test updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update test';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW TEST OR REACTIVATE
========================= */
$check = $db->query("SELECT id, status FROM tests WHERE name='$name' AND specimen='$specimen' AND type='$type' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted test
        $sql = "UPDATE tests SET status=1, amount='$amount' WHERE id='{$row['id']}'";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Test reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate test';
        }

        header("Location: view.php");
        exit;
    } else {
        // Test already active
        $_SESSION['error'] = 'Test already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW TEST
========================= */
$sql = "INSERT INTO tests (name, amount, specimen, type, status) 
        VALUES ('$name', '$amount', '$specimen', '$type', '$status')";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Test added successfully';
} else {
    $_SESSION['error'] = 'Failed to add test';
}

header("Location: view.php");
exit;
