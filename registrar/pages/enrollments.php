<?php
require_once '../config.php';

$message = '';

if (isset($_POST['update_status'])) {
    $id = (int) $_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    $update_sql = "UPDATE enrollments SET status = '$status' WHERE id = $id";
    if ($conn->query($update_sql)) {
        // Sync student enrollment_status and fees
        $enrollment = $conn->query("SELECT e.student_id, s.course_id, c.code as course_code FROM enrollments e JOIN students s ON e.student_id = s.id LEFT JOIN courses c ON s.course_id = c.id WHERE e.id = $id")->fetch_assoc();
        if ($enrollment) {
            $student_status = match($status) {
                'Confirmed' => 'Enrolled',
                'Cancelled' => 'Pending',
                default => 'Pending'
            };
            $sid = intval($enrollment['student_id']);
            $conn->query("UPDATE students SET enrollment_status = '$student_status' WHERE id = $sid");
            
            // Sync student fees from tuition_fees template
            if ($status === 'Confirmed' && !empty($enrollment['course_code'])) {
                $course_code = $enrollment['course_code'];
                $tuition = $conn->query("SELECT total_per_unit FROM tuition_fees WHERE course_code = '$course_code' LIMIT 1")->fetch_assoc();
                if ($tuition) {
                    $total = floatval($tuition['total_per_unit']);
                    $check = $conn->query("SELECT id FROM student_fees WHERE student_id = $sid AND fee_name = 'Tuition Fee'");
                    if ($check->num_rows > 0) {
                        $row = $check->fetch_assoc();
                        $conn->query("UPDATE student_fees SET amount = $total WHERE id = " . $row['id']);
                    } else {
                        $conn->query("INSERT INTO student_fees (student_id, fee_name, amount, is_paid) VALUES ($sid, 'Tuition Fee', $total, 0)");
                    }
                }
            }
        }
        $message = '<div class="alert alert-success">Status updated!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating status: ' . htmlspecialchars($conn->error) . '</div>';
    }
}

if (isset($_POST['delete_enrollment'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM enrollments WHERE id = $id");
    $message = '<div class="alert alert-success">Enrollment deleted!</div>';
}

$enrollments = $conn->query("SELECT e.*, s.firstname, s.lastname, s.student_number, c.name as course_name
    FROM enrollments e 
    LEFT JOIN students s ON e.student_id = s.id
    LEFT JOIN courses c ON e.course_id = c.id
    ORDER BY e.created_at DESC");
?>

<div class="row">
    <div class="col-md-12">
        <h2>Manage Enrollments</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>All Enrollments</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="enrollmentsTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <table class="table table-striped" id="enrollmentsTable">
                    <thead>
                        <tr>
                            <th>Student No</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $enrollments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($row['course_name'] ?? '-'); ?></td>
                            <td><?php echo $row['year_level'] ?? '-'; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="status" class="form-select form-select-sm" style="width:auto;display:inline;" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo ($row['status'] ?? 'Pending') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Confirmed" <?php echo $row['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Cancelled" <?php echo $row['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '-'; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_enrollment" class="btn btn-sm btn-danger" onclick="return confirm('Delete this enrollment?')">Delete</button>
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
$(document).ready(function() {
    $('#enrollmentsTable').DataTable();
});

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
