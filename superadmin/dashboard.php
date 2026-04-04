<?php
require_once '../config.php';
ob_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

$base_url = '';
$portal_title = 'Super Admin Dashboard';
$portal_icon = 'fa-crown';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$nav_items = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['key' => 'users', 'label' => 'User Management', 'url' => 'dashboard.php?page=users', 'icon' => 'fas fa-users-cog'],
    ['key' => 'departments', 'label' => 'Departments & Deans', 'url' => 'dashboard.php?page=departments', 'icon' => 'fas fa-building'],
    ['key' => 'roles', 'label' => 'Roles & Permissions', 'url' => 'dashboard.php?page=roles', 'icon' => 'fas fa-shield-alt'],
    ['key' => 'fees', 'label' => 'Fees Management', 'url' => 'dashboard.php?page=fees', 'icon' => 'fas fa-coins'],
    ['divider' => true],
    ['key' => 'activity_logs', 'label' => 'Activity Logs', 'url' => 'dashboard.php?page=activity_logs', 'icon' => 'fas fa-history'],
];

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$current_page = $page;
$user_name = $_SESSION['username'];
$user_role = $_SESSION['user_role'];

include '../includes/portal_layout_start.php';
require_once dirname(__DIR__) . '/includes/cache.php';

// Get statistics with caching (30 second TTL)
$stats = Cache::get('superadmin_stats');
if (!$stats) {
    $stats = [
        'total_users' => $conn->query("SELECT COUNT(*) as c FROM system_users")->fetch_assoc()['c'],
        'total_deans' => $conn->query("SELECT COUNT(*) as c FROM system_users WHERE role_id = 3")->fetch_assoc()['c'],
        'total_teachers' => $conn->query("SELECT COUNT(*) as c FROM system_users WHERE role_id = 4")->fetch_assoc()['c'],
        'total_students' => $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'],
        'total_departments' => $conn->query("SELECT COUNT(*) as c FROM departments")->fetch_assoc()['c'],
    ];
    Cache::set('superadmin_stats', $stats, 30);
}
$total_users = $stats['total_users'];
$total_deans = $stats['total_deans'];
$total_teachers = $stats['total_teachers'];
$total_students = $stats['total_students'];
$total_departments = $stats['total_departments'];

// Recent activity
$recent_logs = null;
try {
    $recent_logs = $conn->query("SELECT al.*, su.username, su.first_name, su.last_name FROM activity_logs al LEFT JOIN system_users su ON al.user_id = su.id ORDER BY al.created_at DESC LIMIT 5");
} catch (Exception $e) {
    // Table doesn't exist
}

switch($page) {
    case 'users':
        $portal_title = 'User Management';
        include 'pages/users.php';
        break;
    case 'departments':
        $portal_title = 'Departments & Deans';
        include 'pages/departments.php';
        break;
    case 'roles':
        $portal_title = 'Roles & Permissions';
        include 'pages/roles.php';
        break;
    case 'fees':
        $portal_title = 'Fees Management';
        include 'pages/fees.php';
        break;
    case 'activity_logs':
        $portal_title = 'Activity Logs';
        include 'pages/activity_logs.php';
        break;
    case 'branches':
        $portal_title = 'Branches';
        include 'pages/branches.php';
        break;
    default:
        $portal_title = 'Super Admin Dashboard';
        // Dashboard content
        ?>
        <div class="page-header">
            <div class="page-header-left">
                <h1><i class="fas fa-crown"></i> Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>
            </div>
            <div class="page-header-right">
                <a href="?page=users" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4 col-lg-3">
                <div class="stat-card primary">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value"><?php echo number_format($total_users); ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card success">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Deans</div>
                            <div class="stat-value"><?php echo number_format($total_deans); ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card warning">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Teachers</div>
                            <div class="stat-value"><?php echo number_format($total_teachers); ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="stat-card info">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Departments</div>
                            <div class="stat-value"><?php echo number_format($total_departments); ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="?page=users" class="quick-action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add User</span>
                    </a>
                    <a href="?page=roles" class="quick-action-btn">
                        <i class="fas fa-shield-alt"></i>
                        <span>Manage Roles</span>
                    </a>
                    <a href="?page=activity_logs" class="quick-action-btn">
                        <i class="fas fa-history"></i>
                        <span>View Logs</span>
                    </a>
                    <a href="../index.php" class="quick-action-btn">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Site</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Users Table -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="table-header">
                        <h5 class="table-title"><i class="fas fa-users"></i> Recent Users</h5>
                        <a href="?page=users" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Employee ID</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = $conn->query("
                                    SELECT su.*, r.name as role_name, d.name as dept_name 
                                    FROM system_users su 
                                    LEFT JOIN roles r ON su.role_id = r.id 
                                    LEFT JOIN departments d ON su.department_id = d.id 
                                    ORDER BY su.created_at DESC LIMIT 10
                                ");
                                while ($user = $users->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($user['employee_id']); ?></code></td>
                                    <td><span class="badge badge-<?php echo $user['role_id'] == 1 ? 'danger' : ($user['role_id'] == 3 ? 'primary' : 'secondary'); ?>"><?php echo ucfirst($user['role_name']); ?></span></td>
                                    <td><?php echo $user['dept_name'] ? htmlspecialchars($user['dept_name']) : '<span class="text-muted">N/A</span>'; ?></td>
                                    <td><span class="status <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="table-header">
                        <h5 class="table-title"><i class="fas fa-history"></i> Recent Activity</h5>
                        <a href="?page=activity_logs" class="btn btn-sm btn-ghost">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if ($recent_logs && $recent_logs->num_rows > 0): ?>
                                <?php while ($log = $recent_logs->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($log['action']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['description']); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($log['created_at'])); ?></small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p class="mb-0">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
}
ob_end_flush();
?>

<?php include '../includes/portal_layout_end.php'; ?>
