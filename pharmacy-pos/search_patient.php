<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 6)) {
    echo json_encode([]);
    exit;
}

$q = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT
        u.id,
        u.name,
        u.hospital_num,
        u.phone,
        COALESCE(s.name, 'No Scheme') AS scheme_name,
        COALESCE(s.discount_fee, 0) AS discount_fee
    FROM users u
    LEFT JOIN schemes s ON s.id = u.scheme_type AND s.status = 1
    WHERE u.type = 1
      AND (u.name LIKE '%$q%' OR u.hospital_num LIKE '%$q%' OR u.phone LIKE '%$q%')
    ORDER BY u.name ASC
    LIMIT 10
";

$run = $db->query($sql);
$patients = [];

if ($run && $run->num_rows > 0) {
    while ($row = $run->fetch_assoc()) {
        $patients[] = [
            'id'           => $row['id'],
            'name'         => $row['name'],
            'hospital_num' => $row['hospital_num'],
            'phone'        => $row['phone'],
            'scheme_name'  => $row['scheme_name'],
            'discount_fee' => $row['discount_fee']
        ];
    }
}

echo json_encode($patients);
exit;
?>
