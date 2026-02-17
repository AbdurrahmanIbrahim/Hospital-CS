<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0  AND $_SESSION['type'] != 6)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Read JSON payload
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$payment_id     = intval($data['payment_id'] ?? 0);
$collection_status  = floatval($data['collection_status'] ?? 0);


if ($payment_id <= 0 || $collection_status <= 0 || ($collection_status!=1 AND $collection_status !=2)) {
    echo json_encode(['success' => false, 'message' => 'Invalid  data']);
    exit;
}

/* =========================
   FETCH PAYMENT
========================= */
$payQ = $db->query("SELECT * FROM payments WHERE id = '$payment_id' LIMIT 1");
if ($payQ->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Payment record not found']);
    exit;
}

/* =========================
   UPDATE PAYMENT
========================= */
$user_id = getId();

$update = $db->query("
    UPDATE patient_drugs SET
        status = '$collection_status',
        collection_date = NOW(),
        user_id = '$user_id'
    WHERE payment_id = '$payment_id'
");

if (!$update) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status',
        'error'   => $db->error
    ]);
    exit;
}

$text = ($collection_status == '1') ? 'Collected' : 'Not Given';

echo json_encode([
    'success' => true,
    'message' => 'Status saved successfully to '.$text
]);
exit;
?>
