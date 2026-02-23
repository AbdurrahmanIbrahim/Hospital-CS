<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$status         = isset($_GET['status']) ? sanitize($_GET['status']) : '';

/* =========================
   FILTER & PAGINATION
========================= */
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date   = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$search     = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 20;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE
========================= */
$where = "";

if ($status !== '' AND $status!='4' AND $status!=5) {
    $where .= " AND ptl.status = '$status'";
}else if($status!= '' AND $status =='4'){
    $where.=" AND (ptl.status = 4 OR ptl.status = 5 OR ptl.status = 6)";
}else if($status!= '' AND $status =='5'){
    $where.=" AND (ptl.status = 5 OR ptl.status = 7)";
}

$filterText = "Lab Test";

if (empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>All</strong>";
} elseif (!empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>" . formatDateReadable($start_date) . "</strong>";
} elseif (!empty($start_date) && !empty($end_date)) {
    $filterText .= " from <strong>" . formatDateReadable($start_date) . "</strong> 
                     to <strong>" . formatDateReadable($end_date) . "</strong>";
}

/* =========================
   DATE FILTER (REQUEST DATE)
========================= */
if (empty($start_date) && empty($end_date)) {
    $today = date('Y-m-d');
    //$where .= " AND DATE(ptl.date_request) = '$today'";
} elseif (!empty($start_date) && empty($end_date)) {
    $where .= " AND DATE(ptl.date_request) = '$start_date'";
} elseif (!empty($start_date) && !empty($end_date)) {
    $where .= " AND DATE(ptl.date_request) BETWEEN '$start_date' AND '$end_date'";
}

/* =========================
   SEARCH FILTER
========================= */
if (!empty($search)) {
    $where .= " AND (
        t.name LIKE '%$search%'
        OR ptl.labno LIKE '%$search%'
        OR u.name LIKE '%$search%'
        OR u.hospital_num LIKE '%$search%'
        OR u.phone LIKE '%$search%'
        OR pay.note LIKE '%$search%'
    )";
}

/* =========================
   COUNT FOR PAGINATION
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM patient_test pt
    JOIN test_lists ptl ON pt.id = ptl.patient_test_id
    JOIN tests t ON ptl.test_id = t.id
    LEFT JOIN appointments a ON pt.appointment_id = a.id AND pt.appointment_id > 0
    LEFT JOIN users u ON u.id = IF(pt.appointment_id > 0, a.patient_id, pt.user_id)
    LEFT JOIN payments pay ON pt.payment_id = pay.id
    WHERE 1=1 $where
";

$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   FETCH TEST RECORDS
========================= */
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
        ptl.date_acknowledge,
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
        t.status          AS test_master_status,

        u.name            AS patient_name,
        u.hospital_num,

        a.date_appointed,
        COALESCE(a.patient_id, pt.user_id) AS patient_id,

        pay.note          AS payment_note

    FROM patient_test pt
    JOIN test_lists ptl ON pt.id = ptl.patient_test_id
    JOIN tests t ON ptl.test_id = t.id
    LEFT JOIN appointments a ON pt.appointment_id = a.id AND pt.appointment_id > 0
    LEFT JOIN users u ON u.id = IF(pt.appointment_id > 0, a.patient_id, pt.user_id)
    LEFT JOIN payments pay ON pt.payment_id = pay.id

    WHERE 1=1 $where
    ORDER BY ptl.id DESC
    LIMIT $limit OFFSET $offset
";



$tests = $db->query($sql);

// if(!$run){
//      echo $db->error;
//      exit;
// }
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

/* MODAL OVERLAY */
.modal-container.md {
    position: fixed;
    inset: 0;
    background: rgba(0, 33, 71, 0.55); /* hospital dark overlay */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 15px;
    display: none;
}

/* MODAL CARD */
.modal-content.mc {
    background: #ffffff;
    width: 100%;
    max-width: 420px;
    border-radius: 10px;
    padding: 25px 22px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    animation: modalPop .3s ease;
    position: relative;
}

