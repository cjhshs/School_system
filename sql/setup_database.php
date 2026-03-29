<?php
require_once dirname(__DIR__) . '/config.php';

echo "Setting up database schema...\n\n";

$conn->query('SET FOREIGN_KEY_CHECKS = 0');

$tables = [
    'payment_records', 'payments', 'student_fees', 'course_fees', 
    'student_subjects', 'subject_loads', 'grades', 'enrollments',
    'schedules', 'notifications', 'activity_logs',
    'tuition_fees', 'fee_types', 'students', 'teachers',
    'subjects', 'courses', 'departments', 
    'system_users', 'semesters', 'school_years', 'branches', 'roles', 'schools',
    'permissions', 'role_permissions'
];

foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}

$conn->query('SET FOREIGN_KEY_CHECKS = 1');

echo "Creating tables...\n\n";

$conn->query('SET FOREIGN_KEY_CHECKS = 0');

$conn->query('CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    address VARCHAR(500), 
    phone VARCHAR(50), 
    email VARCHAR(100), 
    is_active TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(50) NOT NULL UNIQUE, 
    display_name VARCHAR(100) NOT NULL, 
    description VARCHAR(255), 
    hierarchy_level INT DEFAULT 0, 
    is_active TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    code VARCHAR(20), 
    address VARCHAR(500), 
    is_active TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE school_years (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    year VARCHAR(9), 
    is_active TINYINT(1) DEFAULT 1, 
    is_current TINYINT(1) DEFAULT 0
)');

$conn->query('CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(50), 
    order_num INT DEFAULT 1, 
    is_active TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    code VARCHAR(20) NOT NULL UNIQUE, 
    dean_id INT, 
    passing_grade DECIMAL(5,2) DEFAULT 75.00, 
    description TEXT, 
    is_active TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    code VARCHAR(20) NOT NULL, 
    name VARCHAR(255) NOT NULL, 
    department_id INT NOT NULL, 
    major VARCHAR(100), 
    total_units INT DEFAULT 0, 
    years INT DEFAULT 4, 
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_course (code, department_id)
)');

$conn->query('CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    subject_code VARCHAR(20) NOT NULL, 
    description VARCHAR(255) NOT NULL, 
    course_code VARCHAR(20), 
    department_id INT, 
    units DECIMAL(3,1) DEFAULT 0, 
    year_level INT DEFAULT 1, 
    semester VARCHAR(20), 
    instructor VARCHAR(255), 
    room VARCHAR(50), 
    schedule VARCHAR(100),
    max_students INT DEFAULT 40,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_subject (subject_code, course_code, year_level, semester)
)');

$conn->query('CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_number VARCHAR(50) NOT NULL UNIQUE, 
    firstname VARCHAR(100) NOT NULL, 
    lastname VARCHAR(100) NOT NULL, 
    middle_name VARCHAR(100),
    suffix VARCHAR(20),
    email VARCHAR(100), 
    phone VARCHAR(50), 
    gender VARCHAR(20), 
    birthdate DATE, 
    address VARCHAR(500), 
    city VARCHAR(100), 
    province VARCHAR(100), 
    zipcode VARCHAR(20), 
    guardian_name VARCHAR(200), 
    guardian_relationship VARCHAR(50), 
    guardian_phone VARCHAR(50),
    guardian_email VARCHAR(100),
    guardian_address VARCHAR(500),
    course_id INT, 
    year_level INT DEFAULT 1, 
    enrollment_status ENUM("Pending","Enrolled","Dropped","Graduated","Transferred") DEFAULT "Pending", 
    enrollment_date DATE,
    previous_school VARCHAR(255),
    previous_school_address VARCHAR(500),
    password VARCHAR(255), 
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
)');

$conn->query('CREATE TABLE system_users (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    username VARCHAR(100) NOT NULL UNIQUE, 
    email VARCHAR(100) NOT NULL, 
    password VARCHAR(255) NOT NULL, 
    role_id INT NOT NULL, 
    first_name VARCHAR(100) NOT NULL, 
    last_name VARCHAR(100) NOT NULL, 
    employee_id VARCHAR(50), 
    department_id INT, 
    branch_id INT DEFAULT 1, 
    is_active TINYINT(1) DEFAULT 1, 
    last_login DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    INDEX idx_role (role_id),
    INDEX idx_department (department_id)
)');

$conn->query('CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    school_year VARCHAR(9) NOT NULL, 
    semester VARCHAR(20) NOT NULL, 
    course_id INT NOT NULL, 
    year_level INT NOT NULL, 
    enrollment_date DATE, 
    status ENUM("Pending","Confirmed","Cancelled") DEFAULT "Pending", 
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, school_year, semester),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
)');

