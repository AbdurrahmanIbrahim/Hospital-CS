<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

 if(isset($_GET['id'])){
     $patient_test_id = filter_var(sanitize($_GET['id']),FILTER_VALIDATE_INT);
     $patient_test_id = $patient_test_id - 1200;

     
     $sql = "SELECT * FROM test_lists WHERE id = '$patient_test_id' AND (status = 5 or status = 7) AND paid = 1";
     $run = $db->query($sql);
     if($run->num_rows == 0){
      $_SESSION['error'] = 'This test does not exist';
      echo "<script>window.history.back()</script>";
      exit();
    }
   
    $test_info  = $run->fetch_assoc();
    
    $test_id = $test_info['test_id'];
    $patient_test = $test_info['patient_test_id'];
    $labno = $test_info['labno'];
    $hos_no = $test_info['hos_no'];
    $date_request = $test_info['date_request'];
      $date_verified = $test_info['verified_date'];
    $date_release = $test_info['date_of_result'];
    $notes = $test_info['notes'];
    $release_officer = $test_info['result_releaser_id'];
    $verify_officer = $test_info['verifier'];
    $test_status = $test_info['status'];
    $date = '';

    $sql = "
    SELECT a.patient_id
    FROM patient_test pt
    JOIN appointments a ON pt.appointment_id = a.id
    WHERE pt.id = '$patient_test'
    LIMIT 1
";

$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Patient record not found';
    header('Location: tests.php');
    exit;
}

$row = $run->fetch_assoc();
$patient_id = $row['patient_id'];
$dr_name = $test_info['dr_name'] ?? get("name","users",$row['doctor_id']);




    if(!empty($date_request)){
            $date= date_create($date_request);
            $date= date_format($date,'d M Y'); 
    }

      if(!empty($date_release)){
            $date_release= date_create($date_release);
            $date_release= date_format($date_release,'d M Y'); 
    }

    if(!empty($date_verified)){
            $date_verified= date_create($date_verified);
            $date_verified= date_format($date_verified,'d M Y'); 
    }






    $sql = "SELECT * FROM tests WHERE id = '$test_id' AND status = 1";
     $run = $db->query($sql);
     if($run->num_rows == 0){
      $_SESSION['error'] = 'This test does not exist';
      echo "<script>window.history.back()</script>";
      exit();

    }

    $test_info  = $run->fetch_assoc();
    
   

    $parameters = [];
    $sql = "SELECT * FROM result_parameters WHERE test_id = '$test_id' AND status = 1";
    $run = $db->query($sql);
    while($row = $run->fetch_assoc())$parameters[] = $row;

    $results = [];
    $sql = "SELECT * FROM results WHERE patient_test_id = '$patient_test_id'";
    $run = $db->query($sql);
    while($row = $run->fetch_assoc()){
        $parameter_id = $row['parameter_id'] + 1200;
        $results['parameter_'.$parameter_id] = $row['answer'];
    }  


  }else{
       $_SESSION['error'] = 'An Error Ovccured';
       echo "<script>window.history.back()</script>";
      exit();
  }

  // 
