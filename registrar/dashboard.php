<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
    header('Location: login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$user_name = $_SESSION['username'] ?? 'Registrar';

// Get stats
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'] ?? 0;
$total_teachers = $conn->query("SELECT COUNT(*) as c FROM system_users WHERE role_id = 4")->fetch_assoc()['c'] ?? 0;
$total_courses = $conn->query("SELECT COUNT(*) as c FROM courses")->fetch_assoc()['c'] ?? 0;
$total_subjects = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'] ?? 0;
$pending_enrollments_result = $conn->query("SELECT COUNT(*) as c FROM enrollments WHERE status = 'Pending'");
$pending_enrollments = $pending_enrollments_result ? $pending_enrollments_result->fetch_assoc()['c'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <aside class="app-sidebar" style="background: linear-gradient(135deg, #3730a3 0%, #6366f1 100%);">
            <div class="sidebar-header" style="background: rgba(0,0,0,0.15);">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <div>Registrar Portal</div>
                        <small class="sidebar-subtitle">Records Management</small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($user_name); ?></h6>
                    <span class="user-role-badge">Registrar</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <div class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i><span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=courses" class="nav-link <?php echo $page == 'courses' ? 'active' : ''; ?>">
                            <i class="fas fa-graduation-cap"></i><span>Courses</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=students" class="nav-link <?php echo $page == 'students' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i><span>Students</span>
                            <?php if ($pending_enrollments > 0): ?>
                                <span class="badge bg-warning text-dark"><?php echo $pending_enrollments; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=enrollments" class="nav-link <?php echo $page == 'enrollments' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i><span>Enrollments</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=subjects" class="nav-link <?php echo $page == 'subjects' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i><span>Subjects</span>
                        </a>
                    </div>
                </div>
                    <div class="nav-item">
                        <a href="../cashier/login.php" class="nav-link" target="_blank">
                            <i class="fas fa-cash-register"></i><span>Cashier</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../student/login.php" class="nav-link" target="_blank">
                            <i class="fas fa-graduation-cap"></i><span>Student</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Sign Out</span></a>
            </div>
        </aside>

        <main class="app-main">
            <header class="app-header">
                <div class="header-left">
                    <button class="toggle-sidebar" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1 class="page-title">Registrar Dashboard</h1>
                        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-action" title="Notifications"><i class="fas fa-bell"></i></button>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="user-role">Registrar</div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="#"><i class="fas fa-user"></i> My Profile</a>
                            <div class="divider"></div>
                            <a href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i> Sign Out</a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="app-content" style="margin-top: 0; padding-top: 0;">
                <?php
                switch($page) {
                    case 'courses':
                        include 'pages/courses.php';
                        break;
                    case 'students':
                        include 'pages/students.php';
                        break;
                    case 'enrollments':
                        include 'pages/enrollments.php';
                        break;
                    case 'edit_student':
                        include 'pages/edit_student.php';
                        break;
                    default:
                        ?>
                        <div class="page-header">
                            <div class="page-header-left">
                                <h1><i class="fas fa-home"></i> Dashboard Overview</h1>
                                <p>Welcome, <?php echo htmlspecialchars($user_name); ?>!</p>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card primary">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Total Students</div>
                                            <div class="stat-value"><?php echo number_format($total_students); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card success">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Teachers</div>
                                            <div class="stat-value"><?php echo number_format($total_teachers); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card warning">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Courses</div>
                                            <div class="stat-value"><?php echo number_format($total_courses); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Subjects</div>
                                            <div class="stat-value"><?php echo number_format($total_subjects); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-book"></i></div>
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
                                    <a href="dashboard.php?page=students" class="quick-action-btn">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Student</span>
                                    </a>
                                    <a href="dashboard.php?page=enrollments" class="quick-action-btn">
                                        <i class="fas fa-clipboard-check"></i>
                                        <span>Process Enrollment</span>
                                    </a>
                                    <a href="dashboard.php?page=courses" class="quick-action-btn">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span>Manage Courses</span>
                                    </a>
                                    <a href="dashboard.php?page=subjects" class="quick-action-btn">
                                        <i class="fas fa-book"></i>
                                        <span>Manage Subjects</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Students -->
                        <div class="card">
                            <div class="table-header">
                                <h5 class="table-title"><i class="fas fa-users"></i> Recent Students</h5>
                                <a href="dashboard.php?page=students" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Student Number</th>
                                            <th>Course</th>
                                            <th>Year</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_students = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
                                        while ($student = $recent_students->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar"><?php echo strtoupper(substr($student['firstname'], 0, 1)); ?></div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($student['student_number']); ?></code></td>
                                            <td><?php echo htmlspecialchars($student['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                            <td><span class="status active"><?php echo $student['enrollment_status'] ?? 'Active'; ?></span></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                }
                ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/enrollment_system/js/modern.js"></script>
</body>
</html>
