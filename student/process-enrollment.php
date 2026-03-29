<?php
require_once '../config.php';

// Generate student number
function generateStudentNumber($conn) {
    $year = date('Y');
    $sql = "SELECT COUNT(*) as count FROM students WHERE student_number LIKE '$year%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    return $year . str_pad($count, 5, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $middlename = $conn->real_escape_string($_POST['middlename'] ?? '');
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $suffix = $conn->real_escape_string($_POST['suffix'] ?? '');
    $birthdate = $conn->real_escape_string($_POST['birthdate']);
    $age = intval($_POST['age']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $address = $conn->real_escape_string($_POST['address']);
    $region = $conn->real_escape_string($_POST['region']);
    $province = $conn->real_escape_string($_POST['province']);
    $city = $conn->real_escape_string($_POST['city']);
    $barangay = $conn->real_escape_string($_POST['barangay'] ?? '');
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);
    $email = $conn->real_escape_string($_POST['email']);
    $religion = $conn->real_escape_string($_POST['religion']);
    $ethnicity = $conn->real_escape_string($_POST['ethnicity'] ?? '');
    $dialect = $conn->real_escape_string($_POST['dialect'] ?? '');
    $course = $conn->real_escape_string($_POST['course']);
    $major = $conn->real_escape_string($_POST['major'] ?? '');
    $year_level = intval($_POST['year_level']);
    $school_year = $conn->real_escape_string($_POST['school_year']);
    
    // Guardian info
    $guardian_name = $conn->real_escape_string($_POST['guardian_name'] ?? '');
    $guardian_relationship = $conn->real_escape_string($_POST['guardian_relationship'] ?? '');
    $guardian_phone = $conn->real_escape_string($_POST['guardian_phone'] ?? '');
    $guardian_email = $conn->real_escape_string($_POST['guardian_email'] ?? '');
    $guardian_address = $conn->real_escape_string($_POST['guardian_address'] ?? '');
    
    // Previous school
    $previous_school = $conn->real_escape_string($_POST['previous_school'] ?? '');
    $previous_school_address = $conn->real_escape_string($_POST['previous_school_address'] ?? '');
    
    // Generate student number
    $student_number = generateStudentNumber($conn);
    
    // Get course_id from course code
    $course_result = $conn->query("SELECT id FROM courses WHERE code = '$course' LIMIT 1");
    $course_row = $course_result->fetch_assoc();
    $course_id = $course_row ? $course_row['id'] : null;
    
    // Insert into database
    $full_address = "$barangay, $city, $province, $region";
    
    $enrollment_date = date('Y-m-d');
    
    $sql = "INSERT INTO students (
        student_number, firstname, middle_name, lastname, suffix, birthdate, gender, civil_status,
        address, city, province, barangay, zipcode, phone, email, 
        guardian_name, guardian_relationship, guardian_phone, guardian_email, guardian_address,
        course_id, year_level, enrollment_date, previous_school, previous_school_address
    ) VALUES (
        '$student_number', '$firstname', '$middlename', '$lastname', '$suffix', '$birthdate', '$gender', '$civil_status',
        '$full_address', '$city', '$province', '$barangay', '$zipcode', '$contact_no', '$email',
        '$guardian_name', '$guardian_relationship', '$guardian_phone', '$guardian_email', '$guardian_address',
        " . ($course_id ? $course_id : "NULL") . ", $year_level, '$enrollment_date', '$previous_school', '$previous_school_address'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $student_id = $conn->insert_id;
        
        // Create enrollment record with Pending status by default
        $sql_enroll = "INSERT INTO enrollments (student_id, course_id, school_year, semester, status) 
                       VALUES ($student_id, " . ($course_id ? $course_id : "NULL") . ", '$school_year', 1, 'Pending')";
        $conn->query($sql_enroll);
        
        // Set default password
        $default_password = strtolower(str_replace(' ', '', $lastname)) . str_replace('-', '', $student_number);
        $conn->query("UPDATE students SET password = '$default_password' WHERE id = $student_id");
        
        header("Location: register.php?success=1&student_no=" . urlencode($student_number) . "&default_pass=" . urlencode($default_password));
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
