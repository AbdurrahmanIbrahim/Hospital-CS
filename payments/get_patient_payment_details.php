<?php
include '../functions.php';


header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 7)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/*
  Your JS calls:
  fetch(`get_patient_payment_details.php?id=${paymentId}`)
*/
$payment_id = intval($_GET['id'] ?? 0);


if ($payment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
    exit;
}

/* =========================
   PAYMENT + PATIENT
========================= */
$paymentQ = $db->query("
    SELECT 
        p.*,
        u.name,
        u.phone,
        u.email,
        u.hospital_num
    FROM payments p
    JOIN users u ON u.id = p.patient_id
    WHERE p.id = '$payment_id'
    LIMIT 1
");

if ($paymentQ->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Payment not found']);
    exit;
}

$payment = $paymentQ->fetch_assoc();
$patient_id = $payment['patient_id'];
$purpose = $payment['purpose'];
$totalAmount = $payment['amount'];
$net_amount = $payment['net_amount'];
$discount = $payment['discount'];

/* =========================
   PATIENT OBJECT
========================= */
$patient = [
    'name'         => $payment['name'],
    'hospital_num' => $payment['hospital_num'],
    'phone'        => $payment['phone'],
    'email'        => $payment['email']
];

/* =========================
   APPOINTMENTS
========================= */
$appointments = [];

$appQ = $db->query("
    SELECT 
        date_appointed,
        diagnosis
    FROM appointments
    WHERE patient_id = '$patient_id'
    ORDER BY date_appointed DESC
");



while ($row = $appQ->fetch_assoc()) {
    $appointments[] = $row;
}

/* =========================
   ITEMS / DRUGS / LABS
========================= */
$items = [];
$total_amount = 0;

/* ---- DRUG ITEMS ---- */
if($purpose == 2){
  
    $drugQ = $db->query("
    SELECT 
        d.drug_name AS name,
        'Drug' AS type,
        dl.amount AS price
    FROM patient_drugs pd
    JOIN drug_list dl ON dl.patient_drugs_id = pd.id
    JOIN drugs d ON d.id = dl.drug_id
    WHERE pd.payment_id = '$payment_id'
");

   

while ($row = $drugQ->fetch_assoc()) {
    $items[] = $row;
    $total_amount += (float)$row['price'];
}

}else if($purpose == 3){

/* ---- LAB TEST ITEMS ---- */
$labQ = $db->query("
    SELECT 
        t.name,
        'Lab Test' AS type,
        t.amount AS price
    FROM patient_test pt
    JOIN test_lists tl ON tl.patient_test_id = pt.id
    JOIN tests t ON t.id = tl.test_id
    WHERE pt.payment_id = '$payment_id'
");


while ($row = $labQ->fetch_assoc()) {
    $items[] = $row;
    $total_amount += (float)$row['price'];
}


while ($row = $labQ->fetch_assoc()) {
    $items[] = $row;
    $total_amount += (float)$row['price'];
}



}else if($purpose == 4){

/* ---- ADMISSION BILLING ITEM ---- */
$billingQ = $db->query("
    SELECT description AS name,
           CASE billing_type WHEN 1 THEN 'Room Stay' WHEN 2 THEN 'Drug' ELSE 'Other' END AS type,
           amount AS price
    FROM admission_billing
    WHERE payment_id = '$payment_id'
    LIMIT 1
");
if($billingQ){
    while ($row = $billingQ->fetch_assoc()) {
        $items[] = $row;
        $total_amount += (float)$row['price'];
    }
}

}


/* ---- LAB TESTS (if any) ---- */
/*


/* =========================
   LAST PAYMENT
========================= */
$lastPayment = null;

$lastQ = $db->query("
    SELECT 
        net_amount,
       `payment-method` AS  payment_method,
        payment_date,
        record_date
    FROM payments
    WHERE patient_id = '$patient_id'
    AND status = 1
    ORDER BY id DESC
    LIMIT 1
");

if(!$lastQ)
 echo $db->error;

if ($lastQ->num_rows > 0) {
    $lastPayment = $lastQ->fetch_assoc();
}

/* =========================
   RESPONSE
========================= */
echo json_encode([
    'success'       => true,
    'patient'       => $patient,
    'appointments'  => $appointments,
    'items'         => $items,
    'last_payment'  => $lastPayment,
    'total_amount'  => $totalAmount,
    'net_amount'=>$net_amount,
    'discount'=>$discount
]);

exit;
?>
