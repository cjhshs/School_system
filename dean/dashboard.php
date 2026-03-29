<?php
require_once "../config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "dean") {
    header("Location: login.php");
    exit;
}

$dean_id = $_SESSION["user_id"];
$page = isset($_GET["page"]) ? $_GET["page"] : "dashboard";
$user_name = $_SESSION["username"];

// Get dean info with department
$dean = $conn->query("SELECT su.*, d.name as dept_name, d.code as dept_code 
                      FROM system_users su 
                      LEFT JOIN departments d ON su.department_id = d.id 
                      WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

// Get stats
$total_teachers = $conn->query("SELECT COUNT(*) as c FROM system_users WHERE role_id = 4 AND department_id = $dept_id")->fetch_assoc()['c'];
$total_subjects = $conn->query("SELECT COUNT(*) as c FROM subjects s JOIN courses c ON s.course_code = c.code WHERE c.department_id = $dept_id")->fetch_assoc()['c'];
$pending_grades = $conn->query("SELECT COUNT(*) as c FROM grades WHERE status = 'Submitted'")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(DISTINCT ss.student_id) as c FROM student_subjects ss 
                                 JOIN subjects s ON ss.subject_id = s.id 
                                 JOIN courses c ON s.course_code = c.code
                                 WHERE c.department_id = $dept_id")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Portal - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-user-graduate"></i>
                    <div>
                        <div>Dean Portal</div>
                        <small class="sidebar-subtitle"><?php echo htmlspecialchars($dean['dept_code'] ?? ''); ?></small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($user_name); ?></h6>
                    <span class="user-role-badge">Dean</span>
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
                        <a href="dashboard.php?page=departments" class="nav-link <?php echo $page == 'departments' ? 'active' : ''; ?>">
                            <i class="fas fa-building"></i><span>Departments</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=teachers" class="nav-link <?php echo $page == 'teachers' ? 'active' : ''; ?>">
                            <i class="fas fa-chalkboard-teacher"></i><span>Teachers</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=subjects" class="nav-link <?php echo $page == 'subjects' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i><span>Subjects</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=fees" class="nav-link <?php echo $page == 'fees' ? 'active' : ''; ?>">
                            <i class="fas fa-coins"></i><span>Fees</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=grades" class="nav-link <?php echo $page == 'grades' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i><span>Approve Grades</span>
                            <?php if ($pending_grades > 0): ?>
                                <span class="badge bg-danger"><?php echo $pending_grades; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="nav-divider"></div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=settings" class="nav-link <?php echo $page == 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i><span>Settings</span>
                        </a>
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
                        <h1 class="page-title"><?php echo $dean['dept_name'] ?? 'Dean Dashboard'; ?></h1>
                        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-action" title="Notifications"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="user-role">Dean</div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="#"><i class="fas fa-user"></i> My Profile</a>
                            <a href="dashboard.php?page=settings"><i class="fas fa-cog"></i> Settings</a>
                            <div class="divider"></div>
                            <a href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i> Sign Out</a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="app-content">
                <?php
                switch($page) {
                    case "departments":
                        include "pages/departments.php";
                        break;
                    case "teachers":
                        include "pages/teachers.php";
                        break;
                    case "grades":
                        include "pages/grades.php";
                        break;
                    case "subjects":
                        include "pages/subjects.php";
                        break;
                    case "fees":
                        include "pages/fees.php";
                        break;
                    case "settings":
                        include "pages/settings.php";
                        break;
                    default:
                        // Dashboard content
                        ?>
                        <div class="page-header">
                            <div class="page-header-left">
                                <h1><i class="fas fa-home"></i> Dashboard Overview</h1>
                                <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card primary">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Total Teachers</div>
                                            <div class="stat-value"><?php echo number_format($total_teachers); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card success">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Subjects</div>
                                            <div class="stat-value"><?php echo number_format($total_subjects); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card warning">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Students</div>
                                            <div class="stat-value"><?php echo number_format($total_students); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card danger">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Pending Grades</div>
                                            <div class="stat-value"><?php echo number_format($pending_grades); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
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
                                    <a href="dashboard.php?page=teachers" class="quick-action-btn">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Teacher</span>
                                    </a>
                                    <a href="dashboard.php?page=subjects" class="quick-action-btn">
                                        <i class="fas fa-book-medical"></i>
                                        <span>Manage Subjects</span>
                                    </a>
                                    <a href="dashboard.php?page=grades" class="quick-action-btn">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Review Grades</span>
                                    </a>
                                    <a href="dashboard.php?page=settings" class="quick-action-btn">
                                        <i class="fas fa-cog"></i>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Recent Pending Grades -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="table-header">
                                        <h5 class="table-title"><i class="fas fa-clock"></i> Pending Grade Approvals</h5>
                                        <a href="dashboard.php?page=grades" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Subject</th>
                                                    <th>Grade</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $pending = $conn->query("SELECT g.*, s.firstname, s.lastname, s.student_number, sub.subject_code, sub.description
                                                                         FROM grades g
                                                                         JOIN students s ON g.student_id = s.id
                                                                         JOIN subjects sub ON g.subject_id = sub.id
                                                                         JOIN courses c ON sub.course_code = c.code
                                                                         WHERE g.grade_status = 'Submitted' AND c.department_id = $dept_id
                                                                         ORDER BY g.created_at DESC LIMIT 5");
                                                if ($pending && $pending->num_rows > 0):
                                                    while ($grade = $pending->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="avatar"><?php echo strtoupper(substr($grade['firstname'], 0, 1)); ?></div>
                                                            <div>
                                                                <div class="fw-semibold"><?php echo htmlspecialchars($grade['lastname'] . ', ' . $grade['firstname']); ?></div>
                                                                <small class="text-muted"><?php echo $grade['student_number']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($grade['subject_code']); ?></td>
                                                    <td class="fw-bold"><?php echo $grade['final_grade'] ? number_format($grade['final_grade'], 1) : '-'; ?></td>
                                                    <td><span class="status pending"><?php echo $grade['status']; ?></span></td>
                                                    <td>
                                                        <a href="dashboard.php?page=grades" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                                    </td>
                                                </tr>
                                                <?php endwhile; else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                        <p class="mb-0">No pending grades</p>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Department Info -->
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-building"></i> Department Info</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <div class="avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto 12px;">
                                                <?php echo htmlspecialchars($dean['dept_code'] ?? ''); ?>
                                            </div>
                                            <h4><?php echo htmlspecialchars($dean['dept_name'] ?? 'N/A'); ?></h4>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Teachers</span>
                                            <span class="fw-semibold"><?php echo $total_teachers; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Subjects</span>
                                            <span class="fw-semibold"><?php echo $total_subjects; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Students</span>
                                            <span class="fw-semibold"><?php echo $total_students; ?></span>
                                        </div>
                                    </div>
                                </div>
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
