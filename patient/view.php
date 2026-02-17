<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'patient';

/* =========================
   Search & Pagination Setup
========================= */
$search   = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit    = 20;
$offset   = ($page - 1) * $limit;

/* =========================
   WHERE (PATIENTS ONLY)
========================= */
$where = "WHERE type = 1 AND status != -1";

if (!empty($search)) {
    $where .= " AND (
        name LIKE '%$search%' OR
        email LIKE '%$search%' OR
        phone LIKE '%$search%' OR
        hospital_num LIKE '%$search%'
    )";
}

/* =========================
   Pagination Count
========================= */
$countSql   = "SELECT COUNT(*) AS total FROM users $where";
$totalRows  = $db->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* =========================
   Fetch Patients
========================= */
$sql = "
    SELECT id, name, email, phone, gender, hospital_num, status
    FROM users
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";

$patients = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Patients</title>
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
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Patient List</h2>
        <a href="index.php" class="btn btn-primary">+ Add Patient</a>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- SEARCH -->
        <div class="search-section" style="margin-bottom:24px;">
            <form method="GET">
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <input type="text"
                           name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search name, phone, hospital no..."
                           style="padding:10px;flex:1;border:1px solid #ddd;border-radius:6px;">
                    <button class="btn btn-primary">Search</button>
                    <a href="?" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <?php if ($patients->num_rows == 0): ?>
            <p style="text-align:center;padding:30px;color:#777;">
                No patient records found.
            </p>
        <?php else: ?>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Hospital No</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php $i = $offset + 1; while ($row = $patients->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['hospital_num']) ?></td>
                    <td><?= htmlspecialchars($row['gender']) ?></td>
                    <td>
                        <?= $row['status'] == 1
                            ? '<span class="status-active">Active</span>'
                            : '<span class="status-inactive">Inactive</span>' ?>
                    </td>
                    <td class="table-actions">
                         <a onclick="open_modal(<?= $row['id'] ?>)" class="modal_btn">View</a>
                        <a href="index.php?id=<?= $row['id'] ?>">Edit</a>
                        <?php if($row['status'] == 1){
                            ?>
                         <a href="book.php?patient_id=<?= $row['id'] ?>" class="modal_btn">
                              Book Appointment
                         </a>
                        <?php } ?>
                       
                        <a onclick="deletePatient(<?= $row['id'] ?>)" class="delete-btn">Delete</a>
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
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"
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
function deletePatient(id) {
    swal("Are you sure?", "This will delete the patient permanently.", "warning", {
        buttons: ["Cancel", "Yes, delete"]
    }).then((ok) => {
        if (ok) window.location.href = 'delete.php?id=' + id;
    });
}
</script>

