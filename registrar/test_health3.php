<?php
require_once '../config.php';
require_once '../includes/bootstrap.php';
/** @var \Includes\db $db */

echo '<h2>Registrar Health Check (3)</h2>';

try {
    $db = $GLOBALS['db'];
    $rows = $db->getConn()->query("SELECT COUNT(*) as cnt FROM students");
    $students = $rows ? $rows->fetch_assoc()['cnt'] : 0;
    $rows = $db->getConn()->query("SELECT COUNT(*) as cnt FROM courses");
    $courses = $rows ? $rows->fetch_assoc()['cnt'] : 0;
    $rows = $db->getConn()->query("SELECT COUNT(*) as cnt FROM subjects");
    $subjects = $rows ? $rows->fetch_assoc()['cnt'] : 0;
    $rows = $db->getConn()->query("SELECT COUNT(*) as cnt FROM enrollments");
    $enrollments = $rows ? $rows->fetch_assoc()['cnt'] : 0;

    echo "<ul>";
    echo "<li>Students: $students</li>";
    echo "<li>Courses: $courses</li>";
    echo "<li>Subjects: $subjects</li>";
    echo "<li>Enrollments: $enrollments</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Health check error: ' . $e->getMessage() . '</div>';
}
?>
