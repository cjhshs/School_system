<?php
require_once '../config.php';

$user = $conn->query("SELECT * FROM system_users WHERE id = " . intval($_SESSION['user_id']))->fetch_assoc();
$teacher_name = $user['first_name'] . ' ' . $user['last_name'];
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';

$search_sql = '';
if (!empty($search)) {
    $search_sql = " AND (s.student_number LIKE '%$search%' OR s.firstname LIKE '%$search%' OR s.lastname LIKE '%$search%')";
}

if ($subject_id) {
    $subject = $conn->query("SELECT * FROM subjects WHERE id = $subject_id")->fetch_assoc();
    $result = $conn->query("SELECT s.* 
        FROM students s 
        JOIN student_subjects ss ON s.id = ss.student_id 
        WHERE ss.subject_id = $subject_id AND ss.status = 'Enrolled' $search_sql
        ORDER BY s.lastname, s.firstname");
} else {
    $result = $conn->query("SELECT DISTINCT s.*, sub.id as subject_id, sub.subject_code, sub.description 
        FROM students s 
        JOIN student_subjects ss ON s.id = ss.student_id
        JOIN subjects sub ON ss.subject_id = sub.id
        WHERE sub.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%' $search_sql
        ORDER BY sub.subject_code, s.lastname");
}
$students = $result;
$student_count = $result ? $result->num_rows : 0;
?>

<h2><i class="fas fa-users me-2"></i>Students
    <?php if ($subject_id && isset($subject)): ?>
        - <?php echo htmlspecialchars($subject['subject_code']); ?>
    <?php endif; ?>
</h2>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="students">
            <?php if ($subject_id): ?>
                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
            <?php endif; ?>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or student number..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?page=students<?php echo $subject_id ? '&subject_id='.$subject_id : ''; ?>" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="mb-0">
            <?php if ($subject_id): ?>
                Students enrolled in <?php echo htmlspecialchars($subject['description'] ?? ''); ?>
            <?php else: ?>
                All My Students
            <?php endif; ?>
            <span class="badge bg-light text-dark float-end">
                <?php echo $student_count; ?> student(s)
            </span>
        </h5>
    </div>
    <div class="card-body">
        <?php if ($students && $students->num_rows > 0): ?>
            <div class="search-wrapper mb-3">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" data-table="studentsTable" placeholder="Search...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
            <table class="table table-striped datatable" id="studentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if (!$subject_id): ?>
                            <th>Subject</th>
                        <?php endif; ?>
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>Year Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <?php if (!$subject_id): ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['subject_code']); ?></strong>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($student['description'] ?? ''); ?></small>
                                </td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="dashboard.php?page=grade_entry&student_id=<?php echo $student['id']; ?><?php echo $subject_id ? '&subject_id='.$subject_id : ''; ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-edit me-1"></i>Grade
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if (!empty($search)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-search text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No students found matching "<?php echo htmlspecialchars($search); ?>"</p>
                    <a href="?page=students<?php echo $subject_id ? '&subject_id='.$subject_id : ''; ?>" class="btn btn-secondary">Clear Search</a>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">No students found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(function(input) {
    input.addEventListener('input', function() {
        const tableId = this.getAttribute('data-table');
        const table = document.getElementById(tableId);
        if (!table) return;
        const query = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
        const clearBtn = this.parentElement.querySelector('.search-clear');
        if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
