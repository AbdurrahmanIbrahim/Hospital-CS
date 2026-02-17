<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'rooms';

$wards = [];
$sql = "SELECT * FROM wards WHERE status = 1";
$run = $db->query($sql);
while($row = $run->fetch_assoc())$wards[] = $row;

/* =========================
   Search & Pagination
========================= */
$w    = isset($_GET['w']) ? sanitize($_GET['w']) : '';
$doctor_search = isset($_GET['doctor_search']) ? sanitize($_GET['doctor_search']) : '';
$room_type     = isset($_GET['room_type']) ? sanitize($_GET['room_type']) : '';
$page          = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$limit  = 20;
$offset = ($page - 1) * $limit;

/* =========================
   Fetch Doctors (for filter / dropdown)
========================= */
$doctorWhere = "WHERE type = 3 AND status = 1";
if (!empty($doctor_search)) {
    $doctorWhere .= " AND name LIKE '%$doctor_search%'";
}

$doctorsResult = $db->query("
    SELECT id, name 
    FROM users 
    $doctorWhere 
    ORDER BY name ASC
");

$doctors = [];
while ($d = $doctorsResult->fetch_assoc()) {
    $doctors[] = $d;
}

/* =========================
   WHERE CLAUSE FOR ROOMS
========================= */
$where = "WHERE r.status = 1";

if ($room_type !== '') {
    $where .= " AND r.room_type = '$room_type'";
} else {
    // default → consultation rooms
    $where .= " AND r.room_type = 0";
}


if($w != ''){
    $where .= " AND r.ward = '$w'";  
}

/* =========================
   Pagination Count
========================= */
$countSql = "
    SELECT COUNT(*) AS total
    FROM rooms r
    $where
";

$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   Fetch Rooms + CURRENT Doctor
========================= */
$sql = "
    SELECT 
        r.id,
        r.room_name,
        r.room_type,

        ad.doctor_id,
        ad.status AS doctor_status,
        u.name AS doctor_name

    FROM rooms r

    /* Get LAST assignment per room */
    LEFT JOIN assign_doctors ad 
        ON ad.id = (
            SELECT ad2.id
            FROM assign_doctors ad2
            WHERE ad2.room_id = r.id
            ORDER BY ad2.id DESC
            LIMIT 1
        )

    LEFT JOIN users u ON u.id = ad.doctor_id

    $where
    ORDER BY r.id DESC
    LIMIT $limit OFFSET $offset
";

$rooms = $db->query($sql);

if (!$rooms) {
    echo $db->error;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Assign Doctors</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
 <style>
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
.table-actions a[href*="index.php"] {
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
        <h2>Assign Doctors to Consultation Rooms</h2>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- FILTER -->
     <div class="search-section" style="margin-bottom: 24px;">
    <form action="" method="GET" class="search-form">
        <div class="search-filters">

             <!-- Ward -->
            <div class="filter-group">
                <label for="ward" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                     Ward
                </label>
                <select name="w"
                        id="ward"
                        class="form-select"
                        style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 200px; background-color: white; cursor: pointer;">
                    <option value="">All Wards</option>
                      <?php  foreach ($wards as $ward) {
                               ?>
                        <option value="<?=$ward['id']?>"  <?= (isset($_GET['w']) && $_GET['w'] === $ward['id']) ? 'selected' : '' ?>>
                            <?=$ward['ward_name']?>
                        </option>
                        <?php } ?>
                </select>
            </div>

            <!-- Room Type -->
            <div class="filter-group">
                <label for="roomType" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Room Type
                </label>
                <select name="room_type" id="roomType" class="form-select"
                        style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 200px; background-color: white; cursor: pointer;">
                    <option value="">Consultation Rooms Only</option>
                    <option value="0" <?= ($room_type === '0') ? 'selected' : '' ?>>Consultation</option>
                    <option value="1" <?= ($room_type === '1') ? 'selected' : '' ?>>Admission</option>
                </select>
            </div>

            <!-- Doctor Search -->
            <div class="filter-group" style="flex-grow: 1; margin-left: 16px;">
                <label for="doctorSearch" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Search Doctor
                </label>
                <div class="search-input-wrapper" style="display: flex;">
                    <input type="text"
                           name="doctor_search"
                           id="doctorSearch"
                           value="<?= htmlspecialchars($_GET['doctor_search'] ?? '') ?>"
                           placeholder="Enter doctor name..."
                           style="flex-grow: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px 0 0 6px; font-size: 14px; border-right: none;">
                    <button type="submit"
                            class="search-btn"
                            style="padding: 10px 20px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Reset -->
            <div class="filter-group" style="margin-left: 16px; align-self: flex-end;">
                <a href="assign.php"
                   class="reset-btn"
                   style="display: inline-block; padding: 10px 16px; background-color: #6c757d; color: white; border: 1px solid #6c757d; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background-color 0.3s;">
                    Reset
                </a>
            </div>

        </div>
    </form>
</div>


        <?php if ($rooms->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">No consultation rooms found.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Room Name</th>
                    <th>Current Doctor</th>
                     <th>Doctor Status</th>
                    <th>Assign Doctor</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = $offset + 1; while ($row = $rooms->fetch_assoc()):
                                   // Doctor status logic
                    if ($row['doctor_id'] === null) {
                        $statusText  = 'Not Assigned';
                        $statusColor = '#6c757d'; // gray
                        $doctorName  = '—';
                    } elseif ($row['doctor_status'] == 1) {
                        $statusText  = 'On Seat';
                        $statusColor = '#28a745'; // green
                        $doctorName  = htmlspecialchars($row['doctor_name']);
                    } else {
                        $statusText  = 'Not On Seat';
                        $statusColor = '#dc3545'; // red
                        $doctorName  = htmlspecialchars($row['doctor_name']);
                    }
                   
             ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['room_name']) ?></td>
                    <td><?= $doctorName ?? '-' ?></td>
                       <td>
                        <span style="
                            padding:6px 12px;
                            border-radius:20px;
                            font-size:13px;
                            font-weight:600;
                            color:white;
                            background-color: <?= $statusColor ?>;
                        ">
                            <?= $statusText ?>
                        </span>
                     </td>
                    <td>
                        <form method="POST" action="assign_process.php" style="display:flex;gap:8px;" class="my-form_<?=$i?>">
                            <input type="hidden" name="room_id" value="<?= $row['id'] ?>">
                            <select name="doctor_id" onchange="document.querySelector('.my-form_<?=$i?>').submit()" required>
                                <option value="">Select Doctor</option>
                                <?php foreach($doctors as $doc): ?>
                                    <option value="<?= $doc['id'] ?>" <?= ($row['doctor_id']??'')==$doc['id']?'selected':'' ?>>
                                        <?= htmlspecialchars($doc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
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
                    <a href="?page=<?= $p ?>&doctor_search=<?= urlencode($search) ?>&room_type=<?= urlencode($room_type) ?>"
                       style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;<?= $p==$page?'background:#007bff;color:#fff;':'' ?>">
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
</body>
</html>
