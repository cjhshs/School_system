<?php
class RBAC {
    private $conn;
    private $current_user;
    private $current_role;
    private $permissions = array();
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function authenticate($username, $password, $role = null) {
        $sql = "SELECT su.*, r.name as role_name, r.hierarchy_level 
                FROM system_users su 
                JOIN roles r ON su.role_id = r.id 
                WHERE su.username = ? AND su.is_active = 1";
        
        if ($role) {
            $sql .= " AND r.name = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $username, $role);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $username);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check hashed password
            if (substr($user['password'], 0, 4) === '$2y$') {
                if (password_verify($password, $user['password'])) {
                    $this->current_user = $user;
                    $this->current_role = $user['role_name'];
                    $this->loadPermissions($user['role_id']);
                    $this->updateLastLogin($user['id']);
                    $this->logActivity('login', 'User logged in');
                    return true;
                }
            } else {
                // Plain text password comparison
                if ($password === $user['password']) {
                    $this->current_user = $user;
                    $this->current_role = $user['role_name'];
                    $this->loadPermissions($user['role_id']);
                    $this->updateLastLogin($user['id']);
                    $this->logActivity('login', 'User logged in');
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public function authenticateStudent($student_number, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM students WHERE student_number = ?");
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stored_password = $user['password'];
            
            if (substr($stored_password, 0, 4) === '$2y$') {
                if (password_verify($password, $stored_password)) {
                    $this->current_user = $user;
                    $this->current_role = 'student';
                    $this->loadPermissionsByRoleName('student');
                    return true;
                }
            } else {
                if ($password === $stored_password) {
                    $this->current_user = $user;
                    $this->current_role = 'student';
                    $this->loadPermissionsByRoleName('student');
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function loadPermissions($role_id) {
        $sql = "SELECT p.name FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                WHERE rp.role_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[] = $row['name'];
        }
    }
    
    private function loadPermissionsByRoleName($role_name) {
        $sql = "SELECT p.name FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                JOIN roles r ON r.id = rp.role_id 
                WHERE r.name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[] = $row['name'];
        }
    }
    
    public function hasPermission($permission) {
        return in_array($permission, $this->permissions);
    }
    
    public function hasAnyPermission($permissions) {
        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) {
                return true;
            }
        }
        return false;
    }
    
    public function hasAllPermissions($permissions) {
        foreach ($permissions as $perm) {
            if (!$this->hasPermission($perm)) {
                return false;
            }
        }
        return true;
    }
    
    public function getCurrentUser() {
        return $this->current_user;
    }
    
    public function getCurrentRole() {
        return $this->current_role;
    }
    
    public function getUserId() {
        return $this->current_user['id'] ?? null;
    }
    
    public function getUserName() {
        if ($this->current_role === 'student') {
            return $this->current_user['firstname'] . ' ' . $this->current_user['lastname'];
        }
        return $this->current_user['first_name'] . ' ' . $this->current_user['last_name'];
    }
    
    public function getBranchId() {
        return $this->current_user['branch_id'] ?? null;
    }
    
    public function isSuperAdmin() {
        return $this->current_role === 'super_admin';
    }
    
    public function isRegistrar() {
        return $this->current_role === 'registrar';
    }
    
    public function isDean() {
        return $this->current_role === 'dean';
    }
    
    public function isTeacher() {
        return $this->current_role === 'teacher';
    }
    
    public function isFinance() {
        return $this->current_role === 'finance';
    }
    
    public function isStudent() {
        return $this->current_role === 'student';
    }
    
    public function canManageRole($target_role) {
        $hierarchy = array(
            'super_admin' => 100,
            'registrar' => 80,
            'dean' => 70,
            'finance' => 60,
            'teacher' => 40,
            'student' => 20
        );
        
        $current_level = $hierarchy[$this->current_role] ?? 0;
        $target_level = $hierarchy[$target_role] ?? 0;
        
        return $current_level > $target_level;
    }
    
    private function updateLastLogin($user_id) {
        $sql = "UPDATE system_users SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    public function logActivity($action, $description = '') {
        if ($this->current_user) {
            $user_id = $this->current_user['id'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issss", $user_id, $action, $description, $ip, $user_agent);
            $stmt->execute();
        }
    }
    
    public function logout() {
        if ($this->current_user) {
            $this->logActivity('logout', 'User logged out');
        }
        $this->current_user = null;
        $this->current_role = null;
        $this->permissions = array();
    }
}

function requireLogin($role = null) {
    global $conn;
    
    $rbac = new RBAC($conn);
    
    if ($role === 'student') {
        if (!isset($_SESSION['student_id'])) {
            header('Location: /enrollment_system/student/login.php');
            exit;
        }
        $rbac->authenticateStudent($_SESSION['student_number'] ?? '', '');
        if (!$rbac->isStudent()) {
            header('Location: /enrollment_system/student/login.php');
            exit;
        }
    } elseif ($role) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /enrollment_system/index.php');
            exit;
        }
    } else {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['student_id'])) {
            header('Location: /enrollment_system/index.php');
            exit;
        }
    }
    
    return $rbac;
}

function checkPermission($permission) {
    global $conn;
    
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['student_id'])) {
        return false;
    }
    
    $rbac = new RBAC($conn);
    
    if (isset($_SESSION['student_number'])) {
        $rbac->authenticateStudent($_SESSION['student_number'], '');
    }
    
    return $rbac->hasPermission($permission);
}

function hasPermission($conn, $permission) {
    $rbac = new RBAC($conn);
    
    if (isset($_SESSION['student_number'])) {
        $rbac->authenticateStudent($_SESSION['student_number'], '');
    } elseif (isset($_SESSION['username'])) {
        $rbac->authenticate($_SESSION['username'], '');
    }
    
    return $rbac->hasPermission($permission);
}
?>
