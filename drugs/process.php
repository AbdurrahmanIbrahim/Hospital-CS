<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Unauthorized Access';
    header("Location: ../login/index.php");
    exit;
}

/* =========================
   SANITIZE INPUTS
========================= */
$drug_name             = sanitize($_POST['drug_name']);
$generic_name          = sanitize($_POST['generic_name']);
$category              = intval($_POST['category']);
$dosage_form           = sanitize($_POST['dosage_form']);
$strength              = sanitize($_POST['strength']);
$route                 = sanitize($_POST['route']);
$units_per_pack        = intval($_POST['units_per_pack']);
$manufacturer           = sanitize($_POST['manufacturer']);
$cost_price            = floatval($_POST['cost_price']);
$selling_price         = floatval($_POST['selling_price']);
$minimum_stock_level   = isset($_POST['minimum_stock_level']) 
                          ? intval($_POST['minimum_stock_level']) 
                          : 10;
$status                = isset($_POST['status']) ? intval($_POST['status']) : 1;

/* =========================
   VALIDATION
========================= */
if (
    empty($drug_name) ||
    empty($category) ||
    empty($dosage_form) ||
    $units_per_pack <= 0 ||
    $cost_price <= 0 ||
    $selling_price <= 0
) {
    $_SESSION['error'] = 'Please fill all required fields correctly';
    echo "<script>window.history.back()</script>";
    exit;
}

if ($selling_price < $cost_price) {
    $_SESSION['error'] = 'Selling price cannot be lower than cost price';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   UPDATE DRUG
========================= */
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "
        UPDATE drugs SET
            drug_name           = '$drug_name',
            generic_name        = '$generic_name',
            category            = '$category',
            dosage_form         = '$dosage_form',
            strength            = '$strength',
            route               = '$route',
            units_per_pack      = '$units_per_pack',
            manufacturer        = '$manufacturer',
            cost_price          = '$cost_price',
            selling_price       = '$selling_price',
            minimum_stock_level = '$minimum_stock_level',
            status              = '$status',
            updated_at          = NOW()
        WHERE id = '$id'
    ";

    if ($db->query($sql)) {
        $_SESSION['success'] = 'Drug updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update drug';
    }

    header("Location: view.php");
    exit;
}

/* =========================
   CHECK DUPLICATE (NAME + STRENGTH)
========================= */
$check = $db->query("
    SELECT id, status 
    FROM drugs 
    WHERE drug_name = '$drug_name' 
    AND strength = '$strength'
    LIMIT 1
");

if ($check->num_rows > 0) {
    $row = $check->fetch_assoc();

    if ($row['status'] == -1) {
        // Reactivate soft-deleted drug
        $sql = "
            UPDATE drugs SET
                generic_name        = '$generic_name',
                category            = '$category',
                dosage_form         = '$dosage_form',
                strength            = '$strength',
                route               = '$route',
                units_per_pack      = '$units_per_pack',
                manufacturer        = '$manufacturer',
                cost_price          = '$cost_price',
                selling_price       = '$selling_price',
                minimum_stock_level = '$minimum_stock_level',
                status              = 1,
                updated_at          = NOW()
            WHERE id = '{$row['id']}'
        ";

        if ($db->query($sql)) {
            $_SESSION['success'] = 'Drug reactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to reactivate drug';
        }

        header("Location: view.php");
        exit;
    } else {
        $_SESSION['error'] = 'Drug already exists';
        echo "<script>window.history.back()</script>";
        exit;
    }
}

/* =========================
   INSERT NEW DRUG
========================= */
$sql = "
    INSERT INTO drugs (
        drug_name,
        generic_name,
        category,
        dosage_form,
        strength,
        route,
        units_per_pack,
        manufacturer,
        cost_price,
        selling_price,
        minimum_stock_level,
        status,
        created_at
    ) VALUES (
        '$drug_name',
        '$generic_name',
        '$category',
        '$dosage_form',
        '$strength',
        '$route',
        '$units_per_pack',
        '$manufacturer',
        '$cost_price',
        '$selling_price',
        '$minimum_stock_level',
        '$status',
        NOW()
    )
";

if ($db->query($sql)) {
    $_SESSION['success'] = 'Drug added successfully';
} else {
    $_SESSION['error'] = 'Failed to add drug';
}

header("Location: view.php");
exit;
