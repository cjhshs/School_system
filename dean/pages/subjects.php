<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dean') {
    header('Location: login.php');
    exit;
}

$dean_id = $_SESSION['user_id'];
$dean = $conn->query("SELECT su.*, d.id as dept_id FROM system_users su LEFT JOIN departments d ON su.department_id = d.id WHERE su.id = $dean_id")->fetch_assoc();
$dept_id = $dean['department_id'] ?? 0;

$message = '';
$message_type = '';
$MAX_STUDENTS = 40;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_subject'])) {
        $subject_id = intval($_POST['subject_id']);
        $schedule = trim($_POST['schedule']);
        $room = trim($_POST['room']);
        $instructor = trim($_POST['instructor']);
        $capacity = intval($_POST['capacity']);
        $is_open = isset($_POST['is_open']) ? 1 : 0;
        
        // Validate capacity
        if ($capacity > $MAX_STUDENTS) {
            $message = "Maximum capacity is $MAX_STUDENTS students per subject!";
            $message_type = 'danger';
        } else {
            // Check room conflict (exclude current subject)
            if ($room && $schedule) {
                $room_check = $conn->query("SELECT id, subject_code FROM subjects WHERE room = '$room' AND schedule = '$schedule' AND id != $subject_id LIMIT 1");
                if ($room_check && $room_check->num_rows > 0) {
                    $conflict = $room_check->fetch_assoc();
                    $message = "Room conflict! '$room' is already scheduled for {$conflict['subject_code']} at $schedule";
                    $message_type = 'danger';
                }
            }
            
            // Check instructor conflict (exclude current subject)
            if ($instructor && $schedule && !$message) {
                $instructor_check = $conn->query("SELECT id, subject_code FROM subjects WHERE instructor = '$instructor' AND schedule = '$schedule' AND id != $subject_id LIMIT 1");
                if ($instructor_check && $instructor_check->num_rows > 0) {
                    $conflict = $instructor_check->fetch_assoc();
                    $message = "Instructor conflict! $instructor already has {$conflict['subject_code']} at $schedule";
                    $message_type = 'danger';
                }
            }
            
            if (!$message) {
                $stmt = $conn->prepare("UPDATE subjects SET schedule = ?, room = ?, instructor = ?, capacity = ?, is_open = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $schedule, $room, $instructor, $capacity, $is_open, $subject_id);
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
}

$subjects = $conn->query("SELECT s.*, c.name as course_name 
    FROM subjects s 
    LEFT JOIN courses c ON s.course_code = c.code 
    WHERE c.department_id = $dept_id
    ORDER BY s.subject_code");

$teachers = $conn->query("SELECT id, first_name, last_name FROM system_users WHERE role_id = 5 AND department_id = $dept_id AND is_active = 1");
$departments = $conn->query("SELECT * FROM departments WHERE id = $dept_id");
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
$edit_subject = $conn->query("SELECT * FROM subjects WHERE id = $edit_subject_id")->fetch_assoc();
$teachers = $conn->query("SELECT id, first_name, last_name FROM teachers WHERE is_active = 1");

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
                    <select name="instructor" id="instructorSelect" class="form-select" onchange="checkInstructorConflict()">
                        <option value="">Select Instructor</option>
                        <?php while ($t = $teachers->fetch_assoc()): ?>
                            <?php $fullName = $t['first_name'] . ' ' . $t['last_name']; ?>
                            <option value="<?php echo htmlspecialchars($fullName); ?>" <?php echo ($edit_subject['instructor'] == $fullName) ? 'selected' : ''; ?>>
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
                           value="<?php echo $edit_subject['capacity']; ?>" min="1" max="<?php echo $MAX_STUDENTS; ?>" required
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
                               <?php echo $edit_subject['is_open'] ? 'checked' : ''; ?>>
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

<div class="card">
    <div class="card-header bg-info text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Enrolled Students (<?php echo $enrolled_students->num_rows; ?>)</h5>
            <span class="badge bg-light text-dark"><?php echo $enrolled_count; ?>/<?php echo $edit_subject['capacity']; ?> enrolled</span>
        </div>
    </div>
    <div class="card-body">
        <?php if ($enrolled_students->num_rows > 0): ?>
            <table class="table table-striped">
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

<script>
document.querySelectorAll('.search-input').forEach(input => {
    input.addEventListener('input', function() {
        const tableId = this.getAttribute('data-table');
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
        
        const clearBtn = this.parentElement.querySelector('.search-clear');
        clearBtn.style.display = filter ? 'block' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No students enrolled in this subject.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Parse existing schedule to populate day/time fields
document.addEventListener('DOMContentLoaded', function() {
    const schedule = document.getElementById('scheduleOutput').value;
    if (schedule) {
        const match = schedule.match(/([A-Z]+)\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})/);
        if (match) {
            document.getElementById('daysSelect').value = match[1];
            document.getElementById('startTime').value = match[2];
            document.getElementById('endTime').value = match[3];
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
    
    if (!room || !schedule) {
        statusEl.innerHTML = '';
        return;
    }
    
    fetch('check_conflict.php?type=room&room=' + encodeURIComponent(room) + '&schedule=' + encodeURIComponent(schedule) + '&subject_id=' + subjectId)
        .then(r => r.json())
        .then(data => {
            if (data.conflict) {
                statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
                showConflict(data.message);
            } else {
                statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Room available</span>';
                hideConflict();
            }
        });
}

function checkInstructorConflict() {
    const instructor = document.getElementById('instructorSelect').value;
    const schedule = document.getElementById('scheduleOutput').value;
    const subjectId = <?php echo $edit_subject_id; ?>;
    const statusEl = document.getElementById('instructorStatus');
    
    if (!instructor || !schedule) {
        statusEl.innerHTML = '';
        return;
    }
    
    fetch('check_conflict.php?type=instructor&instructor=' + encodeURIComponent(instructor) + '&schedule=' + encodeURIComponent(schedule) + '&subject_id=' + subjectId)
        .then(r => r.json())
        .then(data => {
            if (data.conflict) {
                statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</span>';
                showConflict(data.message);
            } else {
                statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Instructor available</span>';
                hideConflict();
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

function showConflict(message) {
    document.getElementById('conflictMessage').textContent = message;
    document.getElementById('conflictAlert').classList.remove('d-none');
    document.getElementById('submitBtn').disabled = true;
}

function hideConflict() {
    const roomOk = document.getElementById('roomStatus').textContent.includes('available') || !document.getElementById('roomStatus').textContent;
    const instructorOk = document.getElementById('instructorStatus').textContent.includes('available') || !document.getElementById('instructorStatus').textContent;
    
    if (roomOk && instructorOk) {
        document.getElementById('conflictAlert').classList.add('d-none');
        document.getElementById('submitBtn').disabled = false;
    }
}
</script>

<?php else: ?>

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
                        $enrolled_count = $conn->query("SELECT COUNT(*) as cnt FROM student_subjects WHERE subject_id = " . $subject['id'] . " AND status = 'Enrolled'")->fetch_assoc()['cnt'];
                        $capacity_class = $enrolled_count >= $MAX_STUDENTS ? 'bg-danger' : ($enrolled_count >= $MAX_STUDENTS - 5 ? 'bg-warning' : 'bg-primary');
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($subject['description'] ?? ''); ?></td>
                            <td><?php echo $subject['schedule'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                            <td><?php echo $subject['room'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                            <td><?php echo $subject['instructor'] ?: '<span class="text-muted">TBA</span>'; ?></td>
                            <td>
                                <span class="badge <?php echo $capacity_class; ?>"><?php echo $enrolled_count; ?></span> / <?php echo $subject['capacity']; ?>
                            </td>
                            <td>
                                <?php if ($subject['is_open']): ?>
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
        <?php else: ?>
            <p class="text-center text-muted">No subjects found.</p>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
