<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
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
    $_SESSION['error'] = 'Category name is required';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE CATEGORY
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE categories SET
            name   = '$name',
            status = '$status'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Category updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update category';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW CATEGORY OR REACTIVATE
========================= */
$check = $db->query("SELECT id, status FROM categories WHERE name='$name' LIMIT 1");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted category
        $sql = "UPDATE categories SET status=1 WHERE id='{$row['id']}'";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Category reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate category';
        }

        header("Location: view.php");
        exit;
    } else {
        // Category already active
        $_SESSION['error'] = 'Category already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW CATEGORY
========================= */
$sql = "INSERT INTO categories (name, status) VALUES ('$name', '$status')";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Category added successfully';
} else {
    $_SESSION['error'] = 'Failed to add category';
}

header("Location: view.php");
exit;
