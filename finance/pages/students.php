<?php
require_once '../config.php';

$message = '';

// Process payment
if (isset($_POST['add_payment'])) {
    $student_number = $conn->real_escape_string($_POST['student_number']);
    $amount = floatval($_POST['amount']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $reference = $conn->real_escape_string($_POST['reference_number'] ?? '');
    $or_number = $conn->real_escape_string($_POST['or_number']);
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? '');
    
    // Get student ID
    $student = $conn->query("SELECT id FROM students WHERE student_number = '$student_number'")->fetch_assoc();
    
    if ($student) {
        $stmt = $conn->prepare("INSERT INTO payments (student_id, amount, payment_method, reference_number, or_number, payment_date, received_by, remarks) VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?)");
        $user = $conn->query("SELECT first_name, last_name FROM system_users WHERE id = " . intval($_SESSION['user_id']))->fetch_assoc();
        $received_by = $user['first_name'] . ' ' . $user['last_name'];
        $stmt->bind_param("idsssss", $student['id'], $amount, $payment_method, $reference, $or_number, $received_by, $remarks);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Payment recorded successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error recording payment.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Student not found.</div>';
    }
}

// Get all students with balances
$students = $conn->query("
    SELECT s.student_number, s.firstname, s.lastname, s.email, s.course_code,
           COALESCE(SUM(sf.amount), 0) as total_fee,
           COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0) as paid,
           (COALESCE(SUM(sf.amount), 0) - COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.student_id = s.id), 0)) as balance
    FROM students s
    LEFT JOIN student_fees sf ON s.id = sf.student_id
    GROUP BY s.id
    ORDER BY balance DESC
");
?>

<div class="row">
    <div class="col-md-12">
        <h3><i class="fas fa-users me-2"></i>Student Accounts</h3>
        <p class="text-muted">Manage student finances and payments</p>
    </div>
</div>

<?php echo $message; ?>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-plus me-2"></i>Record Payment</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Student Number *</label>
                        <input type="text" name="student_number" class="form-control" required placeholder="e.g., 2020-1001">
                    </div>
                    <div class="mb-3">
                        <label>Amount *</label>
                        <input type="number" name="amount" class="form-control" required step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label>Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="GCash">GCash</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>OR Number *</label>
                        <input type="text" name="or_number" class="form-control" required placeholder="e.g., OR-2026-001">
                    </div>
                    <div class="mb-3">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Bank/GCash reference">
                    </div>
                    <div class="mb-3">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" name="add_payment" class="btn btn-success w-100">
                        <i class="fas fa-save me-2"></i>Record Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Student Accounts</h5>
                <a href="?page=reports" class="btn btn-sm btn-light"><i class="fas fa-chart-bar"></i> View Reports</a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" data-table="studentsTable" placeholder="Search...">
                        <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="studentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Total Fee</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            while($row = $students->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo $row['student_number']; ?></td>
                                <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
                                <td><?php echo $row['course_code'] ?? '-'; ?></td>
                                <td>₱<?php echo number_format($row['total_fee'], 2); ?></td>
                                <td class="text-success">₱<?php echo number_format($row['paid'], 2); ?></td>
                                <td class="<?php echo $row['balance'] > 0 ? 'text-danger fw-bold' : 'text-success'; ?>">
                                    ₱<?php echo number_format($row['balance'], 2); ?>
                                </td>
                                <td>
                                    <a href="dashboard.php?page=student_detail&id=<?php echo $row['student_number']; ?>" class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
