<?php
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (s.student_number LIKE '%$search_esc%' OR s.firstname LIKE '%$search_esc%' OR s.lastname LIKE '%$search_esc%' OR s.email LIKE '%$search_esc%')";
}

$students = $conn->query("SELECT s.*, c.code as course_code, c.name as course_name 
    FROM students s 
    LEFT JOIN courses c ON s.course_id = c.id 
    $where
    ORDER BY s.lastname, s.firstname 
    LIMIT 50");
?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-users"></i> Search Students</h1>
        <p>Find and view student information</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="students">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search by Student Number, Name, or Email" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Students (<?php echo $students ? $students->num_rows : 0; ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student #</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students && $students->num_rows > 0): ?>
                        <?php while ($s = $students->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($s['student_number']); ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($s['email'] ?? ''); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($s['course_code'] ?? 'N/A'); ?></td>
                            <td><?php echo $s['year_level']; ?></td>
                            <td>
                                <?php 
                                $status_class = match($s['enrollment_status']) {
                                    'Enrolled' => 'success',
                                    'Pending' => 'warning',
                                    'Dropped' => 'danger',
                                    'Graduated' => 'info',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>"><?php echo $s['enrollment_status']; ?></span>
                            </td>
                            <td>
                                <a href="?page=payments&student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-money-bill-wave"></i> Payment
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No students found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
