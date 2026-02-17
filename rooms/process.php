<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}
/* =========================
   SANITIZE INPUTS
========================= */
$room_name   = sanitize($_POST['room_name']);
$room_type   = intval($_POST['room_type']); // 0 = consultation, 1 = admission
$ward = sanitize($_POST['ward']);
$status      = 1;
$user_id = getId();

$bed_space   = isset($_POST['bed_space']) ? intval($_POST['bed_space']) : 0;
$room_price  = isset($_POST['room_price']) ? floatval($_POST['room_price']) : 0;

/* =========================
   VALIDATION
========================= */
if (empty($room_name) || ($room_type !== 0 && $room_type !== 1)) {
    $_SESSION['error'] = 'All required fields must be filled';
    echo "<script>window.history.back()</script>";
    exit;
}

// Admission room must have bed & price
if ($room_type == 1 && ($bed_space <= 0 || $room_price <= 0)) {
    $_SESSION['error'] = 'Admission rooms require bed space and price';
    echo "<script>window.history.back()</script>";
    exit;
}

// Consultation room → force reset
if ($room_type == 0) {
    $bed_space  = 0;
    $room_price = 0;
}

$sql =  "SELECT id FROM wards WHERE id = '$ward' AND status = 1";
$run = $db->query($sql);
if($run->num_rows == 0){
     $_SESSION['error'] = 'Ward Not Found';
    echo "<script>window.history.back()</script>";
    exit;  
}

/* =========================
   UPDATE ROOM
========================= */
if (!empty($_POST['id'])) {

    $id = intval($_POST['id']);

    $sql = "
        UPDATE rooms SET
            room_name   = '$room_name',
            room_type   = '$room_type',
            bed_space   = '$bed_space',
            room_price  = '$room_price',
            status      = '$status',
            ward        = '$ward'
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Room Updated Successfully';
    } else {
        $_SESSION['error'] = 'Failed to Update Room';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW ROOM
========================= */

// Prevent duplicate room name
$check = $db->query("SELECT id FROM rooms WHERE room_name='$room_name'");
if ($check->num_rows > 0) {
    $_SESSION['error'] = 'Room name already exists';
    echo "<script>window.history.back()</script>";
    exit;
}

$sql = "
    INSERT INTO rooms (
        room_name,
        room_type,
        bed_space,
        room_price,
        status,
        ward,
        user_id
    ) VALUES (
        '$room_name',
        '$room_type',
        '$bed_space',
        '$room_price',
        '1',
        '$ward',
        '$user_id'
    )
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Room Added Successfully';
} else {
    $_SESSION['error'] = 'Failed to Add Room';
}

header("Location: view.php");
exit;
