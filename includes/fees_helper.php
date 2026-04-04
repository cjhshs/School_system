<?php
function syncStudentFees($conn, $course_code, $total) {
    $stmt = $conn->prepare("
        SELECT s.id FROM students s 
        JOIN enrollments e ON e.student_id = s.id 
        WHERE s.course_id = (SELECT id FROM courses WHERE code = ?) 
        AND e.status = 'Confirmed'
    ");
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($stu = $result->fetch_assoc()) {
        $sid = $stu['id'];
        $check = $conn->prepare("SELECT id FROM student_fees WHERE student_id = ? AND fee_name = 'Tuition Fee'");
        $check->bind_param("i", $sid);
        $check->execute();
        $exists = $check->get_result();
        
        if ($exists->num_rows > 0) {
            $row = $exists->fetch_assoc();
            $upd = $conn->prepare("UPDATE student_fees SET amount = ? WHERE id = ?");
            $upd->bind_param("di", $total, $row['id']);
            $upd->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO student_fees (student_id, fee_name, amount, is_paid) VALUES (?, 'Tuition Fee', ?, 0)");
            $ins->bind_param("id", $sid, $total);
            $ins->execute();
        }
    }
}
