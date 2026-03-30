<?php
require_once 'config.php';

echo "<h2>Testing Registrar Tables</h2>";

// Check tables
echo "<h3>Checking Tables:</h3>";
$tables = ['students', 'courses', 'enrollments', 'subjects', 'system_users'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
    $cnt = $result ? $result->fetch_assoc()['cnt'] : 0;
    echo "$table: $cnt records<br>";
}

// Check students
echo "<h3>Sample Students:</h3>";
$students = $conn->query("SELECT id, student_number, firstname, lastname, course_id FROM students LIMIT 5");
if ($students && $students->num_rows > 0) {
    while ($s = $students->fetch_assoc()) {
        echo "ID: {$s['id']} | {$s['student_number']} | {$s['firstname']} {$s['lastname']} | Course ID: {$s['course_id']}<br>";
    }
} else {
    echo "No students found!<br>";
}

// Check courses
echo "<h3>Sample Courses:</h3>";
$courses = $conn->query("SELECT id, code, name FROM courses LIMIT 5");
if ($courses && $courses->num_rows > 0) {
    while ($c = $courses->fetch_assoc()) {
        echo "ID: {$c['id']} | {$c['code']} | {$c['name']}<br>";
    }
} else {
    echo "No courses found!<br>";
}

// Check enrollments
echo "<h3>Sample Enrollments:</h3>";
$enroll = $conn->query("SELECT id, student_id, course_id, status FROM enrollments LIMIT 5");
if ($enroll && $enroll->num_rows > 0) {
    while ($e = $enroll->fetch_assoc()) {
        echo "ID: {$e['id']} | Student ID: {$e['student_id']} | Course ID: {$e['course_id']} | Status: {$e['status']}<br>";
    }
} else {
    echo "No enrollments found!<br>";
}
?>
