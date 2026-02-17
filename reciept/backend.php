<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 7)) {
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


}






?>