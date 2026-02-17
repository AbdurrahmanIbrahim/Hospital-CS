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
$name          = sanitize($_POST['name']);
$discount_fee  = sanitize($_POST['discount_fee']);

/* =========================
   VALIDATION
========================= */
if (empty($name) || $discount_fee === '') {
    $_SESSION['error'] = 'All fields are required';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($discount_fee < 0 || $discount_fee > 100) {
    $_SESSION['error'] = 'Discount must be between 0 and 100';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE SCHEME
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE schemes SET
            name = '$name',
            discount_fee = '$discount_fee',
            status = 1
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Scheme updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update scheme';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW SCHEME OR REACTIVATE
========================= */
$check = $db->query("SELECT id, status FROM schemes WHERE name='$name' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted scheme
        $sql = "UPDATE schemes SET discount_fee='$discount_fee', status=1 WHERE id='{$row['id']}'";
        if ($db->query($sql)) {
            $_SESSION['success'] = 'Scheme reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate scheme';
        }
        header("Location: view.php");
        exit;
    } else {
        // Scheme is active
        $_SESSION['error'] = 'Scheme already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW SCHEME
========================= */
$sql = "INSERT INTO schemes (name, discount_fee, status) VALUES ('$name', '$discount_fee', 1)";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Scheme added successfully';
} else {
    $_SESSION['error'] = 'Failed to add scheme';
}

header("Location: view.php");
exit;
