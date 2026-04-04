<?php
require_once '../config.php';

$message = '';
$message_type = '';

// Handle opening/activating a subject
if (isset($_POST['open_subject'])) {
    $id = intval($_POST['subject_id']);
    $schedule = trim($_POST['schedule']);
    $room = trim($_POST['room']);
    $max_students = intval($_POST['max_students']);
    $semester = trim($_POST['semester']);
    $year_level = intval($_POST['year_level']);
    
    $stmt = $conn->prepare("UPDATE subjects SET schedule = ?, room = ?, max_students = ?, semester = ?, year_level = ?, is_active = 1 WHERE id = ?");
    $stmt->bind_param("ssiisi", $schedule, $room, $max_students, $semester, $year_level, $id);
    if ($stmt->execute()) {
        $message = "Subject opened successfully!";
        $message_type = 'success';
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = 'danger';
    }
}

// Handle closing/deactivating a subject
if (isset($_POST['close_subject'])) {
    $id = intval($_POST['subject_id']);
    $conn->query("UPDATE subjects SET is_active = 0 WHERE id = $id");
    $message = "Subject closed.";
    $message_type = 'warning';
}

// Get all subjects with course and instructor info
$filter_course = isset($_GET['course']) ? $_GET['course'] : '';
$filter_sem = isset($_GET['semester']) ? $_GET['semester'] : '';
$filter_year = isset($_GET['year_level']) ? intval($_GET['year_level']) : 0;

$where = [];
$params = [];
$types = '';

