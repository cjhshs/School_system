<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dean') {
    header('Location: login.php');
    exit;
}

$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT su.*, d.name as dept_name, d.code as dept_code, d.id as dept_id 
                      FROM system_users su 
                      LEFT JOIN departments d ON su.department_id = d.id 
                      WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

$message = '';
$message_type = '';

function generateTeacherId($conn) {
    $year = date('Y');
    $prefix = 'T-' . $year . '-';
    $result = $conn->query("SELECT employee_id FROM system_users WHERE employee_id LIKE '$prefix%' ORDER BY employee_id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $num = intval(substr($result->fetch_assoc()['employee_id'], -3)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_teacher'])) {
        $employee_id = generateTeacherId($conn);
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $plain_password = $_POST['password'];
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
        $encrypted_password = encryptPassword($plain_password);
        
        $check = $conn->prepare("SELECT id FROM system_users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $employee_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = "Email or username already exists!";
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, password_encrypted, role_id, first_name, last_name, department_id, employee_id, created_by) VALUES (?, ?, ?, ?, 4, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $employee_id, $email, $hashed_password, $encrypted_password, $first_name, $last_name, $dept_id, $employee_id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "Teacher added!<br>Employee ID: <strong>$employee_id</strong><br>Password: <strong>" . htmlspecialchars($plain_password) . "</strong>";
                $message_type = 'success';
            } else {
                $message = "Error: " . $conn->error;
                $message_type = 'danger';
            }
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $teacher_id = intval($_POST['teacher_id']);
        $new_status = intval($_POST['current_status']) == 1 ? 0 : 1;
        $conn->query("UPDATE system_users SET is_active = $new_status WHERE id = $teacher_id AND role_id = 4");
        $message = "Teacher status updated!";
        $message_type = 'success';
    }
}

$teachers = $conn->query("SELECT * FROM system_users WHERE role_id = 4 AND department_id = $dept_id ORDER BY last_name, first_name");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-chalkboard-teacher"></i> Teachers</h1>
        <p>Manage teachers in <?php echo htmlspecialchars($dean['dept_name'] ?? ''); ?></p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i><?php echo $message; ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Teacher -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-user-plus me-2"></i>Add New Teacher</h5>
            </div>
            <div class="card-body">
                <form method="POST">
    <?php echo csrf_field(); ?>
                    <input type="hidden" name="add_teacher" value="1">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" name="password" id="teacherPassword" class="form-control" required minlength="6" value="password123">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateTeacherPwd()"><i class="fas fa-magic"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Add Teacher</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Teachers List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Teachers List</h5>
            </div>
            <div class="table-responsive">
                <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="teachersTable" placeholder="Search teachers..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <table class="table" id="teachersTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($teachers && $teachers->num_rows > 0): while ($t = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo $t['employee_id']; ?></code></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar"><?php echo strtoupper(substr($t['first_name'], 0, 1)); ?></div>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($t['email']); ?></td>
                            <td>
                                <span class="status <?php echo $t['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $t['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" title="View Password" onclick="viewTeacherPassword(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>')"><i class="fas fa-eye"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-warning" title="Reset Password" onclick="showTeacherReset(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>')"><i class="fas fa-key"></i></button>
                                <form method="POST" class="d-inline">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="toggle_status" value="1">
                                    <input type="hidden" name="teacher_id" value="<?php echo $t['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $t['is_active']; ?>">
                                    <button type="submit" class="btn btn-sm btn-<?php echo $t['is_active'] ? 'warning' : 'success'; ?>">
                                        <i class="fas fa-<?php echo $t['is_active'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <h4>No Teachers</h4>
                                    <p>Add teachers using the form on the left.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Password Modal -->
<div class="modal fade" id="viewTeacherPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Teacher Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-2">Password for <strong id="modalTeacherName"></strong></p>
                <p class="mb-2">Username: <code id="modalTeacherUsername"></code></p>
                <div class="input-group">
                    <input type="text" class="form-control text-center fw-bold" id="modalTeacherPassword" readonly style="font-size: 1.2rem; letter-spacing: 1px;">
                    <button class="btn btn-outline-primary" type="button" onclick="copyTeacherPassword()" title="Copy"><i class="fas fa-copy"></i></button>
                </div>
                <div id="teacherPasswordError" class="text-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetTeacherPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Teacher Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Reset password for <strong id="resetTeacherName"></strong></p>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="resetTeacherNewPassword" minlength="6" required placeholder="Min 6 characters">
                        <button class="btn btn-outline-secondary" type="button" onclick="generateResetTeacherPwd()"><i class="fas fa-magic"></i></button>
                    </div>
                </div>
                <div id="resetTeacherError" class="text-danger mt-2" style="display: none;"></div>
                <div id="resetTeacherSuccess" class="text-success mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="resetTeacherBtn" onclick="resetTeacherPassword()"><i class="fas fa-save me-1"></i>Reset</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentResetTeacherId = 0;

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

function generateTeacherPwd() {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let pwd = '';
    for (let i = 0; i < 8; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('teacherPassword').value = pwd;
}

function viewTeacherPassword(teacherId, teacherName) {
    document.getElementById('modalTeacherName').textContent = teacherName;
    document.getElementById('modalTeacherPassword').value = 'Loading...';
    document.getElementById('teacherPasswordError').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('viewTeacherPasswordModal'));
    modal.show();
    
    const formData = new FormData();
    formData.append('action', 'view_teacher_password');
    formData.append('teacher_id', teacherId);
    
    fetch('/enrollment_system/dean/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                document.getElementById('modalTeacherUsername').textContent = data.username;
                document.getElementById('modalTeacherPassword').value = data.password;
            } else {
                document.getElementById('modalTeacherPassword').value = '';
                document.getElementById('teacherPasswordError').textContent = data.message;
                document.getElementById('teacherPasswordError').style.display = 'block';
            }
        } catch(e) {
            document.getElementById('modalTeacherPassword').value = '';
            document.getElementById('teacherPasswordError').textContent = 'Invalid server response.';
            document.getElementById('teacherPasswordError').style.display = 'block';
        }
    })
    .catch(err => {
        document.getElementById('modalTeacherPassword').value = '';
        document.getElementById('teacherPasswordError').textContent = 'Failed: ' + err.message;
        document.getElementById('teacherPasswordError').style.display = 'block';
    });
}

