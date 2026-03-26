<?php
require_once '../config.php';

$user_id = $_SESSION['user_id'];

// Get user info
$user = $conn->query("SELECT * FROM system_users WHERE id = $user_id")->fetch_assoc();

$stats = [];
$stats['total_students'] = $conn->query("SELECT COUNT(*) as cnt FROM students")->fetch_assoc()['cnt'];
$stats['total_teachers'] = $conn->query("SELECT COUNT(*) as cnt FROM teachers")->fetch_assoc()['cnt'];
$stats['total_subjects'] = $conn->query("SELECT COUNT(*) as cnt FROM subjects")->fetch_assoc()['cnt'];
$stats['pending_grades'] = $conn->query("SELECT COUNT(*) as cnt FROM grades WHERE status = 'Submitted'")->fetch_assoc()['cnt'];
?>

<h2><i class="fas fa-home me-2"></i>Welcome, <?php echo $_SESSION['username']; ?></h2>
<p class="text-muted">Dean Dashboard</p>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users me-2"></i>Students</h5>
                <h2><?php echo $stats['total_students']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i>Teachers</h5>
                <h2><?php echo $stats['total_teachers']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book me-2"></i>Subjects</h5>
                <h2><?php echo $stats['total_subjects']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-clock me-2"></i>Pending Grades</h5>
                <h2><?php echo $stats['pending_grades']; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Employee ID:</th>
                        <td><code><?php echo htmlspecialchars($user['employee_id'] ?: 'N/A'); ?></code></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-warning">Dean</span></td>
                    </tr>
                </table>
                <a href="?page=settings" class="btn btn-dark"><i class="fas fa-cog me-1"></i>Settings</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="?page=grades" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-check-circle me-2"></i>Review Grades (<?php echo $stats['pending_grades']; ?>)
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=teachers" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Manage Teachers
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=subjects" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-book me-2"></i>Edit Subjects
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=settings" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-graduation-cap me-2"></i>Set Passing Grade
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Dean Capabilities</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Approve Grades</h6>
                        <small class="text-muted">Review and approve submitted grades</small>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-graduation-cap text-success me-2"></i>Set Passing Grade</h6>
                        <small class="text-muted">Adjust minimum passing grade per department</small>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-chalkboard-teacher text-success me-2"></i>Manage Teachers</h6>
                        <small class="text-muted">Add, edit, activate/deactivate teachers</small>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="fas fa-book text-success me-2"></i>Edit Subjects</h6>
                        <small class="text-muted">Update schedule, room, instructor</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-ban text-success me-2"></i>Block Students</h6>
                        <small class="text-muted">Block/unblock students from subjects</small>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-user-plus text-success me-2"></i>Assign Instructors</h6>
                        <small class="text-muted">Assign teachers to subjects</small>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-door-open text-success me-2"></i>Manage Capacity</h6>
                        <small class="text-muted">Set subject enrollment capacity</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
