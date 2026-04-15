<?php
require_once "../config.php";
require_once "../includes/rbac.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    $rbac = new RBAC($conn);
    
    if ($rbac->authenticate($username, $password, "dean")) {
        $_SESSION["user_id"] = $rbac->getUserId();
        $_SESSION["username"] = $rbac->getUserName();
        $_SESSION["user_role"] = "dean";
        $_SESSION["branch_id"] = $rbac->getBranchId();
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials or insufficient permissions";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Login - CJLG University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body { height: 100%; width: 100%; margin: 0; padding: 0; }
        body {
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460) !important;
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 380px !important;
            max-width: 95vw !important;
            margin: auto !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
        }
        .login-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 16px 16px 0 0;
        }
        .login-header i { font-size: 2.5rem; margin-bottom: 10px; display: block; }
        .login-header h3 { margin: 0 0 5px 0; font-weight: 700; }
        .login-header p { margin: 0; opacity: 0.9; font-size: 0.9rem; }
        .login-body { padding: 30px; }
        .login-body .form-label { font-weight: 600; margin-bottom: 8px; display: block; color: #333; }
        .login-body .input-group { border-radius: 8px; overflow: hidden; display: flex; }
        .login-body .input-group-text { background: #f9fafb; border: 1px solid #ddd; border-right: none; padding: 10px 12px; color: #666; }
        .login-body .form-control { border: 1px solid #ddd; border-left: none; padding: 10px 12px; width: 100%; }
        .login-body .form-control:focus { outline: none; border-color: #2c3e50; box-shadow: 0 0 0 3px rgba(44,62,80,0.15); }
        .login-body .btn-primary { 
            background: linear-gradient(135deg, #2c3e50, #34495e); 
            border: none; 
            padding: 12px; 
            font-weight: 600; 
            width: 100%;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-body .btn-primary:hover { background: linear-gradient(135deg, #1a252f, #2c3e50); }
        .login-footer { text-align: center; padding-top: 15px; margin-top: 15px; border-top: 1px solid #eee; }
        .login-footer a { color: #2c3e50; text-decoration: none; font-weight: 500; }
        .login-body .mb-3 { margin-bottom: 20px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .alert-danger { background: #fee; color: #c00; border: 1px solid #fcc; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-user-graduate"></i>
            <h3>Dean Panel</h3>
            <p>CJLG University</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            <div class="login-footer">
                <a href="../index.php"><i class="fas fa-home me-1"></i> Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
