<?php
require_once '../config.php';

$user = $conn->query("SELECT * FROM system_users WHERE id = " . intval($_SESSION['user_id']))->fetch_assoc();
$teacher_name = $user['first_name'] . ' ' . $user['last_name'];

$subjects = $conn->query("SELECT DISTINCT s.* FROM subjects s WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'");

$all_grades = $conn->query("SELECT g.*, s.firstname, s.lastname, s.student_number, sub.subject_code, sub.description
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN subjects sub ON g.subject_id = sub.id
    WHERE sub.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'
    ORDER BY sub.subject_code, s.lastname");
?>

<h2><i class="fas fa-chart-line me-2"></i>Encode Grades</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Select Subject to Encode</h5>
            </div>
            <div class="card-body">
                <?php if ($subjects && $subjects->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($subject = $subjects->fetch_assoc()): ?>
                            <a href="dashboard.php?page=grade_entry&subject_id=<?php echo $subject['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong>
                                        <small class="d-block text-muted"><?php echo htmlspecialchars($subject['description'] ?? ''); ?></small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No subjects assigned.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Grades</h5>
            </div>
            <div class="card-body">
                <?php if ($all_grades && $all_grades->num_rows > 0): ?>
                    <div class="search-wrapper mb-3">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" data-table="gradesTable" placeholder="Search...">
                        <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                    </div>
                    <table class="table table-sm" id="gradesTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($grade = $all_grades->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade['lastname'] . ', ' . $grade['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['subject_code']); ?></td>
                                    <td><?php echo $grade['final_grade'] ? number_format($grade['final_grade'], 2) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_class = $grade['status'] == 'Approved' ? 'bg-success' : ($grade['status'] == 'Submitted' ? 'bg-warning' : 'bg-secondary');
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $grade['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
        <?php else: ?>
            <p class="text-muted text-center">No grades recorded yet.</p>
        <?php endif; ?>
    </div>
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
