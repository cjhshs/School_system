<?php
require_once '../config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_passing_grade'])) {
        $department_id = intval($_POST['department_id']);
        $passing_grade = floatval($_POST['passing_grade']);
        $conn->query("UPDATE departments SET passing_grade = $passing_grade WHERE id = $department_id");
        $message = "Passing grade updated to $passing_grade%";
        $message_type = 'success';
    }
    
    if (isset($_POST['update_all_passing'])) {
        $passing_grade = floatval($_POST['passing_grade']);
        $conn->query("UPDATE departments SET passing_grade = $passing_grade");
        $message = "All departments updated to $passing_grade%";
        $message_type = 'success';
    }
}

$departments = $conn->query("SELECT * FROM departments ORDER BY name");
$default_passing = $departments->fetch_assoc()['passing_grade'] ?? 75;
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
                <form method="POST">
                    <p class="text-muted">Set the same passing grade for all departments.</p>
                    <div class="input-group mb-3">
                        <input type="number" name="passing_grade" class="form-control" value="75" min="50" max="100" step="0.5" required>
                        <span class="input-group-text">%</span>
                        <button type="submit" name="update_all_passing" class="btn btn-secondary">
                            <i class="fas fa-save me-1"></i>Apply to All
                        </button>
                    </div>
                </form>
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
