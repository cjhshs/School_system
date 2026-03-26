<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
$enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id = $student_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

$message = '';

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $stored_password = $student['password'];
    $password_valid = false;
    
    if (password_get_info($stored_password)['algo'] === 0) {
        $password_valid = ($current_password === $stored_password);
    } else {
        $password_valid = password_verify($current_password, $stored_password);
    }
    
    if (!$password_valid) {
        $message = '<div class="alert alert-danger">Current password is incorrect.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">New passwords do not match.</div>';
    } elseif (strlen($new_password) < 6) {
        $message = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE students SET password = '$hashed_password' WHERE id = $student_id");
        $message = '<div class="alert alert-success">Password changed successfully!</div>';
        $student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
    }
}
?>
<div class="row">
    <div class="col-md-12">
        <h3><i class="fas fa-user me-2"></i>My Profile</h3>
        <p class="text-muted">View and manage your personal information</p>
    </div>
</div>

<?php echo $message; ?>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if($student['profile_picture']): ?>
                    <img src="../uploads/<?php echo $student['profile_picture']; ?>" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-7x text-muted mb-3"></i>
                <?php endif; ?>
                <h4><?php echo $student['firstname'] . ' ' . $student['lastname']; ?></h4>
                <p class="text-muted"><?php echo $student['student_number']; ?></p>
                <span class="badge bg-<?php echo ($enrollment['status'] ?? '') == 'Confirmed' ? 'success' : 'warning'; ?>">
                    <?php echo $enrollment['status'] ?? 'Pending'; ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>First Name:</strong> <?php echo $student['firstname']; ?></p>
                        <p><strong>Middle Name:</strong> <?php echo $student['middlename'] ?: 'N/A'; ?></p>
                        <p><strong>Last Name:</strong> <?php echo $student['lastname']; ?></p>
                        <p><strong>Birth Date:</strong> <?php echo $student['birthdate'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Gender:</strong> <?php echo $student['gender'] ?: 'N/A'; ?></p>
                        <p><strong>Civil Status:</strong> <?php echo $student['civil_status'] ?: 'N/A'; ?></p>
                        <p><strong>Nationality:</strong> <?php echo $student['nationality']; ?></p>
                        <p><strong>Religion:</strong> <?php echo $student['religion'] ?: 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?php echo $student['email'] ?: 'N/A'; ?></p>
                        <p><strong>Contact Number:</strong> <?php echo $student['contact_no'] ?: 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Address:</strong> <?php echo $student['address'] ?: 'N/A'; ?></p>
                        <p><strong>City:</strong> <?php echo $student['city'] ?: 'N/A'; ?></p>
                        <p><strong>Province:</strong> <?php echo $student['province'] ?: 'N/A'; ?></p>
                        <p><strong>ZIP Code:</strong> <?php echo $student['zipcode'] ?: 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
