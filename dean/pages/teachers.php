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
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $check = $conn->query("SELECT id FROM system_users WHERE email = '$email' OR username = '$employee_id'");
        if ($check && $check->num_rows > 0) {
            $message = "Email or username already exists!";
            $message_type = 'danger';
        } else {
            $stmt = $conn->prepare("INSERT INTO system_users (username, email, password, role_id, first_name, last_name, department_id, employee_id, created_by) VALUES (?, ?, ?, 5, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $employee_id, $email, $hashed_password, $first_name, $last_name, $dept_id, $employee_id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "Teacher added!<br>Employee ID: <strong>$employee_id</strong><br>Password: <strong>$password</strong>";
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
                        <input type="password" name="password" class="form-control" required minlength="6" value="password123">
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
                                <form method="POST" class="d-inline">
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
