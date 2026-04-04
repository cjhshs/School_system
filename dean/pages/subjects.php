<?php
require_once '../config.php';
require_once dirname(dirname(__DIR__)) . '/includes/pagination.php';
require_once dirname(dirname(__DIR__)) . '/includes/cache.php';

$per_page = 20;
$current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dean') {
    header('Location: login.php');
    exit;
}

$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT su.*, d.id as dept_id FROM system_users su LEFT JOIN departments d ON su.department_id = d.id WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

// DEBUG: Uncomment to debug
// echo "<pre>Dean ID: $dean_id | Dept ID: $dept_id | Dean data: "; print_r($dean); echo "</pre>";

$message = '';
$message_type = '';
$MAX_STUDENTS = 40;

// Handle AJAX conflict check
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_instructor_conflict') {
    header('Content-Type: application/json');
    $instructor_id = intval($_POST['instructor_id']);
    $schedule = trim($_POST['schedule']);
    $subject_id = intval($_POST['subject_id']);
    $check = $conn->prepare("SELECT s.subject_code FROM subjects s WHERE s.instructor_id = ? AND s.schedule = ? AND s.id != ? AND s.schedule != '' AND s.instructor_id IS NOT NULL LIMIT 1");
    $check->bind_param("isi", $instructor_id, $schedule, $subject_id);
    $check->execute();
    $conflict = $check->get_result()->fetch_assoc();
    if ($conflict) {
        $name = $conn->query("SELECT CONCAT(first_name, ' ', last_name) as n FROM system_users WHERE id = $instructor_id")->fetch_assoc()['n'];
        echo json_encode(['conflict' => true, 'message' => "$name is already teaching {$conflict['subject_code']} at $schedule"]);
    } else {
        echo json_encode(['conflict' => false]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_room_conflict') {
    header('Content-Type: application/json');
    $room = trim($_POST['room']);
    $schedule = trim($_POST['schedule']);
    $subject_id = intval($_POST['subject_id']);
    $check = $conn->prepare("SELECT subject_code FROM subjects WHERE room = ? AND schedule = ? AND id != ? AND schedule != '' LIMIT 1");
    $check->bind_param("ssi", $room, $schedule, $subject_id);
    $check->execute();
    $conflict = $check->get_result()->fetch_assoc();
    if ($conflict) {
        echo json_encode(['conflict' => true, 'message' => "Room '$room' is already scheduled for {$conflict['subject_code']} at $schedule"]);
    } else {
        echo json_encode(['conflict' => false]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_subject'])) {
        $subject_id = intval($_POST['subject_id']);
        $schedule = trim($_POST['schedule']);
        $room = trim($_POST['room']);
        $instructor_id = !empty($_POST['instructor_id']) ? intval($_POST['instructor_id']) : null;
        $capacity = intval($_POST['capacity']);
        $is_open = isset($_POST['is_open']) ? 1 : 0;
        
        // Validate capacity
        if ($capacity > $MAX_STUDENTS) {
            $message = "Maximum capacity is $MAX_STUDENTS students per subject!";
            $message_type = 'danger';
        } else {
            // Check room conflict (exclude current subject)
            if ($room && $schedule) {
                $room_check = $conn->prepare("SELECT id, subject_code FROM subjects WHERE room = ? AND schedule = ? AND id != ? LIMIT 1");
                $room_check->bind_param("ssi", $room, $schedule, $subject_id);
                $room_check->execute();
                $room_conflict = $room_check->get_result()->fetch_assoc();
                if ($room_conflict) {
                    $message = "Room conflict! '$room' is already scheduled for {$room_conflict['subject_code']} at $schedule";
                    $message_type = 'danger';
                }
            }
            
            // Check instructor conflict (exclude current subject)
            if ($instructor_id && $schedule && !$message) {
                $instructor_check = $conn->prepare("SELECT id, subject_code FROM subjects WHERE instructor_id = ? AND schedule = ? AND id != ? LIMIT 1");
                $instructor_check->bind_param("isi", $instructor_id, $schedule, $subject_id);
                $instructor_check->execute();
                $instructor_conflict = $instructor_check->get_result()->fetch_assoc();
                if ($instructor_conflict) {
                    $conflict_name = $conn->query("SELECT CONCAT(first_name, ' ', last_name) as name FROM system_users WHERE id = $instructor_id")->fetch_assoc()['name'];
                    $message = "Instructor conflict! $conflict_name is already teaching {$instructor_conflict['subject_code']} at $schedule";
                    $message_type = 'danger';
                }
            }
            
            if (!$message) {
                $stmt = $conn->prepare("UPDATE subjects SET schedule = ?, room = ?, instructor_id = ?, max_students = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssiiii", $schedule, $room, $instructor_id, $capacity, $is_open, $subject_id);
                $stmt->execute();
                $message = "Subject updated successfully!";
                $message_type = 'success';
            }
        }
    }
    
    if (isset($_POST['block_student'])) {
        $student_id = intval($_POST['student_id']);
        $subject_id = intval($_POST['subject_id']);
        $action = $_POST['action'];
        
        $stmt = $conn->prepare("UPDATE student_subjects SET status = ? WHERE student_id = ? AND subject_id = ?");
        $status = ($action == 'block') ? 'Blocked' : 'Enrolled';
        $stmt->bind_param("sii", $status, $student_id, $subject_id);
        $stmt->execute();
        
        $message = ($action == 'block') ? "Student blocked from subject!" : "Student unblocked!";
        $message_type = 'success';
    }
    
    if (isset($_POST['add_student_to_subject'])) {
        $student_id = intval($_POST['add_student_id']);
        $subject_id = intval($_POST['subject_id']);
        $school_year = trim($_POST['school_year']);
        $semester = trim($_POST['semester']);
        
        // Check if already enrolled
        $check = $conn->prepare("SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ?");
        $check->bind_param("ii", $student_id, $subject_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $message = "Student is already enrolled in this subject!";
            $message_type = 'warning';
        } else {
            // Check capacity
            $cap = $conn->query("SELECT max_students FROM subjects WHERE id = $subject_id")->fetch_assoc();
            $enrolled = $conn->query("SELECT COUNT(*) as c FROM student_subjects WHERE subject_id = $subject_id AND status = 'Enrolled'")->fetch_assoc()['c'];
            if ($enrolled >= $cap['max_students']) {
                $message = "Subject is at full capacity (" . $cap['max_students'] . ")!";
                $message_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id, school_year, semester, status) VALUES (?, ?, ?, ?, 'Enrolled')");
                $stmt->bind_param("iiss", $student_id, $subject_id, $school_year, $semester);
                if ($stmt->execute()) {
                    $message = "Student enrolled in subject successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error enrolling student: " . $stmt->error;
                    $message_type = 'danger';
                }
            }
        }
    }
}

$subjects = $conn->query("SELECT s.id, s.subject_code, s.description, s.units, s.schedule, s.room, s.instructor_id, s.max_students, s.is_active, c.name as course_name, CONCAT(su.first_name, ' ', su.last_name) as instructor_name
    FROM subjects s 
    LEFT JOIN courses c ON s.course_code = c.code 
    LEFT JOIN system_users su ON s.instructor_id = su.id
    WHERE c.department_id = $dept_id
    ORDER BY s.subject_code
    LIMIT {$per_page} OFFSET " . (($current_page - 1) * $per_page));

$total_subjects = $conn->query("SELECT COUNT(*) as c FROM subjects s JOIN courses c ON s.course_code = c.code WHERE c.department_id = $dept_id")->fetch_assoc()['c'];
$pagination = new Pagination($total_subjects, $per_page, $current_page);

// Pre-fetch enrollment counts to avoid N+1
$enrollment_counts = [];
$ec_result = $conn->query("SELECT subject_id, COUNT(*) as cnt FROM student_subjects WHERE status = 'Enrolled' GROUP BY subject_id");
while ($ec = $ec_result->fetch_assoc()) {
    $enrollment_counts[$ec['subject_id']] = $ec['cnt'];
}

$teachers = $conn->query("SELECT id, first_name, last_name FROM system_users WHERE role_id = 4 AND is_active = 1 ORDER BY first_name, last_name");
$departments = $conn->query("SELECT id, name, code FROM departments WHERE id = $dept_id");
?>

<h2><i class="fas fa-book me-2"></i>Manage Subjects</h2>
<p class="text-muted">Edit schedule, room, instructor, and capacity</p>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['edit_subject'])): ?>
<?php 
$edit_subject_id = intval($_GET['edit_subject']);
$edit_subject = $conn->query("SELECT s.*, CONCAT(su.first_name, ' ', su.last_name) as instructor_name FROM subjects s LEFT JOIN system_users su ON s.instructor_id = su.id WHERE s.id = $edit_subject_id")->fetch_assoc();
$teachers = $conn->query("SELECT id, first_name, last_name FROM system_users WHERE role_id = 4 AND is_active = 1 ORDER BY last_name, first_name");