/* ANIMATION */
@keyframes modalPop {
    from {
        transform: scale(.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* CLOSE ICON */
.modal-content.mc .close {
    position: absolute;
    right: 15px;
    top: 12px;
    font-size: 22px;
    font-weight: bold;
    color: #888;
}

.modal-content.mc .close:hover {
    color: #d63031;
}

/* TITLE */
.modal-content.mc h2 {
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    color: #002147;
    margin-bottom: 18px;
}

/* SELECT & INPUT */
.modal-content.mc select,
.modal-content.mc input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccd6e0;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 12px;
}

.modal-content.mc select:focus,
.modal-content.mc input:focus {
    outline: none;
    border-color: #0364FF;
    box-shadow: 0 0 0 2px rgba(3,100,255,.15);
}

/* CONFIRM BUTTON */
.modal-content.mc button {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 6px;
    background: linear-gradient(135deg, #0364FF, #00A3FF);
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s ease;
}

.modal-content.mc button:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 15px rgba(3,100,255,.35);
}


</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header">
        <h2><?=$filterText?></h2>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- DATE FILTER -->
            <!-- DATE FILTER -->
        <div class="search-section" style="margin-bottom: 24px;">
      <form action="" method="GET" class="search-form"> 
    <div class="search-filters">

        <!-- Start Date -->
        <div class="filter-group">
            <label for="startDate" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                Start Date
            </label>
            <input type="date" 
                   name="start_date" 
                   id="startDate"
                   value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>"
                   style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 160px; background-color: white;">
        </div>

        <!-- End Date -->
        <div class="filter-group" style="margin-left: 16px;">
            <label for="endDate" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                End Date
            </label>
            <input type="date" 
                   name="end_date" 
                   id="endDate"
                   value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>"
                   style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 160px; background-color: white;">
        </div>

        <!-- Text Search -->
        <div class="filter-group" style="flex-grow: 1; margin-left: 16px;">
            <label for="searchQuery" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                Search (Name, Phone, Hospital No)
            </label>
            <div class="search-input-wrapper" style="display: flex;">
                <input type="text" 
                       name="search" 
                       id="searchQuery" 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       placeholder="Enter patient name, phone or hospital number..."
                       style="flex-grow: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px 0 0 6px; font-size: 14px; border-right: none;">
                <button type="submit" 
                        class="search-btn"
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;">
                    Search
                </button>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="filter-group" style="margin-left: 16px; align-self: flex-end;">
            <a href="?" 
               class="reset-btn"
               style="display: inline-block; padding: 10px 16px; background-color: #6c757d; color: white; border: 1px solid #6c757d; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                Reset
            </a>
        </div>
      <input type="hidden" name="status" value="<?=$status?>">
    </div>
</form>
</div>

<?php if ($tests->num_rows == 0): ?>
    <p style="text-align:center;padding:30px;color:#777;">
        No lab tests found.
    </p>
<?php else: ?>

<div class="table-responsive">
    <table class="table" style="overflow:hidden;">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient Name</th>
                <th>Hospital Number</th>
                <th>Appointment Date</th>
                <th>Test Name</th>
                <th>Specimen</th>
                <th>Lab No</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Priority</th>
                
                <th>Sample Collector/Rejector</th>
                <th>Acknowledger</th>
                <th>Result Collector</th>
               
                <th>Requested Date</th>
                <th>Sample Date</th>
                <th>Acknowledge Date</th>
                <th>Result Date</th>

                <th>Status</th>
                <th>Reason</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
                 <form method="POST" action="collect.php" id="my_test_form">
            <?php $i = $offset + 1; while ($row = $tests->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>

                <td>
                    <?php if (!empty($row['patient_name'])): ?>
                        <?= htmlspecialchars($row['patient_name']) ?>
                    <?php elseif (!empty($row['payment_note'])): ?>
                        <?= htmlspecialchars(str_replace('POS Walk-In: ', '', $row['payment_note'])) ?>
                        <br><small style="color:#e67e22;font-weight:600;">(Walk-in)</small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($row['hospital_num'] ?? '-') ?></td>
                      <td>
                    <?php if ($row['appointment_id'] > 0 && $row['date_appointed']): ?>
                        <?= date('d M Y', strtotime($row['date_appointed'])) ?>
                    <?php else: ?>
                        <span style="color:#6c757d;">POS</span>
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($row['test_name']) ?></td>

                <td><?= htmlspecialchars(get('name','specimen',$row['specimen'])) ?></td>

                <td><?= htmlspecialchars($row['labno']) ?></td>

                <td><?= number_format($row['test_amount'], 2) ?></td>

                <td>
                    <?= $row['paid'] == 1
                        ? '<span class="status-paid">Paid</span>'
                        : '<span class="status-pending">Unpaid</span>' ?>
                </td>

                <td>
                    <?= $row['priority'] ?>
                </td>

                <td>
                    <?= get("name","users",$row['sample_collector']) ?>
                </td>

                <td>
                    <?= get("name","users",$row['acknowleger_id']) ?>
                </td>

                <td>
                    <?= $row['collector'] ?? 'NILL' ?>
                </td>

        


                <td><?= date('d M Y', strtotime($row['date_request'])) ?></td>

                <td>
                    <?= $row['sample_collection_date']
                        ? date('d M Y', strtotime($row['sample_collection_date']))
                        : '-' ?>
                </td>

                  <td>
                    <?= $row['date_acknowledge']
                        ? date('d M Y', strtotime($row['date_acknowledge']))
                        : '-' ?>
                </td>

                <td>
                    <?= $row['date_of_result']
                        ? date('d M Y', strtotime($row['date_of_result']))
                        : '-' ?>
                </td>

                <td>
                        <?=get_status($row['testlist_status'])?>
                </td>

                  <td>
                    <?= $row['reason'] ?? 'NILL' ?>
                </td>

                <td >
                
                <?php if (in_array($row['testlist_status'], [1,-1])): ?>

                    <select name="test_<?=$row['testlist_id']+1200?>"
                            onchange="submit_reason(this,<?=$row['testlist_id']+1200?>)">
                            <option value="">---Select---</option>
                            <option value="2" <?=($row['testlist_status']=='2') ? 'selected' : ''?>>Collected</option>
                            <option value="1" <?=($row['testlist_status']=='1') ? 'selected' : ''?>>Not Collected</option>
                            <option value="-1" <?=($row['testlist_status']=='-1') ? 'selected' : ''?>>Rejected</option>
                    </select>

                    <input type="hidden"
                           name="reason_<?=$row['testlist_id']+1200?>"
                           id="input_reason_<?=$row['testlist_id']+1200?>">

                <?php endif; ?>

                  <?php if (in_array($row['testlist_status'], [2])): ?>
                     <div class="action-links"><a href="acknowledge.php?status=3&patient_test_id=<?=$row['testlist_id']+1200?>">Acknowledge Collection</a></div>
                 <?php endif; ?>

                  <?php if (in_array($row['testlist_status'], [3])): ?>
                     <div class="action-links"><a href="../upload-result/index.php?id=<?=$row['testlist_id']+1200?>">Prepare Results</a></div>
                 <?php endif; ?>


                <?php if (in_array($row['testlist_status'], [4,5,6])): ?>
                     <div class="action-links"><a href="../upload-result/index.php?id=<?=$row['testlist_id']+1200?>">Results Verification</a></div>
                 <?php endif; ?>

                  <?php if (in_array($row['testlist_status'], [5,7])): ?>
                     <div class="action-links">
                        <a href="../view-results/index.php?id=<?=$row['testlist_id']+1200?>">Print Result</a>
                        <?php if($row['testlist_status'] != 7){
                            ?>
                              <a onclick="confirm_result(<?=$row['testlist_id']+1200?>)">Confirm result given to patient</a>
                        <?php } ?>
                     
                   </div>
                 <?php endif; ?>

                </td>
            </tr>
            <?php endwhile; ?>

        </form>
        </tbody>

    </table>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div style="margin-top:24px;display:flex;justify-content:center;">
        <ul style="list-style:none;display:flex;gap:8px;padding:0;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li>
                <a href="?page=<?= $p ?>
                    &appointment_id=<?= $appointment_id ?>
                    &start_date=<?= urlencode($start_date) ?>
                    &end_date=<?= urlencode($end_date) ?>
                    &search=<?= urlencode($search) ?>"
                   style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;
                   <?= $p == $page ? 'background:#007bff;color:#fff;' : '' ?>">
                   <?= $p ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>
    <div align="center" style="margin-top: 10px;"><button class="btn-primary" onclick="submit_test_form()">Submit</button></div>
</div>

</div>

<!-- PAYMENT MODAL -->
 <div class="modal-container">
    <div class="modal-content">
        <span class="close" onclick="closeModal()" style="cursor: pointer;text-align: right;">×</span>
        <!-- <form method="POST" action="reject.php">  -->
                <h2>Enter Reason for Rejection</h2>
                  <textarea id="rejectReason" placeholder="Enter reason..." name="reason"></textarea>
                  <button  id="confirm_btn">Confirm Reject</button>
                   <input type="hidden" name="patient_test_id" value="" id="my_test">
       <!--  </form> -->
    
    </div>
</div>


 <div class="modal-container md">
    <div class="modal-content mc">
        <span class="close" onclick="closeModal()" style="cursor: pointer;text-align: right;">×</span>
        <!-- <form method="POST" action="reject.php">  -->
                <h2>Result Collected By: </h2>
                   <select>
                        <option>Self</option>
                        <option>Other</option>
                   </select> <br>
                   <input type="text" id="other_name" class="form-control" style="display:none;" placeholder="Enter Collector name"> <br>
                   <button  id="confirm_btn" onclick="confirmCollection()">Confirm Collection</button>
       <!--  </form> -->
    
    </div>
</div>


<style>
    /* Modal Styles */

      .modal-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 20px;
        border-radius: 10px;
        max-width: 400px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .modal-content textarea {
        width: calc(100% - 40px);
        height: 100px;
        resize: none;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
    }

    .modal-content button {
        padding: 10px 20px;
        border: none;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .modal-content button:hover {
        background-color: #0056b3;
    }

    .modal-content h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 18px;
    }
  
</style>

<script>

  currentId =  0;
   function confirm_result(id){
    
       $('.md').css('display','flex');
      $('.md select').on('change',function(x){
       if($(this).val() == 'Other'){
             $('#other_name').css('display','block');
       }else{
          $('#other_name').css('display','none');
       }

       currentId = id;
            
     });




}


 function confirmCollection(){

     if($('.md select').val() == 'Other' && $('#other_name').val() == ''){
             alert('Enter Collector name');
          }else{
             var collector = ($('.md select').val() == 'Other') ? $('#other_name').val() : 'Self';
               if(confirm('Are you sure you have given result to patient')){
                    window.location.href = 'confirm.php?id='+currentId+'&collector='+collector;
                }
          }
 }
       



      function submit_reason(x,mid){
    

   if($(x).val() == -1){
       $('.modal-container').css('display','flex');
       $('#confirm_btn').on('click',function(){
         var mreason =  $('#rejectReason').val();
         if(mreason.trim() == ''){
            alert('Enter valid reason')
         }else{
             $('#input_reason_'+mid).val(mreason);
             $('#span_reason_'+mid).html(mreason);
              $('.modal-container').css('display','none');
         }
       })
   }
   }

    function submit_test_form(){
    $('#my_test_form').submit();
   }

    function closeModal(){
      $('#my_test').val("");
     $('.modal-container').css('display','none');
   }
</script>


<?php include '../includes/footer.php'; ?>
</main>

<script>
function deletePayment(id) {
    swal("Are you sure?",
         "This will permanently delete the payment.",
         "warning",
    {
        buttons: ["Cancel", "Yes, delete"]
    }).then((ok) => {
        if (ok) {
            window.location.href = 'delete.php?id=' + id;
        }
    });
}
</script>

</body>
</html>
