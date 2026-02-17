<?php
include '../functions.php';

if (!isLoggedIn() or $_SESSION['type']!=0) {
    $_SESSION['error'] = 'Login to continue';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Sanitize Inputs
========================= */
$id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name      = sanitize($_POST['name']);
$email     = sanitize($_POST['email']);
$phone     = sanitize($_POST['phone']);
$userno    = sanitize($_POST['staff_id']);     // staff ID
$type  = sanitize($_POST['staff_type']);   // staff type
$user_id   = getId();              // hospital owner
$status    = 1;

/* =========================
   Validation
========================= */
if (empty($name) || empty($email) || empty($phone) || empty($userno) || empty($type)) {
    $_SESSION['error'] = 'All fields are required';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Duplicate Checks
========================= */
if ($id > 0) {
    $check = "
        SELECT id FROM users 
        WHERE (email='$email' OR userno='$userno')
        AND id!='$id'
    ";
} else {
    $check = "
        SELECT id FROM users 
        WHERE email='$email' OR userno='$userno'
    ";
}

$run = $db->query($check);
if ($run->num_rows > 0) {
    $_SESSION['error'] = 'Staff with same Email or Staff ID already exists';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Insert or Update
========================= */
if ($id > 0) {

    // UPDATE STAFF
    $sql = "
        UPDATE users SET
            name='$name',
            email='$email',
            phone='$phone',
            userno='$userno',
            type='$type'
        WHERE id='$id'
    ";

    $msg = "Staff updated successfully";

} else {

    // DEFAULT PASSWORD (change later)
    $password = password_hash('123456', PASSWORD_DEFAULT);

    // ADD STAFF
    $sql = "
        INSERT INTO users
        (name, email, phone, userno, category, password, user_id, type, status)
        VALUES
        ('$name','$email','$phone','$userno','$type','$password','$user_id','$type','$status')
    ";

    $msg = "Staff added successfully";
}

$run = $db->query($sql);

/* =========================
   Response
========================= */
if ($run) {
    $_SESSION['success'] = $msg;
    header('Location: view.php');
    exit;
} else {
    $_SESSION['error'] = "Database error: " . $db->error;
    echo "<script>window.history.back()</script>";
    exit;
}
