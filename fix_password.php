<?php
require_once 'config.php';

// Fix all users to use plain password
$conn->query("UPDATE system_users SET password = 'password123' WHERE role_id IN (3, 5, 6) AND is_active = 1");

echo "Fixed! All staff passwords set to 'password123'<br><br>";

$users = $conn->query("SELECT id, username, employee_id, role_id, password FROM system_users WHERE role_id IN (3, 5, 6) LIMIT 10");
while ($u = $users->fetch_assoc()) {
    echo "ID: {$u['id']} | Username: {$u['username']} | Password: {$u['password']}<br>";
}
?>
<br><br>
<a href="dean/login.php">Test Dean Login</a> | 
<a href="finance/login.php">Test Finance Login</a>
