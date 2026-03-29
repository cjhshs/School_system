-- ================================================
-- CJLG UNIVERSITY - COMPREHENSIVE DATABASE SCHEMA
-- ================================================

-- Drop existing tables (in correct order due to foreign keys)
DROP TABLE IF EXISTS payment_records;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS student_subjects;
DROP TABLE IF EXISTS subject_loads;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS student_fees;
DROP TABLE IF EXISTS course_fees;
DROP TABLE IF EXISTS tuition_fees;
DROP TABLE IF EXISTS fee_types;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS system_users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS schools;
DROP TABLE IF EXISTS school_years;
DROP TABLE IF EXISTS semesters;

-- ================================================
-- CORE CONFIGURATION TABLES
-- ================================================

-- Schools table
CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(500),
    phone VARCHAR(50),
    email VARCHAR(100),
    logo VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- School Years
CREATE TABLE school_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(9) NOT NULL, -- e.g., '2025-2026'
    is_active TINYINT(1) DEFAULT 1,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Semesters
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- '1st Semester', '2nd Semester', 'Summer'
    order_num INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1
);

-- Insert default semesters
INSERT INTO semesters (name, order_num) VALUES 
('1st Semester', 1),
('2nd Semester', 2),
('Summer', 3);

-- Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE, -- super_admin, registrar, dean, teacher, finance, cashier, student
    display_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    hierarchy_level INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

INSERT INTO roles (name, display_name, description, hierarchy_level) VALUES
('super_admin', 'Super Admin', 'Full system access', 100),
('registrar', 'Registrar', 'Student enrollment & records', 80),
('dean', 'Dean', 'Department head - academic management', 70),
('teacher', 'Teacher', 'Faculty member - grade encoding', 60),
('finance', 'Finance Officer', 'Financial management', 50),
('cashier', 'Cashier', 'Payment processing', 45),
('student', 'Student', 'Student portal access', 10);

-- Permissions
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- Role Permissions
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_perm (role_id, permission_id)
);

INSERT INTO permissions (name, description) VALUES 
('manage_users', 'Manage system users'),
('manage_students', 'Manage student records'),
('manage_enrollments', 'Manage enrollments'),
('manage_courses', 'Manage courses and subjects'),
('manage_grades', 'Manage student grades'),
('manage_fees', 'Manage fees and finances'),
('view_reports', 'View reports'),
('process_payments', 'Process payments'),
('view_own_grades', 'View own grades'),
('enroll_subjects', 'Enroll in subjects');

INSERT INTO role_permissions (role_id, permission_id) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7),
(2, 2), (2, 3), (2, 4), (2, 7),
(3, 4), (3, 5), (3, 7),
(4, 5),
(5, 6), (5, 7),
(6, 6), (6, 8),
(7, 9), (7, 10);

-- Branches/Campuses
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20),
    address VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- ACADEMIC STRUCTURE TABLES
-- ================================================

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    dean_id INT NULL,
    passing_grade DECIMAL(5,2) DEFAULT 75.00,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses/Programs
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    major VARCHAR(100),
    description TEXT,
    total_units INT DEFAULT 0,
    years INT DEFAULT 4,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course (code, department_id)
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL,
    description VARCHAR(255) NOT NULL,
    course_code VARCHAR(20), -- Links to courses table
    department_id INT,
    units DECIMAL(3,1) DEFAULT 0,
    lecture_units DECIMAL(3,1) DEFAULT 0,
    lab_units DECIMAL(3,1) DEFAULT 0,
    year_level INT DEFAULT 1,
    semester VARCHAR(20), -- 1st, 2nd, Summer
    instructor VARCHAR(255),
    room VARCHAR(50),
    schedule VARCHAR(100),
    max_students INT DEFAULT 40,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    UNIQUE KEY unique_subject (subject_code, course_code, year_level, semester)
);

-- Teachers (kept for backward compatibility, but primarily use system_users with role_id=5)
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(50),
    department_id INT,
    is_active TINYINT(1) DEFAULT 1,
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- ================================================
-- STUDENT MANAGEMENT TABLES
-- ================================================

