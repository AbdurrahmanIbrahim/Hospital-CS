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
    SELECT id, name 
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
    AND status=0
");

if ($activeCheck->num_rows > 0) {
    $_SESSION['error'] = 'Patient already has an active appointment';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   BOOK APPOINTMENT
========================= */
$date_appointed = date('Y-m-d H:i:s');

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

header("Location: ../appointments/index.php");
exit;
