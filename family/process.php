<?php
include '../functions.php';

// Check login and permissions
if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$name    = sanitize($_POST['name']);
$phone   = sanitize($_POST['phone']);
$email   = sanitize($_POST['email']);
$address = sanitize($_POST['address']);
$status  = 1; // default to active

/* =========================
   VALIDATION
========================= */
if (empty($name)) {
    $_SESSION['error'] = 'Family name is required';
    echo "<script>window.history.back()</script>";
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE FAMILY
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE families SET
            name = '$name',
            phone = '$phone',
            email = '$email',
            address = '$address',
            status = '$status'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Family updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update family';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   CHECK IF FAMILY EXISTS
========================= */
$check = $db->query("SELECT id, status FROM families WHERE name='$name' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == 'inactive' || $row['status'] == -1) {
        // Reactivate soft-deleted family
        $sql = "
            UPDATE families SET
                phone='$phone',
                email='$email',
                address='$address',
                status='active'
            WHERE id='{$row['id']}'
        ";
        if ($db->query($sql)) {
            $_SESSION['success'] = 'Family reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate family';
        }
        header("Location: view.php");
        exit;
    } else {
        // Family is active
        $_SESSION['error'] = 'Family already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW FAMILY
========================= */
$sql = "
    INSERT INTO families (name, phone, email, address, status) 
    VALUES ('$name', '$phone', '$email', '$address', '1')
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Family added successfully';
} else {
    $_SESSION['error'] = 'Failed to add family';
}

header("Location: view.php");
exit;
