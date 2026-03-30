<?php
require_once 'config.php';

echo "<h2>Debug: Dean Users</h2>";

// Check all deans
$deans = $conn->query("
    SELECT su.id, su.username, su.email, su.role_id, su.is_active, su.employee_id, 
           r.name as role_name, r.id as r_id,
           d.name as dept_name, d.id as dept_id
    FROM system_users su
    JOIN roles r ON su.role_id = r.id
    LEFT JOIN departments d ON su.department_id = d.id
    WHERE r.name = 'dean'
    OR su.username = 'dean_cj' 
    OR d.name LIKE '%Criminal%'
    ORDER BY su.id DESC
    LIMIT 20
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Username</th><th>Role ID</th><th>Role Name</th><th>Dept</th><th>Active</th><th>Password Hash</th></tr>";

if ($deans) {
    while ($row = $deans->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . ($row['dept_name'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['is_active'] ?? 'NULL') . "</td>";
        echo "<td>" . substr($row['password'] ?? '', 0, 30) . "...</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<h3>All Roles:</h3>";
$roles = $conn->query("SELECT * FROM roles");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Display Name</th></tr>";
while ($r = $roles->fetch_assoc()) {
    echo "<tr><td>{$r['id']}</td><td>{$r['name']}</td><td>{$r['display_name']}</td></tr>";
}
echo "</table>";
?>
