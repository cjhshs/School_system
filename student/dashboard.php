<?php
require_once '../config.php';
require_once '../includes/rbac.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get student info
$student = $conn->query("SELECT s.*, c.code as course_code, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();
$student_name = $_SESSION['student_name'];
$current_school_year = date('Y') . '-' . (date('Y') + 1);

// Get stats
$subjects = $conn->query("SELECT * FROM subjects WHERE course_code = '" . $student['course_code'] . "' AND year_level = " . $student['year_level']);
$total_units = 0;
$subjects_array = [];
if ($subjects) {
    while ($row = $subjects->fetch_assoc()) {
        $subjects_array[] = $row;
        $total_units += $row['units'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <aside class="app-sidebar" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
            <div class="sidebar-header" style="background: rgba(0,0,0,0.2);">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-graduation-cap"></i>
                    <div>
                        <div>Student Portal</div>
                        <small class="sidebar-subtitle"><?php echo htmlspecialchars($student['student_number'] ?? ''); ?></small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar" style="background: var(--primary-600);">
                    <?php if(isset($student['profile_picture']) && $student['profile_picture']): ?>
                        <img src="../uploads/<?php echo $student['profile_picture']; ?>" alt="">
                    <?php else: ?>
                        <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($student_name); ?></h6>
                    <span class="user-role-badge">Student</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Navigation</div>
                    <div class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i><span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=profile" class="nav-link <?php echo $page == 'profile' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i><span>My Profile</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=schedule" class="nav-link <?php echo $page == 'schedule' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar"></i><span>My Schedule</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=grades" class="nav-link <?php echo $page == 'grades' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i><span>My Grades</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=finance" class="nav-link <?php echo $page == 'finance' ? 'active' : ''; ?>">
                            <i class="fas fa-coins"></i><span>Finance</span>
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
                        <h1 class="page-title">Student Dashboard</h1>
                        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="header-action" title="Notifications"><i class="fas fa-bell"></i></button>
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="avatar">
                                <?php if(isset($student['profile_picture']) && $student['profile_picture']): ?>
                                    <img src="../uploads/<?php echo $student['profile_picture']; ?>" alt="">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($student_name); ?></div>
                                <div class="user-role"><?php echo htmlspecialchars($student['course_code'] ?? 'Student'); ?></div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="dashboard.php?page=profile"><i class="fas fa-user"></i> My Profile</a>
                            <div class="divider"></div>
                            <a href="logout.php"><i class="fas fa-sign-out-alt text-danger"></i> Sign Out</a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="app-content">
                <?php
                switch($page) {
                    case 'profile':
                        include 'pages/profile.php';
                        break;
                    case 'schedule':
                        include 'pages/schedule.php';
                        break;
                    case 'grades':
                        include 'pages/grades.php';
                        break;
                    case 'finance':
                        include 'pages/finance.php';
                        break;
                    default:
                        ?>
                        <div class="page-header">
                            <div class="page-header-left">
                                <h1><i class="fas fa-home"></i> Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
                                <p><?php echo htmlspecialchars($student['course_code'] ?? ''); ?> - Year <?php echo htmlspecialchars($student['year_level'] ?? ''); ?></p>
                            </div>
                        </div>

                        <!-- Student Info Card -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar" style="width: 60px; height: 60px; font-size: 1.5rem; background: var(--primary-600);">
                                                <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h4 class="mb-0"><?php echo htmlspecialchars($student_name); ?></h4>
                                                <small class="text-muted"><?php echo htmlspecialchars($student['student_number'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="stat-label">Course</div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-label">Year Level</div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-label">Status</div>
                                                <div class="fw-semibold"><span class="status active"><?php echo htmlspecialchars($student['status'] ?? 'Active'); ?></span></div>
                                            </div>
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
                                    <a href="dashboard.php?page=schedule" class="quick-action-btn">
                                        <i class="fas fa-calendar"></i>
                                        <span>View Schedule</span>
                                    </a>
                                    <a href="dashboard.php?page=grades" class="quick-action-btn">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Check Grades</span>
                                    </a>
                                    <a href="dashboard.php?page=finance" class="quick-action-btn">
                                        <i class="fas fa-coins"></i>
                                        <span>View Balance</span>
                                    </a>
                                    <a href="export_schedule.php" target="_blank" class="quick-action-btn">
                                        <i class="fas fa-download"></i>
                                        <span>Download Schedule</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- My Schedule Preview -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="table-header">
                                        <h5 class="table-title"><i class="fas fa-book"></i> My Subjects</h5>
                                        <a href="dashboard.php?page=schedule" class="btn btn-sm btn-outline-primary">View Full Schedule</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Units</th>
                                                    <th>Schedule</th>
                                                    <th>Room</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($subjects_array) > 0): foreach(array_slice($subjects_array, 0, 5) as $subj): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($subj['subject_code']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($subj['description'] ?? ''); ?></small>
                                                    </td>
                                                    <td><span class="badge badge-info"><?php echo $subj['units']; ?></span></td>
                                                    <td><?php echo $subj['schedule'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                                                    <td><?php echo $subj['room'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                                                </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="fas fa-book fa-2x mb-2"></i>
                                                        <p class="mb-0">No subjects enrolled yet</p>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Finance Summary -->
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-coins"></i> Account Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $balance = $student['balance'] ?? 0;
                                        $total_fees = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as c FROM payments WHERE student_id = $student_id")->fetch_assoc()['c'];
                                        ?>
                                        <div class="balance-card <?php echo $balance > 0 ? 'unpaid' : 'paid'; ?> mb-3">
                                            <div class="balance-label">Outstanding Balance</div>
                                            <div class="balance-amount">₱<?php echo number_format($balance, 2); ?></div>
                                        </div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted">Total Paid</span>
                                            <span class="fw-semibold text-success">₱<?php echo number_format($total_fees, 2); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Total Units</span>
                                            <span class="fw-semibold"><?php echo $total_units; ?></span>
                                        </div>
                                        <a href="dashboard.php?page=finance" class="btn btn-outline-primary w-100 mt-3">
                                            <i class="fas fa-receipt me-2"></i>View Payment History
                                        </a>
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
