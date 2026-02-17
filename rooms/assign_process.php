<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

/* =========================
   VALIDATE INPUT
========================= */
if (empty($_POST['room_id']) || empty($_POST['doctor_id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$room_id   = sanitize($_POST['room_id']);
$doctor_id = sanitize($_POST['doctor_id']);
$user_id = getId();
$today = date('Y-m-d h:i:s');

/* =========================
   CHECK ROOM EXISTS
========================= */
$checkRoom = $db->query("SELECT id FROM rooms WHERE id = '$room_id' AND status = 1");
if ($checkRoom->num_rows == 0) {
    $_SESSION['error'] = 'Room not found';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK DOCTOR EXISTS
========================= */
$checkDoctor = $db->query("SELECT id FROM users WHERE id = '$doctor_id' AND type = 3 AND status = 1");
if ($checkDoctor->num_rows == 0) {
    $_SESSION['error'] = 'Doctor not found or inactive';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DEACTIVATE CURRENT DOCTOR
========================= */
$db->query("
    UPDATE assign_doctors
    SET status = -1,ended_date = '$today';
    WHERE room_id = '$room_id' AND status = 1
");

/* =========================
   REMOVE DOCTOR FROM OTHER ROOMS
========================= */
$db->query("
    UPDATE assign_doctors
    SET status = -1
    WHERE doctor_id = '$doctor_id' AND status = 1
");


/* =========================
   ASSIGN NEW DOCTOR
========================= */
$sql = "
    INSERT INTO assign_doctors (room_id, doctor_id, status, assigned_at,user_id)
    VALUES ('$room_id', '$doctor_id', 1, NOW(),'$user_id')
";

if (!$db->query($sql)) {
    $_SESSION['error'] = 'Failed to assign doctor';
    echo "<script>window.history.back()</script>";
    exit;
}

$_SESSION['success'] = 'Doctor assigned successfully';
header('Location: assign_doctors.php');
exit;
