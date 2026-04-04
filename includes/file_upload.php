<?php
// File Upload Validator
class FileUpload {
    private $allowed_types = [];
    private $max_size = 5242880; // 5MB default
    private $upload_dir;
    public $errors = [];
    public $filename;

    public function __construct($upload_dir, $max_size = 5242880) {
        $this->upload_dir = rtrim($upload_dir, '/');
        $this->max_size = $max_size;
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    public function allowedTypes($types) {
        $this->allowed_types = array_map('strtolower', $types);
        return $this;
    }

    public function maxSize($bytes) {
        $this->max_size = $bytes;
        return $this;
    }

    public function validate($file_input_name) {
        $this->errors = [];
        
        if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'No file uploaded or upload error.';
            return false;
        }

        $file = $_FILES[$file_input_name];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $size = $file['size'];
        $mime = mime_content_type($file['tmp_name']);

        // Check size
        if ($size > $this->max_size) {
            $max_mb = round($this->max_size / 1048576, 1);
            $this->errors[] = "File too large. Maximum: {$max_mb}MB.";
        }

        // Check type
        if (!empty($this->allowed_types)) {
            $allowed_exts = $this->allowed_types;
            if (!in_array($ext, $allowed_exts)) {
                $this->errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_exts);
            }
        }

        // Check MIME
        $allowed_mimes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
            'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        if (isset($allowed_mimes[$ext]) && $mime !== $allowed_mimes[$ext]) {
            $this->errors[] = 'File MIME type mismatch.';
        }

        // Check for malicious extensions
        $dangerous = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'htm'];
        if (in_array($ext, $dangerous)) {
            $this->errors[] = 'Dangerous file type rejected.';
        }

        return empty($this->errors);
    }

    public function save($file_input_name, $prefix = '') {
        if (!$this->validate($file_input_name)) return false;

        $file = $_FILES[$file_input_name];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->filename = $prefix . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = $this->upload_dir . '/' . $this->filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->filename;
        }

        $this->errors[] = 'Failed to save file.';
        return false;
    }
}
