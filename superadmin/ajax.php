<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

header('Content-Type: application/json');

if ($_POST['action'] === 'view_password') {
    $user_id = intval($_POST['user_id']);
    try {
        $stmt = $conn->prepare("SELECT password_encrypted FROM system_users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['password_encrypted'])) {
                $decrypted = decryptPassword($row['password_encrypted']);
                if ($decrypted !== false) {
                    echo json_encode(['success' => true, 'password' => $decrypted]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to decrypt password.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No encrypted password found. This user was created before encryption was enabled.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
