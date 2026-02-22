<?php
include '../functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    echo json_encode([]);
    exit;
}

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];
$sql = "SELECT u.id, u.name, u.hospital_num, u.phone, s.scheme_name, s.discount_fee
        FROM users u
        LEFT JOIN schemes s ON s.id = u.scheme_type AND s.status = 1
        WHERE u.type = 1
        AND (u.name LIKE '%$q%' OR u.hospital_num LIKE '%$q%' OR u.phone LIKE '%$q%')
        ORDER BY u.name ASC
        LIMIT 10";
$run = $db->query($sql);
if ($run) {
    while ($row = $run->fetch_assoc()) {
        $results[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'hospital_num' => $row['hospital_num'],
            'phone' => $row['phone'],
            'scheme_name' => $row['scheme_name'] ?? 'None',
            'discount_fee' => floatval($row['discount_fee'] ?? 0)
        ];
    }
}

echo json_encode($results);
exit;
