<?php
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'process_payment') {
        $student_id = intval($_POST['student_id']);
        $amount = floatval($_POST['amount']);
        $payment_method = $_POST['payment_method'];
        $school_year = $_POST['school_year'] ?? '2025-2026';
        $semester = $_POST['semester'] ?? '1st';
        
        // Generate OR Number
        $or_number = 'OR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Get total fees for this student
        $student = $conn->query("SELECT s.*, c.code as course_code FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();
        
        // Get tuition
        $tuition = $conn->query("SELECT * FROM tuition_fees WHERE course_code = '{$student['course_code']}' AND year_level = {$student['year_level']}")->fetch_assoc();
        
        $total_fees = $tuition ? ($tuition['tuition_amount'] + $tuition['miscellaneous_amount'] + $tuition['laboratory_amount'] + $tuition['other_fees']) : 0;
        
        // Add course fees
        $course_fees = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM course_fees WHERE course_code = '{$student['course_code']}'");
        $total_fees += $course_fees->fetch_assoc()['total'];
        
        $balance = max(0, $total_fees - $amount);
        
        $stmt = $conn->prepare("INSERT INTO payments (student_id, or_number, payment_amount, total_fees, balance, payment_method, payment_date, school_year, semester, received_by) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
        $stmt->bind_param("isdddsssi", $student_id, $or_number, $amount, $total_fees, $balance, $payment_method, $school_year, $semester, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = "Payment processed! OR Number: <strong>$or_number</strong>";
        } else {
            $error = "Error processing payment: " . $conn->error;
        }
    }
}

// Get school year
$school_years = $conn->query("SELECT * FROM school_years ORDER BY year DESC");
?>
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-money-bill-wave"></i> Process Payment</h1>
        <p>Record student payments</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-search me-2"></i>Find Student</h5>
            </div>
            <div class="card-body">
                <form method="GET">
                    <input type="hidden" name="page" value="payments">
                    <div class="input-group mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Enter Student Number or Name" value="<?php echo $_GET['search'] ?? ''; ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </form>
                
                <?php if (isset($_GET['search'])): ?>
                    <?php
                    $search = $conn->real_escape_string($_GET['search']);
                    $students = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code 
                        FROM students s 
                        LEFT JOIN courses c ON s.course_id = c.id 
                        WHERE s.student_number LIKE '%$search%' OR s.firstname LIKE '%$search%' OR s.lastname LIKE '%$search%'
                        LIMIT 10");
                    ?>
                    <div class="list-group">
                        <?php if ($students && $students->num_rows > 0): ?>
                            <?php while ($s = $students->fetch_assoc()): ?>
                                <a href="?page=payments&student_id=<?php echo $s['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($s['student_number']); ?></strong>
                                            <br><small><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></small>
                                        </div>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($s['course_code']); ?></span>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-muted text-center py-3">No students found</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7">
        <?php if (isset($_GET['student_id'])): ?>
            <?php
            $student_id = intval($_GET['student_id']);
            $student = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code 
                FROM students s 
                LEFT JOIN courses c ON s.course_id = c.id 
                WHERE s.id = $student_id")->fetch_assoc();
            
            if ($student):
                // Get tuition
                $tuition = $conn->query("SELECT * FROM tuition_fees WHERE course_code = '{$student['course_code']}' AND year_level = {$student['year_level']}")->fetch_assoc();
                $total_fees = $tuition ? ($tuition['tuition_amount'] + $tuition['miscellaneous_amount'] + $tuition['laboratory_amount'] + $tuition['other_fees']) : 0;
                
                // Get course fees
                $cf = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM course_fees WHERE course_code = '{$student['course_code']}'")->fetch_assoc();
                $total_fees += $cf['total'];
                
                // Get payments made
                $paid = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE student_id = $student_id")->fetch_assoc();
                $balance = max(0, $total_fees - $paid['total']);
            ?>
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_number']); ?></p>
                            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course_code'] . ' - ' . $student['course_name']); ?></p>
                            <p><strong>Year Level:</strong> <?php echo $student['year_level']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Fees:</strong> <span class="text-primary fw-bold">₱<?php echo number_format($total_fees, 2); ?></span></p>
                            <p><strong>Paid:</strong> <span class="text-success fw-bold">₱<?php echo number_format($paid['total'], 2); ?></span></p>
                            <p><strong>Balance:</strong> <span class="text-danger fw-bold">₱<?php echo number_format($balance, 2); ?></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Process Payment</h6>
                    <form method="POST">
                        <input type="hidden" name="action" value="process_payment">
                        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Amount (PHP)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" max="<?php echo $balance; ?>" value="<?php echo $balance; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Check">Check</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Online Payment">Online Payment</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">School Year</label>
                                <select name="school_year" class="form-select">
                                    <?php while ($sy = $school_years->fetch_assoc()): ?>
                                        <option value="<?php echo $sy['year']; ?>" <?php echo $sy['is_current'] ? 'selected' : ''; ?>><?php echo $sy['year']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-select">
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check-circle me-2"></i>Process Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-money-bill-wave text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Select a Student</h5>
                    <p class="text-muted">Search for a student on the left to process payment</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
