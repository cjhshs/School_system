<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config.php';

header('Content-Type: application/json');

if (isset($_POST['province'])) {
    $province = $_POST['province'];
    $province_escaped = $conn->real_escape_string($province);
    $sql = "SELECT DISTINCT CITIES_MUNICIPALITIES FROM cities WHERE PROVINCE = '$province_escaped' ORDER BY CITIES_MUNICIPALITIES";
    $result = $conn->query($sql);
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['CITIES_MUNICIPALITIES'];
    }
    
    echo json_encode($cities);
}
?>
