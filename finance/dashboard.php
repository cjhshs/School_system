<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'finance') {
    header('Location: login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$user_name = $_SESSION['username'];

// Get stats
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$total_payments = $conn->query("SELECT COALESCE(SUM(amount), 0) as c FROM payments WHERE YEAR(payment_date) = YEAR(CURDATE())")->fetch_assoc()['c'];
$pending_payments = $conn->query("SELECT COUNT(*) as c FROM students WHERE enrollment_status = 'Pending'")->fetch_assoc()['c'];
$recent_payments = $conn->query("SELECT COUNT(*) as c FROM payments WHERE DATE(payment_date) = CURDATE()")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Portal - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <aside class="app-sidebar" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="sidebar-header" style="background: rgba(0,0,0,0.15);">
                <a href="dashboard.php" class="sidebar-brand">
                    <i class="fas fa-coins"></i>
                    <div>
                        <div>Finance Portal</div>
                        <small class="sidebar-subtitle">Payment Management</small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($user_name); ?></h6>
                    <span class="user-role-badge">Finance</span>
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
                        <a href="dashboard.php?page=students" class="nav-link <?php echo $page == 'students' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i><span>Student Accounts</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=payments" class="nav-link <?php echo $page == 'payments' ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill"></i><span>Payments</span>
                            <?php if ($pending_payments > 0): ?>
                                <span class="badge bg-warning text-dark"><?php echo $pending_payments; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="dashboard.php?page=reports" class="nav-link <?php echo $page == 'reports' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i><span>Reports</span>
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
                        <h1 class="page-title">Finance Dashboard</h1>
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
                                <div class="user-role">Finance</div>
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
                    case 'students':
                        include 'pages/students.php';
                        break;
                    case 'payments':
                        include 'pages/payments.php';
                        break;
                    case 'reports':
                        include 'pages/reports.php';
                        break;
                    case 'student_detail':
                        include 'pages/student_detail.php';
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
                                <div class="stat-card success">
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
                                <div class="stat-card primary">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Collections (YTD)</div>
                                            <div class="stat-value">₱<?php echo number_format($total_payments, 0); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card warning">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">With Balance</div>
                                            <div class="stat-value"><?php echo number_format($pending_payments); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="stat-card info">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="stat-label">Today's Payments</div>
                                            <div class="stat-value"><?php echo number_format($recent_payments); ?></div>
                                        </div>
                                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
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
                                        <span>Record Payment</span>
                                    </a>
                                    <a href="dashboard.php?page=payments" class="quick-action-btn">
                                        <i class="fas fa-receipt"></i>
                                        <span>View Payments</span>
                                    </a>
                                    <a href="dashboard.php?page=reports" class="quick-action-btn">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Generate Report</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Payments -->
                        <div class="card">
                            <div class="table-header">
                                <h5 class="table-title"><i class="fas fa-clock"></i> Recent Payments</h5>
                                <a href="dashboard.php?page=payments" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Student</th>
                                            <th>OR Number</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent = $conn->query("SELECT p.*, s.firstname, s.lastname, s.student_number 
                                                                 FROM payments p 
                                                                 LEFT JOIN students s ON p.student_id = s.id 
                                                                 ORDER BY p.payment_date DESC LIMIT 10");
                                        if ($recent && $recent->num_rows > 0):
                                            while ($payment = $recent->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar"><?php echo strtoupper(substr($payment['firstname'], 0, 1)); ?></div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($payment['firstname'] . ' ' . $payment['lastname']); ?></div>
                                                        <small class="text-muted"><?php echo $payment['student_number']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($payment['or_number']); ?></code></td>
                                            <td class="text-success fw-bold">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><span class="status active">Completed</span></td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                                <p class="mb-0">No payments recorded yet</p>
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
