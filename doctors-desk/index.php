<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}


$location = 'appointments';

if(empty($_GET['id'])){
      $_SESSION['error'] = 'Appointment Not Found';
        echo "<script>window.history.back()</script>";
        exit;
}

$appointment_id = sanitize($_GET['id']);
$sql = "SELECT 
    a.id AS appointment_id,
    a.date_appointed,
    a.date_ended,
    a.nurse_notes,
    a.room_id,
    a.diagnosis,
    a.clinical_notes,
    a.status AS appointment_status,

    u.id AS patient_id,
    u.name AS patient_name,
    u.email,
    u.phone,
    u.gender,
    u.address,
    u.hospital_num,
    u.dob
FROM appointments a
INNER JOIN users u ON u.id = a.patient_id
WHERE a.id = '$appointment_id'
AND u.type = 1
LIMIT 1;
";
$run = $db->query($sql);
if($run->num_rows == 0){
     $_SESSION['error'] = 'Appointment Not Found';
        echo "<script>window.history.back()</script>";
        exit;
}

$appointment = $run->fetch_assoc();
$patient_id = $appointment['patient_id'];


$sql = "
    SELECT *
    FROM vitals
    WHERE status = 1
    ORDER BY id DESC
";

$vitals = $db->query($sql);


    $values = [];
    $sql = "SELECT * FROM patient_vitals  WHERE appointment_id = '$appointment_id'";
    $run = $db->query($sql);
   while($row = $run->fetch_assoc())$values[$row['vital_id']] = $row['vital_value'];


 $myTests = [];
  $sql = "SELECT * FROM tests WHERE status = 1 ORDER BY name ASC ";
  $run = $db->query($sql);
  while($row = $run->fetch_assoc())$myTests[] = $row;
  


   $myDrugs = [];
  $sql = "SELECT * FROM drugs WHERE status = 1 ORDER BY drug_name ASC ";
  $run = $db->query($sql);
  while($row = $run->fetch_assoc())$myDrugs[] = $row;

  $previous_appointments = [];

$sql = "
    SELECT a.*,
           u.name AS doctor_name
    FROM appointments a
    LEFT JOIN users u ON a.doctor_id = u.id
    WHERE a.patient_id = '$patient_id' AND a.id != '$appointment_id'
    ORDER BY a.date_appointed DESC
";
  $run = $db->query($sql);

  while($row = $run->fetch_assoc()) $previous_appointments[] = $row;






 $sql = "
    SELECT dl.id AS drug_list_id,
           d.drug_name,
           d.dosage_form,
           d.strength,
           dl.prescription,
           dl.quantity,
           dl.amount,
           pd.priority,
           pd.notes,
           pd.delivery_option,
           pd.status
    FROM drug_list dl
    JOIN patient_drugs pd ON dl.patient_drugs_id = pd.id
    JOIN drugs d ON dl.drug_id = d.id
    WHERE pd.appointment_id = $appointment_id
    ORDER BY d.drug_name ASC
";


$prescribed_drugs = [];  // initialize array
$run = $db->query($sql);
while ($row = $run->fetch_assoc()) {
    $prescribed_drugs[] = $row;
}



 // replace with the actual appointment ID

// Get all patient tests linked to this appointment
 
 $sql = "
    SELECT 
        pt.id            AS patient_test_id,
        pt.appointment_id,
        pt.user_id,
        pt.payment_id,
        pt.priority,
        pt.specimen_collection,
        pt.preferred_date,
        pt.preferred_time,
        pt.status         AS patient_test_status,

        ptl.id            AS testlist_id,
        ptl.test_id,
        ptl.result_releaser_id,
        ptl.status        AS testlist_status,
        ptl.sample_collector,
        ptl.acknowleger_id,
        ptl.date_request,
        ptl.sample_collection_date,
        ptl.date_of_result,
        ptl.notes,
        ptl.invoice_id,
        ptl.labno,
        ptl.paid,
        ptl.amount,

        t.name            AS test_name,
        t.amount          AS test_amount,
        t.specimen,
        t.type,
        t.status          AS test_master_status
    FROM patient_test pt
    JOIN test_lists ptl ON pt.id = ptl.patient_test_id
    JOIN tests t ON ptl.test_id = t.id
    WHERE pt.appointment_id = {$appointment_id}
