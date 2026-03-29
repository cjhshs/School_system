<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.code as course_code FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();

$course_code = $student['course_code'] ?? '';
$year_level = $student['year_level'] ?? 1;

$tuition = $conn->query("SELECT * FROM tuition_fees WHERE course_code = '$course_code' AND year_level = $year_level LIMIT 1")->fetch_assoc();

$course_fees = $conn->query("SELECT * FROM course_fees WHERE course_code = '$course_code'");

$payments = $conn->query("
    SELECT * FROM payments 
    WHERE student_id = $student_id 
    ORDER BY payment_date DESC
");

$fees_data = [];
$total_amount = 0;

if ($tuition) {
    $fees_data[] = [
        'fee_name' => 'Tuition Fee',
        'amount' => $tuition['tuition_amount'] ?? 0
    ];
    $total_amount += $tuition['tuition_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Miscellaneous Fee',
        'amount' => $tuition['miscellaneous_amount'] ?? 0
    ];
    $total_amount += $tuition['miscellaneous_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Laboratory Fee',
        'amount' => $tuition['laboratory_amount'] ?? 0
    ];
    $total_amount += $tuition['laboratory_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Other Fees',
        'amount' => $tuition['other_fees'] ?? 0
    ];
    $total_amount += $tuition['other_fees'] ?? 0;
}

if ($course_fees) {
    while ($cf = $course_fees->fetch_assoc()) {
        $fees_data[] = [
            'fee_name' => $cf['fee_name'],
            'amount' => $cf['amount']
        ];
        $total_amount += $cf['amount'];
    }
}

$paid_amount = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE student_id = $student_id")->fetch_assoc()['total'];
$balance = $total_amount - $paid_amount;
?>
<div class="row">
    <div class="col-md-12">
        <h3><i class="fas fa-coins me-2"></i>Finance & Billing</h3>
        <p class="text-muted">View your tuition fees and payment history</p>
    </div>
</div>

<!-- Payment Summary Cards -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                <h6 class="text-uppercase">Total Fees</h6>
                <h4>₱<?php echo number_format($total_amount, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h6 class="text-uppercase">Paid</h6>
                <h4>₱<?php echo number_format($paid_amount, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h6 class="text-uppercase">Balance</h6>
                <h4>₱<?php echo number_format($balance, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-<?php echo $balance > 0 ? 'danger' : 'success'; ?> text-white">
            <div class="card-body text-center">
                <i class="fas fa-<?php echo $balance > 0 ? 'exclamation-triangle' : 'check-double'; ?> fa-2x mb-2"></i>
                <h6 class="text-uppercase">Status</h6>
                <h4><?php echo $balance > 0 ? 'Unpaid' : 'Fully Paid'; ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Fee Breakdown</h5>
                <a href="/enrollment_system/student/print_statement.php" target="_blank" class="btn btn-sm btn-light">
                    <i class="fas fa-download me-1"></i>Download PDF
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($fees_data)): ?>
                <table class="table table-borderless">
                    <?php foreach ($fees_data as $fee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fee['fee_name'] ?? 'Fee'); ?></td>
                        <td class="text-end">₱<?php echo number_format($fee['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="border-top">
                        <td><strong>Total</strong></td>
                        <td class="text-end"><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
                    </tr>
                </table>
                <?php else: ?>
                <p class="text-muted text-center">No fees assessed yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" data-table="paymentsTable" placeholder="Search payments...">
                    <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                </div>
                <table class="table table-sm" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>OR Number</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payments && $payments->num_rows > 0): ?>
                        <?php while ($p = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($p['or_number'] ?? '-'); ?></td>
                            <td class="text-success">₱<?php echo number_format($p['payment_amount'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No payments recorded</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>For payment inquiries, please visit the registrar's office or contact us.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-university me-2"></i>Payment Methods</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-secondary mb-0">
                            <h6><i class="fas fa-bank me-2"></i>Bank Transfer</h6>
                            <p class="mb-0 small">Bank of the Philippine Islands<br>Account #: 1234-5678-90</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-secondary mb-0">
                            <h6><i class="fas fa-mobile-alt me-2"></i>GCash</h6>
                            <p class="mb-0 small">Mobile #: 0912-345-6789<br>Name: CJLG University</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-secondary mb-0">
                            <h6><i class="fas fa-building me-2"></i>Over the Counter</h6>
                            <p class="mb-0 small">Visit the registrar's office<br>Monday - Friday, 8AM - 5PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var tableId = this.getAttribute('data-table');
        var table = document.getElementById(tableId);
        if (!table) return;
        var filter = this.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
        });
    });
});

function clearSearch(btn) {
    var input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