$conn->query('CREATE TABLE subject_loads (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    enrollment_id INT, 
    subject_id INT NOT NULL, 
    school_year VARCHAR(9) NOT NULL, 
    semester VARCHAR(20) NOT NULL, 
    status ENUM("Enrolled","Dropped","Completed") DEFAULT "Enrolled", 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subject_load (student_id, subject_id, school_year, semester),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
)');

$conn->query('CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    subject_id INT NOT NULL, 
    teacher_id INT, 
    school_year VARCHAR(9) NOT NULL, 
    semester VARCHAR(20) NOT NULL, 
    prelim DECIMAL(5,2), 
    midterm DECIMAL(5,2), 
    final_exam DECIMAL(5,2), 
    final_grade DECIMAL(5,2), 
    remarks VARCHAR(20), 
    grade_status ENUM("Draft","Submitted","Approved") DEFAULT "Draft", 
    submitted_at DATETIME, 
    approved_at DATETIME, 
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_grade (student_id, subject_id, school_year, semester),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES system_users(id),
    FOREIGN KEY (approved_by) REFERENCES system_users(id)
)');

$conn->query('CREATE TABLE fee_types (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(100) NOT NULL, 
    description VARCHAR(255), 
    amount DECIMAL(10,2) DEFAULT 0, 
    category VARCHAR(50), 
    is_required TINYINT(1) DEFAULT 1
)');

$conn->query('CREATE TABLE tuition_fees (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    course_code VARCHAR(20) NOT NULL, 
    year_level INT DEFAULT 1, 
    semester VARCHAR(20) DEFAULT "All", 
    tuition_amount DECIMAL(10,2) NOT NULL DEFAULT 0, 
    miscellaneous_amount DECIMAL(10,2) NOT NULL DEFAULT 0, 
    laboratory_amount DECIMAL(10,2) NOT NULL DEFAULT 0, 
    other_fees DECIMAL(10,2) NOT NULL DEFAULT 0, 
    total_per_unit DECIMAL(10,2) DEFAULT 0,
    units_required INT DEFAULT 0,
    UNIQUE KEY unique_tuition (course_code, year_level, semester)
)');

$conn->query('CREATE TABLE course_fees (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    course_code VARCHAR(20) NOT NULL, 
    fee_name VARCHAR(100) NOT NULL, 
    amount DECIMAL(10,2) NOT NULL, 
    semester VARCHAR(20) DEFAULT "All", 
    is_required TINYINT(1) DEFAULT 1, 
    description VARCHAR(255)
)');

$conn->query('CREATE TABLE student_fees (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    fee_type_id INT, 
    fee_name VARCHAR(100), 
    amount DECIMAL(10,2) NOT NULL, 
    due_date DATE, 
    is_paid TINYINT(1) DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id)
)');

$conn->query('CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    or_number VARCHAR(50) NOT NULL UNIQUE, 
    payment_amount DECIMAL(10,2) NOT NULL, 
    total_fees DECIMAL(10,2), 
    balance DECIMAL(10,2) DEFAULT 0, 
    payment_method VARCHAR(50), 
    payment_date DATE NOT NULL, 
    school_year VARCHAR(9), 
    semester VARCHAR(20), 
    received_by INT, 
    notes TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES system_users(id)
)');

$conn->query('CREATE TABLE payment_records (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    payment_id INT NOT NULL, 
    fee_description VARCHAR(255) NOT NULL, 
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
)');

$conn->query('CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    subject_id INT NOT NULL, 
    teacher_id INT, 
    day VARCHAR(20), 
    start_time TIME, 
    end_time TIME, 
    room VARCHAR(50), 
    section VARCHAR(20), 
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES system_users(id)
)');

