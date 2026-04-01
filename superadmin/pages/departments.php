<?php
// Handle AJAX request for getting assigned courses
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_courses' && isset($_GET['dept_id'])) {
    $dept_id = intval($_GET['dept_id']);
    $courses = $conn->query("SELECT id FROM courses WHERE department_id = $dept_id");
    $course_ids = [];
    while ($c = $courses->fetch_assoc()) {
        $course_ids[] = $c['id'];
    }
    echo json_encode($course_ids);
    exit;
}

// Handle form submissions - MUST BE BEFORE any output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'create_department') {
        $name = $conn->real_escape_string($_POST['name']);
        $code = $conn->real_escape_string(strtoupper($_POST['code']));
        
        $conn->query("INSERT INTO departments (name, code) VALUES ('$name', '$code') ON DUPLICATE KEY UPDATE name=VALUES(name), code=VALUES(code)");
        $dept_id = $conn->insert_id;
        
        $_SESSION['success'] = "Department '$name' created! Now assign a dean and courses.";
        header("Location: dashboard.php?page=departments&action=edit_dept&id=$dept_id");
        exit;
    }
    
    if ($_POST['action'] == 'assign_dean') {
        $dept_id = intval($_POST['department_id']);
        $dean_id = intval($_POST['dean_id']);
        
        // Remove previous dean assignment if exists
        $conn->query("UPDATE departments SET dean_id = NULL WHERE dean_id = $dean_id");
        
        // Update department with dean
        $conn->query("UPDATE departments SET dean_id = $dean_id WHERE id = $dept_id");
        
        // Also update the dean's department_id in system_users
        $conn->query("UPDATE system_users SET department_id = $dept_id WHERE id = $dean_id");
        
        // Insert into deans table
        $conn->query("INSERT INTO deans (user_id, department_id, appointment_date, status) 
            VALUES ($dean_id, $dept_id, CURDATE(), 'Active') 
            ON DUPLICATE KEY UPDATE status = 'Active', department_id = $dept_id");
        
        $_SESSION['success'] = "Dean assigned successfully!";
        header("Location: dashboard.php?page=departments");
        exit;
    }
    
    if ($_POST['action'] == 'assign_courses') {
        $dept_id = intval($_POST['department_id']);
        $course_ids = $_POST['course_ids'] ?? [];
        
        // Remove all courses from this department first
        $conn->query("UPDATE courses SET department_id = NULL WHERE department_id = $dept_id");
        
        // Assign selected courses
        foreach ($course_ids as $course_id) {
            $conn->query("UPDATE courses SET department_id = $dept_id WHERE id = " . intval($course_id));
        }
        
        $_SESSION['success'] = "Courses assigned successfully!";
        header("Location: dashboard.php?page=departments");
        exit;
    }
    
    if ($_POST['action'] == 'delete_department') {
        $dept_id = intval($_POST['department_id']);
        
        // Unassign courses
        $conn->query("UPDATE courses SET department_id = NULL WHERE department_id = $dept_id");
        
        // Unassign dean
        $conn->query("UPDATE departments SET dean_id = NULL WHERE id = $dept_id");
        
        // Delete department
        $conn->query("DELETE FROM departments WHERE id = $dept_id");
        
        $_SESSION['success'] = "Department deleted!";
        header("Location: dashboard.php?page=departments");
        exit;
    }
    // Phase A: Bulk Move Courses into a destination department
    else if ($_POST['action'] == 'move_courses') {
        $dest = isset($_POST['destination_dept_id']) ? intval($_POST['destination_dept_id']) : 0;
        $course_ids = isset($_POST['course_ids']) ? array_map('intval', $_POST['course_ids']) : [];
        if (!$dest) {
            $error = 'Please select a destination department.';
        } elseif (empty($course_ids)) {
            $error = 'No courses selected.';
        } else {
            $ids = implode(',', $course_ids);
            $conn->query("UPDATE courses SET department_id = $dest WHERE id IN ($ids)");
            $message = 'Moved ' . count($course_ids) . ' course(s) to the selected department.';
        }
    }
}

