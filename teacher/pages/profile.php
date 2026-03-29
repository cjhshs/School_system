<?php
$teacher_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM system_users WHERE id = " . intval($teacher_id))->fetch_assoc();
$teacher_name = $user['first_name'] . ' ' . $user['last_name'];

// Get all subjects handled by this teacher
$all_subjects = $conn->query("SELECT s.*, c.name as course_name, c.department_id
    FROM subjects s 
    LEFT JOIN courses c ON s.course_code = c.code 
    WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'
    ORDER BY s.year_level, s.semester");

$subject_count = $all_subjects ? $all_subjects->num_rows : 0;
$major_count = ceil($subject_count / 2); // First half as major

// Separate major and minor subjects
$major_subjects = [];
$minor_subjects = [];
$idx = 0;
if ($all_subjects) {
    while ($row = $all_subjects->fetch_assoc()) {
        if ($idx < $major_count) {
            $major_subjects[] = $row;
        } else {
            $minor_subjects[] = $row;
        }
        $idx++;
    }
}

$message = '';
$error = '';

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $stored_password = $user['password'];
    
    if (!password_verify($current_password, $stored_password)) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE system_users SET password = '" . $hashed_password . "' WHERE id = " . intval($teacher_id));
        $message = 'Password changed successfully!';
        $user = $conn->query("SELECT * FROM system_users WHERE id = " . intval($teacher_id))->fetch_assoc();
    }
}
?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-user"></i> My Profile</h1>
        <p>View and manage your profile information</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-id-card me-2"></i>Teacher Information</h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <hr>
                
                <div class="text-start">
                    <p><strong><i class="fas fa-briefcase me-2"></i>Employee ID:</strong> <?php echo htmlspecialchars($user['employee_id'] ?? 'N/A'); ?></p>
                    <p><strong><i class="fas fa-user-tag me-2"></i>Role:</strong> Teacher</p>
                    <p><strong><i class="fas fa-clock me-2"></i>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-star me-2"></i>Major Subject(s)</h5>
            </div>
            <div class="card-body">
                <?php if (count($major_subjects) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Description</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Semester</th>
                                    <th>Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($major_subjects as $sub): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sub['description']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['course_code'] ?? 'N/A'); ?></td>
                                    <td><?php echo $sub['year_level']; ?></td>
                                    <td><?php echo htmlspecialchars($sub['semester']); ?></td>
                                    <td><?php echo $sub['units']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No major subjects assigned.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (count($minor_subjects) > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title"><i class="fas fa-plus-circle me-2"></i>Minor Subject(s)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Description</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Semester</th>
                                <th>Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($minor_subjects as $sub): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sub['description']); ?></td>
                                <td><?php echo htmlspecialchars($sub['course_code'] ?? 'N/A'); ?></td>
                                <td><?php echo $sub['year_level']; ?></td>
                                <td><?php echo htmlspecialchars($sub['semester']); ?></td>
                                <td><?php echo $sub['units']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-12">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
    margin: 0 auto;
}
</style>
