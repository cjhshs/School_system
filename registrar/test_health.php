<?php
require_once '../config.php';

$counts = [];
$counts['students'] = $conn->query("SELECT COUNT(*) AS cnt FROM students")->fetch_assoc()['cnt'];
$counts['courses'] = $conn->query("SELECT COUNT(*) AS cnt FROM courses")->fetch_assoc()['cnt'];
$counts['subjects'] = $conn->query("SELECT COUNT(*) AS cnt FROM subjects")->fetch_assoc()['cnt'];
$counts['enrollments'] = $conn->query("SELECT COUNT(*) AS cnt FROM enrollments")->fetch_assoc()['cnt'];

?><!DOCTYPE html>
<html>
 <head><title>Registrar Health</title></head>
 <body>
  <h1>Registrar Health Check</h1>
  <ul>
   <li>Students: <?php echo $counts['students']; ?></li>
   <li>Courses: <?php echo $counts['courses']; ?></li>
   <li>Subjects: <?php echo $counts['subjects']; ?></li>
   <li>Enrollments: <?php echo $counts['enrollments']; ?></li>
  </ul>
  <p>Data connectivity: OK</p>
 </body>
</html>
