<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dean') {
    header('Location: login.php');
    exit;
}

$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT su.*, d.name as dept_name, d.code as dept_code, d.id as dept_id 
                      FROM system_users su 
                      LEFT JOIN departments d ON su.department_id = d.id 
                      WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_department'])) {
        $passing_grade = floatval($_POST['passing_grade']);
        
        $sql = "UPDATE departments SET passing_grade = $passing_grade WHERE id = $dept_id";
        
        if ($conn->query($sql)) {
            $message = "Passing grade updated successfully!";
            $message_type = 'success';
            // Refresh dean data
            $dean = $conn->query("SELECT su.*, d.name as dept_name, d.code as dept_code, d.id as dept_id 
                                  FROM system_users su 
                                  LEFT JOIN departments d ON su.department_id = d.id 
                                  WHERE su.id = $dean_id")->fetch_assoc();
        } else {
            $message = "Error: " . $conn->error;
            $message_type = 'danger';
        }
    }
}

// Get department details
$department = $conn->query("SELECT d.*, COUNT(t.id) as teacher_count 
                            FROM departments d 
                            LEFT JOIN system_users t ON d.id = t.department_id AND t.role_id = 4
                            WHERE d.id = $dept_id 
                            GROUP BY d.id")->fetch_assoc();

// Get courses in this department
$courses = $conn->query("SELECT * FROM courses WHERE department_id = $dept_id ORDER BY code");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-building"></i> My Department</h1>
        <p><?php echo htmlspecialchars($dean['dept_name'] ?? ''); ?></p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i><?php echo $message; ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Department Details -->
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Department Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar" style="width: 80px; height: 80px; font-size: 2rem; background: var(--primary-500); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <?php echo strtoupper(substr($dean['dept_code'] ?? '', 0, 2)); ?>
                    </div>
                    <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($dean['dept_name'] ?? 'N/A'); ?></h4>
                    <span class="badge badge-primary"><?php echo htmlspecialchars($dean['dept_code'] ?? ''); ?></span>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Department Code</span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($dean['dept_code'] ?? 'N/A'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Dean</span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($dean['first_name'] . ' ' . $dean['last_name']); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Courses</span>
                    <span class="fw-semibold"><?php echo $courses ? $courses->num_rows : 0; ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Teachers</span>
                    <span class="fw-semibold"><?php echo $department['teacher_count'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <!-- Edit Passing Grade -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-graduation-cap me-2"></i>Passing Grade</h5>
            </div>
            <div class="card-body">
                <form method="POST">
    <?php echo csrf_field(); ?>
                    <input type="hidden" name="update_department" value="1">
                    <div class="form-group">
                        <label class="form-label">Minimum Passing Grade (%)</label>
                        <div class="input-group">
                            <input type="number" name="passing_grade" class="form-control" 
                                   value="<?php echo $department['passing_grade'] ?? 75; ?>" 
                                   min="50" max="100" step="0.5" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Students must score this or higher to pass.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Update Passing Grade
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Courses in Department -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-graduation-cap me-2"></i>Courses in <?php echo htmlspecialchars($dean['dept_code'] ?? ''); ?></h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Major</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $courses = $conn->query("SELECT * FROM courses WHERE department_id = $dept_id ORDER BY code");
                        if ($courses && $courses->num_rows > 0): 
                            $i = 1;
                            while ($course = $courses->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($course['code']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($course['name']); ?></strong></td>
                            <td><?php echo $course['major'] ? htmlspecialchars($course['major']) : '<span class="text-muted">-</span>'; ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-graduation-cap"></i></div>
                                    <h4>No Courses</h4>
                                    <p>No courses assigned to this department.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Department Overview</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-card primary mb-0">
                            <div class="stat-icon"><i class="fas fa-book"></i></div>
                            <div class="stat-value"><?php echo $courses ? $courses->num_rows : 0; ?></div>
                            <div class="stat-label">Courses</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card success mb-0">
                            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div class="stat-value"><?php echo $department['teacher_count'] ?? 0; ?></div>
                            <div class="stat-label">Teachers</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card warning mb-0">
                            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div class="stat-value"><?php echo $department['passing_grade'] ?? 75; ?>%</div>
                            <div class="stat-label">Pass Grade</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
