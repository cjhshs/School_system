<?php
require_once '../config.php';

$region = $_POST['region'] ?? '';

if ($region) {
    $region_escaped = $conn->real_escape_string($region);
    $result = $conn->query("SELECT DISTINCT p.province_name FROM provinces p JOIN regions r ON r.id = p.region_id WHERE r.region_name = '$region_escaped' ORDER BY p.province_name");
    $provinces = [];
    while ($row = $result->fetch_assoc()) {
        $provinces[] = $row['province_name'];
    }
    echo json_encode($provinces);
} else {
    echo json_encode([]);
}
