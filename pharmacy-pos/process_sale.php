<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 6)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Read JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$patient_type   = sanitize($data['patient_type'] ?? '');
$patient_id     = intval($data['patient_id'] ?? 0);
$walkin_name    = sanitize($data['walkin_name'] ?? '');
$walkin_phone   = sanitize($data['walkin_phone'] ?? '');
$drugs          = $data['drugs'] ?? [];
$payment_method = sanitize($data['payment_method'] ?? 'Cash');
$current_user   = getId();

// Validate
if (empty($drugs)) {
    echo json_encode(['success' => false, 'message' => 'No drugs selected']);
    exit;
}

if ($patient_type === 'registered' && $patient_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a registered patient']);
    exit;
}

if ($patient_type === 'walkin' && empty($walkin_name)) {
    echo json_encode(['success' => false, 'message' => 'Please enter walk-in customer name']);
    exit;
}

// 1. Check stock for all drugs
$insufficient = [];
foreach ($drugs as $d) {
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);

    if ($qty <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity for one or more drugs']);
        exit;
    }

    $stockResult = $db->query("SELECT quantity FROM pharmacy_stock WHERE drug='$drug_id'");
    $stock = ($stockResult && $stockResult->num_rows > 0) ? $stockResult->fetch_assoc()['quantity'] : 0;

    if ($qty > $stock) {
        $drugName = $db->query("SELECT drug_name FROM drugs WHERE id='$drug_id'")->fetch_assoc()['drug_name'] ?? 'Unknown';
        $insufficient[] = "$drugName (available: $stock, requested: $qty)";
    }
}

if (!empty($insufficient)) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock: ' . implode(', ', $insufficient)]);
    exit;
}

// 2. Calculate total from drug selling_price * quantity
$amount_total = 0;
foreach ($drugs as $d) {
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);
    $price   = $db->query("SELECT selling_price FROM drugs WHERE id='$drug_id'")->fetch_assoc()['selling_price'];
    $amount_total += $price * $qty;
}

// 3. Get scheme discount for registered patients, 0 for walk-in
$discount = 0;
$scheme_name = '';
if ($patient_type === 'registered' && $patient_id > 0) {
    $patientInfo = $db->query("SELECT scheme_type FROM users WHERE id='$patient_id' AND type=1")->fetch_assoc();
    if ($patientInfo && !empty($patientInfo['scheme_type'])) {
        $schemeResult = $db->query("SELECT * FROM schemes WHERE id='" . intval($patientInfo['scheme_type']) . "' AND status=1");
        if ($schemeResult && $schemeResult->num_rows > 0) {
            $schemeInfo = $schemeResult->fetch_assoc();
            $discount = $schemeInfo['discount_fee'];
            $scheme_name = $schemeInfo['name'];
        }
    }
}

$discount_amount = ($amount_total * $discount) / 100;
$net_amount = max(0, round($amount_total - $discount_amount, 2));

// 4. Generate receipt number
$receipt_num = generateReceiptNumber($db);

// 5. Build note
if ($patient_type === 'walkin') {
    $note = "POS Walk-In: $walkin_name | $walkin_phone";
    $db_patient_id = 0;
} else {
    $note = "POS Drug Sale";
    $db_patient_id = $patient_id;
}

// 6. INSERT payment record
$db->query("
    INSERT INTO payments (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status, `payment-method`, payment_date, accountant_id, reciept_num, note)
    VALUES ('$db_patient_id', 0, '$current_user', '$amount_total', '$discount', '$net_amount', 2, NOW(), 1, '$payment_method', NOW(), '$current_user', '$receipt_num', '$note')
");

if ($db->error) {
    echo json_encode(['success' => false, 'message' => 'Payment insert failed: ' . $db->error]);
    exit;
}

$payment_id = $db->insert_id;

// 7. INSERT patient_drugs
$db->query("
    INSERT INTO patient_drugs (priority, notes, delivery_option, appointment_id, payment_id, user_id, status, created_date)
    VALUES ('normal', '$note', 'pickup', 0, '$payment_id', '$current_user', 1, NOW())
");

if ($db->error) {
    echo json_encode(['success' => false, 'message' => 'Patient drugs insert failed: ' . $db->error]);
    exit;
}

$patient_drugs_id = $db->insert_id;

// 8. INSERT drug_list for each drug and deduct stock
foreach ($drugs as $d) {
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);
    $presc   = sanitize($d['prescription'] ?? '');
    $price   = $db->query("SELECT selling_price FROM drugs WHERE id='$drug_id'")->fetch_assoc()['selling_price'];
    $amt     = $price * $qty;

    // Insert drug_list
    $db->query("
        INSERT INTO drug_list (drug_id, prescription, quantity, amount, patient_drugs_id)
        VALUES ('$drug_id', '$presc', '$qty', '$amt', '$patient_drugs_id')
    ");

    // Deduct pharmacy_stock
    $db->query("UPDATE pharmacy_stock SET quantity = quantity - $qty WHERE drug='$drug_id'");
}

// 9. If registered patient has active admission, add to admission_billing
if ($patient_type === 'registered' && $patient_id > 0) {
    $activeAdmission = getActiveAdmission($patient_id);
    if ($activeAdmission) {
        foreach ($drugs as $d) {
            $drug_id   = intval($d['id']);
            $qty       = intval($d['quantity']);
            $price     = $db->query("SELECT selling_price FROM drugs WHERE id='$drug_id'")->fetch_assoc()['selling_price'];
            $drug_name = $db->query("SELECT drug_name FROM drugs WHERE id='$drug_id'")->fetch_assoc()['drug_name'];
            $drug_total = $price * $qty;
            $desc = $db->real_escape_string("Drug: $drug_name (Qty: $qty) - POS Sale");
            $db->query("
                INSERT INTO admission_billing (admission_id, description, amount, billing_type, reference_id, paid)
                VALUES ('" . $activeAdmission['id'] . "', '$desc', '$drug_total', 2, '$payment_id', 1)
            ");
        }
    }
}

// 10. Return success
echo json_encode([
    'success'     => true,
    'payment_id'  => $payment_id,
    'receipt_num' => $receipt_num,
    'message'     => 'Sale completed successfully'
]);
exit;
?>
