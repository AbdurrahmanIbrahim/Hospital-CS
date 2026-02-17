<?php
include '../functions.php';

if (!isLoggedIn() || $_SESSION['type'] != 0) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Sanitize Inputs
========================= */
$id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name     = sanitize($_POST['hospital_name']);
$email    = sanitize($_POST['hospital_email']);
$phone    = sanitize($_POST['hospital_phone']);
$address  = sanitize($_POST['hospital_address']);
$website  = sanitize($_POST['hospital_website']);
$year     = intval($_POST['established_year']);
$user_id  = getId();

/* =========================
   Duplicate Check (by name)
========================= */
if ($id > 0) {
    $check = "SELECT * FROM hospital_details WHERE name='$name' AND id!='$id'";
} else {
    $check = "SELECT * FROM hospital_details WHERE name='$name'";
}

$run = $db->query($check);
if ($run->num_rows > 0) {
    $_SESSION['error'] = "Hospital already exists";
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Fetch Existing Record
========================= */
$oldLogoPath = '';
if ($id > 0) {
    $q = $db->query("SELECT * FROM hospital_details WHERE id='$id'");
    if ($q->num_rows == 0) {
        $_SESSION['error'] = "Hospital not found";
        echo "<script>window.history.back()</script>";
        exit;
    }
    $old = $q->fetch_assoc();
    $oldLogoPath = !empty($old['logo']) ? '../images/logos/' . $old['logo'] : '';
}

/* =========================
   Logo Upload
========================= */
$logo = '';

if (isset($_FILES['hospital_logo']) && $_FILES['hospital_logo']['error'] === UPLOAD_ERR_OK) {

    $tmp  = $_FILES['hospital_logo']['tmp_name'];
    $nameFile = $_FILES['hospital_logo']['name'];
    $ext  = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG or WEBP allowed";
        echo "<script>window.history.back()</script>";
        exit;
    }

    $logo = uniqid('hospital_', true) . '.' . $ext;
    $uploadPath = '../images/logos/' . $logo;

    if (!move_uploaded_file($tmp, $uploadPath)) {
        $_SESSION['error'] = "Logo upload failed";
        echo "<script>window.history.back()</script>";
        exit;
    }

    // Delete old logo
    if (!empty($oldLogoPath) && file_exists($oldLogoPath)) {
        unlink($oldLogoPath);
    }
}

/* =========================
   Validation
========================= */
if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    $_SESSION['error'] = "Please fill all required fields";
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   Insert / Update
========================= */
if ($id > 0) {

    $logoSQL = !empty($logo) ? ", logo='$logo'" : '';

    $sql = "
        UPDATE hospital_details SET
            name='$name',
            email='$email',
            phone='$phone',
            address='$address',
            website='$website',
            year='$year'
            $logoSQL
        WHERE id='$id'
    ";

} else {

    if (empty($logo)) {
        $_SESSION['error'] = "Hospital logo is required";
        echo "<script>window.history.back()</script>";
        exit;
    }

    $sql = "
        INSERT INTO hospital_details
        (name, email, phone, address, logo, website, year, user_id)
        VALUES
        ('$name','$email','$phone','$address','$logo','$website','$year','$user_id')
    ";
}

$run = $db->query($sql);

/* =========================
   Response
========================= */
if ($run) {
    $_SESSION['success'] = $id > 0 
        ? "Hospital details updated successfully"
        : "Hospital details added successfully";
    header('Location:index.php');
    exit;
} else {
    $_SESSION['error'] = "Database error: " . $db->error;
    echo "<script>window.history.back()</script>";
    exit;
}
