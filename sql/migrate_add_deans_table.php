<?php
require_once dirname(__DIR__) . '/config.php';

$message = '';
$error = '';

// Check if deans table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'deans'");

if ($table_exists && $table_exists->num_rows > 0) {
    $message = "Deans table already exists!";
} else {
    // Create deans table
    $sql = "CREATE TABLE IF NOT EXISTS deans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        department_id INT NOT NULL,
        appointment_date DATE,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE CASCADE,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
        UNIQUE KEY unique_dean_department (user_id, department_id)
    )";
    
    if ($conn->query($sql)) {
        // Migrate existing dean assignments from departments table
        $conn->query("INSERT IGNORE INTO deans (user_id, department_id, appointment_date, status)
            SELECT dean_id, id, CURDATE(), 'Active' 
            FROM departments 
            WHERE dean_id IS NOT NULL");
        
        $message = "Deans table created successfully! Existing dean assignments have been migrated.";
    } else {
        $error = "Error creating table: " . $conn->error;
    }
}

// Show current deans
$deans = $conn->query("
    SELECT d.*, su.first_name, su.last_name, dep.name as department_name, dep.code as department_code
    FROM deans d
    JOIN system_users su ON d.user_id = su.id
    JOIN departments dep ON d.department_id = dep.id
    WHERE d.status = 'Active'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration: Add Deans Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-database me-2"></i>Migration: Deans Table</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5>Current Deans in Database:</h5>
                        <?php if ($deans && $deans->num_rows > 0): ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($dean = $deans->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $dean['id']; ?></td>
                                        <td><?php echo htmlspecialchars($dean['first_name'] . ' ' . $dean['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($dean['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($dean['department_code']); ?></td>
                                        <td><span class="badge bg-success"><?php echo $dean['status']; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No deans found. Please assign deans through the Departments page.</p>
                        <?php endif; ?>
                        
                        <a href="dashboard.php?page=departments" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-2"></i>Go to Departments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
