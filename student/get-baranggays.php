<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config.php';

header('Content-Type: application/json');

if (isset($_POST['municipality'])) {
    $municipality = $_POST['municipality'];
    $municipality_escaped = $conn->real_escape_string($municipality);
    
    $sql = "SELECT DISTINCT b.barangay_name 
            FROM barangays b
            JOIN municipalities m ON m.id = b.municipality_id
            WHERE m.municipality_name = '$municipality_escaped' 
            ORDER BY b.barangay_name";
    
    $result = $conn->query($sql);
    
    $barangays = [];
    while ($row = $result->fetch_assoc()) {
        $barangays[] = $row['barangay_name'];
    }
    
    echo json_encode($barangays);
}
?>
