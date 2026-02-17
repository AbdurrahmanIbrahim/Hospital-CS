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
$vital_name = isset($_POST['vital_name']) ? sanitize($_POST['vital_name']) : '';
$vital_unit = isset($_POST['vital_unit']) ? sanitize($_POST['vital_unit']) : '';
$id         = isset($_POST['id']) ? intval($_POST['id']) : null;

/* =========================
   VALIDATION
========================= */
if (empty($vital_name) OR empty($vital_unit)) {
    $_SESSION['error'] = 'Please provide a valid vital name and unit.';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   ADD OR UPDATE
========================= */
if ($id) {
    // UPDATE EXISTING VITAL
    $sql = "UPDATE vitals 
            SET name='$vital_name',unit='$vital_unit',status=1 
            WHERE id='$id'";
    $run = $db->query($sql);

    if (!$run) {
        $_SESSION['error'] = "Failed to update vital: " . $db->error;
        echo "<script>window.history.back()</script>";
        exit;
    }

    $_SESSION['success'] = "Vital updated successfully!";
} else {
    // ADD NEW VITAL
    $sql = "INSERT INTO vitals (name,unit, status) VALUES ('$vital_name','$vital_unit', 1)";
    $run = $db->query($sql);

    if (!$run) {
        $_SESSION['error'] = "Failed to add vital: " . $db->error;
        echo "<script>window.history.back()</script>";
        exit;
    }

    $_SESSION['success'] = "Vital added successfully!";
}

/* =========================
   REDIRECT BACK
========================= */
header('Location: view.php');
exit;
