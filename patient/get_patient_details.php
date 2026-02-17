<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 5)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}


if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit;
}

$id = intval($_GET['id']);

// Fetch complete patient details
$sql = "
SELECT 
    u.*,

    s.name  AS scheme_name,
    s.discount_fee,

    ftype.name AS file_type_name,
    ftype.amount AS file_amount,

    fam.name AS family_name,
    fam.phone AS family_phone,

    sta.name AS state_name,
    lg.name AS lga_name


FROM users u

LEFT JOIN schemes s 
    ON u.scheme_type = s.id AND s.status = 1

LEFT JOIN file_types ftype 
    ON u.file_type = ftype.id AND ftype.status = 1

LEFT JOIN families fam 
    ON u.family_id = fam.id AND fam.status = 1

LEFT JOIN states sta 
    ON u.state = sta.id

LEFT JOIN lgas lg 
    ON u.lga = lg.id

WHERE u.id = '$id' 
  AND u.type = 1
";

$run = $db->query($sql);

if ($run->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
    exit;
}

$patient = $run->fetch_assoc();
$patient['reciept_num'] = getReciept($patient['id']);

// Return as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'patient' => $patient
]);