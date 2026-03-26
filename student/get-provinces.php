<?php
require_once '../config.php';

$region = $_POST['region'] ?? '';

if ($region) {
    $result = $conn->query("SELECT DISTINCT PROVINCE FROM cities WHERE REGION = '" . $conn->real_escape_string($region) . "' ORDER BY PROVINCE");
    $provinces = [];
    while ($row = $result->fetch_assoc()) {
        $provinces[] = $row['PROVINCE'];
    }
    echo json_encode($provinces);
} else {
    echo json_encode([]);
}
