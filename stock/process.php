<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Unauthorized access';
    echo "<script>window.history.back()</script>";
    exit;
   }

/* =========================
   SANITIZE INPUTS
========================= */
$category_id = intval($_POST['category_id'] ?? 0);
$drug_id     = intval($_POST['drug_id'] ?? 0);
$quantity    = intval($_POST['quantity'] ?? 0);
$user_id     = getId();

if ($category_id <= 0 || $drug_id <= 0 || $quantity <= 0) {
    $_SESSION['error'] = "Invalid input data";
      echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK CATEGORY EXISTS
========================= */
$catCheck = $db->query("
    SELECT id FROM categories 
    WHERE id = $category_id AND status = 1
");

if ($catCheck->num_rows == 0) {
    $_SESSION['error'] = "Selected category does not exist";
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   CHECK DRUG EXISTS & BELONGS TO CATEGORY
========================= */
$drugSql = "
    SELECT id, drug_name 
    FROM drugs 
    WHERE id = $drug_id 
    AND category = $category_id 
    AND status = 1
";
$drugRun = $db->query($drugSql);

if ($drugRun->num_rows == 0) {
    $_SESSION['error'] = "Drug does not belong to selected category";
    echo "<script>window.history.back()</script>";
    exit;
}

$drug = $drugRun->fetch_assoc();
$drug_name = $drug['drug_name'];

/* =========================
   CHECK IF DRUG EXISTS IN STOCK
========================= */
$stockCheck = $db->query("
    SELECT id, quantity 
    FROM pharmacy_stock 
    WHERE drug = $drug_id
");

if ($stockCheck->num_rows > 0) {

    // UPDATE STOCK
    $stock = $stockCheck->fetch_assoc();
    $newQty = $stock['quantity'] + $quantity;

    $updateStock = $db->query("
        UPDATE pharmacy_stock 
        SET quantity = $newQty 
        WHERE drug = $drug_id
    ");

    if (!$updateStock) {
        $_SESSION['error'] = "Failed to update stock";
         echo "<script>window.history.back()</script>";
        exit;
    }

    $actionText = "added $quantity more";

} else {

    // INSERT NEW STOCK
    $insertStock = $db->query("
        INSERT INTO pharmacy_stock (drug, quantity)
        VALUES ($drug_id, $quantity)
    ");

    if (!$insertStock) {
        $_SESSION['error'] = "Failed to add new stock";
         echo "<script>window.history.back()</script>";
        exit;
    }

    $actionText = "added $quantity";
}

/* =========================
   RECORD PHARMACY ACTIVITY
========================= */
$userSql = $db->query("SELECT name FROM users WHERE id = $user_id");
$user    = $userSql->fetch_assoc();
$user_name = $user['name'] ?? 'User';

$notes = "$user_name $actionText units of $drug_name to pharmacy stock";

$db->query("
    INSERT INTO pharmacy_activities (user_id, notes)
    VALUES ($user_id, '$notes')
");

/* =========================
   SUCCESS
========================= */
$_SESSION['success'] = "Stock updated successfully";
header("Location: view.php");
exit;