$enrolled_count = $conn->query("SELECT COUNT(*) as cnt FROM student_subjects WHERE subject_id = $edit_subject_id AND status = 'Enrolled'")->fetch_assoc()['cnt'];

$stmt = $conn->prepare("
    SELECT ss.id as enrollment_id, ss.status, s.id as student_id, s.student_number, s.firstname, s.lastname, s.year_level
    FROM student_subjects ss
    JOIN students s ON ss.student_id = s.id
    WHERE ss.subject_id = ?
    ORDER BY s.lastname
");
$stmt->bind_param("i", $edit_subject_id);
$stmt->execute();
$enrolled_students = $stmt->get_result();
?>

<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Subject: <?php echo htmlspecialchars($edit_subject['subject_code']); ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" id="subjectForm">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="subject_id" value="<?php echo $edit_subject_id; ?>">
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Subject Code</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_subject['subject_code']); ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Description</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_subject['description'] ?? ''); ?>" disabled>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Units</label>
                    <input type="text" class="form-control" value="<?php echo $edit_subject['units']; ?>" disabled>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label>Day(s)</label>
                    <select name="days" id="daysSelect" class="form-select" onchange="updateSchedule()">
                        <option value="">Select Day</option>
                        <option value="MWF">MWF (Mon-Wed-Fri)</option>
                        <option value="TTH">TTH (Tue-Thu)</option>
                        <option value="MW">MW (Mon-Wed)</option>
                        <option value="WF">WF (Wed-Fri)</option>
                        <option value="M">Monday</option>
                        <option value="T">Tuesday</option>
                        <option value="W">Wednesday</option>
                        <option value="TH">Thursday</option>
                        <option value="F">Friday</option>
                        <option value="S">Saturday</option>
                        <option value="SUN">Sunday</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Start Time</label>
                    <input type="time" name="start_time" id="startTime" class="form-control" onchange="updateSchedule()">
                </div>
                <div class="col-md-2 mb-3">
                    <label>End Time</label>
                    <input type="time" name="end_time" id="endTime" class="form-control" onchange="updateSchedule()">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Room</label>
                    <input type="text" name="room" id="roomInput" class="form-control" 
                           value="<?php echo htmlspecialchars($edit_subject['room'] ?? ''); ?>" 
                           placeholder="e.g., Room 101" onblur="checkRoomConflict()">
                    <small id="roomStatus" class="text-muted"></small>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Instructor</label>
                    <select name="instructor_id" id="instructorSelect" class="form-select">
                        <option value="">Select Instructor</option>
                        <?php 
                        $teachers->data_seek(0);
                        while ($t = $teachers->fetch_assoc()): 
                        ?>
                            <?php $fullName = $t['first_name'] . ' ' . $t['last_name']; ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($edit_subject['instructor_id'] ?? 0) == $t['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($fullName); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small id="instructorStatus" class="text-muted"></small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Schedule (Auto-generated)</label>
                    <input type="text" name="schedule" id="scheduleOutput" class="form-control" 
                           value="<?php echo htmlspecialchars($edit_subject['schedule'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Capacity (Max: <?php echo $MAX_STUDENTS; ?>)</label>
                    <input type="number" name="capacity" id="capacityInput" class="form-control" 
                           value="<?php echo $edit_subject['max_students']; ?>" min="1" max="<?php echo $MAX_STUDENTS; ?>" required
                           oninput="checkCapacity()">
                    <small id="capacityStatus" class="text-muted"></small>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Current Enrolled</label>
                    <input type="text" class="form-control" value="<?php echo $enrolled_count; ?>" disabled>
                    <?php if ($enrolled_count > $MAX_STUDENTS): ?>
                        <small class="text-danger">Over limit!</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Status</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_open" class="form-check-input" id="is_open" 
                               <?php echo $edit_subject['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_open">Open for Enrollment</label>
                    </div>
                </div>
            </div>
            
            <div id="conflictAlert" class="alert alert-danger d-none">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="conflictMessage"></span>
            </div>
            
            <button type="submit" name="update_subject" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save me-1"></i>Update Subject
            </button>
            <a href="?page=subjects" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Enrolled Students (<?php echo $enrolled_students->num_rows; ?>)</h5>
            <span class="badge bg-light text-dark"><?php echo $enrolled_count; ?>/<?php echo $edit_subject['max_students']; ?> enrolled</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Add Student to Subject -->
        <div class="mb-3 p-3 bg-light rounded">
            <form method="POST" class="row g-2 align-items-end">
    <?php echo csrf_field(); ?>
                <input type="hidden" name="subject_id" value="<?php echo $edit_subject_id; ?>">
                <div class="col-md-5">
                    <label class="form-label small">Select Student</label>
                    <select name="add_student_id" class="form-select form-select-sm" required>
                        <option value="">-- Choose Student --</option>
                        <?php
                        $eligible = $conn->query("SELECT s.id, s.student_number, s.firstname, s.lastname, s.year_level, c.code as course_code 
                            FROM students s 
                            JOIN enrollments e ON e.student_id = s.id 
                            LEFT JOIN courses c ON s.course_id = c.id 
                            WHERE e.status = 'Confirmed' 
                            AND s.id NOT IN (SELECT student_id FROM student_subjects WHERE subject_id = $edit_subject_id)
                            ORDER BY s.lastname, s.firstname");
                        while ($es = $eligible->fetch_assoc()): ?>
                            <option value="<?php echo $es['id']; ?>"><?php echo htmlspecialchars($es['student_number'] . ' - ' . $es['lastname'] . ', ' . $es['firstname'] . ' (' . ($es['course_code'] ?? 'N/A') . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">School Year</label>
                    <input type="text" name="school_year" class="form-control form-control-sm" value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Semester</label>
                    <select name="semester" class="form-select form-select-sm">
                        <option value="1st">1st</option>
                        <option value="2nd">2nd</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_student_to_subject" class="btn btn-sm btn-success w-100">
                        <i class="fas fa-user-plus me-1"></i>Add
                    </button>
                </div>
            </form>
        </div>

        <?php if ($enrolled_students->num_rows > 0): ?>
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($student = $enrolled_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></td>
                            <td><?php echo $student['year_level']; ?></td>
                            <td>
                                <?php if ($student['status'] == 'Blocked'): ?>
                                    <span class="badge bg-danger">Blocked</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                    <input type="hidden" name="subject_id" value="<?php echo $edit_subject_id; ?>">
                                    <?php if ($student['status'] == 'Blocked'): ?>
                                        <input type="hidden" name="action" value="unblock">
                                        <button type="submit" name="block_student" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i>Unblock
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="block">
                                        <button type="submit" name="block_student" class="btn btn-sm btn-danger" onclick="return confirm('Block this student?')">
                                            <i class="fas fa-ban me-1"></i>Block
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No students enrolled in this subject yet. Use the form above to add students.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const schedule = document.getElementById('scheduleOutput');
    if (schedule && schedule.value) {
        const match = schedule.value.match(/([A-Z]+)\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/);
        if (match) {
            const daysEl = document.getElementById('daysSelect');
            if (daysEl) daysEl.value = match[1];
            const startEl = document.getElementById('startTime');
            if (startEl) startEl.value = match[2];
            const endEl = document.getElementById('endTime');
            if (endEl) endEl.value = match[3];
        }
    }
});

function updateSchedule() {
    const days = document.getElementById('daysSelect').value;
    const start = document.getElementById('startTime').value;
    const end = document.getElementById('endTime').value;
    let schedule = '';
    if (days && start && end) {
        schedule = days + ' ' + formatTime(start) + '-' + formatTime(end);
    } else if (days) {
        schedule = days;
    }
    document.getElementById('scheduleOutput').value = schedule;
    if (schedule) {
        checkRoomConflict();
        checkInstructorConflict();
    }
}

function formatTime(time) {
    if (!time) return '';
    const [h, m] = time.split(':');
    const hour = parseInt(h);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return hour12 + ':' + m + ' ' + ampm;
}

function checkRoomConflict() {
    const room = document.getElementById('roomInput').value.trim();
    const schedule = document.getElementById('scheduleOutput').value;
    const subjectId = <?php echo $edit_subject_id; ?>;
    const statusEl = document.getElementById('roomStatus');
    if (!room || !schedule) { statusEl.innerHTML = ''; return; }
    statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Checking...</span>';
    const formData = new FormData();
    formData.append('action', 'check_room_conflict');
    formData.append('room', room);
    formData.append('schedule', schedule);
    formData.append('subject_id', subjectId);
    fetch(window.location.href, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.conflict) {
                statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
            } else {
                statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Room available</span>';
            }
        });
}

function checkInstructorConflict() {
    const instructorId = document.getElementById('instructorSelect').value;
    const schedule = document.getElementById('scheduleOutput').value;
    const statusEl = document.getElementById('instructorStatus');
    if (!instructorId || !schedule) { statusEl.innerHTML = ''; return; }
    statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Checking...</span>';
    const formData = new FormData();
    formData.append('action', 'check_instructor_conflict');
    formData.append('instructor_id', instructorId);
    formData.append('schedule', schedule);
    formData.append('subject_id', <?php echo $edit_subject_id; ?>);
    fetch(window.location.href, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.conflict) {
                statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
            } else {
                statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Instructor available</span>';
            }
        });
}

