<?php
require_once '../config.php';

$message = '';
$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT department_id FROM system_users WHERE id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

if (isset($_GET['approve'])) {
    $grade_id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE grades g JOIN subjects s ON g.subject_id = s.id JOIN courses c ON s.course_code = c.code SET g.grade_status = 'Approved', g.approved_at = NOW() WHERE g.id = ? AND c.department_id = ?");
    $stmt->bind_param("ii", $grade_id, $dept_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        logActivity($conn, $dean_id, 'approve_grade', "Approved grade ID: $grade_id");
        $message = "Grade approved!";
    } else {
        $message = "Grade not found or not in your department.";
    }
}

if (isset($_GET['approve_all'])) {
    $stmt = $conn->prepare("UPDATE grades g JOIN subjects s ON g.subject_id = s.id JOIN courses c ON s.course_code = c.code SET g.grade_status = 'Approved', g.approved_at = NOW() WHERE g.grade_status = 'Submitted' AND c.department_id = ?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $count = $stmt->affected_rows;
    if ($count > 0) {
        logActivity($conn, $dean_id, 'approve_all_grades', "Approved $count grades for department $dept_id");
    }
    $message = "$count pending grade(s) approved for your department!";
}

$pending_grades = $conn->query("SELECT g.*, s.firstname, s.lastname, s.student_number, sub.subject_code, sub.description,
    su.first_name as teacher_first, su.last_name as teacher_last
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN subjects sub ON g.subject_id = sub.id
    LEFT JOIN system_users su ON g.teacher_id = su.id
    WHERE g.grade_status = 'Submitted' AND sub.course_code IN (SELECT code FROM courses WHERE department_id = $dept_id)
    ORDER BY sub.subject_code, s.lastname");

$default_passing = $conn->query("SELECT AVG(passing_grade) as avg_grade FROM departments")->fetch_assoc()['avg_grade'] ?? 75;
?>

<h2><i class="fas fa-chart-line me-2"></i>Approve Grades</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Grades for Approval (<?php echo $pending_grades->num_rows; ?>)</h5>
            <?php if ($pending_grades->num_rows > 0): ?>
                <a href="?page=grades&approve_all=1" class="btn btn-dark btn-sm" onclick="return confirm('Approve all pending grades?')">
                    <i class="fas fa-check-double me-1"></i>Approve All
                </a>
            <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('input', function() {
        const tableId = this.getAttribute('data-table');
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
        
        const clearBtn = this.parentElement.querySelector('.search-clear');
        clearBtn.style.display = filter ? 'block' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
    <div class="card-body">
        <p class="text-muted">Average Passing Grade: <strong><?php echo number_format($default_passing, 1); ?>%</strong></p>
        
        <?php if ($pending_grades && $pending_grades->num_rows > 0): ?>
            <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="pendingGradesTable" placeholder="Search pending grades..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
            <table class="table table-striped datatable" id="pendingGradesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student No.</th>
                        <th>Subject</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Average</th>
                        <th>Remarks</th>
                        <th>Teacher</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($grade = $pending_grades->fetch_assoc()): ?>
                        <?php 
                        $avg = $grade['final_grade'];
                        $passing = $default_passing;
                        $remark_class = $avg >= $passing ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($grade['lastname'] . ', ' . $grade['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($grade['student_number']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($grade['subject_code']); ?></strong>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($grade['description'] ?? ''); ?></small>
                            </td>
                            <td class="text-center"><?php echo $grade['prelim'] !== null ? number_format($grade['prelim'], 2) : '-'; ?></td>
                            <td class="text-center"><?php echo $grade['midterm'] !== null ? number_format($grade['midterm'], 2) : '-'; ?></td>
                            <td class="text-center"><?php echo $grade['final_exam'] !== null ? number_format($grade['final_exam'], 2) : '-'; ?></td>
                            <td class="text-center"><strong><?php echo $avg !== null ? number_format($avg, 2) : '-'; ?></strong></td>
                            <td class="text-center">
                                <span class="badge <?php echo $remark_class; ?>"><?php echo $grade['remarks'] ?? 'N/A'; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars(($grade['teacher_last'] ?? '') . ', ' . ($grade['teacher_first'] ?? '')); ?></td>
                            <td>
                                <a href="?page=grades&approve=<?php echo $grade['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this grade?')">
                                    <i class="fas fa-check me-1"></i>Approve
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No pending grades for approval.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recently Approved Grades</h5>
    </div>
    <div class="card-body">
        <?php
        $approved_grades = $conn->query("SELECT g.*, s.firstname, s.lastname, s.student_number, sub.subject_code, sub.description
            FROM grades g
            JOIN students s ON g.student_id = s.id
            JOIN subjects sub ON g.subject_id = sub.id
            WHERE g.grade_status = 'Approved'
            ORDER BY g.approved_at DESC LIMIT 20");
        ?>
        
        <?php if ($approved_grades && $approved_grades->num_rows > 0): ?>
            <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="approvedGradesTable" placeholder="Search approved grades..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
            <table class="table table-sm datatable" id="approvedGradesTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                        <th>Approved At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($grade = $approved_grades->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['lastname'] . ', ' . $grade['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($grade['subject_code']); ?></td>
                            <td><?php echo $grade['final_grade'] ? number_format($grade['final_grade'], 2) : '-'; ?></td>
                            <td><span class="badge bg-<?php echo $grade['remarks'] == 'Passed' ? 'success' : 'danger'; ?>"><?php echo $grade['remarks']; ?></span></td>
                            <td><small><?php echo $grade['approved_at'] ? date('M d, Y H:i', strtotime($grade['approved_at'])) : '-'; ?></small></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No approved grades yet.</p>
        <?php endif; ?>
    </div>
</div>