$conn->query('CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT, 
    action VARCHAR(100) NOT NULL, 
    description TEXT, 
    ip_address VARCHAR(50), 
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES system_users(id)
)');

$conn->query('CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT NOT NULL, 
    title VARCHAR(200) NOT NULL, 
    message TEXT, 
    type VARCHAR(50), 
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES system_users(id)
)');

$conn->query('CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    employee_id VARCHAR(50) NOT NULL UNIQUE, 
    first_name VARCHAR(100) NOT NULL, 
    last_name VARCHAR(100) NOT NULL, 
    email VARCHAR(100), 
    phone VARCHAR(50),
    department_id INT, 
    is_active TINYINT(1) DEFAULT 1,
    hire_date DATE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
)');

$conn->query('CREATE TABLE student_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    student_id INT NOT NULL, 
    subject_id INT NOT NULL, 
    school_year VARCHAR(9), 
    semester VARCHAR(20), 
    status ENUM("Enrolled","Dropped","Completed") DEFAULT "Enrolled",
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
)');

$conn->query('SET FOREIGN_KEY_CHECKS = 1');

echo "Tables created!\n\n";

echo "Inserting sample data...\n\n";

$conn->query("INSERT INTO schools (name, address, phone, email) VALUES ('CJLG University', 'Quezon City', '(02) 1234-5678', 'info@cjlg.edu')");

