<?php
require_once 'config.php';

echo "<h3>Tables in enrollment_system:</h3>";
$tables = $conn->query("SHOW TABLES");
while ($t = $tables->fetch_array()) {
    echo $t[0] . "<br>";
}
?>
