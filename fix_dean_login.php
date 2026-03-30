<?php
require_once 'config.php';

$message = '';

// If form submitted, update password
if (isset($_POST['reset'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $conn->query("UPDATE system_users SET password = '$password' WHERE username = '$username'");
    $message = "Password updated for $username!";
}

// Show users
$users = $conn->query("
    SELECT su.id, su.username, su.role_id, r.name as role_name, su.is_active
    FROM system_users su
    JOIN roles r ON su.role_id = r.id
    WHERE r.name = 'dean' OR su.username = 'dean_cj'
    ORDER BY su.id DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Dean Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <h2>Fix Dean Login</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">Reset Dean Password</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="e.g., dean_cj">
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="text" name="password" class="form-control" required placeholder="password123">
                    </div>
                    <button type="submit" name="reset" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
        
        <h4>Dean Users:</h4>
        <table class="table table-bordered">
            <tr><th>ID</th><th>Username</th><th>Role ID</th><th>Role</th><th>Active</th></tr>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo $u['role_id']; ?></td>
                <td><?php echo $u['role_name']; ?></td>
                <td><?php echo $u['is_active']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
