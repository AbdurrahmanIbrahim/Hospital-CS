<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/* =========================
   READ JSON INPUT
========================= */
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$patient_type   = sanitize($data['patient_type'] ?? '');
$patient_id     = intval($data['patient_id'] ?? 0);
$walkin_name    = sanitize($data['walkin_name'] ?? '');
$walkin_phone   = sanitize($data['walkin_phone'] ?? '');
$tests          = $data['tests'] ?? [];
$clinical_notes = sanitize($data['clinical_notes'] ?? '');
$payment_method = sanitize($data['payment_method'] ?? 'Cash');

/* =========================
   VALIDATION
========================= */
if (empty($tests)) {
    echo json_encode(['success' => false, 'message' => 'Please select at least one test']);
    exit;
}

if ($patient_type === 'registered' && $patient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a registered patient']);
    exit;
}

if ($patient_type === 'walkin' && empty($walkin_name)) {
    echo json_encode(['success' => false, 'message' => 'Please enter walk-in patient name']);
    exit;
}

/* =========================
   CALCULATE TOTALS
========================= */
$total_amount = 0;
$test_ids = [];

foreach ($tests as $test) {
    $tid = intval($test['id']);
    $test_ids[] = $tid;

    // Get actual amount from database for security
    $tQ = $db->query("SELECT amount FROM tests WHERE id = '$tid' AND status = 1");
    if ($tQ && $tQ->num_rows > 0) {
        $tInfo = $tQ->fetch_assoc();
        $total_amount += floatval($tInfo['amount']);
    }
}

/* =========================
   SCHEME DISCOUNT
========================= */
$discount_pct = 0;
$discount_amount = 0;

if ($patient_type === 'registered' && $patient_id > 0) {
    $schQ = $db->query("
        SELECT IFNULL(s.discount_fee, 0) AS discount_fee
        FROM users u
        LEFT JOIN schemes s ON u.scheme_type = s.id AND s.status = 1
        WHERE u.id = '$patient_id'
    ");
    if ($schQ && $schQ->num_rows > 0) {
        $schInfo = $schQ->fetch_assoc();
        $discount_pct = floatval($schInfo['discount_fee']);
    }
}

$discount_amount = ($total_amount * $discount_pct) / 100;
$net_amount = $total_amount - $discount_amount;

/* =========================
   GENERATE RECEIPT
========================= */
$receipt_num = generateReceiptNumber($db);

/* =========================
   INSERT PAYMENT
========================= */
$pay_patient_id = ($patient_type === 'registered') ? $patient_id : 0;
$accountant_id  = getId();

if ($patient_type === 'walkin') {
    $note = "POS Walk-In: $walkin_name | $walkin_phone";
} else {
    $note = "POS Lab Test";
}

$paySQL = "
    INSERT INTO payments
        (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status, `payment-method`, payment_date, accountant_id, reciept_num, note)
    VALUES
        ('$pay_patient_id', 0, '$accountant_id', '$total_amount', '$discount_pct', '$net_amount', 3, NOW(), 1, '$payment_method', NOW(), '$accountant_id', '$receipt_num', '$note')
";

if (!$db->query($paySQL)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create payment', 'error' => $db->error]);
    exit;
}

$payment_id = $db->insert_id;

/* =========================
   INSERT PATIENT_TEST
========================= */
$pt_user_id = ($patient_type === 'registered') ? $patient_id : 0;

$ptSQL = "
    INSERT INTO patient_test
        (appointment_id, user_id, payment_id, priority, specimen_collection, preferred_date, preferred_time, status)
    VALUES
        (0, '$pt_user_id', '$payment_id', 'routine', '', CURDATE(), CURTIME(), 1)
";

if (!$db->query($ptSQL)) {
    echo json_encode(['success' => false, 'message' => 'Failed to create patient test', 'error' => $db->error]);
    exit;
}

$patient_test_id = $db->insert_id;

/* =========================
   INSERT TEST_LISTS
========================= */
$asker_id = getId();

foreach ($tests as $test) {
    $tid = intval($test['id']);

    // Get the actual test amount
    $tQ = $db->query("SELECT amount FROM tests WHERE id = '$tid' AND status = 1");
    $test_amount = 0;
    if ($tQ && $tQ->num_rows > 0) {
        $tInfo = $tQ->fetch_assoc();
        $test_amount = floatval($tInfo['amount']);
    }

    $tlSQL = "
        INSERT INTO test_lists
            (patient_test_id, test_id, asker_id, status, date_request, clinical, paid, amount)
        VALUES
            ('$patient_test_id', '$tid', '$asker_id', 1, NOW(), '$clinical_notes', 1, '$test_amount')
    ";

    if (!$db->query($tlSQL)) {
        echo json_encode(['success' => false, 'message' => 'Failed to add test to list', 'error' => $db->error]);
        exit;
    }
}

/* =========================
   SUCCESS RESPONSE
========================= */
echo json_encode([
    'success'     => true,
    'payment_id'  => $payment_id,
    'receipt_num' => $receipt_num,
    'message'     => 'Lab tests processed successfully'
]);
exit;
?>
