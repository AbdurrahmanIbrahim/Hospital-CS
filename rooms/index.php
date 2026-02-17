<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}


$location = 'rooms';
$action   = 'Add';
$room     = null;


$wards = [];
$sql = "SELECT * FROM wards WHERE status = 1";
$run = $db->query($sql);
while($row = $run->fetch_assoc())$wards[] = $row;

/* =========================
   EDIT MODE
========================= */
if (!empty($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM rooms WHERE id='$id'";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Room Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $room   = $run->fetch_assoc();
    $action = 'Update';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Room Management</title>
    <link rel="stylesheet" href="../styles/styles.css">

   <script>
function toggleAdmissionFields() {
    const type = document.getElementById('room_type').value;
    const admissionFields = document.getElementById('admissionFields');

    const bedSpace  = document.querySelector('[name="bed_space"]');
    const roomPrice = document.querySelector('[name="room_price"]');

    if (type == 1) {
        admissionFields.style.display = 'flex';

        bedSpace.disabled  = false;
        roomPrice.disabled = false;

        bedSpace.required  = true;
        roomPrice.required = true;

    } else {
        admissionFields.style.display = 'none';

        bedSpace.disabled  = true;
        roomPrice.disabled = true;

        bedSpace.required  = false;
        roomPrice.required = false;

        bedSpace.value  = '';
        roomPrice.value = '';
    }
}
</script>

</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="card">
        <div class="card-header">
            <h2><?= $action ?> Room</h2>
        </div>

        <div class="card-body" style="padding:32px;">
            <form method="POST" action="process.php">

                <!-- ROOM NAME -->
                <div class="form-group">
                    <label>Room Name *</label>
                    <input type="text" name="room_name" required
                           value="<?= $room['room_name'] ?? '' ?>">
                </div>

                  <!-- ROOM TYPE -->
                <div class="form-group">
                    <label>Ward *</label>
                    <select name="ward" id="ward" required>
                        <option value="">-- Select --</option>
                        <?php  foreach ($wards as $ward) {
                               ?>
                        <option value="<?=$ward['id']?>" <?= ($room['ward'] ?? '') == $ward['id'] ? 'selected' : '' ?>>
                            <?=$ward['ward_name']?>
                        </option>
                        <?php } ?>
                       
                    </select>
                </div>

                <!-- ROOM TYPE -->
                <div class="form-group">
                    <label>Room Type *</label>
                    <select name="room_type" id="room_type" required onchange="toggleAdmissionFields()">
                        <option value="">-- Select --</option>
                        <option value="0" <?= ($room['room_type'] ?? '') == 0 ? 'selected' : '' ?>>
                            Consultation Room
                        </option>
                        <option value="1" <?= ($room['room_type'] ?? '') == 1 ? 'selected' : '' ?>>
                            Admission Room
                        </option>
                    </select>
                </div>

                <!-- ADMISSION ONLY -->
                <div id="admissionFields" class="form-row"
                     style="display: <?= (($room['room_type'] ?? '') == 1) ? 'flex' : 'none' ?>">

                    <div class="form-group">
                        <label>Bed Space *</label>
                        <input type="number" name="bed_space" min="1"
                               value="<?= $room['bed_space'] ?? '1' ?>">
                    </div>

                    <div class="form-group">
                        <label>Room Price (₦) *</label>
                        <input type="number" name="room_price" step="0.01" min="0"
                               value="<?= $room['room_price'] ?? '1' ?>">
                    </div>
                </div>

                <!-- STATUS -->
             <!--    <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="1" <?= ($room['status'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($room['status'] ?? '') == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div> -->

                <!-- HIDDEN ID -->
                <?php if (!empty($room['id'])): ?>
                    <input type="hidden" name="id" value="<?= $room['id'] ?>">
                <?php endif; ?>

                <!-- SUBMIT -->
                <div class="form-group" style="text-align:right;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">
                        <?= $action ?> Room
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
    toggleAdmissionFields();
</script>

</body>
</html>
