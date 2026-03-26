<?php
require_once '../config.php';

$message = '';

// Delete payment
if (isset($_POST['delete_payment'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM payments WHERE id = $id");
    $message = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Payment deleted successfully!</div>';
}

// Get all payments
$payments = $conn->query("
    SELECT p.*, s.student_number, s.firstname, s.lastname 
    FROM payments p 
    LEFT JOIN students s ON p.student_id = s.id 
    ORDER BY p.payment_date DESC
");

// Stats
$total_collected = $conn->query("SELECT COALESCE(SUM(amount), 0) as c FROM payments")->fetch_assoc()['c'];
$today_collected = $conn->query("SELECT COALESCE(SUM(amount), 0) as c FROM payments WHERE DATE(payment_date) = CURDATE()")->fetch_assoc()['c'];
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-money-bill"></i> Payment Records</h1>
        <p>View and manage all payment transactions</p>
    </div>
    <div class="page-header-right">
        <a href="dashboard.php?page=students" class="btn btn-success"><i class="fas fa-plus me-2"></i>Record Payment</a>
    </div>
</div>

<?php echo $message; ?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-value">₱<?php echo number_format($total_collected, 2); ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Collected Today</div>
                    <div class="stat-value">₱<?php echo number_format($today_collected, 2); ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value"><?php echo $payments ? $payments->num_rows : 0; ?></div>
                </div>
                <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="table-header">
        <h5 class="table-title"><i class="fas fa-list"></i> All Payment Records</h5>
        <div class="table-actions">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" data-table="paymentsTable" placeholder="Search...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
            <button class="btn btn-outline-primary btn-sm" onclick="exportTableToCSV('paymentsTable', 'payments')">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="paymentsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Student</th>
                    <th>OR Number</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Received By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while($row = $payments->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar"><?php echo strtoupper(substr($row['firstname'], 0, 1)); ?></div>
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars(($row['firstname'] ?? 'N/A') . ' ' . ($row['lastname'] ?? '')); ?></div>
                                <small class="text-muted"><?php echo $row['student_number'] ?? 'N/A'; ?></small>
                            </div>
                        </div>
                    </td>
                    <td><code><?php echo htmlspecialchars($row['or_number']); ?></code></td>
                    <td><span class="badge badge-secondary"><?php echo htmlspecialchars($row['payment_method']); ?></span></td>
                    <td><?php echo $row['reference_number'] ?: '-'; ?></td>
                    <td class="text-success fw-bold">₱<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['received_by']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="print_receipt.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-icon btn-ghost" title="Print Receipt">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="dashboard.php?page=student_detail&id=<?php echo $row['student_id']; ?>" class="btn btn-icon btn-ghost" title="View Student">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirmDelete(this);">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_payment" class="btn btn-icon btn-ghost text-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="table-info">Showing <?php echo $counter - 1; ?> records</div>
    </div>
</div>

<script>
function confirmDelete(form) {
    if (confirm('Are you sure you want to delete this payment record? This action cannot be undone.')) {
        form.submit();
    }
    return false;
}

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
