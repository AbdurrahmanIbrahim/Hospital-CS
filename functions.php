<?php 
  session_start();


date_default_timezone_set("Africa/Lagos");                  # set your time zone (optional)
 

// 8 and 40 and 30  ini_set('session.gc_maxlifetime', 86400);                   # set how long (in secs, 86400=1day) you want you session to last, 
															#comment this off if you want to use default from server (optional)

# REQUIRED VALUES
######################################################################
#########     Please provide values for these varables     ###########
######################################################################

$db_host = 'localhost';
$db_username = 'root'; 
$db_password = '';
$db_name = 'hospital';


// $db_host = 'localhost';
// $db_username = 'fmcbirni_pay'; 
// $db_password = 'GeVpoL5qxxt4';
// $db_name = 'fmcbirni_payslips';

$db = new mysqli($db_host,$db_username,$db_password,$db_name);


// $db_host = 'localhost';
// $db_username = 'muhammadrec1fa'; 
// $db_password = '0c~T[BxDx2aP';
// $db_name = 'recifa';

# replace the values below with your values

# comment this part below when live 
define('ROOT', '/ibn_katheer/');    # use this for localhost
//define('WEB_ROOT', $_SERVER['PROJECT_ROOT'].'/');    # use this for localhost
define('ROOT_URL', 'http://localhost/hms/');   
define('APP_NAME', 'MediFlow Pro');

//define('ROOT_URL', 'https://www.fmcbirninkebbi.gov.ng/payslips/');  


 #192.168.190.53
 
# remove comment from this part below when live on server
// define('ROOT', ' /home/q6i7hg7vicom/public_html/recifa.com/');    		# use this when live on server
// define('WEB_ROOT', $_SERVER['PROJECT_ROOT'].'/');    # use this for localhost
// define('ROOT_URL', 'http://recifa.com/');    	        # use this when live on server



// session_set_cookie_params(2,592,000);                       # set how long (in secs, 86400=1day) you want you cookies to last, 
															#comment this off if you want to use PHP default or not using cookies at all (optional)	



If(ROOT === '' OR ROOT_URL ===''){
	echo '<p style="color:red;"> Please open your crib.php file and provide the required values</p>';
	return;
}

function getInvoiceNumber(){
    global $db;
    do{
       $today = date('Y-m-d');
       $sql = "SELECT id FROM invoice ORDER BY id DESC";
       $run = $db->query($sql);
       $info = $run->fetch_assoc();
       $row_count = !empty($info['id']) ? $info['id'] + 1 : 1;
       $row_count = get3digits($row_count);
       $invoice_number =  date('Ymd').$row_count;
       $check = "SELECT id FROM invoice WHERE invoice_number = '$invoice_number'";
       $run_check = $db->query($check);
    }while($run_check->num_rows > 0);
   return $invoice_number; 
}

function getLabNo($test_id){
      global $db;
    do{
       $sql = "SELECT id FROM patient_test WHERE test_id = '$test_id' ORDER BY id DESC";
      $run = $db->query($sql);
       $info = $run->fetch_assoc();
       $row_count = !empty($info['id']) ? $info['id'] + 1 : 1;
       $lab_no = get3digits($row_count);
       $check = "SELECT id FROM patient_test WHERE labno = '$lab_no'";
       $run_check = $db->query($check);
    }while($run_check->num_rows > 0);
   return $lab_no; 
}

function get3digits($row_count){
    if(strlen($row_count) == 1)
        $number = '00'.$row_count;
    else if(strlen($row_count) == 2)
        $number = '0'.$row_count;
    else
        $number = $row_count;
    return $number;
}



function isLoggedIn(){
	return isset($_SESSION['email']);
}

function get_specimen($s_id){
    global $db;
     $sql = "SELECT name FROM specimen WHERE id = '$s_id' AND status = 1";
     $run = $db->query($sql);
    $s_info = $run->fetch_assoc();
    $s_name = !empty($s_info['name']) ? $s_info['name'] : '';
    return $s_name;
}

// function get_status($status)
// {
//     if($status == '0')
//         $txt = 'Pending';
//     else if($status == '1')
//        $txt = 'Specimen collected waiting to be acknowledged'; 
//    else if($status == '2')
//        $txt = 'Specimen acknowledged waiting for result to be uploaded'; 
//   else if($status == '3')
//        $txt = 'result has been uploaded waiting to be verified';
//   else if($status == '-1'){
//         $txt = 'Specimen rejected'; 
//   }else if($status == '4'){
//     $txt = 'result has been verified';
//   }else if($status == '5'){
//     $txt = 'result has been unverified';
//   }else if($status == '6'){
//     $txt = 'result has been given to patient';
//   }

