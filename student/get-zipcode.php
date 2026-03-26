<?php
require_once '../config.php';

if (isset($_POST['city'])) {
    $city = $conn->real_escape_string($_POST['city']);
    $sql = "SELECT ZIPCODE FROM cities WHERE CITIES_MUNICIPALITIES = '$city' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['ZIPCODE'];
    } else {
        echo '';
    }
}
?>
