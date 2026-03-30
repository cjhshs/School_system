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
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role_id = intval($_POST['role_id']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
            $employee_id = generateEmployeeId($conn, $role_id);
            
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
                $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, first_name, last_name, department_id, employee_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisssisi", $username, $email, $password, $role_id, $first_name, $last_name, $department_id, $employee_id, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $dept_name = '';
                    if ($department_id) {
                        $dept = $conn->query("SELECT name FROM departments WHERE id = $department_id")->fetch_assoc();
                        $dept_name = " | Department: {$dept['name']}";
                    }
                    $message = "User created! Employee ID: <strong>$employee_id</strong> | Password: <strong>{$_POST['password']}</strong>$dept_name";
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            if ($id != $_SESSION['user_id']) {
                $conn->query("DELETE FROM system_users WHERE id = $id");
                $message = "User deleted successfully!";
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
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" required placeholder="e.g., jsmith">
            </div>
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
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['display_name']; ?></option>
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
                    <td><code><?php echo $user['employee_id'] ?: '-'; ?></code></td>
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
                    <td><span class="badge badge-<?php echo match($user['role_name']) { 'Super Admin' => 'danger', 'Registrar' => 'primary', 'Dean' => 'warning', 'Finance' => 'success', 'Teacher' => 'info', default => 'secondary' }; ?>"><?php echo $user['role_name']; ?></span></td>
                    <td><?php echo $user['dept_name'] ? htmlspecialchars($user['dept_name']) : '<span class="text-muted">-</span>'; ?></td>
                    <td><span class="status <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirmDelete(this);">
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
