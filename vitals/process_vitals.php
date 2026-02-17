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
$nurse_notes = isset($_POST['nurse_notes']) ? sanitize($_POST['nurse_notes']) : '';
$appointment_id = null;
$vitals_to_insert = []; // array to hold vital_id => value

// Loop through POST to detect vitals
foreach ($_POST as $key => $value) {
    if (preg_match('/^vital_(\d+)_(\d+)$/', $key, $matches)) {
        $vital_id = intval($matches[1]);
        $appointment_id = intval($matches[2]);
        $vital_value = sanitize($value);
        $vitals_to_insert[] = [
            'vital_id' => $vital_id,
            'vital_value' => $vital_value
        ];
    }
}

/* =========================
   VALIDATION
========================= */
if (!$appointment_id) {
    $_SESSION['error'] = 'Invalid appointment selected.';
    echo "<script>window.history.back()</script>";
    exit;
}

if (empty($vitals_to_insert)) {
    $_SESSION['error'] = 'Please provide at least one vital value.';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   STORE VITALS
========================= */
// Delete old vitals for this appointment
$db->query("DELETE FROM patient_vitals WHERE appointment_id='$appointment_id'");

// Insert new vitals
foreach ($vitals_to_insert as $v) {
    $v_id = $v['vital_id'];
    $v_value = $v['vital_value'];
    $sql = "INSERT INTO patient_vitals (appointment_id, vital_id, vital_value) 
            VALUES ('$appointment_id', '$v_id', '$v_value')";
    $run = $db->query($sql);

    if (!$run) {
        $_SESSION['error'] = "Failed to save vitals: " . $db->error;
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   UPDATE NURSE NOTES
========================= */
$sql = "UPDATE appointments 
        SET nurse_notes='$nurse_notes' 
        WHERE id='$appointment_id'";
$run = $db->query($sql);

if (!$run) {
    $_SESSION['error'] = "Failed to save nurse notes: " . $db->error;
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SUCCESS
========================= */
$_SESSION['success'] = 'Vitals and Nurse Notes recorded successfully!';
header("Location: record_patient_vitals.php?id=$appointment_id");
exit;
?>