//        return $txt; 

// }

function get_tests($invoice_id){
    global $db;
    $tests = [];
    $sql = "SELECT * FROM patient_test WHERE invoice_id = '$invoice_id'";
    $run = $db->query($sql);
    while($row = $run->fetch_assoc())$tests[] = $row;
    return $tests;
}

function get_paid_tests($invoices)
{
    $paid = $not_paid = $amount = $paid_amount =   0;
    foreach($invoices as $inv){
        if($inv['paid'] == 0){
            $not_paid++;
        } else{
            $paid++;
            $paid_amount +=$inv['amount'];
        }

        $amount+=$inv['amount'];
    }
  if($paid == 0)
     $txt =  'No payment have been made for any test';
 else if($not_paid == 0)
     $txt =  'All payment has been made';
 else
    $txt = 'Payment for '.$paid.' test(s) has been made remaining '.$not_paid;

 return array('txt'=>$txt,'amount'=>$amount,"paid_amount"=>$paid_amount);
}

function getTestInfo($test_id){
     global $db;
     $sql = "SELECT tests.name,departments.name AS dept FROM tests INNER JOIN departments WHERE tests.id = '$test_id' AND departments.id = tests.department_id";
     $run = $db->query($sql);
     return $run->fetch_assoc();
}

function get_dept($s_id){
    global $db;
     $sql = "SELECT name FROM departments WHERE id = '$s_id' AND status = 1";
     $run = $db->query($sql);
    $s_info = $run->fetch_assoc();
    $s_name = !empty($s_info['name']) ? $s_info['name'] : '';
    return $s_name;
}



function random_characters($length, $special_chars=false){
        $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if($special_chars) $characters.="~!@#$%^&*()_+";
        $charactersLength = strlen($characters);

        $random_characters = "";
        for ($i = 0; $i < $length; $i++) {
            $random_characters .= $characters[rand(0, $charactersLength - 1)];
        }

        return $random_characters;
}


function send_mail($attachment_path,$recipient_email,$title) {
    // Email configurations
    $sender_email = "fmc@gmail.com.com";
 
    // Create a new PHPMailer instance
    $mail = new PHPMailer;

    // Set up SMTP if needed
    // $mail->isSMTP();
    // $mail->Host = 'smtp.example.com';
    // $mail->Port = 587;
    // $mail->SMTPAuth = true;
    // $mail->Username = 'your_email@example.com';
    // $mail->Password = 'your_email_password';

    // Compose the email
    $mail->setFrom($sender_email);
    $mail->addAddress($recipient_email);
    $mail->Subject = $title. 'Pay Slip';
    $mail->Body = 'Please find your attached pay slip for the month of '.$title;

    // Attach the PDF page to the email
    $mail->addAttachment($attachment_path);

    // Send the email
    $mail->send();
        
    
}


 function getId(){
     global $db;
     $email =!empty($_SESSION['email']) ? $_SESSION['email'] : ''; 
     $sql = "SELECT id FROM users WHERE email = '$email'";
     $run = $db->query($sql);
     $info = $run->fetch_assoc();
     return !empty($info['id']) ? $info['id'] : '';
  }

  function get_user_type(){
    return ($_SESSION['type'] == '0') ? 'Staff' : 'Admin';
  }

  function getTests($my_dept){
     global $db;
     $tests = [];
     $sql = "SELECT * FROM tests WHERE status = 1 AND department_id = '$my_dept'";
     $run = $db->query($sql);
     while($row = $run->fetch_assoc())$tests[] = $row;
     return $tests;

  }



  function get($column,$table,$id){
     global $db;
    $sql = "SELECT $column FROM $table WHERE id = '$id'";
    $run = $db->query($sql);
    $info = $run->fetch_assoc();
    return (!empty($info[$column])) ? $info[$column] : '';
  }


  function getDepartments(){
       global $db;
       $departments = [];
       $sql = "SELECT * FROM departments WHERE status = 1 AND category = 1 ORDER BY name ASC";
       $run = $db->query($sql);
       while($row = $run->fetch_assoc())$departments[] = $row;
       return $departments;
  }


  function getAllDeptTests($department_id){
    $tests = [];
    global $db;
    $sql = "SELECT * FROM tests WHERE department_id = '$department_id' AND  status = 1 ORDER BY name ASC";
    $run = $db->query($sql);
   while($row = $run->fetch_assoc())$tests[] = $row;

    return $tests;
  }