if ($filter_course) {
    $where[] = "s.course_code = ?";
    $params[] = $filter_course;
    $types .= 's';
}
if ($filter_sem) {
    $where[] = "s.semester = ?";
    $params[] = $filter_sem;
    $types .= 's';
}
if ($filter_year) {
    $where[] = "s.year_level = ?";
    $params[] = $filter_year;
    $types .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$subjects_sql = "SELECT s.id, s.subject_code, s.description, s.units, s.course_code, s.department_id, 
    s.year_level, s.semester, s.schedule, s.room, s.instructor_id, s.max_students, s.is_active,
    c.name as course_name,
    CONCAT(u.first_name, ' ', u.last_name) as instructor_name
    FROM subjects s 
    LEFT JOIN courses c ON s.course_code = c.code 
    LEFT JOIN system_users u ON s.instructor_id = u.id
    $where_sql
    ORDER BY c.code, s.semester, s.subject_code";

if ($where) {
    $stmt = $conn->prepare($subjects_sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $subjects = $stmt->get_result();
} else {
    $subjects = $conn->query($subjects_sql);
}

// Get counts
$total = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$active = $conn->query("SELECT COUNT(*) as c FROM subjects WHERE is_active = 1")->fetch_assoc()['c'];
$inactive = $conn->query("SELECT COUNT(*) as c FROM subjects WHERE is_active = 0")->fetch_assoc()['c'];
$assigned = $conn->query("SELECT COUNT(*) as c FROM subjects WHERE instructor_id IS NOT NULL")->fetch_assoc()['c'];

// Get all courses for filter
$courses = $conn->query("SELECT DISTINCT code, name FROM courses ORDER BY code");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-book me-2"></i>Subject Management</h1>
        <p>Open subjects, assign schedules, and manage enrollment</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?php echo $total; ?></h3>
                <p class="text-muted mb-0">Total Subjects</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?php echo $active; ?></h3>
                <p class="text-muted mb-0">Open</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning"><?php echo $assigned; ?></h3>
                <p class="text-muted mb-0">With Instructor</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-secondary"><?php echo $inactive; ?></h3>
                <p class="text-muted mb-0">Closed</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="subjects">
            <div class="col-md-3">
                <label class="form-label small">Course</label>
                <select name="course" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php $courses->data_seek(0); while($c = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $c['code']; ?>" <?php echo $filter_course === $c['code'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Semester</label>
                <select name="semester" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1st" <?php echo $filter_sem === '1st' ? 'selected' : ''; ?>>1st</option>
                    <option value="2nd" <?php echo $filter_sem === '2nd' ? 'selected' : ''; ?>>2nd</option>
                    <option value="Summer" <?php echo $filter_sem === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Year Level</label>
                <select name="year_level" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" <?php echo $filter_year === 1 ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2" <?php echo $filter_year === 2 ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3" <?php echo $filter_year === 3 ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4" <?php echo $filter_year === 4 ? 'selected' : ''; ?>>4th Year</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="?page=subjects" class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-times me-1"></i>Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Subjects Table -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Subjects</h5>
    </div>
    <div class="card-body">
        <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="subjectsTable" placeholder="Search subjects..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="subjectsTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Units</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Sem</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Instructor</th>
                        <th>Cap</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $subjects->fetch_assoc()): ?>
                    <tr class="<?php echo $row['is_active'] ? '' : 'table-secondary'; ?>">
                        <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><span class="badge bg-info"><?php echo $row['units']; ?></span></td>
                        <td><small><?php echo htmlspecialchars($row['course_code']); ?></small></td>
                        <td><?php echo $row['year_level']; ?></td>
                        <td><?php echo $row['semester']; ?></td>
                        <td><small><?php echo $row['schedule'] ?: '<span class="text-muted">TBA</span>'; ?></small></td>
                        <td><small><?php echo $row['room'] ?: '<span class="text-muted">TBA</span>'; ?></small></td>
                        <td>
                            <?php if ($row['instructor_name']): ?>
                                <span class="text-success"><i class="fas fa-user-check me-1"></i><?php echo htmlspecialchars($row['instructor_name']); ?></span>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-user-slash me-1"></i>Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['max_students']; ?></td>
                        <td>
                            <?php if ($row['is_active']): ?>
                                <span class="badge bg-success">Open</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Closed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Open/Edit Button -->
                            <button type="button" class="btn btn-sm btn-<?php echo $row['is_active'] ? 'warning' : 'success'; ?>" 
                                data-bs-toggle="modal" data-bs-target="#openModal<?php echo $row['id']; ?>" 
                                title="<?php echo $row['is_active'] ? 'Edit' : 'Open'; ?>">
                                <i class="fas fa-<?php echo $row['is_active'] ? 'edit' : 'door-open'; ?>"></i>
                            </button>
                            
                            <?php if ($row['is_active']): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Close this subject?');">
                                <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="close_subject" class="btn btn-sm btn-outline-danger" title="Close">
                                    <i class="fas fa-door-closed"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Open/Edit Modal -->
                    <div class="modal fade" id="openModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header bg-<?php echo $row['is_active'] ? 'warning' : 'success'; ?> text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-<?php echo $row['is_active'] ? 'edit' : 'door-open'; ?> me-2"></i>
                                            <?php echo $row['is_active'] ? 'Edit' : 'Open'; ?>: <?php echo htmlspecialchars($row['subject_code'] . ' - ' . $row['description']); ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Schedule</label>
                                            <input type="text" name="schedule" class="form-control" 
                                                value="<?php echo htmlspecialchars($row['schedule'] ?? ''); ?>" 
                                                placeholder="e.g., MWF 8:00-9:00">
                                            <small class="text-muted">Format: Days Time-End (e.g., MWF 8:00-9:00, TTH 10:00-11:30)</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Room</label>
                                            <input type="text" name="room" class="form-control" 
                                                value="<?php echo htmlspecialchars($row['room'] ?? ''); ?>" 
                                                placeholder="e.g., Room 101">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Year Level</label>
                                                <select name="year_level" class="form-select">
                                                    <?php for ($y = 1; $y <= 4; $y++): ?>
                                                        <option value="<?php echo $y; ?>" <?php echo $row['year_level'] == $y ? 'selected' : ''; ?>><?php echo $y; ?><?php echo $y == 1 ? 'st' : ($y == 2 ? 'nd' : ($y == 3 ? 'rd' : 'th')); ?> Year</option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Semester</label>
                                                <select name="semester" class="form-select">
                                                    <option value="1st" <?php echo $row['semester'] === '1st' ? 'selected' : ''; ?>>1st</option>
                                                    <option value="2nd" <?php echo $row['semester'] === '2nd' ? 'selected' : ''; ?>>2nd</option>
                                                    <option value="Summer" <?php echo $row['semester'] === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Capacity</label>
                                                <input type="number" name="max_students" class="form-control" 
                                                    value="<?php echo $row['max_students'] ?? 40; ?>" min="1" max="100">
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info small mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Note:</strong> After opening, the Dean will assign an instructor and enroll students.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="open_subject" class="btn btn-<?php echo $row['is_active'] ? 'warning' : 'success'; ?>">
                                            <i class="fas fa-<?php echo $row['is_active'] ? 'save' : 'door-open'; ?> me-1"></i>
                                            <?php echo $row['is_active'] ? 'Update' : 'Open Subject'; ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
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
