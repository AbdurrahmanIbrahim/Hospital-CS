<?php
include '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
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
        IFNULL(s.name, '') AS scheme_name,
        IFNULL(s.discount_fee, 0) AS discount_fee
    FROM users u
    LEFT JOIN schemes s ON u.scheme_type = s.id AND s.status = 1
    WHERE u.type = 1
      AND (
          u.name LIKE '%$q%'
          OR u.hospital_num LIKE '%$q%'
          OR u.phone LIKE '%$q%'
      )
    ORDER BY u.name ASC
    LIMIT 10
";

$run = $db->query($sql);

$results = [];
if ($run && $run->num_rows > 0) {
    while ($row = $run->fetch_assoc()) {
        $results[] = [
            'id'           => $row['id'],
            'name'         => $row['name'],
            'hospital_num' => $row['hospital_num'],
            'phone'        => $row['phone'],
            'scheme_name'  => $row['scheme_name'],
            'discount_fee' => $row['discount_fee']
        ];
    }
}

echo json_encode($results);
exit;
?>