";

$prescribed_tests = [];
$run = $db->query($sql);
if(!$run)echo $db->error;
while ($row = $run->fetch_assoc()) {
    $prescribed_tests[] = $row;
}

// $myTests now contains all lab tests for that appointment

// Fetch admission history for this patient
$admission_history = [];
$sql = "
    SELECT
        a.*,
        r.room_name,
        r.room_price,
        w.ward_name,
        d.name AS doctor_name,
        (SELECT COUNT(*) FROM admission_reports ar WHERE ar.admission_id = a.id) AS report_count,
        (SELECT COALESCE(SUM(ab.amount), 0) FROM admission_billing ab WHERE ab.admission_id = a.id) AS total_bill,
        (SELECT COALESCE(SUM(ab.amount), 0) FROM admission_billing ab WHERE ab.admission_id = a.id AND ab.paid = 1) AS paid_amount
    FROM admissions a
    INNER JOIN rooms r ON r.id = a.room_id
    LEFT JOIN wards w ON w.id = r.ward
    LEFT JOIN users d ON d.id = a.doctor_id
    WHERE a.patient_id = '$patient_id'
    ORDER BY a.admission_date DESC
";
$run = $db->query($sql);
if($run) while ($row = $run->fetch_assoc()) $admission_history[] = $row;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Doctors Desk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style type="text/css">
         .card{

           margin: 15px;

         }
    </style>
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="content-scroll">
     <div class="content-scroll">
    <!-- Page Header -->
    <div class="view-header">
        <div>
            <h1>Patient Consultation</h1>
            <p>Diagnosis, prescription, and treatment management</p>
        </div>
        <div class="header-actions">
            <a href="../appointments/index.php" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back to Appointments
            </a>
            <a href="complete_consultation.php?appointment_id=<?=$appointment_id?>" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Complete Consultation
            </a>
            <?php
            $activeAdmission = getActiveAdmission($patient_id);
            if(!$activeAdmission): ?>
                <a href="../admission/index.php?appointment_id=<?=$appointment_id?>" class="btn-primary" style="background:#059669;text-decoration:none;display:inline-flex;align-items:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                        <rect x="3" y="7" width="18" height="10" rx="2"></rect>
                        <line x1="7" y1="17" x2="7" y2="21"></line>
                        <line x1="17" y1="17" x2="17" y2="21"></line>
                    </svg>
                    Admit Patient
                </a>
            <?php elseif($activeAdmission): ?>
                <span style="background:#fef3c7;color:#92400e;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;">Currently Admitted - <?= get('room_name','rooms',$activeAdmission['room_id']) ?>, Bed <?= $activeAdmission['bed_number'] ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-row">
        <!-- Left Column - Patient Info & Vitals -->
        <div>
            <!-- Patient Information Card -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2>Patient Information</h2>
                    <div class="patient-status-badges">
                        <?php if($appointment['appointment_status'] == 1){
                             ?>
                                 <span class="badge badge-primary">Active Consultation</span>
                        <?php }else{
                             ?>
                                <span class="badge badge-warning">Follow-up Required</span>
                        <?php } ?>
                    
                     
                    </div>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <div class="patient-profile">
                        <div class="patient-avatar-large">
                            <span><?=shortName($appointment['patient_name'])?></span>
                        </div>
                        <div class="patient-details-grid">
                            <div class="patient-info-item">
                                <span class="info-label">Name</span>
                                <span class="info-value"><?= $appointment['patient_name'] ?? 'John Doe' ?></span>
                            </div>
                            <div class="patient-info-item">
                                <span class="info-label">Hospital Number</span>
                                <span class="info-value badge-number"><?= $appointment['hospital_num'] ?? 'H-2024-00123' ?></span>
                            </div>
                            <div class="patient-info-item">
                                <span class="info-label">Phone</span>
                                <span class="info-value"><?= $appointment['phone'] ?? '+1 (555) 123-4567' ?></span>
                            </div>
                            <div class="patient-info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value"><?= $appointment['gender'] ?? 'Male' ?></span>
                            </div>
                            <div class="patient-info-item">
                                <span class="info-label">Date of Birth</span>
                                <span class="info-value"><?= formatDateReadable($appointment['dob']) ?? '1992-05-15 (32 years)' ?></span>
                            </div>
                            <div class="patient-info-item">
                                <span class="info-label">Appointment Date</span>
                                <span class="info-value badge-success"><?= formatDateReadableWithTime($appointment['date_appointed']) ?? date('Y-m-d') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vitals Card -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2>Vital Signs</h2>
               <!--      <button type="button" class="btn-small" id="updateVitalsBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                            <path d="M16 16h5v5"></path>
                        </svg>
                        Update Vitals
                    </button> -->
                </div>
                <div class="card-body">
                    <div class="vitals-grid">
                       
                        <?php foreach($vitals as $v){
                                $vital_id = $v['id'];
                               $vital_value =  !(empty($values[$vital_id])) ? $values[$vital_id] : ""   
                                ?>
                        <div class="vital-item">
                            <div class="vital-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path>
                                </svg>
                                <?=$v['name']?>
                            </div>
                            <div class="vital-value"><?=$vital_value.' '.$v['unit']?></div>
                         <!--    <div class="vital-status normal">Normal</div> -->
                        </div>
                          <?php } ?>
                      
                       
                     
                    
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Diagnosis & Actions -->
        <div>
            <!-- Diagnosis & Prescription Card -->
            <form method="POST" action="process_diagnosis.php" id="diagnosisForm" class="card" style="margin-bottom: 24px;">
                <input type="hidden" id="patient_id" name="patient_id" value="<?= $appointment['patient_id'] ?? '' ?>">
                <input type="hidden" id="appointment_id" name="appointment_id" value="<?= $appointment_id ?? '' ?>">
                
                <div class="card-header">
                    <h2>Diagnosis & Treatment</h2>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <!-- Diagnosis -->
                    <div class="form-section">
                        <label for="diagnosis" class="form-label">
                                 <svg xmlns="http://www.w3.org/2000/svg"
             width="16" height="16"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2"
             stroke-linecap="round"
             stroke-linejoin="round"
             style="margin-right:8px;">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <path d="M14 2v6h6"></path>
            <path d="M8 13h8"></path>
            <path d="M8 17h5"></path>
        </svg>
                            Primary Diagnosis
                        </label>
                      
                    
                        <textarea id="diagnosis" name="diagnosis" rows="3" class="form-textarea" placeholder="Enter diagnosis details..."><?= $appointment['diagnosis'] ?? '' ?></textarea>
                            <!-- Clinical Notes -->
                   
                            <div class="form-section">
                                <label for="clinicalNotes" class="form-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    Clinical Notes
                                </label>
                                <textarea id="clinicalNotes" name="clinical_notes" rows="4" class="form-textarea" placeholder="Additional observations, recommendations, follow-up instructions..."><?= $appointment['clinical_notes'] ?? '' ?>
                                </textarea>
                            </div>


                            <div class="form-actions" style="margin-top: 16px;">
                            <button type="submit" class="btn-secondary" onclick="saveDiagnosis()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                  Save 
                            </button>
                        </form>
                        </div>
                    </div>

                    <!-- Prescription Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <label class="form-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Prescribe Medication
                            </label>
                      <!--       <button type="button" class="btn-small" onclick="addPrescriptionItem()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Medication
                            </button> -->
                        </div>
                        
                        <div class="lab-tests-grid" id="drugGrid">

    <!-- SEARCH INPUT -->
    <div class="lab-tests-search" style="margin-bottom:12px;">
        <input type="text"
               id="drugSearch"
               placeholder="Search drug..."
               style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
    </div>

    <?php foreach($myDrugs as $d): ?>
    <label class="checkbox-label drug-item"
           data-name="<?= strtolower($d['drug_name']) ?>">

        <input type="checkbox"
               name="drug[]"
               value="<?= $d['id'] ?>"
               class="checkbox-input drug-checkbox">

        <span class="checkbox-custom"></span>
        <span class="checkbox-text"><?= htmlspecialchars($d['drug_name']) ?></span>

        <!-- Prescription Input -->
        <input type="text"
               name="drugs[<?= $d['id'] ?>][prescription]"
               class="drug-prescription"
               placeholder="e.g. 1 tab twice daily for 5 days"
               disabled>



    </label>
    <?php endforeach; ?>

