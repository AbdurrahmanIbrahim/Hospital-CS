<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$name   = sanitize($_POST['name']);
$status = isset($_POST['status']) ? intval($_POST['status']) : 1;

/* =========================
   VALIDATION
========================= */
if (empty($name)) {
    $_SESSION['error'] = 'Specimen name is required';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE SPECIMEN
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE specimen SET
            name   = '$name',
            status = '$status'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Specimen updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update specimen';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW SPECIMEN OR REACTIVATE
========================= */
$check = $db->query("SELECT id, status FROM specimen WHERE name='$name' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted specimen
        $sql = "UPDATE specimen SET status=1 WHERE id='{$row['id']}'";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Specimen reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate specimen';
        }

        header("Location: view.php");
        exit;
    } else {
        // Specimen already exists
        $_SESSION['error'] = 'Specimen already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW SPECIMEN
========================= */
$sql = "INSERT INTO specimen (name, status) VALUES ('$name', '$status')";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Specimen added successfully';
} else {
    $_SESSION['error'] = 'Failed to add specimen';
}

header("Location: view.php");
exit;
