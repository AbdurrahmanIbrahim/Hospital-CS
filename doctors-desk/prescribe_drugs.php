<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Read JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$appointment_id = intval($data['appointment_id'] ?? 0);
$patient_id     = intval($data['patient_id'] ?? 0);
$doctor_id      = getId();
$drugs          = $data['drugs'] ?? [];
$priority       = sanitize($data['priority']);
$delivery       = sanitize($data['delivery_option']);
$notes          = sanitize($data['notes']);

// Check appointment exists
$appt = $db->query("SELECT * FROM appointments WHERE id='$appointment_id' LIMIT 1")->fetch_assoc();
if (!$appt) {
    echo json_encode(['status'=>'error','message'=>'Appointment not found']);
    exit;
}

// Check doctor's active room (if doctor user type)
if($_SESSION['type'] == 3){
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

// Check stock
$insufficient = [];
foreach($drugs as $d){
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);
    $stock   = $db->query("SELECT quantity FROM pharmacy_stock WHERE drug='$drug_id'")->fetch_assoc()['quantity'] ?? 0;
    if ($qty > $stock) $insufficient[] = $drug_id;
}

if (!empty($insufficient)) {
    echo json_encode(['status'=>'error','message'=>'Some drugs are out of stock']);
    exit;
}

// Handle resubmission: delete old prescription and restore stock
$existingPD = $db->query("SELECT * FROM patient_drugs WHERE appointment_id='$appointment_id'  LIMIT 1")->fetch_assoc();
if ($existingPD) {

    $existingPatientDrugsId = $existingPD['id'];
    $existingPaymentId = $existingPD['payment_id'];

    $sql = "SELECT status FROM payments WHERE id = '$existingPaymentId'";
    $run = $db->query($sql);
    $info = $run->fetch_assoc();
    if($info['status'] == 1){
    echo json_encode(['status'=>'error','message'=>'payment already made']);
    exit;  
    }

    // Restore stock
    $oldDrugs = $db->query("SELECT drug_id, quantity FROM drug_list WHERE patient_drugs_id='$existingPatientDrugsId'");
    while($old = $oldDrugs->fetch_assoc()) {
        $db->query("UPDATE pharmacy_stock SET quantity = quantity + ".$old['quantity']." WHERE drug='".$old['drug_id']."'");
    }

    // Delete old records
    $db->query("DELETE FROM drug_list WHERE patient_drugs_id='$existingPatientDrugsId'");
    $db->query("DELETE FROM patient_drugs WHERE id='$existingPatientDrugsId'");
    $db->query("DELETE FROM payments WHERE id='$existingPaymentId' AND purpose = 2");
}

// Generate payment receipt
$amount_total = 0;
foreach($drugs as $d){
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);
    $price   = $db->query("SELECT selling_price FROM drugs WHERE id='$drug_id'")->fetch_assoc()['selling_price'];
    $amount_total += $price * $qty;
}

  $reciept_num = generateReceiptNumber($db);

  $sql = "SELECT scheme_type FROM users WHERE id = '$patient_id'";
  $run = $db->query($sql);
  $patientInfo = $run->fetch_assoc();
  $scheme_type = $patientInfo['scheme_type'];


  $sql = "SELECT * FROM schemes WHERE id = '$scheme_type' AND status = 1";
  $run = $db->query($sql);
  if($run->num_rows == 0){
    echo json_encode(['status'=>'error','message'=>'Scheme Type Not Found '.$patient_id]);
    exit;  
  
  }


  $schemeInfo = $run->fetch_assoc();
  $discount = $schemeInfo['discount_fee'];


$discount_amount = ($amount_total * $discount) / 100;
$net_amount = max(0, round($amount_total - $discount_amount, 2));

// Insert into payments
$db->query("
    INSERT INTO payments (patient_id, appointment_id, user_id, amount, discount, net_amount, purpose, record_date, status,reciept_num,note)
    VALUES ('$patient_id','$appointment_id','$doctor_id','$amount_total','$discount','$net_amount',2,NOW(),0,'$reciept_num','Drug Purchase Payment');
");
$payment_id = $db->insert_id;

// Insert into patient_drugs
$db->query("
    INSERT INTO patient_drugs (priority, notes, delivery_option, appointment_id, payment_id, user_id, status, created_date)
    VALUES ('$priority','$notes','$delivery','$appointment_id','$payment_id','$doctor_id',0,NOW())
");
$patient_drugs_id = $db->insert_id;

// Insert each drug into drug_list and deduct stock
foreach($drugs as $d){
    $drug_id = intval($d['id']);
    $qty     = intval($d['quantity']);
    $presc   = sanitize($d['prescription']);
    $price   = $db->query("SELECT selling_price FROM drugs WHERE id='$drug_id'")->fetch_assoc()['selling_price'];
    $amt     = $price * $qty;

    $db->query("
        INSERT INTO drug_list (drug_id, prescription, quantity, amount, patient_drugs_id)
        VALUES ('$drug_id','$presc','$qty','$amt','$patient_drugs_id')
    ");

    // Deduct stock
    $db->query("UPDATE pharmacy_stock SET quantity = quantity - $qty WHERE drug='$drug_id'");
}

echo json_encode(['status'=>'success','message'=>'Prescription sent to pharmacy successfully']);
exit;
?>