</div>

                        
                        <div class="form-actions" style="margin-top: 16px;">
                            <button type="button" class="btn-secondary" onclick="sendToPharmacy()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                Send to Pharmacy
                            </button>
                        </div>
                    </div>

                    <!-- Lab Tests Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <label class="form-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                                Order Lab Tests
                            </label>
                       <!--      <button type="button" class="btn-small" onclick="addLabTest()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Test
                            </button> -->
                        </div>
                        
                       <div class="lab-tests-grid" id="labTestsGrid">
                        <div class="lab-tests-search" style="margin-bottom:12px;">
    <input type="text"
           id="labTestSearch"
           placeholder="Search lab test..."
           style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
</div>

                                <?php foreach($myTests as $test): ?>
                                    <label class="checkbox-label" data-name="<?= strtolower($test['name']) ?>">
                                        <input type="checkbox"
                                               name="lab_tests[]"
                                               value="<?= $test['id'] ?>"
                                               class="checkbox-input">
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text"><?= htmlspecialchars($test['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                         </div>
                        
                        <div class="form-actions" style="margin-top: 16px;">
                            <button type="button" class="btn-secondary" onclick="sendToLab()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 8v8"></path>
                                    <path d="M8 12h8"></path>
                                </svg>
                                Send to Laboratory
                            </button>
                        </div>
                    </div>

                
                    
                </div>
           
        </div>
    </div>


     <!-- Previous Appointments Card -->
    <div class="card">
        <div class="card-header">
            <h2>Prescibed Drgs</h2>
            <div class="card-actions">
                <span class="badge badge-secondary">Total: <?= count($prescribed_drugs) ?? '0' ?> records</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Drug Name</th>
                            <th>Dosage Form</th>
                            <th>Strength</th>
                            <th>Prescription</th>
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($prescribed_drugs as $pd): ?>
                        <tr>
                            <td>
                                  <div class="diagnosis-cell"><?= htmlspecialchars($pd['drug_name']) ?></div>
                            </td>
                            <td>
                                <div class="diagnosis-cell"><?= htmlspecialchars($pd['dosage_form']) ?></div>
                            </td>
                            <td>
                                <div class="diagnosis-cell"><?= htmlspecialchars($pd['strength']) ?></div>
                            </td>
                            <td>
                                  <div class="diagnosis-cell"><?= htmlspecialchars($pd['prescription']) ?></div>
                            </td>
                            <td>
                                 <div class="diagnosis-cell"><?= htmlspecialchars($pd['quantity']) ?></div>
                            </td>
                              <td>
                                <?php if($pd['status'] == 0){
                                     ?>
                                     <div class="diagnosis-cell">Pending</div>
                                <?php }else if($pd['status'] == 1){
                                     ?>
                                     <div class="diagnosis-cell">Paid</div>
                                <?php }else if($pd['status'] == 2){
                                     ?>
                                     <div class="diagnosis-cell">Collected</div>
                                <?php } ?>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Quick Notes Section -->
        <!--     <div class="quick-notes" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                <h3 style="margin-bottom: 16px;">Add Progress Note</h3>
                <div class="note-input">
                    <textarea placeholder="Enter progress notes for this consultation..." rows="2"></textarea>
                    <button class="btn-primary" style="margin-top: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        Save Note
                    </button>
                </div>
            </div> -->
        </div>
    </div>


    <div class="card">
    <div class="card-header">
        <h2>Prescribed Tests</h2>
        <div class="card-actions">
            <span class="badge badge-secondary">
                Total: <?= count($prescribed_tests) ?? '0' ?> records
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Test Name</th>
                        <th>Specimen</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Lab No</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($prescribed_tests)): ?>
                        <?php foreach($prescribed_tests as $pt): ?>
                            <tr>
                                <td>
                                    <div class="diagnosis-cell">
                                        <?= htmlspecialchars($pt['test_name']) ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="diagnosis-cell">
                                        <?= htmlspecialchars($pt['specimen']) ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="diagnosis-cell">
                                        <?= htmlspecialchars($pt['type']) ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="diagnosis-cell">
                                        <?= htmlspecialchars($pt['priority']) ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="diagnosis-cell">
                                        <?= htmlspecialchars($pt['labno'] ?? '-') ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="diagnosis-cell">
                                        ₦<?= number_format($pt['amount']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?php
                                        echo get_status($pt['testlist_status']);
                                    ?>
                                </td>
                                <td>
                                    <?php if($pt['testlist_status'] ==  5 OR $pt['testlist_status'] ==  7 ){
                                         ?>
                                        <a href="../view-results/index.php?id=<?=$pt['testlist_id']+1200?>">View Result</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">
                                No tests prescribed for this appointment
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Admission History Card -->
<div class="card">
    <div class="card-header">
        <h2>Admission History</h2>
        <div class="card-actions">
            <span class="badge badge-secondary">Total: <?= count($admission_history) ?> record(s)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Room / Bed</th>
                        <th>Admitted</th>
                        <th>Discharged</th>
                        <th>Doctor</th>
                        <th>Reports</th>
                        <th>Billing</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($admission_history)): ?>
                        <?php foreach($admission_history as $adm): ?>
                        <tr>
                            <td>
                                <div class="diagnosis-cell">
                                    <strong><?= htmlspecialchars($adm['room_name']) ?></strong> - Bed <?= $adm['bed_number'] ?>
                                    <br><small style="color:var(--text-muted);"><?= htmlspecialchars($adm['ward_name'] ?? '') ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="date-cell"><?= formatDateReadable($adm['admission_date']) ?></div>
                                <small style="color:var(--text-muted);"><?= date('g:i A', strtotime($adm['admission_date'])) ?></small>
                            </td>
                            <td>
                                <?php if($adm['status'] == 1 && $adm['discharge_date']): ?>
                                    <div class="date-cell"><?= formatDateReadable($adm['discharge_date']) ?></div>
                                    <small style="color:var(--text-muted);"><?= date('g:i A', strtotime($adm['discharge_date'])) ?></small>
                                <?php else: ?>
                                    <span style="color:#d97706;font-weight:600;">Still Admitted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="diagnosis-cell"><?= htmlspecialchars($adm['doctor_name'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= $adm['report_count'] ?></span>
                            </td>
                            <td>
                                <div class="diagnosis-cell">
                                    &#8358;<?= number_format($adm['total_bill'], 2) ?>
                                    <?php if($adm['paid_amount'] < $adm['total_bill']): ?>
                                        <br><small style="color:#dc2626;">Unpaid: &#8358;<?= number_format($adm['total_bill'] - $adm['paid_amount'], 2) ?></small>
                                    <?php else: ?>
                                        <br><small style="color:#15803d;">Fully Paid</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if($adm['status'] == 0): ?>
                                    <span style="background:#dcfce7;color:#15803d;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">Active</span>
                                <?php elseif($adm['status'] == 1): ?>
                                    <span style="background:#f1f5f9;color:#64748b;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">Discharged</span>
                                <?php else: ?>
                                    <span style="background:#fef2f2;color:#dc2626;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a href="../admission/reports.php?id=<?= $adm['id'] ?>" class="btn-secondary" style="padding:5px 12px;font-size:12px;text-decoration:none;">Reports</a>
                                    <a href="../admission/billing.php?id=<?= $adm['id'] ?>" class="btn-secondary" style="padding:5px 12px;font-size:12px;text-decoration:none;">Billing</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">
                                No admission history for this patient
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Previous Appointments Card -->
  <div class="card">
    <div class="card-header">
        <h2>Medical History</h2>
        <div class="card-actions">
            <span class="badge badge-secondary">Total: <?= count($previous_appointments) ?? '0' ?> records</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Diagnosis</th>
                        <th>Prescription / Drugs</th>
                        <th>Lab Tests</th>
                        <th>Doctor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($previous_appointments as $prev): ?>
                    <tr>
                        <td>
                            <div class="date-cell"><?= formatDateReadable($prev['date_appointed']) ?></div>
                            <div class="date-ago"><?= isset($prev['time_ago']) ? $prev['time_ago'] : '' ?></div>
                        </td>
                        <td>
                            <div class="diagnosis-cell"><?= htmlspecialchars($prev['diagnosis']) ?></div>
                            <?php if(!empty($prev['follow_up'])): ?>
                                <span class="badge-small badge-warning">Follow-up</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="prescription-cell">
                                <?php 
                                // Fetch drugs for this appointment
                                $drugs = [];
                                $sql = "
                                    SELECT dl.quantity, dl.amount, d.drug_name
                                    FROM patient_drugs pd
                                    JOIN drug_list dl ON pd.id = dl.patient_drugs_id
                                    JOIN drugs d ON dl.drug_id = d.id
                                    WHERE pd.appointment_id = {$prev['id']}
                                ";
                                $run = $db->query($sql);
                                while($row = $run->fetch_assoc()) $drugs[] = $row;

                                foreach(array_slice($drugs, 0, 2) as $drug): 
                                ?>
                                    <span class="drug-tag">
                                        <?= htmlspecialchars($drug['drug_name']) ?> (<?= $drug['quantity'] ?>) - ₦<?= number_format($drug['amount']) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if(count($drugs) > 2): ?>
                                    <span class="more-indicator">+<?= count($drugs) - 2 ?> more</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="lab-cell">
                                <?php 
                                // Fetch lab tests for this appointment
                                $tests = [];
                                $sql = "
                                    SELECT t.name AS test_name
                                    FROM patient_test pt
                                    JOIN test_lists ptl ON pt.id = ptl.patient_test_id
                                    JOIN tests t ON ptl.test_id = t.id
                                    WHERE pt.appointment_id = {$prev['id']}
                                ";
                                $run = $db->query($sql);
                                while($row = $run->fetch_assoc()) $tests[] = $row;

                                foreach(array_slice($tests, 0, 2) as $test): 
                                ?>
                                    <span class="test-tag"><?= htmlspecialchars($test['test_name']) ?></span>
                                <?php endforeach; ?>
                                <?php if(count($tests) > 2): ?>
                                    <span class="more-indicator">+<?= count($tests) - 2 ?> more</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="doctor-cell">
                                <div class="doctor-avatar-small"><?= strtoupper(substr($prev['doctor_name'], 0, 2)) ?></div>
                                <span><?= htmlspecialchars($prev['doctor_name']) ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>


   


    </div>


 <!-- Pharmacy Modal -->
<div class="modal-overlay" id="pharmacyModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Send Prescription to Pharmacy</h3>
            <button class="modal-close" onclick="closePharmacyModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="modal-patient-info">
                <div class="patient-avatar-small">
                    <span><?=shortName($appointment['patient_name'])?></span>
                </div>
                <div>
                    <h4><?= $appointment['patient_name'] ?></h4>
                    <p>Hospital #: <?= $appointment['hospital_num'] ?> | Appointment: <?= formatDateReadableWithTime($appointment['date_appointed']) ?></p>
                </div>
            </div>
            
            <div class="modal-section">
                <div class="section-header">
                    <h4>Selected Medications</h4>
                    <span class="badge" id="selectedDrugCount">0 drugs selected</span>
                </div>
                
                <div class="selected-items-list" id="selectedDrugsList">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
            
            <div class="modal-section">
                <h4>Pharmacy Instructions</h4>
                <div class="form-group">
                    <label>Priority</label>
                    <div class="priority-select">
                        <label class="radio-label">
                            <input type="radio" name="pharmacyPriority" value="normal" checked>
                            <span class="radio-custom"></span>
                            <span class="radio-text">Normal (24 hours)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="pharmacyPriority" value="urgent">
                            <span class="radio-custom"></span>
                            <span class="radio-text">Urgent (4 hours)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="pharmacyPriority" value="emergency">
                            <span class="radio-custom"></span>
                            <span class="radio-text">Emergency (1 hour)</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="pharmacyNotes">Additional Notes (Optional)</label>
                    <textarea id="pharmacyNotes" rows="3" placeholder="Any special instructions for the pharmacy..."></textarea>
                </div>
            </div>
            
            <div class="modal-section">
                <h4>Delivery Option</h4>
                <div class="delivery-options">
                    <label class="radio-label">
                        <input type="radio" name="deliveryOption" value="pickup" checked>
                        <span class="radio-custom"></span>
                        <span class="radio-text">Patient Pickup</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="deliveryOption" value="ward">
                        <span class="radio-custom"></span>
                        <span class="radio-text">Deliver to Ward</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="deliveryOption" value="emergency_delivery">
                        <span class="radio-custom"></span>
                        <span class="radio-text">Emergency Delivery</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closePharmacyModal()">
                Cancel
            </button>
            <button class="btn-primary" onclick="confirmPharmacySend()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M22 2L11 13"></path>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Send to Pharmacy
            </button>
        </div>
    </div>
</div>

<!-- Lab Modal -->
<div class="modal-overlay" id="labModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Send Lab Test Requests</h3>
            <button class="modal-close" onclick="closeLabModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="modal-patient-info">
                <div class="patient-avatar-small">
                    <span><?=shortName($appointment['patient_name'])?></span>
                </div>
                <div>
                    <h4><?= $appointment['patient_name'] ?></h4>
                    <p>Hospital #: <?= $appointment['hospital_num'] ?> | Appointment: <?= formatDateReadableWithTime($appointment['date_appointed']) ?></p>
                </div>
            </div>
            
            <div class="modal-section">
                <div class="section-header">
                    <h4>Selected Lab Tests</h4>
                    <span class="badge" id="selectedLabCount">0 tests selected</span>
                </div>
                
                <div class="selected-items-list" id="selectedLabList">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
            
            <div class="modal-section">
                <h4>Test Instructions</h4>
                <div class="form-group">
                    <label>Priority</label>
                    <div class="priority-select">
                        <label class="radio-label">
                            <input type="radio" name="labPriority" value="routine" checked>
                            <span class="radio-custom"></span>
                            <span class="radio-text">Routine (24-48 hours)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="labPriority" value="urgent">
                            <span class="radio-custom"></span>
                            <span class="radio-text">Urent (4-6 hours)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="labPriority" value="stat">
                            <span class="radio-custom"></span>
                            <span class="radio-text">STAT (Immediate)</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="labNotes">Clinical Notes (Optional)</label>
                    <textarea id="labNotes" rows="3" placeholder="Relevant clinical information for lab..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Specimen Collection</label>
                    <div class="specimen-options">
                        <label class="checkbox-label">
                            <input type="checkbox" id="collectBlood" checked>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Blood Sample Required</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="collectUrine">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Urine Sample Required</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="fastingRequired">
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">Fasting Required</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-section">
                <h4>Test Schedule</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="preferredDate">Preferred Date</label>
                        <input type="date" id="preferredDate" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label for="preferredTime">Preferred Time</label>
                        <select id="preferredTime">
                            <option value="morning">Morning (8AM-12PM)</option>
                            <option value="afternoon">Afternoon (12PM-4PM)</option>
                            <option value="evening">Evening (4PM-8PM)</option>
                            <option value="asap">ASAP</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeLabModal()">
                Cancel
            </button>
            <button class="btn-primary" onclick="confirmLabSend()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M22 2L11 13"></path>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Send to Laboratory
            </button>
        </div>
    </div>
</div>

<!-- Success Toast Notification -->
<div class="toast-container" id="toastContainer"></div>

</div>

    <script type="text/javascript" src="script.js"></script>
<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>