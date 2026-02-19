<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = intval($_GET['id']);

/* =========================
   VALIDATE ADMISSION
========================= */
$admission = $db->query("SELECT * FROM admissions WHERE id = '$admission_id' AND status = 0");
if ($admission->num_rows == 0) {
    $_SESSION['error'] = 'Admission not found or already discharged';
    echo "<script>window.history.back()</script>";
    exit;
}
$admission = $admission->fetch_assoc();

/* =========================
   PROCESS FINAL ROOM BILLING
========================= */
processRoomBilling($admission_id);

/* =========================
   CHECK ALL ITEMS PAID
========================= */
$unpaid = $db->query("SELECT COUNT(*) AS cnt FROM admission_billing WHERE admission_id = '$admission_id' AND paid = 0");
$unpaid_count = $unpaid->fetch_assoc()['cnt'];

if ($unpaid_count > 0) {
    $_SESSION['error'] = 'Cannot discharge: ' . $unpaid_count . ' unpaid billing item(s). All payments must be cleared before discharge.';
    header("Location: billing.php?id=$admission_id");
    exit;
}

/* =========================
   DISCHARGE PATIENT
========================= */
$db->query("UPDATE admissions SET status = 1, discharge_date = NOW() WHERE id = '$admission_id'");

$_SESSION['success'] = 'Patient discharged successfully!';
header('Location: view.php');
exit;
