<?php
require_once 'config.php';

$message = '';
$errors = array();

// Create branches table
$sql = "CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    contact_number VARCHAR(50),
    is_main TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created branches table<br>";
} else {
    $errors[] = "Error creating branches: " . $conn->error;
}

// Create roles table
$sql = "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    hierarchy_level INT DEFAULT 0,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created roles table<br>";
} else {
    $errors[] = "Error creating roles: " . $conn->error;
}

// Create permissions table
$sql = "CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created permissions table<br>";
} else {
    $errors[] = "Error creating permissions: " . $conn->error;
}

// Create role_permissions table
$sql = "CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created role_permissions table<br>";
} else {
    $errors[] = "Error creating role_permissions: " . $conn->error;
}

// Create system_users table (unified user management)
$sql = "CREATE TABLE IF NOT EXISTS system_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    branch_id INT DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    employee_id VARCHAR(50) UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created system_users table<br>";
} else {
    $errors[] = "Error creating system_users: " . $conn->error;
}

// Create user_activity_log table
$sql = "CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE CASCADE
) ENGINE=InnoDB";
if ($conn->query($sql) === TRUE) {
    $message .= "✓ Created user_activity_log table<br>";
} else {
    $errors[] = "Error creating user_activity_log: " . $conn->error;
}

// Add branch_id to existing tables (safer approach - check column existence first)
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'branch_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN branch_id INT DEFAULT 1");
    $message .= "✓ Added branch_id to students table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM teachers LIKE 'branch_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE teachers ADD COLUMN branch_id INT DEFAULT 1");
    $message .= "✓ Added branch_id to teachers table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM deans LIKE 'branch_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE deans ADD COLUMN branch_id INT DEFAULT 1");
    $message .= "✓ Added branch_id to deans table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM finance_users LIKE 'branch_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE finance_users ADD COLUMN branch_id INT DEFAULT 1");
    $message .= "✓ Added branch_id to finance_users table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM admins LIKE 'branch_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE admins ADD COLUMN branch_id INT DEFAULT 1");
    $message .= "✓ Added branch_id to admins table<br>";
}

// Add is_active column if not exists
$result = $conn->query("SHOW COLUMNS FROM teachers LIKE 'is_active'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE teachers ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER password");
    $message .= "✓ Added is_active to teachers table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM deans LIKE 'is_active'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE deans ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER password");
    $message .= "✓ Added is_active to deans table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM finance_users LIKE 'is_active'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE finance_users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER password");
    $message .= "✓ Added is_active to finance_users table<br>";
}

$result = $conn->query("SHOW COLUMNS FROM admins LIKE 'is_active'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER password");
    $message .= "✓ Added is_active to admins table<br>";
}

// Insert default roles
$roles = array(
    array('name' => 'super_admin', 'display_name' => 'Super Admin', 'description' => 'Full system access with branch management', 'hierarchy_level' => 100, 'is_system' => 1),
    array('name' => 'registrar', 'display_name' => 'Registrar', 'description' => 'Manages students, teachers, subjects, and enrollments', 'hierarchy_level' => 80, 'is_system' => 1),
    array('name' => 'dean', 'display_name' => 'Dean', 'description' => 'Manages departments, subjects, and approves grades', 'hierarchy_level' => 70, 'is_system' => 1),
    array('name' => 'finance', 'display_name' => 'Finance', 'description' => 'Manages payments and financial records', 'hierarchy_level' => 60, 'is_system' => 1),
    array('name' => 'teacher', 'display_name' => 'Teacher', 'description' => 'Encodes and manages grades for assigned subjects', 'hierarchy_level' => 40, 'is_system' => 1),
    array('name' => 'student', 'display_name' => 'Student', 'description' => 'Requests enrollment and views records', 'hierarchy_level' => 20, 'is_system' => 1)
);

foreach ($roles as $role) {
    $check = $conn->query("SELECT id FROM roles WHERE name = '" . $role['name'] . "'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO roles (name, display_name, description, hierarchy_level, is_system) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $role['name'], $role['display_name'], $role['description'], $role['hierarchy_level'], $role['is_system']);
        $stmt->execute();
        $message .= "✓ Inserted role: " . $role['display_name'] . "<br>";
    }
}

