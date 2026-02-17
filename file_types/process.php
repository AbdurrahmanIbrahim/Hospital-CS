<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$name   = sanitize($_POST['name']);
$amount = sanitize($_POST['amount']);
$status = 1; // always active by default

/* =========================
   BASIC VALIDATION
========================= */
if (empty($name) || $amount === '') {
    $_SESSION['error'] = 'All fields are required';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE FILE TYPE
========================= */
if (!empty($_POST['id'])) {

    $id = intval($_POST['id']);

    $sql = "
        UPDATE file_types SET
            name   = '$name',
            amount = '$amount'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'File Type Updated Successfully';
    } else {
        $_SESSION['error'] = 'Failed to Update File Type';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW FILE TYPE
========================= */

/* Prevent duplicate file type name */
$check = $db->query("SELECT id FROM file_types WHERE name='$name' AND status=1");
if ($check->num_rows > 0) {
    $_SESSION['error'] = 'File Type already exists';
    echo "<script>window.history.back()</script>";
    exit;
}

$sql = "
    INSERT INTO file_types (name, amount, status)
    VALUES ('$name', '$amount', '$status')
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'File Type Added Successfully';
} else {
    $_SESSION['error'] = 'Failed to Add File Type';
}

header("Location: view.php");
exit;
