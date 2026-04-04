<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';
require_once dirname(dirname(__DIR__)) . '/includes/fees_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';
$preselected_course = isset($_GET['add_tuition_for']) ? $_GET['add_tuition_for'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_tuition') {
        $course_code = $_POST['course_code'];
        $year_level = intval($_POST['year_level']);
        $semester = $_POST['semester'] ?? 'All';
        $tuition_amount = floatval($_POST['tuition_amount']);
        $misc_amount = floatval($_POST['misc_amount'] ?? 0);
        $lab_amount = floatval($_POST['lab_amount'] ?? 0);
        $other_fees = floatval($_POST['other_fees'] ?? 0);
        $total = $tuition_amount + $misc_amount + $lab_amount + $other_fees;
        
        $stmt = $conn->prepare("INSERT INTO tuition_fees (course_code, year_level, semester, tuition_amount, miscellaneous_amount, laboratory_amount, other_fees, total_per_unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE tuition_amount = VALUES(tuition_amount), miscellaneous_amount = VALUES(miscellaneous_amount), laboratory_amount = VALUES(laboratory_amount), other_fees = VALUES(other_fees), total_per_unit = VALUES(total_per_unit)");
        $stmt->bind_param("sisdiddd", $course_code, $year_level, $semester, $tuition_amount, $misc_amount, $lab_amount, $other_fees, $total);
        
        if ($stmt->execute()) {
            syncStudentFees($conn, $course_code, $total);
            $message = "Tuition fee saved and synced to student accounts!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'update_tuition') {
        $id = intval($_POST['id']);
        $course_code = $_POST['course_code'];
        $year_level = intval($_POST['year_level']);
        $semester = $_POST['semester'] ?? 'All';
        $tuition_amount = floatval($_POST['tuition_amount']);
        $misc_amount = floatval($_POST['misc_amount'] ?? 0);
        $lab_amount = floatval($_POST['lab_amount'] ?? 0);
        $other_fees = floatval($_POST['other_fees'] ?? 0);
        $total = $tuition_amount + $misc_amount + $lab_amount + $other_fees;
        
        $stmt = $conn->prepare("UPDATE tuition_fees SET course_code = ?, year_level = ?, semester = ?, tuition_amount = ?, miscellaneous_amount = ?, laboratory_amount = ?, other_fees = ?, total_per_unit = ? WHERE id = ?");
        $stmt->bind_param("sisdidddi", $course_code, $year_level, $semester, $tuition_amount, $misc_amount, $lab_amount, $other_fees, $total, $id);
        
        if ($stmt->execute()) {
            syncStudentFees($conn, $course_code, $total);
            $message = "Tuition fee updated and synced to student accounts!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'delete_tuition') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM tuition_fees WHERE id = $id");
        $message = "Tuition fee deleted!";
    } elseif ($action === 'add_fee') {
        $course_code = $_POST['course_code'];
        $fee_name = trim($_POST['fee_name']);
        $description = trim($_POST['description'] ?? '');
        $amount = floatval($_POST['amount']);
        $semester = $_POST['semester'] ?? 'All';
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO course_fees (course_code, fee_name, amount, semester, is_required, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsis", $course_code, $fee_name, $amount, $semester, $is_required, $description);
        
        if ($stmt->execute()) {
            $message = "Fee added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'update_fee') {
        $id = intval($_POST['id']);
        $course_code = $_POST['course_code'];
        $fee_name = trim($_POST['fee_name']);
        $description = trim($_POST['description'] ?? '');
        $amount = floatval($_POST['amount']);
        $semester = $_POST['semester'] ?? 'All';
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE course_fees SET course_code = ?, fee_name = ?, amount = ?, semester = ?, is_required = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssdsisi", $course_code, $fee_name, $amount, $semester, $is_required, $description, $id);
        
        if ($stmt->execute()) {
            $message = "Fee updated successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'delete_fee') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM course_fees WHERE id = $id");
        $message = "Fee deleted successfully!";
    } elseif ($action === 'add_fee_type') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $amount = floatval($_POST['amount']);
        
        $stmt = $conn->prepare("INSERT INTO fee_types (name, description, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $description, $amount);
        
        if ($stmt->execute()) {
            $message = "Fee template created!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'update_fee_type') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $amount = floatval($_POST['amount']);
        
        $stmt = $conn->prepare("UPDATE fee_types SET name = ?, description = ?, amount = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $name, $description, $amount, $id);
        
        if ($stmt->execute()) {
            $message = "Fee template updated!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif ($action === 'delete_fee_type') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM fee_types WHERE id = $id");
        $message = "Fee template deleted!";
    }
}

$edit_tuition = null;
$edit_fee = null;
$edit_fee_type = null;

if (isset($_GET['edit_tuition'])) {
    $edit_tuition = $conn->query("SELECT * FROM tuition_fees WHERE id = " . intval($_GET['edit_tuition']))->fetch_assoc();
}
if (isset($_GET['edit_fee'])) {
    $edit_fee = $conn->query("SELECT * FROM course_fees WHERE id = " . intval($_GET['edit_fee']))->fetch_assoc();
}
if (isset($_GET['edit_fee_type'])) {
    $edit_fee_type = $conn->query("SELECT * FROM fee_types WHERE id = " . intval($_GET['edit_fee_type']))->fetch_assoc();
}

$fee_types = $conn->query("SELECT * FROM fee_types ORDER BY name");
$course_fees = $conn->query("SELECT cf.*, c.name as course_name, d.name as dept_name FROM course_fees cf LEFT JOIN courses c ON cf.course_code = c.code LEFT JOIN departments d ON c.department_id = d.id ORDER BY d.name, c.code, cf.fee_name");
$tuition_fees = $conn->query("SELECT tf.*, c.name as course_name FROM tuition_fees tf LEFT JOIN courses c ON tf.course_code = c.code ORDER BY tf.course_code, tf.year_level");
$courses = $conn->query("SELECT * FROM courses ORDER BY code");
$courses_without_fees = $conn->query("SELECT c.*, d.name as dept_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id WHERE c.code NOT IN (SELECT DISTINCT course_code FROM tuition_fees) ORDER BY d.name, c.code");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-coins me-2"></i>Fees Management</h1>
        <p>Manage tuition fees, miscellaneous fees, and templates</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php
$count_no_fees = $courses_without_fees ? $courses_without_fees->num_rows : 0;
if ($count_no_fees > 0) $courses_without_fees->data_seek(0);
?>

<?php if ($count_no_fees > 0): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Courses Without Fees (<?php echo $count_no_fees; ?>)</h5>
    </div>
    <div class="card-body">
        <p class="mb-2">There are <strong><?php echo $count_no_fees; ?> course(s)</strong> that have not been assigned tuition fees yet.</p>
        <table class="table table-sm">
            <thead><tr><th>Course</th><th>Department</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($cw = $courses_without_fees->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($cw['code'] . ' - ' . $cw['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($cw['dept_name'] ?? 'N/A'); ?></td>
                    <td><a href="?page=fees&add_tuition_for=<?php echo urlencode($cw['code']); ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Fee</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Tuition Fees -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-graduation-cap me-2"></i><?php echo $edit_tuition ? 'Edit Tuition Fee' : 'Tuition Fees'; ?></h5>
        <?php if ($edit_tuition): ?><a href="?page=fees" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i>Cancel</a><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="<?php echo $edit_tuition ? 'update_tuition' : 'add_tuition'; ?>">
            <?php if ($edit_tuition): ?><input type="hidden" name="id" value="<?php echo $edit_tuition['id']; ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Course</label>
                    <select name="course_code" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php $courses->data_seek(0); while($c = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $c['code']; ?>" <?php echo ($edit_tuition['course_code'] ?? $preselected_course) === $c['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Year Level</label>
                    <select name="year_level" class="form-select" required>
                        <?php for ($y = 1; $y <= 4; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($edit_tuition['year_level'] ?? 1) == $y ? 'selected' : ''; ?>><?php echo $y; ?><?php echo $y == 1 ? 'st' : ($y == 2 ? 'nd' : ($y == 3 ? 'rd' : 'th')); ?> Year</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tuition</label>
                    <input type="number" name="tuition_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['tuition_amount'] ?? 15000; ?>">
                </div>
                <div class="col-md-2">
                    <label>Miscellaneous</label>
                    <input type="number" name="misc_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['miscellaneous_amount'] ?? 3000; ?>">
                </div>
                <div class="col-md-1">
                    <label>Laboratory</label>
                    <input type="number" name="lab_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['laboratory_amount'] ?? 1000; ?>">
                </div>
                <div class="col-md-1">
                    <label>Other Fees</label>
                    <input type="number" name="other_fees" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['other_fees'] ?? 1000; ?>">
                </div>
                <div class="col-md-1">
                    <label>Semester</label>
                    <select name="semester" class="form-select">
                        <option value="All" <?php echo ($edit_tuition['semester'] ?? 'All') === 'All' ? 'selected' : ''; ?>>All</option>
                        <option value="1st" <?php echo ($edit_tuition['semester'] ?? '') === '1st' ? 'selected' : ''; ?>>1st</option>
                        <option value="2nd" <?php echo ($edit_tuition['semester'] ?? '') === '2nd' ? 'selected' : ''; ?>>2nd</option>
                        <option value="Summer" <?php echo ($edit_tuition['semester'] ?? '') === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo $edit_tuition ? 'Update' : 'Save'; ?> Tuition Fee</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tuition Fees Table -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Configured Tuition Fees</h5>
    </div>
    <div class="card-body">
        <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="tuitionTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
        <table class="table table-striped" id="tuitionTable">
            <thead><tr><th>Course</th><th>Year</th><th>Tuition</th><th>Misc</th><th>Lab</th><th>Other</th><th>Total</th><th>Semester</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($tuition_fees && $tuition_fees->num_rows > 0): ?>
                    <?php while ($tf = $tuition_fees->fetch_assoc()): ?>
                        <?php $total = $tf['tuition_amount'] + $tf['miscellaneous_amount'] + $tf['laboratory_amount'] + $tf['other_fees']; ?>
                        <tr class="<?php echo isset($edit_tuition) && $edit_tuition['id'] == $tf['id'] ? 'table-warning' : ''; ?>">
                            <td><?php echo htmlspecialchars($tf['course_name'] ?? $tf['course_code']); ?></td>
                            <td><?php echo $tf['year_level']; ?></td>
                            <td class="text-primary fw-bold">₱<?php echo number_format($tf['tuition_amount'], 2); ?></td>
                            <td>₱<?php echo number_format($tf['miscellaneous_amount'], 2); ?></td>
                            <td>₱<?php echo number_format($tf['laboratory_amount'], 2); ?></td>
                            <td>₱<?php echo number_format($tf['other_fees'], 2); ?></td>
                            <td class="fw-bold">₱<?php echo number_format($total, 2); ?></td>
                            <td><?php echo $tf['semester']; ?></td>
                            <td>
                                <a href="?page=fees&edit_tuition=<?php echo $tf['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this tuition fee?');">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_tuition">
                                    <input type="hidden" name="id" value="<?php echo $tf['id']; ?>">
                                    <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center py-3 text-muted">No tuition fees configured.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Course Fees -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i><?php echo $edit_fee ? 'Edit Additional Fee' : 'Additional Course Fees'; ?></h5>
        <?php if ($edit_fee): ?><a href="?page=fees" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i>Cancel</a><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="<?php echo $edit_fee ? 'update_fee' : 'add_fee'; ?>">
            <?php if ($edit_fee): ?><input type="hidden" name="id" value="<?php echo $edit_fee['id']; ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Course</label>
                    <select name="course_code" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php $courses->data_seek(0); while($c = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $c['code']; ?>" <?php echo ($edit_fee['course_code'] ?? '') === $c['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Fee Name</label>
                    <input type="text" name="fee_name" class="form-control" required value="<?php echo htmlspecialchars($edit_fee['fee_name'] ?? ''); ?>" placeholder="e.g., Library Fee">
                </div>
                <div class="col-md-2">
                    <label>Amount</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required value="<?php echo $edit_fee['amount'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <label>Semester</label>
                    <select name="semester" class="form-select">
                        <option value="All" <?php echo ($edit_fee['semester'] ?? 'All') === 'All' ? 'selected' : ''; ?>>All</option>
                        <option value="1st" <?php echo ($edit_fee['semester'] ?? '') === '1st' ? 'selected' : ''; ?>>1st</option>
                        <option value="2nd" <?php echo ($edit_fee['semester'] ?? '') === '2nd' ? 'selected' : ''; ?>>2nd</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_required" class="form-check-input" id="feeRequired" <?php echo ($edit_fee['is_required'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="feeRequired">Required</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i><?php echo $edit_fee ? 'Update' : 'Add'; ?> Fee</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Course Fees Table -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Additional Fees</h5>
    </div>
    <div class="card-body">
        <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="courseFeesTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
        <table class="table table-striped" id="courseFeesTable">
            <thead><tr><th>Course</th><th>Fee Name</th><th>Amount</th><th>Semester</th><th>Required</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($course_fees && $course_fees->num_rows > 0): ?>
                    <?php while ($cf = $course_fees->fetch_assoc()): ?>
                        <tr class="<?php echo isset($edit_fee) && $edit_fee['id'] == $cf['id'] ? 'table-warning' : ''; ?>">
                            <td><?php echo htmlspecialchars($cf['course_name'] ?? $cf['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($cf['fee_name']); ?></td>
                            <td class="text-success fw-bold">₱<?php echo number_format($cf['amount'], 2); ?></td>
                            <td><?php echo $cf['semester']; ?></td>
                            <td><span class="badge bg-<?php echo $cf['is_required'] ? 'success' : 'secondary'; ?>"><?php echo $cf['is_required'] ? 'Yes' : 'No'; ?></span></td>
                            <td><?php echo htmlspecialchars($cf['description'] ?? '-'); ?></td>
                            <td>
                                <a href="?page=fees&edit_fee=<?php echo $cf['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this fee?');">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_fee">
                                    <input type="hidden" name="id" value="<?php echo $cf['id']; ?>">
                                    <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-3 text-muted">No additional fees configured.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fee Types -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-file-invoice me-2"></i><?php echo $edit_fee_type ? 'Edit Fee Template' : 'Fee Templates'; ?></h5>
        <?php if ($edit_fee_type): ?><a href="?page=fees" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i>Cancel</a><?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="<?php echo $edit_fee_type ? 'update_fee_type' : 'add_fee_type'; ?>">
            <?php if ($edit_fee_type): ?><input type="hidden" name="id" value="<?php echo $edit_fee_type['id']; ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Template Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_fee_type['name'] ?? ''); ?>" placeholder="e.g., Enrollment Fee">
                </div>
                <div class="col-md-3">
                    <label>Amount</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required value="<?php echo $edit_fee_type['amount'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_fee_type['description'] ?? ''); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-save me-1"></i><?php echo $edit_fee_type ? 'Update' : 'Create'; ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Fee Types Table -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Fee Templates</h5>
    </div>
    <div class="card-body">
        <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="feeTypesTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
        <table class="table table-striped" id="feeTypesTable">
            <thead><tr><th>Name</th><th>Amount</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($fee_types && $fee_types->num_rows > 0): ?>
                    <?php while ($ft = $fee_types->fetch_assoc()): ?>
                        <tr class="<?php echo isset($edit_fee_type) && $edit_fee_type['id'] == $ft['id'] ? 'table-warning' : ''; ?>">
                            <td><?php echo htmlspecialchars($ft['name']); ?></td>
                            <td class="fw-bold">₱<?php echo number_format($ft['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($ft['description'] ?? '-'); ?></td>
                            <td>
                                <a href="?page=fees&edit_fee_type=<?php echo $ft['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this fee template?');">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_fee_type">
                                    <input type="hidden" name="id" value="<?php echo $ft['id']; ?>">
                                    <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-3 text-muted">No fee templates configured.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('keyup', function() {
        const tableId = this.getAttribute('data-table');
        const filter = this.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        table.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('keyup'));
}
</script>
