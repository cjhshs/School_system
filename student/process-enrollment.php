<?php
require_once '../config.php';
require_once '../includes/validator.php';

function generateStudentNumber($conn) {
    $year = date('Y');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE student_number LIKE ?");
    $like = $year . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $year . str_pad($row['count'] + 1, 5, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $errors = Validator::errors([
        'firstname' => ['required', 'max' => 100],
        'lastname' => ['required', 'max' => 100],
        'email' => ['required', 'email'],
        'course' => ['required'],
        'contact_no' => ['phone'],
    ], $_POST);

    if (!empty($errors)) {
        $_SESSION['enrollment_errors'] = $errors;
        header('Location: register.php');
        exit;
    }

    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname']);
    $suffix = trim($_POST['suffix'] ?? '');
    $birthdate = trim($_POST['birthdate']);
    $age = intval($_POST['age']);
    $gender = trim($_POST['gender']);
    $civil_status = trim($_POST['civil_status']);
    $address = trim($_POST['address']);
    $region = trim($_POST['region']);
    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $barangay = trim($_POST['barangay'] ?? '');
    $zipcode = trim($_POST['zipcode']);
    $nationality = trim($_POST['nationality']);
    $contact_no = trim($_POST['contact_no']);
    $email = trim($_POST['email']);
    $religion = trim($_POST['religion']);
    $ethnicity = trim($_POST['ethnicity'] ?? '');
    $dialect = trim($_POST['dialect'] ?? '');
    $course = trim($_POST['course']);
    $major = trim($_POST['major'] ?? '');
    $year_level = intval($_POST['year_level']);
    $school_year = trim($_POST['school_year']);
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $guardian_relationship = trim($_POST['guardian_relationship'] ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');
    $guardian_email = trim($_POST['guardian_email'] ?? '');
    $guardian_address = trim($_POST['guardian_address'] ?? '');
    $previous_school = trim($_POST['previous_school'] ?? '');
    $previous_school_address = trim($_POST['previous_school_address'] ?? '');

    $student_number = generateStudentNumber($conn);

    $stmt = $conn->prepare("SELECT id FROM courses WHERE code = ? LIMIT 1");
    $stmt->bind_param("s", $course);
    $stmt->execute();
    $course_row = $stmt->get_result()->fetch_assoc();
    $course_id = $course_row ? $course_row['id'] : null;

    $full_address = "$barangay, $city, $province, $region";
    $enrollment_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO students (
        student_number, firstname, middle_name, lastname, suffix, birthdate, gender, civil_status,
        address, city, province, barangay, zipcode, phone, email,
        guardian_name, guardian_relationship, guardian_phone, guardian_email, guardian_address,
        course_id, year_level, enrollment_date, previous_school, previous_school_address
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssssssssssssssisisss",
        $student_number, $firstname, $middlename, $lastname, $suffix, $birthdate, $gender, $civil_status,
        $full_address, $city, $province, $barangay, $zipcode, $contact_no, $email,
        $guardian_name, $guardian_relationship, $guardian_phone, $guardian_email, $guardian_address,
        $course_id, $year_level, $enrollment_date, $previous_school, $previous_school_address
    );

    if ($stmt->execute()) {
        $student_id = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO enrollments (student_id, course_id, school_year, semester, status) VALUES (?, ?, ?, 1, 'Pending')");
        $stmt2->bind_param("iiss", $student_id, $course_id, $school_year);
        $stmt2->execute();

        $default_password = strtolower(str_replace(' ', '', $lastname)) . str_replace('-', '', $student_number);
        $hashed = password_hash($default_password, PASSWORD_DEFAULT);
        $encrypted = encryptPassword($default_password);
        $stmt3 = $conn->prepare("UPDATE students SET password = ?, password_encrypted = ? WHERE id = ?");
        $stmt3->bind_param("ssi", $hashed, $encrypted, $student_id);
        $stmt3->execute();

        logActivity($conn, $student_id, 'student_register', "New student registered: $student_number");

        header("Location: register.php?success=1&student_no=" . urlencode($student_number) . "&default_pass=" . urlencode($default_password));
        exit();
    } else {
        error_log("Enrollment insert failed: " . $stmt->error);
        echo "Error processing enrollment. Please try again.";
    }
}
?>
