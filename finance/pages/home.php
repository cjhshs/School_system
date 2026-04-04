<?php
require_once '../config.php';

// Get finance stats
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_collected = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE())")->fetch_assoc()['total'];
$total_balance = $conn->query("
    SELECT COALESCE(SUM(sf.amount), 0) - COALESCE((SELECT SUM(p.payment_amount) FROM payments p), 0) as balance 
    FROM student_fees sf
")->fetch_assoc()['balance'];

// Recent payments
$recent_payments = $conn->query("
    SELECT p.*, s.student_number, s.firstname, s.lastname 
    FROM payments p 
    LEFT JOIN students s ON p.student_id = s.id 
    ORDER BY p.payment_date DESC LIMIT 5
");

// Pending balances
$pending_students = $conn->query("
    SELECT s.student_number, s.firstname, s.lastname, 
           COALESCE(SUM(sf.amount), 0) as total_fee,
           COALESCE((SELECT SUM(p.payment_amount) FROM payments p WHERE p.student_id = s.id), 0) as paid,
           (COALESCE(SUM(sf.amount), 0) - COALESCE((SELECT SUM(p.payment_amount) FROM payments p WHERE p.student_id = s.id), 0)) as balance
    FROM students s
    LEFT JOIN student_fees sf ON s.id = sf.student_id
    GROUP BY s.id
    HAVING balance > 0
    ORDER BY balance DESC
    LIMIT 5
");
?>

<div class="row">
    <div class="col-md-12">
        <h3><i class="fas fa-chart-line me-2"></i>Finance Dashboard</h3>
        <p class="text-muted">Overview of financial status</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                <h6 class="text-uppercase">Total Students</h6>
                <h2><?php echo $total_students; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x mb-3 opacity-50"></i>
                <h6 class="text-uppercase">Collected This Month</h6>
                <h2>₱<?php echo number_format($total_collected, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-circle fa-3x mb-3 opacity-50"></i>
                <h6 class="text-uppercase">Total Balance</h6>
                <h2>₱<?php echo number_format($total_balance, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
                <h6 class="text-uppercase">Pending Accounts</h6>
                <h2><?php echo $pending_students->num_rows; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Recent Payments</h5>
            </div>
            <div class="card-body">
                <?php if($recent_payments->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $recent_payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                <td><?php echo $row['student_number'] . '<br><small>' . $row['firstname'] . ' ' . $row['lastname'] . '</small>'; ?></td>
                                <td class="text-success fw-bold">₱<?php echo number_format($row['payment_amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No recent payments</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Pending Balances</h5>
            </div>
            <div class="card-body">
                <?php if($pending_students->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $pending_students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['student_number'] . '<br><small>' . $row['firstname'] . ' ' . $row['lastname'] . '</small>'; ?></td>
                                <td class="text-danger fw-bold">₱<?php echo number_format($row['balance'], 2); ?></td>
                                <td>
                                    <a href="dashboard.php?page=student_detail&id=<?php echo $row['student_number']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-success">All accounts are paid!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
