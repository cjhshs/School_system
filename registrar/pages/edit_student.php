<?php
require_once '../config.php';

$message = '';
$error = '';

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">No student selected.</div>';
    return;
}

$student_id = intval($_GET['id']);
$student = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code 
    FROM students s 
    LEFT JOIN courses c ON s.course_id = c.id 
    WHERE s.id = $student_id")->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    return;
}

$courses = $conn->query("SELECT * FROM courses ORDER BY code");

if (isset($_POST['update_student'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course_id = intval($_POST['course_id']);
    $year_level = intval($_POST['year_level']);
    
    $conn->query("UPDATE students SET 
        firstname = '$firstname',
        lastname = '$lastname',
        email = '$email',
        phone = '$phone',
        course_id = $course_id,
        year_level = $year_level
        WHERE id = $student_id");
    
    $message = '<div class="alert alert-success">Student updated successfully!</div>';
    $student = $conn->query("SELECT s.*, c.name as course_name, c.code as course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.id = $student_id")->fetch_assoc();
}
?>

<div class="row">
    <div class="col-md-12">
        <a href="dashboard.php?page=students" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
        <h2>Edit Student</h2>
        <?php echo $message; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Student Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Student Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_number']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($student['firstname'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($student['lastname'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Year Level</label>
                                <select name="year_level" class="form-select">
                                    <option value="1" <?php echo ($student['year_level'] ?? 1) == 1 ? 'selected' : ''; ?>>1st Year</option>
                                    <option value="2" <?php echo ($student['year_level'] ?? 1) == 2 ? 'selected' : ''; ?>>2nd Year</option>
                                    <option value="3" <?php echo ($student['year_level'] ?? 1) == 3 ? 'selected' : ''; ?>>3rd Year</option>
                                    <option value="4" <?php echo ($student['year_level'] ?? 1) == 4 ? 'selected' : ''; ?>>4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $student['course_id'] == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_student" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
