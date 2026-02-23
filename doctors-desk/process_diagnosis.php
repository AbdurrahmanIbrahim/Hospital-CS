<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   GET & SANITIZE INPUT
========================= */
$appointment_id = !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

$diagnosis      = !empty($_POST['diagnosis']) ? sanitize($_POST['diagnosis']) : "";
$clinical_notes = !empty($_POST['clinical_notes']) ? sanitize($_POST['clinical_notes']) : "";

$user_id        = getId();
$user_type      = $_SESSION['type'];

if ($appointment_id <= 0 || empty($diagnosis) || empty($clinical_notes)) {
    $_SESSION['error'] = 'Enter All Fields (Diagnosis & clinical notes)';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CONFIRM APPOINTMENT EXISTS
========================= */
$appointmentCheck = $db->query("
    SELECT id, room_id, doctor_id, status
    FROM appointments
    WHERE id='$appointment_id'
");

if ($appointmentCheck->num_rows == 0) {
    $_SESSION['error'] = 'Appointment Not Found';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment = $appointmentCheck->fetch_assoc();

/* =========================
   CHECK APPOINTMENT STATUS
========================= */
if ($appointment['status'] != 1 && $appointment['status'] != 2) {
    $_SESSION['error'] = 'Appointment already closed';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DOCTOR-SPECIFIC VALIDATION
========================= */
if ($user_type == 3) {

    /* ---- Check active room for doctor ---- */
    $roomCheck = $db->query("
        SELECT room_id
        FROM assign_doctors
        WHERE doctor_id='$user_id'
        AND status=1
        AND ended_at IS NULL
        LIMIT 1
    ");

    if ($roomCheck->num_rows == 0) {
        $_SESSION['error'] = 'You are not assigned to any active room';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $activeRoom = $roomCheck->fetch_assoc()['room_id'];

    /* ---- Compare room with appointment room ---- */
    if ($activeRoom != $appointment['room_id']) {
        $_SESSION['error'] = 'You are not assigned to this consultation room';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   SAVE DIAGNOSIS
========================= */
$update = $db->query("
    UPDATE appointments
    SET diagnosis='$diagnosis',
        clinical_notes = '$clinical_notes',
        doctor_id='$user_id'
    WHERE id='$appointment_id'
");

if ($update) {
    $_SESSION['success'] = 'Diagnosis saved successfully';
} else {
    $_SESSION['error'] = 'Failed to save diagnosis';
}

echo "<script>window.history.back()</script>";
exit;
