<?php
require_once '../config.php';

$teacher_id = intval($_SESSION['user_id']);
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;
$message = '';
$message_type = '';

if (isset($_POST['submit_grades']) && isset($_POST['subject_id'])) {
    $sid = intval($_POST['subject_id']);
    $conn->query("UPDATE grades SET grade_status = 'Submitted', submitted_at = NOW() WHERE subject_id = $sid AND teacher_id = $teacher_id AND grade_status = 'Draft'");
    $message = 'Grades submitted for approval!';
    $message_type = 'success';
    $subject_id = $sid;
}

if ($subject_id) {
    $subject = $conn->query("SELECT * FROM subjects WHERE id = $subject_id")->fetch_assoc();
    $students = $conn->query("SELECT s.*, g.prelim, g.midterm, g.final_exam, g.final_grade, g.remarks, g.grade_status
        FROM students s 
        JOIN student_subjects ss ON s.id = ss.student_id
        LEFT JOIN grades g ON s.id = g.student_id AND g.subject_id = $subject_id
        WHERE ss.subject_id = $subject_id AND ss.status = 'Enrolled'
        ORDER BY s.lastname, s.firstname");
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-edit"></i> Encode Grades</h1>
        <p><?php echo isset($subject) ? htmlspecialchars($subject['subject_code'] . ' - ' . ($subject['description'] ?? '')) : 'Select a subject'; ?></p>
    </div>
    <div class="page-header-right">
        <a href="dashboard.php?page=grades" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
        <?php if ($subject_id && isset($subject)): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="submit_grades" value="1">
            <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
            <button type="submit" class="btn btn-success" onclick="return confirm('Submit all grades for dean approval?')">
                <i class="fas fa-paper-plane me-2"></i>Submit All Grades
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($subject_id && isset($subject)): ?>
    <!-- Subject Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h4 class="mb-0"><?php echo htmlspecialchars($subject['subject_code']); ?></h4>
                            <small class="text-muted"><?php echo htmlspecialchars($subject['description'] ?? ''); ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-label">Schedule</div>
                            <div class="fw-semibold"><?php echo $subject['schedule'] ?: 'TBA'; ?></div>
                        </div>
                        <div class="col-4">
                            <div class="stat-label">Room</div>
                            <div class="fw-semibold"><?php echo $subject['room'] ?: 'TBA'; ?></div>
                        </div>
                        <div class="col-4">
                            <div class="stat-label">Students</div>
                            <div class="fw-semibold"><?php echo $students ? $students->num_rows : 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Entry Table -->
    <div class="card">
        <div class="table-header">
            <h5 class="table-title"><i class="fas fa-table"></i> Student Grades</h5>
        </div>
        <?php if ($students && $students->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table grade-input-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th class="text-center">Prelim</th>
                        <th class="text-center">Midterm</th>
                        <th class="text-center">Final</th>
                        <th class="text-center">Average</th>
                        <th class="text-center">Remarks</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td>
                                <div class="student-info">
                                    <div class="avatar"><?php echo strtoupper(substr($student['firstname'], 0, 1)); ?></div>
                                    <div>
                                        <div class="name"><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></div>
                                        <div class="number"><?php echo htmlspecialchars($student['student_number']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <input type="number" name="prelim_<?php echo $student['id']; ?>" 
                                    id="prelim_<?php echo $student['id']; ?>"
                                    class="form-control text-center" 
                                    value="<?php echo $student['prelim']; ?>" 
                                    min="0" max="100" step="0.01"
                                    placeholder="0-100"
                                    onchange="updateGrade(<?php echo $student['id']; ?>)">
                            </td>
                            <td class="text-center">
                                <input type="number" name="midterm_<?php echo $student['id']; ?>" 
                                    id="midterm_<?php echo $student['id']; ?>"
                                    class="form-control text-center" 
                                    value="<?php echo $student['midterm']; ?>" 
                                    min="0" max="100" step="0.01"
                                    placeholder="0-100"
                                    onchange="updateGrade(<?php echo $student['id']; ?>)">
                            </td>
                            <td class="text-center">
                                <input type="number" name="final_<?php echo $student['id']; ?>" 
                                    id="final_<?php echo $student['id']; ?>"
                                    class="form-control text-center" 
                                    value="<?php echo $student['final_exam']; ?>" 
                                    min="0" max="100" step="0.01"
                                    placeholder="0-100"
                                    onchange="updateGrade(<?php echo $student['id']; ?>)">
                            </td>
                            <td class="text-center">
                                <strong class="grade-average" id="avg_<?php echo $student['id']; ?>">
                                    <?php echo $student['final_grade'] ? number_format($student['final_grade'], 2) : '-'; ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $student['remarks'] == 'Passed' ? 'badge-success' : ($student['remarks'] == 'Failed' ? 'badge-danger' : 'badge-secondary'); ?>" 
                                      id="remark_<?php echo $student['id']; ?>">
                                    <?php echo $student['remarks'] ?: 'Incomplete'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm" onclick="saveGrade(<?php echo $student['id']; ?>, <?php echo $subject_id; ?>)">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                <h4>No Students Enrolled</h4>
                <p>There are no students enrolled in this subject yet.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Grade Information -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-info-circle"></i> Grade Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge badge-success">75-100</span>
                        <span>Passing Grade Range</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge badge-danger">0-74</span>
                        <span>Failed Grade Range</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-secondary">-</span>
                        <span>Incomplete (Missing Grades)</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Average Calculation:</strong></p>
                    <code>(Prelim + Midterm + Final) / 3</code>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Grade Submission:</strong></p>
                    <p class="text-muted mb-0">Click "Save" per student or "Submit All Grades" to send for dean approval.</p>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-book"></i></div>
                <h4>Select a Subject</h4>
                <p>Please select a subject from the list to encode grades.</p>
                <a href="dashboard.php?page=grades" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i>Back to Subjects</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function updateGrade(studentId) {
    var prelim = document.getElementById('prelim_' + studentId).value;
    var midterm = document.getElementById('midterm_' + studentId).value;
    var finalExam = document.getElementById('final_' + studentId).value;
    
    var avgField = document.getElementById('avg_' + studentId);
    var remarkField = document.getElementById('remark_' + studentId);
    
    if (prelim && midterm && finalExam) {
        var avg = ((parseFloat(prelim) + parseFloat(midterm) + parseFloat(finalExam)) / 3).toFixed(2);
        avgField.textContent = avg;
        
        var remark = avg >= 75 ? 'Passed' : 'Failed';
        remarkField.textContent = remark;
        remarkField.className = 'badge ' + (remark === 'Passed' ? 'badge-success' : 'badge-danger');
    } else {
        avgField.textContent = '-';
        remarkField.textContent = 'Incomplete';
        remarkField.className = 'badge badge-secondary';
    }
}

function saveGrade(studentId, subjectId) {
    var prelim = document.getElementById('prelim_' + studentId).value;
    var midterm = document.getElementById('midterm_' + studentId).value;
    var finalExam = document.getElementById('final_' + studentId).value;
    
    var formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('subject_id', subjectId);
    formData.append('prelim', prelim);
    formData.append('midterm', midterm);
    formData.append('final_exam', finalExam);
    
    fetch('save_grade.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateGrade(studentId);
            showToast('success', 'Grade Saved', 'Student grade has been saved successfully.');
        } else {
            showToast('error', 'Error', data.message || 'Failed to save grade.');
        }
    })
    .catch(error => {
        showToast('error', 'Error', 'Failed to save grade. Please try again.');
    });
}
</script>
