<?php
require_once '../config.php';

$message = '';

if (isset($_POST['add_course'])) {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $major = $_POST['major'];
    
    $stmt = $conn->prepare("INSERT INTO courses (code, name, major) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $name, $major);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Course added successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding course.</div>';
    }
}

if (isset($_POST['delete_course'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM courses WHERE id = $id");
    $message = '<div class="alert alert-success">Course deleted!</div>';
}

$courses = $conn->query("SELECT * FROM courses ORDER BY code, name");
?>

<div class="row">
    <div class="col-md-12">
        <h2>Manage Courses</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Add New Course</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Course Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g., BSIT">
                    </div>
                    <div class="mb-3">
                        <label>Course Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Bachelor of Science in IT">
                    </div>
                    <div class="mb-3">
                        <label>Major (Optional)</label>
                        <input type="text" name="major" class="form-control" placeholder="e.g., Web Development">
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>All Courses</h5>
            </div>
            <div class="card-body">
                <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" class="search-input" data-table="coursesTable" placeholder="Search..."><button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button></div>
                <table class="table table-striped" id="coursesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Major</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while($row = $courses->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $row['code']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['major'] ?: '-'; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_course" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this course?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5"><strong>Total Courses: <?php echo $counter - 1; ?></strong></td>
                        </tr>
                    </tfoot>
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
