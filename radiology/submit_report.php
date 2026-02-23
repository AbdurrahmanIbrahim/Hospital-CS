<?php
include '../functions.php';

/* =========================
   AUTHORIZATION
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login to continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

/* =========================
   VALIDATE REQUEST
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   SANITIZE INPUT
========================= */
$radiologist_id = getId();
$scan_list_id   = filter_var($_POST['scan_list_id'] ?? null, FILTER_VALIDATE_INT);
$clinical_info  = sanitize($_POST['clinical_info'] ?? '');
$findings       = sanitize($_POST['findings'] ?? '');
$impression     = sanitize($_POST['impression'] ?? '');
$recommendation = sanitize($_POST['recommendation'] ?? '');

if (!$scan_list_id) {
    $_SESSION['error'] = 'Invalid scan reference';
    echo "<script>window.history.back()</script>";
    exit;
}

if (empty($findings)) {
    $_SESSION['error'] = 'Please enter the findings';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   HANDLE FILE UPLOAD
========================= */
$attachment_filename = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['attachment'];
    $max_size = 10 * 1024 * 1024; // 10MB

    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File too large. Maximum size is 10MB.';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/dicom'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'dcm'];

    if (!in_array($ext, $allowed_exts)) {
        $_SESSION['error'] = 'Invalid file type. Accepted: JPG, PNG, GIF, WebP, PDF, DCM.';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $upload_dir = '../images/scans/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $attachment_filename = 'scan_' . $scan_list_id . '_' . time() . '.' . $ext;
    $upload_path = $upload_dir . $attachment_filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $_SESSION['error'] = 'Failed to upload file. Please try again.';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   VALIDATE SCAN LIST EXISTS
========================= */
$sql = "SELECT id, status FROM scan_lists WHERE id = '$scan_list_id'";
$run = $db->query($sql);

if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Scan record not found';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK IF RESULT EXISTS
========================= */
$sql = "SELECT id, attachment FROM scan_results WHERE scan_list_id = '$scan_list_id' LIMIT 1";
$run = $db->query($sql);

if ($run && $run->num_rows > 0) {
    /* UPDATE EXISTING */
    $existing = $run->fetch_assoc();
    $attachment_sql = '';
    if ($attachment_filename) {
        // Delete old attachment if exists
        if (!empty($existing['attachment'])) {
            $old_file = '../images/scans/' . $existing['attachment'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
        $attachment_sql = ", attachment = '$attachment_filename'";
    }
    $sql = "UPDATE scan_results SET
                clinical_info  = '$clinical_info',
                findings       = '$findings',
                impression     = '$impression',
                recommendation = '$recommendation',
                radiologist_id = '$radiologist_id',
                updated_at     = NOW()
                $attachment_sql
            WHERE id = '{$existing['id']}'";

    if (!$db->query($sql)) {
        $_SESSION['error'] = 'Failed to update report';
        echo "<script>window.history.back()</script>";
        exit;
    }
} else {
    /* INSERT NEW */
    $att_col = $attachment_filename ? ', attachment' : '';
    $att_val = $attachment_filename ? ", '$attachment_filename'" : '';
    $sql = "INSERT INTO scan_results
                (scan_list_id, clinical_info, findings, impression, recommendation, radiologist_id, created_at, updated_at $att_col)
            VALUES
                ('$scan_list_id', '$clinical_info', '$findings', '$impression', '$recommendation', '$radiologist_id', NOW(), NOW() $att_val)";

    if (!$db->query($sql)) {
        $_SESSION['error'] = 'Failed to save report';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   UPDATE SCAN LIST STATUS
========================= */
$sql = "UPDATE scan_lists
        SET status = 3,
            date_reported = NOW()
        WHERE id = '$scan_list_id'";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Report submitted successfully';
    header("Location: index.php?status=3");
    exit;
}

/* =========================
   FALLBACK ERROR
========================= */
$_SESSION['error'] = 'An unexpected error occurred';
echo "<script>window.history.back()</script>";
exit;
