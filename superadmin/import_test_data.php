<?php
require_once dirname(__DIR__) . '/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {
    
    // Departments
    $conn->query("INSERT INTO departments (id, name, code, description, dean_id, is_active) VALUES 
        (1, 'Computer Science', 'CS', 'Department of Computer Science', 3, 1),
        (2, 'Business Administration', 'BA', 'Department of Business Administration', NULL, 1),
        (3, 'Engineering', 'ENG', 'Department of Engineering', NULL, 1),
        (4, 'Education', 'EDU', 'Department of Education', NULL, 1),
        (5, 'Nursing', 'NUR', 'Department of Nursing', NULL, 1)
        ON DUPLICATE KEY UPDATE name=VALUES(name)");

    // Courses
    $conn->query("INSERT INTO courses (id, code, name, department_id, years, is_active) VALUES 
        (1, 'BSCS', 'Bachelor of Science in Computer Science', 1, 4, 1),
        (2, 'BSBA', 'Bachelor of Science in Business Administration', 2, 4, 1),
        (3, 'BSCE', 'Bachelor of Science in Civil Engineering', 3, 4, 1),
        (4, 'BSED', 'Bachelor of Secondary Education', 4, 4, 1),
        (5, 'BSN', 'Bachelor of Science in Nursing', 5, 4, 1)
        ON DUPLICATE KEY UPDATE name=VALUES(name)");

    // Subjects
    $conn->query("INSERT INTO subjects (id, subject_code, subject_name, units, year_level, semester, course_code, is_active) VALUES 
        (1, 'CS101', 'Introduction to Programming', 3, 1, '1st', 'BSCS', 1),
        (2, 'CS102', 'Data Structures', 3, 1, '2nd', 'BSCS', 1),
        (3, 'BA101', 'Principles of Management', 3, 1, '1st', 'BSBA', 1),
        (4, 'ENG101', 'Engineering Mechanics', 3, 1, '1st', 'BSCE', 1),
        (5, 'EDU101', 'Foundations of Education', 3, 1, '1st', 'BSED', 1),
        (6, 'NUR101', 'Anatomy and Physiology', 3, 1, '1st', 'BSN', 1),
        (7, 'CS201', 'Database Management', 3, 2, '1st', 'BSCS', 1),
        (8, 'CS301', 'Web Development', 3, 3, '1st', 'BSCS', 1)
        ON DUPLICATE KEY UPDATE subject_name=VALUES(subject_name)");

    // Tuition Fees
    $conn->query("INSERT INTO tuition_fees (course_code, year_level, semester, tuition_amount, miscellaneous_amount, laboratory_amount, other_fees, total_per_unit) VALUES 
        ('BSCS', 1, 'All', 15000, 5000, 3000, 1000, 24000),
        ('BSCS', 2, 'All', 15000, 5000, 3500, 1000, 24500),
        ('BSCS', 3, 'All', 15000, 5000, 4000, 1000, 25000),
        ('BSBA', 1, 'All', 12000, 4500, 0, 800, 17300),
        ('ENG', 1, 'All', 16000, 5500, 4000, 1200, 26700)
        ON DUPLICATE KEY UPDATE tuition_amount=VALUES(tuition_amount)");

    // Course Fees
    $conn->query("INSERT INTO course_fees (course_code, fee_name, amount, semester, is_required, description) VALUES 
        ('BSCS', 'Laboratory Fee', 2000, 'All', 1, 'Computer Lab Usage'),
        ('BSCS', 'Software License', 1500, 'All', 1, 'Software licenses'),
        ('BSBA', 'Business Simulation', 1000, 'All', 0, 'Business game software'),
        ('ENG', 'Engineering Kit', 2500, 'All', 1, 'Drawing instruments'),
        ('BSN', 'Clinical Fee', 3000, 'All', 1, 'Hospital attachment')
        ON DUPLICATE KEY UPDATE amount=VALUES(amount)");

    // Students
    $conn->query("INSERT INTO students (id, student_number, firstname, lastname, email, phone, gender, birthdate, course_id, year_level, enrollment_date, enrollment_status, password) VALUES 
        (1, '2024-0001', 'John', 'Smith', 'john.smith@student.edu', '09123456789', 'Male', '2005-03-15', 1, 1, '2024-08-01', 'Enrolled', 'password123'),
        (2, '2024-0002', 'Maria', 'Garcia', 'maria.garcia@student.edu', '09123456790', 'Female', '2005-05-20', 1, 1, '2024-08-01', 'Enrolled', 'password123'),
        (3, '2024-0003', 'Pedro', 'Santos', 'pedro.santos@student.edu', '09123456791', 'Male', '2005-07-10', 2, 1, '2024-08-01', 'Enrolled', 'password123'),
        (4, '2024-0004', 'Ana', 'Reyes', 'ana.reyes@student.edu', '09123456792', 'Female', '2005-02-28', 3, 1, '2024-08-01', 'Enrolled', 'password123'),
        (5, '2024-0005', 'James', 'Mendoza', 'james.mendoza@student.edu', '09123456793', 'Male', '2005-09-05', 1, 2, '2024-08-01', 'Enrolled', 'password123')
        ON DUPLICATE KEY UPDATE firstname=VALUES(firstname)");

    // System Users
    $conn->query("INSERT INTO system_users (id, employee_id, username, password, first_name, last_name, email, role_id, department_id, is_active) VALUES 
        (1, 'EMP-001', 'admin', 'admin123', 'Admin', 'User', 'admin@school.edu', 1, NULL, 1),
        (2, 'EMP-002', 'registrar', 'password123', 'Lisa', 'Chen', 'registrar@school.edu', 2, NULL, 1),
        (3, 'EMP-003', 'dean_cs', 'password123', 'Robert', 'Lee', 'dean.cs@school.edu', 3, 1, 1),
        (4, 'EMP-004', 'teacher1', 'password123', 'Sarah', 'Johnson', 'teacher1@school.edu', 4, 1, 1),
        (5, 'EMP-005', 'finance', 'password123', 'Michael', 'Brown', 'finance@school.edu', 5, NULL, 1),
        (6, 'EMP-006', 'cashier', 'password123', 'Jennifer', 'Davis', 'cashier@school.edu', 6, NULL, 1)
        ON DUPLICATE KEY UPDATE first_name=VALUES(first_name)");

    // Payments
    $conn->query("INSERT INTO payments (id, student_id, or_number, payment_amount, total_fees, balance, payment_method, payment_date, school_year, semester) VALUES 
        (1, 1, 'OR-2024-0001', 10000, 24000, 14000, 'Cash', '2024-08-15', '2024-2025', '1st'),
        (2, 2, 'OR-2024-0002', 12000, 24000, 12000, 'GCash', '2024-08-16', '2024-2025', '1st'),
        (3, 3, 'OR-2024-0003', 8000, 17300, 9300, 'Bank Transfer', '2024-08-17', '2024-2025', '1st'),
        (4, 4, 'OR-2024-0004', 15000, 26700, 11700, 'Cash', '2024-08-18', '2024-2025', '1st'),
        (5, 5, 'OR-2024-0005', 24500, 24500, 0, 'Cash', '2024-08-19', '2024-2025', '1st')
        ON DUPLICATE KEY UPDATE payment_amount=VALUES(payment_amount)");

    // Update departments dean_id
    $conn->query("UPDATE departments SET dean_id = 3 WHERE code = 'CS'");

    $message = "Test data imported successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Test Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-database me-2"></i>Import Test Data</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <p>This will add the following test data to your database:</p>
                        
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>5 Departments</strong> - CS, BA, ENG, EDU, NUR</li>
                            <li class="list-group-item"><strong>5 Courses</strong> - BSCS, BSBA, BSCE, BSED, BSN</li>
                            <li class="list-group-item"><strong>8 Subjects</strong> - Various subjects per course</li>
                            <li class="list-group-item"><strong>5 Tuition Fees</strong> - For different courses/years</li>
                            <li class="list-group-item"><strong>5 Course Fees</strong> - Additional fees per course</li>
                            <li class="list-group-item"><strong>5 Students</strong> - Test students with enrollments</li>
                            <li class="list-group-item"><strong>6 System Users</strong> - Admin, Registrar, Dean, Teacher, Finance, Cashier</li>
                            <li class="list-group-item"><strong>5 Payments</strong> - Sample payment records</li>
                        </ul>
                        
                        <h5>Login Credentials:</h5>
                        <table class="table table-bordered">
                            <tr><th>Role</th><th>Username</th><th>Password</th></tr>
                            <tr><td>Super Admin</td><td>admin</td><td>admin123</td></tr>
                            <tr><td>Registrar</td><td>registrar</td><td>password123</td></tr>
                            <tr><td>Dean (CS)</td><td>dean_cs</td><td>password123</td></tr>
                            <tr><td>Teacher</td><td>teacher1</td><td>password123</td></tr>
                            <tr><td>Finance</td><td>finance</td><td>password123</td></tr>
                            <tr><td>Cashier</td><td>cashier</td><td>password123</td></tr>
                            <tr><td>Student</td><td>2024-0001</td><td>password123</td></tr>
                        </table>
                        
                        <form method="POST">
                            <button type="submit" name="import" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Import Test Data
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg ms-2">Back to Home</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
