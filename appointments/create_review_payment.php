<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

$appointment_id = intval($_GET['appointment_id'] ?? 0);
$user_id = getId();

if ($appointment_id <= 0) {
    $_SESSION['error'] = 'Invalid Appointment';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM APPOINTMENT IS COMPLETED
========================= */
$appointmentCheck = $db->query("
    SELECT a.id, a.patient_id, a.status
    FROM appointments a
    WHERE a.id = '$appointment_id' AND a.status = 2
");

if ($appointmentCheck->num_rows == 0) {
    $_SESSION['error'] = 'Appointment not found or not completed';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment = $appointmentCheck->fetch_assoc();
$patient_id = $appointment['patient_id'];

/* =========================
   GET CONSULTATION FEE
========================= */
$consultation_fee = 0;
$hospitalQ = $db->query("SELECT consultation_fee FROM hospital_details LIMIT 1");
if ($hospitalQ && $hospitalQ->num_rows > 0) {
    $consultation_fee = floatval($hospitalQ->fetch_assoc()['consultation_fee']);
}

if ($consultation_fee <= 0) {
    $_SESSION['error'] = 'Consultation fee not configured';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK FOR EXISTING PENDING PAYMENT
========================= */
$existingPayment = $db->query("
    SELECT id FROM payments
    WHERE appointment_id = '$appointment_id' AND purpose = 6 AND status = 0
");
if ($existingPayment->num_rows > 0) {
    $_SESSION['error'] = 'A review payment is already pending for this appointment';
    header("Location: index.php");
    exit;
}

/* =========================
   GET SCHEME DISCOUNT
========================= */
$discount_pct = 0;
$patientQ = $db->query("SELECT scheme_type FROM users WHERE id = '$patient_id'");
if ($patientQ && $patientQ->num_rows > 0) {
    $patient = $patientQ->fetch_assoc();
    if (!empty($patient['scheme_type'])) {
        $schQ = $db->query("SELECT discount_fee FROM schemes WHERE id = '" . intval($patient['scheme_type']) . "' AND status = 1");
        if ($schQ && $schQ->num_rows > 0) {
            $discount_pct = floatval($schQ->fetch_assoc()['discount_fee']);
        }
    }
}

$discount_amount = ($consultation_fee * $discount_pct) / 100;
$net_amount = $consultation_fee - $discount_amount;

/* =========================
   SET APPOINTMENT TO PENDING PAYMENT
========================= */
$db->query("UPDATE appointments SET status = -1 WHERE id = '$appointment_id'");

/* =========================
   CREATE PAYMENT RECORD
========================= */
$db->query("
    INSERT INTO payments (
        patient_id, appointment_id, user_id, amount, discount, net_amount,
        purpose, record_date, status, `payment-method`, note, reciept_num
    ) VALUES (
        '$patient_id', '$appointment_id', '$user_id', '$consultation_fee', '$discount_pct', '$net_amount',
        6, NOW(), 0, '', 'Review Consultation Fee', ''
    )
");

$_SESSION['success'] = 'Review payment created. Patient must pay the consultation fee before the doctor can review.';
header("Location: index.php");
exit;
