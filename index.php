<?php
   include 'functions.php';
  if(!isLoggedIn()){
    $_SESSION['error'] = 'Login To Continue';
      echo "<script>window.location.href='login/index.php'</script>";
      exit;
   }

   $location = 'dashboard';

   $sql = "SELECT 
    a.id AS appointment_id,
    a.date_appointed,
    a.date_ended,
    a.nurse_notes,
    a.room_id,
    a.diagnosis,
    a.clinical_notes,
    a.doctor_id,
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
WHERE 1
AND u.type = 1
LIMIT 1;
";
$run = $db->query($sql);
// if($run->num_rows == 0){
//      $_SESSION['error'] = 'Appointment Not Found';
//         echo "<script>window.history.back()</script>";
//         exit;
// }


$appointments = [];

while($row = $run->fetch_assoc())$appointments[] = $row;



 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=APP_NAME?> | Hospital Management</title>
    <link rel="stylesheet" type="text/css" href="styles/styles.css">
</script>
</head>
<body>

    <!-- Sidebar -->
     
     <?php include 'includes/side_nav.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
          

          <?php include 'includes/header.php' ?>

        <div class="content-scroll">
            <?php if($_SESSION['type'] == 0){
                ?>
                  <div class="view-header">
                <div>
                    <h1>Hospital Overview</h1>
                    <p>Track your hospital performance and patient records.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button style="background: white; border: 1px solid var(--border); padding: 10px 16px; border-radius: 10px; cursor: pointer;">Print Report</button>
                </div>
            </div>
            <?php
// Fetch total patients
$sql_patients = "SELECT COUNT(*) AS total_patients FROM users WHERE type = 1 AND status = 1";
$result_patients = $db->query($sql_patients);
$total_patients = $result_patients ? $result_patients->fetch_assoc()['total_patients'] : 0;

// Fetch total staff
$sql_staff = "SELECT COUNT(*) AS total_staff FROM users WHERE type != 0 AND type != 1 AND status = 1";
$result_staff = $db->query($sql_staff);
$total_staff = $result_staff ? $result_staff->fetch_assoc()['total_staff'] : 0;

// Fetch total rooms (example: replace with your rooms table)
$sql_rooms = "SELECT COUNT(*) AS total_rooms FROM rooms WHERE status = 1"; 
$result_rooms = $db->query($sql_rooms);
$total_rooms = $result_rooms ? $result_rooms->fetch_assoc()['total_rooms'] : 0;

// Fetch available beds (example: replace with your beds table and availability status)
$sql_beds = "SELECT SUM(bed_space) AS available_beds FROM rooms WHERE status = '1'";
$result_beds = $db->query($sql_beds);
$available_beds = $result_beds ? $result_beds->fetch_assoc()['available_beds'] : 0;
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap" style="background: #eff6ff; color: #2563eb;">👤</div>
        </div>
        <h3><?= $total_patients ?></h3>
        <p>Total Patients</p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap" style="background: #fdf2f8; color: #db2777;">👤</div>
        </div>
        <h3><?= $total_staff ?></h3>
        <p>Total Staff</p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap" style="background: #f0fdf4; color: #16a34a;">🩺</div>
        </div>
        <h3><?= $total_rooms ?></h3>
        <p>Total Rooms</p>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap" style="background: #fffbeb; color: #d97706;">🛏️</div>
        </div>
        <h3><?= $available_beds ?></h3>
        <p>Available Beds</p>
    </div>
</div>


                <div class="dashboard-row">
                <!-- Main Table Card -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Appointments</h2>
                        <a href="#" style="color: var(--primary); font-size: 13px; font-weight: 600; text-decoration: none;">View All</a>
                    </div>
                  <table>
    <thead>
        <tr>
            <th>Patient Name</th>
            <th>Doctor</th>
            <th>Check-in</th>
            <th>Diagnosis</th>
       <!--      <th>Status</th> -->
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($appointments)): ?>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td style="font-weight: 600;"><?= htmlspecialchars($appointment['patient_name']) ?></td>
                    <td><?= htmlspecialchars(get("name","users",$appointment['doctor_id'])) ?></td>
                    <td><?= htmlspecialchars(formatDateReadable($appointment['date_appointed'])) ?></td>
            <td><?= htmlspecialchars($appointment['diagnosis']) ?></td>
                 <!--    <td>
                        <span class="status-badge>">
                            <?= htmlspecialchars($appointment['appointment_status']) ?>
                        </span>
                    </td> -->
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center; color:#666;">No appointments found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

                </div>

                <!-- Side Info Card -->
               <!--  <div class="card">
                    <div class="card-header">
                        <h2>Patient Status</h2>
                    </div>
                    <div class="patient-list">
                        <div class="patient-item">
                            <div class="patient-info">
                                <h4>Elena Rodriguez</h4>
                                <p>Room 302 • Recovering</p>
                            </div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--success);"></div>
                        </div>
                        <div class="patient-item">
                            <div class="patient-info">
                                <h4>Marcus Thorne</h4>
                                <p>ICU • Critical</p>
                            </div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--danger);"></div>
                        </div>
                        <div class="patient-item">
                            <div class="patient-info">
                                <h4>Lila Vance</h4>
                                <p>Room 105 • Stable</p>
                            </div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></div>
                        </div>
                        <div class="patient-item">
                            <div class="patient-info">
                                <h4>Lila Vance</h4>
                                <p>Room 201 • Stable</p>
                            </div>
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></div>
                        </div>
                    </div>
                </div> -->
            </div>

            <?php } ?>
          

         

        
        </div>
    </main>
      <?php include 'includes/footer.php'; ?>
</body>
</html>