// Get all available deans (users with role_id = 3)
$available_deans = $conn->query("SELECT id, first_name, last_name, department_id FROM system_users WHERE role_id = 3");

// Get all departments with dean info
$departments = $conn->query("
    SELECT d.*, su.first_name, su.last_name 
    FROM departments d 
    LEFT JOIN system_users su ON d.dean_id = su.id
    ORDER BY d.name
");

// Get ungrouped courses (no department)
$ungrouped_courses = $conn->query("
    SELECT c.* FROM courses c ORDER BY c.code, c.name
");

// Get all courses
$all_courses = $conn->query("SELECT * FROM courses ORDER BY code, name");
$all_courses_arr = [];
while ($c = $all_courses->fetch_assoc()) {
    $all_courses_arr[] = $c;
}

// Check if editing specific department
$edit_dept_id = isset($_GET['action']) && $_GET['action'] == 'edit_dept' ? intval($_GET['id']) : null;
$edit_dept = null;
if ($edit_dept_id) {
    $edit_dept = $conn->query("SELECT * FROM departments WHERE id = $edit_dept_id")->fetch_assoc();
}
<?php include __DIR__ . '/../../registrar/pages/move_courses_inline.php'; ?>
?>

<style>
.dept-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
}
.dept-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}
.dept-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.dept-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
}
.dept-code {
    background: #667eea;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-left: 10px;
}
.dept-status {
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 20px;
}
.dept-status.complete {
    background: #d4edda;
    color: #155724;
}
.dept-status.incomplete {
    background: #fff3cd;
    color: #856404;
}
.dept-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.dept-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}
.dept-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dept-info-label {
    font-size: 0.85rem;
    color: #666;
}
.dept-info-value {
    font-weight: 500;
}
.dept-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
}
.dept-section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.course-tag {
    display: inline-block;
    background: #e9ecef;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    margin: 3px;
}
.unassigned-tag {
    background: #f8d7da;
    color: #721c24;
}
.wizard-steps {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.wizard-step {
    flex: 1;
    min-width: 200px;
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
}
.wizard-step.active {
    border-color: #667eea;
    background: #f0f4ff;
}
.wizard-step-number {
    width: 32px;
    height: 32px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 10px;
}
.wizard-step-title {
    font-weight: 600;
    margin-bottom: 5px;
}
.wizard-step-desc {
    font-size: 0.85rem;
    color: #666;
}
.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}
.empty-state i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 15px;
}
</style>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-building"></i> Department Management</h1>
        <p>Create departments, assign deans, and group courses logically</p>
    </div>
</div>

<!-- Wizard Steps -->
<div class="wizard-steps">
    <div class="wizard-step <?php echo !$edit_dept_id ? 'active' : ''; ?>">
        <div class="wizard-step-number">1</div>
        <div class="wizard-step-title">Create Department</div>
        <div class="wizard-step-desc">Add new department name & code</div>
    </div>
    <div class="wizard-step <?php echo $edit_dept_id ? 'active' : ''; ?>">
        <div class="wizard-step-number">2</div>
        <div class="wizard-step-title">Assign Dean</div>
        <div class="wizard-step-desc">Link a dean to this department</div>
    </div>
    <div class="wizard-step">
        <div class="wizard-step-number">3</div>
        <div class="wizard-step-title">Group Courses</div>
        <div class="wizard-step-desc">Assign courses to this department</div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- STEP 1: Create Department Form -->
