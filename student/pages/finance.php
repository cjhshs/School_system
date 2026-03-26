<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

// Sample billing data (in production, this would come from a billing table)
$total_tuition = 15000;
$total_misc = 5000;
$total_lab = 3000;
$total_other = 2000;
$grand_total = $total_tuition + $total_misc + $total_lab;
$paid_amount = 10000;
$balance = $grand_total - $paid_amount + $total_other;
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
                <h4>₱<?php echo number_format($grand_total, 2); ?></h4>
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
                <i class="fas fa-<?php echo $balance > 0 ? 'exclamation-triangle' : '-check-double'; ?> fa-2x mb-2"></i>
                <h6 class="text-uppercase">Status</h6>
                <h4><?php echo $balance > 0 ? 'Unpaid' : 'Fully Paid'; ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Fee Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>Tuition Fee</td>
                        <td class="text-end">₱<?php echo number_format($total_tuition, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Miscellaneous Fee</td>
                        <td class="text-end">₱<?php echo number_format($total_misc, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Laboratory Fee</td>
                        <td class="text-end">₱<?php echo number_format($total_lab, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Other Fees</td>
                        <td class="text-end">₱<?php echo number_format($total_other, 2); ?></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Total</strong></td>
                        <td class="text-end"><strong>₱<?php echo number_format($grand_total, 2); ?></strong></td>
                    </tr>
                </table>
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
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2026-03-01</td>
                            <td>Initial Payment</td>
                            <td class="text-success">₱10,000.00</td>
                        </tr>
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
            </div>
        </div>
    </div>
</div>
