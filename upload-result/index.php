<?php
include '../functions.php';

/* =========================
   AUTHORIZATION
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';



if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request.';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient_test_id = filter_var(sanitize($_GET['id']), FILTER_VALIDATE_INT);
if (!$patient_test_id) {
    $_SESSION['error'] = 'Invalid test reference.';
    echo "<script>window.history.back()</script>";
    exit;
}

$patient_test_id -= 1200;

/* =========================
   FETCH PATIENT TEST
========================= */
$sql = "
    SELECT *
    FROM test_lists
    WHERE id = '$patient_test_id'
      AND (status = 3 OR status = 4 OR status = 5 OR status = 6)
      AND paid = 1
";
$run = $db->query($sql);


if ($run->num_rows === 0) {
    $_SESSION['error'] = 'This test does not exist or is not eligible.';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_info = $run->fetch_assoc();

$test_id        = $test_info['test_id'];
$patient_test     = $test_info['patient_test_id'];
$labno          = $test_info['labno'];
$date_request   = $test_info['date_request'];
$date_release   = $test_info['date_of_result'];
$notes          = $test_info['notes'];
$release_officer= $test_info['result_releaser_id'];
$test_status    = $test_info['status'];

/* =========================
   FORMAT DATES
========================= */
$date = '';
if (!empty($date_request)) {
    $date = date('d M Y', strtotime($date_request));
}

if (!empty($date_release)) {
    $date_release = date('d M Y', strtotime($date_release));
}

/* =========================
   FETCH TEST INFO
========================= */

    $sql = "
        SELECT * 
        FROM tests 
        WHERE id = '$test_id'
          AND status = 1
    ";

$run = $db->query($sql);

if ($run->num_rows === 0) {
    $_SESSION['error'] = 'Test not found or access denied.';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_info = $run->fetch_assoc();

/* =========================
   FETCH PARAMETERS
========================= */
$parameters = [];
$sql = "
    SELECT * 
    FROM result_parameters 
    WHERE test_id = '$test_id'
      AND status = 1
";
$run = $db->query($sql);
while ($row = $run->fetch_assoc()) {
    $parameters[] = $row;
}

/* =========================
   FETCH RESULTS
========================= */
$results = [];
$sql = "
    SELECT * 
    FROM results 
    WHERE patient_test_id = '$patient_test_id'
";
$run = $db->query($sql);

while ($row = $run->fetch_assoc()) {
    $parameter_id = $row['parameter_id'] + 1200;
    $results['parameter_' . $parameter_id] = $row['answer'];
}




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


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Upload Results</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>
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
 .delete-btn {
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

 .delete-btn:hover {
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

    /* SUCCESS BUTTON (Verify) */
.btn-success {
    background-color: #28a745;
    border: 1px solid #28a745;
    color: #fff;
    padding: 8px 18px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.25);
}

.btn-success:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
}

/* WARNING BUTTON (Unverify / Attention) */
.btn-warning {
    background-color: #ffc107;
    border: 1px solid #ffc107;
    color: #212529;
    padding: 8px 18px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.35);
}

.btn-warning:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.35);
}

/* Disabled state */
.btn-success:disabled,
.btn-warning:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    box-shadow: none;
}

</style>
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="col-lg-12">
    <div class="card">

        <!-- PAGE TITLE -->
        <div class="card-header bg-info">
            <h3 class="card-title text-white">Upload Laboratory Result</h3>
        </div>

        <div class="card-body">

            <!-- PATIENT INFO -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title">Patient Information</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <th width="30%">Name</th>
                            <td><?= get('name','users',$patient_id) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= get('email','users',$patient_id) ?></td>
                        </tr>
                        <tr>
                            <th>Lab Number</th>
                            <td><?= $labno ?></td>
                        </tr>
                        <tr>
                            <th>Date Requested</th>
                            <td><?= $date ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- TEST INFO -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title">Test Information</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <th width="30%">Test Name</th>
                            <td><?= $test_info['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Specimen Required</th>
                            <td><?= get_specimen($test_info['specimen']) ?></td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td>Laboratory</td>
                        </tr>
                        <tr>
                            <th>Release Date</th>
                            <td><?= $date_release ?></td>
                        </tr>
                        <tr>
                            <th>Release Officer</th>
                            <td><?= get('name','users',$release_officer) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- VERIFY / UNVERIFY ACTION -->
            <?php if (in_array($_SESSION['type'], [0,2,-2])): ?>
                <div class="text-right mb-3" style="margin:15px">
                    <?php if ($test_status == 4 || $test_status == 6): ?>
                        <a href="verify.php?pid=<?= $patient_test_id + 1200 ?>" class="btn  btn-success">
                            Verify Result
                        </a>
                    <?php elseif ($test_status == 5): ?>
                        <a href="unverify.php?pid=<?= $patient_test_id + 1200 ?>" class="btn btn-warning">
                            Unverify Result
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- RESULT FORM -->
            <form method="post" action="submit_result.php">
                <input type="hidden" name="patient_test" value="<?= $patient_test_id + 1200 ?>">
                <input type="hidden" name="test_id" value="<?= $test_id + 1200 ?>">

                <!-- TYPE 1 -->
                <?php if ($test_info['type'] == 1): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Parameter</th>
                                    <th>Result</th>
                                    <th>Reference Range</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; foreach ($parameters as $parameter): ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= $parameter['name'] ?></td>
                                        <td>
                                            <input type="text"
                                                class="form-control"
                                                name="value_<?= $parameter['id'] + 1200 ?>"
                                                value="<?= $results['parameter_'.($parameter['id']+1200)] ?? '' ?>">
                                        </td>
                                        <td><?= $parameter['para_range'] ?></td>
                                        <td><?= $parameter['unit'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- TYPE 2 -->
                <?php if ($test_info['type'] == 2): ?>
                    <?php foreach ($parameters as $parameter): ?>
                        <div class="form-group">
                            <label><strong><?= $parameter['name'] ?></strong></label>
                            <textarea class="form-control myTextarea" rows="6"
                                name="value_<?= $parameter['id'] + 1200 ?>"><?= trim($results['parameter_'.($parameter['id']+1200)] ?? '') ?></textarea>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- NOTES -->
                <div class="form-group mt-3" style="align-items: center;" align="center">
                    <label><strong>Notes</strong></label>
                    <textarea class="form-control myTextarea" rows="6" cols="40" name="notes"><?= trim($notes) ?></textarea>
                </div>

                <!-- SUBMIT -->
               
                    <div class="text-center mt-4" align="center" style="margin: 10px;">
                        <button type="submit" class="btn btn-info px-5 btn-primary">
                            Upload Result
                        </button>
                    </div>
               
            </form>

        </div>
    </div>
</div>


</div>




<?php include '../includes/footer.php'; ?>
</main>



</body>
</html>
