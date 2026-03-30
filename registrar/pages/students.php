<?php
require_once '../config.php';

$message = '';

if (isset($_POST['delete_student'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM enrollments WHERE student_id = $id");
    $conn->query("DELETE FROM students WHERE id = $id");
    $message = '<div class="alert alert-success">Student deleted!</div>';
}

if (isset($_POST['enroll_student'])) {
    $id = $_POST['id'];
    $school_year = date('Y') . '-' . (date('Y') + 1);
    $check = $conn->query("SELECT id FROM enrollments WHERE student_id = $id");
    if ($check->num_rows == 0) {
        // Enroll student with Pending status by default
        $conn->query("INSERT INTO enrollments (student_id, school_year, semester, status) VALUES ($id, '$school_year', 1, 'Pending')");
        $message = '<div class="alert alert-success">Student enrolled!</div>';
    } else {
        $message = '<div class="alert alert-warning">Student already enrolled!</div>';
    }
}

$students = $conn->query("SELECT s.*, c.code as course_code FROM students s LEFT JOIN courses c ON s.course_id = c.id ORDER BY s.lastname, s.firstname");
?>

<div class="row">
    <div class="col-md-12">
        <h2>Manage Students</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>All Students</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="studentsTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <table class="table table-striped" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Enrolled</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students->data_seek(0);
                        while($row = $students->fetch_assoc()): 
                            $enrolled = $conn->query("SELECT id, status FROM enrollments WHERE student_id = " . $row['id'])->fetch_assoc();
                        ?>
                        <tr>
                            <td><?php echo $row['student_number']; ?></td>
                            <td><?php echo ($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? ''); ?></td>
                            <td><?php echo $row['course_code'] ?? '-'; ?></td>
                            <td><?php echo $row['year_level']; ?></td>
                            <td><?php echo $row['email'] ?? '-'; ?></td>
                            <td><?php echo $row['phone'] ?? '-'; ?></td>
                            <td>
                                <?php if($enrolled): ?>
                                    <span class="badge bg-<?php echo $enrolled['status'] == 'Confirmed' ? 'success' : 'warning'; ?>"><?php echo $enrolled['status']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="dashboard.php?page=edit_student&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                                <?php if(!$enrolled): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="enroll_student" class="btn btn-sm btn-success">Enroll</button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_student" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
