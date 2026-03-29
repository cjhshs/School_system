<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'cashier') {
    header('Location: login.php');
    exit;
}

$base_url = '';
$portal_title = 'Cashier Dashboard';
$portal_icon = 'fa-cash-register';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$nav_items = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
    ['key' => 'payments', 'label' => 'Process Payment', 'url' => 'dashboard.php?page=payments', 'icon' => 'fas fa-money-bill-wave'],
    ['key' => 'receipts', 'label' => 'Receipts', 'url' => 'dashboard.php?page=receipts', 'icon' => 'fas fa-receipt'],
    ['key' => 'students', 'label' => 'Search Student', 'url' => 'dashboard.php?page=students', 'icon' => 'fas fa-search'],
];

$user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$user_role = 'Cashier';

include '../includes/portal_layout_start.php';

// Get statistics
$today = date('Y-m-d');
$today_payments = $conn->query("SELECT COUNT(*) as cnt, COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE payment_date = '$today'")->fetch_assoc();
$total_payments = $conn->query("SELECT COUNT(*) as cnt, COALESCE(SUM(payment_amount), 0) as total FROM payments")->fetch_assoc();
$pending_balance = $conn->query("SELECT COALESCE(SUM(balance), 0) as total FROM (
    SELECT (tf.tuition_amount + tf.miscellaneous_amount + COALESCE((SELECT SUM(amount) FROM course_fees WHERE course_code = tf.course_code), 0) - COALESCE((SELECT SUM(payment_amount) FROM payments p JOIN students s ON p.student_id = s.id WHERE s.course_id = (SELECT id FROM courses WHERE code = tf.course_code LIMIT 1)), 0)) as balance FROM tuition_fees tf
) sub WHERE balance > 0")->fetch_assoc();

switch($current_page) {
    case 'payments':
        $portal_title = 'Process Payment';
        include 'pages/payments.php';
        break;
    case 'receipts':
        $portal_title = 'Receipts';
        include 'pages/receipts.php';
        break;
    case 'students':
        $portal_title = 'Search Student';
        include 'pages/students.php';
        break;
    default:
        // Dashboard content
        ?>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₱<?php echo number_format($today_payments['total'], 2); ?></h3>
                        <p>Today's Collection</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $today_payments['cnt']; ?></h3>
                        <p>Transactions Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₱<?php echo number_format($total_payments['total'], 2); ?></h3>
                        <p>Total Collected</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-history me-2"></i>Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>OR Number</th>
                                        <th>Student</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent = $conn->query("SELECT p.*, s.firstname, s.lastname, s.student_number 
                                        FROM payments p 
                                        JOIN students s ON p.student_id = s.id 
                                        ORDER BY p.created_at DESC LIMIT 10");
                                    if ($recent && $recent->num_rows > 0):
                                        while ($r = $recent->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><code><?php echo $r['or_number']; ?></code></td>
                                        <td><?php echo htmlspecialchars($r['student_number']); ?><br><small><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?></small></td>
                                        <td class="text-success fw-bold">₱<?php echo number_format($r['payment_amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($r['payment_date'])); ?></td>
                                        <td><a href="?page=receipt&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No transactions yet</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="?page=payments" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-money-bill-wave me-2"></i>New Payment
                        </a>
                        <a href="?page=students" class="btn btn-outline-primary w-100 mb-3">
                            <i class="fas fa-search me-2"></i>Find Student
                        </a>
                        <a href="?page=receipts" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-receipt me-2"></i>View All Receipts
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;
}

include '../includes/portal_layout_end.php';
