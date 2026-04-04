<?php
require_once '../config.php';

$message = '';
$message_type = '';

// Handle permit issuance
if (isset($_POST['issue_permit'])) {
    $student_id = intval($_POST['student_id']);
    $period_key = $_POST['period_key'];
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];
    
    // Get student tuition
    $tuition_row = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM student_fees WHERE student_id = $student_id")->fetch_assoc();
    $total_tuition = floatval($tuition_row['total']);
    $per_period = $total_tuition > 0 ? round($total_tuition / 6, 2) : 0;
    
    // Get total paid
    $paid_row = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE student_id = $student_id")->fetch_assoc();
    $total_paid = floatval($paid_row['total']);
    
    // Calculate cumulative amount due for this period
    $periods = ['1_prelim' => 1, '1_midterm' => 2, '1_final' => 3, '2_prelim' => 4, '2_midterm' => 5, '2_final' => 6];
    $period_multiplier = $periods[$period_key];
    $amount_due = $per_period * $period_multiplier;
    $status = ($total_paid >= $amount_due) ? 'Valid' : 'Not Valid';
    
    // Check if permit already issued
    $check = $conn->prepare("SELECT id FROM permits WHERE student_id = ? AND term = ? AND period = ? AND school_year = ? AND semester = ?");
    $parts = explode('_', $period_key);
    $term = $parts[0] == '1' ? '1st Semester' : '2nd Semester';
    $period = ucfirst($parts[1]);
    $check->bind_param("issss", $student_id, $term, $period, $school_year, $semester);
    $check->execute();
    $existing = $check->get_result();
    
    $issued_by = intval($_SESSION['user_id']);
    
    if ($existing->num_rows > 0) {
        // Update existing permit
        $row = $existing->fetch_assoc();
        $stmt = $conn->prepare("UPDATE permits SET total_tuition = ?, amount_due = ?, total_paid = ?, status = ?, issued_by = ?, issued_at = NOW() WHERE id = ?");
        $stmt->bind_param("dddssi", $total_tuition, $amount_due, $total_paid, $status, $issued_by, $row['id']);
        $stmt->execute();
    } else {
        // Insert new permit
        $stmt = $conn->prepare("INSERT INTO permits (student_id, term, period, school_year, semester, total_tuition, amount_due, total_paid, status, issued_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssdddsi", $student_id, $term, $period, $school_year, $semester, $total_tuition, $amount_due, $total_paid, $status, $issued_by);
        $stmt->execute();
    }
    
    $message = "Permit issued for " . htmlspecialchars($_POST['student_name']) . " - $term $period: <strong>$status</strong>";
    $message_type = $status == 'Valid' ? 'success' : 'warning';
}

// Get current school year
$current_sy = $conn->query("SELECT year FROM school_years WHERE is_current = 1")->fetch_assoc();
$school_year = $current_sy ? $current_sy['year'] : date('Y') . '-' . (date('Y') + 1);

$periods = [
    '1_prelim' => ['label' => '1st Sem - Prelim', 'term' => '1st Semester', 'period' => 'Prelim'],
    '1_midterm' => ['label' => '1st Sem - Midterm', 'term' => '1st Semester', 'period' => 'Midterm'],
    '1_final' => ['label' => '1st Sem - Final', 'term' => '1st Semester', 'period' => 'Final'],
    '2_prelim' => ['label' => '2nd Sem - Prelim', 'term' => '2nd Semester', 'period' => 'Prelim'],
    '2_midterm' => ['label' => '2nd Sem - Midterm', 'term' => '2nd Semester', 'period' => 'Midterm'],
    '2_final' => ['label' => '2nd Sem - Final', 'term' => '2nd Semester', 'period' => 'Final'],
];