<!-- Add this modal HTML before the closing </body> tag -->
<div id="patientModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Patient Details</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="patient-details">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn btn-primary" onclick="editPatient()">Edit Patient</button>
        </div>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        position: relative;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        margin: 2% auto;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        animation: slideUp 0.4s ease;
        overflow: hidden;
    }

    .modal-header {
        padding: 20px 30px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 30px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 20px 30px;
        background: #f8f9fa;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    /* Patient Details Styling */
    .patient-details .detail-section {
        margin-bottom: 30px;
        background: #f8fafc;
        padding: 20px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
    }

    .patient-details .detail-section:last-child {
        margin-bottom: 0;
    }

    .patient-details h3 {
        color: var(--primary-dark);
        font-weight: 600;
        padding-bottom: 10px;
        margin: 0 0 20px 0;
        border-bottom: 2px solid var(--primary);
        position: relative;
        font-size: 1.3rem;
        letter-spacing: 0.5px;
    }

    .patient-details h3:before {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 3px 3px 0 0;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        margin-bottom: 15px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 6px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-label i {
        color: var(--primary);
        width: 16px;
    }

    .detail-value {
        padding: 10px 15px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        min-height: 42px;
        display: flex;
        align-items: center;
        color: var(--text-main);
        font-size: 0.95rem;
        word-break: break-word;
    }

    .detail-value.empty {
        color: var(--text-muted);
        font-style: italic;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        gap: 6px;
    }

    .status-badge.active {
        background: rgba(10, 122, 10, 0.1);
        color: #0a7a0a;
        border: 1px solid rgba(10, 122, 10, 0.2);
    }

    .status-badge.inactive {
        background: rgba(192, 57, 43, 0.1);
        color: #c0392b;
        border: 1px solid rgba(192, 57, 43, 0.2);
    }

    .status-badge i {
        font-size: 0.8rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10px auto;
            max-height: 95vh;
        }

        .modal-header {
            padding: 15px 20px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            flex-direction: column;
        }

        .modal-footer button {
            width: 100%;
        }

        .detail-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
</style>

<script>
// Global variable to store current patient ID
let currentPatientId = null;

function open_modal(patientId) {
    currentPatientId = patientId;
    const modal = document.getElementById('patientModal');
    
    // Show loading state
    document.querySelector('.patient-details').innerHTML = `
        <div style="text-align: center; padding: 50px 20px;">
            <div style="margin-bottom: 20px; color: var(--primary);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <p style="color: var(--text-muted);">Loading patient details...</p>
        </div>
    `;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Fetch patient details via AJAX
    fetch(`get_patient_details.php?id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatientDetails(data.patient);
            } else {
                showError('Failed to load patient details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error occurred');
        });
}

function displayPatientDetails(patient) {
    const detailsContainer = document.querySelector('.patient-details');
    
    const formatDate = (dateString) => {
        if (!dateString) return 'Not specified';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatText = (text) => {
        return text || 'Not specified';
    };

    const statusBadge = patient.status == 1 
        ? '<span class="status-badge active"><i>✓</i> Active</span>'
        : '<span class="status-badge inactive"><i>✗</i> Inactive</span>';

    detailsContainer.innerHTML = `
        <!-- Patient Header -->
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">
                ${patient.name?.charAt(0) || 'P'}
            </div>
            <div>
                <h3 style="margin: 0 0 8px 0; color: var(--text-main);">${formatText(patient.name)}</h3>
                <p style="margin: 0 0 5px 0; color: var(--text-muted);">
                    <i style="color: var(--primary);">📧</i> ${formatText(patient.email)}
                </p>
                <p style="margin: 0 0 5px 0; color: var(--text-muted);">
                    <i style="color: var(--primary);">📱</i> ${formatText(patient.phone)}
                </p>
                <div style="margin-top: 10px;">
                    ${statusBadge}
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="detail-section">
            <h3>Basic Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label"><i>👤</i> Full Name</div>
                    <div class="detail-value">${formatText(patient.name)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>📧</i> Email</div>
                    <div class="detail-value">${formatText(patient.email)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>📱</i> Phone</div>
                    <div class="detail-value">${formatText(patient.phone)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>🏥</i> Hospital No</div>
                    <div class="detail-value">${formatText(patient.hospital_num) || 'Not assigned'}</div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="detail-section">
            <h3>Personal Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label"><i>🎂</i> Date of Birth</div>
                    <div class="detail-value">${formatDate(patient.dob)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>⚥</i> Gender</div>
                    <div class="detail-value">${formatText(patient.gender)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>💍</i> Marital Status</div>
                    <div class="detail-value">${formatText(patient.marital_status)}</div>
                </div>
                  <div class="detail-item">
                    <div class="detail-label"><i>📍</i> State</div>
                    <div class="detail-value">${formatText(patient.state_name)}</div>
                </div>
                   <div class="detail-item">
                    <div class="detail-label"><i>🏘️</i> LGA</div>
                    <div class="detail-value">${formatText(patient.lga_name)}</div>
                </div>
                   <div class="detail-item">
                    <div class="detail-label"><i>👥</i> Tribe</div>
                    <div class="detail-value">${formatText(patient.tribe)}</div>
                </div>
                <div class="detail-item full-width">
                    <div class="detail-label"><i>📍</i> Address</div>
                    <div class="detail-value ${!patient.address ? 'empty' : ''}">
                        ${formatText(patient.address)}
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="detail-section">
            <h3>Medical Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label"><i>🩸</i> Blood Group</div>
                    <div class="detail-value">${formatText(patient.blood_group)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>🧬</i> Genotype</div>
                    <div class="detail-value">${formatText(patient.genotype)}</div>
                </div>
                <div class="detail-item full-width">
                    <div class="detail-label"><i>⚠️</i> Known Allergies</div>
                    <div class="detail-value ${!patient.allergies ? 'empty' : ''}">
                        ${formatText(patient.allergies)}
                    </div>
                </div>
                <div class="detail-item full-width">
                    <div class="detail-label"><i>🏥</i> Chronic Conditions</div>
                    <div class="detail-value ${!patient.chronic_condition ? 'empty' : ''}">
                        ${formatText(patient.chronic_condition)}
                    </div>
                </div>
            </div>
        </div>

        <!-- Next of Kin -->
        <div class="detail-section">
            <h3>Next of Kin</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label"><i>👥</i> Next of Kin</div>
                    <div class="detail-value">${formatText(patient.kin)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>📞</i> Next of Kin Phone</div>
                    <div class="detail-value">${formatText(patient.kin_phone)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label"><i>🤝</i> Relationship</div>
                    <div class="detail-value">${formatText(patient.kin_relationship)}</div>
                </div>
            </div>
        </div>



        <!-- Hospital Info -->
    <div class="detail-section">
    <h3>Additional Information</h3>
    <div class="detail-grid">

        <div class="detail-item">
            <div class="detail-label"><i>🛡️</i> Scheme Type</div>
            <div class="detail-value">${formatText(patient.scheme_name)}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label"><i>📂</i> File Type</div>
            <div class="detail-value">${formatText(patient.file_type_name)}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label"><i>👨‍👩‍👧‍👦</i> Family</div>
            <div class="detail-value">${formatText(patient.family_name)}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label"><i>🧾</i> Receipt</div>
            <div class="detail-value">${formatText(patient.reciept_num)}</div>
        </div>

    </div>
</div>

    `;
}

function showError(message) {
    document.querySelector('.patient-details').innerHTML = `
        <div style="text-align: center; padding: 50px 20px;">
            <div style="margin-bottom: 20px; color: var(--danger);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12" y2="16"/>
                </svg>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 20px;">${message}</p>
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    `;
}

function closeModal() {
    const modal = document.getElementById('patientModal');
    modal.style.animation = 'fadeOut 0.3s ease';
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        modal.style.animation = '';
    }, 250);
}

function editPatient() {
    if (currentPatientId) {
        window.location.href = `index.php?id=${currentPatientId}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('patientModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

</script>

</body>
</html>
