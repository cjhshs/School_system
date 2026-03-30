<?php
require_once '../config.php';

$total_courses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];
?>

<div class="row">
    <div class="col-md-12">
        <h2>Dashboard Overview</h2>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book"></i> Total Courses</h5>
                <h2><?php echo $total_courses; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-graduate"></i> Total Students</h5>
                <h2><?php echo $total_students; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-clipboard-list"></i> Total Enrollments</h5>
                <h2><?php echo $total_enrollments; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>Recent Enrollments</h5>
            </div>
            <div class="card-body">
                <?php
                $recent = $conn->query("SELECT e.*, s.firstname, s.lastname, c.code as course_code
                    FROM enrollments e 
                    LEFT JOIN students s ON e.student_id = s.id
                    LEFT JOIN courses c ON e.course_id = c.id
                    ORDER BY e.created_at DESC LIMIT 5");
                if ($recent && $recent->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
                                <td><?php echo $row['course_code'] ?? '-'; ?></td>
                                <td><span class="badge bg-<?php echo ($row['status'] ?? 'Pending') == 'Confirmed' ? 'success' : (($row['status'] ?? 'Pending') == 'Cancelled' ? 'danger' : 'warning'); ?>"><?php echo $row['status'] ?? 'Pending'; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No enrollments yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="dashboard.php?page=courses" class="btn btn-primary mb-2"><i class="fas fa-plus"></i> Add Course</a>
                <a href="dashboard.php?page=students" class="btn btn-success mb-2"><i class="fas fa-user-plus"></i> View Students</a>
                <a href="dashboard.php?page=enrollments" class="btn btn-info mb-2"><i class="fas fa-clipboard-check"></i> Manage Enrollments</a>
            </div>
        </div>
    </div>
</div>
