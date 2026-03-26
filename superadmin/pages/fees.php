<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // ==================== TUITION FEES CRUD ====================
        if ($_POST['action'] === 'add_tuition') {
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
                $message = "Tuition fee saved successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
        
        elseif ($_POST['action'] === 'update_tuition') {
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
                $message = "Tuition fee updated successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
        
        elseif ($_POST['action'] === 'delete_tuition') {
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM tuition_fees WHERE id = $id");
            $message = "Tuition fee deleted!";
        }
        
        // ==================== ADDITIONAL FEES CRUD ====================
        elseif ($_POST['action'] === 'add_fee') {
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
        }
        
        elseif ($_POST['action'] === 'update_fee') {
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
        }
        
        elseif ($_POST['action'] === 'delete_fee') {
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM course_fees WHERE id = $id");
            $message = "Fee deleted successfully!";
        }
        
        // ==================== FEE TYPES CRUD ====================
        elseif ($_POST['action'] === 'add_fee_type') {
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
        }
        
        elseif ($_POST['action'] === 'update_fee_type') {
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
        }
        
        elseif ($_POST['action'] === 'delete_fee_type') {
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM fee_types WHERE id = $id");
            $message = "Fee template deleted!";
        }
    }
}

// Get edit data
$edit_tuition = null;
$edit_fee = null;
$edit_fee_type = null;

if (isset($_GET['edit_tuition'])) {
    $edit_id = intval($_GET['edit_tuition']);
    $edit_tuition = $conn->query("SELECT * FROM tuition_fees WHERE id = $edit_id")->fetch_assoc();
}
if (isset($_GET['edit_fee'])) {
    $edit_id = intval($_GET['edit_fee']);
    $edit_fee = $conn->query("SELECT * FROM course_fees WHERE id = $edit_id")->fetch_assoc();
}
if (isset($_GET['edit_fee_type'])) {
    $edit_id = intval($_GET['edit_fee_type']);
    $edit_fee_type = $conn->query("SELECT * FROM fee_types WHERE id = $edit_id")->fetch_assoc();
}