function cleanInput($input){
 
  $search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
  );
 
    $output = preg_replace($search, '', $input);
    return htmlentities(strip_tags($output));
  }


   function sanitize(&$input) { 
    if (is_array($input)) {
        foreach($input as $var=>$val) {
            $input[$var] = sanitize($val);
        }
    }
    else {
        // if (get_magic_quotes_gpc()) {
        //     $input = stripslashes($input);
        // }
        $input  = cleanInput($input);
        $input = $GLOBALS['db']->real_escape_string($input);
    }

    return $input;
   }


  function calculateAge($birthDate) {
     if($birthDate=='')
        return '';
    else{
           $birthDate = new DateTime($birthDate);
            $currentDate = new DateTime();
            $interval = $currentDate->diff($birthDate);
            
            $years = $interval->y;
            $months = $interval->m;

    return "$years years, $months months"; 
    }
}

   $user_id = getId();



   function getTypes(){
     return   array('2'=>'Lab Technician','3'=>'Doctors','4'=>'Nurses','5'=>'Reception','6'=>'Pharmacist','7'=>'Accountant','8'=>'Store Keeper','9'=>'Radiologist');
   }

   function getUserType($type){

     if($type == '0'){
        return 'Administrator';
    }else{
        $types = getTypes();
        return (!empty($types[$type])) ? $types[$type] : 'Nill';
    }


   }



   function getDoctorsDetails($room_id){
      global $db;

      $sql = "SELECT assign_doctors.*,users.name FROM assign_doctors INNER JOIN users WHERE room_id = '$room_id' AND assign_doctors.doctor = users.id";
      $run = $db->query($sql);
      $info = $run->fetch_assoc();
      return !(empty($info['name'])) ? array("name"=>$info['name'],"id"=>$info['doctor']) : array("name"=>"No Doctor","id"=>"0");
   }


   function getAge($dob)
{
    if (empty($dob)) {
        return '-';
    }

    try {
        $birthDate = new DateTime($dob);
        $today     = new DateTime('today');
        return $birthDate->diff($today)->y;
    } catch (Exception $e) {
        return '-';
    }
}


function generateReceiptNumber($db) {
    do {
        $receipt = 'RCT-' . date('Ymd') . '-' . rand(100000, 999999);
        $check = $db->query("SELECT id FROM payments WHERE reciept_num = '$receipt'");
    } while ($check->num_rows > 0);

    return $receipt;
}


function getReciept($id){
    global $db;

    $sql = "SELECT * FROM payments WHERE patient_id = '$id' AND purpose = 1";
    $run = $db->query($sql);
    $info = $run->fetch_assoc();
    return !empty($info['reciept_num']) ? $info['reciept_num'] : '';

}

