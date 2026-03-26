<?php
require_once 'config.php';

echo "=== SYSTEM CLEANUP ===\n\n";

$message = '';

// Clean up login files to use unified RBAC
echo "1. Updating login files...\n";

// Update dean login
$dean_login = '<?php
require_once "../config.php";
require_once "../includes/rbac.php";

session_start();
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
    <title>Dean Login - Enrollment System</title>
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
                    <div class="login-header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                        <i class="fas fa-user-graduate fa-3x mb-3"></i>
                        <h3>Dean Panel</h3>
                        <p class="mb-0">Enrollment System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="login-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

file_put_contents('C:/xampp/htdocs/enrollment_system/dean/login.php', $dean_login);
echo "   - dean/login.php updated\n";

// Update finance login
$finance_login = '<?php
require_once "../config.php";
require_once "../includes/rbac.php";

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    $rbac = new RBAC($conn);
    
    if ($rbac->authenticate($username, $password, "finance")) {
        $_SESSION["user_id"] = $rbac->getUserId();
        $_SESSION["username"] = $rbac->getUserName();
        $_SESSION["user_role"] = "finance";
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
    <title>Finance Login - Enrollment System</title>
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
                        <i class="fas fa-coins fa-3x mb-3"></i>
                        <h3>Finance Portal</h3>
                        <p class="mb-0">Enrollment System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="login-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

file_put_contents('C:/xampp/htdocs/enrollment_system/finance/login.php', $finance_login);
echo "   - finance/login.php updated\n";

// Update teacher login
$teacher_login = '<?php
require_once "../config.php";
require_once "../includes/rbac.php";

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    $rbac = new RBAC($conn);
    
    if ($rbac->authenticate($username, $password, "teacher")) {
        $_SESSION["user_id"] = $rbac->getUserId();
        $_SESSION["username"] = $rbac->getUserName();
        $_SESSION["user_role"] = "teacher";
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
    <title>Teacher Login - Enrollment System</title>
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
                    <div class="login-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                        <h3>Teacher Portal</h3>
                        <p class="mb-0">Enrollment System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="login-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

file_put_contents('C:/xampp/htdocs/enrollment_system/teacher/login.php', $teacher_login);
echo "   - teacher/login.php updated\n";

// Update registrar login
$registrar_login = '<?php
require_once "../config.php";
require_once "../includes/rbac.php";

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    $rbac = new RBAC($conn);
    
    if ($rbac->authenticate($username, $password, "registrar")) {
        $_SESSION["user_id"] = $rbac->getUserId();
        $_SESSION["username"] = $rbac->getUserName();
        $_SESSION["user_role"] = "registrar";
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
    <title>Registrar Login - Enrollment System</title>
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
                    <div class="login-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <h3>Registrar Portal</h3>
                        <p class="mb-0">Enrollment System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="login-back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

file_put_contents('C:/xampp/htdocs/enrollment_system/registrar/login.php', $registrar_login);
echo "   - registrar/login.php updated\n";

echo "\n2. Updating logout files...\n";

// Update all logout files
$logout_template = '<?php
session_start();
session_destroy();
header("Location: login.php");
exit;
?>';

file_put_contents('C:/xampp/htdocs/enrollment_system/dean/logout.php', $logout_template);
file_put_contents('C:/xampp/htdocs/enrollment_system/finance/logout.php', $logout_template);
file_put_contents('C:/xampp/htdocs/enrollment_system/teacher/logout.php', $logout_template);
file_put_contents('C:/xampp/htdocs/enrollment_system/registrar/logout.php', $logout_template);
file_put_contents('C:/xampp/htdocs/enrollment_system/superadmin/logout.php', $logout_template);

echo "   - All logout files updated\n";

echo "\n3. Updating dashboard files...\n";

// Update dean dashboard
$dean_dashboard = '<?php
require_once "../config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "dean") {
    header("Location: login.php");
    exit;
}

$page = isset($_GET["page"]) ? $_GET["page"] : "dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean Panel - Enrollment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-user-graduate me-2"></i>Dean Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "dashboard" ? "active" : ""; ?>" href="dashboard.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "departments" ? "active" : ""; ?>" href="dashboard.php?page=departments">
                            <i class="fas fa-building me-1"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "teachers" ? "active" : ""; ?>" href="dashboard.php?page=teachers">
                            <i class="fas fa-chalkboard-teacher me-1"></i> Teachers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "grades" ? "active" : ""; ?>" href="dashboard.php?page=grades">
                            <i class="fas fa-chart-line me-1"></i> Approve Grades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "subjects" ? "active" : ""; ?>" href="dashboard.php?page=subjects">
                            <i class="fas fa-book me-1"></i> Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == "settings" ? "active" : ""; ?>" href="dashboard.php?page=settings">
                            <i class="fas fa-cog me-1"></i> Settings
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION["username"]; ?>
                            <small class="d-block text-muted">Dean</small>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        switch($page) {
            case "departments":
                include "pages/departments.php";
                break;
            case "teachers":
                include "pages/teachers.php";
                break;
            case "grades":
                include "pages/grades.php";
                break;
            case "subjects":
                include "pages/subjects.php";
                break;
            case "settings":
                include "pages/settings.php";
                break;
            default:
                include "pages/home.php";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

file_put_contents('C:/xampp/htdocs/enrollment_system/dean/dashboard.php', $dean_dashboard);
echo "   - dean/dashboard.php updated\n";

echo "\n=== CLEANUP COMPLETE ===\n";
echo "\nLogin credentials (all use system_users table):\n";
echo "- Super Admin: admin / admin123\n";
echo "- Dean: Use username created in Super Admin\n";
echo "- Registrar: Use username created in Super Admin\n";
echo "- Finance: Use username created in Super Admin\n";
echo "- Teacher: Use username created in Super Admin\n";
echo "- Student: student_number / password123\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Cleanup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4>System Cleanup Complete!</h4>
            </div>
            <div class="card-body">
                <p>All login files have been updated to use the unified RBAC system.</p>
                
                <div class="alert alert-info">
                    <h5>Login Credentials:</h5>
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Super Admin</td>
                                <td><code>admin</code></td>
                                <td><code>admin123</code></td>
                                <td>Create other users</td>
                            </tr>
                            <tr>
                                <td>Dean</td>
                                <td><code>Use created user</code></td>
                                <td><code>password123</code></td>
                                <td>Create in Super Admin</td>
                            </tr>
                            <tr>
                                <td>Registrar</td>
                                <td><code>Use created user</code></td>
                                <td><code>password123</code></td>
                                <td>Create in Super Admin</td>
                            </tr>
                            <tr>
                                <td>Finance</td>
                                <td><code>Use created user</code></td>
                                <td><code>password123</code></td>
                                <td>Create in Super Admin</td>
                            </tr>
                            <tr>
                                <td>Teacher</td>
                                <td><code>Use created user</code></td>
                                <td><code>password123</code></td>
                                <td>Create in Super Admin</td>
                            </tr>
                            <tr>
                                <td>Student</td>
                                <td><code>student_number</code></td>
                                <td><code>password123</code></td>
                                <td>Created by Registrar</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <a href="index.php" class="btn btn-primary">Go to Home</a>
                <a href="superadmin/login.php" class="btn btn-success">Super Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>