<?php if (!$edit_dept_id): ?>
<div class="dept-card" style="max-width: 600px;">
    <div class="dept-header">
        <div class="dept-title"><i class="fas fa-plus-circle"></i> Step 1: Create New Department</div>
    </div>
    <form method="POST" class="row g-3">
        <input type="hidden" name="action" value="create_department">
        <div class="col-md-6">
            <label class="form-label">Department Name</label>
            <input type="text" name="name" id="dept_name" class="form-control" placeholder="e.g., Engineering" required oninput="autoGenerateCode()">
        </div>
        <div class="col-md-4">
            <label class="form-label">Department Code</label>
            <input type="text" name="code" id="dept_code" class="form-control" placeholder="Auto-generated" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-plus"></i> Create
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- STEP 2 & 3: Edit Department (Assign Dean & Courses) -->
<?php if ($edit_dept_id && $edit_dept): ?>
<div class="dept-card">
    <div class="dept-header">
        <div>
            <span class="dept-title"><?php echo htmlspecialchars($edit_dept['name']); ?></span>
            <span class="dept-code"><?php echo htmlspecialchars($edit_dept['code']); ?></span>
        </div>
        <a href="dashboard.php?page=departments" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="row g-4">
        <!-- Assign Dean Section -->
        <div class="col-md-6">
            <div class="dept-section">
                <div class="dept-section-title">
                    <i class="fas fa-user-tie"></i> Step 2: Assign Dean
                </div>
                
                <?php
                $current_dean = $conn->query("
                    SELECT su.first_name, su.last_name 
                    FROM system_users su 
                    WHERE su.id = " . ($edit_dept['dean_id'] ?? 0)
                )->fetch_assoc();
                ?>
                
                <?php if ($current_dean && $edit_dept['dean_id']): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle"></i> 
                        <strong><?php echo htmlspecialchars($current_dean['first_name'] . ' ' . $current_dean['last_name']); ?></strong> 
                        is assigned as Dean
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="assign_dean">
                    <input type="hidden" name="department_id" value="<?php echo $edit_dept_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Select Dean</label>
                        <select name="dean_id" class="form-select" required>
                            <option value="">-- Select Dean --</option>
                            <?php
                            $available_deans->data_seek(0);
                            while ($dean = $available_deans->fetch_assoc()): 
                                // Skip if dean is already assigned to another department
                                if ($dean['department_id'] && $dean['department_id'] != $edit_dept_id) continue;
                            ?>
                                <option value="<?php echo $dean['id']; ?>" <?php echo ($edit_dept['dean_id'] == $dean['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dean['first_name'] . ' ' . $dean['last_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Assign Dean
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Assign Courses Section -->
        <div class="col-md-6">
            <div class="dept-section">
                <div class="dept-section-title">
                    <i class="fas fa-book"></i> Step 3: Group Courses
                </div>
                
                <?php
                $dept_course_count = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE department_id = $edit_dept_id")->fetch_assoc()['cnt'];
                ?>
                <div class="mb-2">
                    <strong><?php echo $dept_course_count; ?></strong> courses assigned
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="assign_courses">
                    <input type="hidden" name="department_id" value="<?php echo $edit_dept_id; ?>">
                    <div class="mb-3" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px;">
                        <?php foreach ($all_courses_arr as $c): ?>
                            <?php
                            $is_assigned = $conn->query("SELECT id FROM courses WHERE id = " . $c['id'] . " AND department_id = $edit_dept_id")->num_rows > 0;
                            $is_other_dept = $c['department_id'] && $c['department_id'] != $edit_dept_id;
                            ?>
                            <div class="form-check <?php echo $is_other_dept ? 'opacity-50' : ''; ?>">
                                <input class="form-check-input" type="checkbox" name="course_ids[]" 
                                    value="<?php echo $c['id']; ?>" 
                                    id="course_<?php echo $c['id']; ?>"
                                    <?php echo $is_assigned ? 'checked' : ''; ?>
                                    <?php echo $is_other_dept ? 'disabled' : ''; ?>>
                                <label class="form-check-label" for="course_<?php echo $c['id']; ?>">
                                    <strong><?php echo htmlspecialchars($c['code']); ?></strong> - <?php echo htmlspecialchars($c['name']); ?>
                                    <?php if ($is_other_dept): ?>
                                        <span class="text-danger">(assigned to other dept)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Save Courses
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Department -->
    <div class="mt-3 pt-3 border-top">
        <form method="POST" onsubmit="return confirm('Delete this department? Courses will become unassigned.');">
            <input type="hidden" name="action" value="delete_department">
            <input type="hidden" name="department_id" value="<?php echo $edit_dept_id; ?>">
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i> Delete Department
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Department List -->
<h5 class="mb-3"><i class="fas fa-list"></i> Existing Departments</h5>

<?php if ($departments->num_rows > 0): ?>
    <?php while ($dept = $departments->fetch_assoc()): ?>
    <?php
    $course_count = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE department_id = " . $dept['id'])->fetch_assoc()['cnt'];
    $has_dean = $dept['dean_id'] ? true : false;
    $is_complete = $has_dean && $course_count > 0;
    ?>
    <div class="dept-card">
        <div class="dept-header">
            <div>
                <span class="dept-title"><?php echo htmlspecialchars($dept['name']); ?></span>
                <span class="dept-code"><?php echo htmlspecialchars($dept['code']); ?></span>
                <span class="dept-status <?php echo $is_complete ? 'complete' : 'incomplete'; ?>">
                    <?php if ($is_complete): ?>
                        <i class="fas fa-check-circle"></i> Complete
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle"></i> Incomplete
                    <?php endif; ?>
                </span>
            </div>
            <div class="dept-actions">
                <a href="dashboard.php?page=departments&action=edit_dept&id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Manage
                </a>
            </div>
        </div>
        
        <div class="dept-info">
            <div class="dept-info-item">
                <span class="dept-info-label">Dean:</span>
                <span class="dept-info-value">
                    <?php if ($dept['first_name']): ?>
                        <?php echo htmlspecialchars($dept['first_name'] . ' ' . $dept['last_name']); ?>
                    <?php else: ?>
                        <span class="text-danger">Not Assigned</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="dept-info-item">
                <span class="dept-info-label">Courses:</span>
                <span class="dept-info-value"><?php echo $course_count; ?></span>
            </div>
        </div>
        
        <?php if ($course_count > 0): ?>
        <div class="dept-section">
            <div class="dept-section-title">Assigned Courses:</div>
            <?php
            $dept_courses = $conn->query("SELECT code, name FROM courses WHERE department_id = " . $dept['id'] . " ORDER BY code");
            while ($c = $dept_courses->fetch_assoc()): ?>
                <span class="course-tag"><?php echo htmlspecialchars($c['code']); ?></span>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <p>No departments yet. Create your first department above.</p>
    </div>
<?php endif; ?>

<!-- Ungrouped Courses -->
<?php if ($ungrouped_courses->num_rows > 0): ?>
<div class="dept-card" style="border-left: 4px solid #ffc107;">
    <div class="dept-header">
        <div class="dept-title" style="color: #856404;">
            <i class="fas fa-exclamation-triangle"></i> Ungrouped / Unassigned Courses
        </div>
    </div>
    <p class="text-muted">These courses don't belong to any department. Click "Manage" on a department above to assign these courses.</p>
    <div>
        <?php while ($course = $ungrouped_courses->fetch_assoc()): ?>
            <span class="course-tag unassigned-tag"><?php echo htmlspecialchars($course['code']); ?></span>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<script>
function autoGenerateCode() {
    const name = document.getElementById('dept_name').value;
    const words = name.trim().split(/\s+/);
    let code;
    if (words.length === 1) {
        code = words[0].substring(0, 4).toUpperCase();
    } else {
        code = words.map(w => w[0]).join('').toUpperCase().substring(0, 4);
    }
    document.getElementById('dept_code').value = code;
}
</script>
