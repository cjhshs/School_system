<?php
require_once '../../config.php';

// auth: ensure registrar has access to move courses; in practice, this page should be accessible by super admin or registrar with proper perms
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'move_courses') {
    $destDept = isset($_POST['destination_dept_id']) ? intval($_POST['destination_dept_id']) : 0;
    $courseIds = isset($_POST['course_ids']) ? array_map('intval', $_POST['course_ids']) : [];
    if (!$destDept) {
        $error = 'Please select a destination department.';
    } elseif (empty($courseIds)) {
        $error = 'No courses selected.';
    } else {
        // Move all selected courses to destination in one query
        $ids = implode(',', $courseIds);
        $conn->query("UPDATE courses SET department_id = $destDept WHERE id IN ($ids)");
        $message = 'Courses moved successfully to the selected department.';
    }
}

// Fetch data for UI
$depts = $conn->query("SELECT id, code, name FROM departments ORDER BY name");
$courses = $conn->query("SELECT c.id, c.code, c.name, c.department_id, d.name as dept_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.code");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Move Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    .card { margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="mb-3">Move Courses to Department</h2>
    <?php if ($message): ?><div class="alert alert-success mb-3"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger mb-3"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" class="row g-3">
      <input type="hidden" name="action" value="move_courses">
      <div class="col-md-6">
        <label class="form-label">Destination Department</label>
        <select name="destination_dept_id" class="form-select" required>
          <option value="">Select department</option>
          <?php while ($d = $depts->fetch_assoc()): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name'] . ' (' . $d['code'] . ')'); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 align-self-end">
        <button class="btn btn-primary" type="submit">Move Selected Courses</button>
      </div>

      <div class="col-12">
        <table class="table table-bordered table-sm mt-2">
          <thead class="table-light">
            <tr>
              <th>Select</th>
              <th>Course</th>
              <th>Code</th>
              <th>Current Department</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $courses->fetch_assoc()): ?>
            <tr>
              <td><input type="checkbox" name="course_ids[]" value="<?php echo $row['id']; ?>"></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['code']); ?></td>
              <td><?php echo htmlspecialchars($row['dept_name'] ?? '-'); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</body>
</html>
