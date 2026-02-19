<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

if (empty($_POST['appointment_id']) || empty($_POST['patient_id']) || empty($_POST['room_id']) || empty($_POST['bed_number'])) {
    $_SESSION['error'] = 'All required fields must be filled';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment_id = intval($_POST['appointment_id']);
$patient_id = intval($_POST['patient_id']);
$room_id = intval($_POST['room_id']);
$bed_number = intval($_POST['bed_number']);
$notes = sanitize($_POST['notes'] ?? '');
$user_id = getId();
$doctor_id = $user_id;

/* =========================
   VALIDATIONS
========================= */

// Check appointment exists
$app = $db->query("SELECT * FROM appointments WHERE id = '$appointment_id' AND patient_id = '$patient_id'");
if ($app->num_rows == 0) {
    $_SESSION['error'] = 'Appointment not found';
    echo "<script>window.history.back()</script>";
    exit;
}

// Check no existing active admission for this patient
$existing = getActiveAdmission($patient_id);
if ($existing) {
    $_SESSION['error'] = 'Patient already has an active admission';
    echo "<script>window.history.back()</script>";
    exit;
}

// Check room is valid admission room
$room = $db->query("SELECT * FROM rooms WHERE id = '$room_id' AND room_type = 1 AND status = 1");
if ($room->num_rows == 0) {
    $_SESSION['error'] = 'Invalid admission room';
    echo "<script>window.history.back()</script>";
    exit;
}
$room_info = $room->fetch_assoc();

// Check bed availability
$available = getAvailableBeds($room_id);
if ($available <= 0) {
    $_SESSION['error'] = 'No beds available in this room';
    echo "<script>window.history.back()</script>";
    exit;
}

// Validate bed number within range
if ($bed_number < 1 || $bed_number > $room_info['bed_space']) {
    $_SESSION['error'] = 'Invalid bed number';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CREATE ADMISSION
========================= */
$sql = "INSERT INTO admissions (patient_id, appointment_id, room_id, bed_number, doctor_id, admitted_by, admission_date, status, notes, last_billed_at, user_id)
        VALUES ('$patient_id', '$appointment_id', '$room_id', '$bed_number', '$doctor_id', '$user_id', NOW(), 0, '$notes', NOW(), '$user_id')";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = 'Failed to admit patient. Please try again.';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = $db->insert_id;

// Create initial room billing entry (Day 1)
$room_price = $room_info['room_price'];
$db->query("INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
            VALUES ('$admission_id', 'Room charge - Day 1', '$room_price', 1, 0, NOW())");

$_SESSION['success'] = 'Patient admitted successfully!';
header('Location: view.php');
exit;
