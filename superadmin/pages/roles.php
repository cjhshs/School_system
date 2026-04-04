<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_permission') {
        $role_id = intval($_POST['role_id']);
        $permission_id = intval($_POST['permission_id']);
        
        $check = $conn->query("SELECT id FROM role_permissions WHERE role_id = $role_id AND permission_id = $permission_id");
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id AND permission_id = $permission_id");
        } else {
            $conn->query("INSERT INTO role_permissions (role_id, permission_id) VALUES ($role_id, $permission_id)");
        }
    }
}

$roles = $conn->query("SELECT * FROM roles ORDER BY hierarchy_level DESC");
$permissions = $conn->query("SELECT * FROM permissions ORDER BY name");
$role_perms = $conn->query("SELECT * FROM role_permissions");

$perm_map = array();
while ($rp = $role_perms->fetch_assoc()) {
    $perm_map[$rp['role_id']][] = $rp['permission_id'];
}

$permissions_list = array();
while ($p = $permissions->fetch_assoc()) {
    $permissions_list[] = $p;
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-shield-alt"></i> Roles & Permissions</h1>
        <p>Manage role-based access control</p>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>RBAC System:</strong> Each role has specific permissions that control what actions users can perform.
    Super Admin has all permissions. Check/uncheck to update.
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-table me-2"></i>Permission Matrix</h5>
    </div>
    <div class="card-body pt-0">
        <div class="search-container">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input table-search" data-table="rolesTable" placeholder="Search permissions...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="rolesTable">
            <thead>
                <tr>
                    <th style="width: 200px;">Permission</th>
                    <?php $roles = $conn->query("SELECT * FROM roles ORDER BY hierarchy_level DESC"); while ($role = $roles->fetch_assoc()): ?>
                        <th class="text-center" style="min-width: 90px;">
                            <?php echo htmlspecialchars($role['display_name']); ?>
                            <br><small class="text-muted">Lvl <?php echo $role['hierarchy_level']; ?></small>
                        </th>
                    <?php endwhile; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions_list as $perm): ?>
                <tr>
                    <td>
                        <div><?php echo htmlspecialchars($perm['name']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($perm['description'] ?? ''); ?></small>
                    </td>
                    <?php 
                    $roles2 = $conn->query("SELECT * FROM roles ORDER BY hierarchy_level DESC");
                    while ($role = $roles2->fetch_assoc()): 
                        $has = in_array($perm['id'], $perm_map[$role['id']] ?? []);
                    ?>
                                <td class="text-center">
                                    <form method="POST" class="d-inline">
    <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="toggle_permission">
                                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                        <input type="hidden" name="permission_id" value="<?php echo $perm['id']; ?>">
                                        <input type="checkbox" class="form-check-input" <?php echo $has ? 'checked' : ''; ?> 
                                               onchange="this.form.submit()">
                                    </form>
                                </td>
                            <?php endwhile; ?>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-sitemap me-2"></i>Role Hierarchy</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-md-2">
                <div class="p-3 bg-danger bg-opacity-10 text-danger rounded border border-danger">
                    <i class="fas fa-crown fa-2x mb-2"></i>
                    <h6>Super Admin</h6>
                    <small>Level 100</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded border border-primary">
                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                    <h6>Registrar</h6>
                    <small>Level 80</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded border border-warning">
                    <i class="fas fa-user-graduate fa-2x mb-2"></i>
                    <h6>Dean</h6>
                    <small>Level 70</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-success bg-opacity-10 text-success rounded border border-success">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h6>Finance</h6>
                    <small>Level 60</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-info bg-opacity-10 text-info rounded border border-info">
                    <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                    <h6>Teacher</h6>
                    <small>Level 40</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-3 bg-secondary bg-opacity-10 text-secondary rounded border border-secondary">
                    <i class="fas fa-user fa-2x mb-2"></i>
                    <h6>Student</h6>
                    <small>Level 20</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('.table-search[data-table="rolesTable"]')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#rolesTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
