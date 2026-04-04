<?php
require_once '../config.php';

$message = '';
$message_type = '';
$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT department_id FROM system_users WHERE id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_passing_grade'])) {
        $department_id = intval($_POST['department_id']);
        $passing_grade = floatval($_POST['passing_grade']);
        if ($department_id === $dept_id) {
            $stmt = $conn->prepare("UPDATE departments SET passing_grade = ? WHERE id = ?");
            $stmt->bind_param("di", $passing_grade, $department_id);
            $stmt->execute();
            logActivity($conn, $dean_id, 'update_passing_grade', "Set passing grade to $passing_grade% for dept $department_id");
            $message = "Passing grade updated to $passing_grade%";
            $message_type = 'success';
        } else {
            $message = "You can only update your own department's settings.";
            $message_type = 'danger';
        }
    }
}

$departments = $conn->query("SELECT * FROM departments WHERE id = $dept_id ORDER BY name");
$default_passing = $departments->fetch_assoc()['passing_grade'] ?? 75;
$departments->data_seek(0);
$departments->data_seek(0);
?>

<h2><i class="fas fa-cog me-2"></i>Department Settings</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Update Passing Grade</h5>
            </div>
            <div class="card-body">
                <form method="POST">
    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Select Department</label>
                        <select name="department_id" class="form-select">
                            <?php while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?> (<?php echo $dept['passing_grade']; ?>%)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Passing Grade (%)</label>
                        <div class="input-group">
                            <input type="number" name="passing_grade" class="form-control form-control-lg" 
                                value="75" min="50" max="100" step="0.5" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Typical passing grade is 75%</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grade Preview:</label>
                        <div class="d-flex gap-2">
                            <div class="flex-fill text-center p-2 border rounded">
                                <div class="fw-bold">85%</div>
                                <small class="text-success">PASSED</small>
                            </div>
                            <div class="flex-fill text-center p-2 border rounded">
                                <div class="fw-bold">75%</div>
                                <small class="text-success">PASSED</small>
                            </div>
                            <div class="flex-fill text-center p-2 border rounded">
                                <div class="fw-bold">65%</div>
                                <small class="text-danger">FAILED</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_passing_grade" class="btn btn-warning btn-lg w-100">
                        <i class="fas fa-save me-2"></i>Update Passing Grade
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-globe me-2"></i>Update All Departments</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">Bulk updates are disabled for security. Update each department individually.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Department Summary</h5>
            </div>
            <div class="card-body">
                <?php $departments = $conn->query("SELECT * FROM departments ORDER BY name"); ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Passing Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                <td><code><?php echo htmlspecialchars($dept['code']); ?></code></td>
                                <td><span class="badge bg-warning"><?php echo $dept['passing_grade']; ?>%</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>About Passing Grade</h5>
            </div>
            <div class="card-body">
                <p>The <strong>Passing Grade</strong> determines the minimum percentage required for a student to pass a subject.</p>
                <ul>
                    <li><strong>75%</strong> - Standard passing grade used by most institutions</li>
                    <li><strong>70%</strong> - More lenient passing requirement</li>
                    <li><strong>80%</strong> - Stricter academic standard</li>
                </ul>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Students must meet or exceed the passing grade to receive a "Passed" remark.
                </div>
            </div>
        </div>
    </div>
</div>