// Insert default permissions
$permissions = array(
    // Branch Management
    array('name' => 'manage_branches', 'display_name' => 'Manage Branches', 'description' => 'Create, edit, and delete branches', 'category' => 'Branches'),
    
    // User Management
    array('name' => 'manage_all_users', 'display_name' => 'Manage All Users', 'description' => 'Create, edit, and delete any user', 'category' => 'Users'),
    array('name' => 'manage_students', 'display_name' => 'Manage Students', 'description' => 'Create, edit, and delete students', 'category' => 'Users'),
    array('name' => 'manage_teachers', 'display_name' => 'Manage Teachers', 'description' => 'Create, edit, and delete teachers', 'category' => 'Users'),
    array('name' => 'manage_registrars', 'display_name' => 'Manage Registrars', 'description' => 'Create, edit, and delete registrar accounts', 'category' => 'Users'),
    array('name' => 'manage_deans', 'display_name' => 'Manage Deans', 'description' => 'Create, edit, and delete dean accounts', 'category' => 'Users'),
    array('name' => 'manage_finance_staff', 'display_name' => 'Manage Finance Staff', 'description' => 'Create, edit, and delete finance accounts', 'category' => 'Users'),
    
    // Academic Management
    array('name' => 'manage_courses', 'display_name' => 'Manage Courses', 'description' => 'Create, edit, and delete courses', 'category' => 'Academic'),
    array('name' => 'manage_subjects', 'display_name' => 'Manage Subjects', 'description' => 'Create, edit, and delete subjects', 'category' => 'Academic'),
    array('name' => 'manage_departments', 'display_name' => 'Manage Departments', 'description' => 'Create, edit, and delete departments', 'category' => 'Academic'),
    array('name' => 'manage_enrollments', 'display_name' => 'Manage Enrollments', 'description' => 'Process and manage student enrollments', 'category' => 'Academic'),
    array('name' => 'assign_teachers', 'display_name' => 'Assign Teachers', 'description' => 'Assign teachers to subjects', 'category' => 'Academic'),
    array('name' => 'approve_schedules', 'display_name' => 'Approve Schedules', 'description' => 'Approve or reject schedules', 'category' => 'Academic'),
    array('name' => 'set_grading_policy', 'display_name' => 'Set Grading Policy', 'description' => 'Set passing grades and grading thresholds', 'category' => 'Academic'),
    
    // Grade Management
    array('name' => 'encode_grades', 'display_name' => 'Encode Grades', 'description' => 'Encode and update student grades', 'category' => 'Grades'),
    array('name' => 'approve_grades', 'display_name' => 'Approve Grades', 'description' => 'Approve or reject submitted grades', 'category' => 'Grades'),
    array('name' => 'view_grades', 'display_name' => 'View Grades', 'description' => 'View student grades', 'category' => 'Grades'),
    
    // Financial Management
    array('name' => 'manage_payments', 'display_name' => 'Manage Payments', 'description' => 'Record and manage payments', 'category' => 'Finance'),
    array('name' => 'manage_financial_reports', 'display_name' => 'Financial Reports', 'description' => 'Generate and view financial reports', 'category' => 'Finance'),
    array('name' => 'manage_student_accounts', 'display_name' => 'Manage Student Accounts', 'description' => 'Manage student financial accounts', 'category' => 'Finance'),
    
    // Student Portal
    array('name' => 'request_enrollment', 'display_name' => 'Request Enrollment', 'description' => 'Request to enroll in subjects', 'category' => 'Student'),
    array('name' => 'view_own_grades', 'display_name' => 'View Own Grades', 'description' => 'View personal grades', 'category' => 'Student'),
    array('name' => 'view_own_schedule', 'display_name' => 'View Own Schedule', 'description' => 'View personal schedule', 'category' => 'Student'),
    array('name' => 'view_own_finance', 'display_name' => 'View Own Finance', 'description' => 'View personal financial records', 'category' => 'Student')
);

foreach ($permissions as $perm) {
    $check = $conn->query("SELECT id FROM permissions WHERE name = '" . $perm['name'] . "'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO permissions (name, display_name, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $perm['name'], $perm['display_name'], $perm['description'], $perm['category']);
        $stmt->execute();
        $message .= "✓ Inserted permission: " . $perm['display_name'] . "<br>";
    }
}

