<?php
require_once '../config.php';

$message = '';

// CSV Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subjects.csv"');
    
    $subjects = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code FROM subjects s LEFT JOIN courses c ON s.course_code = c.code ORDER BY c.code, s.semester, s.subject_code");
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Code', 'Description', 'Units', 'Course', 'Year', 'Semester', 'Schedule', 'Room', 'Instructor', 'Max Students', 'Status']);
    
    while ($row = $subjects->fetch_assoc()) {
        fputcsv($output, [
            $row['subject_code'],
            $row['description'],
            $row['units'],
            $row['course_code'],
            $row['year_level'],
            $row['semester'],
            $row['schedule'],
            $row['room'],
            $row['instructor'],
            $row['max_students'],
            $row['is_active'] ? 'Active' : 'Inactive'
        ]);
    }
    fclose($output);
    exit;
}

// Handle form submissions
if (isset($_POST['add_subject'])) {
    $subject_code = $conn->real_escape_string($_POST['subject_code']);
    $description = $conn->real_escape_string($_POST['description']);
    $units = intval($_POST['units']);
    $course_code = $conn->real_escape_string($_POST['course_code']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $year_level = intval($_POST['year_level']);
    $schedule = $conn->real_escape_string($_POST['schedule']);
    $room = $conn->real_escape_string($_POST['room']);
    $instructor = $conn->real_escape_string($_POST['instructor']);
    $max_students = intval($_POST['max_students']);
    
    // Inline insert to avoid complex binding
    $sql = "INSERT INTO subjects (subject_code, description, units, course_code, year_level, semester, schedule, room, instructor, max_students, is_active) VALUES ('$subject_code', '$description', $units, '$course_code', $year_level, '$semester', '$schedule', '$room', '$instructor', $max_students, 1)";
    $conn->query($sql);
    
    if ($conn->affected_rows > 0) {
        $message = '<div class="alert alert-success">Subject added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding subject: ' . $conn->error . '</div>';
    }
}

if (isset($_POST['delete_subject'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM subjects WHERE id = $id");
    $message = '<div class="alert alert-success">Subject deleted!</div>';
}

if (isset($_POST['toggle_status'])) {
    $id = intval($_POST['id']);
    $current_status = $_POST['current_status'];
    $new_status = $current_status ? 0 : 1;
    $conn->query("UPDATE subjects SET is_active = $new_status WHERE id = $id");
    $message = '<div class="alert alert-success">Status updated!</div>';
}

// Get data
$subjects = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code FROM subjects s LEFT JOIN courses c ON s.course_code = c.code ORDER BY c.code, s.semester, s.subject_code");
$courses = $conn->query("SELECT DISTINCT code, name FROM courses ORDER BY code");

// Debug prints removed
?>

<div class="row">
    <div class="col-md-12">
        <h2>Manage Subjects</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Add New Subject</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-2">
                        <label>Subject Code *</label>
                        <input type="text" name="subject_code" class="form-control" required placeholder="e.g., MATH101">
                    </div>
                    <div class="mb-2">
                        <label>Description *</label>
                        <input type="text" name="description" class="form-control" required placeholder="e.g., College Mathematics">
                    </div>
                    <div class="mb-2">
                        <label>Units *</label>
                        <input type="number" name="units" class="form-control" required min="1" max="10" value="3">
                    </div>
                    <div class="mb-2">
                        <label>Course *</label>
                        <select name="course_code" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php while($c = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $c['code']; ?>"><?php echo $c['code']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Year Level *</label>
                        <select name="year_level" class="form-select" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Semester *</label>
                        <select name="semester" class="form-select" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Schedule</label>
                        <input type="text" name="schedule" class="form-control" placeholder="e.g., MWF 9:00-10:00">
                    </div>
                    <div class="mb-2">
                        <label>Room</label>
                        <input type="text" name="room" class="form-control" placeholder="e.g., Room 101">
                    </div>
                    <div class="mb-2">
                        <label>Instructor</label>
                        <input type="text" name="instructor" class="form-control" placeholder="e.g., Mr. Smith">
                    </div>
                    <div class="mb-2">
                        <label>Max Students</label>
                        <input type="number" name="max_students" class="form-control" value="30" min="1">
                    </div>
                    <button type="submit" name="add_subject" class="btn btn-primary w-100">Add Subject</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5>All Subjects</h5>
                <a href="?page=subjects&export=csv" class="btn btn-sm btn-success"><i class="fas fa-download"></i> Export CSV</a>
            </div>
            <div class="card-body">
                <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="subjectsTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <div class="table-responsive">
                    <table class="table table-striped" id="subjectsTable">
                        <thead>
                            <tr>
                                <th>#</th>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            while($row = $subjects->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><strong><?php echo $row['subject_code']; ?></strong></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><span class="badge bg-info"><?php echo $row['units']; ?></span></td>
                                <td><?php echo ($row['course_name'] ?? '') . ' (' . ($row['course_code'] ?? '-') . ')'; ?></td>
                                <td><?php echo $row['year_level']; ?></td>
                                <td><?php echo $row['semester']; ?></td>
                                <td><small><?php echo $row['schedule'] ?: '-'; ?></small></td>
                                <td><?php echo $row['room'] ?: '-'; ?></td>
                                <td><?php echo $row['instructor'] ?: '-'; ?></td>
                                <td><?php echo $row['max_students']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $row['is_active']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>" title="<?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-<?php echo $row['is_active'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_subject" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this subject?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="12"><strong>Total Subjects: <?php echo $counter - 1; ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
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