function copyTeacherPassword() {
    const pwdField = document.getElementById('modalTeacherPassword');
    navigator.clipboard.writeText(pwdField.value).then(() => {
        const btn = pwdField.nextElementSibling;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 1500);
    });
}

function showTeacherReset(teacherId, teacherName) {
    currentResetTeacherId = teacherId;
    document.getElementById('resetTeacherName').textContent = teacherName;
    document.getElementById('resetTeacherNewPassword').value = '';
    document.getElementById('resetTeacherError').style.display = 'none';
    document.getElementById('resetTeacherSuccess').style.display = 'none';
    document.getElementById('resetTeacherBtn').disabled = false;
    
    const modal = new bootstrap.Modal(document.getElementById('resetTeacherPasswordModal'));
    modal.show();
}

function generateResetTeacherPwd() {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let pwd = '';
    for (let i = 0; i < 8; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('resetTeacherNewPassword').value = pwd;
}

function resetTeacherPassword() {
    const newPwd = document.getElementById('resetTeacherNewPassword').value;
    if (newPwd.length < 6) {
        document.getElementById('resetTeacherError').textContent = 'Password must be at least 6 characters.';
        document.getElementById('resetTeacherError').style.display = 'block';
        return;
    }
    
    document.getElementById('resetTeacherBtn').disabled = true;
    document.getElementById('resetTeacherError').style.display = 'none';
    
    const formData = new FormData();
    formData.append('action', 'reset_teacher_password');
    formData.append('teacher_id', currentResetTeacherId);
    formData.append('new_password', newPwd);
    
    fetch('/enrollment_system/dean/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                document.getElementById('resetTeacherSuccess').textContent = 'Password reset to: ' + newPwd;
                document.getElementById('resetTeacherSuccess').style.display = 'block';
            } else {
                document.getElementById('resetTeacherError').textContent = data.message;
                document.getElementById('resetTeacherError').style.display = 'block';
                document.getElementById('resetTeacherBtn').disabled = false;
            }
        } catch(e) {
            document.getElementById('resetTeacherError').textContent = 'Invalid server response.';
            document.getElementById('resetTeacherError').style.display = 'block';
            document.getElementById('resetTeacherBtn').disabled = false;
        }
    })
    .catch(err => {
        document.getElementById('resetTeacherError').textContent = 'Failed: ' + err.message;
        document.getElementById('resetTeacherError').style.display = 'block';
        document.getElementById('resetTeacherBtn').disabled = false;
    });
}
</script>