function checkCapacity() {
    const capacity = parseInt(document.getElementById('capacityInput').value);
    const enrolled = <?php echo $enrolled_count; ?>;
    const statusEl = document.getElementById('capacityStatus');
    const max = <?php echo $MAX_STUDENTS; ?>;
    if (capacity > max) {
        statusEl.innerHTML = '<span class="text-danger">Max ' + max + ' students!</span>';
        document.getElementById('capacityInput').value = max;
    } else if (capacity < enrolled) {
        statusEl.innerHTML = '<span class="text-warning">Currently enrolled: ' + enrolled + '</span>';
    } else {
        statusEl.innerHTML = '<span class="text-success">' + (max - capacity) + ' slots available</span>';
    }
}
</script>

<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Subjects (<?php echo $subjects ? $subjects->num_rows : 0; ?>)</h5>
    </div>
    <div class="card-body">
        <?php if ($subjects && $subjects->num_rows > 0): ?>
            <div class="search-wrapper mb-3"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="subjectsTable" placeholder="Search subjects..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
            <table class="table table-striped datatable" id="subjectsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject Code</th>
                        <th>Description</th>
                        <th>Schedule</th>
                        <th>Room</th>
                        <th>Instructor</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; while ($subject = $subjects->fetch_assoc()): ?>
                        <?php
                        $enrolled_count = $enrollment_counts[$subject['id']] ?? 0;
                        $capacity_class = $enrolled_count >= $MAX_STUDENTS ? 'bg-danger' : ($enrolled_count >= $MAX_STUDENTS - 5 ? 'bg-warning' : 'bg-primary');
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($subject['description'] ?? ''); ?></td>
                            <td><?php echo $subject['schedule'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                            <td><?php echo $subject['room'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                            <td><?php echo htmlspecialchars($subject['instructor_name'] ?: 'TBA'); ?></td>
                            <td>
                                <span class="badge <?php echo $capacity_class; ?>"><?php echo $enrolled_count; ?></span> / <?php echo $subject['max_students']; ?>
                            </td>
                            <td>
                                <?php if ($subject['is_active']): ?>
                                    <span class="badge bg-success">Open</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?page=subjects&edit_subject=<?php echo $subject['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php echo $pagination->render('?page=subjects'); ?>
        <?php else: ?>
            <p class="text-center text-muted">No subjects found.</p>
        <?php endif; ?>
    </div>
</div>
