<?php
require_once 'config.php';

$message = '';
$errors = array();

echo "Starting migration...\n";

// Migrate deans to system_users
$deans = $conn->query("SELECT d.*, r.id as role_id FROM deans d 
                        LEFT JOIN roles r ON r.name = 'dean'");
while ($dean = $deans->fetch_assoc()) {
    $check = $conn->query("SELECT id FROM system_users WHERE employee_id = '" . $dean['employee_id'] . "'");
    if ($check->num_rows == 0) {
        $password = $dean['password'] ?: password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, branch_id, first_name, last_name, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $username = $dean['employee_id'];
        $email = $dean['email'] ?: strtolower(str_replace(' ', '', $dean['first_name'])) . '@school.edu';
        $role_id = $dean['role_id'] ?: 3; // dean role
        $branch_id = $dean['branch_id'] ?: 1;
        $first_name = $dean['first_name'];
        $last_name = $dean['last_name'];
        $employee_id = $dean['employee_id'];
        
        $stmt->bind_param("sssiisss", $username, $email, $password, $role_id, $branch_id, $first_name, $last_name, $employee_id);
        if ($stmt->execute()) {
            echo "Migrated dean: " . $first_name . " " . $last_name . " (ID: $employee_id)\n";
        }
    }
}

// Migrate teachers to system_users
$teachers = $conn->query("SELECT t.*, r.id as role_id FROM teachers t 
                          LEFT JOIN roles r ON r.name = 'teacher'");
while ($teacher = $teachers->fetch_assoc()) {
    $check = $conn->query("SELECT id FROM system_users WHERE employee_id = '" . $teacher['employee_id'] . "'");
    if ($check->num_rows == 0) {
        $password = $teacher['password'] ?: password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, branch_id, first_name, last_name, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $username = $teacher['employee_id'];
        $email = $teacher['email'] ?: strtolower(str_replace(' ', '', $teacher['first_name'])) . '@school.edu';
        $role_id = $teacher['role_id'] ?: 5; // teacher role
        $branch_id = $teacher['branch_id'] ?: 1;
        $first_name = $teacher['first_name'];
        $last_name = $teacher['last_name'];
        $employee_id = $teacher['employee_id'];
        
        $stmt->bind_param("sssiisss", $username, $email, $password, $role_id, $branch_id, $first_name, $last_name, $employee_id);
        if ($stmt->execute()) {
            echo "Migrated teacher: " . $first_name . " " . $last_name . " (ID: $employee_id)\n";
        }
    }
}

// Migrate finance_users to system_users
$finance_users = $conn->query("SELECT f.*, r.id as role_id FROM finance_users f 
                               LEFT JOIN roles r ON r.name = 'finance'");
while ($finance = $finance_users->fetch_assoc()) {
    $check = $conn->query("SELECT id FROM system_users WHERE username = '" . $conn->real_escape_string($finance['username']) . "'");
    if ($check->num_rows == 0) {
        $password = $finance['password'] ?: password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, branch_id, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $username = $finance['username'];
        $email = $finance['email'] ?: strtolower(str_replace(' ', '', $finance['fullname'])) . '@school.edu';
        $role_id = $finance['role_id'] ?: 4; // finance role
        $branch_id = $finance['branch_id'] ?: 1;
        $name_parts = explode(' ', $finance['fullname']);
        $first_name = $name_parts[0];
        $last_name = implode(' ', array_slice($name_parts, 1));
        
        $stmt->bind_param("sssiiss", $username, $email, $password, $role_id, $branch_id, $first_name, $last_name);
        if ($stmt->execute()) {
            echo "Migrated finance user: " . $finance['fullname'] . " (Username: $username)\n";
        }
    }
}

// Migrate admins to system_users
$admins = $conn->query("SELECT a.*, r.id as role_id FROM admins a 
                        LEFT JOIN roles r ON r.name = 'registrar'");
while ($admin = $admins->fetch_assoc()) {
    $check = $conn->query("SELECT id FROM system_users WHERE username = '" . $conn->real_escape_string($admin['username']) . "'");
    if ($check->num_rows == 0) {
        $password = $admin['password'] ?: password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, branch_id, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $username = $admin['username'];
        $email = $admin['email'] ?: strtolower(str_replace(' ', '', $admin['username'])) . '@school.edu';
        $role_id = $admin['role_id'] ?: 2; // registrar role
        $branch_id = $admin['branch_id'] ?: 1;
        $first_name = $admin['full_name'] ?: 'Registrar';
        $last_name = '';
        
        $stmt->bind_param("sssiiss", $username, $email, $password, $role_id, $branch_id, $first_name, $last_name);
        if ($stmt->execute()) {
            echo "Migrated admin: " . $admin['username'] . "\n";
        }
    }
}

echo "\nMigration complete!\n";
echo "\nTest credentials:\n";
echo "- Dean: D-2020-001 / password123\n";
echo "- Teacher: T-2020-001 / password123\n";
echo "- Finance: finance / password123\n";
echo "- Registrar: admin / admin123\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>Migration Complete!</h4>
            </div>
            <div class="card-body">
                <p>Users have been migrated to the unified system.</p>
                <div class="alert alert-info">
                    <strong>Test Credentials:</strong>
                    <ul class="mb-0">
                        <li>Dean: <code>D-2020-001</code> / <code>password123</code></li>
                        <li>Teacher: <code>T-2020-001</code> / <code>password123</code></li>
                        <li>Finance: <code>finance</code> / <code>password123</code></li>
                        <li>Registrar: <code>admin</code> / <code>admin123</code></li>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-primary">Go to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
