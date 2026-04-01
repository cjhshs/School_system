<?php
require_once '../config.php';
require_once '../includes/bootstrap.php';
$db = $GLOBALS['db'];
$cn = $db->getConn();
echo '<h2>Registrar Test Harness</h2>';

function ok($msg){ echo '<div class="text-success">OK: '.$msg.'</div>'; }
function fail($msg){ echo '<div class="text-danger">FAIL: '.$msg.'</div>'; }

// Helpers
function getDeptIdByCode($code, $cn){ $r = $cn->query("SELECT id FROM departments WHERE code = '$code'"); if($r && $r->num_rows>0){ return (int)$r->fetch_assoc()['id']; } return null; }
function getCodeIdByCode($code, $cn){ $r = $cn->query("SELECT id FROM courses WHERE code = '$code'"); if($r && $r->num_rows>0){ return (int)$r->fetch_assoc()['id']; } return null; }

// Phase 1: Seed baseline departments
$codes = ['CET1','CET2']; foreach($codes as $c){ $cn->query("INSERT INTO departments (name, code, description, dean_id, is_active) VALUES ('Test $c','${c}','Test', NULL, 1) ON DUPLICATE KEY UPDATE name=VALUES(name)"); }
ok('seeded departments CET1/CET2');

// Phase 2: Seed a test course
$cn->query("INSERT INTO courses (code, name, department_id, duration_years, is_active) VALUES ('TEST101','Test Course', NULL, 4, 1) ON DUPLICATE KEY UPDATE name=VALUES(name)");
ok('seeded TEST101 course');

// Phase 3: Move TEST101 to CET1
$dept1 = getDeptIdByCode('CET1',$cn);
if ($dept1){ $cn->query("UPDATE courses SET department_id = $dept1 WHERE code = 'TEST101'"); ok('assigned TEST101 to CET1'); } else { fail('could not find CET1 department'); }

// Phase 4: Verify
$r = $cn->query("SELECT c.code, d.code as dept_code FROM courses c LEFT JOIN departments d ON c.department_id = d.id WHERE c.code='TEST101'"); if($r && $r->num_rows>0){ $row = $r->fetch_assoc(); ok('TEST101 dept -> '.$row['dept_code']); } else { fail('TEST101 not found after move'); }

echo '<hr/><p>Test complete. This harness prints simple steps and outcomes.</p>';
?>
