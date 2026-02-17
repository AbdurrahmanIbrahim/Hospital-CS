<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}





/* =========================
   SANITIZE INPUTS
========================= */
$name       = sanitize($_POST['name']);
$email      = sanitize($_POST['email']);
$phone      = sanitize($_POST['phone']);
$dob        = sanitize($_POST['dob']);
$gender     = sanitize($_POST['gender']);
$address    = sanitize($_POST['address']);
$state    = sanitize($_POST['state']);
$lga    = sanitize($_POST['lga']);
$tribe    = sanitize($_POST['tribe']);
$blood      = sanitize($_POST['blood_group']);
$genotype   = sanitize($_POST['genotype']);
$allergies  = sanitize($_POST['allergies']);
$chronic    = sanitize($_POST['chronic_conditions']);
$kin        = sanitize($_POST['next_of_kin']);
$kin_phone  = sanitize($_POST['next_of_kin_phone']);
$relationship = sanitize($_POST['relationship']);
$marital_status = sanitize($_POST['marital_status']);
$type       = 1; // patient
$status     = 0;
$discount = $amount = 0;

$file_type = !empty($_POST['file_type']) ? sanitize($_POST['file_type']) : 0;
$scheme_type =  !empty($_POST['scheme_type']) ? sanitize($_POST['scheme_type']) : 0;
$family =  !empty($_POST['family']) ? sanitize($_POST['family']) : 0;

if(empty($file_type)){
    $_SESSION['error'] = 'Select Valid File Type';
    echo "<script>window.history.back()</script>";
    exit; 
}
  
  $sql = "SELECT * FROM file_types WHERE id = '$file_type' AND status = 1";
  $run = $db->query($sql);
  if($run->num_rows == 0){
    $_SESSION['error'] = 'File Type Not Found';
    echo "<script>window.history.back()</script>";
    exit;
  }

  $fileInfo = $run->fetch_assoc();
  $amount = $fileInfo['amount'];




if(empty($scheme_type)){
    $_SESSION['error'] = 'Select Valid Scheme Type';
    echo "<script>window.history.back()</script>";
    exit; 
}
  
  $sql = "SELECT * FROM schemes WHERE id = '$scheme_type' AND status = 1";
  $run = $db->query($sql);
  if($run->num_rows == 0){
    $_SESSION['error'] = 'Scheme Type Not Found';
    echo "<script>window.history.back()</script>";
    exit;
  }


  $schemeInfo = $run->fetch_assoc();
  $discount = $schemeInfo['discount_fee'];


$discount_amount = ($amount * $discount) / 100;
$net_amount = max(0, round($amount - $discount_amount, 2));





if(!empty($family)){
  
  $sql = "SELECT id FROM families WHERE id = '$family' AND status = 1";
  $run = $db->query($sql);
  if($run->num_rows == 0){
    $_SESSION['error'] = 'Family Not Found';
    echo "<script>window.history.back()</script>";
    exit;
  }

}




$user_id = getId();

/* =========================
   MARITAL STATUS MAPPING
========================= */
$maritalMap = [
    'Single'   => 0,
    'Married'  => 1,
    'Divorced' => 2
];


/* =========================
   AUTO GENERATE HOSPITAL NO
========================= */
function generateHospitalNumber($db) {
    do {
        $num = rand(100000, 999999);
        $check = $db->query("SELECT id FROM users WHERE hospital_num='$num'");
    } while ($check->num_rows > 0);

    return $num;
}

/* =========================
   UPDATE PATIENT
========================= */
if (!empty($_POST['id'])) {

    $id = intval($_POST['id']);

    $sql = "UPDATE users SET
        name='$name',
        email='$email',
        phone='$phone',
        dob='$dob',
        gender='$gender',
        address='$address',
        state = '$state',
        lga = '$lga',
        tribe = '$tribe',
        marital_status='$marital_status',
        blood_group='$blood',
        genotype='$genotype',
        allergies='$allergies',
        chronic_condition='$chronic',
        kin='$kin',
        kin_phone='$kin_phone',
        kin_relationship='$relationship',
        file_type = '$file_type',
        family = '$family'
        WHERE id='$id' AND type=1
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Patient Updated Successfully';
    } else {
        $_SESSION['error'] = 'Failed to Update Patient';
        echo "<script>window.history.back()</script>";
        exit;
    }

    header("Location: view.php");
    exit;
}

/* =========================
   ADD NEW PATIENT
========================= */

// Check duplicate email
$check = $db->query("SELECT id FROM users WHERE email='$email'");
if ($check->num_rows > 0) {
    $_SESSION['error'] = 'Email already exists';
    echo "<script>window.history.back()</script>";
    exit;
}

// Generate hospital number
$hospital_num = generateHospitalNumber($db);

// Default password (you can change logic later)
$password = password_hash('patient123', PASSWORD_DEFAULT);

$sql = "INSERT INTO users (
    name, email, phone, dob, gender, address,
    state,lga,tribe,
    marital_status, blood_group, genotype,
    allergies, chronic_condition,
    kin, kin_phone, kin_relationship,
    hospital_num, password, type, status,user_id,
    file_type,scheme_type,family_id
) VALUES (
    '$name', '$email', '$phone', '$dob', '$gender', '$address',
    '$state','$lga','$tribe',
    '$marital_status', '$blood', '$genotype',
    '$allergies', '$chronic',
    '$kin', '$kin_phone', '$relationship',
    '$hospital_num', '$password', '$type', '$status','$user_id',
    '$file_type','$scheme_type','$family'
)";


if ($db->query($sql)) {

    // Get newly inserted patient ID
    $patient_id = $db->insert_id;

    // Generate receipt number
    $receipt_num = generateReceiptNumber($db);

    // Calculate amounts
    $discount_amount = ($discount > 0) ? ($amount * $discount / 100) : 0;
    $net_amount = $amount - $discount_amount;

    // Insert into payments table
    $payment_sql = "INSERT INTO payments (
        patient_id,
        appointment_id,
        accountant_id,
        user_id,
        amount,
        discount,
        net_amount,
        `payment-method`,
        record_date,
        purpose,
        note,
        reciept_num,
        status
    ) VALUES (
        '$patient_id',
        0,
        0,
        '$user_id',
        '$amount',
        '$discount',
        '$net_amount',
        '',
        NOW(),
        1,
        'Patient registration payment',
        '$receipt_num',
        0
    )";

    if(!$db->query($payment_sql)){
        echo $db->error;
        exit;
    }

    $_SESSION['success'] = 'Patient Added Successfully. Receipt No: ' . $receipt_num;

} else {
    echo $db->error;
    $_SESSION['error'] = 'Failed to Add Patient';
    echo "<script>window.history.back()</script>";
    exit;
}


header("Location: view.php");
exit;