function formatDateReadable($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateReadableWithTime($date) {
      return date('jS F Y, g:i A', strtotime($date));
}


function getPurpose($purpose){

    if($purpose == 1)return 'Form Purchase';
    else  if($purpose == 2)return 'For Drugs';
     else  if($purpose == 3)return 'For Laboratory';
     else  if($purpose == 4)return 'For Admission';
     else  if($purpose == 5)return 'For Radiology';
     else  if($purpose == 6)return 'Consultation Fee';
     else return 'Unknown';
}


function getNumberOfPatient($room_id){
  global $db;
$sql = "SELECT COUNT(*) AS total_patients FROM appointments WHERE room_id = '$room_id' AND status = 1";
$run = $db->query($sql);
$info = $run->fetch_assoc();
return !empty($info['total_patients']) ? $info['total_patients'] : 0;

}


function shortName($name) {
    $name = trim($name);

    if (empty($name)) return '';

    $parts = preg_split('/\s+/', $name);
    $initials = '';

    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    return $initials;
}

function get_purpose($status){
  if($status == 1)return 'Form Purchase';
  else  if($status == 2)return 'Drugs Purchase';
  else  if($status == 3)return 'Laboratory';
  else  if($status == 4)return 'Admission';
  else  if($status == 5)return 'Radiology';
  else  if($status == 6)return 'Consultation';
}

function get_payment_status_badge($status){
    if ($status == 0) {
        return '<span class="badge-small badge-warning">Pending</span>';
    } elseif ($status == 1) {
        return '<span class="badge-small badge-success">Paid</span>';
    } elseif ($status == -1) {
        return '<span class="badge-small badge-danger">Rejected</span>';
    } else {
        return '<span class="badge-small badge-secondary">Unknown</span>';
    }
}

function get_status($status)
{
    if($status == '1')
        $txt = 'Pending';
    else if($status == '2')
       $txt = 'Specimen collected waiting to be acknowledged'; 
   else if($status == '3')
       $txt = 'Specimen acknowledged waiting for result to be uploaded'; 
  else if($status == '4')
       $txt = 'result has been uploaded waiting to be verified';
  else if($status == '-1'){
        $txt = 'Specimen rejected'; 
  }else if($status == '5'){
    $txt = 'result has been verified';
  }else if($status == '6'){
    $txt = 'result has been unverified';
  }else if($status == '7'){
    $txt = 'result has been given to patient';
  }

       return $txt;

}


// ===================== RADIOLOGY FUNCTIONS =====================

function get_scan_status($status){
    if($status == '0') $txt = 'Ordered - Awaiting Payment';
    else if($status == '1') $txt = 'Paid - Pending Scan';
    else if($status == '2') $txt = 'Scan Performed';
    else if($status == '3') $txt = 'Report Uploaded';
    else if($status == '4') $txt = 'Report Verified';
    else if($status == '5') $txt = 'Released to Patient';
    else $txt = 'Unknown';
    return $txt;
}

function get_scan_status_badge($status){
    if($status == 0) return '<span class="badge-small badge-warning">Pending</span>';
    else if($status == 1) return '<span class="badge-small badge-info">Awaiting Scan</span>';
    else if($status == 2) return '<span class="badge-small badge-warning">Awaiting Report</span>';
    else if($status == 3) return '<span class="badge-small badge-info">Awaiting Verification</span>';
    else if($status == 4) return '<span class="badge-small badge-success">Verified</span>';
    else if($status == 5) return '<span class="badge-small badge-success">Released</span>';
    else return '<span class="badge-small badge-secondary">Unknown</span>';
}

// ===================== ADMISSION FUNCTIONS =====================

function getActiveAdmission($patient_id){
    global $db;
    $patient_id = intval($patient_id);
    $sql = "SELECT * FROM admissions WHERE patient_id = '$patient_id' AND status = 0 LIMIT 1";
    $run = $db->query($sql);
    return ($run && $run->num_rows > 0) ? $run->fetch_assoc() : null;
}

function getAvailableBeds($room_id){
    global $db;
    $room_id = intval($room_id);
    $room = $db->query("SELECT bed_space FROM rooms WHERE id = '$room_id' AND room_type = 1 AND status = 1");
    if(!$room || $room->num_rows == 0) return 0;
    $room_info = $room->fetch_assoc();
    $occupied = $db->query("SELECT COUNT(*) AS cnt FROM admissions WHERE room_id = '$room_id' AND status = 0");
    $occ_info = $occupied->fetch_assoc();
    return max(0, $room_info['bed_space'] - ($occ_info['cnt'] ?? 0));
}

function getAdmissionTotal($admission_id){
    global $db;
    $admission_id = intval($admission_id);
    $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM admission_billing WHERE admission_id = '$admission_id'";
    $run = $db->query($sql);
    $info = $run->fetch_assoc();
    return $info['total'];
}

function processRoomBilling($admission_id){
    global $db;
    $admission_id = intval($admission_id);
    $admission = $db->query("SELECT * FROM admissions WHERE id = '$admission_id' AND status = 0");
    if(!$admission || $admission->num_rows == 0) return;
    $admission = $admission->fetch_assoc();

    $room = $db->query("SELECT room_price FROM rooms WHERE id = '".$admission['room_id']."'");
    if(!$room || $room->num_rows == 0) return;
    $room = $room->fetch_assoc();

    $last_billed = new DateTime($admission['last_billed_at']);
    $now = new DateTime();
    $diff = $now->diff($last_billed);
    $hours = ($diff->days * 24) + $diff->h;
    $days_to_bill = floor($hours / 24);

    if($days_to_bill > 0){
        $price_per_day = $room['room_price'];
        for($i = 0; $i < $days_to_bill; $i++){
            $bill_date = clone $last_billed;
            $bill_date->modify('+' . ($i + 1) . ' days');
            $desc = $db->real_escape_string('Room stay charge - ' . $bill_date->format('d M Y'));
            $db->query("
                INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
                VALUES ('$admission_id', '$desc', '$price_per_day', 1, 0, '".$bill_date->format('Y-m-d H:i:s')."')
            ");
        }
        $new_last_billed = clone $last_billed;
        $new_last_billed->modify('+' . $days_to_bill . ' days');
        $db->query("UPDATE admissions SET last_billed_at = '".$new_last_billed->format('Y-m-d H:i:s')."' WHERE id = '$admission_id'");
    }
}






?>