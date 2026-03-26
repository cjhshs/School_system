<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dean') {
    header('Location: login.php');
    exit;
}

$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT su.*, d.name as dept_name, d.code as dept_code 
                      FROM system_users su 
                      LEFT JOIN departments d ON su.department_id = d.id 
                      WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_fee') {
            $course_code = $_POST['course_code'];
            $fee_name = trim($_POST['fee_name']);
            $amount = floatval($_POST['amount']);
            $description = trim($_POST['description'] ?? '');
            $semester = $_POST['semester'] ?? 'All';
            $is_required = isset($_POST['is_required']) ? 1 : 0;
            
            $stmt = $conn->prepare("INSERT INTO course_fees (course_code, fee_name, amount, semester, is_required, description) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE amount = VALUES(amount), is_required = VALUES(is_required), semester = VALUES(semester), description = VALUES(description)");
            $stmt->bind_param("ssdsis", $course_code, $fee_name, $amount, $semester, $is_required, $description);
            
            if ($stmt->execute()) {
                $message = "Additional fee saved successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'delete_fee') {
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM course_fees WHERE id = $id");
            $message = "Fee deleted successfully!";
        }
    }
}

// Get courses in this department
$courses = $conn->query("SELECT * FROM courses WHERE department_id = $dept_id ORDER BY code");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-plus-circle"></i> Additional Fees</h1>
        <p>Manage customizable fees for <?php echo htmlspecialchars($dept_name ?? 'your department'); ?> courses</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> These are additional/customizable fees (e.g., Lab Fee, Library Fee, Insurance, etc.). 
    Base tuition fees should be set by the Super Admin.
</div>

<div class="row g-4">
    <!-- Add Fee Form -->
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-plus me-2"></i>Add New Fee</h5>
            </div>
            <div class="card-body">
                <?php if ($courses && $courses->num_rows > 0): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="add_fee">
                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select name="course_code" class="form-select" required>
                            <option value="">Select Course</option>
                            <option value="ALL">All Courses (Same Fee)</option>
                            <?php while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['code']; ?>"><?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fee Name *</label>
                        <input type="text" name="fee_name" class="form-control" required placeholder="e.g., Lab Fee, Insurance, Athletic Fee">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description (optional)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount (PHP) *</label>
                        <input type="number" name="amount" class="form-control" required step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="All">All Semesters</option>
                            <option value="1st">1st Semester Only</option>
                            <option value="2nd">2nd Semester Only</option>
                            <option value="Summer">Summer Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" class="form-check-input" id="is_required" checked>
                            <label class="form-check-label" for="is_required">
                                <strong>Required Fee</strong> (automatically added to student)
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Fee</button>
                </form>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No courses found in your department.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Common Fee Templates -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-bookmark me-2"></i>Quick Add Templates</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Click to auto-fill common fees:</p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Laboratory Fee', 'For computer/science labs', 1500)">Lab Fee</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Library Fee', 'Library services access', 500)">Library Fee</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Athletic Fee', 'Sports and gym access', 750)">Athletic Fee</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Medical/Dental Fee', 'Health services', 300)">Medical Fee</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Guidance Fee', 'Counseling services', 250)">Guidance Fee</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Student Publication', 'School newspaper/magazine', 200)">Publication</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('ID Card Fee', 'Student identification card', 150)">ID Card</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillFee('Registration Fee', 'Enrollment processing', 500)">Registration</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Fees List -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Additional Fees List</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Fee Name</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Semester</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $course_fees = $conn->query("SELECT cf.*, c.name as course_name 
                                                    FROM course_fees cf 
                                                    LEFT JOIN courses c ON cf.course_code = c.code 
                                                    WHERE c.department_id = $dept_id 
                                                    ORDER BY cf.fee_name");
                        if ($course_fees && $course_fees->num_rows > 0): 
                            while ($fee = $course_fees->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($fee['course_code']); ?></span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($fee['fee_name']); ?></strong></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($fee['description'] ?? '-'); ?></small></td>
                            <td class="text-success fw-bold">₱<?php echo number_format($fee['amount'], 2); ?></td>
                            <td><span class="badge badge-light"><?php echo $fee['semester']; ?></span></td>
                            <td>
                                <?php if ($fee['is_required']): ?>
                                    <span class="badge badge-danger">Required</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Optional</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this fee?');">
                                    <input type="hidden" name="action" value="delete_fee">
                                    <input type="hidden" name="id" value="<?php echo $fee['id']; ?>">
                                    <button type="submit" class="btn btn-icon btn-ghost text-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                                    <h4>No Additional Fees</h4>
                                    <p>Add miscellaneous fees using the form on the left.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-calculator me-2"></i>Fee Summary by Course</h5>
            </div>
            <div class="card-body">
                <?php 
                $summary = $conn->query("SELECT c.code, c.name, COUNT(cf.id) as fee_count, SUM(cf.amount) as total 
                                        FROM courses c 
                                        LEFT JOIN course_fees cf ON c.code = cf.course_code 
                                        WHERE c.department_id = $dept_id 
                                        GROUP BY c.id, c.code, c.name 
                                        ORDER BY c.code");
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th class="text-center"># of Fees</th>
                                <th class="text-end">Total Additional Fees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $summary->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['code']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($row['name']); ?></small>
                                </td>
                                <td class="text-center"><?php echo $row['fee_count']; ?></td>
                                <td class="text-end text-success fw-bold">₱<?php echo number_format($row['total'] ?? 0, 2); ?></td>
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
function fillFee(name, desc, amount) {
    document.querySelector('input[name="fee_name"]').value = name;
    document.querySelector('input[name="description"]').value = desc;
    document.querySelector('input[name="amount"]').value = amount;
}
</script>
