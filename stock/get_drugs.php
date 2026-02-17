<?php
include '../functions.php';

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$category_id = intval($_GET['category_id'] ?? 0);

$sql = "
    SELECT id, drug_name
    FROM drugs
    WHERE status = 1
    AND category = $category_id
    ORDER BY drug_name ASC
";

$result = $db->query($sql);

$drugs = [];
while ($row = $result->fetch_assoc()) {
    $drugs[] = $row;
}

echo json_encode($drugs);