$courses = $conn->query("SELECT * FROM courses ORDER BY code");
$fee_types = $conn->query("SELECT * FROM fee_types ORDER BY name");
$course_fees = $conn->query("SELECT cf.*, c.name as course_name, d.name as dept_name 
                             FROM course_fees cf 
                             LEFT JOIN courses c ON cf.course_code = c.code 
                             LEFT JOIN departments d ON c.department_id = d.id 
                             ORDER BY d.name, c.code, cf.fee_name");
$tuition_fees = $conn->query("SELECT tf.*, c.name as course_name FROM tuition_fees tf LEFT JOIN courses c ON tf.course_code = c.code ORDER BY tf.course_code, tf.year_level");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-coins"></i> Fees Management</h1>
        <p>Manage tuition fees, miscellaneous fees, and templates</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<!-- TUITION FEES SECTION -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-graduation-cap me-2"></i><?php echo $edit_tuition ? 'Edit Tuition Fee' : 'Tuition Fees Management'; ?></h5>
        <?php if ($edit_tuition): ?>
            <a href="?page=fees" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i>Cancel Edit</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($edit_tuition): ?>
            <!-- Edit Form -->
            <form method="POST" class="mb-4 p-3 bg-light rounded">
                <input type="hidden" name="action" value="update_tuition">
                <input type="hidden" name="id" value="<?php echo $edit_tuition['id']; ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Course *</label>
                        <select name="course_code" class="form-select" required>
                            <?php $courses_all = $conn->query("SELECT DISTINCT code, name FROM courses ORDER BY code"); ?>
                            <?php while ($c = $courses_all->fetch_assoc()): ?>
                                <option value="<?php echo $c['code']; ?>" <?php echo $edit_tuition['course_code'] === $c['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year Level *</label>
                        <select name="year_level" class="form-select" required>
                            <option value="1" <?php echo $edit_tuition['year_level'] == 1 ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2" <?php echo $edit_tuition['year_level'] == 2 ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3" <?php echo $edit_tuition['year_level'] == 3 ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4" <?php echo $edit_tuition['year_level'] == 4 ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tuition (PHP)</label>
                        <input type="number" name="tuition_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['tuition_amount']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Miscellaneous</label>
                        <input type="number" name="misc_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['miscellaneous_amount']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Laboratory</label>
                        <input type="number" name="lab_amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['laboratory_amount']; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Other Fees</label>
                        <input type="number" name="other_fees" class="form-control" step="0.01" min="0" value="<?php echo $edit_tuition['other_fees']; ?>">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="All" <?php echo $edit_tuition['semester'] === 'All' ? 'selected' : ''; ?>>All Semesters</option>
                            <option value="1st" <?php echo $edit_tuition['semester'] === '1st' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd" <?php echo $edit_tuition['semester'] === '2nd' ? 'selected' : ''; ?>>2nd Semester</option>
                            <option value="Summer" <?php echo $edit_tuition['semester'] === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-2"></i>Update Tuition</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <!-- Add Form -->
            <form method="POST" class="mb-4">
                <input type="hidden" name="action" value="add_tuition">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Course *</label>
                        <select name="course_code" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php $courses_all = $conn->query("SELECT DISTINCT code, name FROM courses ORDER BY code"); ?>
                            <?php while ($c = $courses_all->fetch_assoc()): ?>
                                <option value="<?php echo $c['code']; ?>"><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Year Level *</label>
                        <select name="year_level" class="form-select" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tuition (PHP)</label>
                        <input type="number" name="tuition_amount" class="form-control" step="0.01" min="0" placeholder="15000" value="15000">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Miscellaneous</label>
                        <input type="number" name="misc_amount" class="form-control" step="0.01" min="0" placeholder="5000" value="5000">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Laboratory</label>
                        <input type="number" name="lab_amount" class="form-control" step="0.01" min="0" placeholder="2000" value="2000">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Other Fees</label>
                        <input type="number" name="other_fees" class="form-control" step="0.01" min="0" placeholder="1000" value="1000">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="All">All Semesters</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Add</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Tuition</th>
                        <th>Misc</th>
                        <th>Lab</th>
                        <th>Other</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tuition_fees && $tuition_fees->num_rows > 0): ?>
                        <?php while ($tf = $tuition_fees->fetch_assoc()): ?>
                            <?php $total = $tf['tuition_amount'] + $tf['miscellaneous_amount'] + $tf['laboratory_amount'] + $tf['other_fees']; ?>
                            <tr class="<?php echo isset($edit_tuition) && $edit_tuition['id'] == $tf['id'] ? 'table-warning' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($tf['course_code']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($tf['course_name'] ?? ''); ?></small></td>
                                <td><?php echo $tf['year_level']; ?>th Year</td>
                                <td class="text-primary fw-bold">₱<?php echo number_format($tf['tuition_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($tf['miscellaneous_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($tf['laboratory_amount'], 2); ?></td>
                                <td>₱<?php echo number_format($tf['other_fees'], 2); ?></td>
                                <td class="text-success fw-bold">₱<?php echo number_format($total, 2); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?page=fees&edit_tuition=<?php echo $tf['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this tuition fee?');">
                                            <input type="hidden" name="action" value="delete_tuition">
                                            <input type="hidden" name="id" value="<?php echo $tf['id']; ?>">
                                            <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">No tuition fees configured. Add using the form above.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Fee Templates -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-layer-group me-2"></i>
                    <?php echo $edit_fee_type ? 'Edit Fee Template' : 'Fee Templates'; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($edit_fee_type): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_fee_type">
                        <input type="hidden" name="id" value="<?php echo $edit_fee_type['id']; ?>">
                        <div class="form-group">
                            <label class="form-label">Template Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_fee_type['name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_fee_type['description']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Amount (PHP)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?php echo $edit_fee_type['amount']; ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button>
                            <a href="?page=fees" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="add_fee_type">
                        <div class="form-group">
                            <label class="form-label">Template Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Lab Fee, Insurance">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Amount (PHP)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Create Template</button>
                    </form>
                <?php endif; ?>
                
                <?php $fee_types = $conn->query("SELECT * FROM fee_types ORDER BY name"); ?>
                <?php if ($fee_types && $fee_types->num_rows > 0): ?>
                <hr>
                <h6>Existing Templates</h6>
                <div class="list-group">
                    <?php while ($ft = $fee_types->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo htmlspecialchars($ft['name']); ?></strong>
                                <?php if ($ft['description']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($ft['description']); ?></small>
                                <?php endif; ?>
                                <?php if ($ft['amount']): ?>
                                    <br><span class="badge bg-success">₱<?php echo number_format($ft['amount'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="?page=fees&edit_fee_type=<?php echo $ft['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this template?');">
                                    <input type="hidden" name="action" value="delete_fee_type">
                                    <input type="hidden" name="id" value="<?php echo $ft['id']; ?>">
                                    <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Additional Fees -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    <?php echo $edit_fee ? 'Edit Additional Fee' : 'Add Additional Fee'; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($edit_fee): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_fee">
                        <input type="hidden" name="id" value="<?php echo $edit_fee['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Course *</label>
                                    <select name="course_code" class="form-select" required>
                                        <option value="">Select Course</option>
                                        <option value="ALL" <?php echo $edit_fee['course_code'] === 'ALL' ? 'selected' : ''; ?>>All Courses</option>
                                        <?php $courses = $conn->query("SELECT * FROM courses ORDER BY code"); ?>
                                        <?php while ($course = $courses->fetch_assoc()): ?>
                                            <option value="<?php echo $course['code']; ?>" <?php echo $edit_fee['course_code'] === $course['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Fee Name *</label>
                                    <input type="text" name="fee_name" class="form-control" required value="<?php echo htmlspecialchars($edit_fee['fee_name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Amount (PHP) *</label>
                                    <input type="number" name="amount" class="form-control" required step="0.01" min="0" value="<?php echo $edit_fee['amount']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="All" <?php echo $edit_fee['semester'] === 'All' ? 'selected' : ''; ?>>All Semesters</option>
                                        <option value="1st" <?php echo $edit_fee['semester'] === '1st' ? 'selected' : ''; ?>>1st Semester</option>
                                        <option value="2nd" <?php echo $edit_fee['semester'] === '2nd' ? 'selected' : ''; ?>>2nd Semester</option>
                                        <option value="Summer" <?php echo $edit_fee['semester'] === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_fee['description']); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_required" class="form-check-input" id="is_required" <?php echo $edit_fee['is_required'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_required">Required Fee</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Fee</button>
                                    <a href="?page=fees" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_fee">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Course *</label>
                                    <select name="course_code" class="form-select" required>
                                        <option value="">Select Course</option>
                                        <option value="ALL">All Courses</option>
                                        <?php $courses = $conn->query("SELECT * FROM courses ORDER BY code"); ?>
                                        <?php while ($course = $courses->fetch_assoc()): ?>
                                            <option value="<?php echo $course['code']; ?>"><?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Fee Name *</label>
                                    <input type="text" name="fee_name" class="form-control" required placeholder="e.g., Lab Fee, Athletic Fee">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Amount (PHP) *</label>
                                    <input type="number" name="amount" class="form-control" required step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="All">All Semesters</option>
                                        <option value="1st">1st Semester</option>
                                        <option value="2nd">2nd Semester</option>
                                        <option value="Summer">Summer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_required" class="form-check-input" id="is_required" checked>
                                    <label class="form-check-label" for="is_required">Required Fee</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Fee</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fees List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Additional Fees List</h5>
            </div>
            <div class="card-body pt-0">
                <div class="search-container">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input table-search" data-table="feesTable" placeholder="Search fees by name, course, or department...">
                        <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table" id="feesTable">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Course</th>
                            <th>Fee Name</th>
                            <th>Amount</th>
                            <th>Semester</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $course_fees = $conn->query("SELECT cf.*, c.name as course_name, d.name as dept_name 
                                                     FROM course_fees cf 
                                                     LEFT JOIN courses c ON cf.course_code = c.code 
                                                     LEFT JOIN departments d ON c.department_id = d.id 
                                                     ORDER BY d.name, c.code, cf.fee_name");
                        if ($course_fees && $course_fees->num_rows > 0): 
                            while ($fee = $course_fees->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($fee['dept_name'] ?? 'All'); ?></span></td>
                            <td><?php echo htmlspecialchars($fee['course_code']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($fee['fee_name']); ?></strong>
                                <?php if ($fee['description']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($fee['description']); ?></small>
                                <?php endif; ?>
                            </td>
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
                                <div class="btn-group btn-group-sm">
                                    <a href="?page=fees&edit_fee=<?php echo $fee['id']; ?>" class="btn btn-ghost text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this fee?');">
                                        <input type="hidden" name="action" value="delete_fee">
                                        <input type="hidden" name="id" value="<?php echo $fee['id']; ?>">
                                        <button type="submit" class="btn btn-ghost text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-coins"></i></div>
                                    <h4>No Additional Fees</h4>
                                    <p>Add fees using the form above.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('.table-search[data-table="feesTable"]')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#feesTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
