<?php
require_once 'config.php';

$username = $_GET['username'] ?? 'finance';
$password = $_GET['password'] ?? 'password123';
$role = 'dean';

echo "<h2>Debug Login</h2>";
echo "Trying: username=$username, password=$password, role=$role<br><br>";

$sql = "SELECT su.*, r.name as role_name, r.hierarchy_level 
        FROM system_users su 
        JOIN roles r ON su.role_id = r.id 
        WHERE (su.username = ? OR su.employee_id = ?) AND su.is_active = 1";

if ($role) {
    $sql .= " AND r.name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $username, $role);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
}

$stmt->execute();
$result = $stmt->get_result();

echo "Query: $sql<br>";
echo "Rows found: " . $result->num_rows . "<br><br>";

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "User found: " . $user['username'] . "<br>";
    echo "Role: " . $user['role_name'] . " (id: " . $user['role_id'] . ")<br>";
    echo "Password hash: " . substr($user['password'], 0, 30) . "<br>";
    echo "is_active: " . $user['is_active'] . "<br>";
    echo "Password check: " . ($password === $user['password'] ? 'MATCH' : 'NO MATCH') . "<br>";
} else {
    echo "No user found!<br><br>";
    
    // Check what users exist
    echo "<h3>All Users:</h3>";
    $all = $conn->query("SELECT su.username, su.employee_id, r.name as role_name, su.is_active FROM system_users su JOIN roles r ON su.role_id = r.id LIMIT 20");
    while ($u = $all->fetch_assoc()) {
        echo "- {$u['username']} / {$u['employee_id']} | Role: {$u['role_name']} | Active: {$u['is_active']}<br>";
    }
}
?>
