<?php
require_once '../config.php';

$teacher_id = intval($_SESSION['user_id']);

$subjects = $conn->query("SELECT s.id, s.subject_code, s.description, s.units, s.schedule, s.room, s.max_students, s.is_active, c.name as course_name 
    FROM subjects s 
    LEFT JOIN courses c ON s.course_code = c.code 
    WHERE s.instructor_id = $teacher_id 
    ORDER BY s.subject_code");
?>

<h2><i class="fas fa-book me-2"></i>My Subjects</h2>

<div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="mb-0">Assigned Subjects</h5>
    </div>
    <div class="card-body">
        <?php if ($subjects && $subjects->num_rows > 0): ?>
            <div class="search-wrapper mb-3">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" data-table="subjectsTable" placeholder="Search...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
            <table class="table table-striped datatable" id="subjectsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Units</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($subject = $subjects->fetch_assoc()): ?>
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
                                <a href="dashboard.php?page=students&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-users me-1"></i>Students
                                </a>
                                <a href="dashboard.php?page=grade_entry&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-edit me-1"></i>Grades
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
