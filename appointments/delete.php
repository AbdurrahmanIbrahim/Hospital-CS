<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid appointment';
    echo "<script>window.history.back()</script>";
    exit;
}

// Check appointment exists
$appQ = $db->query("SELECT id, status FROM appointments WHERE id = '$id'");
if ($appQ->num_rows == 0) {
    $_SESSION['error'] = 'Appointment not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment = $appQ->fetch_assoc();

// If pending payment, also delete the unpaid consultation fee payment
if ($appointment['status'] == -1) {
    $db->query("DELETE FROM payments WHERE appointment_id = '$id' AND purpose = 6 AND status = 0");
}

// Delete appointment
if ($db->query("DELETE FROM appointments WHERE id = '$id'")) {
    $_SESSION['success'] = 'Appointment deleted successfully';
} else {
    $_SESSION['error'] = 'Failed to delete appointment';
}

header("Location: index.php");
exit;
