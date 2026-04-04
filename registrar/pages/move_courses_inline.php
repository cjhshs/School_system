<?php
// Inline Move Courses panel for Departments (Phase A)
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">Please login to move courses.</div>';
    exit;
}

// Destination dept list
$dest_depts = $conn->query("SELECT id, name, code FROM departments ORDER BY name");

// Courses list with current dept
$move_courses = $conn->query("SELECT c.id, c.code, c.name, d.name as dept_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.code");
?>
<div class="card mt-4" style="border:1px solid #e5e7eb;">
  <div class="card-header bg-info text-white">Move Courses</div>
  <div class="card-body">
    <form method="POST" class="row g-3" onsubmit="return confirm('Move selected courses to the chosen department?')">
      <input type="hidden" name="action" value="move_courses">
      <div class="col-md-4">
        <label class="form-label">Destination Department</label>
        <select name="destination_dept_id" class="form-select" required>
          <option value="">Select Department</option>
          <?php while ($d = $dest_depts->fetch_assoc()): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name'] . ' (' . $d['code'] . ')'); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label">Courses</label>
        <div class="table-responsive" style="max-height: 180px; overflow:auto; border:1px solid #e5e7eb; border-radius:4px; padding:6px;">
          <table class="table table-sm mb-0">
            <thead><tr><th></th><th>Course</th><th>Code</th><th>Current Dept</th></tr></thead>
            <tbody>
              <?php while ($mc = $move_courses->fetch_assoc()): ?>
              <tr>
                <td><input type="checkbox" name="course_ids[]" value="<?php echo $mc['id']; ?>"></td>
                <td><?php echo htmlspecialchars($mc['name']); ?></td>
                <td><?php echo htmlspecialchars($mc['code']); ?></td>
                <td><?php echo htmlspecialchars($mc['dept_name'] ?? '-'); ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit">Move Selected</button>
      </div>
    </form>
  </div>
  </div>
<?php
?>
