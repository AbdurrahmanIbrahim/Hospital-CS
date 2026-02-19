<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 7)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$billing_id = intval($data['billing_id'] ?? 0);
$payment_method = sanitize($data['payment_method'] ?? '');

if ($billing_id <= 0 || empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Fetch billing item
$billing = $db->query("SELECT ab.*, a.patient_id, a.appointment_id FROM admission_billing ab INNER JOIN admissions a ON a.id = ab.admission_id WHERE ab.id = '$billing_id' LIMIT 1");
if ($billing->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Billing item not found']);
    exit;
}
$item = $billing->fetch_assoc();

if ($item['paid'] == 1) {
    echo json_encode(['success' => false, 'message' => 'Item already paid']);
    exit;
}

$patient_id = $item['patient_id'];
$appointment_id = $item['appointment_id'];
$amount = $item['amount'];
$user_id = getId();

// Get patient scheme discount
$patient = $db->query("SELECT scheme_type FROM users WHERE id = '$patient_id'")->fetch_assoc();
$discount = 0;
if (!empty($patient['scheme_type'])) {
    $scheme = $db->query("SELECT discount_fee FROM schemes WHERE id = '".$patient['scheme_type']."' AND status = 1");
    if ($scheme && $scheme->num_rows > 0) {
        $discount = $scheme->fetch_assoc()['discount_fee'];
    }
}

$discount_amount = $amount * ($discount / 100);
$net_amount = $amount - $discount_amount;

// Create payment record (already paid)
$receipt_num = generateReceiptNumber($db);
$db->query("INSERT INTO payments (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status, `payment-method`, payment_date, accountant_id, reciept_num, note)
            VALUES ('$patient_id', '$appointment_id', '$user_id', '$amount', '$discount_amount', '$net_amount', 4, NOW(), 1, '$payment_method', NOW(), '$user_id', '$receipt_num', '".$db->real_escape_string($item['description'])."')");
$payment_id = $db->insert_id;

// Mark billing item as paid
$db->query("UPDATE admission_billing SET paid = 1, payment_id = '$payment_id' WHERE id = '$billing_id'");

echo json_encode([
    'success' => true,
    'message' => 'Payment completed successfully',
    'receipt_num' => $receipt_num,
    'net_amount' => $net_amount
]);
exit;
