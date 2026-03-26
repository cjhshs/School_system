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

if ($SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $middlename = $conn->real_escape_string($_POST['middlename'] ?? '');
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $birthdate = $conn->real_escape_string($_POST['birthdate']);
    $age = intval($_POST['age']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $address = $conn->real_escape_string($_POST['address']);
    $region = $conn->real_escape_string($_POST['region']);
    $province = $conn->real_escape_string($_POST['province']);
    $city = $conn->real_escape_string($_POST['city']);
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
    
    // Generate student number
    $student_number = generateStudentNumber($conn);
    
    // Insert into database
    $sql = "INSERT INTO students (
        student_number, firstname, middlename, lastname, birthdate, gender,
        civil_status, address, region, province, city, zipcode, nationality,
        contact_no, email, religion, ethnicity, dialect, course_code, major, year_level, school_year
    ) VALUES (
        '$student_number', '$firstname', '$middlename', '$lastname', '$birthdate', '$gender',
        '$civil_status', '$address', '$region', '$province', '$city', '$zipcode', '$nationality',
        '$contact_no', '$email', '$religion', '$ethnicity', '$dialect', '$course', '$major', $year_level, '$school_year'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $student_id = $conn->insert_id;
        
        // Create enrollment record with Pending status by default
        $sql_enroll = "INSERT INTO enrollments (student_id, school_year, semester, status) 
                       VALUES ($student_id, '$school_year', 1, 'Pending')";
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
