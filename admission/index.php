<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admission';

/* =========================
   VALIDATE APPOINTMENT
========================= */
if (empty($_GET['appointment_id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment_id = intval($_GET['appointment_id']);

// Fetch appointment + patient info
$sql = "SELECT
    a.id AS appointment_id,
    a.patient_id,
    a.status AS appointment_status,
    u.name AS patient_name,
    u.hospital_num,
    u.phone,
    u.gender,
    u.dob
FROM appointments a
INNER JOIN users u ON u.id = a.patient_id
WHERE a.id = '$appointment_id' AND u.type = 1
LIMIT 1";
$run = $db->query($sql);

if ($run->num_rows == 0) {
    $_SESSION['error'] = 'Appointment Not Found';
    echo "<script>window.history.back()</script>";
    exit;
}

$appointment = $run->fetch_assoc();
$patient_id = $appointment['patient_id'];

// Check if patient already has active admission
$activeAdmission = getActiveAdmission($patient_id);
if ($activeAdmission) {
    $_SESSION['error'] = 'Patient already has an active admission';
    echo "<script>window.history.back()</script>";
    exit;
}

// Fetch admission rooms (room_type = 1) with available beds
$rooms = [];
$sql = "SELECT r.*, w.ward_name FROM rooms r LEFT JOIN wards w ON w.id = r.ward WHERE r.room_type = 1 AND r.status = 1 ORDER BY r.room_name ASC";
$run = $db->query($sql);
while ($row = $run->fetch_assoc()) {
    $row['available_beds'] = getAvailableBeds($row['id']);
    $rooms[] = $row;
}

// Get doctor id
$doctor_id = getId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Admit Patient</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header">
        <div>
            <h1>Admit Patient</h1>
            <p>Assign a room and bed for patient admission</p>
        </div>
        <div class="header-actions">
            <a href="../doctors-desk/index.php?id=<?= $appointment_id ?>" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back to Consultation
            </a>
        </div>
    </div>

    <!-- Patient Info Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2>Patient Information</h2>
            <span class="badge badge-primary" style="background:#dbeafe;color:#1d4ed8;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">Appointment #<?= $appointment_id ?></span>
        </div>
        <div class="card-body" style="padding: 24px;">
            <div class="form-row">
                <div class="form-group">
                    <label>Patient Name</label>
                    <p style="font-size:16px;font-weight:600;"><?= htmlspecialchars($appointment['patient_name']) ?></p>
                </div>
                <div class="form-group">
                    <label>Hospital Number</label>
                    <p style="font-size:16px;font-weight:600;"><?= htmlspecialchars($appointment['hospital_num']) ?></p>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <p style="font-size:16px;"><?= htmlspecialchars($appointment['phone']) ?></p>
                </div>
                <div class="form-group">
                    <label>Gender / Age</label>
                    <p style="font-size:16px;"><?= htmlspecialchars($appointment['gender']) ?> | <?= getAge($appointment['dob']) ?> yrs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admission Form -->
    <div class="card">
        <div class="card-header">
            <h2>Admission Details</h2>
        </div>
        <div class="card-body" style="padding: 32px;">
            <form method="POST" action="process.php">
                <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">
                <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

                <!-- Room Selection -->
                <div class="form-group">
                    <label>Select Room *</label>
                    <select name="room_id" id="room_id" required onchange="updateBedOptions()">
                        <option value="">-- Select Admission Room --</option>
                        <?php foreach ($rooms as $room): ?>
                            <?php if ($room['available_beds'] > 0): ?>
                            <option value="<?= $room['id'] ?>"
                                    data-beds="<?= $room['available_beds'] ?>"
                                    data-total-beds="<?= $room['bed_space'] ?>"
                                    data-price="<?= number_format($room['room_price'], 2) ?>">
                                <?= htmlspecialchars($room['room_name']) ?>
                                (<?= htmlspecialchars($room['ward_name'] ?? 'No Ward') ?>)
                                - <?= $room['available_beds'] ?>/<?= $room['bed_space'] ?> beds available
                                - &#8358;<?= number_format($room['room_price'], 2) ?>/day
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bed Number -->
                <div class="form-group">
                    <label>Bed Number *</label>
                    <select name="bed_number" id="bed_number" required>
                        <option value="">-- Select Room First --</option>
                    </select>
                </div>

                <!-- Room Price Display -->
                <div class="form-group" id="priceDisplay" style="display:none;">
                    <label>Daily Room Rate</label>
                    <p style="font-size:20px;font-weight:700;color:var(--primary);" id="roomPrice"></p>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label>Admission Notes</label>
                    <textarea name="notes" rows="4" placeholder="Reason for admission, special instructions, etc."></textarea>
                </div>

                <!-- Submit -->
                <div class="form-group" style="text-align: right; margin-top: 24px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 32px; font-size: 15px;">
                        Admit Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
function updateBedOptions() {
    var select = document.getElementById('room_id');
    var bedSelect = document.getElementById('bed_number');
    var priceDisplay = document.getElementById('priceDisplay');
    var roomPrice = document.getElementById('roomPrice');

    bedSelect.innerHTML = '<option value="">-- Select Bed --</option>';

    if (select.value) {
        var option = select.options[select.selectedIndex];
        var totalBeds = parseInt(option.getAttribute('data-total-beds'));
        var price = option.getAttribute('data-price');

        for (var i = 1; i <= totalBeds; i++) {
            bedSelect.innerHTML += '<option value="' + i + '">Bed ' + i + '</option>';
        }

        roomPrice.innerHTML = '&#8358;' + price + ' per day';
        priceDisplay.style.display = 'block';
    } else {
        priceDisplay.style.display = 'none';
    }
}
</script>

</body>
</html>
