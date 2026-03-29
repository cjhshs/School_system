<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (isset($_POST['province'])) {
    $province = $_POST['province'];
    $province_escaped = $conn->real_escape_string($province);
    $sql = "SELECT municipality_name, zipcode FROM municipalities WHERE province_id = (SELECT id FROM provinces WHERE province_name = '$province_escaped') ORDER BY municipality_name";
    $result = $conn->query($sql);
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = [
            'name' => $row['municipality_name'],
            'zipcode' => $row['zipcode'] ?? ''
        ];
    }
    
    echo json_encode($cities);
} else {
    echo json_encode([]);
}
?>
