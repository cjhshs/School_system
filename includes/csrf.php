<?php
// CSRF Protection System
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
        die('Invalid CSRF token.');
    }
}

function verify_csrf_ajax() {
    if (!isset($_POST['_csrf']) || !hash_equals(csrf_token(), $_POST['_csrf'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }
}
