<?php
require_once '../config.php';

if (isset($_POST['city'])) {
    $city = $conn->real_escape_string($_POST['city']);
    $sql = "SELECT zipcode FROM municipalities WHERE municipality_name = '$city' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['zipcode'] ?? '';
    } else {
        echo '';
    }
}
?>
