<?php
require_once 'config.php';

$users = $conn->query("SELECT id, username, employee_id, password, role_id, is_active FROM system_users WHERE role_id IN (3,5,6) LIMIT 10");

while ($row = $users->fetch_assoc()) {
    echo "User: " . $row['username'] . " | EmpID: " . $row['employee_id'] . " | Pass: " . $row['password'] . " | RoleID: " . $row['role_id'] . " | Active: " . $row['is_active'] . "<br>";
}
?>