$conn->query("INSERT INTO roles (name, display_name, description, hierarchy_level) VALUES 
('super_admin', 'Super Admin', 'Full system access', 100),
('registrar', 'Registrar', 'Student enrollment & records', 80),
('dean', 'Dean', 'Department head - academic management', 70),
('teacher', 'Teacher', 'Faculty member - grade encoding', 60),
('finance', 'Finance Officer', 'Financial management', 50),
('cashier', 'Cashier', 'Payment processing', 45),
('student', 'Student', 'Student portal access', 10)");

$conn->query('CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(100) NOT NULL UNIQUE, 
    description VARCHAR(255)
)');

$conn->query('CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    role_id INT NOT NULL, 
    permission_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_perm (role_id, permission_id)
)');

$conn->query("INSERT INTO permissions (name, description) VALUES 
('manage_users', 'Manage system users'),
('manage_students', 'Manage student records'),
('manage_enrollments', 'Manage enrollments'),
('manage_courses', 'Manage courses and subjects'),
('manage_grades', 'Manage student grades'),
('manage_fees', 'Manage fees and finances'),
('view_reports', 'View reports'),
('process_payments', 'Process payments'),
('view_own_grades', 'View own grades'),
('enroll_subjects', 'Enroll in subjects')");

$conn->query("INSERT INTO role_permissions (role_id, permission_id) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7),
(2, 2), (2, 3), (2, 4), (2, 7),
(3, 4), (3, 5), (3, 7),
(4, 5),
(5, 6), (5, 7),
(6, 6), (6, 8),
(7, 9), (7, 10)");

$conn->query("INSERT INTO branches (name, code, address) VALUES ('Main Campus', 'MAIN', 'Quezon City')");

$conn->query("INSERT INTO school_years (year, is_current) VALUES ('2025-2026', 1)");

$conn->query("INSERT INTO semesters (name, order_num) VALUES ('1st Semester', 1), ('2nd Semester', 2), ('Summer', 3)");

$conn->query("INSERT INTO departments (name, code, passing_grade) VALUES 
('Computer Science', 'CS', 75.00),
('Business Administration', 'BA', 75.00),
('Engineering', 'ENG', 75.00),
('Education', 'EDUC', 75.00),
('Arts and Sciences', 'AS', 75.00)");

$conn->query("INSERT INTO courses (code, name, department_id, total_units, years) VALUES 
('BSCS', 'Bachelor of Science in Computer Science', 1, 96, 4),
('BSBA', 'Bachelor of Science in Business Administration', 2, 84, 4),
('BSCE', 'Bachelor of Science in Civil Engineering', 3, 108, 5),
('BSED', 'Bachelor of Secondary Education', 4, 84, 4),
('AB', 'Bachelor of Arts', 5, 84, 4)");

$conn->query("INSERT INTO subjects (subject_code, description, course_code, department_id, units, year_level, semester) VALUES 
('CS101', 'Introduction to Programming', 'BSCS', 1, 3, 1, '1st'),
('CS102', 'Data Structures', 'BSCS', 1, 3, 2, '1st'),
('CS201', 'Database Management', 'BSCS', 1, 3, 2, '2nd'),
('BA101', 'Principles of Management', 'BSBA', 2, 3, 1, '1st'),
('BA102', 'Business Mathematics', 'BSBA', 2, 3, 1, '2nd')");

$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$user_pass = password_hash('password123', PASSWORD_DEFAULT);

$conn->query("INSERT INTO system_users (username, email, password, role_id, first_name, last_name, employee_id, department_id) VALUES 
('admin', 'admin@cjlg.edu', '$admin_pass', 1, 'System', 'Administrator', 'SA-2025-001', NULL),
('registrar', 'registrar@cjlg.edu', '$user_pass', 2, 'Registrar', 'User', 'R-2025-001', NULL),
('dean', 'dean@cjlg.edu', '$user_pass', 3, 'Dean', 'Computer Science', 'D-2025-001', 1),
('teacher', 'teacher@cjlg.edu', '$user_pass', 4, 'Teacher', 'User', 'T-2025-001', 1),
('finance', 'finance@cjlg.edu', '$user_pass', 5, 'Finance', 'Officer', 'F-2025-001', NULL),
('cashier', 'cashier@cjlg.edu', '$user_pass', 6, 'Cashier', 'User', 'C-2025-001', NULL)");

$conn->query("INSERT INTO students (student_number, firstname, lastname, email, course_id, year_level, enrollment_status, password) VALUES 
('2025-0001', 'Juan', 'Dela Cruz', 'juan@cjlg.edu', 1, 1, 'Enrolled', '$user_pass'),
('2025-0002', 'Maria', 'Santos', 'maria@cjlg.edu', 1, 1, 'Enrolled', '$user_pass'),
('2025-0003', 'Pedro', 'Garcia', 'pedro@cjlg.edu', 2, 2, 'Enrolled', '$user_pass')");

$conn->query("INSERT INTO fee_types (name, description, amount, category) VALUES 
('Registration Fee', 'One-time enrollment fee', 500, 'miscellaneous'),
('ID Card', 'Student ID card', 200, 'miscellaneous'),
('Library Fee', 'Library access', 300, 'miscellaneous'),
('Athletic Fee', 'Sports and wellness', 400, 'miscellaneous'),
('Lab Fee', 'Laboratory materials', 500, 'laboratory')");

$conn->query("INSERT INTO tuition_fees (course_code, year_level, semester, tuition_amount, miscellaneous_amount, laboratory_amount, other_fees, total_per_unit) VALUES 
('BSCS', 1, '1st', 15000, 5000, 2500, 1000, 23500),
('BSCS', 2, '1st', 15000, 5000, 3000, 1000, 24000),
('BSBA', 1, '1st', 14000, 5000, 1000, 1000, 21000)");

echo "Sample data inserted!\n\n";

echo "Adding foreign keys for circular references...\n";
$conn->query('ALTER TABLE system_users ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL');
$conn->query('ALTER TABLE departments ADD FOREIGN KEY (dean_id) REFERENCES system_users(id) ON DELETE SET NULL');
echo "Done!\n\n";

$result = $conn->query('SHOW TABLES');
echo "========================================\n";
echo "DATABASE SCHEMA COMPLETE!\n";
echo "Total Tables: " . $result->num_rows . "\n";
echo "========================================\n";

echo "\nDefault Login Credentials:\n";
echo "=========================\n";
echo "Super Admin: admin / admin123\n";
echo "Registrar:   registrar / password123\n";
echo "Dean:        dean / password123\n";
echo "Teacher:     teacher / password123\n";
echo "Finance:     finance / password123\n";
echo "Cashier:     cashier / password123\n";
echo "Student:     2025-0001 / password123\n";
