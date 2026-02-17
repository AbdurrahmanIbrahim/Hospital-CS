<?php
include '../functions.php';

if (
    !IsLoggedIn() ||
    ($_SESSION['type'] != 0 && $_SESSION['type'] != 2 && $_SESSION['type'] != -2)
) {
    $_SESSION['error'] = 'Login to continue';
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$user_id = getId();
$handled = false;



foreach ($_POST as $key => $value) {

    // process only test selections
    if (strpos($key, 'test_') !== 0 || $value === '') {
        continue;
    }

    // Decode testlist ID
    $hash = str_replace('test_', '', $key);
    $testlist_id = intval($hash) - 1200;

    if ($testlist_id <= 0) {
        continue;
    }

    $status = sanitize($value);
    $reason = sanitize($_POST['reason_'.$hash]);

    // Rejection MUST have reason
    if ($status == -1 && empty($reason)) {
        continue;
    }

    // Verify test exists and is collectable
    $check = $db->query("
        SELECT id, status 
        FROM test_lists 
        WHERE id = '$testlist_id'
    ");

    if ($check->num_rows == 0) {
        continue;
    }

    $test = $check->fetch_assoc();

    // Allowed status transitions
    if (!in_array($test['status'], [1, -1])) {
        continue;
    }

    // Build update query
    if ($status == -1) {
        $sql = "
            UPDATE test_lists SET
                status = '-1',
                sample_collector = '$user_id',
                sample_collection_date = NOW(),
                reason = '$reason'
            WHERE id = '$testlist_id'
        ";
    } else {
        $sql = "
            UPDATE test_lists SET
                status = '$status',
                sample_collector = '$user_id',
                sample_collection_date = NOW(),
                reason = NULL
            WHERE id = '$testlist_id'
        ";
    }

    if ($db->query($sql)) {
        $handled = true;
    } else {
        echo $db->error;
        exit;
    }
}

// Final response
if ($handled) {
    $_SESSION['success'] = 'Specimen collection handled successfully';
    echo "<script>window.history.back()</script>";
    exit;
} else {
    $_SESSION['error'] = 'No test was processed';
    echo "<script>window.history.back()</script>";
    exit;
}
