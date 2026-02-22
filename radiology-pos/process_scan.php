<?php
include '../functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$patient_type  = sanitize($data['patient_type'] ?? '');
$patient_id    = intval($data['patient_id'] ?? 0);
$walkin_name   = sanitize($data['walkin_name'] ?? '');
$walkin_phone  = sanitize($data['walkin_phone'] ?? '');
$scans         = $data['scans'] ?? [];
$clinical_info = sanitize($data['clinical_info'] ?? '');
$payment_method = sanitize($data['payment_method'] ?? '');

if (empty($scans) || empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Please select scans and payment method']);
    exit;
}

if ($patient_type === 'registered' && $patient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a patient']);
    exit;
}

if ($patient_type === 'walkin' && empty($walkin_name)) {
    echo json_encode(['success' => false, 'message' => 'Please enter walk-in patient name']);
    exit;
}

$user_id = getId();
$is_walkin = ($patient_type === 'walkin') ? 1 : 0;

// Calculate total
$amount_total = 0;
foreach ($scans as $s) {
    $scan_id = intval($s['id']);
    $scan = $db->query("SELECT amount FROM scans WHERE id='$scan_id' AND status=1");
    if ($scan && $scan->num_rows > 0) {
        $amount_total += $scan->fetch_assoc()['amount'];
    }
}

// Scheme discount (registered patients only)
$discount = 0;
if ($patient_type === 'registered' && $patient_id > 0) {
    $scheme = $db->query("
        SELECT s.discount_fee FROM users u
        JOIN schemes s ON s.id = u.scheme_type AND s.status = 1
        WHERE u.id = '$patient_id'
    ")->fetch_assoc();
    $discount = floatval($scheme['discount_fee'] ?? 0);
}

$discount_amount = ($amount_total * $discount) / 100;
$net_amount = max(0, round($amount_total - $discount_amount, 2));

// Generate receipt
$receipt_num = generateReceiptNumber($db);

// Payment note
$note = $is_walkin ? "POS Walk-In: $walkin_name | $walkin_phone" : "POS Radiology Scan";

// Create payment (status=1 = PAID immediately)
$pid = $is_walkin ? 0 : $patient_id;
$db->query("
    INSERT INTO payments (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status, `payment-method`, payment_date, accountant_id, reciept_num, note)
    VALUES ('$pid', 0, '$user_id', '$amount_total', '$discount', '$net_amount', 5, NOW(), 1, '$payment_method', NOW(), '$user_id', '$receipt_num', '$note')
");
$payment_id = $db->insert_id;

if (!$payment_id) {
    echo json_encode(['success' => false, 'message' => 'Payment creation failed: ' . $db->error]);
    exit;
}

// Create patient_scan
$db->query("
    INSERT INTO patient_scan (appointment_id, patient_id, user_id, payment_id, priority, clinical_info, status, is_walkin, walkin_name, walkin_phone, date_request)
    VALUES (0, '$pid', '$user_id', '$payment_id', 'routine', '$clinical_info', 1, '$is_walkin', '$walkin_name', '$walkin_phone', NOW())
");
$patient_scan_id = $db->insert_id;

// Create scan_lists (status=1, paid=1 - enters workflow at "pending scan")
foreach ($scans as $s) {
    $scan_id = intval($s['id']);
    $scan_amount = $db->query("SELECT amount FROM scans WHERE id='$scan_id'")->fetch_assoc()['amount'] ?? 0;
    $db->query("
        INSERT INTO scan_lists (patient_scan_id, scan_id, asker_id, status, date_request, paid, amount)
        VALUES ('$patient_scan_id', '$scan_id', '$user_id', 1, NOW(), 1, '$scan_amount')
    ");
}

echo json_encode([
    'success' => true,
    'message' => 'Scan order completed successfully',
    'payment_id' => $payment_id,
    'receipt_num' => $receipt_num
]);
exit;
