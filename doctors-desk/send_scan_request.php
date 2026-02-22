<?php
include '../functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = intval($data['appointment_id'] ?? 0);
$patient_id     = intval($data['patient_id'] ?? 0);
$doctor_id      = getId();
$priority       = sanitize($data['priority'] ?? 'routine');
$clinical_info  = sanitize($data['clinical_info'] ?? '');
$scans          = $data['scans'] ?? [];

if ($appointment_id == 0 || empty($scans)) {
    echo json_encode(['status'=>'error','message'=>'Invalid request data']);
    exit;
}

// Verify appointment
$appt = $db->query("SELECT * FROM appointments WHERE id='$appointment_id' LIMIT 1")->fetch_assoc();
if (!$appt) {
    echo json_encode(['status'=>'error','message'=>'Appointment not found']);
    exit;
}

// Doctor room validation
if ($_SESSION['type'] == 3) {
    $roomCheck = $db->query("
        SELECT room_id FROM assign_doctors
        WHERE doctor_id='$doctor_id' AND status=1 AND ended_at IS NULL
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

// Handle resubmission
$existing = $db->query("SELECT * FROM patient_scan WHERE appointment_id='$appointment_id' LIMIT 1")->fetch_assoc();
if ($existing) {
    $payCheck = $db->query("SELECT status FROM payments WHERE id='{$existing['payment_id']}' LIMIT 1")->fetch_assoc();
    if ($payCheck && $payCheck['status'] == 1) {
        echo json_encode(['status'=>'error','message'=>'Scan payment already completed']);
        exit;
    }
    $db->query("DELETE FROM scan_lists WHERE patient_scan_id='{$existing['id']}'");
    $db->query("DELETE FROM payments WHERE id='{$existing['payment_id']}' AND purpose=5");
    $db->query("DELETE FROM patient_scan WHERE id='{$existing['id']}'");
}

// Calculate total
$amount_total = 0;
foreach ($scans as $s) {
    $scan_id = intval($s['id']);
    $price = $db->query("SELECT amount FROM scans WHERE id='$scan_id'")->fetch_assoc()['amount'] ?? 0;
    $amount_total += $price;
}

// Apply scheme discount
$scheme = $db->query("
    SELECT s.discount_fee
    FROM users u
    JOIN schemes s ON s.id=u.scheme_type
    WHERE u.id='$patient_id' AND s.status=1
")->fetch_assoc();

$discount = $scheme['discount_fee'] ?? 0;
$discount_amount = ($amount_total * $discount) / 100;
$net_amount = max(0, round($amount_total - $discount_amount, 2));

// Create payment
$receipt = generateReceiptNumber($db);
$run = $db->query("
    INSERT INTO payments
    (patient_id, appointment_id, user_id, amount, discount, net_amount,
     record_date, purpose, note, reciept_num, status)
    VALUES
    ('$patient_id','$appointment_id','$doctor_id',
     '$amount_total','$discount','$net_amount',
     NOW(),5,'Radiology Scan Payment','$receipt',0)
");

if (!$run) {
    echo json_encode(['status'=>'error','message'=>$db->error]);
    exit;
}

$payment_id = $db->insert_id;

// Insert patient_scan
$db->query("
    INSERT INTO patient_scan
    (appointment_id, patient_id, user_id, payment_id, priority, clinical_info, status, date_request)
    VALUES
    ('$appointment_id','$patient_id','$doctor_id','$payment_id','$priority','$clinical_info',0,NOW())
");
$patient_scan_id = $db->insert_id;

// Insert scan_lists
foreach ($scans as $s) {
    $scan_id = intval($s['id']);
    $scan_amount = $db->query("SELECT amount FROM scans WHERE id='$scan_id'")->fetch_assoc()['amount'] ?? 0;
    $db->query("
        INSERT INTO scan_lists
        (patient_scan_id, scan_id, asker_id, status, date_request, amount)
        VALUES
        ('$patient_scan_id','$scan_id','$doctor_id',0,NOW(),'$scan_amount')
    ");
}

// If patient has active admission, add to admission billing
$activeAdmission = getActiveAdmission($patient_id);
if ($activeAdmission) {
    foreach ($scans as $s) {
        $scan_id = intval($s['id']);
        $scan_info = $db->query("SELECT name, amount FROM scans WHERE id='$scan_id'")->fetch_assoc();
        $desc = $db->real_escape_string("Scan: " . $scan_info['name']);
        $db->query("
            INSERT INTO admission_billing (admission_id, description, amount, billing_type, reference_id, paid)
            VALUES ('" . $activeAdmission['id'] . "', '$desc', '" . $scan_info['amount'] . "', 3, '$payment_id', 0)
        ");
    }
}

echo json_encode(['status'=>'success','message'=>'Scan request sent to radiology successfully']);
exit;
?>