-- Students
CREATE TABLE students (
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
    
    -- Address
    address VARCHAR(500),
    city VARCHAR(100),
    province VARCHAR(100),
    zipcode VARCHAR(20),
    
    -- Guardian Information
    guardian_name VARCHAR(200),
    guardian_relationship VARCHAR(50),
    guardian_phone VARCHAR(50),
    guardian_email VARCHAR(100),
    guardian_address VARCHAR(500),
    
    -- Academic Info
    course_id INT,
    year_level INT DEFAULT 1,
    enrollment_status ENUM('Pending', 'Enrolled', 'Dropped', 'Graduated', 'Transferred') DEFAULT 'Pending',
    enrollment_date DATE,
    
    -- Previous School
    previous_school VARCHAR(255),
    previous_school_address VARCHAR(500),
    
    password VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- System Users (all roles including students for portal login)
CREATE TABLE system_users (
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (created_by) REFERENCES system_users(id),
    INDEX idx_role (role_id),
    INDEX idx_department (department_id)
);

-- ================================================
-- ENROLLMENT TABLES
-- ================================================

-- Enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    school_year VARCHAR(9) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    course_id INT NOT NULL,
    year_level INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY unique_enrollment (student_id, school_year, semester)
);

-- Subject Loads (Student-Subject Enrollments)
CREATE TABLE subject_loads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    enrollment_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(9) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    status ENUM('Enrolled', 'Dropped', 'Completed') DEFAULT 'Enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    UNIQUE KEY unique_subject_load (student_id, subject_id, school_year, semester)
);

-- Student Subjects (alternative tracking - kept for compatibility)
CREATE TABLE student_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    school_year VARCHAR(9),
    semester VARCHAR(20),
    status ENUM('Enrolled', 'Dropped', 'Completed') DEFAULT 'Enrolled',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- ================================================
-- GRADING TABLES
-- ================================================

-- Grades
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    school_year VARCHAR(9) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    
    -- Grade Components
    prelim DECIMAL(5,2),
    midterm DECIMAL(5,2),
    final_exam DECIMAL(5,2),
    final_grade DECIMAL(5,2),
    
    -- Remarks
    remarks VARCHAR(20), -- PASS, FAIL, INC, DROPPED
    grade_status ENUM('Draft', 'Submitted', 'Approved') DEFAULT 'Draft',
    
    -- Timestamps
    submitted_at DATETIME,
    approved_at DATETIME,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES system_users(id),
    FOREIGN KEY (approved_by) REFERENCES system_users(id),
    UNIQUE KEY unique_grade (student_id, subject_id, school_year, semester)
);

-- ================================================
-- FINANCE TABLES
-- ================================================

-- Fee Types (Templates)
CREATE TABLE fee_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(10,2) DEFAULT 0,
    is_required TINYINT(1) DEFAULT 1,
    category VARCHAR(50), -- tuition, miscellaneous, laboratory, other
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tuition Fees (per course/year)
CREATE TABLE tuition_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    year_level INT DEFAULT 1,
    semester VARCHAR(20) DEFAULT 'All',
    tuition_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    miscellaneous_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    laboratory_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    other_fees DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_per_unit DECIMAL(10,2) DEFAULT 0,
    units_required INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tuition (course_code, year_level, semester)
);

-- Course Fees (Additional fees per course)
CREATE TABLE course_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    fee_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    semester VARCHAR(20) DEFAULT 'All',
    is_required TINYINT(1) DEFAULT 1,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Fees (Assigned fees to students)
CREATE TABLE student_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_type_id INT,
    fee_name VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE,
    is_paid TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id)
);

-- Payment Methods
CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 1
);

INSERT INTO payment_methods (name) VALUES ('Cash'), ('Check'), ('Bank Transfer'), ('Credit Card'), ('Online Payment');

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    or_number VARCHAR(50) NOT NULL UNIQUE,
    payment_amount DECIMAL(10,2) NOT NULL,
    total_fees DECIMAL(10,2) NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(50),
    payment_date DATE NOT NULL,
    school_year VARCHAR(9),
    semester VARCHAR(20),
    received_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES system_users(id)
);

-- Payment Records (detailed breakdown)
CREATE TABLE payment_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    fee_description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
);

-- ================================================
-- SCHEDULING TABLES
-- ================================================

-- Schedules
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    teacher_id INT,
    day VARCHAR(20), -- Monday, Tuesday, etc.
    start_time TIME,
    end_time TIME,
    room VARCHAR(50),
    section VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES system_users(id)
);

-- ================================================
-- AUDIT & LOGS
-- ================================================

-- Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES system_users(id)
);

-- ================================================
-- NOTIFICATIONS
-- ================================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    type VARCHAR(50), -- info, warning, success, error
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES system_users(id)
);

