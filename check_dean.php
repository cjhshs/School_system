<?php
require_once 'config.php';

// Get all users with their roles
$users = $conn->query("
    SELECT su.id, su.username, su.employee_id, su.password, su.is_active, r.name as role_name, r.id as role_id
    FROM system_users su
    JOIN roles r ON su.role_id = r.id
    WHERE r.name IN ('dean', 'finance', 'super_admin')
    ORDER BY su.id DESC
    LIMIT 20
");

echo "<h2>All Staff Users</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Username</th><th>Employee ID</th><th>Role</th><th>Active</th><th>Password</th></tr>";

while ($u = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td><strong>{$u['username']}</strong></td>";
    echo "<td>{$u['employee_id']}</td>";
    echo "<td>{$u['role_name']}</td>";
    echo "<td>{$u['is_active']}</td>";
    echo "<td>" . substr($u['password'], 0, 20) . "...</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>Test Login</h3>";
// Test login for dean
$test = $conn->query("SELECT * FROM system_users WHERE (username = '20260001' OR employee_id = '20260001') AND is_active = 1");
if ($test && $test->num_rows > 0) {
    $u = $test->fetch_assoc();
    echo "User found: {$u['username']}<br>";
    echo "Password in DB: {$u['password']}<br>";
    echo "Test 'newpassword123' == password: " . ('newpassword123' === $u['password'] ? 'YES' : 'NO') . "<br>";
} else {
    echo "User 20260001 not found!";
}
?>
