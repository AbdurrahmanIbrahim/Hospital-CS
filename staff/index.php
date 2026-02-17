<?php
include '../functions.php';

if (!isLoggedIn() or $_SESSION['type']!=0) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'admin';
$action = 'Add';
$staff = null;

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id='$id' AND type != 0 AND status = 1 AND type!=1";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Staff Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $staff = $run->fetch_assoc();
    $action = 'Update';
}

$userTypes = getTypes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Staff Management</title>
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
</head>

<body>

<!-- Sidebar -->
<?php include '../includes/side_nav.php'; ?>

<!-- Main Content -->
<main class="main-content">

<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="card">
        <div class="card-header">
            <h2><?= $action ?> Staff</h2>
        </div>

        <div class="card-body" style="padding:32px;">
            <form method="POST" action="process.php">

                <!-- Staff Name -->
                <div class="form-group">
                    <label>Staff Name *</label>
                    <input type="text" name="name" required
                        value="<?= isset($staff['name']) ? htmlspecialchars($staff['name']) : '' ?>">
                </div>

                <!-- Email & Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" required
                            value="<?= isset($staff['email']) ? htmlspecialchars($staff['email']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" required
                            value="<?= isset($staff['phone']) ? htmlspecialchars($staff['phone']) : '' ?>">
                    </div>
                </div>

                <!-- Staff ID & Type -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Staff ID *</label>
                        <input type="text" name="staff_id" required
                            value="<?= isset($staff['userno']) ? htmlspecialchars($staff['userno']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Staff Type *</label>
                        <select name="staff_type" required>
                            <option value="">-- Select Staff Type --</option>
                            <?php foreach($userTypes as $k=>$v){
                                 ?>
                                   <option value="<?=$k?>" <?= (isset($staff['type']) && $staff['type']==$k)?'selected':'' ?>><?=$v?></option>
                            <?php }  ?>
                          
                      
                        </select>
                    </div>
                </div>

                <!-- Hidden ID -->
                <?php if (!empty($staff['id'])): ?>
                    <input type="hidden" name="id" value="<?= $staff['id'] ?>">
                <?php endif; ?>

                <!-- Submit -->
                <div class="form-group" style="text-align:right; margin-top:24px;">
                    <button type="submit" class="btn btn-primary">
                        <?= $action ?> Staff
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

</body>
</html>
