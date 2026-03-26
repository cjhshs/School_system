<?php
require_once '../config.php';

if (isset($_POST['course'])) {
    $course = $conn->real_escape_string($_POST['course']);
    $sql = "SELECT DISTINCT major FROM courses WHERE code = '$course' AND major != '' AND major IS NOT NULL ORDER BY major";
    $result = $conn->query($sql);
    
    echo '<option value="">Select Major (if applicable)</option>';
    echo '<option value="">No Major</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['major'] . '">' . $row['major'] . '</option>';
    }
}
?>