-- ================================================
-- ADD SAMPLE DATA
-- ================================================

-- Insert school
INSERT INTO schools (name, address, phone, email) VALUES 
('CJLG University', 'quezon city', '(02) 1234-5678', 'info@cjlg.edu');

-- Insert branch
INSERT INTO branches (name, code, address) VALUES 
('Main Campus', 'MAIN', 'quezon city');

-- Insert school year
INSERT INTO school_years (year, is_current) VALUES ('2025-2026', 1);

-- Insert departments
INSERT INTO departments (name, code, passing_grade) VALUES 
('Computer Science', 'CS', 75.00),
('Business Administration', 'BA', 75.00),
('Engineering', 'ENG', 75.00),
('Education', 'EDUC', 75.00),
('Arts and Sciences', 'AS', 75.00);

-- Insert courses
INSERT INTO courses (code, name, department_id, total_units, years) VALUES 
('BSCS', 'Bachelor of Science in Computer Science', 1, 96, 4),
('BSBA', 'Bachelor of Science in Business Administration', 2, 84, 4),
('BSCE', 'Bachelor of Science in Civil Engineering', 3, 108, 5),
('BSED', 'Bachelor of Secondary Education', 4, 84, 4),
('AB', 'Bachelor of Arts', 5, 84, 4);

-- Insert sample subjects
INSERT INTO subjects (subject_code, description, course_code, department_id, units, year_level, semester) VALUES 
('CS101', 'Introduction to Programming', 'BSCS', 1, 3, 1, '1st'),
('CS102', 'Data Structures', 'BSCS', 1, 3, 2, '1st'),
('CS201', 'Database Management', 'BSCS', 1, 3, 2, '2nd'),
('BA101', 'Principles of Management', 'BSBA', 2, 3, 1, '1st'),
('BA102', 'Business Mathematics', 'BSBA', 2, 3, 1, '2nd');

-- Insert sample students
INSERT INTO students (student_number, firstname, lastname, email, course_id, year_level, enrollment_status) VALUES 
('2025-0001', 'Juan', 'Dela Cruz', 'juan.delacruz@cjlg.edu', 1, 1, 'Enrolled'),
('2025-0002', 'Maria', 'Santos', 'maria.santos@cjlg.edu', 1, 1, 'Enrolled'),
('2025-0003', 'Pedro', 'Garcia', 'pedro.garcia@cjlg.edu', 2, 2, 'Enrolled');

-- Insert sample tuition fees
INSERT INTO tuition_fees (course_code, year_level, semester, tuition_amount, miscellaneous_amount, laboratory_amount, other_fees, total_per_unit) VALUES 
('BSCS', 1, '1st', 15000, 5000, 2500, 1000, 23500),
('BSCS', 2, '1st', 15000, 5000, 3000, 1000, 24000),
('BSBA', 1, '1st', 14000, 5000, 1000, 1000, 21000);

-- Insert fee types
INSERT INTO fee_types (name, description, amount, category) VALUES 
('Registration Fee', 'One-time enrollment fee', 500, 'miscellaneous'),
('ID Card', 'Student ID card', 200, 'miscellaneous'),
('Library Fee', 'Library access', 300, 'miscellaneous'),
('Athletic Fee', 'Sports and wellness', 400, 'miscellaneous'),
('Lab Fee', 'Laboratory materials', 500, 'laboratory');

-- Insert roles into system_users for portal login
INSERT INTO system_users (username, email, password, role_id, first_name, last_name, employee_id, department_id) VALUES 
('admin', 'admin@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'System', 'Administrator', 'SA-2025-001', NULL),
('registrar', 'registrar@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Registrar', 'User', 'R-2025-001', NULL),
('dean_cs', 'dean.cs@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Dean', 'Computer Science', 'D-2025-001', 1),
('teacher1', 'teacher@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'John', 'Smith', 'T-2025-001', 1),
('finance', 'finance@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 'Finance', 'Officer', 'F-2025-001', NULL),
('cashier', 'cashier@cjlg.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 'Cashier', 'User', 'C-2025-001', NULL);

-- Note: Password for all users except admin is 'password123'
-- Admin password is 'admin123'

-- ================================================
-- ADD CIRCULAR FOREIGN KEYS (AFTER ALL TABLES)
-- ================================================
ALTER TABLE system_users ADD FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;
ALTER TABLE departments ADD FOREIGN KEY (dean_id) REFERENCES system_users(id) ON DELETE SET NULL;
