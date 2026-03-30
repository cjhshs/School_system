<?php
require_once 'config.php';

// Force fix dean user - update username to match employee_id and password to plain text
$conn->query("UPDATE system_users SET username = employee_id WHERE role_id = 3");
$conn->query("UPDATE system_users SET password = 'password123' WHERE role_id = 3 AND is_active = 1");

// Also fix finance
$conn->query("UPDATE system_users SET password = 'password123' WHERE role_id = 5 AND is_active = 1");

echo "Fixed!<br><br>";

$users = $conn->query("SELECT id, username, employee_id, password, role_id FROM system_users WHERE role_id IN (3,5)");
while ($u = $users->fetch_assoc()) {
    echo "ID: {$u['id']} | Username: {$u['username']} | EmpID: {$u['employee_id']} | Pass: {$u['password']} | RoleID: {$u['role_id']}<br>";
}
?>
<br><br>
Try login now with username <strong>20260001</strong> and password <strong>password123</strong>