$sql = "SELECT * FROM hospital_details";
$run = $db->query($sql);
if($run->num_rows == 0){
     $_SESSION['error'] = 'An Error Occured';
    echo "<script>window.history.back()</script>";
    exit; 
}
$hospital = $run->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Payments</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>

    .btn-primary {
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.status-active { color:#0a7a0a; font-weight:600; }
.status-inactive { color:#c0392b; font-weight:600; }
.table-actions a { margin-right:8px; }
   .status-active {
            color: #0a7a0a;
            font-weight: 600;
        }
        .status-inactive {
            color: #c0392b;
            font-weight: 600;
        }
        .table-actions a {
            margin-right: 8px;
        }

        /* No staff message styling */
.card-body > p {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    font-size: 16px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
    margin: 20px 0;
}

/* Edit button styling */
.table-actions a[href*="index.php"],.modal_btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #28a745;
}

.table-actions a[href*="index.php"]:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal_btn{
    background-color: blue;
}

/* Delete button styling */
.table-actions .delete-btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #dc3545;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #dc3545;
}

.table-actions .delete-btn:hover {
    background-color: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

    .search-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .search-form {
        width: 100%;
    }
    
    .search-filters {
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .search-input-wrapper {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .search-btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    .reset-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .search-filters {
            flex-direction: column;
            gap: 16px;
        }
        
        .filter-group {
            margin-left: 0 !important;
            width: 100%;
        }
        
        .filter-group:first-child {
            width: 100%;
        }
        
        #staffType {
            width: 100% !important;
        }
        
        .search-input-wrapper {
            flex-direction: column;
        }
        
        #searchQuery {
            border-radius: 6px !important;
            border-right: 1px solid #ddd !important;
            margin-bottom: 8px;
        }
        
        .search-btn {
            border-radius: 6px !important;
            width: 100%;
        }
        
        .reset-btn {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Focus states */
    #staffType:focus,
    #searchQuery:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
    
    /* Placeholder styling */
    #searchQuery::placeholder {
        color: #6c757d;
        opacity: 0.8;
    }

      .table-responsive {
    width: 100%;
    overflow-x: auto; /* Enables horizontal scroll on small screens */
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
}

.table-responsive table {
    min-width: 1000px; /* Optional: forces table to be wide enough */
    border-collapse: collapse;
}

.action-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.action-links a {
    display: inline-block;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border-radius: 6px;
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.action-links a:hover {
    background: #e2e8f0;
    color: #020617;
}

/* Optional: status-based colors */
.action-links a.acknowledge {
    background: #e0f2fe;
    color: #0369a1;
    border-color: #bae6fd;
}

.action-links a.prepare {
    background: #ecfeff;
    color: #0f766e;
    border-color: #99f6e4;
}

.action-links a.verify {
    background: #fef9c3;
    color: #854d0e;
    border-color: #fde68a;
}

.action-links a.print {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}






</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">
    <style>
:root {
    --hospital-blue: #0066cc;
    --hospital-light-blue: #e6f2ff;
    --hospital-dark-blue: #004080;
    --medical-green: #00a86b;
    --medical-red: #d32f2f;
    --medical-gold: #ffb300;
    --lab-gray: #f5f5f5;
    --border-gray: #e0e0e0;
    --text-dark: #2c3e50;
}

/* Lab Report Card */
.lab-report-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 102, 204, 0.15);
    overflow: hidden;
    border: 1px solid var(--border-gray);
    max-width: 1200px;
    margin: 30px auto;
}

/* Lab Header - Official Seal Style */
.lab-header {
    background: linear-gradient(135deg, var(--hospital-blue) 0%, var(--hospital-dark-blue) 100%);
    color: white;
    padding: 25px 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.lab-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
    opacity: 0.1;
    animation: movePattern 20s linear infinite;
}

@keyframes movePattern {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(20px, 20px) rotate(360deg); }
}

.lab-logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}

.lab-logo {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid white;
    padding: 5px;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.lab-logo:hover {
    transform: scale(1.05);
}

.lab-title-container {
    margin-left: 25px;
    text-align: left;
}

.lab-title-main {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
}

.lab-subtitle {
    font-size: 14px;
    opacity: 0.95;
    margin-bottom: 3px;
    font-weight: 300;
}

.lab-contact {
    font-size: 15px;
    font-weight: 600;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.lab-contact::before {
    content: "📞";
    font-size: 16px;
}

.lab-report-title {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 2;
}

.lab-report-title h4 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    letter-spacing: 1px;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Patient Information - Medical Form Style */
.patient-info-section {
    padding: 30px;
    background: var(--hospital-light-blue);
    border-bottom: 3px solid var(--hospital-blue);
}

.medical-form-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.1);
}

