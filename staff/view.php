<?php
include '../functions.php';

if (!isLoggedIn() || $_SESSION['type'] != 0) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admin';


/* =========================
   Search & Pagination Setup
========================= */
$search   = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$type     = isset($_GET['type']) ? intval($_GET['type']) : '';
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit    = 20; // records per page
$offset   = ($page - 1) * $limit;

/* =========================
   Build WHERE Clause
========================= */
$where = "WHERE type != 0 AND type != 1 AND status = 1";

if (!empty($type)) {
    $where .= " AND type = '$type'";
}

if (!empty($search)) {
    $where .= " AND (
        name LIKE '%$search%' OR
        email LIKE '%$search%' OR
        phone LIKE '%$search%'
    )";
}

/* =========================
   Total Records (for pagination)
========================= */
$countSql = "SELECT COUNT(*) AS total FROM users $where";
$totalRes = $db->query($countSql)->fetch_assoc();
$totalRows = $totalRes['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   Fetch Staff Records
========================= */
$sql = "
    SELECT id, name, email, phone, userno, category, status, type
    FROM users
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";

$staff = $db->query($sql);


$userTypes = getTypes();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Staff Management</title>
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">

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

<!-- Sidebar -->
<?php include '../includes/side_nav.php'; ?>

<!-- Main Content -->
<main class="main-content">

<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Staff List</h2>
            <a href="index.php" class="btn btn-primary">+ Add Staff</a>
        </div>

        <div class="card-body" style="padding:24px;">
            <!-- Add this after the card-header div, before the table/no-staff message -->
<div class="search-section" style="margin-bottom: 24px;">
    <form action="" method="GET" class="search-form">
        <div class="search-filters">
            <div class="filter-group">
                <label for="staffType" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Staff Type
                </label>
                <select name="type" id="staffType" class="form-select" style="padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 200px; background-color: white; cursor: pointer;">
                    <option value="">All Types</option>
                    <?php foreach ($userTypes as $typeId => $typeName): 
                        if($typeId != 0 && $typeId != 1): ?>
                        <option value="<?= $typeId ?>" <?= (isset($_GET['type']) && $_GET['type'] == $typeId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($typeName) ?>
                        </option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group" style="flex-grow: 1; margin-left: 16px;">
                <label for="searchQuery" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    Search (Name, Email, Phone)
                </label>
                <div class="search-input-wrapper" style="display: flex;">
                    <input type="text" 
                           name="search" 
                           id="searchQuery" 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                           placeholder="Enter name, email or phone number..."
                           style="flex-grow: 1; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px 0 0 6px; font-size: 14px; border-right: none;">
                    <button type="submit" 
                            class="search-btn"
                            style="padding: 10px 20px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 0 6px 6px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;">
                        Search
                    </button>
                </div>
            </div>
            
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

            <?php if ($staff->num_rows == 0): ?>
                <p>No staff records found.</p>
            <?php else: ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Staff ID</th>
                            <th>Staff Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = $offset + 1; while ($row = $staff->fetch_assoc()): 
                        $user_type = !empty($userTypes[$row['type']]) ? $userTypes[$row['type']] : 'Nill'
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['userno']) ?></td>
                                <td><?= htmlspecialchars($user_type) ?></td>
                                <td>
                                    <?php if ($row['status'] == 1): ?>
                                        <span class="status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="index.php?id=<?= $row['id'] ?>" class="">
                                        Edit
                                    </a>

                                
                                        <a onclick="deleteStaff(<?= $row['id'] ?>)"
                                           class="delete-btn">
                                           Delete
                                        </a>
                                    
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                  <?php if ($totalPages > 1): ?>
<div style="margin-top: 24px; display: flex; justify-content: center;">
    <ul style="list-style:none; display:flex; gap:8px; padding:0;">

        <!-- Previous -->
        <?php if ($page > 1): ?>
            <li>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>"
                   style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;text-decoration:none;">
                    &laquo;
                </a>
            </li>
        <?php endif; ?>

        <!-- Pages -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li>
                <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>"
                   style="
                        padding:8px 14px;
                        border:1px solid #ddd;
                        border-radius:6px;
                        text-decoration:none;
                        <?= $p == $page ? 'background:#007bff;color:#fff;' : '' ?>
                   ">
                    <?= $p ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Next -->
        <?php if ($page < $totalPages): ?>
            <li>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>"
                   style="padding:8px 14px;border:1px solid #ddd;border-radius:6px;text-decoration:none;">
                    &raquo;
                </a>
            </li>
        <?php endif; ?>

    </ul>
</div>
<?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
<script type="text/javascript">
     function deleteStaff(id) {
    swal("Are you sure?", "This action will delete the staff permanently.", "warning", {
      buttons: ["Cancel", "Yes, delete"],
    })
    .then((willDelete) => {
      if (willDelete) {
        window.location.href = 'delete.php?id=' + id;
      }
    });
  }
</script>
</main>

</body>
</html>
