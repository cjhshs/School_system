<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$user_name = $_SESSION['username'];

// Get teacher info
$user = $conn->query("SELECT * FROM system_users WHERE id = " . intval($teacher_id))->fetch_assoc();
$teacher_name = $user['first_name'] . ' ' . $user['last_name'];

// Get stats
$my_subjects = $conn->query("SELECT DISTINCT s.* FROM subjects s WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'");
$subject_count = $my_subjects ? $my_subjects->num_rows : 0;
$my_students = $conn->query("SELECT COUNT(DISTINCT ss.student_id) as c FROM student_subjects ss 
                              JOIN subjects s ON ss.subject_id = s.id 
                              WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'")->fetch_assoc()['c'];
$pending_grades = $conn->query("SELECT COUNT(*) as c FROM grades g 
                                JOIN subjects s ON g.subject_id = s.id 
                                WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%' 
                                AND g.status = 'Draft'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <aside class="app-sidebar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="sidebar-header" style="background: rgba(0,0,0,0.15);">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>
                        <div>Teacher Portal</div>
                        <small class="sidebar-subtitle">Grade Management</small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($user_name); ?></h6>
                    <span class="user-role-badge">Teacher</span>
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
                        <a href="dashboard.php?page=subjects" class="nav-link <?php echo $page == 'subjects' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i><span>My Subjects</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=students" class="nav-link <?php echo $page == 'students' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i><span>Students</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=grades" class="nav-link <?php echo $page == 'grades' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i><span>Encode Grades</span>
                            <?php if ($pending_grades > 0): ?>
                                <span class="badge bg-warning text-dark"><?php echo $pending_grades; ?></span>
                            <?php endif; ?>
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
                        <h1 class="page-title">Teacher Dashboard</h1>
                        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-action" title="Notifications"><i class="fas fa-bell"></i></button>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="user-role">Teacher</div>
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

            <div class="app-content">
                <?php
                switch($page) {
                    case 'subjects':
                        include 'pages/subjects.php';
                        break;
                    case 'students':
                        include 'pages/students.php';
                        break;
                    case 'grades':
                        include 'pages/grades.php';
                        break;
                    case 'grade_entry':
                        include 'pages/grade_entry.php';
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
                                            <div class="stat-label">My Subjects</div>
                                            <div class="stat-value"><?php echo number_format($subject_count); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card success">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Total Students</div>
                                            <div class="stat-value"><?php echo number_format($my_students); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card warning">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Pending Grades</div>
                                            <div class="stat-value"><?php echo number_format($pending_grades); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">This Semester</div>
                                            <div class="stat-value"><?php echo date('F'); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
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
                                    <a href="dashboard.php?page=grades" class="quick-action-btn">
                                        <i class="fas fa-edit"></i>
                                        <span>Encode Grades</span>
                                    </a>
                                    <a href="dashboard.php?page=subjects" class="quick-action-btn">
                                        <i class="fas fa-book"></i>
                                        <span>View Subjects</span>
                                    </a>
                                    <a href="dashboard.php?page=students" class="quick-action-btn">
                                        <i class="fas fa-users"></i>
                                        <span>My Students</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- My Subjects -->
                        <div class="card">
                            <div class="table-header">
                                <h5 class="table-title"><i class="fas fa-book"></i> My Subjects</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Description</th>
                                            <th>Schedule</th>
                                            <th>Room</th>
                                            <th>Students</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $subjects = $conn->query("SELECT DISTINCT s.*, 
                                            (SELECT COUNT(*) FROM student_subjects ss WHERE ss.subject_id = s.id) as student_count
                                            FROM subjects s 
                                            WHERE s.instructor LIKE '%" . $conn->real_escape_string($teacher_name) . "%'");
                                        if ($subjects && $subjects->num_rows > 0):
                                            while ($subject = $subjects->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($subject['description'] ?? ''); ?></td>
                                            <td><?php echo $subject['schedule'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                                            <td><?php echo $subject['room'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                                            <td><span class="badge badge-primary"><?php echo $subject['student_count']; ?></span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="dashboard.php?page=grade_entry&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-edit"></i> Enter Grades
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-book fa-2x mb-2"></i>
                                                <p class="mb-0">No subjects assigned yet</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
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
