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
$ward_name = sanitize($_POST['ward_name']);
$status    = isset($_POST['status']) ? intval($_POST['status']) : 1;

/* =========================
   VALIDATION
========================= */
if (empty($ward_name)) {
    $_SESSION['error'] = 'Ward name is required';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE WARD
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE wards SET
            ward_name = '$ward_name',
            status    = '$status'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Ward updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update ward';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW WARD OR REACTIVATE
========================= */
$check = $db->query("
    SELECT id, status 
    FROM wards 
    WHERE ward_name = '$ward_name' 
    LIMIT 1
");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted ward
        $sql = "UPDATE wards SET status = 1 WHERE id = '{$row['id']}'";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Ward reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate ward';
        }

        header("Location: view.php");
        exit;
    } else {
        // Ward already exists
        $_SESSION['error'] = 'Ward already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW WARD
========================= */
$sql = "
    INSERT INTO wards (ward_name, status) 
    VALUES ('$ward_name', '$status')
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Ward added successfully';
} else {
    $_SESSION['error'] = 'Failed to add ward';
}

header("Location: view.php");
exit;
