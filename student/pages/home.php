<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.code as course_code FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();
$enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id = $student_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
?>
<div class="row">
    <div class="col-md-12">
        <h3>Welcome, <?php echo $student['firstname']; ?>!</h3>
        <p class="text-muted">Here's your enrollment summary</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Student Number</h6>
                        <h4><?php echo $student['student_number']; ?></h4>
                    </div>
                    <i class="fas fa-id-card fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Enrollment Status</h6>
                        <h4><?php echo $enrollment['status'] ?? 'Not Enrolled'; ?></h4>
                    </div>
                    <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Current Year</h6>
                        <h4><?php echo $student['year_level']; ?> Year</h4>
                    </div>
                    <i class="fas fa-layer-group fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Course Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Course:</strong></td>
                        <td><?php echo $student['course_code'] ?? 'Not assigned'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Major:</strong></td>
                        <td><?php echo $student['major'] ?? 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Year Level:</strong></td>
                        <td><?php echo $student['year_level']; ?> Year</td>
                    </tr>
                    <tr>
                        <td><strong>School Year:</strong></td>
                        <td><?php echo $enrollment['school_year'] ?? 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Semester:</strong></td>
                        <td><?php echo $enrollment['semester'] ?? 'N/A'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Announcements</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <strong><i class="fas fa-info-circle me-2"></i>Welcome!</strong>
                    <p class="mb-0">Welcome to the Student Portal. You can view your schedule, grades, and finance information here.</p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?page=schedule" class="btn btn-outline-primary"><i class="fas fa-calendar me-2"></i>View Schedule</a>
                    <a href="?page=finance" class="btn btn-outline-success"><i class="fas fa-coins me-2"></i>View Billing</a>
                </div>
            </div>
        </div>
    </div>
</div>
