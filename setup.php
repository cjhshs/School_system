<?php
require_once 'config.php';

$message = '';

// Add password column if not exists
$result = $conn->query("SHOW COLUMNS FROM students LIKE 'password'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER profile_picture");
    $message .= "✓ Added password column<br>";
}

// Update passwords for existing students (format: lastname + student_number without dash)
$conn->query("UPDATE students SET password = CONCAT(LOWER(REPLACE(lastname, ' ', '')), REPLACE(student_number, '-', '')) WHERE password IS NULL OR password = ''");
$message .= "✓ Updated passwords for all students<br>";

// Show sample login credentials
$students = $conn->query("SELECT student_number, lastname, password FROM students LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Student Passwords</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Database Setup</h4>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-success">
                                <h5>Setup Complete!</h5>
                                <p><?php echo $message; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <h5>Sample Login Credentials:</h5>
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Student Number</th>
                                    <th>Lastname</th>
                                    <th>Default Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['student_number']; ?></td>
                                    <td><?php echo $row['lastname']; ?></td>
                                    <td><code><?php echo $row['password']; ?></code></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <div class="alert alert-info mt-3">
                            <strong>Note:</strong> Password format is: lastname + student_number (without dash)
                            <br>Example: Dela Cruz + 2020-1001 = <code>delacruz20201001</code>
                        </div>
                        
                        <a href="index.php" class="btn btn-primary mt-3">Go to Home</a>
                        <a href="student/login.php" class="btn btn-success mt-3">Go to Student Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