// Get all enrolled students with their tuition and payments
$students = $conn->query("
    SELECT s.id, s.student_number, s.firstname, s.lastname, s.year_level,
           c.code as course_code,
           COALESCE((SELECT SUM(sf.amount) FROM student_fees sf WHERE sf.student_id = s.id), 0) as total_tuition,
           COALESCE((SELECT SUM(p.payment_amount) FROM payments p WHERE p.student_id = s.id), 0) as total_paid
    FROM students s
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE EXISTS (SELECT 1 FROM enrollments e WHERE e.student_id = s.id AND e.status = 'Confirmed')
    ORDER BY s.lastname, s.firstname
");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-id-card me-2"></i>Student Permits</h1>
        <p>Assess and issue enrollment permits based on tuition payments</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Schedule</h5>
    </div>
    <div class="card-body">
        <p class="mb-2">Tuition is divided into <strong>6 equal periods</strong>. Cumulative payment requirements:</p>
        <div class="row text-center">
            <div class="col"><span class="badge bg-primary">1st Sem Prelim</span><br><small>1/6 of tuition</small></div>
            <div class="col"><span class="badge bg-primary">1st Sem Midterm</span><br><small>2/6 of tuition</small></div>
            <div class="col"><span class="badge bg-primary">1st Sem Final</span><br><small>3/6 of tuition</small></div>
            <div class="col"><span class="badge bg-success">2nd Sem Prelim</span><br><small>4/6 of tuition</small></div>
            <div class="col"><span class="badge bg-success">2nd Sem Midterm</span><br><small>5/6 of tuition</small></div>
            <div class="col"><span class="badge bg-success">2nd Sem Final</span><br><small>6/6 (full)</small></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Enrolled Students - Permit Assessment</h5>
    </div>
    <div class="card-body">
        <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="permitsTable" placeholder="Search students..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
        <div class="table-responsive">
            <table class="table table-striped" id="permitsTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Tuition</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <?php foreach ($periods as $key => $p): ?>
                            <th class="text-center" style="font-size:0.75rem;"><?php echo $p['label']; ?><br><small>(<?php echo number_format(($key == '1_prelim' ? 1 : ($key == '1_midterm' ? 2 : ($key == '1_final' ? 3 : ($key == '2_prelim' ? 4 : ($key == '2_midterm' ? 5 : 6))))) / 6 * 100); ?>%)</small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students && $students->num_rows > 0): ?>
                        <?php while ($stu = $students->fetch_assoc()): ?>
                            <?php
                            $total_tuition = floatval($stu['total_tuition']);
                            $total_paid = floatval($stu['total_paid']);
                            $per_period = $total_tuition > 0 ? round($total_tuition / 6, 2) : 0;
                            $balance = $total_tuition - $total_paid;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($stu['lastname'] . ', ' . $stu['firstname']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($stu['student_number']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($stu['course_code'] ?? '-'); ?></td>
                                <td><?php echo number_format($total_tuition, 2); ?></td>
                                <td class="text-success fw-bold"><?php echo number_format($total_paid, 2); ?></td>
                                <td class="<?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>"><?php echo number_format($balance, 2); ?></td>
                                <?php
                                $multipliers = [1, 2, 3, 4, 5, 6];
                                $i = 0;
                                foreach ($periods as $key => $p):
                                    $cumulative_due = $per_period * $multipliers[$i];
                                    $is_valid = $total_paid >= $cumulative_due && $total_tuition > 0;
                                    $has_no_tuition = $total_tuition <= 0;
                                ?>
                                    <td class="text-center">
                                        <?php if ($has_no_tuition): ?>
                                            <span class="badge bg-secondary">No Fee</span>
                                        <?php elseif ($is_valid): ?>
                                            <span class="badge bg-success" title="Paid: <?php echo number_format($total_paid); ?> >= Due: <?php echo number_format($cumulative_due); ?>">Valid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" title="Need: <?php echo number_format($cumulative_due - $total_paid); ?> more">Short</span>
                                        <?php endif; ?>
                                        <br>
                                        <form method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
                                            <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
                                            <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($stu['firstname'] . ' ' . $stu['lastname']); ?>">
                                            <input type="hidden" name="period_key" value="<?php echo $key; ?>">
                                            <input type="hidden" name="school_year" value="<?php echo $school_year; ?>">
                                            <input type="hidden" name="semester" value="<?php echo $p['term']; ?>">
                                            <button type="submit" name="issue_permit" class="btn btn-sm btn-<?php echo $is_valid ? 'success' : 'outline-secondary'; ?> mt-1" style="font-size:0.7rem;">
                                                <i class="fas fa-print"></i> Issue
                                            </button>
                                        </form>
                                    </td>
                                <?php $i++; endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center text-muted">No enrolled students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Permits -->
<div class="card mt-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recently Issued Permits</h5>
    </div>
    <div class="card-body">
        <?php
        $recent_permits = $conn->query("
            SELECT p.*, s.student_number, s.firstname, s.lastname, su.first_name as issued_by_name
            FROM permits p
            JOIN students s ON p.student_id = s.id
            LEFT JOIN system_users su ON p.issued_by = su.id
            ORDER BY p.issued_at DESC LIMIT 10
        ");
        ?>
        <?php if ($recent_permits && $recent_permits->num_rows > 0): ?>
            <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="permitsTable" placeholder="Search permits..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
            <table class="table table-sm" id="permitsTable">
                <thead><tr><th>Student</th><th>Term</th><th>Period</th><th>School Year</th><th>Tuition</th><th>Paid</th><th>Status</th><th>Issued</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while ($rp = $recent_permits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rp['lastname'] . ', ' . $rp['firstname']); ?><br><small class="text-muted"><?php echo htmlspecialchars($rp['student_number']); ?></small></td>
                            <td><?php echo $rp['term']; ?></td>
                            <td><?php echo $rp['period']; ?></td>
                            <td><?php echo $rp['school_year']; ?></td>
                            <td><?php echo number_format($rp['total_tuition'], 2); ?></td>
                            <td><?php echo number_format($rp['total_paid'], 2); ?></td>
                            <td><span class="badge bg-<?php echo $rp['status'] == 'Valid' ? 'success' : 'danger'; ?>"><?php echo $rp['status']; ?></span></td>
                            <td><small><?php echo date('M d, Y', strtotime($rp['issued_at'])); ?></small></td>
                            <td><a href="print_permit.php?id=<?php echo $rp['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i></a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center">No permits issued yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.search-input').forEach(function(input) {
        input.addEventListener('keyup', function() {
            var filter = this.value.toLowerCase();
            var tableId = this.getAttribute('data-table');
            var table = document.getElementById(tableId);
            var clearBtn = this.parentElement.querySelector('.search-clear');
            if (clearBtn) clearBtn.style.display = filter ? 'block' : 'none';
            if (!table) return;
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    });
});

function clearSearch(btn) {
    var input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    btn.style.display = 'none';
    input.dispatchEvent(new Event('keyup'));
}
</script>
