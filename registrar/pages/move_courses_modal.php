<?php
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">Please login to move courses.</div>';
    exit;
}

// Destination departments
$dest = $conn->query("SELECT id, name, code FROM departments ORDER BY name");

// Courses with current department
$courses = $conn->query("SELECT c.id, c.code, c.name, d.name as dept_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.code");
?>

<!-- Move Courses Modal (Bootstrap) -->
<div class="modal fade" id="moveCoursesModal" tabindex="-1" aria-labelledby="moveCoursesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="moveCoursesLabel">Move Courses</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="move_courses">
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Destination Department</label>
              <select name="destination_dept_id" class="form-select" required>
                <option value="">Select Department</option>
                <?php while ($d = $dest->fetch_assoc()): ?>
                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name'] . ' (' . $d['code'] . ')'); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
          <div class="table-responsive" style="max-height: 260px; overflow:auto; border:1px solid #e5e7eb; border-radius:6px;">
            <table class="table table-sm mb-0">
              <thead class="table-light"><tr>
                <th>Select</th><th>Course</th><th>Code</th><th>Current Dept</th>
              </tr></thead>
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Move Selected</button>
        </div>
      </form>
    </div>
  </div>
</div>
