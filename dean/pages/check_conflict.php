<?php
require_once '../config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$schedule = $_GET['schedule'] ?? '';
$subject_id = intval($_GET['subject_id'] ?? 0);

$result = ['conflict' => false, 'message' => ''];

if ($type === 'room') {
    $room = trim($_GET['room'] ?? '');
    if ($room && $schedule) {
        $check = $conn->query("SELECT subject_code FROM subjects WHERE room = '$room' AND schedule = '$schedule' AND id != $subject_id LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $result['conflict'] = true;
            $result['message'] = "Room booked for {$row['subject_code']}";
        }
    }
} elseif ($type === 'instructor') {
    $instructor = trim($_GET['instructor'] ?? '');
    if ($instructor && $schedule) {
        $check = $conn->query("SELECT subject_code FROM subjects WHERE instructor = '$instructor' AND schedule = '$schedule' AND id != $subject_id LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $result['conflict'] = true;
            $result['message'] = "Instructor teaching {$row['subject_code']}";
        }
    }
}

echo json_encode($result);
