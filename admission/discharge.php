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
$user_id = getId();

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
   PROCESS FINAL BILLING
========================= */
// Process any remaining room charges
processRoomBilling($admission_id);

// Discharge the patient
$db->query("UPDATE admissions SET status = 1, discharge_date = NOW() WHERE id = '$admission_id'");

// Calculate total from admission_billing
$total = getAdmissionTotal($admission_id);

// Get patient scheme for discount
$patient = $db->query("SELECT scheme_type FROM users WHERE id = '".$admission['patient_id']."'")->fetch_assoc();
$discount = 0;
if (!empty($patient['scheme_type'])) {
    $scheme = $db->query("SELECT discount_fee FROM schemes WHERE id = '".$patient['scheme_type']."' AND status = 1");
    if ($scheme && $scheme->num_rows > 0) {
        $discount = $scheme->fetch_assoc()['discount_fee'];
    }
}

$discount_amount = $total * ($discount / 100);
$net_amount = $total - $discount_amount;

// Update existing admission payment with final total or create discharge payment
$existing_payment = $db->query("SELECT id FROM payments WHERE appointment_id = '".$admission['appointment_id']."' AND purpose = 4 AND patient_id = '".$admission['patient_id']."' ORDER BY id ASC LIMIT 1");
if ($existing_payment && $existing_payment->num_rows > 0) {
    $payment_id = $existing_payment->fetch_assoc()['id'];
    $db->query("UPDATE payments SET amount = '$total', discount = '$discount_amount', net_amount = '$net_amount', note = 'Admission - Final Discharge Bill' WHERE id = '$payment_id'");
} else {
    $receipt_num = generateReceiptNumber($db);
    $db->query("INSERT INTO payments (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status, reciept_num, note)
                VALUES ('".$admission['patient_id']."', '".$admission['appointment_id']."', '$user_id', '$total', '$discount_amount', '$net_amount', 4, NOW(), 0, '$receipt_num', 'Admission - Final Discharge Bill')");
}

$_SESSION['success'] = 'Patient discharged successfully! Total bill: &#8358;' . number_format($net_amount, 2);
header('Location: view.php');
exit;
