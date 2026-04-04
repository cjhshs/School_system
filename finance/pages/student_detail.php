<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?page=students');
    exit;
}

$student_id = intval($_GET['id']);
$student = $conn->query("SELECT s.*, c.code as course_code, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    return;
}

// Get fees
$fees = $conn->query("
    SELECT sf.*, ft.name as fee_name 
    FROM student_fees sf 
    LEFT JOIN fee_types ft ON sf.fee_type_id = ft.id 
    WHERE sf.student_id = " . $student['id']
);

// Get payments
$payments = $conn->query("
    SELECT * FROM payments 
    WHERE student_id = " . $student['id'] . " 
    ORDER BY payment_date DESC"
);

// Calculate totals
$total_fee = $fees->num_rows > 0 ? $fees->fetch_all(MYSQLI_ASSOC) : [];
$total_amount = 0;
$fees->data_seek(0);
while($f = $fees->fetch_assoc()) {
    $total_amount += $f['amount'];
}

$total_paid = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE student_id = " . $student['id'])->fetch_assoc()['total'];
$balance = $total_amount - $total_paid;
?>

<div class="row">
    <div class="col-md-12">
        <a href="dashboard.php?page=students" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Students
        </a>
        <a href="print_receipt.php?id=<?php echo $student['id']; ?>" target="_blank" class="btn btn-success mb-3">
            <i class="fas fa-print me-2"></i>Print Statement of Account
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-user me-2"></i>Student Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_number']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course_code'] ?? '-'); ?></p>
                <p><strong>Year Level:</strong> <?php echo htmlspecialchars($student['year_level']); ?></p>
                <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-<?php echo $balance > 0 ? 'warning' : 'success'; ?> text-<?php echo $balance > 0 ? 'dark' : 'white'; ?>">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>Total Fees:</td>
                        <td class="text-end">₱<?php echo number_format($total_amount, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td class="text-success text-end">₱<?php echo number_format($total_paid, 2); ?></td>
                    </tr>
                    <tr class="fw-bold fs-5">
                        <td>Balance:</td>
                        <td class="text-end <?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>">
                            ₱<?php echo number_format($balance, 2); ?>
                        </td>
                    </tr>
                </table>
                
                <?php if ($balance == 0): ?>
                    <div class="alert alert-success mb-0 text-center">
                        <i class="fas fa-check-circle"></i> FULLY PAID
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Fees Assessment</h5>
            </div>
            <div class="card-body">
                <?php if($fees->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($f = $fees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($f['fee_name']); ?></td>
                                <td class="text-end">₱<?php echo number_format($f['amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total:</td>
                                <td class="text-end">₱<?php echo number_format($total_amount, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No fees assigned</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment History</h5>
            </div>
            <div class="card-body">
                <?php if($payments->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>OR #</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($p['or_number']); ?></td>
                                <td class="text-success fw-bold">₱<?php echo number_format($p['payment_amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No payments recorded</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
