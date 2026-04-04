<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

function generateEmployeeId($conn, $role_id) {
    $year = date('Y');
    $prefix = $year;
    $result = $conn->query("SELECT employee_id FROM system_users WHERE employee_id LIKE '$prefix%' ORDER BY employee_id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $num = intval(substr($result->fetch_assoc()['employee_id'], -4)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $role_id = intval($_POST['role_id']);
            $employee_id = generateEmployeeId($conn, $role_id);
            $username = $employee_id; // Username = Employee ID
            $email = trim($_POST['email']);
            $plain_password = trim($_POST['password']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
            
            $check = $conn->query("SELECT id FROM system_users WHERE username = '$username' OR email = '$email'");
            if ($check && $check->num_rows > 0) {
                $error = "Username or email already exists!";
            } elseif ($role_id == 3 && $department_id) {
                $dean_check = $conn->query("SELECT id FROM system_users WHERE role_id = 3 AND department_id = $department_id");
                if ($dean_check && $dean_check->num_rows > 0) {
                    $dept = $conn->query("SELECT name FROM departments WHERE id = $department_id")->fetch_assoc();
                    $error = "A Dean is already assigned to {$dept['name']}!";
                }
            }
            
            if (!$error) {
                $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
                $encrypted_password = encryptPassword($plain_password);
                $created_by = $_SESSION['user_id'];
                
                $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, password_encrypted, role_id, first_name, last_name, department_id, employee_id, created_by, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $dept_val = $department_id ? $department_id : null;
                $stmt->bind_param("ssssissisi", $username, $email, $hashed_password, $encrypted_password, $role_id, $first_name, $last_name, $dept_val, $employee_id, $created_by);
                
                if ($stmt->execute()) {
                    $dept_name = '';
                    if ($department_id) {
                        $dept = $conn->query("SELECT name FROM departments WHERE id = $department_id")->fetch_assoc();
                        $dept_name = " | Department: {$dept['name']}";
                    }
                    $message = "User created! Username/ID: <strong>$employee_id</strong> | Password: <strong>" . htmlspecialchars($plain_password) . "</strong>$dept_name";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            if ($id != $_SESSION['user_id']) {
                // Check for references in payments (foreign key constraint on payments.received_by)
                $ref = $conn->query("SELECT COUNT(*) AS c FROM payments WHERE received_by = $id");
                $refCount = $ref ? (int)$ref->fetch_assoc()['c'] : 0;
                if ($refCount > 0) {
                    $error = "Cannot delete user. There are $refCount payment(s) referencing this user.";
                } else {
                    // Remove dependent logs first due to FK constraint
                    $conn->query("DELETE FROM activity_logs WHERE user_id = $id");
                    // Then delete user
                    $conn->query("DELETE FROM system_users WHERE id = $id");
                    $message = "User and related logs deleted!";
                }
            } else {
                $error = "Cannot delete yourself!";
            }
        }
    }
}

$users = $conn->query("SELECT su.*, r.display_name as role_name, d.name as dept_name FROM system_users su JOIN roles r ON su.role_id = r.id LEFT JOIN departments d ON su.department_id = d.id ORDER BY r.hierarchy_level DESC, su.last_name ASC");
$roles = $conn->query("SELECT * FROM roles ORDER BY hierarchy_level DESC");
$all_departments = $conn->query("SELECT * FROM departments ORDER BY name");
$available_departments = $conn->query("SELECT d.* FROM departments d WHERE d.id NOT IN (SELECT department_id FROM system_users WHERE role_id = 3 AND department_id IS NOT NULL) ORDER BY d.name");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-users-cog"></i> User Management</h1>
        <p>Create and manage system users</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-user-plus"></i> Create New User</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="form-row">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="username" value="">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required placeholder="name@school.edu">
            </div>
            <div class="form-group">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role_id" id="roleSelect" class="form-select" required onchange="toggleDepartment()">
                    <option value="">Select Role</option>
                    <?php $roles = $conn->query("SELECT * FROM roles ORDER BY hierarchy_level DESC");
                    while ($role = $roles->fetch_assoc()): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['display_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" id="departmentField" style="display: none;">
                <label class="form-label">Department *</label>
                <select name="department_id" id="deptSelect" class="form-select">
                    <option value="">Select Department</option>
                    <?php while ($dept = $available_departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" id="passwordInput" class="form-control" required minlength="6" placeholder="Min 6 characters">
            </div>
            <div class="form-group" style="flex: 0 0 auto; align-self: flex-end;">
                <button type="button" class="btn btn-outline-primary" onclick="generatePwd()"><i class="fas fa-magic me-2"></i>Generate</button>
            </div>
            <div class="form-group" style="flex: 0 0 auto; align-self: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create User</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-building"></i> Dean Assignments</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php $all_depts = $conn->query("SELECT * FROM departments ORDER BY name"); ?>
            <?php while ($dept = $all_depts->fetch_assoc()): ?>
                <?php $dean = $conn->query("SELECT * FROM system_users WHERE role_id = 3 AND department_id = " . $dept['id'])->fetch_assoc(); ?>
                <div class="col-md-3 col-lg-2">
                    <div class="card <?php echo $dean ? 'border-success' : 'border-secondary'; ?>" style="border-width: 2px;">
                        <div class="card-body p-2 text-center">
                            <h6 class="mb-1"><?php echo htmlspecialchars($dept['code']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($dept['name']); ?></small>
                            <hr class="my-2">
                            <?php if ($dean): ?>
                                <span class="badge badge-success"><?php echo htmlspecialchars($dean['first_name'] . ' ' . $dean['last_name']); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary">No Dean</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-header">
        <h5 class="table-title"><i class="fas fa-users"></i> All Users</h5>
    </div>
    <div class="card-body pt-0">
        <div class="search-container">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input table-search" data-table="usersTable" placeholder="Search users by name, username, email, or role...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($user['employee_id'] ?: '-'); ?></code></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><span class="badge badge-<?php echo match($user['role_name']) { 'Super Admin' => 'danger', 'Registrar' => 'primary', 'Dean' => 'warning', 'Finance' => 'success', 'Teacher' => 'info', default => 'secondary' }; ?>"><?php echo htmlspecialchars($user['role_name']); ?></span></td>
                    <td><?php echo $user['dept_name'] ? htmlspecialchars($user['dept_name']) : '<span class="text-muted">-</span>'; ?></td>
                    <td><span class="status <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-icon btn-ghost text-info" title="View Password" onclick="viewPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')"><i class="fas fa-eye"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirmDelete(this);">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-icon btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Password Modal -->
<div class="modal fade" id="viewPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>User Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-2">Password for <strong id="modalUserName"></strong></p>
                <div class="input-group">
                    <input type="text" class="form-control text-center fw-bold" id="modalPassword" readonly style="font-size: 1.2rem; letter-spacing: 1px;">
                    <button class="btn btn-outline-primary" type="button" onclick="copyPassword()" title="Copy"><i class="fas fa-copy"></i></button>
                </div>
                <div id="passwordError" class="text-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDepartment() {
    const roleId = document.getElementById('roleSelect').value;
    const deptField = document.getElementById('departmentField');
    const deptSelect = document.getElementById('deptSelect');
    if (roleId == 3) {
        deptField.style.display = 'block';
        deptSelect.required = true;
    } else {
        deptField.style.display = 'none';
        deptSelect.required = false;
        deptSelect.value = '';
    }
}

function generatePwd() {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let pwd = '';
    for (let i = 0; i < 8; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('passwordInput').value = pwd;
}

function confirmDelete(form) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        form.submit();
    }
    return false;
}

function viewPassword(userId, userName) {
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalPassword').value = 'Loading...';
    document.getElementById('passwordError').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('viewPasswordModal'));
    modal.show();
    
    const formData = new FormData();
    formData.append('action', 'view_password');
    formData.append('user_id', userId);
    
    fetch('/enrollment_system/superadmin/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                document.getElementById('modalPassword').value = data.password;
            } else {
                document.getElementById('modalPassword').value = '';
                document.getElementById('passwordError').textContent = data.message;
                document.getElementById('passwordError').style.display = 'block';
            }
        } catch(e) {
            document.getElementById('modalPassword').value = '';
            document.getElementById('passwordError').textContent = 'Invalid server response.';
            document.getElementById('passwordError').style.display = 'block';
        }
    })
    .catch(err => {
        document.getElementById('modalPassword').value = '';
        document.getElementById('passwordError').textContent = 'Failed to retrieve password: ' + err.message;
        document.getElementById('passwordError').style.display = 'block';
    });
}

function copyPassword() {
    const pwdField = document.getElementById('modalPassword');
    navigator.clipboard.writeText(pwdField.value).then(() => {
        const btn = pwdField.nextElementSibling;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 1500);
    });
}

document.getElementById('searchUsers')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
