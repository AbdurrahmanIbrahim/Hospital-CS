<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 4)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

/* =========================
   SANITIZE INPUT
========================= */
$room_id        = isset($_GET['room']) ? intval($_GET['room']) : 0;
$appointment_id = isset($_GET['appointment']) ? intval($_GET['appointment']) : 0;
$nurse_id       = getId();

/* =========================
   BASIC VALIDATION
========================= */
if (!$room_id || !$appointment_id) {
    $_SESSION['error'] = 'Invalid request.';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   VERIFY APPOINTMENT
========================= */
$sql = "
    SELECT *
    FROM appointments
    WHERE id = '$appointment_id'
    AND status = 0
    LIMIT 1
";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Appointment is not valid or already closed.';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment = $run->fetch_assoc();

/* =========================
   CHECK IF VITALS EXIST
========================= */
$sql = "
    SELECT COUNT(*) AS total
    FROM patient_vitals
    WHERE appointment_id = '$appointment_id'
";
$run = $db->query($sql);
$row = $run->fetch_assoc();

if ($row['total'] == 0) {
    $_SESSION['error'] = 'Vitals must be recorded before assigning a room.';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   GET DOCTOR ASSIGNED TO ROOM
========================= */
$sql = "
    SELECT doctor_id
    FROM assign_doctors
    WHERE room_id = '$room_id'
    AND status = 1
    LIMIT 1
";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'No active doctor assigned to this room.';
    echo "<script>window.history.back()</script>";
    exit;
}

$doctor = $run->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

/* =========================
   ASSIGN ROOM TO APPOINTMENT
========================= */
$sql = "
    UPDATE appointments SET
        room_id   = '$room_id',
        doctor_id = '$doctor_id',
        nurse_id  = '$nurse_id',
        status = 1
    WHERE id = '$appointment_id'
";

$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = 'Failed to assign room: ' . $db->error;
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SUCCESS
========================= */
$_SESSION['success'] = 'Patient successfully assigned to room and doctor.';
header("Location: record_vital.php");
exit;
