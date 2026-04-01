<?php
echo '<h2>Registrar Guide</h2>';
echo '<p>Use this guide to test all flows for Admin, Registrar, Dean, Finance, Cashier, and Student portals.</p>';
echo '<ul>';
echo '<li>Login URLs:';
echo '<ul><li>Admin: /superadmin/login.php</li><li>Registrar: /registrar/login.php</li><li>Dean: /dean/login.php</li><li>Finance: /finance/login.php</li><li>Cashier: /registrar/login-cashier-sample</li><li>Student: /student/login.php</li></ul>';
echo '</li>';
echo '<li>Health checks: /registrar/test_health.php, /registrar/test_health2.php, /registrar/test_health3.php' ;
echo '</li>';
echo '<li>Move Courses: Departments > Move Courses (bulk) or /registrar/pages/move_courses.php</li>';
echo '<li>SOA Printing: /student/print_statement.php (print-friendly view) or print from finance page</li>';
echo '</ul>';
?>
