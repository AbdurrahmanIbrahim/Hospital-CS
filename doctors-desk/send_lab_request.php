<?php
include '../functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

/* ============================
   READ JSON PAYLOAD
============================ */
$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = intval($data['appointment_id']);
$patient_id     = intval($data['patient_id']);
$doctor_id      = getId();
$priority       = !empty($_POST['priority']) ? sanitize($data['priority']) : 'routine';
$preferred_date = !empty($_POST['preferred_date']) ? sanitize($data['preferred_date']) : date('Y-m-d');
$preferred_time = !empty($_POST['preferred_time']) ? sanitize($data['preferred_time']) : 'asap';
$notes          = sanitize($data['notes']);
$specimen       = json_encode($data['specimen'] ?? []);
$tests          = $data['tests'] ?? [];

if ($appointment_id == 0 || empty($tests)) {
    echo json_encode(['status'=>'error','message'=>'Invalid request data']);
    exit;
}

/* ============================
   VERIFY APPOINTMENT
============================ */
$appt = $db->query("SELECT * FROM appointments WHERE id='$appointment_id' LIMIT 1")->fetch_assoc();
if (!$appt) {
    echo json_encode(['status'=>'error','message'=>'Appointment not found']);
    exit;
}

/* ============================
   DOCTOR ROOM VALIDATION
============================ */
if ($_SESSION['type'] == 3) {
    $roomCheck = $db->query("
        SELECT room_id
        FROM assign_doctors
        WHERE doctor_id='$doctor_id'
        AND status=1
        AND ended_at IS NULL
        LIMIT 1
    ");

    if ($roomCheck->num_rows == 0) {
        echo json_encode(['status'=>'error','message'=>'You are not assigned to any active room']);
        exit;
    }

    $activeRoom = $roomCheck->fetch_assoc()['room_id'];

    if ($activeRoom != $appt['room_id']) {
        echo json_encode(['status'=>'error','message'=>'Doctor not assigned to this room']);
        exit;
    }
}

/* ============================
   HANDLE RESUBMISSION
============================ */
$existing = $db->query("
    SELECT * FROM patient_test 
    WHERE appointment_id='$appointment_id' 
    LIMIT 1
")->fetch_assoc();

if ($existing) {

    // If already paid → block
    $payCheck = $db->query("
        SELECT status FROM payments 
        WHERE id='{$existing['payment_id']}'
        LIMIT 1
    ")->fetch_assoc();

    if ($payCheck && $payCheck['status'] == 1) {
        echo json_encode(['status'=>'error','message'=>'Lab payment already completed']);
        exit;
    }

    // Remove old records
    $db->query("DELETE FROM test_lists WHERE patient_test_id='{$existing['id']}'");
    $db->query("DELETE FROM payments WHERE id='{$existing['payment_id']}' AND purpose=3");
    $db->query("DELETE FROM patient_test WHERE id='{$existing['id']}'");
}

/* ============================
   CALCULATE LAB FEES
============================ */
$amount_total = 0;
foreach ($tests as $t) {
    $test_id = intval($t['id']);
    $price   = $db->query("
        SELECT amount FROM tests WHERE id='$test_id'
    ")->fetch_assoc()['amount'] ?? 0;

    $amount_total += $price;
}

/* ============================
   APPLY SCHEME DISCOUNT
============================ */
$scheme = $db->query("
    SELECT s.discount_fee
    FROM users u
    JOIN schemes s ON s.id=u.scheme_type
    WHERE u.id='$patient_id' AND s.status=1
")->fetch_assoc();

$discount = $scheme['discount_fee'] ?? 0;
$discount_amount = ($amount_total * $discount) / 100;
$net_amount = max(0, round($amount_total - $discount_amount, 2));

/* ============================
   CREATE PAYMENT
============================ */
$receipt = generateReceiptNumber($db);

$run = $db->query("
    INSERT INTO payments
    (patient_id, appointment_id, user_id, amount, discount, net_amount,
     record_date, purpose, note, reciept_num, status)
    VALUES
    ('$patient_id','$appointment_id','$doctor_id',
     '$amount_total','$discount','$net_amount',
     NOW(),3,'Lab Test Payment','$receipt',0)
");


if(!$run){
     echo json_encode(['status'=>'error','message'=>' '.$db->error]);
        exit;  
}



$payment_id = $db->insert_id;

/* ============================
   INSERT PATIENT TEST
============================ */
$db->query("
    INSERT INTO patient_test
    (appointment_id, user_id, payment_id, priority, specimen_collection,
     preferred_date, preferred_time, status)
    VALUES
    ('$appointment_id','$patient_id','$payment_id','$priority',
     '$specimen','$preferred_date','$preferred_time',0)
");

$patient_test_id = $db->insert_id;

/* ============================
   INSERT TEST LISTS
============================ */
foreach ($tests as $t) {
    $test_id = intval($t['id']);

    $db->query("
        INSERT INTO test_lists
        (patient_test_id, test_id, asker_id, status, date_request, clinical)
        VALUES
        ('$patient_test_id','$test_id','$doctor_id',0,NOW(),'$notes')
    ");
}

echo json_encode([
    'status' => 'success',
    'message' => 'Lab request sent successfully '.$doctor_id
]);
exit;
?>
