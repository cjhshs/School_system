<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.code as course_code, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();

$passing_grade = 75;
$dept_result = $conn->query("SELECT passing_grade FROM departments LIMIT 1");
if ($dept_result && $dept_result->num_rows > 0) {
    $passing_grade = $dept_result->fetch_assoc()['passing_grade'];
}

$enrolled_subjects = $conn->query("
    SELECT sub.id, sub.subject_code, sub.description, sub.units, sub.semester, sub.year_level,
           g.id as grade_id, g.prelim, g.midterm, g.final_exam, g.final_grade, g.remarks, g.grade_status
    FROM student_subjects ss
    JOIN subjects sub ON ss.subject_id = sub.id
    LEFT JOIN grades g ON sub.id = g.subject_id AND g.student_id = $student_id
    WHERE ss.student_id = $student_id
    ORDER BY sub.semester, sub.subject_code
");

$total_subjects = 0;
$with_grades = 0;
$approved_grades = 0;
$passed_count = 0;
$failed_count = 0;
$pending_grades = 0;
$incomplete_count = 0;

$subjects_data = [];
if ($enrolled_subjects) {
    while ($row = $enrolled_subjects->fetch_assoc()) {
        $subjects_data[] = $row;
        $total_subjects++;
        
        if ($row['grade_id']) {
            $with_grades++;
            if ($row['grade_status'] == 'Approved') {
                $approved_grades++;
                if ($row['remarks'] == 'Passed') $passed_count++;
                elseif ($row['remarks'] == 'Failed') $failed_count++;
            } elseif ($row['grade_status'] == 'Submitted') {
                $pending_grades++;
            }
        } else {
            $incomplete_count++;
        }
    }
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-chart-line"></i> My Grades</h1>
        <p>View your academic performance and grades</p>
    </div>
    <div class="page-header-right">
        <a href="export_grades.php" target="_blank" class="btn btn-primary">
            <i class="fas fa-download me-2"></i>Download PDF
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Subjects</div>
                    <div class="stat-value"><?php echo $total_subjects; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Passed</div>
                    <div class="stat-value"><?php echo $passed_count; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Failed</div>
                    <div class="stat-value"><?php echo $failed_count; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?php echo $pending_grades; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="stat-card secondary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">No Grade</div>
                    <div class="stat-value"><?php echo $incomplete_count; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Passing Grade</div>
                    <div class="stat-value"><?php echo $passing_grade; ?>%</div>
                </div>
                <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Grades Table -->
<div class="card">
    <div class="table-header">
        <h5 class="table-title"><i class="fas fa-graduation-cap"></i> Academic Record</h5>
        <div>
            <span class="badge badge-success me-1">Approved: <?php echo $approved_grades; ?></span>
            <span class="badge badge-warning me-1">Pending: <?php echo $pending_grades; ?></span>
            <span class="badge badge-secondary">No Grade: <?php echo $incomplete_count; ?></span>
        </div>
    </div>
    <div class="search-wrapper mb-3">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" data-table="gradesTable" placeholder="Search grades...">
        <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
    </div>
    <div class="table-responsive">
        <table class="table" id="gradesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th class="text-center">Units</th>
                    <th class="text-center">Sem</th>
                    <th class="text-center">Prelim</th>
                    <th class="text-center">Midterm</th>
                    <th class="text-center">Final</th>
                    <th class="text-center">Average</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($subjects_data)): $i = 1; foreach ($subjects_data as $subject): ?>
                    <?php
                    $has_grade = $subject['grade_id'] !== null;
                    $status_class = !$has_grade ? 'secondary' : ($subject['grade_status'] == 'Approved' ? 'success' : ($subject['grade_status'] == 'Submitted' ? 'warning' : 'info'));
                    $remark_class = $subject['remarks'] == 'Passed' ? 'badge-success' : ($subject['remarks'] == 'Failed' ? 'badge-danger' : 'badge-secondary');
                    ?>
                    <tr class="<?php echo !$has_grade ? 'text-muted' : ''; ?>">
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($subject['description']); ?></td>
                        <td class="text-center"><span class="badge badge-info"><?php echo $subject['units']; ?></span></td>
                        <td class="text-center"><?php echo $subject['semester']; ?></td>
                        <td class="text-center">
                            <?php if ($has_grade && $subject['prelim'] !== null): ?>
                                <?php echo number_format($subject['prelim'], 1); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($has_grade && $subject['midterm'] !== null): ?>
                                <?php echo number_format($subject['midterm'], 1); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($has_grade && $subject['final_exam'] !== null): ?>
                                <?php echo number_format($subject['final_exam'], 1); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-bold">
                            <?php if ($has_grade && $subject['final_grade'] !== null): ?>
                                <?php echo number_format($subject['final_grade'], 2); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!$has_grade): ?>
                                <span class="badge badge-secondary">No Grade</span>
                            <?php else: ?>
                                <?php
                                $status_text = $subject['grade_status'];
                                if ($subject['grade_status'] == 'Draft') $status_text = 'Draft';
                                if ($subject['grade_status'] == 'Submitted') $status_text = 'Pending';
                                if ($subject['grade_status'] == 'Approved') $status_text = 'Approved';
                                ?>
                                <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($subject['grade_status'] == 'Approved'): ?>
                                <span class="badge <?php echo $remark_class; ?>"><?php echo $subject['remarks']; ?></span>
                            <?php elseif ($subject['grade_status'] == 'Submitted'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif ($has_grade): ?>
                                <span class="badge badge-secondary"><?php echo $subject['remarks'] ?: 'Incomplete'; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                                <h4>No Subjects Enrolled</h4>
                                <p>Please contact the registrar if you believe this is an error.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Legend Cards -->
<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Grade Status Legend</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <span class="badge badge-secondary me-2">No Grade</span>
                        <small class="text-muted">Subject not yet graded</small>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge badge-info me-2">Draft</span>
                        <small class="text-muted">Saved but not submitted</small>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge badge-warning me-2">Pending</span>
                        <small class="text-muted">Awaiting dean approval</small>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge badge-success me-2">Approved</span>
                        <small class="text-muted">Official grade</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-check-circle"></i> Remarks Legend</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <span class="badge badge-success me-2">Passed</span>
                        <small class="text-muted"><?php echo $passing_grade; ?>% and above</small>
                    </div>
                    <div class="col-6 mb-2">
                        <span class="badge badge-danger me-2">Failed</span>
                        <small class="text-muted">Below <?php echo $passing_grade; ?>%</small>
                    </div>
                    <div class="col-6">
                        <span class="badge badge-secondary me-2">Incomplete</span>
                        <small class="text-muted">Grade incomplete</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
setTimeout(function(){ location.reload(); }, 60000);
</script>
<script>
document.querySelectorAll('.search-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var tableId = this.getAttribute('data-table');
        var table = document.getElementById(tableId);
        if (!table) return;
        var filter = this.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
        });
    });
});

function clearSearch(btn) {
    var input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
