<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'appointments';

/* =========================
   FILTER & PAGINATION
========================= */
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date   = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 20;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE CLAUSE (DATE LOGIC)
========================= */
$where = "WHERE 1";

// Case 1: No dates → TODAY
if (empty($start_date) && empty($end_date)) {

    $today = date('Y-m-d');
    $where .= " AND DATE(a.date_appointed) = '$today'";

}
// Case 2: Start date only → THAT DAY ONLY
elseif (!empty($start_date) && empty($end_date)) {

    $where .= " AND DATE(a.date_appointed) = '$start_date'";

}
// Case 3: Start + End date → RANGE
elseif (!empty($start_date) && !empty($end_date)) {

    $where .= " AND DATE(a.date_appointed) BETWEEN '$start_date' AND '$end_date'";

}


/* =========================
   WHERE CLAUSE (DOCTOR)
========================= */

 if($_SESSION['type'] == 3){

       $doctor_id = getId();

       $sql = "SELECT room_id FROM assign_doctors WHERE doctor_id = '$doctor_id' AND status = 1";
       $run = $db->query($sql);
       if($run->num_rows == 0){
         
         $_SESSION['error'] = 'Doctors Room Not Found';
         echo "<script>window.history.back()</script>";
         exit;

       }

       $roomInfo = $run->fetch_assoc();

       $room_id = $roomInfo['room_id'];

       $where .=" AND a.room_id = '$room_id'";
       $where .=" AND a.status = 1";



 }


/* =========================
   FILTER TEXT
========================= */
$filterText = "Appointments";

if (empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>today</strong>";
}
elseif (!empty($start_date) && empty($end_date)) {
    $filterText .= " for <strong>" . formatDateReadable($start_date) . "</strong>";
}
elseif (!empty($start_date) && !empty($end_date)) {
    $filterText .= " from <strong>" . formatDateReadable($start_date) . "</strong> 
                     to <strong>" . formatDateReadable($end_date) . "</strong>";
}


/* =========================
   COUNT FOR PAGINATION
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM appointments a
    JOIN users u ON u.id = a.patient_id
    $where
";

$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   FETCH APPOINTMENTS
========================= */
$sql = "
    SELECT 
        a.id,
        a.date_appointed,
        a.date_ended,
        a.status,
        u.name AS patient_name,
        u.phone,
        u.hospital_num
    FROM appointments a
    JOIN users u ON u.id = a.patient_id
    $where
    ORDER BY a.id DESC
    LIMIT $limit OFFSET $offset
";

$appointments = $db->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Appointments</title>
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

    </div>
</form>
</div>


        <?php if ($appointments->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">
                No appointments found.
            </p>
        <?php else: ?>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Hospital No</th>
                    <th>Phone</th>
                    <th>Date Appointed</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php $i = $offset + 1; while ($row = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    <td><?= htmlspecialchars($row['hospital_num']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($row['date_appointed'])) ?></td>
                    <td>
                        <?= $row['date_ended']
                            ? date('d M Y H:i', strtotime($row['date_ended']))
                            : '-' ?>
                    </td>
                    <td>
                        <?php if($row['status'] == -1): ?>
                            <span style="color:#c0392b;font-weight:600;">Pending Payment</span>
                        <?php elseif($row['status'] == 0): ?>
                            <span style="color:#e67e22;font-weight:600;">Active</span>
                        <?php else: ?>
                            <span style="color:#0a7a0a;font-weight:600;">Completed</span>
                        <?php endif; ?>
                    </td>

                    <td class="table-actions">
                        <a onclick="deleteAppointment(<?= $row['id'] ?>)"
                           class="delete-btn">Delete</a>
                    </td>
                  <?php if(($_SESSION['type'] == 0 OR $_SESSION['type'] == 3) && $row['status'] >= 0){
                     ?>
                    <td class="table-actions">
                        <a href="../doctors-desk/index.php?id=<?= $row['id'] ?>"> Attend Patient</a>
                    </td>
                  <?php } ?>
                 
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div style="margin-top:24px;display:flex;justify-content:center;">
            <ul style="list-style:none;display:flex;gap:8px;padding:0;">
                <?php for ($p=1;$p<=$totalPages;$p++): ?>
                <li>
                    <a href="?page=<?= $p ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>"
                       style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;
                       <?= $p==$page?'background:#007bff;color:#fff;':'' ?>">
                       <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
function deleteAppointment(id) {
    swal("Are you sure?",
         "This will permanently delete the appointment.",
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
