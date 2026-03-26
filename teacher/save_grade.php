<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $prelim = $_POST['prelim'] !== '' ? $_POST['prelim'] : null;
    $midterm = $_POST['midterm'] !== '' ? $_POST['midterm'] : null;
    $final_exam = $_POST['final_exam'] !== '' ? $_POST['final_exam'] : null;
    $semester = $_POST['semester'] ?: '1st Semester';
    $school_year = $_POST['school_year'] ?: '2025-2026';
    
    $final_grade = null;
    $remarks = 'Incomplete';
    
    if ($prelim !== null && $midterm !== null && $final_exam !== null) {
        $final_grade = ($prelim + $midterm + $final_exam) / 3;
        
        $passing_grade = 75;
        $dept_result = $conn->query("SELECT passing_grade FROM departments LIMIT 1");
        if ($dept_result && $dept_result->num_rows > 0) {
            $passing_grade = $dept_result->fetch_assoc()['passing_grade'];
        }
        
        $remarks = $final_grade >= $passing_grade ? 'Passed' : 'Failed';
    }
    
    $check = $conn->query("SELECT id FROM grades WHERE student_id = $student_id AND subject_id = $subject_id AND semester = '$semester' AND school_year = '$school_year'");
    
    if ($check->num_rows > 0) {
        $sql = "UPDATE grades SET prelim = " . ($prelim !== null ? $prelim : "NULL") . ", 
                midterm = " . ($midterm !== null ? $midterm : "NULL") . ", 
                final_exam = " . ($final_exam !== null ? $final_exam : "NULL") . ", 
                final_grade = " . ($final_grade !== null ? $final_grade : "NULL") . ", 
                remarks = '$remarks', updated_at = NOW() 
                WHERE student_id = $student_id AND subject_id = $subject_id AND semester = '$semester' AND school_year = '$school_year'";
    } else {
        $sql = "INSERT INTO grades (student_id, subject_id, prelim, midterm, final_exam, final_grade, remarks, semester, school_year, status)
                VALUES ($student_id, $subject_id, " . ($prelim !== null ? $prelim : "NULL") . ", " . ($midterm !== null ? $midterm : "NULL") . ", " . ($final_exam !== null ? $final_exam : "NULL") . ", " . ($final_grade !== null ? $final_grade : "NULL") . ", '$remarks', '$semester', '$school_year', 'Draft')";
    }
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'final_grade' => $final_grade, 'remarks' => $remarks]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
