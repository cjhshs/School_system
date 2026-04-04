<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $subject_id = intval($_POST['subject_id']);
    $prelim = $_POST['prelim'] !== '' ? floatval($_POST['prelim']) : null;
    $midterm = $_POST['midterm'] !== '' ? floatval($_POST['midterm']) : null;
    $final_exam = $_POST['final_exam'] !== '' ? floatval($_POST['final_exam']) : null;
    $teacher_id = intval($_SESSION['user_id']);
    
    // Get subject info for semester
    $subj = $conn->query("SELECT semester FROM subjects WHERE id = $subject_id")->fetch_assoc();
    $semester = $subj['semester'] ?? '1st';
    $school_year = date('Y') . '-' . (date('Y') + 1);
    
    if (empty($semester)) $semester = '1st';
    
    $final_grade = null;
    $remarks = 'Incomplete';
    
    $dept_result = $conn->query("SELECT AVG(passing_grade) as avg_grade FROM departments");
    if ($dept_result && $dept_result->num_rows > 0) {
        $passing_grade = floatval($dept_result->fetch_assoc()['avg_grade']);
    } else {
        $passing_grade = 75;
    }
    
    if ($prelim !== null && $midterm !== null && $final_exam !== null) {
        $final_grade = round(($prelim + $midterm + $final_exam) / 3, 2);
        $remarks = $final_grade >= $passing_grade ? 'Passed' : 'Failed';
    }
    
    $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND subject_id = ? AND semester = ? AND school_year = ?");
    $check->bind_param("iiss", $student_id, $subject_id, $semester, $school_year);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE grades SET prelim = ?, midterm = ?, final_exam = ?, final_grade = ?, remarks = ?, updated_at = NOW() WHERE student_id = ? AND subject_id = ? AND semester = ? AND school_year = ?");
        $stmt->bind_param("ddddiiiss", $prelim, $midterm, $final_exam, $final_grade, $remarks, $student_id, $subject_id, $semester, $school_year);
    } else {
        $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, teacher_id, prelim, midterm, final_exam, final_grade, remarks, semester, school_year, grade_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft')");
        $stmt->bind_param("iisddsssss", $student_id, $subject_id, $teacher_id, $prelim, $midterm, $final_exam, $final_grade, $remarks, $semester, $school_year);
    }
    
    if ($stmt->execute()) {
        logActivity($conn, $teacher_id, 'save_grade', "Saved grade for student $student_id, subject $subject_id");
        echo json_encode(['success' => true, 'final_grade' => $final_grade, 'remarks' => $remarks]);
    } else {
        error_log("Save grade failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to save grade: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
