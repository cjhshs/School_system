<?php
require_once '../config.php';
require_once dirname(dirname(__DIR__)) . '/includes/pagination.php';

$message = '';
$per_page = 25;
$current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;

if (isset($_POST['delete_student'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    logActivity($conn, $_SESSION['user_id'], 'delete_student', "Deleted student ID: $id");
    $message = '<div class="alert alert-success">Student deleted!</div>';
}

if (isset($_POST['enroll_student'])) {
    $id = intval($_POST['id']);
    $school_year = date('Y') . '-' . (date('Y') + 1);
    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, school_year, semester, status) VALUES (?, ?, 1, 'Pending')");
        $stmt->bind_param("is", $id, $school_year);
        $stmt->execute();
        logActivity($conn, $_SESSION['user_id'], 'enroll_student', "Enrolled student ID: $id");
        $message = '<div class="alert alert-success">Student enrolled!</div>';
    } else {
        $message = '<div class="alert alert-warning">Student already enrolled!</div>';
    }
}

$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$pagination = new Pagination($total_students, $per_page, $current_page);
$students = $conn->query("SELECT s.id, s.student_number, s.firstname, s.lastname, s.email, s.phone, s.year_level, c.code as course_code, e.status as enrollment_status FROM students s LEFT JOIN courses c ON s.course_id = c.id LEFT JOIN enrollments e ON e.student_id = s.id ORDER BY s.lastname, s.firstname LIMIT {$pagination->per_page} OFFSET {$pagination->offset}");
?>

<div class="row">
    <div class="col-md-12">
        <h2>Manage Students</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>All Students</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="studentsTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <table class="table table-striped" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Enrolled</th>
                            <th>Credentials</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students->data_seek(0);
                        while($row = $students->fetch_assoc()): 
                            $enrolled_status = $row['enrollment_status'] ?? null;
                        ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($row['student_number']); ?></code></td>
                            <td><?php echo htmlspecialchars(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($row['course_code'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                            <td>
                                <?php if($enrolled_status): ?>
                                    <span class="badge bg-<?php echo $enrolled_status == 'Confirmed' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($enrolled_status); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" title="View Password" onclick="viewStudentPassword(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')); ?>')"><i class="fas fa-eye"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Reset Password" onclick="showResetForm(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')); ?>')"><i class="fas fa-key"></i></button>
                            </td>
                            <td>
                                <a href="dashboard.php?page=edit_student&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                                <?php if(!$enrolled_status): ?>
                                <form method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="enroll_student" class="btn btn-sm btn-success">Enroll</button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_student" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php echo $pagination->render('?page=students'); ?>
            </div>
        </div>
    </div>
</div>

<!-- View Password Modal -->
<div class="modal fade" id="viewPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Student Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-2">Password for <strong id="modalStudentName"></strong></p>
                <p class="mb-2">Username: <code id="modalStudentUsername"></code></p>
                <div class="input-group">
                    <input type="text" class="form-control text-center fw-bold" id="modalStudentPassword" readonly style="font-size: 1.2rem; letter-spacing: 1px;">
                    <button class="btn btn-outline-primary" type="button" onclick="copyStudentPassword()" title="Copy"><i class="fas fa-copy"></i></button>
                </div>
                <div id="studentPasswordError" class="text-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Student Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Reset password for <strong id="resetStudentName"></strong></p>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="resetNewPassword" minlength="6" required placeholder="Min 6 characters">
                        <button class="btn btn-outline-secondary" type="button" onclick="generateStudentPwd()"><i class="fas fa-magic"></i></button>
                    </div>
                </div>
                <div id="resetPasswordError" class="text-danger mt-2" style="display: none;"></div>
                <div id="resetPasswordSuccess" class="text-success mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="resetPasswordBtn" onclick="resetStudentPassword()"><i class="fas fa-save me-1"></i>Reset Password</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentResetStudentId = 0;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.search-input').forEach(function(input) {
        input.addEventListener('keyup', function() {
            var filter = this.value.toLowerCase();
            var tableId = this.getAttribute('data-table');
            var table = document.getElementById(tableId);
            var clearBtn = this.parentElement.querySelector('.search-clear');
            if (clearBtn) clearBtn.style.display = filter ? 'block' : 'none';
            if (!table) return;
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    });
});

function clearSearch(btn) {
    var input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    btn.style.display = 'none';
    input.dispatchEvent(new Event('keyup'));
}

function viewStudentPassword(studentId, studentName) {
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStudentPassword').value = 'Loading...';
    document.getElementById('studentPasswordError').style.display = 'none';
    
    var modal = new bootstrap.Modal(document.getElementById('viewPasswordModal'));
    modal.show();
    
    var formData = new FormData();
    formData.append('action', 'view_student_password');
    formData.append('student_id', studentId);
    
    fetch('/enrollment_system/registrar/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.text(); })
    .then(function(text) {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                document.getElementById('modalStudentUsername').textContent = data.username;
                document.getElementById('modalStudentPassword').value = data.password;
            } else {
                document.getElementById('modalStudentPassword').value = '';
                document.getElementById('studentPasswordError').textContent = data.message;
                document.getElementById('studentPasswordError').style.display = 'block';
            }
        } catch(e) {
            document.getElementById('modalStudentPassword').value = '';
            document.getElementById('studentPasswordError').textContent = 'Invalid server response.';
            document.getElementById('studentPasswordError').style.display = 'block';
        }
    })
    .catch(function(err) {
        document.getElementById('modalStudentPassword').value = '';
        document.getElementById('studentPasswordError').textContent = 'Failed to retrieve password: ' + err.message;
        document.getElementById('studentPasswordError').style.display = 'block';
    });
}

