<?php
require_once '../config.php';
require_once '../includes/rbac.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];
    
    $rbac = new RBAC($conn);
    
    if ($rbac->authenticateStudent($student_number, $password)) {
        $_SESSION['student_id'] = $rbac->getUserId();
        $_SESSION['student_number'] = $student_number;
        $_SESSION['student_name'] = $rbac->getUserName();
        $_SESSION['user_role'] = 'student';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid student number or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/style.css">
    <link rel="stylesheet" href="/enrollment_system/css/login.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-graduate fa-3x mb-3"></i>
                        <h3>Student Portal</h3>
                        <p class="mb-0">CJLG University</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="student_number" class="form-label">Student Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g., 202600001" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login text-white">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
