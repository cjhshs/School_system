<?php
require_once dirname(__DIR__) . '/config.php';

// Fix existing users - set username = employee_id where username is empty or different
$conn->query("UPDATE system_users SET username = employee_id WHERE username = '' OR username IS NULL OR username != employee_id");

echo "Fixed! Username now equals Employee ID for all users.";

echo "<h3>Dean Users:</h3>";
$deans = $conn->query("SELECT id, username, employee_id, role_id FROM system_users WHERE role_id = 3");
while ($d = $deans->fetch_assoc()) {
    echo "<br>ID: {$d['id']} | Username: {$d['username']} | Employee ID: {$d['employee_id']}";
}
?>
<br><br>
<a href="../dean/login.php">Go to Dean Login</a>
