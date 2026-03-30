<?php
require_once '../config.php';
$empId = '20260003';
$hash = password_hash('password123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("SELECT id FROM system_users WHERE (employee_id = ? OR username = ?) AND role_id = ?");
$stmt->bind_param("ssi", $empId, $empId, 6);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
  $row = $res->fetch_assoc();
  $conn->query("UPDATE system_users SET is_active = 1, password = '$hash' WHERE id = ".$row['id']);
  $status = "updated existing cashier";
} else {
  $conn->query("INSERT INTO system_users (username, password, role_id, employee_id, is_active) VALUES ('$empId', '$hash', 6, '$empId', 1)");
  $status = "inserted new cashier";
}
echo "Cashier login fix for $empId: $status<br/>";
echo "<a href=\"/enrollment_system/cashier/login.php\">Login as cashier</a>";
?>
