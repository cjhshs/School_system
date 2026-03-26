<?php
require_once dirname(dirname(__DIR__)) . '/config.php';

$page_title = "Finance Reports";

$total_students_query = "SELECT COUNT(*) as total FROM students WHERE enrollment_status = 'Confirmed'";
$total_students_result = mysqli_query($conn, $total_students_query);
$total_students = mysqli_fetch_assoc($total_students_result)['total'];

$total_fees_query = "SELECT SUM(amount) as total FROM student_fees";
$total_fees_result = mysqli_query($conn, $total_fees_query);
$total_fees = mysqli_fetch_assoc($total_fees_result)['total'] ?? 0;

$total_payments_query = "SELECT SUM(amount) as total FROM payments";
$total_payments_result = mysqli_query($conn, $total_payments_query);
$total_payments = mysqli_fetch_assoc($total_payments_result)['total'] ?? 0;

$pending_payments_query = "SELECT COUNT(*) as total FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$pending_payments_result = mysqli_query($conn, $pending_payments_query);
$pending_payments = mysqli_fetch_assoc($pending_payments_result)['total'];

$outstanding_balance = $total_fees - $total_payments;

$fee_types_summary = [];
$fee_types_query = "SELECT ft.name, COUNT(sf.id) as student_count, SUM(sf.amount) as total_amount 
                    FROM fee_types ft 
                    LEFT JOIN student_fees sf ON ft.id = sf.fee_type_id 
                    GROUP BY ft.id, ft.name 
                    ORDER BY ft.name";
$fee_types_result = mysqli_query($conn, $fee_types_query);
while ($row = mysqli_fetch_assoc($fee_types_result)) {
    $fee_types_summary[] = $row;
}

$monthly_payments = [];
$monthly_query = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total 
                  FROM payments 
                  GROUP BY month ORDER BY month DESC LIMIT 12";
$monthly_result = mysqli_query($conn, $monthly_query);
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_payments[] = $row;
}

$recent_payments = [];
$recent_query = "SELECT p.*, s.firstname, s.middlename, s.lastname, s.student_number 
                 FROM payments p 
                 JOIN students s ON p.student_id = s.id 
                 ORDER BY p.payment_date DESC LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_payments[] = $row;
}

$students_with_balance = [];
$balance_query = "SELECT s.id, s.student_number, s.lastname, s.firstname, s.middlename,
                   SUM(sf.amount) as total_fees,
COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0) as total_paid,
                    (SUM(sf.amount) - COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0)) as balance
                   FROM students s
                   LEFT JOIN student_fees sf ON s.id = sf.student_id
                   WHERE s.enrollment_status = 'Confirmed'
                   GROUP BY s.id
                   HAVING balance > 0
                   ORDER BY balance DESC
                   LIMIT 10";
$balance_result = mysqli_query($conn, $balance_query);
while ($row = mysqli_fetch_assoc($balance_result)) {
    $students_with_balance[] = $row;
}

if (isset($_GET['export']) && $_GET['export'] == 'summary') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="finance_summary_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Finance Summary Report - ' . date('Y-m-d')]);
    fputcsv($output, []);
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Enrolled Students', $total_students]);
    fputcsv($output, ['Total Fees Billed', number_format($total_fees, 2)]);
    fputcsv($output, ['Total Payments Received', number_format($total_payments, 2)]);
    fputcsv($output, ['Outstanding Balance', number_format($outstanding_balance, 2)]);
    fputcsv($output, ['Pending Payments', $pending_payments]);
    fclose($output);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] == 'students_balance') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_with_balance_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student Number', 'Last Name', 'First Name', 'Middle Name', 'Total Fees', 'Total Paid', 'Balance']);
    
    $export_query = "SELECT s.student_number, s.lastname, s.firstname, s.middlename,
                     SUM(sf.amount) as total_fees,
                     COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0) as total_paid,
                     (SUM(sf.amount) - COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0)) as balance
                     FROM students s
                     LEFT JOIN student_fees sf ON s.id = sf.student_id
                     WHERE s.enrollment_status = 'Confirmed'
                     GROUP BY s.id
                     HAVING balance > 0
                     ORDER BY balance DESC";
    $export_result = mysqli_query($conn, $export_query);
    while ($row = mysqli_fetch_assoc($export_result)) {
        fputcsv($output, [
            $row['student_number'],
            $row['lastname'],
            $row['firstname'],
            $row['middle_name'] ?? '',
            number_format($row['total_fees'] ?? 0, 2),
            number_format($row['total_paid'], 2),
            number_format($row['balance'], 2)
        ]);
    }
    fclose($output);
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-bar me-2"></i>Finance Reports</h2>
            <div>
                <a href="?page=reports&export=summary" class="btn btn-success btn-sm">
                    <i class="fas fa-download me-1"></i> Export Summary
                </a>
                <a href="?page=reports&export=students_balance" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-csv me-1"></i> Export Students with Balance
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users me-2"></i>Total Students</h5>
                <h2><?php echo number_format($total_students); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-file-invoice-dollar me-2"></i>Total Fees Billed</h5>
                <h2>₱<?php echo number_format($total_fees, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-money-bill-wave me-2"></i>Total Collected</h5>
                <h2>₱<?php echo number_format($total_payments, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-exclamation-circle me-2"></i>Outstanding Balance</h5>
                <h2>₱<?php echo number_format($outstanding_balance, 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Fee Types Summary</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" data-table="feeTypesTable" placeholder="Search...">
                    <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                </div>
                <table class="table table-sm table-striped" id="feeTypesTable">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th class="text-center">Students</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fee_types_summary as $fee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fee['name']); ?></td>
                                <td class="text-center"><?php echo number_format($fee['student_count']); ?></td>
                                <td class="text-end">₱<?php echo number_format($fee['total_amount'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Students with Outstanding Balance</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" data-table="balanceTable" placeholder="Search...">
                    <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                </div>
                <table class="table table-sm table-striped" id="balanceTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students_with_balance)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">No students with outstanding balance</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students_with_balance as $student): ?>
                                <tr>
                                    <td>
                                        <a href="dashboard.php?page=student_detail&id=<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?>
                                        </a>
                                    </td>
                                    <td class="text-end text-danger">₱<?php echo number_format($student['balance'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payments</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper mb-3">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" data-table="recentPaymentsTable" placeholder="Search...">
                    <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                </div>
                <table class="table table-striped datatable" id="recentPaymentsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($recent_payments as $payment): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($payment['lastname'] . ', ' . $payment['firstname']); ?>
                                    <small class="text-muted">(<?php echo $payment['student_number']; ?>)</small>
                                </td>
                                <td><?php echo htmlspecialchars($payment['reference_number']); ?></td>
                                <td class="text-success fw-bold">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('keyup', function() {
        const tableId = this.getAttribute('data-table');
        const filter = this.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});

function clearSearch(btn) {
    const wrapper = btn.parentElement;
    const input = wrapper.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('keyup'));
}
</script>
