<?php
require_once 'config.php';

$users = $conn->query("
    SELECT su.id, su.username, su.employee_id, su.role_id, r.name as role_name, su.is_active
    FROM system_users su
    JOIN roles r ON su.role_id = r.id
    WHERE r.name = 'dean'
    OR su.username LIKE '%dean%'
    OR su.username LIKE '%cj%'
    ORDER BY su.id DESC
    LIMIT 20
");

echo "<h3>Dean Users:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Username</th><th>Employee ID</th><th>Role</th><th>Active</th></tr>";
while ($u = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td><strong>{$u['username']}</strong></td>";
    echo "<td>{$u['employee_id']}</td>";
    echo "<td>{$u['role_name']}</td>";
    echo "<td>{$u['is_active']}</td>";
    echo "</tr>";
}
echo "</table>";
?>
