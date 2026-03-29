<?php
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$where = "WHERE p.payment_date BETWEEN '$date_from' AND '$date_to'";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (p.or_number LIKE '%$search_esc%' OR s.student_number LIKE '%$search_esc%' OR s.firstname LIKE '%$search_esc%' OR s.lastname LIKE '%$search_esc%')";
}

$receipts = $conn->query("SELECT p.*, s.firstname, s.lastname, s.student_number, c.code as course_code 
    FROM payments p 
    JOIN students s ON p.student_id = s.id 
    LEFT JOIN courses c ON s.course_id = c.id 
    $where
    ORDER BY p.payment_date DESC, p.id DESC");

$total_amount = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total 
    FROM payments p 
    JOIN students s ON p.student_id = s.id 
    $where")->fetch_assoc()['total'];
?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-receipt"></i> Payment Receipts</h1>
        <p>View and print payment receipts</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="receipts">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="OR #, Student #, Name" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Transactions (<?php echo $receipts ? $receipts->num_rows : 0; ?>)</h5>
        <span class="badge bg-success fs-6">Total: ₱<?php echo number_format($total_amount, 2); ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>OR Number</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($receipts && $receipts->num_rows > 0): ?>
                        <?php while ($r = $receipts->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo $r['or_number']; ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($r['student_number']); ?></strong><br>
                                <small><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($r['course_code'] ?? 'N/A'); ?></td>
                            <td class="text-success fw-bold">₱<?php echo number_format($r['payment_amount'], 2); ?></td>
                            <td><?php echo $r['payment_method']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($r['payment_date'])); ?></td>
                            <td>
                                <a href="?page=receipt&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No receipts found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