// Assign permissions to roles
$role_permissions = array(
    'super_admin' => array('manage_branches', 'manage_all_users', 'manage_students', 'manage_teachers', 'manage_registrars', 'manage_deans', 'manage_finance_staff', 'manage_courses', 'manage_subjects', 'manage_departments', 'manage_enrollments', 'assign_teachers', 'approve_schedules', 'set_grading_policy', 'encode_grades', 'approve_grades', 'view_grades', 'manage_payments', 'manage_financial_reports', 'manage_student_accounts', 'request_enrollment', 'view_own_grades', 'view_own_schedule', 'view_own_finance'),
    'registrar' => array('manage_students', 'manage_teachers', 'manage_courses', 'manage_subjects', 'manage_enrollments', 'view_grades', 'view_own_schedule'),
    'dean' => array('manage_departments', 'assign_teachers', 'approve_schedules', 'set_grading_policy', 'approve_grades', 'view_grades', 'manage_subjects', 'view_own_schedule'),
    'finance' => array('manage_payments', 'manage_financial_reports', 'manage_student_accounts', 'view_own_schedule'),
    'teacher' => array('encode_grades', 'view_grades', 'view_own_schedule'),
    'student' => array('request_enrollment', 'view_own_grades', 'view_own_schedule', 'view_own_finance')
);

foreach ($role_permissions as $role_name => $perms) {
    $role_result = $conn->query("SELECT id FROM roles WHERE name = '$role_name'");
    if ($role_result->num_rows > 0) {
        $role_id = $role_result->fetch_assoc()['id'];
        foreach ($perms as $perm_name) {
            $perm_result = $conn->query("SELECT id FROM permissions WHERE name = '$perm_name'");
            if ($perm_result->num_rows > 0) {
                $perm_id = $perm_result->fetch_assoc()['id'];
                $check = $conn->query("SELECT id FROM role_permissions WHERE role_id = $role_id AND permission_id = $perm_id");
                if ($check->num_rows == 0) {
                    $conn->query("INSERT INTO role_permissions (role_id, permission_id) VALUES ($role_id, $perm_id)");
                }
            }
        }
    }
}
$message .= "✓ Assigned permissions to roles<br>";

// Insert default main branch if not exists
$check = $conn->query("SELECT id FROM branches WHERE is_main = 1");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO branches (code, name, address, is_main) VALUES ('MAIN', 'Main Campus', 'Default Address', 1)");
    $message .= "✓ Inserted main branch<br>";
}

// Create super admin user
$check = $conn->query("SELECT id FROM system_users WHERE username = 'admin'");
if ($check->num_rows == 0) {
    $role_result = $conn->query("SELECT id FROM roles WHERE name = 'super_admin'");
    $branch_result = $conn->query("SELECT id FROM branches WHERE is_main = 1");
    $role_id = $role_result->fetch_assoc()['id'];
    $branch_id = $branch_result->fetch_assoc()['id'];
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, branch_id, first_name, last_name, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $username = 'admin';
    $email = 'admin@school.edu';
    $first_name = 'System';
    $last_name = 'Administrator';
    $employee_id = 'SA-0001';
    $stmt->bind_param("sssiisss", $username, $email, $hashed_password, $role_id, $branch_id, $first_name, $last_name, $employee_id);
    $stmt->execute();
    $message .= "✓ Created Super Admin (admin / admin123)<br>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBAC Setup - Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>RBAC System Setup</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5>Errors:</h5>
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <h5>Setup Complete!</h5>
                                <p><?php echo $message; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info mt-3">
                            <h5>Role Hierarchy:</h5>
                            <ol class="mb-0">
                                <li><strong>Super Admin (100)</strong> - Full access, branch management</li>
                                <li><strong>Registrar (80)</strong> - Students, teachers, subjects, enrollments</li>
                                <li><strong>Dean (70)</strong> - Departments, subjects, grade approval</li>
                                <li><strong>Finance (60)</strong> - Payments, financial records</li>
                                <li><strong>Teacher (40)</strong> - Encode grades only</li>
                                <li><strong>Student (20)</strong> - View records, request enrollment</li>
                            </ol>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <strong>Super Admin Credentials:</strong><br>
                            Username: <code>admin</code><br>
                            Password: <code>admin123</code>
                        </div>
                        
                        <a href="index.php" class="btn btn-primary mt-3">Go to Home</a>
                        <a href="superadmin/login.php" class="btn btn-success mt-3">Go to Super Admin Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
