<?php
require_once 'config.php';

echo "<h3>Roles Table:</h3>";
$roles = $conn->query("SELECT * FROM roles");
while ($r = $roles->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Display: {$r['display_name']}<br>";
}

echo "<h3>Dean Users (role_id=3):</h3>";
$deans = $conn->query("SELECT * FROM system_users WHERE role_id = 3");
while ($u = $deans->fetch_assoc()) {
    echo "ID: {$u['id']} | Username: {$u['username']} | EmpID: {$u['employee_id']} | Pass: {$u['password']} | Active: {$u['is_active']}<br>";
}

echo "<h3>Finance Users (role_id=5):</h3>";
$fin = $conn->query("SELECT * FROM system_users WHERE role_id = 5");
while ($u = $fin->fetch_assoc()) {
    echo "ID: {$u['id']} | Username: {$u['username']} | EmpID: {$u['employee_id']} | Pass: {$u['password']} | Active: {$u['is_active']}<br>";
}
?>
