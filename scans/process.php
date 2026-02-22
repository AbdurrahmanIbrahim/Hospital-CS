<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$name        = sanitize($_POST['name']);
$modality    = sanitize($_POST['modality']);
$body_part   = sanitize($_POST['body_part']);
$amount      = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$description = sanitize($_POST['description']);

/* =========================
   VALIDATION
========================= */
$validModalities = ['X-Ray', 'CT', 'MRI', 'Ultrasound', 'Mammography', 'Fluoroscopy', 'Nuclear', 'PET'];

if (empty($name) || $amount <= 0 || empty($modality) || !in_array($modality, $validModalities)) {
    $_SESSION['error'] = 'Please fill all required fields correctly';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE SCAN TYPE
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE scans SET
            name        = '$name',
            modality    = '$modality',
            body_part   = '$body_part',
            amount      = '$amount',
            description = '$description'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Scan type updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update scan type';
    }

    header("Location: index.php?id=$id");
    exit;
}

/* =========================
   ADD NEW SCAN TYPE OR REACTIVATE
========================= */
$check = $db->query("SELECT id, status FROM scans WHERE name='$name' AND modality='$modality' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted scan type
        $sql = "UPDATE scans SET status=1, amount='$amount', body_part='$body_part', description='$description' WHERE id='{$row['id']}'";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Scan type reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate scan type';
        }

        header("Location: view.php");
        exit;
    } else {
        // Scan type already active
        $_SESSION['error'] = 'Scan type already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW SCAN TYPE
========================= */
$sql = "INSERT INTO scans (name, modality, body_part, amount, description)
        VALUES ('$name', '$modality', '$body_part', '$amount', '$description')";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Scan type added successfully';
} else {
    $_SESSION['error'] = 'Failed to add scan type';
}

header("Location: view.php");
exit;
