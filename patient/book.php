<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}


/* =========================
   GET & SANITIZE INPUT
========================= */
$patient_id = intval($_GET['patient_id'] ?? 0);
$user_id    = getId(); // staff/admin booking

if ($patient_id <= 0) {
    $_SESSION['error'] = 'Invalid Patient';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM PATIENT EXISTS
========================= */
$patientCheck = $db->query("
    SELECT id, name, scheme_type
    FROM users
    WHERE id='$patient_id'
    AND type=1
    AND status=1
");

if ($patientCheck->num_rows == 0) {
    $_SESSION['error'] = 'Patient Not Found';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient = $patientCheck->fetch_assoc();

/* =========================
   CHECK ACTIVE APPOINTMENT
========================= */
$activeCheck = $db->query("
    SELECT id
    FROM appointments
    WHERE patient_id='$patient_id'
    AND (status=-1 OR status=0 OR status=1)
");

if ($activeCheck->num_rows > 0) {
    $_SESSION['error'] = 'Patient already has an active appointment';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   GET CONSULTATION FEE
========================= */
$consultation_fee = 0;
$hospitalQ = $db->query("SELECT consultation_fee FROM hospital_details LIMIT 1");
if ($hospitalQ && $hospitalQ->num_rows > 0) {
    $consultation_fee = floatval($hospitalQ->fetch_assoc()['consultation_fee']);
}

/* =========================
   GET SCHEME DISCOUNT
========================= */
$discount_pct = 0;
if (!empty($patient['scheme_type'])) {
    $schQ = $db->query("SELECT discount_fee FROM schemes WHERE id = '" . intval($patient['scheme_type']) . "' AND status = 1");
    if ($schQ && $schQ->num_rows > 0) {
        $discount_pct = floatval($schQ->fetch_assoc()['discount_fee']);
    }
}

/* =========================
   BOOK APPOINTMENT
========================= */
$date_appointed = date('Y-m-d H:i:s');

if ($consultation_fee > 0) {
    // Fee required: create appointment as pending payment (status = -1)
    $sql = "
        INSERT INTO appointments (
            patient_id,
            date_appointed,
            status
        ) VALUES (
            '$patient_id',
            '$date_appointed',
            -1
        )
    ";

    if (!$db->query($sql)) {
        $_SESSION['error'] = 'Failed to book appointment';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $appointment_id = $db->insert_id;

    // Calculate amounts
    $discount_amount = ($consultation_fee * $discount_pct) / 100;
    $net_amount = $consultation_fee - $discount_amount;

    // Create pending payment (purpose=6, status=0)
    $db->query("
        INSERT INTO payments (
            patient_id, appointment_id, user_id, amount, discount, net_amount,
            purpose, record_date, status, `payment-method`, note, reciept_num
        ) VALUES (
            '$patient_id', '$appointment_id', '$user_id', '$consultation_fee', '$discount_pct', '$net_amount',
            6, NOW(), 0, '', 'Consultation Fee', ''
        )
    ");

    $_SESSION['success'] = 'Appointment booked. Consultation fee payment is pending.';
} else {
    // No fee: create appointment directly as active (status = 0)
    $sql = "
        INSERT INTO appointments (
            patient_id,
            date_appointed,
            status
        ) VALUES (
            '$patient_id',
            '$date_appointed',
            0
        )
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Appointment booked successfully';
    } else {
        $_SESSION['error'] = 'Failed to book appointment';
    }
}

header("Location: ../appointments/index.php");
exit;