.medical-form-table th {
    background: var(--hospital-blue);
    color: white;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-right: 1px solid rgba(255,255,255,0.2);
    min-width: 140px;
}

.medical-form-table th:last-child {
    border-right: none;
}

.medical-form-table td {
    padding: 16px 12px;
    background: white;
    border-bottom: 1px solid var(--border-gray);
    font-weight: 500;
    color: var(--text-dark);
    border-right: 1px solid var(--border-gray);
}

.medical-form-table tr:last-child td {
    border-bottom: none;
}

.medical-form-table td:last-child {
    border-right: none;
}

.medical-form-table tr:nth-child(even) td {
    background: #fafafa;
}

/* Result Section */
.result-section {
    padding: 30px;
    background: white;
}

.result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px double var(--hospital-blue);
}

.result-header h5 {
    font-size: 20px;
    font-weight: 700;
    color: var(--hospital-dark-blue);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-header h5::before {
    content: "🔬";
    font-size: 24px;
}

.result-meta {
    display: flex;
    gap: 20px;
}

.result-meta-badge {
    background: var(--hospital-light-blue);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--hospital-dark-blue);
    display: flex;
    align-items: center;
    gap: 6px;
}

.result-meta-badge::before {
    content: "📊";
    font-size: 14px;
}

/* Test Results Tables */
.results-table-container {
    margin: 25px 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}

.results-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.results-table thead {
    background: linear-gradient(135deg, var(--hospital-blue) 0%, var(--hospital-dark-blue) 100%);
}

.results-table th {
    padding: 18px 15px;
   /* color: white;*/
    font-weight: 600;
    text-align: left;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
}

.results-table th::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: rgba(255,255,255,0.3);
}

.results-table th:last-child::after {
    display: none;
}

.results-table td {
    padding: 16px 15px;
    border-bottom: 1px solid var(--border-gray);
    color: var(--text-dark);
    font-weight: 500;
}

.results-table tbody tr {
    transition: background-color 0.2s ease;
}

.results-table tbody tr:hover {
    background: var(--hospital-light-blue);
}

.results-table .parameter-name {
    font-weight: 600;
    color: var(--hospital-dark-blue);
}

.results-table .parameter-result {
    font-weight: 700;
    color: var(--medical-green);
    position: relative;
}

.results-table .parameter-result::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--medical-green);
    opacity: 0.3;
}

/* Antibody Table Specific */
.antibody-table {
    background: #fff8e1;
    border: 2px solid var(--medical-gold);
}

.antibody-table thead {
    background: var(--medical-gold);
}

.antibody-table th {
    color: #5d4037;
}

.antibody-table .antigen-cell {
    background: #fffde7;
    font-weight: 600;
    color: #5d4037;
}

/* Text Results */
.text-result-item {
    margin-bottom: 20px;
    padding: 20px;
    background: var(--lab-gray);
    border-radius: 8px;
    border-left: 4px solid var(--hospital-blue);
}