function copyStudentPassword() {
    var pwdField = document.getElementById('modalStudentPassword');
    navigator.clipboard.writeText(pwdField.value).then(function() {
        var btn = pwdField.nextElementSibling;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function() { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 1500);
    });
}

function showResetForm(studentId, studentName) {
    currentResetStudentId = studentId;
    document.getElementById('resetStudentName').textContent = studentName;
    document.getElementById('resetNewPassword').value = '';
    document.getElementById('resetPasswordError').style.display = 'none';
    document.getElementById('resetPasswordSuccess').style.display = 'none';
    document.getElementById('resetPasswordBtn').disabled = false;
    
    var modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

function generateStudentPwd() {
    var chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    var pwd = '';
    for (var i = 0; i < 8; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('resetNewPassword').value = pwd;
}

function resetStudentPassword() {
    var newPwd = document.getElementById('resetNewPassword').value;
    if (newPwd.length < 6) {
        document.getElementById('resetPasswordError').textContent = 'Password must be at least 6 characters.';
        document.getElementById('resetPasswordError').style.display = 'block';
        return;
    }
    
    document.getElementById('resetPasswordBtn').disabled = true;
    document.getElementById('resetPasswordError').style.display = 'none';
    
    var formData = new FormData();
    formData.append('action', 'reset_student_password');
    formData.append('student_id', currentResetStudentId);
    formData.append('new_password', newPwd);
    
    fetch('/enrollment_system/registrar/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.text(); })
    .then(function(text) {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                document.getElementById('resetPasswordSuccess').textContent = 'Password reset to: ' + newPwd;
                document.getElementById('resetPasswordSuccess').style.display = 'block';
            } else {
                document.getElementById('resetPasswordError').textContent = data.message;
                document.getElementById('resetPasswordError').style.display = 'block';
                document.getElementById('resetPasswordBtn').disabled = false;
            }
        } catch(e) {
            document.getElementById('resetPasswordError').textContent = 'Invalid server response.';
            document.getElementById('resetPasswordError').style.display = 'block';
            document.getElementById('resetPasswordBtn').disabled = false;
        }
    })
    .catch(function(err) {
        document.getElementById('resetPasswordError').textContent = 'Failed: ' + err.message;
        document.getElementById('resetPasswordError').style.display = 'block';
        document.getElementById('resetPasswordBtn').disabled = false;
    });
}
</script>
