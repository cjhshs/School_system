<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'enrollment_system';

// Application constants
define('APP_NAME', 'CJLG University');
define('DEFAULT_PASSWORD', 'password123');

// Encryption key for reversible password storage (change this in production)
define('ENCRYPTION_KEY', 'CJLG_Enrollment_System_2025_SecretKey!@#$%');
define('ENCRYPTION_CIPHER', 'aes-256-cbc');

// Error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Set charset
$conn->set_charset("utf8");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Encrypt password (reversible) for admin viewing
function encryptPassword($plain_password) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPTION_CIPHER));
    $encrypted = openssl_encrypt($plain_password, ENCRYPTION_CIPHER, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Decrypt password (for admin viewing)
function decryptPassword($encrypted_data) {
    $data = base64_decode($encrypted_data);
    $parts = explode('::', $data);
    if (count($parts) !== 2) {
        return false;
    }
    return openssl_decrypt($parts[0], ENCRYPTION_CIPHER, ENCRYPTION_KEY, 0, $parts[1]);
}

// Log activity
function logActivity($conn, $user_id, $action, $details = '') {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// CSRF Protection
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf() {
    if (!isset($_POST['_csrf']) || !hash_equals(csrf_token(), $_POST['_csrf'])) {
        http_response_code(403);
        die('Invalid CSRF token. Refresh the page and try again.');
    }
}
?>