.text-result-title {
    font-weight: 700;
    color: var(--hospital-dark-blue);
    margin-bottom: 12px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.text-result-title::before {
    content: "📝";
    font-size: 18px;
}

.text-result-content {
    background: white;
    padding: 18px;
    border-radius: 6px;
    border: 1px solid var(--border-gray);
    font-family: 'Courier New', monospace;
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Notes Section */
.notes-section {
    background: #fff3e0;
    border-radius: 8px;
    padding: 25px;
    margin: 30px 0;
    border: 2px dashed #ffb74d;
}

.notes-title {
    font-weight: 700;
    color: #e65100;
    margin-bottom: 15px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notes-title::before {
    content: "📌";
    font-size: 18px;
}

.notes-content {
    background: white;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #ffcc80;
    font-style: italic;
    color: #5d4037;
    line-height: 1.6;
    min-height: 80px;
}

/* Disclaimer */
.disclaimer {
    text-align: center;
    padding: 20px;
    margin: 30px 0;
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    border-radius: 8px;
    border: 2px solid var(--medical-red);
    position: relative;
    overflow: hidden;
}

.disclaimer::before {
    content: "⚠️";
    position: absolute;
    top: 50%;
    left: 30px;
    transform: translateY(-50%);
    font-size: 32px;
    opacity: 0.3;
}

.disclaimer p {
    font-weight: 800;
    color: #b71c1c;
    font-size: 16px;
    letter-spacing: 0.5px;
    margin: 0;
    padding-left: 50px;
    padding-right: 50px;
}

/* Print Button */
.print-controls {
    text-align: center;
    padding: 25px;
    background: var(--lab-gray);
    border-top: 1px solid var(--border-gray);
}

.print-btn {
    background: linear-gradient(135deg, var(--hospital-blue) 0%, var(--hospital-dark-blue) 100%);
    color: white;
    border: none;
    padding: 16px 45px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.print-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 102, 204, 0.4);
    background: linear-gradient(135deg, var(--hospital-dark-blue) 0%, var(--hospital-blue) 100%);
}

.print-btn::before {
    content: "🖨️";
    font-size: 20px;
}

/* Print Styles */
@media print {
    .lab-report-card {
        box-shadow: none;
        border: 1px solid #000;
        margin: 0;
        max-width: 100%;
    }
    
    .print-btn {
        display: none;
    }
    
    .lab-header {
        background: white !important;
        color: black !important;
        border-bottom: 3px solid black;
    }
    
    .lab-header::before {
        display: none;
    }
    
    .lab-logo {
        border: 2px solid black;
        filter: grayscale(100%);
    }
    
    .lab-report-title {
        background: white !important;
        border: 2px solid black;
    }
    
    .lab-report-title h4 {
        color: black !important;
        text-shadow: none !important;
    }
    
    .medical-form-table th {
        background: #f0f0f0 !important;
        color: black !important;
        -webkit-print-color-adjust: exact;
    }
    
    .results-table thead {
        background: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
    }
    
    .results-table th {
        color: black !important;
    }
    
    .parameter-result {
        color: black !important;
    }
    
    .disclaimer {
        border: 2px solid black !important;
        background: white !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .lab-header {
        padding: 20px;
    }
    
    .lab-logo-container {
        flex-direction: column;
        text-align: center;
    }
    
    .lab-title-container {
        margin-left: 0;
        margin-top: 15px;
        text-align: center;
    }
    
    .patient-info-section {
        padding: 15px;
    }
    
    .medical-form-table {
        display: block;
        overflow-x: auto;
    }
    
    .result-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .results-table {
        font-size: 14px;
    }
    
    .disclaimer::before {
        display: none;
    }
    
    .disclaimer p {
        padding-left: 15px;
        padding-right: 15px;
    }
}

@media print {
    .no-print {
        display: none !important;
        visibility: hidden !important;
    }
}

</style>

<div class="lab-report-card">

    <!-- LAB HEADER -->
    <div class="lab-header">
        <div class="lab-logo-container">
            <img src="<?= ROOT_URL ?>images/logos/<?=$hospital['logo']?>" class="lab-logo">
            <div class="lab-title-container">
                <h1 class="lab-title-main">
                   <?=$hospital['name']?>
                </h1>
                <div class="lab-subtitle"><?=$hospital['address']?></div>
                <div class="lab-subtitle">BIRNIN KEBBI, KEBBI STATE</div>
                <div class="lab-contact">
                    BN 3264413 · TEL: <?=$hospital['phone']?>
                </div>
            </div>
        </div>

        <div class="lab-report-title">
            <h4>General Request / Report Form</h4>
        </div>
    </div>

    <!-- PATIENT INFO -->
    <div class="patient-info-section">
        <table class="medical-form-table">
            <tr>
                <th>Name</th>
                <td><?= get('name','users',$patient_id) ?></td>
                <th>GSM</th>
                <td><?= get('phone','users',$patient_id) ?></td>
                <th>Sex / Age</th>
                <td>
                    <?= get('gender','users',$patient_id) == '1' ? 'Male' : 'Female' ?>
                    / <?= get('age','users',$patient_id) ?>
                </td>
            </tr>

            <tr>
                <th>Hospital No</th>
                <td><?=  get('hospital_num','users',$patient_id)  ?></td>
                <th>Lab No</th>
                <td><?= $labno ?></td>
                <th>Doctor</th>
                <td><?= $dr_name ?></td>
            </tr>

            <tr>
                <th>Test Name</th>
                <td><?= $test_info['name'] ?></td>
                <th>Specimen</th>
                <td><?= get_specimen($test_info['specimen']) ?></td>
                <th>Department</th>
                <td>Laboratory</td>
            </tr>

            <tr>
                <th>Date Received</th>
                <td><?= $date ?></td>
                <th>Date Dispatched</th>
                <td><?= $date_release ?></td>
                <th>Date Verified</th>
                <td><?= $date_verified ?></td>
            </tr>

            <tr>
                <th>Prepared By</th>
                <td><?= get('name','users',$release_officer) ?></td>
                <th>Verified By</th>
                <td colspan="3"><?= get('name','users',$verify_officer) ?></td>
            </tr>
        </table>
    </div>

    <!-- RESULT -->
    <div class="result-section">
        <div class="result-header">
            <h5>Test Results</h5>
            <div class="result-meta">
                <span class="result-meta-badge">Test: <?= $test_info['name'] ?></span>
                <span class="result-meta-badge">Lab No: <?= $labno ?></span>
            </div>
        </div>

        <?php if ($test_info['type'] == 1): ?>
            <div class="results-table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Parameter</th>
                            <th>Result</th>
                            <th>Unit</th>
                            <th>Reference Range</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn=1; foreach($parameters as $parameter): ?>
                            <tr>
                                <td><?= $sn++ ?></td>
                                <td class="parameter-name"><?= $parameter['name'] ?></td>
                                <td class="parameter-result"><?= $results['parameter_'.($parameter['id']+1200)] ?? '' ?></td>
                                <td><?= $parameter['unit'] ?></td>
                                <td><?= $parameter['para_range'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($test_info['type'] == 2): ?>
            <?php foreach($parameters as $parameter): ?>
                <div class="text-result-item">
                    <div class="text-result-title"><?= $parameter['name'] ?></div>
                    <div class="text-result-content">
                        <?= nl2br($results['parameter_'.($parameter['id']+1200)] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($test_info['type'] == 3): ?>
            <div class="results-table-container">
                <table class="results-table antibody-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Antigen</th>
                            <th>O</th>
                            <th>H</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sn=1;
                        foreach($parameters as $parameter):
                            $vals = explode(',', $results['parameter_'.($parameter['id']+1200)] ?? '');
                        ?>
                        <tr>
                            <td><?= $sn++ ?></td>
                            <td class="antigen-cell"><?= $parameter['name'] ?></td>
                            <td><?= $vals[0] ?? '' ?></td>
                            <td><?= $vals[1] ?? '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- NOTES -->
        <div class="notes-section">
            <div class="notes-title">Clinical Notes & Observations</div>
            <div class="notes-content">
                <?= trim($notes) ?: 'No additional clinical notes recorded.' ?>
            </div>
        </div>

        <!-- DISCLAIMER -->
        <div class="disclaimer">
            <p>Please note: Any alteration to this result renders it invalid</p>
        </div>

        <!-- PRINT -->
        <div class="print-controls">
            <button onclick="window.print()" class="print-btn no-print">
                Print Laboratory Report
            </button>
        </div>
    </div>
</div>    
</div>







<?php include '../includes/footer.php'; ?>
</main>


</body>
</html>
