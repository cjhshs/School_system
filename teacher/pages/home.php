<?php
require_once '../config.php';

$teacher_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM system_users WHERE id = $teacher_id")->fetch_assoc();
$teacher_name = $user['first_name'] . ' ' . $user['last_name'];

$stats = [];
$stats['subjects'] = $conn->query("SELECT COUNT(DISTINCT id) as cnt FROM subjects WHERE instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'")->fetch_assoc()['cnt'];
$stats['students'] = $conn->query("SELECT COUNT(DISTINCT ss.student_id) as cnt FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'")->fetch_assoc()['cnt'];
$stats['pending_grades'] = $conn->query("SELECT COUNT(*) as cnt FROM grades WHERE teacher_id = $teacher_id AND (prelim IS NULL OR midterm IS NULL OR final_exam IS NULL)")->fetch_assoc()['cnt'];
$stats['submitted_grades'] = $conn->query("SELECT COUNT(*) as cnt FROM grades WHERE teacher_id = $teacher_id AND status = 'Submitted'")->fetch_assoc()['cnt'];

$my_subjects = $conn->query("SELECT DISTINCT s.* FROM subjects s WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'");
?>

<h2><i class="fas fa-home me-2"></i>Welcome, <?php echo htmlspecialchars($teacher_name); ?></h2>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book me-2"></i>My Subjects</h5>
                <h2><?php echo $stats['subjects']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users me-2"></i>Total Students</h5>
                <h2><?php echo $stats['students']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-edit me-2"></i>Pending Grades</h5>
                <h2><?php echo $stats['pending_grades']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-check me-2"></i>Submitted</h5>
                <h2><?php echo $stats['submitted_grades']; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>My Assigned Subjects</h5>
            </div>
            <div class="card-body">
                <?php if ($my_subjects && $my_subjects->num_rows > 0): ?>
                    <table class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject Code</th>
                                <th>Description</th>
                                <th>Units</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Students</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($subject = $my_subjects->fetch_assoc()): ?>
                                <?php 
                                $student_count = $conn->query("SELECT COUNT(*) as cnt FROM student_subjects WHERE subject_id = " . intval($subject['id']))->fetch_assoc()['cnt'];
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($subject['description'] ?? ''); ?></td>
                                    <td><?php echo $subject['units']; ?></td>
                                    <td><?php echo htmlspecialchars($subject['schedule'] ?? 'TBA'); ?></td>
                                    <td><?php echo htmlspecialchars($subject['room'] ?? 'TBA'); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $student_count; ?></span></td>
                                    <td>
                                        <a href="dashboard.php?page=students&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-users me-1"></i>View Students
                                        </a>
                                        <a href="dashboard.php?page=grade_entry&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>Encode Grades
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center">No subjects assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
