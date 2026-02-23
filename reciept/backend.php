<?php
include '../functions.php';

if (!isLoggedIn() || !in_array($_SESSION['type'], [0, 2, 5, 6, 7, 9])) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'payments';
$user_id = getId();
$user_name = get('name','users',$user_id);
if(empty($_GET['id'])){

    $_SESSION['error'] = 'An Error Occured';
    echo "<script>window.history.back()</script>";
    exit;

}

$payment_id = sanitize($_GET['id']);
$sql = "SELECT * FROM payments WHERE id = '$payment_id'";
$run = $db->query($sql);
if($run->num_rows == 0){
     $_SESSION['error'] = 'An Error Occured';
    echo "<script>window.history.back()</script>";
    exit; 
}
$payment = $run->fetch_assoc();
$purpose = $payment['purpose'];
$patient_id = $payment['patient_id'];

// 
$sql = "SELECT * FROM hospital_details";
$run = $db->query($sql);
if($run->num_rows == 0){
     $_SESSION['error'] = 'An Error Occured';
    echo "<script>window.history.back()</script>";
    exit; 
}
$hospital = $run->fetch_assoc();

$items = [];
$subtotal = 0;
$total_amount = 0;



/* ---- DRUG ITEMS ---- */
if($purpose == 2){
  
    $drugQ = $db->query("
    SELECT 
        d.drug_name AS name,
        'Drug' AS type,
        dl.amount AS price,
        dl.quantity 
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





}else if($purpose == 1){
    $fileQ = $db->query( "SELECT
        ft.name AS name,
        'File' AS type,
        ft.amount AS price,
        1 AS quantity
    FROM payments p
    JOIN users u ON p.patient_id = u.id
    JOIN file_types ft ON u.file_type = ft.id
    WHERE u.id = '$patient_id'");



    while ($row = $fileQ->fetch_assoc()) {
    $items[] = $row;
    $total_amount += (float)$row['price'];
}


}else if($purpose == 4){
    // Admission billing item (individual per-item payment)
    $billingQ = $db->query("
        SELECT description AS name,
               CASE billing_type WHEN 1 THEN 'Room Stay' WHEN 2 THEN 'Drug' ELSE 'Other' END AS type,
               amount AS price,
               1 AS quantity
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
}else if($purpose == 5){
    // Radiology scan items
    $scanQ = $db->query("
        SELECT s.name,
               'Radiology Scan' AS type,
               sl.amount AS price,
               1 AS quantity
        FROM patient_scan ps
        JOIN scan_lists sl ON sl.patient_scan_id = ps.id
        JOIN scans s ON s.id = sl.scan_id
        WHERE ps.payment_id = '$payment_id'
    ");
    if($scanQ){
        while ($row = $scanQ->fetch_assoc()) {
            $items[] = $row;
            $total_amount += (float)$row['price'];
        }
    }
}else if($purpose == 6){
    // Consultation fee
    $items[] = [
        'name' => 'Consultation Fee',
        'type' => 'Consultation',
        'price' => $payment['amount'],
        'quantity' => 1
    ];
    $total_amount = (float)$payment['amount'];
}




?>