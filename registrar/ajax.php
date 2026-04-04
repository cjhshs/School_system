<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

header('Content-Type: application/json');

if ($_POST['action'] === 'view_student_password') {
    $student_id = intval($_POST['student_id']);
    try {
        $stmt = $conn->prepare("SELECT student_number, password_encrypted FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['password_encrypted'])) {
                $decrypted = decryptPassword($row['password_encrypted']);
                if ($decrypted !== false) {
                    echo json_encode(['success' => true, 'username' => $row['student_number'], 'password' => $decrypted]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to decrypt password.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No encrypted password found. Try resetting the password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} elseif ($_POST['action'] === 'reset_student_password') {
    $student_id = intval($_POST['student_id']);
    $new_password = trim($_POST['new_password']);
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }
    try {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $encrypted = encryptPassword($new_password);
        $stmt = $conn->prepare("UPDATE students SET password = ?, password_encrypted = ? WHERE id = ?");
        $stmt->bind_param("ssi", $hashed, $encrypted, $student_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reset password.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
